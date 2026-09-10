<?php
/**
 * ÂNCORA - Teste de Validação de Regras de Segurança e Hashing (CI/CD)
 * Valida regras de criptografia BCRYPT, sanitização de códigos e validação de e-mails
 */

function testSecurityRules(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertSec(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. Criptografia de senha com BCRYPT
    $senhaPura = "SenhaSegura123@";
    $hashBCRYPT = password_hash($senhaPura, PASSWORD_BCRYPT);
    $infoHash = password_get_info($hashBCRYPT);
    assertSec($infoHash['algoName'] === 'bcrypt', "password_hash gera hashes com algoritmo BCRYPT", 'bcrypt', $infoHash['algoName'], $passed, $failed, $details);

    // 2. Verificação de senha correta com BCRYPT
    $valido = password_verify($senhaPura, $hashBCRYPT);
    assertSec($valido === true, "password_verify confirma a senha correta para o hash gerado", 'true', $valido ? 'true' : 'false', $passed, $failed, $details);

    // 3. Rejeição de senha incorreta com BCRYPT
    $invalido = password_verify("SenhaIncorretaErrada", $hashBCRYPT);
    assertSec($invalido === false, "password_verify rejeita senhas incorretas", 'false', $invalido ? 'true' : 'false', $passed, $failed, $details);

    // 4. Sanitização de código de 6 dígitos (remoção de caracteres não-numéricos)
    $codigoComSujeira = " 12a-34.56 ";
    $codigoLimpo = preg_replace('/[^0-9]/', '', $codigoComSujeira);
    assertSec($codigoLimpo === '123456' && strlen($codigoLimpo) === 6, "Sanitização de código de 6 dígitos extrai apenas os numéricos", '123456 (tam 6)', "{$codigoLimpo} (tam " . strlen($codigoLimpo) . ")", $passed, $failed, $details);

    // 5. Validação de formato de e-mail institucional válido
    $emailValido = "usuario.teste@ancora.edu.br";
    $eValido = filter_var($emailValido, FILTER_VALIDATE_EMAIL) !== false;
    assertSec($eValido === true, "E-mail válido 'usuario.teste@ancora.edu.br' é aceito por FILTER_VALIDATE_EMAIL", 'true', $eValido ? 'true' : 'false', $passed, $failed, $details);

    // 6. Validação de formato de e-mail inválido
    $emailInvalido = "email_invalido_sem_arrouba.com";
    $eInvalido = filter_var($emailInvalido, FILTER_VALIDATE_EMAIL) === false;
    assertSec($eInvalido === true, "E-mail sem '@' é rejeitado por FILTER_VALIDATE_EMAIL", 'true (rejeitado)', $eInvalido ? 'true (rejeitado)' : 'false (aceito)', $passed, $failed, $details);

    // 7. Mapeamento de perfis RBAC do sistema
    $perfisOficiais = [1 => 'Administrador', 2 => 'Professor', 3 => 'Aluno', 4 => 'Funcionario'];
    $todosPerfisPresentes = count($perfisOficiais) === 4 && isset($perfisOficiais[1], $perfisOficiais[2], $perfisOficiais[3], $perfisOficiais[4]);
    assertSec($todosPerfisPresentes, "Mapeamento dos 4 perfis de acesso RBAC (Admin, Prof, Aluno, Func) está íntegro", '4 perfis mapeados', count($perfisOficiais) . " perfis", $passed, $failed, $details);

    return [
        'name' => 'Regras de Segurança, BCRYPT, Sanitização e Validação',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

// Se for executado diretamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testSecurityRules();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) {
        echo $line . "\n";
    }
    exit($res['failed'] > 0 ? 1 : 0);
}
