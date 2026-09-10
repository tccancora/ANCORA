<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Configuração e Conexão com o Banco de Dados (PDO)
 */

// Credenciais de Conexão (Padrão XAMPP)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'ancora');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtém uma conexão ativa com o banco de dados via PDO.
 *
 * @return PDO
 * @throws Exception
 */
function getDatabaseConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            @$pdo->exec("SET time_zone = '-03:00'");
        } catch (PDOException $e) {
            throw new Exception("Erro de Conexão com o Banco de Dados: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    return $pdo;
}
