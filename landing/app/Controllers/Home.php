<?php

namespace App\Controllers;

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
            $latest = $this->artikelApi->latest(3);
            if (ENVIRONMENT === 'production') {
                $this->cachePage(600);
            }
        } catch (ArtikelApiException $e) {
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

        return view('layout/header', $data)
            . view('home/index', $data)
            . view('layout/footer', $data);
    }

    public function sitemap(): ResponseInterface
    {
        try {
            $articles = $this->artikelApi->allForSitemap();
        } catch (ArtikelApiException $e) {
            return $this->response
                ->setStatusCode(503)
                ->setHeader('Retry-After', '60')
                ->setContentType('text/plain', 'utf-8')
                ->setBody('Sitemap temporarily unavailable');
        }

        if (ENVIRONMENT === 'production') {
            $this->cachePage(3600);
        }

        $urls = [
            ['loc' => base_url('/'), 'lastmod' => date('c'), 'priority' => '1.0'],
            ['loc' => base_url('artikel'), 'lastmod' => date('c'), 'priority' => '0.8'],
        ];

        foreach ($articles as $row) {
            $lastmod = ! empty($row->published_at) ? date('c', strtotime($row->published_at)) : date('c');
            $urls[] = [
                'loc'      => base_url('artikel/' . $row->slug),
                'lastmod'  => $lastmod,
                'priority' => '0.6',
            ];
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
