<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Api extends BaseConfig
{
    /**
     * Base URL endpoint for Campaign REST API.
     * Override via .env: landing.apiBase = 'https://app.kolab.id'
     */
    public string $articlesApiBase = 'http://localhost:8000';

    /**
     * Timeout for cURL connection in seconds.
     * Override via .env: landing.apiConnectTimeout = 2
     */
    public int $connectTimeout = 2;

    /**
     * Timeout for total cURL execution in seconds.
     * Override via .env: landing.apiTimeout = 5
     */
    public int $timeout = 5;

    /**
     * Maximum pages of articles to fetch for XML sitemap.
     * Override via .env: landing.apiMaxSitemapPages = 40
     */
    public int $maxSitemapPages = 40;

    public function __construct()
    {
        parent::__construct();

        // CI4 best practice: env() reads from .env, with typed defaults
        $this->articlesApiBase = rtrim(
            (string) env('landing.apiBase', $this->articlesApiBase),
            '/'
        );
        $this->connectTimeout   = (int) env('landing.apiConnectTimeout', $this->connectTimeout);
        $this->timeout          = (int) env('landing.apiTimeout', $this->timeout);
        $this->maxSitemapPages  = (int) env('landing.apiMaxSitemapPages', $this->maxSitemapPages);
    }
}
