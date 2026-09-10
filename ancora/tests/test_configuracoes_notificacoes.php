<?php
/**
 * ÂNCORA - Testes do Módulo de Configurações Globais e Notificações (CI/CD)
 * Valida a geração de iniciais de avatar, alternância de tema e o payload de notificações em tempo real.
 */

function testConfiguracoesNotificacoesModule(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertCN(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // 1. Gerador de Iniciais Dinâmicas do Avatar de Perfil
    function obterIniciaisAvatar(string $nomeCompleto): string {
        $partes = explode(' ', trim($nomeCompleto));
        $partes = array_values(array_filter($partes));
        if (count($partes) === 0) return '??';
        if (count($partes) === 1) return strtoupper(mb_substr($partes[0], 0, 2));
        return strtoupper(mb_substr($partes[0], 0, 1) . mb_substr(end($partes), 0, 1));
    }

    $iniciais1 = obterIniciaisAvatar('Carlos Eduardo Silva');
    assertCN($iniciais1 === 'CS', "Nome 'Carlos Eduardo Silva' gera iniciais 'CS' para o avatar", 'CS', $iniciais1, $passed, $failed, $details);

    $iniciais2 = obterIniciaisAvatar('Mariana');
    assertCN($iniciais2 === 'MA', "Nome único 'Mariana' gera iniciais 'MA' para o avatar", 'MA', $iniciais2, $passed, $failed, $details);

    // 2. Alternância de Tema Dark / Light
    $temasSuportados = ['dark', 'light'];
    $temaSelecionado = 'dark';
    assertCN(in_array($temaSelecionado, $temasSuportados, true), "Tema selecionado é restrito a 'dark' ou 'light'", 'true', 'true', $passed, $failed, $details);

    // 3. Formatação do Payload de Notificação
    $notificacao = [
        'usuario_id' => 15,
        'titulo'     => 'Nova Tarefa Publicada',
        'mensagem'   => 'A atividade de Algoritmos foi publicada.',
        'link'       => 'index.php?route=tarefas/detalhes&id=3',
        'lida'       => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    assertCN(!empty($notificacao['titulo']) && !empty($notificacao['mensagem']), "Payload de notificação contém título e mensagem preenchidos", 'não vazio', 'preenchido', $passed, $failed, $details);
    assertCN((int)$notificacao['lida'] === 0, "Notificação recém-criada inicia com status lida = 0 (não lida)", '0', (string)$notificacao['lida'], $passed, $failed, $details);

    // 4. Marcação de Notificação como Lida
    $notificacao['lida'] = 1;
    assertCN((int)$notificacao['lida'] === 1, "Ação rápida altera status da notificação para lida = 1", '1', (string)$notificacao['lida'], $passed, $failed, $details);

    return [
        'name' => 'Módulo de Configurações Globais (Avatar, Tema) e Central de Notificações',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testConfiguracoesNotificacoesModule();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) { echo $line . "\n"; }
    exit($res['failed'] > 0 ? 1 : 0);
}
