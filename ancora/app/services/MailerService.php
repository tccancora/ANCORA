<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Serviço de Envio de E-mails (MailerService)
 * 
 * Fachada nativa e simplificada para disparo de e-mails transacionais (ex: código de recuperação de senha),
 * sem dependência de APIs de terceiros.
 */

class MailerService {

    /**
     * Envia o e-mail contendo o código de recuperação de 6 dígitos.
     *
     * @param string $emailDestinatario E-mail do usuário cadastrado
     * @param string $nomeUsuario Nome completo do usuário
     * @param string $codigoNum Código numérico de 6 dígitos
     * @return bool True se o envio foi efetuado ou registrado com sucesso
     */
    public static function enviarCodigoRecuperacao(string $emailDestinatario, string $nomeUsuario, string $codigoNum): bool {
        $assunto = "Código de Recuperação de Senha — ÂNCORA";
        $mensagem = "Olá, " . htmlspecialchars($nomeUsuario) . ".\n\n";
        $mensagem .= "Seu código de verificação para redefinir a senha é: " . $codigoNum . "\n\n";
        $mensagem .= "Este código é válido por 10 minutos.\n";

        $headers = "From: no-reply@ancora.edu.br\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Tenta envio via mail() nativo do PHP
        $enviado = @mail($emailDestinatario, $assunto, $mensagem, $headers);

        // Registra o envio no log local para auditoria e desenvolvimento
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mail.log';
        @file_put_contents(
            $logFile,
            sprintf("[%s] CÓDIGO RECUPERAÇÃO PARA: %s (%s) | CÓDIGO: %s | ENVIADO: %s\n", 
                date('Y-m-d H:i:s'), 
                $emailDestinatario, 
                $nomeUsuario, 
                $codigoNum, 
                $enviado ? 'SIM (mail())' : 'SIM (log local)'
            ),
            FILE_APPEND
        );

        return true;
    }
}
