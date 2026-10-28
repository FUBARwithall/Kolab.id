<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ArticlesApi extends BaseController
{
    /**
     * Entry point for /campaign/articles/feed and /campaign/articles/feed/{slug}
     */
    public function feed(?string $slug = null): ResponseInterface
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept');

        if ($this->request->getMethod() === 'OPTIONS') {
            return $this->response->setStatusCode(204);
        }

        try {
            if ($slug !== null && $slug !== '') {
                return $this->detail($slug);
            }

            return $this->listing();
        } catch (\Throwable $e) {
            log_message('error', 'Articles API Exception: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Gagal memuat feed artikel: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get paginated articles listing
     */
    protected function listing(): ResponseInterface
    {
        $db = \Config\Database::connect();

        $limit    = (int) ($this->request->getGet('limit') ?? 6);
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $search   = trim((string) ($this->request->getGet('q') ?? ''));

        $limit  = $limit > 0 ? min($limit, 50) : 6;
        $page   = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $limit;

        // Base query builder
        $builder = $db->table('articles')
            ->where('status', 'PUBLISHED');

        if ($category !== '') {
            $builder->like('category', $category);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('excerpt', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);

        $builder->orderBy('published_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset);

        $rows = $builder->get()->getResultArray();

        $articles = [];
        foreach ($rows as $row) {
            $articles[] = $this->formatPayload($row, false);
        }

        // Get category frequencies
        $catRows = $db->table('articles')
            ->select('category')
            ->where('status', 'PUBLISHED')
            ->where('category IS NOT NULL')
            ->get()
            ->getResultArray();

        $catCounts = [];
        foreach ($catRows as $cRow) {
            $parts = explode(',', $cRow['category']);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $catCounts[$p] = ($catCounts[$p] ?? 0) + 1;
                }
            }
        }
        arsort($catCounts);
        $categoriesList = array_keys($catCounts);

        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 1;

        return $this->response->setJSON([
            'success' => true,
            'data'    => $articles,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => max(1, $totalPages),
                'categories'  => $categoriesList,
            ],
        ]);
    }

    /**
     * Get single article detail by slug
     */
    protected function detail(string $slug): ResponseInterface
    {
        $db = \Config\Database::connect();

        $row = $db->table('articles')
            ->where('slug', $slug)
            ->where('status', 'PUBLISHED')
            ->get()
            ->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Artikel tidak ditemukan.',
            ]);
        }

        // Increment views
        try {
            $db->table('articles')
                ->where('id', $row['id'])
                ->increment('views', 1);
            $row['views'] = ($row['views'] ?? 0) + 1;
        } catch (\Throwable $e) {
            // Non-blocking view increment
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $this->formatPayload($row, true),
        ]);
    }

    /**
     * Format payload to match Landing Page expectations
     */
    protected function formatPayload(array $row, bool $withContent = false): object
    {
        $coverImage = $row['cover_image'] ?? $row['featured_image'] ?? null;
        $coverUrl = '';
        if (!empty($coverImage)) {
            if (strpos($coverImage, 'http://') === 0 || strpos($coverImage, 'https://') === 0) {
                $coverUrl = $coverImage;
            } elseif (strpos($coverImage, 'uploads/') === 0) {
                $coverUrl = base_url(ltrim($coverImage, '/'));
            } else {
                $coverUrl = base_url('uploads/campaign/articles/' . ltrim($coverImage, '/'));
            }
        } else {
            $coverUrl = base_url('assets/images/hero-mockup.png');
        }

        $tags = [];
        if (!empty($row['tags'])) {
            $tags = array_map('trim', explode(',', $row['tags']));
        }

        $payload = [
            'id'              => (int) $row['id'],
            'title'           => $row['title'],
            'slug'            => $row['slug'],
            'excerpt'         => $row['excerpt'] ?? '',
            'cover_image_url' => $coverUrl,
            'category'        => $row['category'] ?? 'Umum',
            'tags'            => $tags,
            'author_name'     => $row['author_name'] ?? 'Tim Kolab',
            'is_featured'     => (int) ($row['is_featured'] ?? 0),
            'views'           => (int) ($row['views'] ?? 0),
            'published_at'    => $row['published_at'] ?? $row['created_at'],
            'created_at'      => $row['created_at'],
        ];

        if ($withContent) {
            $payload['content'] = $row['content'];
        }

        return (object) $payload;
    }
}
