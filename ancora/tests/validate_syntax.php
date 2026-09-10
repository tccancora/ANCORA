<?php
/**
 * ÂNCORA - Validador de Sintaxe PHP (PHP Lint Automated Checker - CI/CD)
 * Verifica a integridade sintática de 100% dos arquivos .php do projeto
 */

echo "====================================================================\n";
echo " VALIDAÇÃO DE SINTAXE PHP (PHP LINT) — PROJETO ÂNCORA\n";
echo "====================================================================\n\n";

$rootDir = realpath(__DIR__ . '/..');

// Busca recursiva de todos os arquivos .php do projeto
$directory = new RecursiveDirectoryIterator($rootDir);
$iterator = new RecursiveIteratorIterator($directory);
$phpFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        
        // Ignora pastas de cache ou terceiros se existirem
        if (strpos($path, 'vendor') !== false || strpos($path, '.git') !== false) {
            continue;
        }
        
        $phpFiles[] = $path;
    }
}

sort($phpFiles);

$passed = 0;
$failed = 0;
$errors = [];

$phpBinary = PHP_BINARY ?: 'php';

foreach ($phpFiles as $file) {
    $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file);
    $cmd = sprintf('%s -l %s 2>&1', escapeshellarg($phpBinary), escapeshellarg($file));
    $output = [];
    $returnCode = 0;
    exec($cmd, $output, $returnCode);

    $outputStr = implode("\n", $output);

    if ($returnCode === 0 && strpos($outputStr, 'No syntax errors detected') !== false) {
        $passed++;
        echo " [PASS] Sintaxe OK: {$relativePath}\n";
    } else {
        $failed++;
        $errors[] = [
            'file' => $relativePath,
            'output' => $outputStr
        ];
        echo " [FAIL] ERRO DE SINTAXE: {$relativePath}\n";
    }
}

echo "\n====================================================================\n";
echo " RESULTADO DA VALIDAÇÃO DE SINTAXE:\n";
echo " - Total de Arquivos Analisados: " . count($phpFiles) . "\n";
echo " - Aprovados (Sintaxe Válida)  : {$passed}\n";
echo " - Reprovados (Erros de Sintaxe): {$failed}\n";
echo "====================================================================\n\n";

if ($failed > 0) {
    echo "DETALHES DOS ERROS ENCONTRADOS:\n";
    foreach ($errors as $err) {
        echo "Arquivo: {$err['file']}\n";
        echo "Mensagem: {$err['output']}\n";
        echo "--------------------------------------------------------------------\n";
    }
    exit(1);
} else {
    echo "[SUCESSO] 100% dos arquivos PHP do projeto ÂNCORA possuem sintaxe perfeita!\n";
    exit(0);
}
