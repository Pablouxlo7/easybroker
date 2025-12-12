<?php

declare(strict_types=1);

namespace EasyBroker\Api;

use Exception;

class HttpClient
{
    private const DEFAULT_TIMEOUT = 30;
    private const RATE_LIMIT_PER_SECOND = 20;

    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private array $lastRequestTimes = [];

    /**
     * @param string $baseUrl
     * @param string $apiKey
     * @param int $timeout
     */
    public function __construct(string $baseUrl, string $apiKey, int $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * Make a GET request to the API.
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return array
     * @throws ApiException
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
            throw new ApiException("Connection error: {$error}");
        }

        return $this->handleResponse($response, $httpCode);
    }

    /**
     * Build the complete URL with query parameters.
     *
     * @param string $endpoint
     * @param array $queryParams
     * @return string
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
     * @return array Headers
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
     * It handles the API response and throws exceptions in case of error.
     *
     * @param string $response
     * @param int $httpCode
     * @return array
     * @throws ApiException
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
     * Respect the rate limit of 20 requests per second.
     */
    private function respectRateLimit(): void
    {
        $now = microtime(true);
        $oneSecondAgo = $now - 1.0;

        $this->lastRequestTimes = array_filter(
            $this->lastRequestTimes,
            fn($time) => $time > $oneSecondAgo
        );

        //If we reach the limit, wait
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
 * Custom exception for API errors.
 */
class ApiException extends Exception
{
}