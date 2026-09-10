<?php
/**
 * ÂNCORA - Ponto de Entrada Principal (MVC Router)
 */

require_once __DIR__ . '/config/app.php';

// SESSÃO GLOBAL: Configurar cookie de sessão ANTES de iniciar a sessão
// Garante que o cookie de sessão funcione em qualquer caminho do servidor
if (session_status() === PHP_SESSION_NONE) {
    $cookiePath = '/';
    // Detectar o diretório base da aplicação para o cookie path
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptDir !== '/' && !empty($scriptDir)) {
        $cookiePath = rtrim($scriptDir, '/') . '/';
    }
    
    session_set_cookie_params([
        'lifetime' => 0,           // Cookie de sessão (expira ao fechar o navegador)
        'path'     => '/',         // SEMPRE '/' para garantir persistência entre /public/ e /
        'domain'   => '',          // Domínio atual
        'secure'   => false,       // false para XAMPP local (alterar para true em produção HTTPS)
        'httponly'  => true,       // Impede acesso via JavaScript
        'samesite'  => 'Lax'      // Proteção contra CSRF
    ]);
    
    session_start();
}

// Configurar fuso horário oficial do sistema para o Brasil
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
// Rotas PÚBLICAS que NÃO exigem login:
$rotasPublicas = [
    'home', 'login', 'cadastro', 'primeiro-acesso',
    'recuperar-senha', 'verificar-codigo', 'redefinir-senha',
    'logout'
];

// Se a rota NÃO é pública, exige sessão válida ANTES de despachar ao controller
if (!in_array($route, $rotasPublicas, true)) {
    if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        // Requisição AJAX retorna JSON 401, páginas normais redirecionam para login
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
        require_once __DIR__ . '/app/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->index();
        break;

    case 'cadastro':
        require_once __DIR__ . '/app/controllers/CadastroController.php';
        $controller = new CadastroController();
        $controller->index();
        break;

    case 'primeiro-acesso':
        require_once __DIR__ . '/app/controllers/PrimeiroAcessoController.php';
        $controller = new PrimeiroAcessoController();
        $controller->index();
        break;

    case 'recuperar-senha':
        require_once __DIR__ . '/app/controllers/RecuperarSenhaController.php';
        $controller = new RecuperarSenhaController();
        $controller->index();
        break;

    case 'verificar-codigo':
        require_once __DIR__ . '/app/controllers/VerificarCodigoController.php';
        $controller = new VerificarCodigoController();
        $controller->index();
        break;

    case 'redefinir-senha':
        require_once __DIR__ . '/app/controllers/RedefinirSenhaController.php';
        $controller = new RedefinirSenhaController();
        $controller->index();
        break;

    case 'admin':
        require_once __DIR__ . '/app/controllers/AdminController.php';
        $controller = new AdminController();
        $controller->index();
        break;

    case 'admin/turmas':
    case 'turmas':
        require_once __DIR__ . '/app/controllers/TurmasController.php';
        $controller = new TurmasController();
        $controller->index();
        break;

    case 'aluno':
        require_once __DIR__ . '/app/controllers/AlunoController.php';
        $controller = new AlunoController();
        $controller->index();
        break;

    case 'professor':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->index();
        break;

    case 'funcionario':
        require_once __DIR__ . '/app/controllers/FuncionarioController.php';
        $controller = new FuncionarioController();
        $controller->index();
        break;

    case 'usuarios':
        require_once __DIR__ . '/app/controllers/UsuariosController.php';
        $controller = new UsuariosController();
        $controller->index();
        break;

    case 'configuracoes':
        require_once __DIR__ . '/app/controllers/ConfiguracoesController.php';
        $controller = new ConfiguracoesController();
        $controller->index();
        break;

    case 'notificacoes/poll':
        require_once __DIR__ . '/app/controllers/NotificacoesController.php';
        $controller = new NotificacoesController();
        $controller->poll();
        break;

    case 'notificacoes/action':
        require_once __DIR__ . '/app/controllers/NotificacoesController.php';
        $controller = new NotificacoesController();
        $controller->action();
        break;

    case 'notificacoes':
        require_once __DIR__ . '/app/controllers/NotificacoesController.php';
        $controller = new NotificacoesController();
        $controller->index();
        break;

    // Rotas do Módulo de Tarefas
    case 'tarefas/criar':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->criar();
        break;

    case 'tarefas/editar':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->editar();
        break;

    case 'tarefas/excluir':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->excluir();
        break;

    case 'tarefas/detalhes':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->detalhes();
        break;

    case 'tarefas/entregas':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->entregas();
        break;

    case 'tarefas/submeter':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->submeter();
        break;

    case 'tarefas/corrigir':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->corrigir();
        break;

    case 'tarefas/download-material':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->downloadMaterial();
        break;

    case 'tarefas/download-entrega':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->downloadEntrega();
        break;

    case 'tarefas':
        require_once __DIR__ . '/app/controllers/TarefasController.php';
        $controller = new TarefasController();
        $controller->index();
        break;

    case 'logout':
        // Limpar dados de sessão antes de destruir
        $_SESSION = [];
        // Destruir cookie de sessão
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
        require_once __DIR__ . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    default:
        // Se estiver autenticado e tentar uma rota não mapeada, redireciona para o dashboard
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            header('Location: ' . url('dashboard'));
            exit;
        }
        // Se não autenticado, exibe a landing page
        require_once __DIR__ . '/app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
}
