<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Serviço de Gerenciamento e Emissão de Notificações em Tempo Real
 * 
 * Compatível com ambientes PHP + MySQL (InfinityFree / XAMPP)
 */

require_once __DIR__ . '/../../config/database.php';

class NotificationService {
    private static ?PDO $db = null;

    private static function getDb(): PDO {
        if (self::$db === null) {
            if (function_exists('getDatabaseConnection')) {
                self::$db = getDatabaseConnection();
            } else {
                require_once __DIR__ . '/../../config/database.php';
                self::$db = getDatabaseConnection();
            }
        }
        return self::$db;
    }

    /**
     * Envia aviso manual distribuindo para o grupo de destinatários da instituição.
     */
    public static function enviarAvisoManual(
        int $instituicaoId,
        ?int $remetenteId,
        string $destinatarioGrupo,
        string $tipo,
        string $titulo,
        string $mensagem
    ): bool {
        $db = self::getDb();

        // Mapeamento de grupos de destinatários para perfil_id (1=Admin, 2=Prof, 3=Aluno, 4=Func)
        $perfilIdTarget = null;
        $grupoClean = mb_strtolower(trim($destinatarioGrupo));
        
        if ($grupoClean === 'alunos' || $grupoClean === 'aluno') {
            $perfilIdTarget = 3;
        } elseif ($grupoClean === 'professores' || $grupoClean === 'professor') {
            $perfilIdTarget = 2;
        } elseif ($grupoClean === 'funcionários' || $grupoClean === 'funcionarios' || $grupoClean === 'funcionario') {
            $perfilIdTarget = 4;
        } elseif ($grupoClean === 'administradores' || $grupoClean === 'administrador') {
            $perfilIdTarget = 1;
        }

        // Buscar todos os usuários alvos ativos na mesma instituição
        if ($perfilIdTarget !== null) {
            $stmtUsers = $db->prepare("
                SELECT id FROM usuarios 
                WHERE instituicao_id = :inst_id AND perfil_id = :perfil_id AND status = 'ativo'
            ");
            $stmtUsers->execute([
                ':inst_id' => $instituicaoId,
                ':perfil_id' => $perfilIdTarget
            ]);
        } else { // 'Todos'
            $stmtUsers = $db->prepare("
                SELECT id FROM usuarios 
                WHERE instituicao_id = :inst_id AND status = 'ativo'
            ");
            $stmtUsers->execute([':inst_id' => $instituicaoId]);
        }

        $destinatarios = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);
        if (empty($destinatarios)) {
            return false;
        }

        // Inserção em lote (Bulk Insert) para máximo desempenho no MySQL
        $db->beginTransaction();
        try {
            $stmtInsert = $db->prepare("
                INSERT INTO notificacoes 
                (instituicao_id, usuario_id, remetente_id, tipo, categoria, titulo, mensagem, lida, created_at)
                VALUES 
                (:inst_id, :user_id, :remetente_id, :tipo, 'aviso', :titulo, :mensagem, 0, NOW())
            ");

            foreach ($destinatarios as $uid) {
                $stmtInsert->execute([
                    ':inst_id' => $instituicaoId,
                    ':user_id' => $uid,
                    ':remetente_id' => $remetenteId,
                    ':tipo' => $tipo,
                    ':titulo' => $titulo,
                    ':mensagem' => $mensagem
                ]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Erro no NotificationService::enviarAvisoManual: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria uma notificação automática individual para um usuário específico.
     */
    public static function criarParaUsuario(
        int $instituicaoId,
        int $usuarioId,
        string $tipo,
        string $titulo,
        string $mensagem,
        string $categoria = 'geral',
        ?string $link = null,
        ?int $remetenteId = null
    ): bool {
        $db = self::getDb();
        try {
            $stmt = $db->prepare("
                INSERT INTO notificacoes 
                (instituicao_id, usuario_id, remetente_id, tipo, categoria, titulo, mensagem, link, lida, created_at)
                VALUES 
                (:inst_id, :user_id, :remetente_id, :tipo, :categoria, :titulo, :mensagem, :link, 0, NOW())
            ");
            return $stmt->execute([
                ':inst_id' => $instituicaoId,
                ':user_id' => $usuarioId,
                ':remetente_id' => $remetenteId,
                ':tipo' => $tipo,
                ':categoria' => $categoria,
                ':titulo' => $titulo,
                ':mensagem' => $mensagem,
                ':link' => $link
            ]);
        } catch (Exception $e) {
            error_log('Erro no NotificationService::criarParaUsuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna a quantidade de notificações não lidas de um usuário.
     */
    public static function obterNaoLidasCount(int $usuarioId): int {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notificacoes WHERE usuario_id = :uid AND lida = 0");
        $stmt->execute([':uid' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna o histórico de notificações de um usuário com suporte a paginação/limite.
     */
    public static function obterNotificacoes(int $usuarioId, int $limite = 50): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT id, instituicao_id, usuario_id, remetente_id, tipo, categoria, titulo, mensagem, link, lida,
                   DATE_FORMAT(created_at, '%d %b %Y às %H:%i') as data_formatada,
                   created_at
            FROM notificacoes 
            WHERE usuario_id = :uid 
            ORDER BY created_at DESC, id DESC 
            LIMIT :limite
        ");
        $stmt->bindValue(':uid', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna apenas notificações com ID superior a lastId (para polling leve).
     */
    public static function obterNovasNotificacoes(int $usuarioId, int $lastId = 0): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT id, instituicao_id, usuario_id, remetente_id, tipo, categoria, titulo, mensagem, link, lida,
                   DATE_FORMAT(created_at, '%d %b %Y às %H:%i') as data_formatada,
                   created_at
            FROM notificacoes 
            WHERE usuario_id = :uid AND id > :last_id
            ORDER BY id ASC
        ");
        $stmt->execute([
            ':uid' => $usuarioId,
            ':last_id' => $lastId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca uma notificação específica como lida.
     */
    public static function marcarComoLida(int $notificacaoId, int $usuarioId): bool {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :uid");
        return $stmt->execute([':id' => $notificacaoId, ':uid' => $usuarioId]);
    }

    /**
     * Marca todas as notificações do usuário como lidas.
     */
    public static function marcarTodasComoLidas(int $usuarioId): bool {
        $db = self::getDb();
        $stmt = $db->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = :uid AND lida = 0");
        return $stmt->execute([':uid' => $usuarioId]);
    }

    /**
     * Exclui uma notificação do usuário.
     */
    public static function excluirNotificacao(int $notificacaoId, int $usuarioId): bool {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM notificacoes WHERE id = :id AND usuario_id = :uid");
        return $stmt->execute([':id' => $notificacaoId, ':uid' => $usuarioId]);
    }
}
