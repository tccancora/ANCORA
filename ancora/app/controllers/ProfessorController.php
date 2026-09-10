<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller do Dashboard do Professor (/professor)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Gerenciar o acesso seguro do perfil Professor, isolando o ambiente administrativo
 * e fornecendo as métricas e resumos das suas turmas, tarefas e agenda.
 * 
 * REGRAS DE SEGURANÇA E PERMISSÕES:
 * 1. AUTENTICAÇÃO: Exige sessão ativa ($_SESSION['user']).
 * 2. AUTORIZAÇÃO POR PERFIL: Exclusivo para perfil 'Professor' (perfil_id = 2).
 *    Usuários com perfis Administrador, Aluno ou Funcionário são bloqueados e redirecionados.
 * 3. ISOLAMENTO MULTI-TENANCY: Dados filtrados estritamente pela instituição e vínculos do professor.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/Tarefa.php';

class ProfessorController {

    /**
     * Valida se o usuário logado possui a permissão estrita de Professor.
     */
    protected function protegerAcesso() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Exige autenticação prévia no sistema
        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $usuarioLogado = $_SESSION['user'];

        // 2. Garante que o perfil seja estritamente 'Professor'
        $perfilNome = mb_strtolower(trim($usuarioLogado['perfil_nome'] ?? ''));
        if (empty($perfilNome) && isset($usuarioLogado['perfil_id'])) {
            $perfil = Perfil::buscarPorId((int)$usuarioLogado['perfil_id']);
            $perfilNome = $perfil ? mb_strtolower(trim($perfil['nome'])) : '';
        }

        if ($perfilNome !== 'professor') {
            if ($perfilNome === 'administrador') {
                header('Location: ' . url('admin'));
            } elseif ($perfilNome === 'funcionario' || $perfilNome === 'funcionário') {
                header('Location: ' . url('funcionario'));
            } elseif ($perfilNome === 'aluno') {
                header('Location: ' . url('aluno'));
            } else {
                header('Location: ' . url('login'));
            }
            exit;
        }
    }

    public function index() {
        $this->protegerAcesso();

        $usuarioLogado = $_SESSION['user'];
        $instituicaoId = (int)($usuarioLogado['instituicao_id'] ?? 1);
        $professorId   = (int)($usuarioLogado['id'] ?? 0);

        // Dados do Professor para o Header/Sidebar
        $userName       = $usuarioLogado['nome'] ?? 'Professor';
        $userRole       = 'Professor';
        $userDepartment = 'Ciência da Computação';

        // Iniciais dinâmicas para o avatar circular (ex: 'Carlos Mendes' -> 'CM')
        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'P';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        // Consulta turmas vinculadas a este professor no banco de dados
        $db = getDatabaseConnection();
        $stmtTurmas = $db->prepare("
            SELECT COUNT(DISTINCT tp.turma_id) as total_turmas 
            FROM turma_professores tp
            JOIN turmas t ON tp.turma_id = t.id
            WHERE tp.professor_id = :prof_id AND t.instituicao_id = :inst_id
        ");
        $stmtTurmas->execute([
            ':prof_id' => $professorId,
            ':inst_id' => $instituicaoId
        ]);
        $rowTurmas = $stmtTurmas->fetch();
        $totalTurmasVinculadas = (int)($rowTurmas['total_turmas'] ?? 0);

        // Contadores reais de tarefas
        $tarefasContadores = Tarefa::obterContadoresDashboard($professorId, 2, $instituicaoId);
        $tarefasPendentes  = $tarefasContadores['entregas_pendentes_correcao'] ?? $tarefasContadores['tarefas_ativas'];

        $pageTitle = "Dashboard Professor — ÂNCORA";

        require __DIR__ . '/../views/professor/dashboard.php';
    }
}
