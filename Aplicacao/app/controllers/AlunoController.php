<?php
/**
 * Controller responsável pelo Dashboard do Aluno
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Tarefa.php';

class AlunoController {

    protected function protegerAcesso() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $perfilNome = mb_strtolower(trim($_SESSION['user']['perfil_nome'] ?? ''));
        if (empty($perfilNome) && isset($_SESSION['user']['perfil_id'])) {
            $perfil = Perfil::buscarPorId((int)$_SESSION['user']['perfil_id']);
            $perfilNome = $perfil ? mb_strtolower(trim($perfil['nome'])) : '';
        }

        // Se o usuário não for Aluno, redireciona para o dashboard correto do seu perfil
        if ($perfilNome !== 'aluno') {
            if ($perfilNome === 'administrador') {
                header('Location: ' . url('admin'));
            } elseif ($perfilNome === 'professor') {
                header('Location: ' . url('professor'));
            } elseif ($perfilNome === 'funcionario' || $perfilNome === 'funcionário') {
                header('Location: ' . url('funcionario'));
            } else {
                header('Location: ' . url('login'));
            }
            exit;
        }
    }

    public function index() {
        $this->protegerAcesso();

        $usuarioLogado = $_SESSION['user'];
        $alunoId       = (int)($usuarioLogado['id'] ?? 0);
        $instituicaoId = (int)($usuarioLogado['instituicao_id'] ?? 1);

        $userName   = $usuarioLogado['nome'] ?? 'Aluno';
        $userRole   = 'Aluno';
        $userCourse = 'Ciência da Computação';

        // Iniciais para o avatar (ex: 'Ana Beatriz Silva' -> 'AB')
        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'A';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        // Contadores reais de tarefas do aluno
        $tarefasContadores = Tarefa::obterContadoresDashboard($alunoId, 3, $instituicaoId);
        $tarefasPendentes  = $tarefasContadores['tarefas_pendentes'] ?? 0;

        $pageTitle = "Dashboard Aluno — ÂNCORA";

        require __DIR__ . '/../views/aluno/dashboard.php';
    }
}
