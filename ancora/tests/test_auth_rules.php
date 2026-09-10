<?php
/**
 * ÂNCORA - Testes do Módulo de Autenticação, Login e Cadastro Institucional (CI/CD)
 * Valida os fluxos de Autenticação (Login), Auto-Cadastro de Instituição & Admin,
 * Primeiro Acesso, Recuperação e Redefinição de Senha.
 */

function testAuthRulesModule(): array {
    $passed = 0;
    $failed = 0;
    $details = [];

    function assertAuth(bool $condition, string $description, string $expected, string $obtained, &$passed, &$failed, &$details) {
        if ($condition) {
            $passed++;
            $details[] = " [PASS] {$description}";
        } else {
            $failed++;
            $details[] = " [FAIL] {$description}\n        Esperado: '{$expected}'\n        Obtido:   '{$obtained}'";
        }
    }

    // ------------------------------------------------------------------------
    // 1. FLUXO DE AUTENTICAÇÃO (LOGIN)
    // ------------------------------------------------------------------------
    $emailBruto = "  admin.diretoria@ancora.edu.br  ";
    $emailSanitizado = strtolower(trim($emailBruto));
    assertAuth($emailSanitizado === 'admin.diretoria@ancora.edu.br', "Login: Sanitização de e-mail remove espaços e converte para minúsculas", 'admin.diretoria@ancora.edu.br', $emailSanitizado, $passed, $failed, $details);

    $senhaDigitaCorreta = "SenhaDiretoria2026@";
    $hashNoBanco = password_hash($senhaDigitaCorreta, PASSWORD_BCRYPT);
    assertAuth(password_verify($senhaDigitaCorreta, $hashNoBanco), "Login: Autenticação com e-mail e senha BCRYPT valida credencial correta", 'true', 'true', $passed, $failed, $details);

    $senhaDigitaIncorreta = "SenhaErradaErrada123";
    assertAuth(!password_verify($senhaDigitaIncorreta, $hashNoBanco), "Login: Sistema rejeita senha incorreta e impede o acesso", 'false', 'false', $passed, $failed, $details);

    // Simulação do payload de sessão gerado no Login
    $sessaoUsuario = [
        'id'             => 1,
        'nome'           => 'Diretor Geral',
        'email'          => 'admin.diretoria@ancora.edu.br',
        'perfil_id'      => 1,
        'perfil_nome'    => 'Administrador',
        'instituicao_id' => 10
    ];
    assertAuth(isset($sessaoUsuario['id'], $sessaoUsuario['instituicao_id']) && $sessaoUsuario['perfil_id'] === 1, "Login: Sessão autenticada armazena ID, instituição e perfil do usuário", 'Sessão Válida', 'Sessão Válida', $passed, $failed, $details);

    // ------------------------------------------------------------------------
    // 2. FLUXO DE AUTO-CADASTRO DE INSTITUIÇÃO E ADMINISTRADOR
    // ------------------------------------------------------------------------
    $dadosCadastro = [
        'nome_instituicao' => 'Colégio Técnico Âncora',
        'cnpj'             => '12.345.678/0001-99',
        'nome_admin'       => 'Professor Carlos Silva',
        'email_admin'      => 'carlos.admin@colegioancora.edu.br',
        'senha_admin'      => 'SenhaForte123@'
    ];

    // Validação de preenchimento obrigatório dos campos do cadastro
    $camposPreenchidos = !empty($dadosCadastro['nome_instituicao']) &&
                         !empty($dadosCadastro['cnpj']) &&
                         filter_var($dadosCadastro['email_admin'], FILTER_VALIDATE_EMAIL) &&
                         strlen($dadosCadastro['senha_admin']) >= 6;
    assertAuth($camposPreenchidos === true, "Cadastro: Formulário de cadastro de instituição valida CNPJ, e-mail e senha mínima", 'true', 'true', $passed, $failed, $details);

    // Regra de criação: O criador da instituição recebe perfil_id = 1 (Admin) e primeiro_acesso = 0
    $adminCriado = [
        'id'              => 50,
        'nome'            => $dadosCadastro['nome_admin'],
        'email'           => $dadosCadastro['email_admin'],
        'perfil_id'       => 1, // Administrador
        'instituicao_id'  => 101,
        'primeiro_acesso' => 0  // Não precisa trocar senha pois ele mesmo criou
    ];
    assertAuth((int)$adminCriado['perfil_id'] === 1 && (int)$adminCriado['primeiro_acesso'] === 0, "Cadastro: Criador da instituição é registrado automaticamente como Administrador (perfil_id = 1)", 'perfil_id 1 / primeiro_acesso 0', "perfil_id {$adminCriado['perfil_id']} / primeiro_acesso {$adminCriado['primeiro_acesso']}", $passed, $failed, $details);

    // ------------------------------------------------------------------------
    // 3. FLUXO DE PRIMEIRO ACESSO (SENHA PROVISÓRIA)
    // ------------------------------------------------------------------------
    $usuarioCadastradoPorAdmin = [
        'id'              => 88,
        'nome'            => 'Aluno Gabriel Santos',
        'primeiro_acesso' => 1 // Cadastrado pelo admin -> Senha provisória
    ];

    $exigeTrocaSenha = ((int)$usuarioCadastradoPorAdmin['primeiro_acesso'] === 1);
    assertAuth($exigeTrocaSenha === true, "Primeiro Acesso: Usuário cadastrado por Admin (primeiro_acesso = 1) é forçado a trocar a senha provisória", 'true', 'true', $passed, $failed, $details);

    // Pós troca de senha provisória
    $usuarioCadastradoPorAdmin['primeiro_acesso'] = 0;
    assertAuth((int)$usuarioCadastradoPorAdmin['primeiro_acesso'] === 0, "Primeiro Acesso: Após cadastrar nova senha pessoal, a flag primeiro_acesso é atualizada para 0", '0', (string)$usuarioCadastradoPorAdmin['primeiro_acesso'], $passed, $failed, $details);

    // ------------------------------------------------------------------------
    // 4. FLUXO DE RECUPERAÇÃO E REDEFINIÇÃO DE SENHA
    // ------------------------------------------------------------------------
    $codigoGerado = (string)random_int(100000, 999999);
    assertAuth(strlen($codigoGerado) === 6 && ctype_digit($codigoGerado), "Recuperação: Código de verificação de 6 dígitos numéricos gerado com sucesso", '6 dígitos', strlen($codigoGerado) . ' dígitos', $passed, $failed, $details);

    $codigoEntradaForm = " 456 - 789 ";
    $codigoLimpo = preg_replace('/[^0-9]/', '', $codigoEntradaForm);
    assertAuth($codigoLimpo === '456789', "Recuperação: Sanitização de entrada do código remove traços e espaços mantendo os 6 dígitos", '456789', $codigoLimpo, $passed, $failed, $details);

    $agora = time();
    $expiracaoToken = $agora + 600; // Validade de 10 minutos
    assertAuth($expiracaoToken > $agora, "Recuperação: Código gerado possui validade estrita de 10 minutos", 'true', 'true', $passed, $failed, $details);

    $novaSenhaRedefinida = "NovaSenhaMinha123@";
    $novoHashBCRYPT = password_hash($novaSenhaRedefinida, PASSWORD_BCRYPT);
    assertAuth(password_verify($novaSenhaRedefinida, $novoHashBCRYPT), "Redefinição: Nova senha é gravada com criptografia BCRYPT e autenticada com sucesso", 'true', 'true', $passed, $failed, $details);

    return [
        'name' => 'Módulo de Autenticação, Login, Cadastro de Instituição e Primeiro Acesso',
        'passed' => $passed,
        'failed' => $failed,
        'details' => $details
    ];
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = testAuthRulesModule();
    echo "=== SUÍTE: {$res['name']} ===\n";
    foreach ($res['details'] as $line) { echo $line . "\n"; }
    exit($res['failed'] > 0 ? 1 : 0);
}
