<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Exceptions\ArtikelApiException;
use App\Libraries\ArtikelApi;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    protected ArtikelApi $artikelApi;

    public function __construct()
    {
        $this->artikelApi = new ArtikelApi();
    }

    public function index(): string
    {
        $latest = [];
        try {
            // Ambil feed artikel lewat API Campaign
            $latest = $this->artikelApi->latest(3);
        } catch (ArtikelApiException $e) {
            log_message('error', 'Gagal memanggil API feed artikel untuk homepage: ' . $e->getMessage());
            $latest = [];
        }

        $data = [
            'active'           => 'home',
            'meta_title'       => 'KOLAB.ID — Kelola Seluruh Campaign KOL dalam Satu Platform',
            'meta_description' => 'Sistem orkestrasi & analitik influencer marketing enterprise: database kreator, campaign, approval, dan performa dalam satu sistem terintegrasi.',
            'og_type'          => 'website',
            'og_image'         => base_url('assets/images/hero-mockup.png'),
            'canonical'        => base_url('/'),
            'latest'           => $latest,
        ];

        $this->response->setContentType('text/html');

        return view('layout/header', $data)
            . view('home/index', $data)
            . view('layout/footer', $data);
    }

    public function sitemap(): ResponseInterface
    {
        try {
            $articles = $this->artikelApi->allForSitemap();
        } catch (ArtikelApiException $e) {
            log_message('error', 'Gagal memanggil API sitemap artikel: ' . $e->getMessage());
            $articles = [];
        }

        $urls = [
            ['loc' => base_url('/'), 'lastmod' => date('c'), 'priority' => '1.0'],
            ['loc' => base_url('artikel'), 'lastmod' => date('c'), 'priority' => '0.8'],
        ];

        foreach ($articles as $row) {
            $slug = is_object($row) ? $row->slug : ($row['slug'] ?? '');
            $publishedAt = is_object($row) ? ($row->published_at ?? null) : ($row['published_at'] ?? null);
            $lastmod = !empty($publishedAt) ? date('c', strtotime($publishedAt)) : date('c');

            if ($slug !== '') {
                $urls[] = [
                    'loc'      => base_url('artikel/' . $slug),
                    'lastmod'  => $lastmod,
                    'priority' => '0.6',
                ];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . esc($url['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $this->response
            ->setContentType('application/xml', 'utf-8')
            ->setBody($xml);
    }
}
