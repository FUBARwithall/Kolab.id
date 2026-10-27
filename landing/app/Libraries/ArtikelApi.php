<?php

namespace App\Libraries;

use App\Exceptions\ArtikelApiException;
use CodeIgniter\HTTP\CURLRequest;
use Config\Api as ApiConfig;

class ArtikelApi
{
    protected string $apiBase;
    protected int $maxSitemapPages;
    protected CURLRequest $http;

    public function __construct(?ApiConfig $config = null)
    {
        $config = $config ?? config(ApiConfig::class);

        $this->apiBase         = rtrim($config->articlesApiBase, '/');
        $this->maxSitemapPages = $config->maxSitemapPages;

        // Use CI4's built-in HTTP client (testable, configurable, PSR-compliant)
        $this->http = \Config\Services::curlrequest([
            'baseURI'    => $this->apiBase . '/',
            'timeout'    => $config->timeout,
            'connect_timeout' => $config->connectTimeout,
            'headers'    => ['Accept' => 'application/json'],
            'http_errors' => false, // Don't throw on 4xx/5xx — we handle manually
        ]);
    }

    /**
     * Execute HTTP GET request to the API feed.
     *
     * @param string $path   Relative API path (e.g. 'articles/feed')
     * @param array  $query  Query-string key-value pairs
     * @param bool   $allow404  If true, 404 returns null instead of throwing
     *
     * @throws ArtikelApiException
     */
    protected function request(string $path, array $query = [], bool $allow404 = false): ?object
    {
        $options = [];
        if (! empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->http->get(ltrim($path, '/'), $options);
        } catch (\Exception $e) {
            throw new ArtikelApiException("Koneksi API gagal: {$e->getMessage()}", 0);
        }

        $httpCode = $response->getStatusCode();

        if ($httpCode === 0) {
            throw new ArtikelApiException('Koneksi API gagal (HTTP 0)', 0);
        }

        if ($httpCode === 404 && $allow404) {
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new ArtikelApiException("API merespons status HTTP {$httpCode}", $httpCode);
        }

        $decoded = json_decode((string) $response->getBody(), false);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_object($decoded)) {
            throw new ArtikelApiException('Respons API bukan JSON yang valid');
        }

        if (empty($decoded->success) || $decoded->success !== true) {
            $msg = isset($decoded->message) ? (string) $decoded->message : 'Operasi API tidak berhasil';
            throw new ArtikelApiException($msg);
        }

        return $decoded;
    }

    // ---------------------------------------------------------------
    //  Public API methods (unchanged signatures)
    // ---------------------------------------------------------------

    /**
     * Ambil artikel terbaru untuk homepage.
     */
    public function latest(int $limit = 3): array
    {
        $res = $this->request('articles/feed', ['limit' => max(1, $limit)]);
        return (isset($res->data) && is_array($res->data)) ? $res->data : [];
    }

    /**
     * Ambil artikel terpaginasi dengan filter kategori dan search opsional.
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

        $res  = $this->request('articles/feed', $query);
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
     * Ambil artikel spesifik berdasarkan slug.
     */
    public function bySlug(string $slug): ?object
    {
        $slug = trim($slug);
        if ($slug === '' || ! preg_match('/^[a-z0-9\-]+$/i', $slug)) {
            return null;
        }

        $res = $this->request('articles/feed/' . rawurlencode($slug), [], true);
        if ($res === null || empty($res->data) || ! is_object($res->data)) {
            return null;
        }

        return $res->data;
    }

    /**
     * Backward-compatible alias for bySlug.
     */
    public function by_slug(string $slug): ?object
    {
        return $this->bySlug($slug);
    }

    /**
     * Ambil artikel terkait (exclude slug saat ini).
     */
    public function related(string $slug, int $limit = 3): array
    {
        try {
            $res  = $this->request('articles/feed', ['limit' => max(4, $limit + 1)]);
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
     * Ambil seluruh artikel publik untuk sitemap XML.
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

    /**
     * Backward-compatible alias for allForSitemap.
     */
    public function all_for_sitemap(): array
    {
        return $this->allForSitemap();
    }
}

