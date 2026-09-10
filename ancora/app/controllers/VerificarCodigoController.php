<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Controller da Tela de Digitação e Validação do Código (/verificar-codigo)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Validar o código de 6 dígitos digitado pelo usuário, controlando o número de tentativas
 * (máximo 5), tempo de expiração (10 minutos) e liberando a autorização para redefinição.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../services/MailerService.php';

class VerificarCodigoController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Se o usuário não passou pela tela de solicitar e-mail, redireciona ao início da recuperação
        $emailSession = $_SESSION['reset_email'] ?? '';
        if (empty($emailSession)) {
            header('Location: ' . url('recuperar-senha'));
            exit;
        }

        $pageTitle = "Verificar código — ÂNCORA";
        $errorMsg  = '';
        $successMsg = '';

        // Calcula o tempo de cooldown de reenvio de 60 segundos
        $cooldownRestante = PasswordReset::tempoParaReenvio($emailSession);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'verificar';

            if ($action === 'verificar') {
                $codigoDigitado = trim($_POST['codigo'] ?? '');
                // Remove quaisquer caracteres não numéricos
                $codigoDigitado = preg_replace('/[^0-9]/', '', $codigoDigitado);

                if (empty($codigoDigitado)) {
                    $errorMsg = "Por favor, digite o código de 6 dígitos enviado para seu e-mail.";
                } elseif (strlen($codigoDigitado) !== 6) {
                    $errorMsg = "O código deve conter exatamente 6 dígitos numéricos.";
                } else {
                    // Executa a validação do código via hash BCRYPT e controle de tentativas
                    $resultado = PasswordReset::validarCodigo($emailSession, $codigoDigitado);

                    if ($resultado['status'] === 'success') {
                        $record = $resultado['record'];

                        // Cria o token temporário de autorização na sessão para liberar /redefinir-senha
                        $_SESSION['reset_authorized'] = [
                            'usuario_id' => (int)$record['usuario_id'],
                            'reset_id'   => (int)$record['id'],
                            'expires'    => time() + 600 // Validade de 10 minutos para redefinir
                        ];

                        // Redireciona para a tela de criação da nova senha
                        header('Location: ' . url('redefinir-senha'));
                        exit;
                    } else {
                        $errorMsg = $resultado['message'];
                    }
                }
            } elseif ($action === 'reenviar') {
                if ($cooldownRestante > 0) {
                    $errorMsg = "Aguarde {$cooldownRestante} segundos para solicitar um novo código.";
                } else {
                    $usuario = Usuario::buscarPorEmail($emailSession);
                    if ($usuario && isset($usuario['status']) && $usuario['status'] === 'ativo') {
                        try {
                            $novoCodigo = PasswordReset::criarCodigo((int)$usuario['id'], $emailSession);
                            MailerService::enviarCodigoRecuperacao($emailSession, $usuario['nome'], $novoCodigo);
                            $successMsg = "Um novo código de 6 dígitos foi enviado para seu e-mail.";
                            $cooldownRestante = 60;
                        } catch (Exception $e) {
                            $errorMsg = "Erro ao reenviar o código: " . $e->getMessage();
                        }
                    } else {
                        $successMsg = "Se o e-mail estiver cadastrado no sistema, um novo código será enviado.";
                        $cooldownRestante = 60;
                    }
                }
            }
        }

        require __DIR__ . '/../views/auth/verificar_codigo.php';
    }
}
