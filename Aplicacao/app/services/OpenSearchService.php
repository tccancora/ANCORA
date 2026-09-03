<?php
/**
 * ÂNCORA - Sistema de Gestão Acadêmica
 * Serviço de Integração com o Banco de Dados Aiven OpenSearch 2.11 (OpenSearchService)
 * 
 * OBJETIVO E ARQUITETURA:
 * Fornecer um cliente de conexão nativo em PHP (sem dependências externas de pacotes),
 * utilizando a extensão cURL com HTTPS, Basic Authentication e leitura segura de variáveis de ambiente.
 */

require_once __DIR__ . '/../../config/opensearch.php';

class OpenSearchService {

    /**
     * Executa requisições REST HTTP parametrizadas para o cluster Aiven OpenSearch.
     *
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $path Caminho do endpoint do OpenSearch (ex: '/', 'ancora_logs/_doc/1')
     * @param array|null $body Payload em array associativo PHP a ser convertido para JSON
     * @return array Retorna resposta estruturada contendo 'success', 'code', 'data' e 'error'
     */
    public static function request(string $method, string $path, ?array $body = null): array {
        $config = getOpenSearchConfig();
        $baseUrl = sprintf('%s://%s:%d', $config['scheme'], $config['host'], $config['port']);
        $url = $baseUrl . '/' . ltrim($path, '/');

        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $config['user'] . ':' . $config['password']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['verify_ssl']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $config['verify_ssl'] ? 2 : 0);

        if ($body !== null) {
            $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        $responseBody = curl_exec($ch);
        $httpCode     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorNo  = curl_errno($ch);
        $curlError    = curl_error($ch);

        curl_close($ch);

        if ($curlErrorNo !== 0) {
            return [
                'success' => false,
                'code'    => 0,
                'error'   => "Erro de comunicação cURL ({$curlErrorNo}): {$curlError}",
                'data'    => null
            ];
        }

        $decodedData = json_decode($responseBody, true);
        $isSuccess = ($httpCode >= 200 && $httpCode < 300);

        return [
            'success' => $isSuccess,
            'code'    => $httpCode,
            'data'    => $decodedData ?? $responseBody,
            'error'   => $isSuccess ? null : ($decodedData['error']['reason'] ?? "HTTP Error {$httpCode}")
        ];
    }

    /**
     * Testa a conectividade com o cluster OpenSearch (Ping / Cluster Info).
     */
    public static function ping(): array {
        return self::request('GET', '/');
    }

    /**
     * Retorna a saúde do cluster OpenSearch (Health Check).
     */
    public static function getClusterHealth(): array {
        return self::request('GET', '/_cluster/health');
    }

    /**
     * Indexa (insere ou atualiza) um documento no OpenSearch.
     *
     * @param string $index Nome do índice no OpenSearch (ex: 'ancora_logs')
     * @param string|null $id ID do documento (se nulo, o OpenSearch gera um ID automático)
     * @param array $document Dados em array PHP
     * @return array
     */
    public static function indexDocument(string $index, ?string $id, array $document): array {
        $path = $id !== null ? "/{$index}/_doc/{$id}" : "/{$index}/_doc";
        $method = $id !== null ? 'PUT' : 'POST';
        return self::request($method, $path, $document);
    }

    /**
     * Busca um documento por ID no OpenSearch.
     */
    public static function getDocument(string $index, string $id): array {
        return self::request('GET', "/{$index}/_doc/{$id}");
    }

    /**
     * Realiza uma busca parametrizada usando o Query DSL do OpenSearch.
     */
    public static function search(string $index, array $queryBody): array {
        return self::request('POST', "/{$index}/_search", $queryBody);
    }

    /**
     * Exclui um documento por ID.
     */
    public static function deleteDocument(string $index, string $id): array {
        return self::request('DELETE', "/{$index}/_doc/{$id}");
    }
}
