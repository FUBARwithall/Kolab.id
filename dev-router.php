<?php
/**
 * KOLAB.ID — Unified Development Server Router (PHP Built-in Server)
 *
 * Jalankan satu perintah dari root folder Kolab.id:
 *   php8.3 -S localhost:8087 dev-router.php
 *
 * Router ini otomatis menyalurkan:
 *   - /              -> landing/public/index.php (Public Web, Blog SSR, Hub Login)
 *   - /campaign/*    -> campaign/public/index.php (Campaign Management & Backoffice)
 *   - /datahub/*     -> datahub/public/index.php (KOL Data Hub, Scraper & Intelligence)
 *   - /KOL/*         -> campaign/public/index.php (Portal Creator)
 *   - /uploads/*     -> shared/uploads/
 *   - /asset/*       -> assets/adminlte/
 *   - /dist/*        -> landing/public/dist/
 *   - /assets/*      -> landing/public/assets/
 */

$repoRoot = __DIR__;
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Helper menyajikan file statis dengan Content-Type yang tepat
function serve_static_file(string $file): void
{
    if (!is_file($file)) {
        return;
    }
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'json'  => 'application/json; charset=UTF-8',
        'xml'   => 'application/xml; charset=UTF-8',
        'pdf'   => 'application/pdf',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// 1. Shared Uploads (/uploads/*)
if (strpos($uriPath, '/uploads/') === 0) {
    $subPath = substr($uriPath, strlen('/uploads/'));
    $uploadFile = $repoRoot . '/shared/uploads/' . $subPath;
    if (is_file($uploadFile)) {
        serve_static_file($uploadFile);
    }
}

// 2. AdminLTE Assets (/asset/* atau /assets/adminlte/*)
if (strpos($uriPath, '/asset/') === 0 || strpos($uriPath, '/assets/adminlte/') === 0) {
    $cleanPath = preg_replace('#^/(asset|assets/adminlte)/#', '/', $uriPath);
    $adminlteFile = $repoRoot . '/campaign/public/asset' . $cleanPath;
    if (is_file($adminlteFile)) {
        serve_static_file($adminlteFile);
    }
}

// 3. Landing Assets (/dist/* dan /assets/*)
if (strpos($uriPath, '/dist/') === 0) {
    $distFile = $repoRoot . '/landing/public' . $uriPath;
    if (is_file($distFile)) {
        serve_static_file($distFile);
    }
}
if (strpos($uriPath, '/assets/') === 0 && strpos($uriPath, '/assets/adminlte/') !== 0) {
    $assetFile = $repoRoot . '/landing/public' . $uriPath;
    if (is_file($assetFile)) {
        serve_static_file($assetFile);
    }
}

// 4. Favicon & Robots
if ($uriPath === '/favicon.ico' || $uriPath === '/robots.txt') {
    $rootStatic = $repoRoot . '/landing/public' . $uriPath;
    if (is_file($rootStatic)) {
        serve_static_file($rootStatic);
    }
}

// 5. Legacy Creator Route Redirect (/KOL/* atau /kol/* -> /datahub/dashboard)
if ($uriPath === '/KOL' || strpos($uriPath, '/KOL/') === 0 || $uriPath === '/kol' || strpos($uriPath, '/kol/') === 0) {
    header('Location: /datahub/dashboard');
    exit;
}

// 6. Sub-App: Campaign Management (/campaign/*)
if ($uriPath === '/campaign' || strpos($uriPath, '/campaign/') === 0) {
    if ($uriPath === '/campaign') {
        header('Location: /campaign/');
        exit;
    }
    // Jika ada aset statis langsung di dalam campaign/public
    $subPath = substr($uriPath, strlen('/campaign'));
    $campaignStatic = $repoRoot . '/campaign/public' . $subPath;
    if ($subPath !== '/' && is_file($campaignStatic)) {
        serve_static_file($campaignStatic);
    }

    $_SERVER['SCRIPT_NAME'] = '/campaign/index.php';
    $_SERVER['DOCUMENT_ROOT'] = $repoRoot . '/campaign/public';
    chdir($repoRoot . '/campaign/public');
    require $repoRoot . '/campaign/public/index.php';
    exit;
}

// 6. Sub-App: KOL Data Hub (/datahub/*)
if ($uriPath === '/datahub' || strpos($uriPath, '/datahub/') === 0) {
    if ($uriPath === '/datahub') {
        header('Location: /datahub/');
        exit;
    }
    $subPath = substr($uriPath, strlen('/datahub'));
    $datahubStatic = $repoRoot . '/datahub/public' . $subPath;
    if ($subPath !== '/' && is_file($datahubStatic)) {
        serve_static_file($datahubStatic);
    }

    $_SERVER['SCRIPT_NAME'] = '/datahub/index.php';
    $_SERVER['DOCUMENT_ROOT'] = $repoRoot . '/datahub/public';
    chdir($repoRoot . '/datahub/public');
    require $repoRoot . '/datahub/public/index.php';
    exit;
}

// 7. Sub-App Default: Landing Page & Blog Publik (/*)
$landingStatic = $repoRoot . '/landing/public' . $uriPath;
if ($uriPath !== '/' && is_file($landingStatic)) {
    serve_static_file($landingStatic);
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['DOCUMENT_ROOT'] = $repoRoot . '/landing/public';
chdir($repoRoot . '/landing/public');
require $repoRoot . '/landing/public/index.php';
