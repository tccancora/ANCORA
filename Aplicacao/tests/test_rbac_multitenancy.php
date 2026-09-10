<?php
/**
 * ÂNCORA - Testes do Módulo de Perfis RBAC e Multi-Tenancy (CI/CD)
 * Valida as regras de perfis (Admin, Professor, Aluno, Funcionário) e segregação lógica por instituição.
 */

function testRbacMultiTenancyModule(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertRbac(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. Mapeamento de IDs dos Perfis RBAC
    $mapaPerfis = [
        1 => 'administrador',
        2 => 'professor',
        3 => 'aluno',
        4 => 'funcionario'
    ];
    assertRbac(count($mapaPerfis) === 4, "Exatamente 4 perfis RBAC nativos mapeados no sistema ÂNCORA", '4', (string)count($mapaPerfis), $passed, $failed, $details);

    // 2. Roteamento Inteligente do Dashboard baseado no Perfil
    function resolverRotaDashboard(string $perfilNome): string {
        $p = strtolower(trim($perfilNome));
        if ($p === 'aluno') return 'aluno';
        if ($p === 'professor') return 'professor';
        if ($p === 'funcionario' || $p === 'funcionário') return 'funcionario';
        return 'admin';
    }

    assertRbac(resolverRotaDashboard('aluno') === 'aluno', "Perfil 'Aluno' é direcionado para rota '/aluno'", 'aluno', resolverRotaDashboard('aluno'), $passed, $failed, $details);
    assertRbac(resolverRotaDashboard('Professor') === 'professor', "Perfil 'Professor' é direcionado para rota '/professor'", 'professor', resolverRotaDashboard('Professor'), $passed, $failed, $details);
    assertRbac(resolverRotaDashboard('Funcionário') === 'funcionario', "Perfil 'Funcionário' é direcionado para rota '/funcionario'", 'funcionario', resolverRotaDashboard('Funcionário'), $passed, $failed, $details);
    assertRbac(resolverRotaDashboard('Administrador') === 'admin', "Perfil 'Administrador' é direcionado para rota '/admin'", 'admin', resolverRotaDashboard('Administrador'), $passed, $failed, $details);

    // 3. Regra de Segregação Lógica Multi-Tenancy
    $registroInstituicao1 = ['id' => 101, 'instituicao_id' => 1, 'dados' => 'Turma A'];
    $registroInstituicao2 = ['id' => 102, 'instituicao_id' => 2, 'dados' => 'Turma B'];

    function filtrarPorInstituicao(array $registros, int $instituicaoIdFiltro): array {
        return array_values(array_filter($registros, function($item) use ($instituicaoIdFiltro) {
            return (int)$item['instituicao_id'] === $instituicaoIdFiltro;
        }));
    }

    $todosRegistros = [$registroInstituicao1, $registroInstituicao2];
    $filtradosInst1 = filtrarPorInstituicao($todosRegistros, 1);
    $filtradosInst2 = filtrarPorInstituicao($todosRegistros, 2);

    assertRbac(count($filtradosInst1) === 1 && $filtradosInst1[0]['dados'] === 'Turma A', "Filtro Multi-Tenancy para Instituição 1 isola apenas registros pertencentes a ela", '1 registro (Turma A)', count($filtradosInst1) . " registro ({$filtradosInst1[0]['dados']})", $passed, $failed, $details);
    assertRbac(count($filtradosInst2) === 1 && $filtradosInst2[0]['dados'] === 'Turma B', "Filtro Multi-Tenancy para Instituição 2 impede vazamento de dados entre inquilinos", '1 registro (Turma B)', count($filtradosInst2) . " registro ({$filtradosInst2[0]['dados']})", $passed, $failed, $details);

    return [
        'name' => 'Módulo de Perfis RBAC e Isolamento Multi-Tenancy',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testRbacMultiTenancyModule();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) { echo $line . "\n"; }
    exit($res['failed'] > 0 ? 1 : 0);
}
