<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Model PasswordReset (Gestão de Códigos de Recuperação de Senha)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Gerenciar as solicitações de redefinição de senha com alta segurança.
 * 
 * REGRAS DE SEGURANÇA APLICADAS:
 * 1. GERAÇÃO: Código numérico imprevisível de 6 dígitos gerado via random_int().
 * 2. ARMAZENAMENTO HASH: O código NUNCA é salvo em texto puro; apenas seu hash password_hash().
 * 3. VALIDADE: Código expira em exatamente 10 minutos (10 MINUTE no MySQL).
 * 4. TENTATIVAS: Máximo de 5 tentativas por código. Excedidas 5 tentativas, o código é invalidado.
 * 5. REENVIO COOLDOWN: Intervalo obrigatório de 60 segundos entre solicitações.
 * 6. INVALIDAÇÃO: Ao gerar novo código, solicitações anteriores ativas são invalidadas (used_at = NOW()).
 */

require_once __DIR__ . '/../../config/database.php';

class PasswordReset {

    /**
     * Gera e registra um novo código de recuperação de 6 dígitos para o usuário.
     * Invalidando solicitações anteriores.
     *
     * @param int $usuarioId ID do usuário no banco de dados.
     * @param string $email E-mail do usuário.
     * @return string Retorna o código numérico em texto puro para ser enviado via e-mail.
     * @throws Exception
     */
    public static function criarCodigo(int $usuarioId, string $email): string {
        $db = getDatabaseConnection();

        try {
            $db->beginTransaction();

            // 1. Invalida todas as solicitações ativas anteriores do mesmo usuário
            $stmtInvalidate = $db->prepare("
                UPDATE password_resets 
                SET used_at = NOW(), updated_at = NOW() 
                WHERE usuario_id = :usuario_id AND used_at IS NULL
            ");
            $stmtInvalidate->execute([':usuario_id' => $usuarioId]);

            // 2. Gera um código de 6 dígitos numericamente seguro utilizando random_int()
            // random_int() fornece números aleatórios adequados para cenários de alta segurança.
            $codigoNum = (string)random_int(100000, 999999);

            // 3. Criptografa o código com password_hash() antes de salvar no banco
            $codigoHash = password_hash($codigoNum, PASSWORD_DEFAULT);

            // 4. Insere o novo registro com expiração definida para 10 minutos a partir do momento atual
            $stmtInsert = $db->prepare("
                INSERT INTO password_resets (usuario_id, email, codigo_hash, expires_at, attempts, used_at, created_at, updated_at)
                VALUES (:usuario_id, :email, :codigo_hash, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0, NULL, NOW(), NOW())
            ");
            $stmtInsert->execute([
                ':usuario_id'  => $usuarioId,
                ':email'       => trim($email),
                ':codigo_hash' => $codigoHash
            ]);

            $db->commit();

            return $codigoNum;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Busca o registro de recuperação ativo mais recente para um e-mail.
     */
    public static function buscarRegistroAtivo(string $email) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT * FROM password_resets 
            WHERE email = :email AND used_at IS NULL AND expires_at > NOW() AND attempts < 5 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':email' => trim($email)]);
        return $stmt->fetch();
    }

    /**
     * Valida o código digitado pelo usuário incrementando o número de tentativas.
     *
     * @param string $email E-mail do usuário.
     * @param string $codigoDigitado Código de 6 dígitos recebido.
     * @return array Retorna o status e a mensagem da validação.
     */
    public static function validarCodigo(string $email, string $codigoDigitado): array {
        $db = getDatabaseConnection();

        // Busca o registro mais recente que ainda não foi marcado como usado e checa a expiração via MySQL
        $stmt = $db->prepare("
            SELECT *, (expires_at < NOW()) as is_expired 
            FROM password_resets 
            WHERE email = :email AND used_at IS NULL 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':email' => trim($email)]);
        $record = $stmt->fetch();

        if (!$record) {
            return [
                'status'  => 'not_found',
                'message' => 'Nenhuma solicitação de recuperação encontrada.'
            ];
        }

        // Verifica se o código já expirou (Validade de 10 minutos no MySQL)
        if (!empty($record['is_expired'])) {
            return [
                'status'  => 'expired',
                'message' => 'Este código expirou. Solicite um novo código.'
            ];
        }

        // Verifica limite de 5 tentativas incorretas
        if ((int)$record['attempts'] >= 5) {
            self::marcarComoUsado((int)$record['id']);
            return [
                'status'  => 'max_attempts',
                'message' => 'Este código não é mais válido. Solicite um novo código.'
            ];
        }

        // Incrementa o número de tentativas no banco
        $novasTentativas = (int)$record['attempts'] + 1;
        $stmtUpdateAttempts = $db->prepare("UPDATE password_resets SET attempts = :attempts, updated_at = NOW() WHERE id = :id");
        $stmtUpdateAttempts->execute([
            ':attempts' => $novasTentativas,
            ':id'       => $record['id']
        ]);

        // Valida o código digitado contra o hash BCRYPT armazenado via password_verify()
        if (!password_verify(trim($codigoDigitado), $record['codigo_hash'])) {
            if ($novasTentativas >= 5) {
                self::marcarComoUsado((int)$record['id']);
                return [
                    'status'  => 'max_attempts',
                    'message' => 'Este código não é mais válido. Solicite um novo código.'
                ];
            }
            return [
                'status'  => 'invalid',
                'message' => 'Código de verificação incorreto. Tente novamente.'
            ];
        }

        return [
            'status' => 'success',
            'record' => $record
        ];
    }

    /**
     * Marca uma solicitação como utilizada ou desativada.
     */
    public static function marcarComoUsado(int $resetId): bool {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW(), updated_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $resetId]);
    }

    /**
     * Invalida todas as solicitações ativas de um determinado usuário.
     */
    public static function invalidarTodosDoUsuario(int $usuarioId): bool {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("UPDATE password_resets SET used_at = NOW(), updated_at = NOW() WHERE usuario_id = :usuario_id AND used_at IS NULL");
        return $stmt->execute([':usuario_id' => $usuarioId]);
    }

    /**
     * Retorna a quantidade de segundos restantes para o limite de reenvio de 60 segundos.
     */
    public static function tempoParaReenvio(string $email): int {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as elapsed 
            FROM password_resets 
            WHERE email = :email 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':email' => trim($email)]);
        $row = $stmt->fetch();

        if (!$row || $row['elapsed'] === null) {
            return 0;
        }

        $elapsed = (int)$row['elapsed'];
        $cooldown = 60; // 60 segundos de intervalo entre reenvios

        if ($elapsed < $cooldown) {
            return $cooldown - $elapsed;
        }

        return 0;
    }
}
