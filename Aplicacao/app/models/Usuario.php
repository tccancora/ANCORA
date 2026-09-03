<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Model Usuario (Com Suporte a Multi-Tenancy / Isolamento de Instituições)
 */

require_once __DIR__ . '/../../config/database.php';

class Usuario {
    /**
     * Busca um usuário pelo e-mail trazendo os dados da sua instituição
     */
    public static function buscarPorEmail(string $email) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT u.*, i.nome as instituicao_nome FROM usuarios u LEFT JOIN instituicoes i ON u.instituicao_id = i.id WHERE u.email = :email LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        return $stmt->fetch();
    }

    /**
     * Cria um novo usuário vinculado a uma instituição específica
     */
    public static function criar(string $nome, string $email, string $senha, int $perfilId, int $instituicaoId = 1) {
        $db = getDatabaseConnection();

        try {
            $db->beginTransaction();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO usuarios (nome, email, senha, perfil_id, instituicao_id, primeiro_acesso, created_at, updated_at)
                VALUES (:nome, :email, :senha, :perfil_id, :instituicao_id, 1, NOW(), NOW())
            ");

            $stmt->execute([
                ':nome'           => trim($nome),
                ':email'          => trim($email),
                ':senha'          => $senhaHash,
                ':perfil_id'      => $perfilId,
                ':instituicao_id' => $instituicaoId,
            ]);

            $newUserId = $db->lastInsertId();

            $db->commit();

            return $newUserId;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Busca um usuário pelo ID trazendo o nome do perfil e garantindo escopo da instituição (se informado)
     */
    public static function buscarPorId(int $id, ?int $instituicaoId = null) {
        $db = getDatabaseConnection();
        
        $sql = "
            SELECT u.*, p.nome as perfil_nome 
            FROM usuarios u 
            JOIN perfis p ON u.perfil_id = p.id 
            WHERE u.id = :id
        ";

        $params = [':id' => $id];

        if ($instituicaoId !== null) {
            $sql .= " AND u.instituicao_id = :instituicao_id";
            $params[':instituicao_id'] = $instituicaoId;
        }

        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Lista todos os usuários de uma instituição específica
     */
    public static function listarTodosComPerfil(?int $instituicaoId = null) {
        $db = getDatabaseConnection();

        if ($instituicaoId !== null) {
            $stmt = $db->prepare("
                SELECT u.id, u.nome, u.email, u.perfil_id, u.instituicao_id, u.status, u.primeiro_acesso, u.created_at, p.nome as perfil_nome 
                FROM usuarios u 
                JOIN perfis p ON u.perfil_id = p.id 
                WHERE u.instituicao_id = :instituicao_id
                ORDER BY u.id DESC
            ");
            $stmt->execute([':instituicao_id' => $instituicaoId]);
        } else {
            $stmt = $db->query("
                SELECT u.id, u.nome, u.email, u.perfil_id, u.instituicao_id, u.status, u.primeiro_acesso, u.created_at, p.nome as perfil_nome 
                FROM usuarios u 
                JOIN perfis p ON u.perfil_id = p.id 
                ORDER BY u.id DESC
            ");
        }

        return $stmt->fetchAll();
    }

    /**
     * Retorna a contagem de usuários por perfil para uma instituição específica
     */
    public static function contarPorPerfil(?int $instituicaoId = null) {
        $db = getDatabaseConnection();

        if ($instituicaoId !== null) {
            $stmt = $db->prepare("
                SELECT p.nome as perfil_nome, COUNT(u.id) as total 
                FROM perfis p 
                LEFT JOIN usuarios u ON u.perfil_id = p.id AND u.instituicao_id = :instituicao_id
                GROUP BY p.id, p.nome 
                ORDER BY p.id ASC
            ");
            $stmt->execute([':instituicao_id' => $instituicaoId]);
        } else {
            $stmt = $db->query("
                SELECT p.nome as perfil_nome, COUNT(u.id) as total 
                FROM perfis p 
                LEFT JOIN usuarios u ON u.perfil_id = p.id 
                GROUP BY p.id, p.nome 
                ORDER BY p.id ASC
            ");
        }

        $counts = [
            'Administrador' => 0,
            'Professor'     => 0,
            'Funcionario'   => 0,
            'Aluno'         => 0,
        ];

        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $nome = trim($row['perfil_nome'] ?? '');
            $nomeLower = mb_strtolower($nome);
            if ($nomeLower === 'funcionário' || $nomeLower === 'funcionario') {
                $counts['Funcionario'] = (int)$row['total'];
            } else {
                foreach ($counts as $key => $val) {
                    if (mb_strtolower($key) === $nomeLower) {
                        $counts[$key] = (int)$row['total'];
                    }
                }
            }
        }
        return $counts;
    }

    /**
     * Atualiza dados de um usuário dentro da mesma instituição
     */
    public static function atualizar(int $id, string $nome, string $email, int $perfilId, string $status = 'ativo', ?int $instituicaoId = null) {
        $db = getDatabaseConnection();

        $sql = "
            UPDATE usuarios 
            SET nome = :nome, email = :email, perfil_id = :perfil_id, status = :status, updated_at = NOW() 
            WHERE id = :id
        ";

        $params = [
            ':id'        => $id,
            ':nome'      => trim($nome),
            ':email'     => trim($email),
            ':perfil_id' => $perfilId,
            ':status'    => $status,
        ];

        if ($instituicaoId !== null) {
            $sql .= " AND instituicao_id = :instituicao_id";
            $params[':instituicao_id'] = $instituicaoId;
        }

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Altera o status (ativo/inativo) garantindo escopo de instituição
     */
    public static function alterarStatus(int $id, string $status, ?int $instituicaoId = null) {
        $db = getDatabaseConnection();

        $sql = "
            UPDATE usuarios 
            SET status = :status, updated_at = NOW() 
            WHERE id = :id
        ";

        $params = [
            ':id'     => $id,
            ':status' => $status === 'inativo' ? 'inativo' : 'ativo',
        ];

        if ($instituicaoId !== null) {
            $sql .= " AND instituicao_id = :instituicao_id";
            $params[':instituicao_id'] = $instituicaoId;
        }

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Retorna a quantidade de administradores ativos em uma instituição
     */
    public static function contarAdministradoresAtivos(?int $instituicaoId = null) {
        $db = getDatabaseConnection();

        if ($instituicaoId !== null) {
            $stmt = $db->prepare("
                SELECT COUNT(u.id) as total 
                FROM usuarios u 
                JOIN perfis p ON u.perfil_id = p.id 
                WHERE LOWER(p.nome) = 'administrador' AND u.status = 'ativo' AND u.instituicao_id = :instituicao_id
            ");
            $stmt->execute([':instituicao_id' => $instituicaoId]);
        } else {
            $stmt = $db->query("
                SELECT COUNT(u.id) as total 
                FROM usuarios u 
                JOIN perfis p ON u.perfil_id = p.id 
                WHERE LOWER(p.nome) = 'administrador' AND u.status = 'ativo'
            ");
        }

        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Conclui o primeiro acesso: substitui a senha inicial pelo novo hash BCRYPT e define primeiro_acesso = 0
     */
    public static function concluirPrimeiroAcesso(int $id, string $novaSenha) {
        $db = getDatabaseConnection();
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET senha = :senha, primeiro_acesso = 0, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'    => $id,
            ':senha' => $senhaHash,
        ]);
    }

    /**
     * Atualiza o nome do próprio usuário autenticado
     *
     * @param int $id ID do usuário
     * @param string $nome Novo nome completo
     * @return bool True se atualizado com sucesso
     */
    public static function atualizarNome(int $id, string $nome): bool {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET nome = :nome, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'   => $id,
            ':nome' => trim($nome),
        ]);
    }

    /**
     * Atualiza o e-mail do próprio usuário com validação de duplicidade
     *
     * @param int $id ID do usuário
     * @param string $email Novo endereço de e-mail
     * @return bool True se atualizado com sucesso
     * @throws Exception Se o e-mail já estiver em uso por outro usuário
     */
    public static function atualizarEmailProprio(int $id, string $email): bool {
        $db = getDatabaseConnection();
        $emailNormalizado = trim($email);

        // Verifica se outro usuário já possui este e-mail
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1");
        $stmtCheck->execute([
            ':email' => $emailNormalizado,
            ':id'    => $id
        ]);
        if ($stmtCheck->fetch()) {
            throw new Exception("O endereço de e-mail informado já está em uso por outra conta.");
        }

        $stmt = $db->prepare("
            UPDATE usuarios 
            SET email = :email, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'    => $id,
            ':email' => $emailNormalizado,
        ]);
    }

    /**
     * Valida a senha atual digitada contra o hash armazenado no banco
     *
     * @param int $id ID do usuário
     * @param string $senhaDigitada Senha atual informada pelo usuário
     * @return bool True se a senha confere
     */
    public static function verificarSenhaAtual(int $id, string $senhaDigitada): bool {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT senha FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row || empty($row['senha'])) {
            return false;
        }

        return password_verify($senhaDigitada, $row['senha']);
    }

    /**
     * Redefine a senha do usuário a partir do fluxo de Recuperação de Senha por e-mail ou Configurações.
     * Criptografa a nova senha com BCRYPT / PASSWORD_DEFAULT.
     *
     * @param int $usuarioId ID do usuário no banco.
     * @param string $novaSenha Nova senha pessoal informada pelo usuário.
     * @return bool True em caso de sucesso.
     */
    public static function redefinirSenha(int $usuarioId, string $novaSenha): bool {
        $db = getDatabaseConnection();
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET senha = :senha, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'    => $usuarioId,
            ':senha' => $senhaHash,
        ]);
    }
}

