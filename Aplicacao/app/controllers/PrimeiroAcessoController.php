<?php
/**
 * Controller responsável pelo fluxo de Primeiro Acesso (Troca de Senha Inicial)
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';

class PrimeiroAcessoController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Se já estiver autenticado, redireciona para o seu dashboard
        if (isset($_SESSION['user'])) {
            $perfilAtual = mb_strtolower(trim($_SESSION['user']['perfil_nome'] ?? ''));
            if ($perfilAtual === 'aluno') {
                header('Location: ' . url('aluno'));
            } elseif ($perfilAtual === 'professor') {
                header('Location: ' . url('professor'));
            } elseif ($perfilAtual === 'funcionario' || $perfilAtual === 'funcionário') {
                header('Location: ' . url('funcionario'));
            } else {
                header('Location: ' . url('admin'));
            }
            exit;
        }

        $pageTitle = "Primeiro Acesso — ÂNCORA";
        $errorMsg = '';
        $sucesso = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email          = trim($_POST['email'] ?? '');
            $senhaInicial   = $_POST['senha_inicial'] ?? '';
            $novaSenha      = $_POST['nova_senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            if (empty($email) || empty($senhaInicial) || empty($novaSenha) || empty($confirmarSenha)) {
                $errorMsg = "Por favor, preencha todos os campos obrigatórios.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "E-mail inválido.";
            } elseif (strlen($novaSenha) < 6) {
                $errorMsg = "A nova senha deve possuir pelo menos 6 caracteres.";
            } elseif ($novaSenha !== $confirmarSenha) {
                $errorMsg = "As senhas não coincidem.";
            } else {
                $usuario = Usuario::buscarPorEmail($email);

                if (!$usuario || !password_verify($senhaInicial, $usuario['senha'])) {
                    $errorMsg = "E-mail ou senha inicial incorretos.";
                } elseif (isset($usuario['status']) && $usuario['status'] === 'inativo') {
                    $errorMsg = "Este usuário está inativo.";
                } elseif (isset($usuario['primeiro_acesso']) && (int)$usuario['primeiro_acesso'] === 0) {
                    $errorMsg = "Este usuário já realizou o primeiro acesso. Utilize o Login para entrar no ÂNCORA.";
                } else {
                    try {
                        Usuario::concluirPrimeiroAcesso((int)$usuario['id'], $novaSenha);
                        $sucesso = true;
                    } catch (Exception $e) {
                        $errorMsg = "Erro no servidor ao alterar a senha: " . $e->getMessage();
                    }
                }
            }
        }

        require __DIR__ . '/../views/auth/primeiro_acesso.php';
    }
}
