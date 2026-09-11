<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller do Módulo de Tarefas (/tarefas)
 * 
 * CONTROLADOR PRINCIPAL RESPONSÁVEL POR:
 * 1. Gestão de Tarefas para Professores e Administradores (Criação, Edição, Exclusão, Entregas, Correção, Devolução).
 * 2. Visualização, Questionários e Submissão para Alunos.
 * 3. Upload e Download Seguro de Materiais de Apoio e Arquivos de Resposta.
 * 4. Validação Rigorosa de Privacidade, Multi-Tenancy e Prazos no Servidor.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Tarefa.php';
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';
require_once __DIR__ . '/../services/NotificationService.php';

class TarefasController {

    private function requireAuth(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'Sessão expirada. Faça login novamente.']);
                exit;
            }
            header('Location: ' . url('login'));
            exit;
        }

        return $_SESSION['user'];
    }

    private function isAjaxRequest(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Tela Principal de Tarefas (listagem com filtros, busca e criação)
     */
    public function index() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];
        $perfilId      = (int) ($user['perfil_id'] ?? 0);
        $perfilNome    = mb_strtolower(trim($user['perfil_nome'] ?? ''));

        // Parâmetros de Filtro e Busca
        $busca        = trim($_GET['busca'] ?? '');
        $statusFiltro = trim($_GET['status'] ?? 'todos');
        $turmaFiltro  = trim($_GET['turma'] ?? 'todas');
        $ordenacao    = trim($_GET['ordem'] ?? 'recentes');

        $isAluno = ($perfilId === 3 || $perfilNome === 'aluno');
        $isDocenteOuAdmin = ($perfilId === 1 || $perfilId === 2 || $perfilNome === 'administrador' || $perfilNome === 'professor');

        if ($isDocenteOuAdmin) {
            $tarefas = Tarefa::listarCriadasPorUsuario($userId, $instituicaoId, $busca, $statusFiltro, $turmaFiltro, $ordenacao);
            $turmasDisponiveis = Turma::listarTodas($instituicaoId);
            
            // Buscar alunos ativos da instituição para permitir seleção individual
            $db = getDatabaseConnection();
            $stmtA = $db->prepare("SELECT id, nome, email FROM usuarios WHERE instituicao_id = :inst AND perfil_id = 3 AND status = 'ativo' ORDER BY nome ASC");
            $stmtA->execute([':inst' => $instituicaoId]);
            $alunosDisponiveis = $stmtA->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $tarefas = Tarefa::listarDestinadasAoAluno($userId, $instituicaoId, $busca, $statusFiltro, $turmaFiltro, $ordenacao);
            $turmasDisponiveis = [];
            $alunosDisponiveis = [];
        }

        // Dados do usuário para a Sidebar e Topbar
        $userName      = $user['nome'] ?? 'Usuário';
        $userRole      = $user['perfil_nome'] ?? 'Perfil';
        $userRoleTitle = $user['perfil_nome'] ?? 'Perfil';
        $perfilSlug    = mb_strtolower(trim($user['perfil_nome'] ?? ''));
        $userSector    = ($perfilId === 1) ? 'Diretoria' : (($perfilId === 2) ? 'Corpo Docente' : 'Corpo Discente');

        if ($perfilSlug === 'aluno') {
            $inicioUrl = url('aluno');
        } elseif ($perfilSlug === 'professor') {
            $inicioUrl = url('professor');
        } elseif ($perfilSlug === 'funcionario' || $perfilSlug === 'funcionário') {
            $inicioUrl = url('funcionario');
        } else {
            $inicioUrl = url('admin');
        }

        $partes = explode(' ', trim($userName));
        $firstChar = !empty($partes[0]) ? mb_strtoupper(mb_substr($partes[0], 0, 1)) : 'U';
        $secondChar = (isset($partes[1]) && !empty($partes[1])) ? mb_strtoupper(mb_substr($partes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $pageTitle = "Tarefas — ÂNCORA";

        require __DIR__ . '/../views/tarefas/index.php';
    }

    /**
     * Processa a criação de uma nova tarefa
     */
    public function criar() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];
        $perfilId      = (int) ($user['perfil_id'] ?? 0);
        $perfilNome    = mb_strtolower(trim($user['perfil_nome'] ?? ''));

        // Apenas Administrador e Professor podem criar tarefas
        if ($perfilId !== 1 && $perfilId !== 2 && $perfilNome !== 'administrador' && $perfilNome !== 'professor') {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'Permissão negada. Apenas professores e administradores podem criar tarefas.']);
                exit;
            }
            $_SESSION['flash_error'] = 'Permissão negada para criação de tarefas.';
            header('Location: ' . url('tarefas'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tarefas'));
            exit;
        }

        try {
            $titulo            = trim($_POST['titulo'] ?? '');
            $descricao         = trim($_POST['descricao'] ?? '');
            $disciplina        = trim($_POST['disciplina'] ?? '');
            $tipoAtividade     = trim($_POST['tipo_atividade'] ?? 'tradicional');
            $permiteAnexoAluno = !empty($_POST['permite_anexo_aluno']) ? 1 : 0;
            $dataEntrega       = trim($_POST['prazo_data'] ?? '');
            $horaEntrega       = trim($_POST['prazo_hora'] ?? '23:59');

            if (empty($titulo)) {
                throw new Exception('O título da tarefa é obrigatório.');
            }

            if (empty($dataEntrega)) {
                throw new Exception('O prazo de entrega é obrigatório.');
            }

            $prazoEntrega = date('Y-m-d H:i:s', strtotime("{$dataEntrega} {$horaEntrega}"));

            // Processar Destinatários (Turmas e Alunos)
            $turmasIds = isset($_POST['turmas']) ? (array)$_POST['turmas'] : [];
            $alunosIds = isset($_POST['alunos']) ? (array)$_POST['alunos'] : [];

            if (empty($turmasIds) && empty($alunosIds)) {
                throw new Exception('Selecione pelo menos uma turma ou aluno destinatário.');
            }

            $destinatarios = [
                'turmas' => array_map('intval', $turmasIds),
                'alunos' => array_map('intval', $alunosIds)
            ];

            // Processar Upload de Materiais de Apoio
            $materiais = [];
            if (!empty($_FILES['materiais']['name'][0])) {
                $materiais = $this->processarUploadMateriais($_FILES['materiais']);
            }

            // Processar Questões do Questionário (se aplicável)
            $questoes = [];
            if ($tipoAtividade === 'questionario' || $tipoAtividade === 'hibrida') {
                $questoesJson = $_POST['questoes_json'] ?? '';
                if (!empty($questoesJson)) {
                    $questoes = json_decode($questoesJson, true);
                    if (!is_array($questoes)) {
                        $questoes = [];
                    }
                }
            }

            $dadosTarefa = [
                'instituicao_id'      => $instituicaoId,
                'created_by'          => $userId,
                'titulo'              => $titulo,
                'descricao'           => $descricao,
                'disciplina'          => $disciplina,
                'tipo_atividade'      => $tipoAtividade,
                'permite_anexo_aluno' => $permiteAnexoAluno,
                'disponivel_em'       => date('Y-m-d H:i:s'),
                'prazo_entrega'       => $prazoEntrega,
                'status'              => 'publicada'
            ];

            $tarefaId = Tarefa::criarTarefa($dadosTarefa, $destinatarios, $materiais, $questoes);

            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'tarefa_id' => $tarefaId, 'message' => 'Tarefa criada e publicada com sucesso!']);
                exit;
            }

            $_SESSION['flash_success'] = 'Tarefa criada e publicada com sucesso!';
            header('Location: ' . url('tarefas'));
            exit;

        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . url('tarefas'));
            exit;
        }
    }

    /**
     * Processa a edição de uma tarefa (apenas pelo criador original)
     */
    public function editar() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tarefas'));
            exit;
        }

        try {
            $tarefaId = (int) ($_POST['tarefa_id'] ?? 0);
            if (!Tarefa::validarPropriedade($tarefaId, $userId, $instituicaoId)) {
                throw new Exception('Acesso negado. Você só pode editar tarefas que você próprio criou.');
            }

            $titulo            = trim($_POST['titulo'] ?? '');
            $descricao         = trim($_POST['descricao'] ?? '');
            $disciplina        = trim($_POST['disciplina'] ?? '');
            $tipoAtividade     = trim($_POST['tipo_atividade'] ?? 'tradicional');
            $permiteAnexoAluno = !empty($_POST['permite_anexo_aluno']) ? 1 : 0;
            $dataEntrega       = trim($_POST['prazo_data'] ?? '');
            $horaEntrega       = trim($_POST['prazo_hora'] ?? '23:59');

            if (empty($titulo)) {
                throw new Exception('O título da tarefa é obrigatório.');
            }

            $prazoEntrega = date('Y-m-d H:i:s', strtotime("{$dataEntrega} {$horaEntrega}"));

            $turmasIds = isset($_POST['turmas']) ? (array)$_POST['turmas'] : [];
            $alunosIds = isset($_POST['alunos']) ? (array)$_POST['alunos'] : [];

            if (empty($turmasIds) && empty($alunosIds)) {
                throw new Exception('Selecione pelo menos uma turma ou aluno destinatário.');
            }

            $destinatarios = [
                'turmas' => array_map('intval', $turmasIds),
                'alunos' => array_map('intval', $alunosIds)
            ];

            // Novos materiais
            $novosMateriais = [];
            if (!empty($_FILES['materiais']['name'][0])) {
                $novosMateriais = $this->processarUploadMateriais($_FILES['materiais']);
            }

            // Materiais a remover
            $removerMateriais = isset($_POST['remover_materiais']) ? (array)$_POST['remover_materiais'] : [];

            // Questões atualizadas
            $questoes = [];
            if (!empty($_POST['questoes_json'])) {
                $questoes = json_decode($_POST['questoes_json'], true);
                if (!is_array($questoes)) $questoes = [];
            }

            $dadosTarefa = [
                'titulo'              => $titulo,
                'descricao'           => $descricao,
                'disciplina'          => $disciplina,
                'tipo_atividade'      => $tipoAtividade,
                'permite_anexo_aluno' => $permiteAnexoAluno,
                'prazo_entrega'       => $prazoEntrega,
                'status'              => 'publicada'
            ];

            Tarefa::atualizarTarefa($tarefaId, $userId, $instituicaoId, $dadosTarefa, $destinatarios, $novosMateriais, $removerMateriais, $questoes);

            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Tarefa atualizada com sucesso!']);
                exit;
            }

            $_SESSION['flash_success'] = 'Tarefa atualizada com sucesso!';
            header('Location: ' . url('tarefas'));
            exit;

        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: ' . url('tarefas'));
            exit;
        }
    }

    /**
     * Processa a exclusão de uma tarefa (apenas pelo criador original)
     */
    public function excluir() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tarefas'));
            exit;
        }

        $tarefaId = (int) ($_POST['tarefa_id'] ?? 0);

        if (Tarefa::excluirTarefa($tarefaId, $userId, $instituicaoId)) {
            $_SESSION['flash_success'] = 'Tarefa excluída com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao excluir tarefa ou permissão negada.';
        }

        header('Location: ' . url('tarefas'));
        exit;
    }

    /**
     * Visualização de Detalhes da Tarefa pelo Aluno (fazer atividade, responder questionário, ver nota)
     */
    public function detalhes() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];
        $perfilId      = (int) ($user['perfil_id'] ?? 0);
        $perfilNome    = mb_strtolower(trim($user['perfil_nome'] ?? ''));

        $tarefaId = (int) ($_GET['id'] ?? 0);

        // Se for professor ou administrador, redirecionar para a tela de entregas
        if ($perfilId === 1 || $perfilId === 2 || $perfilNome === 'administrador' || $perfilNome === 'professor') {
            if (Tarefa::validarPropriedade($tarefaId, $userId, $instituicaoId)) {
                header('Location: ' . url('tarefas/entregas', ['id' => $tarefaId]));
                exit;
            } else {
                $_SESSION['flash_error'] = 'Acesso negado. Você só pode gerenciar tarefas criadas por você.';
                header('Location: ' . url('tarefas'));
                exit;
            }
        }

        // Validar que o aluno tem acesso à tarefa
        if (!Tarefa::validarAcessoAluno($tarefaId, $userId, $instituicaoId)) {
            $_SESSION['flash_error'] = 'Acesso negado. Esta atividade não está destinada a você.';
            header('Location: ' . url('tarefas'));
            exit;
        }

        $tarefa = Tarefa::buscarPorId($tarefaId, $instituicaoId);
        if (!$tarefa) {
            $_SESSION['flash_error'] = 'Tarefa não encontrada.';
            header('Location: ' . url('tarefas'));
            exit;
        }

        $entrega = Tarefa::obterEntregaDoAluno($tarefaId, $userId);

        // Dados de perfil e cabeçalho
        $userName      = $user['nome'] ?? 'Aluno';
        $userRole      = $user['perfil_nome'] ?? 'Aluno';
        $userRoleTitle = $user['perfil_nome'] ?? 'Aluno';
        $perfilSlug    = mb_strtolower(trim($user['perfil_nome'] ?? 'aluno'));
        $userSector    = 'Corpo Discente';
        $inicioUrl     = url('aluno');

        $partes = explode(' ', trim($userName));
        $firstChar = !empty($partes[0]) ? mb_strtoupper(mb_substr($partes[0], 0, 1)) : 'A';
        $secondChar = (isset($partes[1]) && !empty($partes[1])) ? mb_strtoupper(mb_substr($partes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $pageTitle = htmlspecialchars($tarefa['titulo']) . " — ÂNCORA";

        require __DIR__ . '/../views/tarefas/detalhes.php';
    }

    /**
     * Submissão da atividade pelo Aluno (com validação estrita de prazo no backend)
     */
    public function submeter() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tarefas'));
            exit;
        }

        $tarefaId = (int) ($_POST['tarefa_id'] ?? 0);

        try {
            $respostas = isset($_POST['respostas']) ? (array)$_POST['respostas'] : [];

            // Upload de arquivos da entrega
            $arquivos = [];
            if (!empty($_FILES['arquivos_entrega']['name'][0])) {
                $arquivos = $this->processarUploadEntrega($_FILES['arquivos_entrega']);
            }

            // Verificar se a tarefa possui questionário
            $questoes = Tarefa::obterQuestoes($tarefaId);
            $temQuestionario = !empty($questoes);

            // REGRA: Quando tiver questionário, anexar arquivo é 100% opcional.
            // Quando NÃO tiver questionário, é obrigatório anexar ao menos um arquivo se ainda não houver entrega anterior com arquivos.
            if (!$temQuestionario && empty($arquivos)) {
                $entregaExistente = Tarefa::obterEntregaDoAluno($tarefaId, $userId);
                if (empty($entregaExistente['arquivos'])) {
                    $_SESSION['flash_error'] = 'Esta tarefa não possui questionário. É obrigatório anexar ao menos um arquivo de entrega.';
                    header('Location: ' . url('tarefas/detalhes', ['id' => $tarefaId]));
                    exit;
                }
            }

            $resultado = Tarefa::salvarEntregaAluno($tarefaId, $userId, $instituicaoId, $respostas, $arquivos);

            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($resultado);
                exit;
            }

            if (!empty($resultado['success'])) {
                $_SESSION['flash_success'] = 'Atividade enviada com sucesso!';
            } else {
                $_SESSION['flash_error'] = $resultado['error'] ?? 'Erro ao enviar atividade.';
            }

        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['flash_error'] = 'Erro ao enviar atividade: ' . $e->getMessage();
        }

        header('Location: ' . url('tarefas/detalhes', ['id' => $tarefaId]));
        exit;
    }

    /**
     * Painel de Gestão de Entregas da Tarefa para Professor e Administrador
     */
    public function entregas() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];

        $tarefaId = (int) ($_GET['id'] ?? 0);

        if (!Tarefa::validarPropriedade($tarefaId, $userId, $instituicaoId)) {
            $_SESSION['flash_error'] = 'Acesso negado. Você só pode gerenciar as entregas das tarefas criadas por você.';
            header('Location: ' . url('tarefas'));
            exit;
        }

        $tarefa = Tarefa::buscarPorId($tarefaId, $instituicaoId);
        if (!$tarefa) {
            $_SESSION['flash_error'] = 'Tarefa não encontrada.';
            header('Location: ' . url('tarefas'));
            exit;
        }

        $resumoEntregas = Tarefa::obterResumoEntregas($tarefaId, $instituicaoId);
        $listaAlunos    = Tarefa::listarAlunosEEntregasDaTarefa($tarefaId, $instituicaoId);

        // Dados do usuário logado
        $userName      = $user['nome'] ?? 'Professor';
        $userRole      = $user['perfil_nome'] ?? 'Professor';
        $userRoleTitle = $user['perfil_nome'] ?? 'Professor';
        $perfilSlug    = mb_strtolower(trim($user['perfil_nome'] ?? 'professor'));
        $userSector    = ($user['perfil_id'] == 1) ? 'Diretoria' : 'Corpo Docente';
        $inicioUrl     = ($user['perfil_id'] == 1) ? url('admin') : url('professor');

        $partes = explode(' ', trim($userName));
        $firstChar = !empty($partes[0]) ? mb_strtoupper(mb_substr($partes[0], 0, 1)) : 'P';
        $secondChar = (isset($partes[1]) && !empty($partes[1])) ? mb_strtoupper(mb_substr($partes[1], 0, 1)) : '';
        $userInitials = $firstChar . $secondChar;

        $pageTitle = "Entregas: " . htmlspecialchars($tarefa['titulo']) . " — ÂNCORA";

        require __DIR__ . '/../views/tarefas/entregas.php';
    }

    /**
     * Processa a correção, atribuição de nota e devolução da atividade
     */
    public function corrigir() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tarefas'));
            exit;
        }

        $tarefaId           = (int) ($_POST['tarefa_id'] ?? 0);
        $entregaId          = (int) ($_POST['entrega_id'] ?? 0);
        $action             = trim($_POST['action'] ?? 'devolver'); // 'corrigir' ou 'devolver'
        $notaFinal          = isset($_POST['nota_final']) && $_POST['nota_final'] !== '' ? (float)str_replace(',', '.', $_POST['nota_final']) : null;
        $feedbackGeral      = trim($_POST['feedback_geral'] ?? '');
        $notasQuestoes      = isset($_POST['notas_questoes']) ? (array)$_POST['notas_questoes'] : [];
        $comentariosQuestoes= isset($_POST['comentarios_questoes']) ? (array)$_POST['comentarios_questoes'] : [];

        $devolver = ($action === 'devolver');

        $sucesso = Tarefa::corrigirEDevolverEntrega(
            $entregaId,
            $tarefaId,
            $userId,
            $instituicaoId,
            $notaFinal,
            $feedbackGeral,
            $notasQuestoes,
            $comentariosQuestoes,
            $devolver
        );

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $sucesso]);
            exit;
        }

        if ($sucesso) {
            $_SESSION['flash_success'] = $devolver ? 'Atividade corrigida e devolvida ao aluno com sucesso!' : 'Correção salva com sucesso!';
        } else {
            $_SESSION['flash_error'] = 'Erro ao salvar correção ou permissão negada.';
        }

        header('Location: ' . url('tarefas/entregas', ['id' => $tarefaId]));
        exit;
    }

    /**
     * Download seguro de material de apoio
     */
    public function downloadMaterial() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];
        $perfilId      = (int) ($user['perfil_id'] ?? 0);

        $materialId = (int) ($_GET['id'] ?? 0);

        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT tm.*, t.instituicao_id, t.created_by, t.id as tarefa_id 
            FROM tarefa_materiais tm
            JOIN tarefas t ON tm.tarefa_id = t.id
            WHERE tm.id = :id
        ");
        $stmt->execute([':id' => $materialId]);
        $mat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mat || (int)$mat['instituicao_id'] !== $instituicaoId) {
            http_response_code(403);
            die('Acesso negado.');
        }

        // Se aluno, validar se é destinatário da tarefa
        if ($perfilId === 3) {
            if (!Tarefa::validarAcessoAluno((int)$mat['tarefa_id'], $userId, $instituicaoId)) {
                http_response_code(403);
                die('Acesso negado ao arquivo.');
            }
        }

        $caminhoCompleto = __DIR__ . '/../../' . $mat['caminho_arquivo'];
        if (!file_exists($caminhoCompleto)) {
            http_response_code(404);
            die('Arquivo não encontrado no servidor.');
        }

        $mime = $mat['mime_type'] ?: 'application/octet-stream';
        $nome = $mat['nome_original'];

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($nome) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($caminhoCompleto));
        readfile($caminhoCompleto);
        exit;
    }

    /**
     * Download seguro de arquivo anexado na entrega pelo aluno
     */
    public function downloadEntrega() {
        $user = $this->requireAuth();
        $userId        = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];
        $perfilId      = (int) ($user['perfil_id'] ?? 0);

        $arquivoId = (int) ($_GET['id'] ?? 0);

        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT tea.*, te.aluno_id, t.instituicao_id, t.created_by 
            FROM tarefa_entrega_arquivos tea
            JOIN tarefa_entregas te ON tea.entrega_id = te.id
            JOIN tarefas t ON te.tarefa_id = t.id
            WHERE tea.id = :id
        ");
        $stmt->execute([':id' => $arquivoId]);
        $arq = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$arq || (int)$arq['instituicao_id'] !== $instituicaoId) {
            http_response_code(403);
            die('Acesso negado.');
        }

        // Permissão: Apenas o próprio aluno que entregou OU o criador da tarefa
        $isProprioAluno = ((int)$arq['aluno_id'] === $userId);
        $isCriadorTarefa = ((int)$arq['created_by'] === $userId);

        if (!$isProprioAluno && !$isCriadorTarefa) {
            http_response_code(403);
            die('Acesso negado ao arquivo de entrega.');
        }

        $caminhoCompleto = __DIR__ . '/../../' . $arq['caminho_arquivo'];
        if (!file_exists($caminhoCompleto)) {
            http_response_code(404);
            die('Arquivo não encontrado no servidor.');
        }

        $mime = $arq['mime_type'] ?: 'application/octet-stream';
        $nome = $arq['nome_original'];

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($nome) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($caminhoCompleto));
        readfile($caminhoCompleto);
        exit;
    }

    /* -------------------------------------------------------------
     * MÉTODOS DE UPLOAD SEGURO DE ARQUIVOS
     * ------------------------------------------------------------- */

    private function processarUploadMateriais(array $files): array {
        $uploadDir = __DIR__ . '/../../storage/uploads/tarefas/materiais/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $materiais = [];
        $extensoesPermitidas = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg', 'mp4'];
        $maxTamanhoBytes = 30 * 1024 * 1024; // 30MB

        $totalArquivos = count($files['name']);
        for ($i = 0; $i < $totalArquivos; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $nomeOriginal = $files['name'][$i];
            $tamanho      = (int) $files['size'][$i];
            $tmpPath      = $files['tmp_name'][$i];
            $extensao     = mb_strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensao, $extensoesPermitidas)) {
                throw new Exception("Tipo de arquivo não permitido no material: .{$extensao}");
            }

            if ($tamanho > $maxTamanhoBytes) {
                throw new Exception("O arquivo {$nomeOriginal} excede o limite de 30MB.");
            }

            // Gerar nome único e seguro no disco
            $nomeSeguro = 'mat_' . bin2hex(random_bytes(16)) . '.' . $extensao;
            $destino = $uploadDir . $nomeSeguro;

            if (move_uploaded_file($tmpPath, $destino)) {
                $materiais[] = [
                    'nome_original'   => $nomeOriginal,
                    'caminho_arquivo' => 'storage/uploads/tarefas/materiais/' . $nomeSeguro,
                    'tamanho_bytes'   => $tamanho,
                    'mime_type'       => $files['type'][$i] ?? 'application/octet-stream'
                ];
            }
        }

        return $materiais;
    }

    private function processarUploadEntrega(array $files): array {
        $uploadDir = __DIR__ . '/../../storage/uploads/tarefas/entregas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $arquivos = [];
        $extensoesPermitidas = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg', 'c', 'cpp', 'py', 'java', 'js', 'html', 'css', 'sql'];
        $maxTamanhoBytes = 30 * 1024 * 1024; // 30MB

        $totalArquivos = count($files['name']);
        for ($i = 0; $i < $totalArquivos; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $nomeOriginal = $files['name'][$i];
            $tamanho      = (int) $files['size'][$i];
            $tmpPath      = $files['tmp_name'][$i];
            $extensao     = mb_strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensao, $extensoesPermitidas)) {
                throw new Exception("Extensão de arquivo não permitida na entrega: .{$extensao}");
            }

            if ($tamanho > $maxTamanhoBytes) {
                throw new Exception("O arquivo {$nomeOriginal} excede o limite de 30MB.");
            }

            $nomeSeguro = 'entrega_' . bin2hex(random_bytes(16)) . '.' . $extensao;
            $destino = $uploadDir . $nomeSeguro;

            if (move_uploaded_file($tmpPath, $destino)) {
                $arquivos[] = [
                    'nome_original'   => $nomeOriginal,
                    'caminho_arquivo' => 'storage/uploads/tarefas/entregas/' . $nomeSeguro,
                    'tamanho_bytes'   => $tamanho,
                    'mime_type'       => $files['type'][$i] ?? 'application/octet-stream'
                ];
            }
        }

        return $arquivos;
    }
}
