<?php
/**
 * ÂNCORA - Runner Central de Suítes de Testes Automatizados (CI/CD)
 * Executa 100% das suítes de validação de regras de negócio de TODOS os módulos do sistema.
 * 
 * RETORNO CLI:
 * Exit Code 0 = Todos os testes passaram (SUCCESS)
 * Exit Code 1 = Pelo menos um teste falhou (FAILURE - Interrompe a esteira do GitHub Actions)
 */

require_once __DIR__ . '/test_url_helpers.php';
require_once __DIR__ . '/test_instituicao_format.php';
require_once __DIR__ . '/test_security_rules.php';
require_once __DIR__ . '/test_auth_rules.php';
require_once __DIR__ . '/test_rbac_multitenancy.php';
require_once __DIR__ . '/test_usuarios_turmas.php';
require_once __DIR__ . '/test_configuracoes_notificacoes.php';
require_once __DIR__ . '/test_tarefas_module.php';

echo "====================================================================\n";
echo " SUÍTE COMPLETA DE TESTES AUTOMATIZADOS DO ÂNCORA (CI/CD)\n";
echo "====================================================================\n\n";

$suites = [
    testUrlHelpers(),
    testInstituicaoFormat(),
    testSecurityRules(),
    testAuthRulesModule(),
    testRbacMultiTenancyModule(),
    testUsuariosTurmasModule(),
    testConfiguracoesNotificacoesModule(),
    testTarefasModule()
];

$totalPassed = 0;
$totalFailed = 0;

foreach ($suites as $suite) {
    echo "=== SUÍTE: {$suite['name']} ===\n";
    foreach ($suite['details'] as $detail) {
        echo $detail . "\n";
    }
    $totalPassed += $suite['passed'];
    $totalFailed += $suite['failed'];
    echo "\n";
}

$totalTests = $totalPassed + $totalFailed;

echo "====================================================================\n";
echo " RESUMO FINAL DA EXECUÇÃO DOS TESTES DO SISTEMA ÂNCORA:\n";
echo " - Total de Suítes Executadas : " . count($suites) . "\n";
echo " - Total de Validações        : {$totalTests}\n";
echo " - Sucessos (PASS)             : {$totalPassed}\n";
echo " - Falhas (FAIL)               : {$totalFailed}\n";
echo "====================================================================\n\n";

if ($totalFailed > 0) {
    echo "❌ STATUS: FALHA (CI FAILED). Pelo menos um teste falhou. Retornando Exit Code 1.\n";
    exit(1);
} else {
    echo "✅ STATUS: SUCESSO (CI PASSED). 100% das validações foram aprovadas com sucesso! Retornando Exit Code 0.\n";
    exit(0);
}
