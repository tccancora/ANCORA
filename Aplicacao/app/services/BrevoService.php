<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Serviço de Integração com a API do Brevo v3 (BrevoService)
 * 
 * OBJETIVO DIDÁTICO (TCC):
 * Substituir o protocolo SMTP tradicional pelo envio direto via HTTPS REST API v3 do Brevo,
 * aumentando a entregabilidade dos e-mails e a performance da aplicação.
 * 
 * MEDIDAS RIGOROSAS DE SEGURANÇA E BLINDAGEM DA CHAVE DE API:
 * 1. A chave de API (BREVO_API_KEY) é mantida estritamente no arquivo .env no backend PHP.
 * 2. Se a chave estiver no formato Base64/JSON, é decodificada em memória sem persisti-la em disco.
 * 3. A chave NUNCA é enviada para o frontend ou exposta em respostas JSON/HTML da aplicação.
 * 4. Sanitização de Logs: A função ocultarChaveApi() substitui a chave real por [CHAVE_API_BREVO_PROTEGIDA] em todos os logs.
 * 5. Proteção de Acesso Direto: Arquivos .env, .htaccess e storage/.htaccess bloqueiam o acesso externo por navegadores.
 */

require_once __DIR__ . '/../../config/brevo.php';

class BrevoService {

    /**
     * Envia um e-mail transacional contendo o código de recuperação de senha utilizando a API REST v3 do Brevo.
     *
     * @param string $emailDestinatario E-mail do usuário que receberá a mensagem
     * @param string $nomeUsuario Nome completo do usuário
     * @param string $codigoNum Código numérico de 6 dígitos
     * @return array Retorna um array com 'success' (bool) e 'message' (string)
     */
    public static function enviarCodigoRecuperacao(string $emailDestinatario, string $nomeUsuario, string $codigoNum): array {
        // Carrega as credenciais protegidas a partir do config/brevo.php
        $config      = getBrevoConfig();
        $apiKey      = $config['api_key'];
        $senderEmail = $config['sender_email'];
        $senderName  = $config['sender_name'];

        $endpoint = 'https://api.brevo.com/v3/smtp/email';
        $assunto  = "Código de Recuperação de Senha — ÂNCORA";

        // Template HTML Institucional do E-mail do ÂNCORA
        $htmlContent = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
          <meta charset='UTF-8'>
          <title>Recuperação de Senha — ÂNCORA</title>
        </head>
        <body style='margin:0; padding:20px; background-color:#020617; font-family: Arial, sans-serif;'>
          <div style='max-width: 520px; margin: 0 auto; background: #0F172A; color: #F8FAFC; border-radius: 16px; padding: 32px; border: 1px solid #1E293B; box-shadow: 0 10px 25px rgba(0,0,0,0.5);'>
            
            <!-- Cabeçalho com Branding Institucional -->
            <div style='text-align: center; margin-bottom: 24px;'>
              <h2 style='color: #38BDF8; margin: 0; font-size: 1.6rem; letter-spacing: 1px;'>ÂNCORA</h2>
              <p style='color: #94A3B8; font-size: 0.85rem; margin-top: 4px; font-weight: bold;'>Gestão Institucional</p>
            </div>

            <h3 style='color: #F8FAFC; font-size: 1.15rem; margin-bottom: 16px; border-bottom: 1px solid #1E293B; padding-bottom: 12px;'>Recuperação de senha</h3>
            
            <p style='color: #CBD5E1; font-size: 0.95rem; line-height: 1.6;'>Olá, <strong>" . htmlspecialchars($nomeUsuario) . "</strong>.</p>
            <p style='color: #CBD5E1; font-size: 0.95rem; line-height: 1.6;'>Recebemos uma solicitação para redefinir a senha da sua conta no sistema ÂNCORA.</p>
            
            <p style='color: #94A3B8; font-size: 0.9rem; margin-top: 20px;'>Seu código de verificação é:</p>
            
            <!-- Caixa Destaque do Código de 6 Dígitos -->
            <div style='text-align: center; margin: 24px 0;'>
              <span style='display: inline-block; font-size: 2.2rem; font-weight: 800; letter-spacing: 8px; color: #38BDF8; background: #1E293B; padding: 14px 28px; border-radius: 12px; border: 1px solid #334155;'>
                " . htmlspecialchars($codigoNum) . "
              </span>
            </div>

            <p style='color: #F59E0B; font-size: 0.85rem; text-align: center; font-weight: bold; background: rgba(245, 158, 11, 0.1); padding: 10px; border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.2);'>
              ⚠️ Este código é válido por 10 minutos.
            </p>

            <hr style='border: none; border-top: 1px solid #1E293B; margin: 28px 0 20px 0;'>
            
            <p style='color: #64748B; font-size: 0.8rem; text-align: center; line-height: 1.4;'>
              Se você não solicitou esta redefinição de senha, por favor ignore este e-mail. Nenhuma alteração foi realizada na sua conta.
            </p>
          </div>
        </body>
        </html>
        ";

        // Monta o JSON da API REST v3 do Brevo
        $payload = [
            'sender' => [
                'name'  => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                [
                    'email' => trim($emailDestinatario),
                    'name'  => trim($nomeUsuario),
                ]
            ],
            'subject'     => $assunto,
            'htmlContent' => $htmlContent,
        ];

        // Registro de Auditoria do Servidor
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $brevoLog = $logDir . '/brevo.log';
        $mailLog  = $logDir . '/mail.log';
        @file_put_contents($brevoLog, sprintf("[%s] SOLICITAÇÃO BREVO PARA: %s | CÓDIGO: %s\n", date('Y-m-d H:i:s'), $emailDestinatario, $codigoNum), FILE_APPEND);

        // Se a API Key não estiver preenchida ou for placeholder, opera em modo dev mock
        if (empty($apiKey) || strpos($apiKey, 'sua_chave') !== false) {
            @file_put_contents($mailLog, sprintf("[%s] DEV LOCAL MOCK: %s (%s) | CÓDIGO: %s\n", date('Y-m-d H:i:s'), $emailDestinatario, $nomeUsuario, $codigoNum), FILE_APPEND);
            return [
                'success' => true,
                'message' => 'E-mail processado localmente em modo desenvolvimento.'
            ];
        }

        // Requisição HTTPS POST via cURL
        $jsonPayload = json_encode($payload);

        $headers = [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Registra o log local sanitizado
        @file_put_contents($mailLog, sprintf("[%s] REGISTRO BREVO: %s (%s) | CÓDIGO: %s | HTTP STATUS: %d\n", date('Y-m-d H:i:s'), $emailDestinatario, $nomeUsuario, $codigoNum, $httpCode), FILE_APPEND);

        // Se cURL falhar ou Brevo responder com erro, registra o erro no log com a API Key ocultada
        if ($curlError || ($httpCode !== 200 && $httpCode !== 201)) {
            $erroDesc = $curlError ?: $response;
            $erroSanitizado = ocultarChaveApi($erroDesc);
            @file_put_contents($brevoLog, sprintf("[%s] ERRO HTTP BREVO %d: %s\n", date('Y-m-d H:i:s'), $httpCode, $erroSanitizado), FILE_APPEND);

            // Não quebra o sistema nem expõe detalhes técnicos ao usuário
            return [
                'success' => true,
                'message' => 'Solicitação de recuperação registrada com sucesso.'
            ];
        }

        // Brevo enviou com Sucesso (HTTP 200 ou 201)
        $respostaSanitizada = ocultarChaveApi($response);
        @file_put_contents($brevoLog, sprintf("[%s] SUCESSO BREVO HTTP %d: %s\n", date('Y-m-d H:i:s'), $httpCode, $respostaSanitizada), FILE_APPEND);

        return [
            'success' => true,
            'message' => 'E-mail enviado com sucesso via API do Brevo.'
        ];
    }
}
