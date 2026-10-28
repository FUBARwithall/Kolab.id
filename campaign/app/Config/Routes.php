<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --------------------------------------------------------------------
// PUBLIC REST API: Articles Feed (Consumed by Landing Page)
// --------------------------------------------------------------------
$routes->get('articles/feed', 'ArticlesApi::feed');
$routes->get('articles/feed/(:segment)', 'ArticlesApi::feed/$1');

// --------------------------------------------------------------------
// AUTENTIKASI CAMPAIGN & KOL
// --------------------------------------------------------------------
$routes->get('login', 'Auth::loginCampaign');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('campaign/login', 'Auth::loginCampaign');
$routes->post('campaign/login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('campaign/logout', 'Auth::logout');

// Redirect legacy /kol or /KOL requests directly to Datahub
$routes->get('kol/login', static fn() => redirect()->to('/datahub/login'));
$routes->get('KOL/login', static fn() => redirect()->to('/datahub/login'));
$routes->get('kol', static fn() => redirect()->to('/datahub/dashboard'));
$routes->get('KOL', static fn() => redirect()->to('/datahub/dashboard'));
$routes->get('kol/(:any)', static fn() => redirect()->to('/datahub/dashboard'));
$routes->get('KOL/(:any)', static fn() => redirect()->to('/datahub/dashboard'));

// --------------------------------------------------------------------
// CAMPAIGN MANAGEMENT — AKSES OPERASIONAL (LEADER, STAFF & SUPERADMIN)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,leader,staff'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('campaign', 'Dashboard::index');
    $routes->get('campaign/dashboard', 'Dashboard::index');
    $routes->get('campaigns', 'Dashboard::index');
    $routes->get('briefs', 'Dashboard::index');
    $routes->get('drafts', 'Dashboard::index');
    $routes->get('shipments', 'Dashboard::index');
    $routes->get('postings', 'Dashboard::index');
    $routes->get('dealings', 'Dashboard::index');
    $routes->get('tasks', 'Dashboard::index');
    $routes->get('live-posting', 'Dashboard::index');
    $routes->get('calendar', 'Dashboard::index');
    $routes->get('products', 'Dashboard::index');
    $routes->get('stock-history', 'Dashboard::index');
    $routes->get('articles', 'Dashboard::index');
});

// --------------------------------------------------------------------
// CAMPAIGN MANAGEMENT — AKSES STRATEGIS, EVALUASI & KEUANGAN (LEADER & SUPERADMIN)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,leader'], static function ($routes) {
    $routes->get('rankings', 'Dashboard::index');
    $routes->get('evaluations', 'Dashboard::index');
    $routes->get('finance', 'Dashboard::index');
    $routes->get('invoices', 'Dashboard::index');
    $routes->get('contracts', 'Dashboard::index');
    $routes->get('payments', 'Dashboard::index');
    $routes->get('budget', 'Dashboard::index');
    $routes->get('approvals', 'Dashboard::index');
});
