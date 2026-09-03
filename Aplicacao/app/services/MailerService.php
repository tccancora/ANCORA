<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Serviço de Envio de E-mails (MailerService -> Integração Brevo API v3)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Atua como fachada unificada de envio de e-mails da aplicação ÂNCORA,
 * delegando todas as chamadas de envio para o BrevoService (API REST HTTPS v3 do Brevo).
 */

require_once __DIR__ . '/BrevoService.php';

class MailerService {

    /**
     * Envia o e-mail contendo o código de recuperação de 6 dígitos utilizando a API do Brevo.
     *
     * @param string $emailDestinatario E-mail do usuário cadastrado
     * @param string $nomeUsuario Nome completo do usuário
     * @param string $codigoNum Código numérico de 6 dígitos
     * @return bool True se o envio/registro foi efetuado com sucesso
     */
    public static function enviarCodigoRecuperacao(string $emailDestinatario, string $nomeUsuario, string $codigoNum): bool {
        $res = BrevoService::enviarCodigoRecuperacao($emailDestinatario, $nomeUsuario, $codigoNum);
        return (bool)($res['success'] ?? false);
    }
}
