<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Model Perfil
 */

require_once __DIR__ . '/../../config/database.php';

class Perfil {

    /**
     * Busca um perfil pelo seu nome (ex: 'Administrador', 'Professor', 'Aluno', 'Funcionário')
     *
     * @param string $nome
     * @return array|false
     */
    public static function buscarPorNome(string $nome) {
        $db = getDatabaseConnection();
        $nomeNormalizado = trim($nome);

        // Tratamento para variações de acentuação em Funcionário/Funcionario
        if (mb_strtolower($nomeNormalizado) === 'funcionário') {
            $nomeNormalizado = 'Funcionario';
        }

        $stmt = $db->prepare("
            SELECT id, nome, descricao FROM perfis 
            WHERE LOWER(nome) = LOWER(:nome)
            LIMIT 1
        ");
        $stmt->execute([':nome' => $nomeNormalizado]);
        return $stmt->fetch();
    }

    /**
     * Busca um perfil pelo seu ID
     *
     * @param int $id
     * @return array|false
     */
    public static function buscarPorId(int $id) {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT id, nome, descricao FROM perfis WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Retorna todos os perfis cadastrados no sistema
     *
     * @return array
     */
    public static function listarTodos() {
        $db = getDatabaseConnection();
        $stmt = $db->query("SELECT id, nome, descricao FROM perfis ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}
