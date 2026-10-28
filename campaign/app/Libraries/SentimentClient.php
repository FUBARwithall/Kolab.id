<?php

namespace App\Libraries;

/**
 * SentimentClient
 *
 * HTTP client library for the KOL Sentiment Analysis Microservice.
 * Performs IndoBERT-based sentiment analysis on TikTok, Instagram, and
 * YouTube post comments.
 */
class SentimentClient
{
    protected string $baseUrl;
    protected string $bearerToken;
    protected int $connectTimeout;
    protected int $timeout;
    protected int $commentLimit;

    protected array $platformMap = [
        'TIKTOK'    => 'tiktok',
        'INSTAGRAM' => 'instagram',
        'YOUTUBE'   => 'youtube',
        'IG'        => 'instagram',
        'YT'        => 'youtube',
        'TT'        => 'tiktok',
    ];

    protected array $domainMap = [
        'tiktok.com'    => 'tiktok',
        'instagram.com' => 'instagram',
        'youtube.com'   => 'youtube',
        'youtu.be'      => 'youtube',
    ];

    public function __construct()
    {
        $this->baseUrl        = rtrim(env('sentiment.baseUrl', 'https://precaudal-intraspinal-russel.ngrok-free.dev'), '/');
        $this->bearerToken    = (string) env('sentiment.bearerToken', '');
        $this->connectTimeout = (int) env('sentiment.connectTimeout', 10);
        $this->timeout         = (int) env('sentiment.timeout', 60);
        $this->commentLimit   = (int) env('sentiment.commentLimit', 300);
    }

    /**
     * Check if microservice is healthy
     */
    public function checkHealth(): array
    {
        $res = $this->request('/health', 'GET');
        if (!$res['success']) {
            return [
                'healthy'      => false,
                'model_loaded' => false,
                'error'        => $res['error'],
            ];
        }

        $data = $res['data'] ?? [];
        return [
            'healthy'      => isset($data['status']) && $data['status'] === 'healthy',
            'model_loaded' => isset($data['model_loaded']) && $data['model_loaded'] === true,
            'error'        => null,
        ];
    }

    /**
     * Resolve platform code or url domain
     */
    public function resolvePlatform(?string $platformCode = null, ?string $url = null): ?string
    {
        if (!empty($platformCode)) {
            $upper = strtoupper(trim($platformCode));
            if (isset($this->platformMap[$upper])) {
                return $this->platformMap[$upper];
            }
        }

        if (!empty($url)) {
            $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
            $host = preg_replace('/^www\./', '', $host);
            if (isset($this->domainMap[$host])) {
                return $this->domainMap[$host];
            }
        }

        return 'tiktok';
    }

    /**
     * Trigger IndoBERT sentiment analysis
     */
    public function analyze(string $platform, string $url, ?int $limit = null): array
    {
        if (empty($platform) || empty($url)) {
            return [
                'success'   => false,
                'http_code' => 0,
                'data'      => null,
                'error'     => 'Platform dan URL live posting tidak boleh kosong.',
            ];
        }

        $payload = [
            'platform' => $platform,
            'url'      => $url,
            'limit'    => (int) ($limit ?: $this->commentLimit),
        ];

        $res = $this->request('/api/v1/analyze', 'POST', $payload);
        if (!$res['success']) {
            return $res;
        }

        $body = $res['data'];
        if (isset($body['data'])) {
            $res['data'] = $body['data'];
        }

        return $res;
    }

    /**
     * Helper curl executor
     */
    protected function request(string $endpoint, string $method = 'GET', ?array $data = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch  = curl_init();

        $headers = [
            'Accept: application/json',
            'ngrok-skip-browser-warning: 1',
        ];

        if (!empty($this->bearerToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->bearerToken;
        }

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'KOLAB.ID SentimentClient/2.0',
        ];

        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $jsonPayload = json_encode($data ?? []);
            $options[CURLOPT_POSTFIELDS] = $jsonPayload;
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($jsonPayload);
        }

        $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $options);

        $response  = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'data'      => null,
                'error'     => 'Koneksi ke Microservice Sentimen Gagal: ' . $curlError,
            ];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success'   => true,
                'http_code' => $httpCode,
                'data'      => $decoded,
                'error'     => null,
            ];
        }

        $errorMsg = $decoded['message'] ?? $decoded['detail'] ?? ('HTTP Error ' . $httpCode);
        return [
            'success'   => false,
            'http_code' => $httpCode,
            'data'      => $decoded,
            'error'     => is_array($errorMsg) ? json_encode($errorMsg) : (string)$errorMsg,
        ];
    }
}
