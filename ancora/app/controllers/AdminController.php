<?php
/**
 * Controller responsável pelo Dashboard do Administrador
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Tarefa.php';

class AdminController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Proteção de Rota: Exige usuário autenticado na sessão
        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $usuarioLogado = $_SESSION['user'];

        // 2. Verificação do Perfil do usuário
        $perfilNome = mb_strtolower(trim($usuarioLogado['perfil_nome'] ?? ''));
        if (empty($perfilNome) && isset($usuarioLogado['perfil_id'])) {
            $perfil = Perfil::buscarPorId((int)$usuarioLogado['perfil_id']);
            $perfilNome = $perfil ? mb_strtolower(trim($perfil['nome'])) : '';
        }

        // Apenas perfil Administrador pode acessar o dashboard administrativo
        if ($perfilNome !== 'administrador') {
            if ($perfilNome === 'aluno') {
                header('Location: ' . url('aluno'));
            } elseif ($perfilNome === 'professor') {
                header('Location: ' . url('professor'));
            } elseif ($perfilNome === 'funcionario' || $perfilNome === 'funcionário') {
                header('Location: ' . url('funcionario'));
            } else {
                header('Location: ' . url('login'));
            }
            exit;
        }

        // 3. Nome do Usuário REAL autenticado vindo da sessão
        $userName   = $usuarioLogado['nome'] ?? 'Administrador';
        $userRole   = 'Administrador';
        $userSector = 'Diretoria';

        // Iniciais para o avatar circular (ex: 'Roberto Lima' -> 'RL')
        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'A';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        // Métricas de tarefas reais do banco
        $tarefasContadores = Tarefa::obterContadoresDashboard((int)$usuarioLogado['id'], 1, (int)$usuarioLogado['instituicao_id']);
        $tarefasPendentes = $tarefasContadores['entregas_pendentes_correcao'] ?? $tarefasContadores['tarefas_ativas'];

        $pageTitle = "Dashboard Administrador — ÂNCORA";

        require __DIR__ . '/../views/admin/dashboard.php';
    }
}
