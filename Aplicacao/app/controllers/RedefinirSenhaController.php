<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller da Tela de Redefinição de Nova Senha (/redefinir-senha)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Permitir a criação e gravação da nova senha pessoal do usuário somente após
 * a validação completa e autorizada do código de verificação.
 * 
 * REGRAS DE SEGURANÇA:
 * 1. PROTEÇÃO DE ACESSO: Exige a autorização $_SESSION['reset_authorized'] criada pelo VerificarCodigoController.
 * 2. CRIPTOGRAFIA: Salva a nova senha utilizando password_hash($novaSenha, PASSWORD_DEFAULT) com BCRYPT.
 * 3. INVALIDAÇÃO: Marca o código como utilizado (used_at = NOW()) e limpa a sessão de recuperação.
 * 4. SEM LOGIN AUTOMÁTICO: O usuário deve retornar à tela de login e entrar com suas novas credenciais.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';

class RedefinirSenhaController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Valida se o usuário concluiu a verificação do código e possui a autorização na sessão
        $authorized = $_SESSION['reset_authorized'] ?? null;
        if (!$authorized || empty($authorized['usuario_id']) || time() > ($authorized['expires'] ?? 0)) {
            unset($_SESSION['reset_authorized']);
            header('Location: ' . url('recuperar-senha'));
            exit;
        }

        $pageTitle = "Redefinir senha — ÂNCORA";
        $errorMsg  = '';
        $sucesso   = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $novaSenha      = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            if (empty($novaSenha) || empty($confirmarSenha)) {
                $errorMsg = "Por favor, preencha todos os campos obrigatórios.";
            } elseif (strlen($novaSenha) < 6) {
                $errorMsg = "A nova senha deve possuir pelo menos 6 caracteres.";
            } elseif ($novaSenha !== $confirmarSenha) {
                $errorMsg = "As senhas não coincidem.";
            } else {
                try {
                    $usuarioId = (int)$authorized['usuario_id'];
                    $resetId   = (int)$authorized['reset_id'];

                    // 1. Atualiza a senha no banco de dados com hash BCRYPT via Prepared Statement
                    Usuario::redefinirSenha($usuarioId, $novaSenha);

                    // 2. Marca o código de recuperação como utilizado e invalida outros códigos do usuário
                    PasswordReset::marcarComoUsado($resetId);
                    PasswordReset::invalidarTodosDoUsuario($usuarioId);

                    // 3. Limpa os dados temporários de recuperação da sessão por segurança
                    unset($_SESSION['reset_authorized']);
                    unset($_SESSION['reset_email']);

                    $sucesso = true;
                } catch (Exception $e) {
                    $errorMsg = "Erro no servidor ao redefinir a senha: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/auth/redefinir_senha.php';
    }
}
