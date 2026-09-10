<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Configuração Geral da Aplicação e Carregador de Variáveis de Ambiente (.env)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Carregar as configurações globais e ler de forma segura o arquivo .env
 * garantindo que credenciais sensíveis (como senhas SMTP ou banco de dados)
 * NUNCA fiquem expostas diretamente no código-fonte PHP.
 */

define('APP_NAME', 'ÂNCORA');
define('APP_VERSION', '1.0.0');

/**
 * Função utilitária para ler e carregar o arquivo .env na memória do PHP
 */
function carregarEnv($envPath) {
    if (!file_exists($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignora comentários e linhas vazias
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Executa o carregamento do arquivo .env localizado na raiz do projeto
carregarEnv(__DIR__ . '/../.env');

// Configura o fuso horário padrão para o Brasil (America/Sao_Paulo)
date_default_timezone_set('America/Sao_Paulo');

// Identifica se a execução é pela raiz ou pela pasta public/
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (strpos($scriptName, '/public/') !== false) {
    define('ASSET_URL', '');
} else {
    define('ASSET_URL', 'public/');
}

/**
 * Helper para geração de URLs universais do sistema MVC
 */
function url($path = '', $params = []) {
    $cleanPath = ltrim($path, '/');
    if (empty($cleanPath)) {
        $base = 'index.php';
    } else {
        // Se o caminho já contiver '?', separa a rota dos parâmetros GET
        if (strpos($cleanPath, '?') !== false) {
            list($routePart, $queryPart) = explode('?', $cleanPath, 2);
            $base = 'index.php?route=' . $routePart . '&' . $queryPart;
        } else {
            $base = 'index.php?route=' . $cleanPath;
        }
    }

    // Se houver parâmetros passados em array, anexa-os com segurança
    if (!empty($params) && is_array($params)) {
        $separator = (strpos($base, '?') !== false) ? '&' : '?';
        $base .= $separator . http_build_query($params);
    }

    return $base;
}

/**
 * Helper para geração de caminhos de assets estáticos (CSS, JS, Imagens)
 */
function asset($path = '') {
    $cleanPath = ltrim($path, '/');
    return (defined('ASSET_URL') ? ASSET_URL : 'public/') . $cleanPath;
}
