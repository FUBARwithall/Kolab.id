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
    // Wave 1: Master Produk & Mutasi Stok Sampel
    $routes->get('products', 'Products::index');
    $routes->post('products/get-datatable', 'Products::getDatatable');
    $routes->get('products/get-one/(:num)', 'Products::getOne/$1');
    $routes->post('products/save', 'Products::save');
    $routes->post('products/adjust-stock', 'Products::adjustStock');
    $routes->post('products/delete/(:num)', 'Products::delete/$1');
    $routes->get('stock-history', 'StockHistory::index');

    // Wave 2: Perencanaan Kampanye & Penugasan KOL
    $routes->get('campaigns', 'Campaigns::index');
    $routes->post('campaigns/get-datatable', 'Campaigns::getDatatable');
    $routes->get('campaigns/get-one/(:num)', 'Campaigns::getOne/$1');
    $routes->get('campaigns/view/(:num)', 'Campaigns::view/$1');
    $routes->post('campaigns/save', 'Campaigns::save');
    $routes->post('campaigns/delete/(:num)', 'Campaigns::delete/$1');
    $routes->post('campaign-kols/assign', 'CampaignKols::assign');
    $routes->get('campaign-kols/tasks-json/(:num)', 'CampaignKols::getTasksJson/$1');
    $routes->post('campaign-kols/remove/(:num)', 'CampaignKols::remove/$1');

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
