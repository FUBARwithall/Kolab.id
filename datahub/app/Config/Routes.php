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
// AVATAR CORS PROXY
// --------------------------------------------------------------------
$routes->get('proxy/avatar', 'Proxy::avatar');
$routes->get('datahub/proxy/avatar', 'Proxy::avatar');

// --------------------------------------------------------------------
// PERFORMANCE CARD GENERATOR (PUBLIC SIGNED / EMBED / AUTH)
// --------------------------------------------------------------------
$routes->get('performance-card/view/(:num)', 'PerformanceCard::view/$1');
$routes->get('datahub/performance-card/view/(:num)', 'PerformanceCard::view/$1');
$routes->get('performance-card/(:num)', 'PerformanceCard::view/$1');
$routes->get('datahub/performance-card/(:num)', 'PerformanceCard::view/$1');
$routes->get('performance-card/data/(:num)', 'PerformanceCard::cardDataAjax/$1');
$routes->get('datahub/performance-card/data/(:num)', 'PerformanceCard::cardDataAjax/$1');

// --------------------------------------------------------------------
// KOL DATA HUB — AKSES UMUM (SUPERADMIN & KOL TALENT)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,kol'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('datahub', 'Dashboard::index');
    $routes->get('datahub/dashboard', 'Dashboard::index');
    $routes->get('kols', 'Dashboard::kols');
    $routes->get('snapshots', 'Dashboard::snapshots');
    $routes->get('datahub/snapshots', 'Dashboard::snapshots');
    $routes->get('kol-profile-snapshots', 'Dashboard::snapshots');
    $routes->get('datahub/kol-profile-snapshots', 'Dashboard::snapshots');
    $routes->get('snapshots/delete/(:num)', 'Dashboard::deleteSnapshot/$1');
    $routes->get('datahub/snapshots/delete/(:num)', 'Dashboard::deleteSnapshot/$1');
    $routes->get('platforms', 'Dashboard::platforms');
    $routes->get('market', 'Dashboard::platforms');
    $routes->get('rate-cards', 'Dashboard::rateCards');
    $routes->get('categories', 'Dashboard::categories');
    $routes->get('tiers', 'Dashboard::tiers');

    // Performance Card (Internal Auth)
    $routes->get('performance-card', 'PerformanceCard::index');
    $routes->get('datahub/performance-card', 'PerformanceCard::index');
    $routes->get('performance-card/my-card', 'PerformanceCard::myCard');
    $routes->get('datahub/performance-card/my-card', 'PerformanceCard::myCard');
    
    // Ruang Talent KOL (Self-service creator) — Refactored ke Talent Controller
    $routes->get('my-profile', 'Talent::myProfile');
    $routes->get('datahub/my-profile', 'Talent::myProfile');
    $routes->post('my-profile/update', 'Talent::updateProfile');
    $routes->post('datahub/my-profile/update', 'Talent::updateProfile');

    $routes->get('open-campaigns', 'Talent::openCampaigns');
    $routes->get('datahub/open-campaigns', 'Talent::openCampaigns');
    $routes->get('open-campaigns/detail/(:num)', 'Talent::getCampaignDetailAjax/$1');
    $routes->get('datahub/open-campaigns/detail/(:num)', 'Talent::getCampaignDetailAjax/$1');
    $routes->post('apply-campaign', 'Talent::applyCampaign');
    $routes->post('datahub/apply-campaign', 'Talent::applyCampaign');

    $routes->get('my-applications', 'Talent::myApplications');
    $routes->get('datahub/my-applications', 'Talent::myApplications');

    $routes->get('my-tasks', 'Talent::myTasks');
    $routes->get('datahub/my-tasks', 'Talent::myTasks');
    $routes->post('my-tasks/submit-draft', 'Talent::submitDraft');
    $routes->post('datahub/my-tasks/submit-draft', 'Talent::submitDraft');
    $routes->post('my-tasks/submit-live', 'Talent::submitLiveUrl');
    $routes->post('datahub/my-tasks/submit-live', 'Talent::submitLiveUrl');

    $routes->get('my-contracts', 'Talent::myContracts');
    $routes->get('datahub/my-contracts', 'Talent::myContracts');
    $routes->get('my-contracts/preview/(:num)', 'Talent::getContractAjax/$1');
    $routes->get('datahub/my-contracts/preview/(:num)', 'Talent::getContractAjax/$1');
    $routes->post('my-contracts/sign', 'Talent::signContract');
    $routes->post('datahub/my-contracts/sign', 'Talent::signContract');

    $routes->get('my-rates', 'Talent::myRates');
    $routes->get('datahub/my-rates', 'Talent::myRates');
    $routes->post('my-rates/save', 'Talent::saveRate');
    $routes->post('datahub/my-rates/save', 'Talent::saveRate');
    $routes->get('my-rates/delete/(:num)', 'Talent::deleteRate/$1');
    $routes->get('datahub/my-rates/delete/(:num)', 'Talent::deleteRate/$1');

    $routes->get('my-invoices', 'Talent::myInvoices');
    $routes->get('datahub/my-invoices', 'Talent::myInvoices');
    $routes->get('my-invoices/deal-detail/(:num)', 'Talent::getDealDetailAjax/$1');
    $routes->get('datahub/my-invoices/deal-detail/(:num)', 'Talent::getDealDetailAjax/$1');
    $routes->post('my-invoices/submit', 'Talent::submitInvoice');
    $routes->post('datahub/my-invoices/submit', 'Talent::submitInvoice');
    $routes->get('my-invoices/detail/(:num)', 'Talent::getInvoiceDetailAjax/$1');
    $routes->get('datahub/my-invoices/detail/(:num)', 'Talent::getInvoiceDetailAjax/$1');

    // Pusat Pemberitahuan (Notifications) — Full Parity with CI3 kol_notifications
    $routes->get('notifications', 'Notifications::index');
    $routes->get('datahub/notifications', 'Notifications::index');
    $routes->get('kol_notifications', 'Notifications::index');
    $routes->get('datahub/kol_notifications', 'Notifications::index');
    $routes->get('administrator/kol_notifications', 'Notifications::index');
    $routes->get('datahub/administrator/kol_notifications', 'Notifications::index');

    $routes->get('notifications/inbox', 'Notifications::getInboxAjax');
    $routes->get('datahub/notifications/inbox', 'Notifications::getInboxAjax');
    $routes->get('administrator/kol_notifications/get_inbox', 'Notifications::getInboxAjax');
    $routes->get('datahub/administrator/kol_notifications/get_inbox', 'Notifications::getInboxAjax');

    $routes->post('notifications/mark-read', 'Notifications::markAllReadAjax');
    $routes->post('datahub/notifications/mark-read', 'Notifications::markAllReadAjax');
    $routes->post('administrator/kol_notifications/mark_all_read', 'Notifications::markAllReadAjax');
    $routes->post('datahub/administrator/kol_notifications/mark_all_read', 'Notifications::markAllReadAjax');
});

// --------------------------------------------------------------------
// KOL DATA HUB — AKSES SUPERADMIN ONLY
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin'], static function ($routes) {
    // Scraper Engine (dedicated Scraper controller)
    $routes->get('scraper', 'Scraper::index');
    $routes->get('datahub/scraper', 'Scraper::index');
    $routes->get('scraping', 'Scraper::index');
    $routes->get('datahub/scraping', 'Scraper::index');
    $routes->post('scraper/run', 'Scraper::runScraper');
    $routes->post('datahub/scraper/run', 'Scraper::runScraper');
    $routes->post('scraper/bulk', 'Scraper::bulkScrape');
    $routes->post('datahub/scraper/bulk', 'Scraper::bulkScrape');
    $routes->get('scraper/job-status/(:num)', 'Scraper::jobStatus/$1');
    $routes->get('datahub/scraper/job-status/(:num)', 'Scraper::jobStatus/$1');
    $routes->post('scraper/webhook/(:segment)', 'Scraper::webhook/$1');
    $routes->post('datahub/scraper/webhook/(:segment)', 'Scraper::webhook/$1');

    $routes->get('settings', 'Dashboard::settings');
    $routes->get('datahub/settings', 'Dashboard::settings');
});
