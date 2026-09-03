<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller da Central e Serviço de Notificações em Tempo Real (/notificacoes)
 */

require_once __DIR__ . '/../services/NotificationService.php';

class NotificacoesController {

    private function requireAuth(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'Não autenticado']);
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
     * Renderiza a View Central de Notificações ou processa submissões POST tradicionais.
     */
    public function index() {
        $user = $this->requireAuth();
        $usuarioId = (int) $user['id'];
        $instituicaoId = (int) $user['instituicao_id'];

        $flashSuccess = null;
        $flashError = null;

        // Processamento de Ações POST Tradicionais
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'criar_aviso') {
                $titulo = trim($_POST['titulo'] ?? '');
                $mensagem = trim($_POST['mensagem'] ?? '');
                $tipo = trim($_POST['tipo'] ?? 'Informativo');
                $destinatario = trim($_POST['destinatario'] ?? 'Todos');

                if (empty($titulo) || empty($mensagem)) {
                    $flashError = 'Título e mensagem são obrigatórios.';
                } else {
                    $sucesso = NotificationService::enviarAvisoManual(
                        $instituicaoId,
                        $usuarioId,
                        $destinatario,
                        $tipo,
                        $titulo,
                        $mensagem
                    );

                    if ($sucesso) {
                        $flashSuccess = 'Aviso enviado com sucesso aos destinatários!';
                    } else {
                        $flashError = 'Ocorreu um erro ou nenhum usuário ativo foi encontrado para receber o aviso.';
                    }
                }
            } elseif ($action === 'marcar_lida') {
                $notificacaoId = (int) ($_POST['id'] ?? 0);
                if ($notificacaoId > 0) {
                    NotificationService::marcarComoLida($notificacaoId, $usuarioId);
                    $flashSuccess = 'Notificação marcada como lida.';
                }
            } elseif ($action === 'marcar_todas_lidas') {
                NotificationService::marcarTodasComoLidas($usuarioId);
                $flashSuccess = 'Todas as notificações foram marcadas como lidas.';
            } elseif ($action === 'excluir') {
                $notificacaoId = (int) ($_POST['id'] ?? 0);
                if ($notificacaoId > 0) {
                    NotificationService::excluirNotificacao($notificacaoId, $usuarioId);
                    $flashSuccess = 'Notificação excluída com sucesso.';
                }
            }
        }

        // Dados para a View
        $notificacoes = NotificationService::obterNotificacoes($usuarioId, 100);
        $unreadCount = NotificationService::obterNaoLidasCount($usuarioId);

        // Dados de perfil e avatar
        $userName = $user['nome'] ?? 'Usuário';
        $userRole = $user['perfil_nome'] ?? 'Perfil';
        $perfilSlug = mb_strtolower($userRole);
        
        $partes = explode(' ', trim($userName));
        $userInitials = !empty($partes[0]) ? mb_strtoupper(mb_substr($partes[0], 0, 1)) : 'U';
        if (isset($partes[1]) && !empty($partes[1])) {
            $userInitials .= mb_strtoupper(mb_substr($partes[1], 0, 1));
        }

        $pageTitle = 'Notificações — ÂNCORA';
        require_once __DIR__ . '/../views/notificacoes/index.php';
    }

    /**
     * Endpoint de Polling AJAX Otimizado (Retorna JSON com delta de notificações e contagem)
     */
    public function poll() {
        $user = $this->requireAuth();
        $usuarioId = (int) $user['id'];

        header('Content-Type: application/json; charset=utf-8');

        $lastId = (int) ($_GET['last_id'] ?? 0);
        $novas = NotificationService::obterNovasNotificacoes($usuarioId, $lastId);
        $unreadCount = NotificationService::obterNaoLidasCount($usuarioId);

        // Identifica a maior ID retornada
        $maxId = $lastId;
        foreach ($novas as $item) {
            if ((int)$item['id'] > $maxId) {
                $maxId = (int)$item['id'];
            }
        }

        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount,
            'new_items' => $novas,
            'max_id' => $maxId,
            'timestamp' => date('c')
        ]);
        exit;
    }

    /**
     * Endpoint AJAX para marcar leitura/exclusão sem recarregar a página
     */
    public function action() {
        $user = $this->requireAuth();
        $usuarioId = (int) $user['id'];

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método inválido']);
            exit;
        }

        $action = $_POST['action'] ?? '';
        $notificacaoId = (int) ($_POST['id'] ?? 0);

        if ($action === 'marcar_lida' && $notificacaoId > 0) {
            $ok = NotificationService::marcarComoLida($notificacaoId, $usuarioId);
            $unreadCount = NotificationService::obterNaoLidasCount($usuarioId);
            echo json_encode(['success' => $ok, 'unread_count' => $unreadCount]);
            exit;
        }

        if ($action === 'marcar_todas_lidas') {
            $ok = NotificationService::marcarTodasComoLidas($usuarioId);
            echo json_encode(['success' => $ok, 'unread_count' => 0]);
            exit;
        }

        if ($action === 'excluir' && $notificacaoId > 0) {
            $ok = NotificationService::excluirNotificacao($notificacaoId, $usuarioId);
            $unreadCount = NotificationService::obterNaoLidasCount($usuarioId);
            echo json_encode(['success' => $ok, 'unread_count' => $unreadCount]);
            exit;
        }

        echo json_encode(['success' => false, 'error' => 'Ação não reconhecida']);
        exit;
    }
}
