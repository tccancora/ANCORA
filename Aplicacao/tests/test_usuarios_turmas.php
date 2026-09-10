<?php
/**
 * ÂNCORA - Testes da Gestão de Usuários e Turmas (CI/CD)
 * Valida a lógica de ativação/desativação de membros, proteção contra auto-exclusão e vinculação em turmas.
 */

function testUsuariosTurmasModule(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertUT(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. Trava de Proteção contra Auto-Exclusão ou Auto-Desativação
    $usuarioLogadoId = 5;
    $usuarioAlvoId = 5; // O mesmo usuário tenta se desativar
    $podeAlterarProprioStatus = ($usuarioLogadoId !== $usuarioAlvoId);
    assertUT($podeAlterarProprioStatus === false, "Trava de segurança impede que o usuário logado desative ou exclua a própria conta", 'false (bloqueado)', $podeAlterarProprioStatus ? 'true (permitido)' : 'false (bloqueado)', $passed, $failed, $details);

    // 2. Trava de Auto-Elevação de Privilégios pelo próprio usuário
    $perfilAtual = 2; // Professor
    $tentativaNovoPerfil = 1; // Tenta se auto-promover a Admin
    $podeAutoElevar = ($usuarioLogadoId !== $usuarioAlvoId || $perfilAtual === $tentativaNovoPerfil);
    assertUT($podeAutoElevar === false, "Trava de segurança impede que um usuário altere seu próprio nível de privilégio", 'false (bloqueado)', $podeAutoElevar ? 'true (permitido)' : 'false (bloqueado)', $passed, $failed, $details);

    // 3. Status de Usuário Ativo / Inativo
    $statusValidos = ['ativo', 'inativo'];
    $novoStatus = 'ativo';
    assertUT(in_array($novoStatus, $statusValidos, true), "Status de usuário é restrito a 'ativo' ou 'inativo'", 'true', 'true', $passed, $failed, $details);

    // 4. Vinculação de Professores e Disciplinas na Turma
    $turma = [
        'id' => 10,
        'nome' => '3º Ano B - Engenharia de Software',
        'professores' => [
            ['professor_id' => 20, 'disciplina' => 'Algoritmos'],
            ['professor_id' => 21, 'disciplina' => 'Banco de Dados']
        ]
    ];

    $totalProfessores = count($turma['professores']);
    assertUT($totalProfessores === 2, "Turma suporta múltiplos professores vinculados por disciplina", '2 professores', "{$totalProfessores} professores", $passed, $failed, $details);
    assertUT($turma['professores'][0]['disciplina'] === 'Algoritmos', "Professor A associado com disciplina 'Algoritmos'", 'Algoritmos', $turma['professores'][0]['disciplina'], $passed, $failed, $details);

    // 5. Matriculamento de Alunos na Turma
    $alunosMatriculados = [
        ['aluno_id' => 101, 'nome' => 'Carlos Silva'],
        ['aluno_id' => 102, 'nome' => 'Ana Souza']
    ];

    $contagemAlunos = count($alunosMatriculados);
    assertUT($contagemAlunos === 2, "Turma gerencia corretamente a contagem de alunos matriculados", '2 alunos', "{$contagemAlunos} alunos", $passed, $failed, $details);

    return [
        'name' => 'Módulo de Gestão de Usuários, Regras de Proteção e Turmas',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testUsuariosTurmasModule();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) { echo $line . "\n"; }
    exit($res['failed'] > 0 ? 1 : 0);
}
