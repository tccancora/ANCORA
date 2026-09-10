<?php
/**
 * ÂNCORA - Teste de Validação de Roteamento e URLs (CI/CD)
 * Valida o comportamento dos helpers url() e asset() definidos em config/app.php
 */

require_once __DIR__ . '/../config/app.php';

function testUrlHelpers(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertCondition(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. Rota raiz sem parâmetros
    $res1 = url('');
    assertCondition($res1 === 'index.php', "url('') gera a rota inicial da aplicação", 'index.php', $res1, $passed, $failed, $details);

    // 2. Rota simples de login
    $res2 = url('login');
    assertCondition($res2 === 'index.php?route=login', "url('login') gera 'index.php?route=login'", 'index.php?route=login', $res2, $passed, $failed, $details);

    // 3. Rota com parâmetros GET via array
    $res3 = url('tarefas/detalhes', ['id' => 5, 'tab' => 'entregas']);
    $expected3 = 'index.php?route=tarefas/detalhes&id=5&tab=entregas';
    assertCondition($res3 === $expected3, "url('tarefas/detalhes', ['id' => 5, 'tab' => 'entregas']) anexa parâmetros GET", $expected3, $res3, $passed, $failed, $details);

    // 4. Rota que já contém query string
    $res4 = url('admin?filtro=ativo', ['perfil' => 1]);
    $expected4 = 'index.php?route=admin&filtro=ativo&perfil=1';
    assertCondition($res4 === $expected4, "url('admin?filtro=ativo', ['perfil' => 1]) trata corretamente a query string", $expected4, $res4, $passed, $failed, $details);

    // 5. Helper asset() para recursos estáticos
    $res5 = asset('css/admin.css');
    assertCondition(strpos($res5, 'css/admin.css') !== false, "asset('css/admin.css') gera caminho de recurso estático", 'contendo css/admin.css', $res5, $passed, $failed, $details);

    return [
        'name' => 'Helper de Roteamento e URLs (url / asset)',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

// Se for executado diretamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testUrlHelpers();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) {
        echo $line . "\n";
    }
    exit($res['failed'] > 0 ? 1 : 0);
}
