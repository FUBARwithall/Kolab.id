<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --------------------------------------------------------------------
// AUTENTIKASI DATAHUB
// --------------------------------------------------------------------
$routes->get('login', 'Auth::loginDatahub');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('datahub/login', 'Auth::loginDatahub');
$routes->post('datahub/login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('datahub/logout', 'Auth::logout');

// --------------------------------------------------------------------
// KOL DATA HUB — AKSES UMUM (SUPERADMIN & KOL TALENT)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,kol'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('datahub', 'Dashboard::index');
    $routes->get('datahub/dashboard', 'Dashboard::index');
    $routes->get('kols', 'Dashboard::kols');
    $routes->get('platforms', 'Dashboard::platforms');
    $routes->get('rate-cards', 'Dashboard::rateCards');
    $routes->get('categories', 'Dashboard::categories');
    $routes->get('tiers', 'Dashboard::tiers');
    
    // Ruang Talent KOL (Self-service creator)
    $routes->get('my-profile', 'Dashboard::myProfile');
    $routes->get('my-rates', 'Dashboard::myRates');
    $routes->get('open-campaigns', 'Dashboard::openCampaigns');
    $routes->get('my-applications', 'Dashboard::myApplications');
    $routes->get('my-tasks', 'Dashboard::myTasks');
    $routes->get('my-contracts', 'Dashboard::myContracts');
    $routes->get('my-invoices', 'Dashboard::myInvoices');
});

// --------------------------------------------------------------------
// KOL DATA HUB — AKSES SUPERADMIN ONLY
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin'], static function ($routes) {
    $routes->get('scraper', 'Dashboard::scraper');
    $routes->post('scraper/run', 'Dashboard::runScraper');
    $routes->get('settings', 'Dashboard::settings');
});
