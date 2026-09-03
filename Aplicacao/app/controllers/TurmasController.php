<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller do Módulo de Gestão de Turmas (/admin/turmas)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Controlar as ações administrativas de criação, edição, exclusão de turmas e o
 * gerenciamento de membros (professores com suas disciplinas e alunos vinculados).
 * 
 * REGRAS DE SEGURANÇA:
 * 1. AUTORIZAÇÃO: Acesso restrito ao perfil de Administrador (perfil_id = 1).
 * 2. MULTI-TENANCY: Garante que o Administrador opere apenas na sua própria instituição.
 * 3. VALIDAÇÕES: Impede turmas sem nome ou cadastros duplicados na mesma instituição.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../models/Turma.php';

class TurmasController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Proteção de Autenticação na Sessão
        if (!isset($_SESSION['user'])) {
            header('Location: ' . url('login'));
            exit;
        }

        $usuarioLogado = $_SESSION['user'];
        $instituicaoId = (int)($usuarioLogado['instituicao_id'] ?? 1);

        // 2. Proteção de Perfil: Apenas Administrador pode acessar esta área
        $perfilNome = mb_strtolower(trim($usuarioLogado['perfil_nome'] ?? ''));
        if (empty($perfilNome) && isset($usuarioLogado['perfil_id'])) {
            $perfil = Perfil::buscarPorId((int)$usuarioLogado['perfil_id']);
            $perfilNome = $perfil ? mb_strtolower(trim($perfil['nome'])) : '';
        }

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

        // Processamento das Ações HTTP POST (CRUD e Associação de Membros)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            try {
                if ($action === 'criar') {
                    $nome = trim($_POST['nome_turma'] ?? '');
                    if (empty($nome)) {
                        $_SESSION['flash_error'] = "O nome da turma é obrigatório.";
                    } else {
                        Turma::criar($nome, $instituicaoId);
                        $_SESSION['flash_success'] = "Turma '{$nome}' criada com sucesso!";
                    }
                } elseif ($action === 'editar') {
                    $id   = (int)($_POST['turma_id'] ?? 0);
                    $nome = trim($_POST['nome_turma'] ?? '');
                    if ($id <= 0 || empty($nome)) {
                        $_SESSION['flash_error'] = "Dados inválidos para edição da turma.";
                    } else {
                        Turma::atualizar($id, $nome, $instituicaoId);
                        $_SESSION['flash_success'] = "Turma atualizada com sucesso!";
                    }
                } elseif ($action === 'excluir') {
                    $id = (int)($_POST['turma_id'] ?? 0);
                    if ($id <= 0) {
                        $_SESSION['flash_error'] = "Identificador da turma inválido.";
                    } else {
                        Turma::excluir($id, $instituicaoId);
                        $_SESSION['flash_success'] = "Turma excluída com sucesso!";
                    }
                } elseif ($action === 'adicionar_professor') {
                    $turmaId     = (int)($_POST['turma_id'] ?? 0);
                    $professorId = (int)($_POST['professor_id'] ?? 0);
                    $disciplina  = trim($_POST['disciplina'] ?? '');

                    $turmaObj = Turma::buscarPorId($turmaId, $instituicaoId);
                    if (!$turmaObj || $professorId <= 0) {
                        $_SESSION['flash_error'] = "Selecione uma turma e professor válidos da sua instituição.";
                    } else {
                        Turma::adicionarProfessor($turmaId, $professorId, $disciplina);
                        $_SESSION['flash_success'] = "Professor vinculado à turma com sucesso!";
                        $_SESSION['open_membros_modal'] = $turmaId;
                    }
                } elseif ($action === 'remover_professor') {
                    $turmaId   = (int)($_POST['turma_id'] ?? 0);
                    $vinculoId = (int)($_POST['vinculo_id'] ?? 0);
                    $profId    = (int)($_POST['professor_id'] ?? 0);

                    $turmaObj = Turma::buscarPorId($turmaId, $instituicaoId);
                    if (!$turmaObj) {
                        $_SESSION['flash_error'] = "Acesso negado: Turma não encontrada ou pertence a outra instituição.";
                    } else {
                        Turma::removerProfessor($turmaId, $profId, $vinculoId);
                        $_SESSION['flash_success'] = "Professor desvinculado da turma!";
                        $_SESSION['open_membros_modal'] = $turmaId;
                    }
                } elseif ($action === 'adicionar_aluno') {
                    $turmaId = (int)($_POST['turma_id'] ?? 0);
                    $alunoId = (int)($_POST['aluno_id'] ?? 0);

                    $turmaObj = Turma::buscarPorId($turmaId, $instituicaoId);
                    if (!$turmaObj || $alunoId <= 0) {
                        $_SESSION['flash_error'] = "Selecione uma turma e aluno válidos da sua instituição.";
                    } else {
                        Turma::adicionarAluno($turmaId, $alunoId);
                        $_SESSION['flash_success'] = "Aluno vinculado à turma com sucesso!";
                        $_SESSION['open_membros_modal'] = $turmaId;
                    }
                } elseif ($action === 'remover_aluno') {
                    $turmaId = (int)($_POST['turma_id'] ?? 0);
                    $alunoId = (int)($_POST['aluno_id'] ?? 0);

                    $turmaObj = Turma::buscarPorId($turmaId, $instituicaoId);
                    if (!$turmaObj || $alunoId <= 0) {
                        $_SESSION['flash_error'] = "Dados de desvinculação de aluno inválidos.";
                    } else {
                        Turma::removerAluno($turmaId, $alunoId);
                        $_SESSION['flash_success'] = "Aluno desvinculado da turma!";
                        $_SESSION['open_membros_modal'] = $turmaId;
                    }
                }
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }

            header('Location: ' . url('admin/turmas'));
            exit;
        }

        // Leitura de Dados para Exibição na View
        $busca = trim($_GET['busca'] ?? '');
        $turmas = Turma::listarTodas($instituicaoId, $busca);
        $professoresDisponiveis = Turma::listarProfessoresDisponiveis($instituicaoId);
        $alunosDisponiveis      = Turma::listarAlunosDisponiveis($instituicaoId);

        // Dados do Usuário Logado para o Header/Sidebar
        $userName   = $usuarioLogado['nome'] ?? 'Administrador';
        $userRole   = 'Administrador';
        $userSector = 'Diretoria';

        $nomePartes   = explode(' ', trim($userName));
        $firstChar    = !empty($nomePartes[0]) ? mb_strtoupper(mb_substr($nomePartes[0], 0, 1)) : 'A';
        $secondChar   = (isset($nomePartes[1]) && !empty($nomePartes[1])) ? mb_strtoupper(mb_substr($nomePartes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $pageTitle = "Turmas — ÂNCORA";

        require __DIR__ . '/../views/admin/turmas.php';
    }
}
