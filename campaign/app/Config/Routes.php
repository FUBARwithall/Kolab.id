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
    $routes->get('products/variants/(:num)', 'Products::getVariants/$1');
    $routes->post('products/variants/save', 'Products::saveVariant');
    $routes->post('products/variants/delete/(:num)', 'Products::deleteVariant/$1');
    $routes->get('stock-history', 'StockHistory::index');
    $routes->post('stock-history/get-datatable', 'StockHistory::getDatatable');

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

    // Wave 7: Backoffice CMS Artikel Blog & Landing
    $routes->get('articles', 'Articles::index');
    $routes->post('articles/get-datatable', 'Articles::getDatatable');
    $routes->get('articles/get-one/(:num)', 'Articles::getOne/$1');
    $routes->post('articles/save', 'Articles::save');
    $routes->post('articles/delete/(:num)', 'Articles::delete/$1');

    // Wave 8: WhatsApp Communication Console & Broadcast
    $routes->get('whatsapp', 'Whatsapp::index');
    $routes->get('whatsapp/chat/(:num)', 'Whatsapp::chat/$1');
    $routes->post('whatsapp/send', 'Whatsapp::ajaxSendMessage');
    $routes->get('whatsapp/poll/(:num)', 'Whatsapp::ajaxPollMessages/$1');
    $routes->get('whatsapp/session-status', 'Whatsapp::ajaxSessionStatus');
    $routes->post('whatsapp/start-session', 'Whatsapp::ajaxStartSession');
    $routes->get('whatsapp/broadcast-kols', 'Whatsapp::ajaxGetBroadcastKols');
    $routes->post('whatsapp/send-broadcast-item', 'Whatsapp::ajaxSendBroadcastItem');

    // Wave 9: Monitoring Scraping & Apify Jobs
    $routes->get('scraping-jobs', 'ScrapingJobs::index');
    $routes->post('scraping-jobs/get-datatable', 'ScrapingJobs::getDatatable');
    $routes->get('scraping-jobs/get-one/(:num)', 'ScrapingJobs::getOne/$1');
    $routes->post('scraping-jobs/retry/(:num)', 'ScrapingJobs::retry/$1');
    $routes->post('scraping-jobs/trigger', 'ScrapingJobs::triggerNew');
    $routes->get('apify-jobs', static fn() => redirect()->to(base_url('scraping-jobs')));
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
    $routes->get('internal-evaluation', 'InternalEvaluation::index');
    $routes->get('internal-evaluation/export-excel', 'InternalEvaluation::exportExcel');
    // Wave 6: Executive Finance Hub
    $routes->get('finance', 'FinanceHub::index');
    $routes->post('finance/get-datatable', 'FinanceHub::getDatatable');
    $routes->post('finance/reject-invoice/(:num)', 'FinanceHub::rejectInvoice/$1');
    $routes->post('finance/upload-bupot/(:num)', 'FinanceHub::uploadBupot/$1');
    $routes->get('finance/invoice-print/(:num)', 'FinanceHub::printInvoice/$1');

    // Wave 6: Invoices & Finansial
    $routes->get('invoices', 'Invoices::index');
    $routes->post('invoices/get-datatable', 'Invoices::getDatatable');
    $routes->get('invoices/get-one/(:num)', 'Invoices::getOne/$1');
    $routes->post('invoices/create-from-deal/(:num)', 'Invoices::createFromDeal/$1');
    $routes->post('invoices/verify/(:num)', 'Invoices::verify/$1');
    $routes->post('invoices/save-payment/(:num)', 'Invoices::savePayment/$1');

    // Wave 6: Contracts & Komisi
    $routes->get('contracts', 'Contracts::index');
    $routes->post('contracts/get-datatable', 'Contracts::getDatatable');
    $routes->get('contracts/get-one/(:num)', 'Contracts::getOne/$1');
    $routes->post('contracts/generate', 'Contracts::generate');
    $routes->get('contracts/print/(:num)', 'Contracts::printContract/$1');
    $routes->post('contracts/sign/(:num)', 'Contracts::signContract/$1');

    // Wave 6: Master Contract Templates
    $routes->get('contract-templates', 'ContractTemplates::index');
    $routes->post('contract-templates/get-datatable', 'ContractTemplates::getDatatable');
    $routes->get('contract-templates/get-one/(:num)', 'ContractTemplates::getOne/$1');
    $routes->post('contract-templates/save', 'ContractTemplates::save');
    $routes->post('contract-templates/delete/(:num)', 'ContractTemplates::delete/$1');

    // Wave 10: Audit Trail & Activity Logs
    $routes->get('audit-logs', 'AuditLogs::index');
    $routes->post('audit-logs/get-datatable', 'AuditLogs::getDatatable');
    $routes->get('audit-logs/get-one/(:num)', 'AuditLogs::getOne/$1');

    // Wave 11: Team & User Management
    $routes->get('users', 'Users::index');
    $routes->post('users/get-datatable', 'Users::getDatatable');
    $routes->get('users/get-one/(:num)', 'Users::getOne/$1');
    $routes->post('users/save', 'Users::save');
    $routes->post('users/toggle-status/(:num)', 'Users::toggleStatus/$1');

    $routes->get('payments', 'Invoices::index');
    $routes->get('budget', 'Invoices::index');
    $routes->get('approvals', 'Invoices::index');
});
