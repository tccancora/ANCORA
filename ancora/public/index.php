<?php
/**
 * ÂNCORA - Front Controller (Public Router)
 */

require_once __DIR__ . '/../config/app.php';

// SESSÃO GLOBAL: Configurar cookie de sessão ANTES de iniciar a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',         // SEMPRE '/' para garantir persistência entre /public/ e /
        'domain'   => '',
        'secure'   => false,
        'httponly'  => true,
        'samesite'  => 'Lax'
    ]);
    
    session_start();
}

// Configurar fuso horário oficial do sistema
date_default_timezone_set('America/Sao_Paulo');

// Captura a rota solicitada via URL ou Parâmetro ?route=
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');

if ($scriptDir !== '/' && $scriptDir !== '\\' && strpos($requestUri, $scriptDir) === 0) {
    $path = substr($requestUri, strlen($scriptDir));
} else {
    $path = $requestUri;
}

$path = trim($path, '/');
if (empty($path) || $path === 'index.php') {
    $route = $_GET['route'] ?? 'home';
} else {
    $route = $path;
}

// Normalização robusta da rota: se contiver '?', separa a rota real dos parâmetros GET
if (strpos($route, '?') !== false) {
    list($cleanRoute, $queryString) = explode('?', $route, 2);
    $route = trim($cleanRoute, '/');
    parse_str($queryString, $extraGet);
    $_GET = array_merge($_GET, $extraGet);
    $_REQUEST = array_merge($_REQUEST, $extraGet);
}

// ============================================================================
// PROTEÇÃO GLOBAL DE AUTENTICAÇÃO (Auth Guard)
// ============================================================================
$rotasPublicas = [
    'home', 'login', 'cadastro', 'primeiro-acesso',
    'recuperar-senha', 'verificar-codigo', 'redefinir-senha',
    'logout'
];

if (!in_array($route, $rotasPublicas, true)) {
    if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Sessão expirada. Faça login novamente.']);
            exit;
        }
        
        header('Location: ' . url('login'));
        exit;
    }
}

// Despacho de Rotas
switch ($route) {
    case 'dashboard':
        $perfilAtual = mb_strtolower(trim($_SESSION['user']['perfil_nome'] ?? ''));
        if ($perfilAtual === 'aluno') {
            header('Location: ' . url('aluno'));
        } elseif ($perfilAtual === 'professor') {
            header('Location: ' . url('professor'));
        } elseif ($perfilAtual === 'funcionario' || $perfilAtual === 'funcionário') {
            header('Location: ' . url('funcionario'));
        } else {
            header('Location: ' . url('admin'));
        }
        exit;

    case 'eventos':
    case 'mensagens':
    case 'reservas':
    case 'achados-perdidos':
        $_SESSION['flash_info'] = 'Este módulo estará disponível em breve.';
        header('Location: ' . url('dashboard'));
        exit;

    case 'login':
        require_once __DIR__ . '/../app/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->index();
        break;

    case 'cadastro':
        require_once __DIR__ . '/../app/controllers/CadastroController.php';
        $controller = new CadastroController();
        $controller->index();
        break;

    case 'primeiro-acesso':
        require_once __DIR__ . '/../app/controllers/PrimeiroAcessoController.php';
        $controller = new PrimeiroAcessoController();
        $controller->index();
        break;

    case 'recuperar-senha':
        require_once __DIR__ . '/../app/controllers/RecuperarSenhaController.php';
        $controller = new RecuperarSenhaController();
        $controller->index();
        break;

    case 'verificar-codigo':
        require_once __DIR__ . '/../app/controllers/VerificarCodigoController.php';
        $controller = new VerificarCodigoController();
        $controller->index();
        break;

    case 'redefinir-senha':
        require_once __DIR__ . '/../app/controllers/RedefinirSenhaController.php';
        $controller = new RedefinirSenhaController();
        $controller->index();
        break;

    case 'admin':
        require_once __DIR__ . '/../app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->index();
        break;

    case 'admin/turmas':
    case 'turmas':
        require_once __DIR__ . '/../app/controllers/TurmasController.php';
        $controller = new TurmasController();
        $controller->index();
        break;

    case 'aluno':
        require_once __DIR__ . '/../app/controllers/AlunoController.php';
        $controller = new AlunoController();
        $controller->index();
        break;

    case 'professor':
        require_once __DIR__ . '/../app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->index();
        break;

    case 'funcionario':
        require_once __DIR__ . '/../app/controllers/FuncionarioController.php';
        $controller = new FuncionarioController();
        $controller->index();
        break;

    case 'usuarios':
        require_once __DIR__ . '/../app/controllers/UsuariosController.php';
        $controller = new UsuariosController();
        $controller->index();
        break;

    case 'configuracoes':
        require_once __DIR__ . '/../app/controllers/ConfiguracoesController.php';
        $controller = new ConfiguracoesController();
        $controller->index();
        break;

    case 'notificacoes/poll':
        require_once __DIR__ . '/../app/controllers/NotificacoesController.php';
        $controller = new NotificacoesController();
        $controller->poll();
        break;

    case 'notificacoes/action':
        require_once __DIR__ . '/../app/controllers/NotificacoesController.php';
        $controller = new NotificacoesController();
        $controller->action();
        break;

    case 'notificacoes':
        require_once __DIR__ . '/../app/controllers/NotificacoesController.php';
        $controller = new NotificacoesController();
        $controller->index();
        break;

    // Rotas de Tarefas
    case 'tarefas/criar':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->criar();
        break;

    case 'tarefas/editar':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->editar();
        break;

    case 'tarefas/excluir':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->excluir();
        break;

    case 'tarefas/detalhes':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->detalhes();
        break;

    case 'tarefas/entregas':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->entregas();
        break;

    case 'tarefas/submeter':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->submeter();
        break;

    case 'tarefas/corrigir':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->corrigir();
        break;

    case 'tarefas/download-material':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->downloadMaterial();
        break;

    case 'tarefas/download-entrega':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->downloadEntrega();
        break;

    case 'tarefas':
        require_once __DIR__ . '/../app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->index();
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: ' . url('login'));
        exit;

    case 'home':
        require_once __DIR__ . '/../app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    default:
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            header('Location: ' . url('dashboard'));
            exit;
        }
        require_once __DIR__ . '/../app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
}
