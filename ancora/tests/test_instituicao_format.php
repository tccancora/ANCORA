<?php
/**
 * ÂNCORA - Teste de Validação de Formatação Institucional (CI/CD)
 * Valida a regra de negócio de formatação de códigos de instituição (Instituicao::formatarCodigo)
 */

require_once __DIR__ . '/../app/models/Instituicao.php';

function testInstituicaoFormat(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertFormat(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. ID de um único dígito (ex: 1 -> ANC-0001)
    $res1 = Instituicao::formatarCodigo(1);
    assertFormat($res1 === 'ANC-0001', "Instituicao::formatarCodigo(1) retorna 'ANC-0001'", 'ANC-0001', $res1, $passed, $failed, $details);

    // 2. ID de dois dígitos (ex: 42 -> ANC-0042)
    $res2 = Instituicao::formatarCodigo(42);
    assertFormat($res2 === 'ANC-0042', "Instituicao::formatarCodigo(42) retorna 'ANC-0042'", 'ANC-0042', $res2, $passed, $failed, $details);

    // 3. ID de três dígitos (ex: 789 -> ANC-0789)
    $res3 = Instituicao::formatarCodigo(789);
    assertFormat($res3 === 'ANC-0789', "Instituicao::formatarCodigo(789) retorna 'ANC-0789'", 'ANC-0789', $res3, $passed, $failed, $details);

    // 4. ID de quatro dígitos (ex: 1005 -> ANC-1005)
    $res4 = Instituicao::formatarCodigo(1005);
    assertFormat($res4 === 'ANC-1005', "Instituicao::formatarCodigo(1005) retorna 'ANC-1005'", 'ANC-1005', $res4, $passed, $failed, $details);

    // 5. ID maior de quatro dígitos (ex: 12345 -> ANC-12345)
    $res5 = Instituicao::formatarCodigo(12345);
    assertFormat($res5 === 'ANC-12345', "Instituicao::formatarCodigo(12345) mantém integridade de IDs com >4 dígitos", 'ANC-12345', $res5, $passed, $failed, $details);

    return [
        'name' => 'Formatação de Código Institucional (Instituicao::formatarCodigo)',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

// Se for executado diretamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testInstituicaoFormat();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) {
        echo $line . "\n";
    }
    exit($res['failed'] > 0 ? 1 : 0);
}
