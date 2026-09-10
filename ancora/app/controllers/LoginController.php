<?php
/**
 * Controller responsável pela exibição e autenticação do Login
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perfil.php';

class LoginController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $pageTitle = "Entrar no sistema — ÂNCORA";
        $errorMsg = '';

        // Se já estiver logado, redireciona para o dashboard correspondente ao seu perfil
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

        // Processa formulário via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if (empty($email) || empty($senha)) {
                $errorMsg = "Por favor, preencha todos os campos obrigatórios.";
            } else {
                try {
                    $usuario = Usuario::buscarPorEmail($email);

                    if ($usuario && password_verify($senha, $usuario['senha'])) {
                        if (isset($usuario['status']) && $usuario['status'] === 'inativo') {
                            $errorMsg = "Sua conta está inativa. Entre em contato com a administração.";
                        } else {
                            // Segurança: Regenerar ID da sessão se houver sessão ativa
                            if (session_status() === PHP_SESSION_ACTIVE) {
                                @session_regenerate_id(true);
                            }

                            // Buscar o perfil do usuário
                            $perfil = Perfil::buscarPorId((int)$usuario['perfil_id']);
                            $perfilNome = $perfil ? $perfil['nome'] : 'Administrador';

                            // Guardar apenas as informações necessárias na sessão (sem guardar a senha)
                            $_SESSION['user'] = [
                                'id'             => $usuario['id'],
                                'nome'           => $usuario['nome'],
                                'email'          => $usuario['email'],
                                'perfil_id'      => $usuario['perfil_id'],
                                'perfil_nome'    => $perfilNome,
                                'instituicao_id' => (int)($usuario['instituicao_id'] ?? 1)
                            ];

                            // Redireciona conforme o perfil
                            $perfilLimpo = mb_strtolower(trim($perfilNome));
                            if ($perfilLimpo === 'aluno') {
                                header('Location: ' . url('aluno'));
                            } elseif ($perfilLimpo === 'professor') {
                                header('Location: ' . url('professor'));
                            } elseif ($perfilLimpo === 'funcionario' || $perfilLimpo === 'funcionário') {
                                header('Location: ' . url('funcionario'));
                            } else {
                                header('Location: ' . url('admin'));
                            }
                            exit;
                        }
                    } else {
                        $errorMsg = "E-mail ou senha incorretos. Tente novamente.";
                    }
                } catch (Exception $e) {
                    $errorMsg = "Erro no servidor ao autenticar: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }
}
