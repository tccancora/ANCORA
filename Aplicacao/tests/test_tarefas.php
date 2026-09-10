<?php
/**
 * Script de Testes Automatizados End-to-End (E2E)
 * Módulo de Tarefas do Sistema ÂNCORA
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/models/Tarefa.php';
require_once __DIR__ . '/../app/models/Turma.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/services/NotificationService.php';

echo "========================================================\n";
echo " INICIANDO SUÍTE DE TESTES E2E: MÓDULO DE TAREFAS ÂNCORA\n";
echo "========================================================\n\n";

$db = getDatabaseConnection();
$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $testName) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        $passed++;
    } else {
        echo " [FAIL] {$testName}\n";
        $failed++;
    }
}

try {
    // -------------------------------------------------------------
    // 1. SETUP DE DADOS DE TESTE (Instituições, Usuários, Turmas)
    // -------------------------------------------------------------
    $inst1 = 1;
    $inst2 = 2;

    // Garantir que instituição 2 existe
    $db->prepare("INSERT IGNORE INTO instituicoes (id, nome, cnpj, created_at, updated_at) VALUES (2, 'Instituição Secundária', '99.999.999/0001-99', NOW(), NOW())")->execute();

    // Criar/Obter Professor A e Professor B
    $profA = Usuario::buscarPorEmail('profA_test@ancora.com');
    if (!$profA) {
        $idA = Usuario::criar('Professor A Teste', 'profA_test@ancora.com', 'Senha123@', 2, $inst1);
        $profA = Usuario::buscarPorId($idA);
    }

    $profB = Usuario::buscarPorEmail('profB_test@ancora.com');
    if (!$profB) {
        $idB = Usuario::criar('Professor B Teste', 'profB_test@ancora.com', 'Senha123@', 2, $inst1);
        $profB = Usuario::buscarPorId($idB);
    }

    // Criar/Obter Admin A
    $adminA = Usuario::buscarPorEmail('adminA_test@ancora.com');
    if (!$adminA) {
        $idAdmin = Usuario::criar('Admin A Teste', 'adminA_test@ancora.com', 'Senha123@', 1, $inst1);
        $adminA = Usuario::buscarPorId($idAdmin);
    }

    // Criar Aluno A, Aluno B, Aluno C
    $alunoA = Usuario::buscarPorEmail('alunoA_test@ancora.com');
    if (!$alunoA) {
        $idAlA = Usuario::criar('Aluno A Silva', 'alunoA_test@ancora.com', 'Senha123@', 3, $inst1);
        $alunoA = Usuario::buscarPorId($idAlA);
    }

    $alunoB = Usuario::buscarPorEmail('alunoB_test@ancora.com');
    if (!$alunoB) {
        $idAlB = Usuario::criar('Aluno B Santos', 'alunoB_test@ancora.com', 'Senha123@', 3, $inst1);
        $alunoB = Usuario::buscarPorId($idAlB);
    }

    $alunoC = Usuario::buscarPorEmail('alunoC_test@ancora.com');
    if (!$alunoC) {
        $idAlC = Usuario::criar('Aluno C Oliveira', 'alunoC_test@ancora.com', 'Senha123@', 3, $inst1);
        $alunoC = Usuario::buscarPorId($idAlC);
    }

    // Criar Turma de Teste e vincular Aluno A e Aluno B
    $stmtT = $db->prepare("SELECT id FROM turmas WHERE nome = 'Turma Teste Tarefas' AND instituicao_id = :inst");
    $stmtT->execute([':inst' => $inst1]);
    $turmaId = $stmtT->fetchColumn();

    if (!$turmaId) {
        $turmaId = Turma::criar('Turma Teste Tarefas', $inst1);
    }

    try { Turma::adicionarAluno((int)$turmaId, (int)$alunoA['id']); } catch (Exception $e) {}
    try { Turma::adicionarAluno((int)$turmaId, (int)$alunoB['id']); } catch (Exception $e) {}

    echo "Dados de infraestrutura e teste preparados.\n\n";

    // -------------------------------------------------------------
    // TESTE 1: Criação de Tarefa com Questionário e Anexos
    // -------------------------------------------------------------
    $prazoValido = date('Y-m-d H:i:s', strtotime('+5 days'));
    $dadosTarefa1 = [
        'instituicao_id'      => $inst1,
        'created_by'          => (int)$profA['id'],
        'titulo'              => 'Atividade 1: Algoritmos e Estruturas',
        'descricao'           => 'Por favor respondam às questões e enviem seu código.',
        'disciplina'          => 'Algoritmos',
        'tipo_atividade'      => 'hibrida',
        'permite_anexo_aluno' => 1,
        'prazo_entrega'       => $prazoValido,
        'status'              => 'publicada'
    ];

    // Destinatários combinados: Turma 1 + Aluno A (duplicado intencionalmente) + Aluno C (individual)
    $destinatarios1 = [
        'turmas' => [(int)$turmaId],
        'alunos' => [(int)$alunoA['id'], (int)$alunoC['id']]
    ];

    $questoes1 = [
        [
            'enunciado'        => 'Qual a complexidade de busca binária?',
            'tipo'             => 'multipla_escolha',
            'pontos'           => 2.5,
            'obrigatoria'      => 1,
            'alternativas'     => ['O(1)', 'O(n)', 'O(log n)', 'O(n^2)'],
            'resposta_correta' => ['correta' => 'O(log n)']
        ],
        [
            'enunciado'        => 'Uma lista ligada permite acesso aleatório em O(1)?',
            'tipo'             => 'verdadeiro_falso',
            'pontos'           => 2.5,
            'obrigatoria'      => 1,
            'alternativas'     => ['Verdadeiro', 'Falso'],
            'resposta_correta' => ['correta' => 'Falso']
        ],
        [
            'enunciado'        => 'Explique a diferença entre Pilha e Fila.',
            'tipo'             => 'discursiva',
            'pontos'           => 5.0,
            'obrigatoria'      => 1,
            'alternativas'     => [],
            'resposta_correta' => null
        ]
    ];

    $materiais1 = [
        [
            'nome_original'   => 'apostila_algoritmos.pdf',
            'caminho_arquivo' => 'storage/uploads/tarefas/materiais/test_apostila.pdf',
            'tamanho_bytes'   => 1024,
            'mime_type'       => 'application/pdf'
        ]
    ];

    $tarefaId1 = Tarefa::criarTarefa($dadosTarefa1, $destinatarios1, $materiais1, $questoes1);
    assertTest($tarefaId1 > 0, "Criação de tarefa híbrida com questionário e materiais");

    // -------------------------------------------------------------
    // TESTE 2: Regra de Deduplicação de Destinatários
    // -------------------------------------------------------------
    $destinatariosUnicos = Tarefa::obterAlunosDestinatariosUnicos($tarefaId1, $inst1);
    $idsAlunos = array_map(function($a) { return (int)$a['id']; }, $destinatariosUnicos);
    
    $contagemAlunoA = count(array_keys($idsAlunos, (int)$alunoA['id']));
    assertTest($contagemAlunoA === 1, "Deduplicação: Aluno A inserido por turma e individualmente aparece exatamente 1 vez");
    assertTest(count($destinatariosUnicos) === 3, "Total de destinatários únicos consolidado é 3 (Aluno A, B e C)");

    // -------------------------------------------------------------
    // TESTE 3: Regra de Notificações Automáticas e Deduplicadas
    // -------------------------------------------------------------
    $stmtNotif = $db->prepare("
        SELECT COUNT(*) FROM notificacoes 
        WHERE usuario_id = :uid AND link LIKE :link
    ");
    $stmtNotif->execute([':uid' => (int)$alunoA['id'], ':link' => "%id={$tarefaId1}%"]);
    $notifsAlunoA = (int)$stmtNotif->fetchColumn();
    assertTest($notifsAlunoA === 1, "Notificação: Aluno A recebeu exatamente 1 notificação sobre a publicação da tarefa");

    // -------------------------------------------------------------
    // TESTE 4: Regra de Privacidade Estrita (Professor A vs Professor B vs Admin)
    // -------------------------------------------------------------
    $tarefasProfA = Tarefa::listarCriadasPorUsuario((int)$profA['id'], $inst1);
    $tarefasProfB = Tarefa::listarCriadasPorUsuario((int)$profB['id'], $inst1);
    $tarefasAdminA = Tarefa::listarCriadasPorUsuario((int)$adminA['id'], $inst1);

    $profAViuTarefa1 = in_array($tarefaId1, array_column($tarefasProfA, 'id'));
    $profBViuTarefa1 = in_array($tarefaId1, array_column($tarefasProfB, 'id'));
    $adminAViuTarefa1 = in_array($tarefaId1, array_column($tarefasAdminA, 'id'));

    assertTest($profAViuTarefa1 === true, "Privacidade: Professor A visualiza sua tarefa criada");
    assertTest($profBViuTarefa1 === false, "Privacidade: Professor B NÃO visualiza tarefa criada pelo Professor A");
    assertTest($adminAViuTarefa1 === false, "Privacidade: Admin A NÃO visualiza tarefa criada pelo Professor A em sua lista de autoria");

    $permProfA = Tarefa::validarPropriedade($tarefaId1, (int)$profA['id'], $inst1);
    $permProfB = Tarefa::validarPropriedade($tarefaId1, (int)$profB['id'], $inst1);
    $permAdminA = Tarefa::validarPropriedade($tarefaId1, (int)$adminA['id'], $inst1);

    assertTest($permProfA === true, "Validação de Propriedade: Professor A possui propriedade da tarefa");
    assertTest($permProfB === false, "Validação de Propriedade: Professor B NÃO possui propriedade da tarefa de A");
    assertTest($permAdminA === false, "Validação de Propriedade: Admin A NÃO possui propriedade da tarefa de A");

    // -------------------------------------------------------------
    // TESTE 5: Submissão do Aluno e Auto-Correção de Objetivas
    // -------------------------------------------------------------
    $questoesCriadas = Tarefa::obterQuestoes($tarefaId1);
    $q1Id = (int)$questoesCriadas[0]['id']; // Multipla escolha: 'O(log n)'
    $q2Id = (int)$questoesCriadas[1]['id']; // Verdadeiro/Falso: 'Falso'
    $q3Id = (int)$questoesCriadas[2]['id']; // Discursiva

    $respostasAlunoA = [
        $q1Id => 'O(log n)', // Correta (+2.5 pts)
        $q2Id => 'Falso',     // Correta (+2.5 pts)
        $q3Id => 'Pilha é LIFO (Last In First Out) e Fila é FIFO (First In First Out).' // Discursiva
    ];

    $arquivosAlunoA = [
        [
            'nome_original'   => 'algoritmo_pilha.c',
            'caminho_arquivo' => 'storage/uploads/tarefas/entregas/test_pilha.c',
            'tamanho_bytes'   => 512,
            'mime_type'       => 'text/x-c'
        ]
    ];

    $resSubmissao = Tarefa::salvarEntregaAluno($tarefaId1, (int)$alunoA['id'], $inst1, $respostasAlunoA, $arquivosAlunoA);
    assertTest($resSubmissao['success'] === true, "Submissão do aluno com respostas e anexos gravada com sucesso");

    $entregaAlunoA = Tarefa::obterEntregaDoAluno($tarefaId1, (int)$alunoA['id']);
    assertTest($entregaAlunoA !== null && $entregaAlunoA['status'] === 'entregue', "Status da entrega registrado como 'entregue'");

    // Verificar auto-correção das objetivas
    $respostasSalvas = $entregaAlunoA['respostas'];
    $pontosQ1 = 0;
    $pontosQ2 = 0;
    $pontosQ3 = -1;

    foreach ($respostasSalvas as $rs) {
        if ((int)$rs['questao_id'] === $q1Id) $pontosQ1 = (float)$rs['pontos_obtidos'];
        if ((int)$rs['questao_id'] === $q2Id) $pontosQ2 = (float)$rs['pontos_obtidos'];
        if ((int)$rs['questao_id'] === $q3Id) $pontosQ3 = $rs['pontos_obtidos'];
    }

    assertTest($pontosQ1 === 2.5, "Auto-correção: Questão de múltipla escolha pontuada corretamente (2.5)");
    assertTest($pontosQ2 === 2.5, "Auto-correção: Questão Verdadeiro/Falso pontuada corretamente (2.5)");
    assertTest($pontosQ3 === null, "Questão discursiva aguarda avaliação manual do professor");

    // -------------------------------------------------------------
    // TESTE 6: Bloqueio Rigoroso de Prazo no Servidor (Backend Enforcement)
    // -------------------------------------------------------------
    $prazoPassado = date('Y-m-d H:i:s', strtotime('-2 hours'));
    $dadosTarefaExpirada = [
        'instituicao_id'      => $inst1,
        'created_by'          => (int)$profA['id'],
        'titulo'              => 'Tarefa Expirada para Teste de Bloqueio',
        'descricao'           => 'Esta tarefa expirou há 2 horas.',
        'disciplina'          => 'História',
        'tipo_atividade'      => 'tradicional',
        'permite_anexo_aluno' => 1,
        'prazo_entrega'       => $prazoPassado,
        'status'              => 'publicada'
    ];

    $tarefaExpiradaId = Tarefa::criarTarefa($dadosTarefaExpirada, ['alunos' => [(int)$alunoB['id']]]);
    $resSubmissaoExpirada = Tarefa::salvarEntregaAluno($tarefaExpiradaId, (int)$alunoB['id'], $inst1, [], []);

    assertTest(
        $resSubmissaoExpirada['success'] === false && strpos($resSubmissaoExpirada['error'], 'Prazo de entrega encerrado') !== false,
        "Bloqueio de Prazo no Backend: Tentativa de entrega após prazo rejeitada com sucesso pelo servidor"
    );

    // -------------------------------------------------------------
    // TESTE 7: Correção pelo Professor, Atribuição de Nota e Devolução
    // -------------------------------------------------------------
    $entregaIdA = (int)$entregaAlunoA['id'];
    $notasQuestoes = [
        $q3Id => 4.5 // 4.5 de 5.0 na discursiva -> Total 2.5 + 2.5 + 4.5 = 9.5
    ];
    $comentariosQuestoes = [
        $q3Id => 'Ótima explicação teórica sobre as estruturas de dados!'
    ];

    $resCorrecao = Tarefa::corrigirEDevolverEntrega(
        $entregaIdA,
        $tarefaId1,
        (int)$profA['id'],
        $inst1,
        9.5,
        'Parabéns pelo trabalho! Código muito bem estruturado.',
        $notasQuestoes,
        $comentariosQuestoes,
        true // Devolver
    );

    assertTest($resCorrecao === true, "Correção e devolução da tarefa pelo Professor A realizada com sucesso");

    $entregaDevolvida = Tarefa::obterEntregaDoAluno($tarefaId1, (int)$alunoA['id']);
    assertTest($entregaDevolvida['status'] === 'devolvida', "Status da entrega atualizado para 'devolvida'");
    assertTest((float)$entregaDevolvida['nota'] === 9.5, "Nota final 9.5 gravada com sucesso");

    // Verificar notificação de devolução enviada ao aluno
    $stmtNotifDevolucao = $db->prepare("
        SELECT COUNT(*) FROM notificacoes 
        WHERE usuario_id = :uid AND mensagem LIKE :msg
    ");
    $stmtNotifDevolucao->execute([':uid' => (int)$alunoA['id'], ':msg' => '%9,5/10,0%']);
    $notifsDevolucao = (int)$stmtNotifDevolucao->fetchColumn();
    assertTest($notifsDevolucao >= 1, "Notificação de devolução com nota enviada ao Aluno A");

    // -------------------------------------------------------------
    // TESTE 8: Métricas de Dashboard (Admin, Professor, Aluno)
    // -------------------------------------------------------------
    $dashProfA = Tarefa::obterContadoresDashboard((int)$profA['id'], 2, $inst1);
    assertTest($dashProfA['total_tarefas'] >= 2, "Dashboard Professor: Contagem de tarefas criadas correta");

    $dashAlunoA = Tarefa::obterContadoresDashboard((int)$alunoA['id'], 3, $inst1);
    assertTest($dashAlunoA['tarefas_avaliadas'] >= 1, "Dashboard Aluno: Contagem de tarefas avaliadas/devolvidas correta");

} catch (Exception $e) {
    echo "\nEXCEÇÃO INESPERADA: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $failed++;
}

echo "\n========================================================\n";
echo " RESULTADO FINAL DOS TESTES: {$passed} PASSARAM, {$failed} FALHARAM\n";
echo "========================================================\n";
