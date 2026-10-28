<?php

namespace App\Libraries;

use App\Exceptions\ArtikelApiException;
use Config\Api as ApiConfig;

class ArtikelApi
{
    protected string $apiBase;
    protected int $maxSitemapPages;

    public function __construct(?ApiConfig $config = null)
    {
        $config = $config ?? config(ApiConfig::class);

        $base = $config->articlesApiBase ?? '';
        if ($base === '' || $base === 'http://localhost:8000') {
            $base = base_url('campaign');
        }

        $this->apiBase         = rtrim($base, '/');
        $this->maxSitemapPages = $config->maxSitemapPages ?? 40;
    }

    /**
     * Execute API request to Campaign articles feed
     */
    protected function request(string $path, array $query = [], bool $allow404 = false): ?object
    {
        $url = $this->apiBase . '/' . ltrim($path, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        // Single instance / php -S: call Campaign ArticlesApi directly to bypass local cURL single-thread blocking
        if (php_sapi_name() === 'cli-server' || strpos($url, base_url()) === 0) {
            $slug = null;
            if (strpos($path, 'articles/feed/') === 0) {
                $slug = substr($path, strlen('articles/feed/'));
            }

            $_GET = array_merge($_GET, $query);
            $campaignApiFile = dirname(ROOTPATH) . '/campaign/app/Controllers/ArticlesApi.php';
            if (is_file($campaignApiFile) && !class_exists(\App\Controllers\ArticlesApi::class)) {
                require_once $campaignApiFile;
            }

            $internalResponse = new \CodeIgniter\HTTP\Response(config(\Config\App::class));
            $apiController = new \App\Controllers\ArticlesApi();
            $apiController->initController(service('request'), $internalResponse, service('logger'));

            $response = $apiController->feed($slug);
            $httpCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($httpCode === 404 && $allow404) {
                return null;
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                throw new ArtikelApiException("API merespons status HTTP {$httpCode}", $httpCode);
            }

            $decoded = json_decode($body, false);
            if (json_last_error() !== JSON_ERROR_NONE || !is_object($decoded)) {
                throw new ArtikelApiException('Respons API bukan JSON yang valid');
            }

            if (empty($decoded->success) || $decoded->success !== true) {
                $msg = isset($decoded->message) ? (string) $decoded->message : 'Operasi API tidak berhasil';
                throw new ArtikelApiException($msg);
            }

            return $decoded;
        }

        // Standard HTTP cURL request (for cPanel production with separate domain/docroot or async worker)
        $client = \Config\Services::curlrequest([
            'timeout'         => 5,
            'connect_timeout' => 2,
            'headers'         => ['Accept' => 'application/json'],
            'http_errors'     => false,
        ]);

        try {
            $response = $client->get($url);
        } catch (\Exception $e) {
            throw new ArtikelApiException("Koneksi API gagal: {$e->getMessage()}", 0);
        }

        $httpCode = $response->getStatusCode();
        if ($httpCode === 404 && $allow404) {
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new ArtikelApiException("API merespons status HTTP {$httpCode}", $httpCode);
        }

        $decoded = json_decode((string) $response->getBody(), false);
        if (json_last_error() !== JSON_ERROR_NONE || !is_object($decoded)) {
            throw new ArtikelApiException('Respons API bukan JSON yang valid');
        }

        if (empty($decoded->success) || $decoded->success !== true) {
            $msg = isset($decoded->message) ? (string) $decoded->message : 'Operasi API tidak berhasil';
            throw new ArtikelApiException($msg);
        }

        return $decoded;
    }

    /**
     * Get latest articles for homepage
     */
    public function latest(int $limit = 3): array
    {
        $res = $this->request('articles/feed', ['limit' => max(1, $limit)]);
        return (isset($res->data) && is_array($res->data)) ? $res->data : [];
    }

    /**
     * Get paginated articles with optional category and search filters
     */
    public function paginate(int $limit, int $page, string $category = '', string $search = ''): object
    {
        $query = [
            'limit' => max(1, $limit),
            'page'  => max(1, $page),
        ];

        if ($category !== '') {
            $query['category'] = $category;
        }

        if ($search !== '') {
            $query['q'] = $search;
        }

        $res = $this->request('articles/feed', $query);

        $data = (isset($res->data) && is_array($res->data)) ? $res->data : [];
        $meta = isset($res->meta) && is_object($res->meta) ? $res->meta : (object) [
            'page'        => $query['page'],
            'per_page'    => $query['limit'],
            'total'       => count($data),
            'total_pages' => 1,
            'categories'  => [],
        ];

        return (object) [
            'data' => $data,
            'meta' => $meta,
        ];
    }

    /**
     * Get single article by slug
     */
    public function bySlug(string $slug): ?object
    {
        $slug = trim($slug);
        if ($slug === '' || !preg_match('/^[a-z0-9\-]+$/i', $slug)) {
            return null;
        }

        $res = $this->request('articles/feed/' . rawurlencode($slug), [], true);
        if ($res === null || empty($res->data) || !is_object($res->data)) {
            return null;
        }

        return $res->data;
    }

    /**
     * Get related articles
     */
    public function related(string $slug, int $limit = 3): array
    {
        try {
            $res = $this->request('articles/feed', ['limit' => max(4, $limit + 1)]);
            $rows = (isset($res->data) && is_array($res->data)) ? $res->data : [];
        } catch (ArtikelApiException $e) {
            return [];
        }

        $filtered = [];
        foreach ($rows as $item) {
            if (isset($item->slug) && $item->slug === $slug) {
                continue;
            }
            $filtered[] = $item;
            if (count($filtered) >= $limit) {
                break;
            }
        }

        return $filtered;
    }

    /**
     * Get all articles for XML sitemap
     */
    public function allForSitemap(): array
    {
        $firstPage = $this->request('articles/feed', ['limit' => 50, 'page' => 1]);
        $articles  = (isset($firstPage->data) && is_array($firstPage->data)) ? $firstPage->data : [];

        $totalPages = 1;
        if (isset($firstPage->meta->total_pages)) {
            $totalPages = min((int) $firstPage->meta->total_pages, $this->maxSitemapPages);
        }

        for ($p = 2; $p <= $totalPages; $p++) {
            $next = $this->request('articles/feed', ['limit' => 50, 'page' => $p]);
            if (isset($next->data) && is_array($next->data)) {
                $articles = array_merge($articles, $next->data);
            }
        }

        return $articles;
    }
}
