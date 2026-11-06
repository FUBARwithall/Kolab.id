<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Proxy extends BaseController
{
    /**
     * Avatar image proxy to prevent CORS issues during card generation,
     * html2canvas capture, and PDF export.
     */
    public function avatar()
    {
        $url = $this->request->getGet('url');

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $url)) {
            return $this->returnFallbackPixel();
        }

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        $imageData   = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($imageData)) {
            // Default to image/jpeg if Content-Type is missing
            $mime = !empty($contentType) ? explode(';', $contentType)[0] : 'image/jpeg';

            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Cache-Control', 'public, max-age=86400')
                ->setHeader('Access-Control-Allow-Origin', '*')
                ->setBody($imageData);
        }

        return $this->returnFallbackPixel();
    }

    /**
     * Return 1x1 transparent PNG fallback
     */
    protected function returnFallbackPixel(): ResponseInterface
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody($pixel);
    }
}

