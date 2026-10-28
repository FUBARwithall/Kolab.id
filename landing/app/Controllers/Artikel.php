<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Exceptions\ArtikelApiException;
use App\Libraries\ArtikelApi;
use CodeIgniter\Exceptions\PageNotFoundException;

class Artikel extends BaseController
{
    protected ArtikelApi $artikelApi;

    public function __construct()
    {
        $this->artikelApi = new ArtikelApi();
    }

    public function index()
    {
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $search   = trim((string) ($this->request->getGet('q') ?? ''));
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $page     = $page > 0 ? $page : 1;
        $perPage  = 9;

        try {
            // Ambil data lewat REST API /campaign/articles/feed
            $result     = $this->artikelApi->paginate($perPage, $page, $category, $search);
            $articles   = $result->data ?? [];
            $meta       = $result->meta ?? (object) [];
            $categories = $meta->categories ?? [];
        } catch (ArtikelApiException $e) {
            log_message('error', 'Gagal memanggil API feed artikel: ' . $e->getMessage());
            $articles   = [];
            $meta       = (object) ['page' => 1, 'per_page' => $perPage, 'total' => 0, 'total_pages' => 1];
            $categories = [];
        }

        $totalPages = $meta->total_pages ?? 1;

        $data = [
            'active'          => 'artikel',
            'meta_title'      => 'Artikel & Insight — KOLAB.ID',
            'meta_description'=> 'Insight, panduan, dan data tren influencer marketing untuk brand dan agensi.',
            'og_type'         => 'website',
            'og_image'        => base_url('assets/images/hero-mockup.png'),
            'canonical'       => base_url('artikel'),
            'artikel'         => $articles,
            'articles'        => $articles,
            'meta'            => $meta,
            'active_category' => $category,
            'currentCategory' => $category,
            'categories'      => $categories,
            'search'          => $search,
            'page'            => $page,
            'total_pages'     => $totalPages,
        ];

        $this->response->setContentType('text/html');

        return view('layout/header', $data)
            . view('artikel/index', $data)
            . view('layout/footer', $data);
    }

    public function detail(string $slug)
    {
        try {
            // Ambil data detail artikel lewat REST API /campaign/articles/feed/{slug}
            $article = $this->artikelApi->bySlug($slug);
        } catch (ArtikelApiException $e) {
            log_message('error', 'Gagal memanggil API detail artikel: ' . $e->getMessage());
            $article = null;
        }

        if (!$article) {
            throw PageNotFoundException::forPageNotFound('Artikel tidak ditemukan: ' . $slug);
        }

        $related = [];
        try {
            $related = $this->artikelApi->related($slug, 3);
        } catch (\Throwable $e) {
            $related = [];
        }

        $title       = is_object($article) ? $article->title : $article['title'];
        $excerpt     = is_object($article) ? ($article->excerpt ?? '') : ($article['excerpt'] ?? '');
        $cover       = is_object($article) ? ($article->cover_image_url ?? '') : ($article['cover_image_url'] ?? '');
        $publishedAt = is_object($article) ? ($article->published_at ?? '') : ($article['published_at'] ?? '');
        $dateLabel   = (!empty($publishedAt) && strtotime($publishedAt)) ? date('d F Y', strtotime($publishedAt)) : '';

        $content     = is_object($article) ? ($article->content ?? '') : ($article['content'] ?? '');
        $tags        = is_object($article) ? ($article->tags ?? []) : ($article['tags'] ?? []);

        $data = [
            'active'           => 'artikel',
            'meta_title'       => esc($title) . ' — KOLAB.ID',
            'meta_description' => esc($excerpt),
            'og_type'          => 'article',
            'og_image'         => !empty($cover) ? $cover : base_url('assets/images/hero-mockup.png'),
            'canonical'        => base_url('artikel/' . $slug),
            'artikel'          => $article,
            'article'          => $article,
            'cover'            => $cover,
            'content'          => $content,
            'tags'             => $tags,
            'date_label'       => $dateLabel,
            'related'          => $related,
        ];

        $this->response->setContentType('text/html');

        return view('layout/header', $data)
            . view('artikel/detail', $data)
            . view('layout/footer', $data);
    }
}
