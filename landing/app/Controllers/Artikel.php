<?php

namespace App\Controllers;

use App\Exceptions\ArtikelApiException;
use App\Libraries\ArtikelApi;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Artikel extends BaseController
{
    protected ArtikelApi $artikelApi;

    public function __construct()
    {
        $this->artikelApi = new ArtikelApi();
    }

    public function index(): string|RedirectResponse
    {
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        $search   = trim((string) ($this->request->getGet('q') ?? ''));
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $page     = $page > 0 ? $page : 1;
        $perPage  = 9;

        try {
            $result = $this->artikelApi->paginate($perPage, $page, $category, $search);
        } catch (ArtikelApiException $e) {
            return $this->renderOffline();
        }

        $total = (int) $result->meta->total;
        $pages = max(1, (int) $result->meta->total_pages);

        if ($page > $pages && $total > 0) {
            $redirectParams = [];
            if ($category !== '') {
                $redirectParams['category'] = $category;
            }
            if ($search !== '') {
                $redirectParams['q'] = $search;
            }
            $redirectUrl = ! empty($redirectParams) ? 'artikel?' . http_build_query($redirectParams) : 'artikel';
            return redirect()->to($redirectUrl);
        }

        if (ENVIRONMENT === 'production') {
            $this->cachePage(600);
        }

        $title = 'Artikel & Insight — KOLAB.ID';
        if ($category !== '' && $search !== '') {
            $title = 'Pencarian "' . $search . '" di ' . $category . ' — KOLAB.ID';
        } elseif ($search !== '') {
            $title = 'Pencarian "' . $search . '" — KOLAB.ID';
        } elseif ($category !== '') {
            $title = 'Artikel ' . $category . ' — KOLAB.ID';
        }
        if ($page > 1) {
            $title .= ' (Halaman ' . $page . ')';
        }

        $canonicalParams = [];
        if ($category !== '') {
            $canonicalParams['category'] = $category;
        }
        if ($search !== '') {
            $canonicalParams['q'] = $search;
        }
        $canonical = ! empty($canonicalParams)
            ? base_url('artikel?' . http_build_query($canonicalParams))
            : base_url('artikel');

        $data = [
            'active'           => 'artikel',
            'meta_title'       => $title,
            'meta_description' => 'Panduan, riset, dan praktik terbaik seputar influencer marketing, KOL, dan kampanye digital dari KOLAB.ID.',
            'og_type'          => 'website',
            'og_image'         => base_url('assets/images/hero-mockup.png'),
            'canonical'        => $canonical,
            'artikel'          => $result->data,
            'categories'       => $result->meta->categories ?? [],
            'active_category'  => $category,
            'search'           => $search,
            'page'             => $page,
            'total_pages'      => $pages,
            'total'            => $total,
        ];

        return view('layout/header', $data)
            . view('artikel/index', $data)
            . view('layout/footer', $data);
    }

    public function detail(?string $slug = null): string
    {
        $slug = trim((string) $slug);

        if ($slug === '' || ! preg_match('/^[a-z0-9\-]+$/i', $slug)) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            $artikel = $this->artikelApi->bySlug($slug);
        } catch (ArtikelApiException $e) {
            return $this->renderOffline();
        }

        if (! $artikel) {
            throw PageNotFoundException::forPageNotFound();
        }

        $related      = $this->artikelApi->related($artikel->slug, 3);
        $cover        = ! empty($artikel->cover_image_url) ? $artikel->cover_image_url : null;
        $author       = ! empty($artikel->author) ? $artikel->author : 'Admin';
        $dateLabel    = ! empty($artikel->published_at_label) ? $artikel->published_at_label : '';
        $readingTime  = isset($artikel->reading_time) ? (int) $artikel->reading_time : 1;
        $tags         = isset($artikel->tags) && is_array($artikel->tags) ? $artikel->tags : [];
        $publishedIso = ! empty($artikel->published_at) ? date('c', strtotime($artikel->published_at)) : date('c');

        if (ENVIRONMENT === 'production') {
            $this->cachePage(600);
        }

        $data = [
            'active'           => 'artikel',
            'meta_title'       => ! empty($artikel->meta_title) ? $artikel->meta_title : $artikel->title . ' — KOLAB.ID',
            'meta_description' => ! empty($artikel->meta_description) ? $artikel->meta_description : (string) $artikel->excerpt,
            'og_type'          => 'article',
            'og_image'         => $cover ? $cover : base_url('assets/images/hero-mockup.png'),
            'canonical'        => base_url('artikel/' . $artikel->slug),
            'artikel'          => $artikel,
            'cover'            => $cover,
            'content'          => $artikel->content ?? '',
            'tags'             => $tags,
            'date_label'       => $dateLabel,
            'reading_time'     => $readingTime,
            'related'          => $related,
            'json_ld'          => json_encode([
                '@context'         => 'https://schema.org',
                '@type'            => 'Article',
                'headline'         => $artikel->title,
                'description'      => ! empty($artikel->meta_description) ? $artikel->meta_description : (string) $artikel->excerpt,
                'image'            => $cover ? [$cover] : [base_url('assets/images/hero-mockup.png')],
                'datePublished'    => $publishedIso,
                'dateModified'     => $publishedIso,
                'author'           => [
                    '@type' => 'Person',
                    'name'  => $author,
                ],
                'publisher'        => [
                    '@type' => 'Organization',
                    'name'  => 'KOLAB.ID',
                    'logo'  => [
                        '@type' => 'ImageObject',
                        'url'   => base_url('assets/images/kolab-logo.png'),
                    ],
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id'   => base_url('artikel/' . $artikel->slug),
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        return view('layout/header', $data)
            . view('artikel/detail', $data)
            . view('layout/footer', $data);
    }
}
