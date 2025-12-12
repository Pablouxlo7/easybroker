<?php

declare(strict_types=1);

namespace EasyBroker\Api;

use Exception;

/**
 * Cliente HTTP para interactuar con la API de EasyBroker.
 * 
 * Proporciona métodos para realizar peticiones HTTP con autenticación
 * y manejo de errores centralizado.
 */
class HttpClient
{
    private const DEFAULT_TIMEOUT = 30;
    private const RATE_LIMIT_PER_SECOND = 20;

    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private array $lastRequestTimes = [];

    /**
     * Constructor del cliente HTTP.
     *
     * @param string $baseUrl URL base de la API
     * @param string $apiKey Clave de API para autenticación
     * @param int $timeout Tiempo de espera en segundos
     */
    public function __construct(string $baseUrl, string $apiKey, int $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * Realiza una petición GET a la API.
     *
     * @param string $endpoint Endpoint a consultar
     * @param array $queryParams Parámetros de consulta opcionales
     * @return array Respuesta decodificada de la API
     * @throws ApiException Si la petición falla
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        $this->respectRateLimit();

        $url = $this->buildUrl($endpoint, $queryParams);
        $headers = $this->buildHeaders();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new ApiException("Error de conexión: {$error}");
        }

        return $this->handleResponse($response, $httpCode);
    }

    /**
     * Construye la URL completa con parámetros de consulta.
     *
     * @param string $endpoint Endpoint de la API
     * @param array $queryParams Parámetros de consulta
     * @return string URL completa
     */
    private function buildUrl(string $endpoint, array $queryParams): string
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Construye los encabezados HTTP necesarios.
     *
     * @return array Lista de encabezados
     */
    private function buildHeaders(): array
    {
        return [
            'X-Authorization: ' . $this->apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ];
    }

    /**
     * Maneja la respuesta de la API y lanza excepciones en caso de error.
     *
     * @param string $response Respuesta cruda de la API
     * @param int $httpCode Código de estado HTTP
     * @return array Respuesta decodificada
     * @throws ApiException Si hay un error en la respuesta
     */
    private function handleResponse(string $response, int $httpCode): array
    {
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Error al decodificar JSON: " . json_last_error_msg());
        }

        if ($httpCode >= 400) {
            $message = $decoded['message'] ?? $decoded['error'] ?? 'Error desconocido';
            throw new ApiException("Error HTTP {$httpCode}: {$message}", $httpCode);
        }

        return $decoded;
    }

    /**
     * Respeta el límite de tasa de 20 requests por segundo.
     */
    private function respectRateLimit(): void
    {
        $now = microtime(true);
        $oneSecondAgo = $now - 1.0;

        // Eliminar requests antiguos
        $this->lastRequestTimes = array_filter(
            $this->lastRequestTimes,
            fn($time) => $time > $oneSecondAgo
        );

        // Si alcanzamos el límite, esperar
        if (count($this->lastRequestTimes) >= self::RATE_LIMIT_PER_SECOND) {
            $oldestRequest = min($this->lastRequestTimes);
            $sleepTime = max(0, 1.0 - ($now - $oldestRequest));
            if ($sleepTime > 0) {
                usleep((int)($sleepTime * 1_000_000));
            }
        }

        $this->lastRequestTimes[] = microtime(true);
    }
}

/**
 * Excepción personalizada para errores de la API.
 */
class ApiException extends Exception
{
}