<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller da Tela de Solicitação de Recuperação de Senha (/recuperar-senha)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Gerenciar a entrada do e-mail do usuário, validação no banco de dados e envio do código.
 * 
 * REGRAS DE SEGURANÇA E PRIVACIDADE:
 * 1. MENSAGEM GENÉRICA: Não revela se o e-mail existe ou não no sistema para evitar enumeration attacks.
 * 2. USUÁRIO INATIVO: Se o usuário estiver inativo no banco (status = 'inativo'), a mensagem genérica é exibida, mas NENHUM e-mail nem código são gerados.
 * 3. COOLDOWN 60s: Limita solicitações frequentes para evitar spam de envio de e-mails.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../services/MailerService.php';

class RecuperarSenhaController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Se o usuário já estiver autenticado no sistema, redireciona ao seu dashboard
        if (isset($_SESSION['user'])) {
            $perfilAtual = strtolower($_SESSION['user']['perfil_nome'] ?? '');
            if ($perfilAtual === 'aluno') {
                header('Location: ' . url('aluno'));
            } else {
                header('Location: ' . url('admin'));
            }
            exit;
        }

        $pageTitle = "Recuperar senha — ÂNCORA";
        $errorMsg  = '';
        $infoMsg   = '';

        // Processa envio do formulário de e-mail via método POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            // 1. Validação de preenchimento e formato de e-mail
            if (empty($email)) {
                $errorMsg = "Por favor, informe seu e-mail institucional.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "Por favor, informe um endereço de e-mail válido.";
            } else {
                // 2. Consulta o banco de dados via Prepared Statement protegendo contra SQL Injection
                $usuario = Usuario::buscarPorEmail($email);

                // 3. Somente gera código e envia e-mail se o usuário existir E estiver com status 'ativo'
                if ($usuario && isset($usuario['status']) && $usuario['status'] === 'ativo') {
                    // 4. Verifica se existe o intervalo obrigatório de 60 segundos entre solicitações
                    $cooldownRestante = PasswordReset::tempoParaReenvio($email);

                    if ($cooldownRestante > 0) {
                        $errorMsg = "Aguarde {$cooldownRestante} segundos antes de solicitar um novo código.";
                    } else {
                        try {
                            // 5. Gera o código seguro de 6 dígitos e grava seu hash BCRYPT no banco
                            $codigoNum = PasswordReset::criarCodigo((int)$usuario['id'], $email);

                            // 6. Envia o código por e-mail utilizando as credenciais SMTP do .env
                            MailerService::enviarCodigoRecuperacao($email, $usuario['nome'], $codigoNum);

                            // Salva o e-mail na sessão para permitir a digitação na tela seguinte
                            $_SESSION['reset_email'] = $email;

                            // Redireciona imediatamente para a tela de verificação do código
                            header('Location: ' . url('verificar-codigo'));
                            exit;
                        } catch (Exception $e) {
                            $errorMsg = "Erro no servidor ao processar o código: " . $e->getMessage();
                        }
                    }
                } else {
                    // Para usuários inativos ou e-mails inexistentes, salvamos o e-mail na sessão
                    // e redirecionamos com a mesma mensagem genérica por segurança
                    $_SESSION['reset_email'] = $email;
                    header('Location: ' . url('verificar-codigo'));
                    exit;
                }
            }
        }

        require __DIR__ . '/../views/auth/recuperar_senha.php';
    }
}
