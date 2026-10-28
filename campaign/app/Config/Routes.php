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

    // Wave 3: Tasks Engine, Briefs, Drafts & Logistik Pengiriman Sampel
    $routes->get('tasks', 'Tasks::index');
    $routes->post('tasks/get-datatable', 'Tasks::getDatatable');
    $routes->get('tasks/get-one/(:num)', 'Tasks::getOne/$1');
    $routes->post('tasks/update-status/(:num)', 'Tasks::updateStatus/$1');
    $routes->post('tasks/set-schedule/(:num)', 'Tasks::setSchedule/$1');
    $routes->post('tasks/delete/(:num)', 'Tasks::delete/$1');

    $routes->get('briefs', 'Briefs::index');
    $routes->post('briefs/get-datatable', 'Briefs::getDatatable');
    $routes->get('briefs/get-one/(:num)', 'Briefs::getOne/$1');
    $routes->post('briefs/save', 'Briefs::save');
    $routes->post('briefs/approve/(:num)', 'Briefs::approve/$1');
    $routes->post('briefs/revision/(:num)', 'Briefs::revision/$1');

    $routes->get('drafts', 'Drafts::index');
    $routes->post('drafts/get-datatable', 'Drafts::getDatatable');
    $routes->get('drafts/get-one/(:num)', 'Drafts::getOne/$1');
    $routes->post('drafts/submit', 'Drafts::submit');
    $routes->post('drafts/approve/(:num)', 'Drafts::approve/$1');
    $routes->post('drafts/revision/(:num)', 'Drafts::revision/$1');

    $routes->get('shipments', 'Shipments::index');
    $routes->post('shipments/get-datatable', 'Shipments::getDatatable');
    $routes->get('shipments/get-one/(:num)', 'Shipments::getOne/$1');
    $routes->post('shipments/save', 'Shipments::save');
    $routes->post('shipments/update-status/(:num)', 'Shipments::updateStatus/$1');

    // Wave 4: Postings, Metrics, Sentiment & Kalender
    $routes->get('postings', 'Postings::index');
    $routes->post('postings/get-datatable', 'Postings::getDatatable');
    $routes->get('postings/get-one/(:num)', 'Postings::getOne/$1');
    $routes->post('postings/save', 'Postings::save');
    $routes->post('postings/update-metrics/(:num)', 'Postings::updateMetrics/$1');
    $routes->post('postings/analyze-sentiment/(:num)', 'Postings::analyzeSentiment/$1');

    $routes->get('kalender', 'Kalender::index');
    $routes->get('calendar', 'Kalender::index');
    $routes->get('kalender/get-events', 'Kalender::getEvents');

    $routes->get('articles', 'Dashboard::index');
});

// --------------------------------------------------------------------
// CAMPAIGN MANAGEMENT — AKSES STRATEGIS, EVALUASI & KEUANGAN (LEADER & SUPERADMIN)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,leader'], static function ($routes) {
    $routes->get('rankings', 'Dashboard::index');
    // Wave 5: Evaluasi Kinerja Internal Tim Operasional
    $routes->get('evaluations', 'InternalEvaluation::index');
    $routes->get('evaluations/staff', 'InternalEvaluation::staff');
    $routes->get('evaluations/export-excel', 'InternalEvaluation::exportExcel');
    $routes->get('finance', 'Dashboard::index');
    $routes->get('invoices', 'Dashboard::index');
    $routes->get('contracts', 'Dashboard::index');
    $routes->get('payments', 'Dashboard::index');
    $routes->get('budget', 'Dashboard::index');
    $routes->get('approvals', 'Dashboard::index');
});
