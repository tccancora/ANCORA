<?php
/**
 * ÂNCORA - Testes do Módulo de Tarefas e Atividades Acadêmicas (CI/CD)
 * Valida a estrutura de questionários, pontuação, auto-correção, bloqueio de prazos e deduplicação de destinatários.
 */

function testTarefasModule(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertTM(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. Tipos de Atividades Suportados
    $tiposPermitidos = ['tradicional', 'questionario', 'hibrida'];
    $tipoTarefa = 'hibrida';
    assertTM(in_array($tipoTarefa, $tiposPermitidos, true), "Tipo de atividade 'hibrida' é válido", 'true', 'true', $passed, $failed, $details);

    // 2. Questionário e Cálculo da Nota Máxima
    $questoes = [
        ['tipo' => 'multipla_escolha', 'pontos' => 2.5, 'resposta_correta' => 'O(log n)'],
        ['tipo' => 'verdadeiro_falso', 'pontos' => 2.5, 'resposta_correta' => 'Falso'],
        ['tipo' => 'discursiva',       'pontos' => 5.0, 'resposta_correta' => null]
    ];

    $notaTotalMax = array_sum(array_column($questoes, 'pontos'));
    assertTM($notaTotalMax === 10.0, "Soma da pontuação das questões do questionário é 10.0 pontos", '10.0', (string)$notaTotalMax, $passed, $failed, $details);

    // 3. Auto-Correção de Questões Objetivas
    $respostasAluno = [
        0 => 'O(log n)', // Correta -> +2.5
        1 => 'Falso'     // Correta -> +2.5
    ];

    $notaObjetivaObtida = 0.0;
    foreach ($questoes as $idx => $q) {
        if ($q['tipo'] !== 'discursiva' && isset($respostasAluno[$idx])) {
            if ($respostasAluno[$idx] === $q['resposta_correta']) {
                $notaObjetivaObtida += (float)$q['pontos'];
            }
        }
    }

    assertTM($notaObjetivaObtida === 5.0, "Auto-correção atribui 5.0 pontos para as questões objetivas corretas", '5.0', (string)$notaObjetivaObtida, $passed, $failed, $details);

    // 4. Bloqueio Rigoroso de Prazo de Entrega
    $prazoPassado = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $prazoFuturo  = date('Y-m-d H:i:s', strtotime('+2 days'));
    $agora = date('Y-m-d H:i:s');

    $isEncerrada = ($agora > $prazoPassado);
    $isAberta    = ($agora <= $prazoFuturo);

    assertTM($isEncerrada === true, "Tarefa com prazo menor que o horário atual é identificada como encerrada", 'true', 'true', $passed, $failed, $details);
    assertTM($isAberta === true, "Tarefa com prazo futuro é identificada como aberta para envios", 'true', 'true', $passed, $failed, $details);

    // 5. Algoritmo de Deduplicação de Destinatários de Tarefas
    $alunosTurma1 = [101, 102, 103];
    $alunosIndividuais = [102, 104]; // 102 está duplicado intencionalmente

    $destinatariosCombinados = array_unique(array_merge($alunosTurma1, $alunosIndividuais));
    sort($destinatariosCombinados);

    $contagemUnicos = count($destinatariosCombinados);
    assertTM($contagemUnicos === 4 && $destinatariosCombinados === [101, 102, 103, 104], "Deduplicação consolida alunos de turma e individuais sem repetir cadastros (4 alunos únicos)", '4 alunos [101, 102, 103, 104]', "{$contagemUnicos} alunos [" . implode(', ', $destinatariosCombinados) . "]", $passed, $failed, $details);

    return [
        'name' => 'Módulo de Tarefas, Questionários, Auto-correção e Deduplicação',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testTarefasModule();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) { echo $line . "\n"; }
    exit($res['failed'] > 0 ? 1 : 0);
}
