<?php
/**
 * Model responsável pela gestão da tabela 'instituicoes' (Multi-Tenancy)
 */

require_once __DIR__ . '/../../config/database.php';

class Instituicao {

    /**
     * Cria uma nova instituição no banco de dados
     */
    public static function criar(string $nome, ?string $cnpj = null, ?string $email = null, ?string $telefone = null, ?string $cidade = null, ?string $estado = null): int {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("
            INSERT INTO instituicoes (nome, cnpj, email, telefone, cidade, estado, created_at, updated_at) 
            VALUES (:nome, :cnpj, :email, :telefone, :cidade, :estado, NOW(), NOW())
        ");
        $stmt->execute([
            ':nome'     => trim($nome),
            ':cnpj'     => $cnpj ? trim($cnpj) : null,
            ':email'    => $email ? trim($email) : null,
            ':telefone' => $telefone ? trim($telefone) : null,
            ':cidade'   => $cidade ? trim($cidade) : null,
            ':estado'   => $estado ? trim($estado) : null,
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Busca uma instituição pelo ID
     */
    public static function buscarPorId(int $id) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT * FROM instituicoes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Busca uma instituição pelo CNPJ
     */
    public static function buscarPorCnpj(string $cnpj) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT * FROM instituicoes WHERE cnpj = :cnpj LIMIT 1");
        $stmt->execute([':cnpj' => trim($cnpj)]);
        return $stmt->fetch();
    }

    /**
     * Gera o código de identificação institucional formatado (ex: 'ANC-0001')
     *
     * @param int $id ID da instituição
     * @return string Código institucional
     */
    public static function formatarCodigo(int $id): string {
        return 'ANC-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
    }
}

