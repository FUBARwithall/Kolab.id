<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --------------------------------------------------------------------
// PUBLIC REST API: Articles Feed (Consumed by Landing Page)
// --------------------------------------------------------------------
$routes->get('articles/feed', 'ArticlesApi::feed');
$routes->get('articles/feed/(:segment)', 'ArticlesApi::feed/$1');

// --------------------------------------------------------------------
// PUBLIC REST API: WhatsApp Webhook Receiver (WAHA & Baileys)
// --------------------------------------------------------------------
$routes->get('api/whatsapp', 'Whatsapp::ping');
$routes->get('api/whatsapp/ping', 'Whatsapp::ping');
$routes->match(['get', 'post'], 'api/whatsapp/webhook', 'Whatsapp::webhook');
$routes->match(['get', 'post'], 'whatsapp/webhook', 'Whatsapp::webhook');

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
$routes->get('kol/login', static fn() => redirect()->to(root_url('datahub/login')));
$routes->get('KOL/login', static fn() => redirect()->to(root_url('datahub/login')));
$routes->get('kol', static fn() => redirect()->to(root_url('datahub/dashboard')));
$routes->get('KOL', static fn() => redirect()->to(root_url('datahub/dashboard')));
$routes->get('kol/(:any)', static fn() => redirect()->to(root_url('datahub/dashboard')));
$routes->get('KOL/(:any)', static fn() => redirect()->to(root_url('datahub/dashboard')));

// --------------------------------------------------------------------
// CAMPAIGN MANAGEMENT — AKSES OPERASIONAL (LEADER, STAFF & SUPERADMIN)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,leader,staff'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('campaign', 'Dashboard::index');
    $routes->get('campaign/dashboard', 'Dashboard::index');
    // Wave 1: Master Produk & Mutasi Stok Sampel (Full Parity with CI3 modules/products)
    $routes->get('products', 'Products::index');
    $routes->post('products/get-datatable', 'Products::getDatatable');
    $routes->get('products/get-one/(:num)', 'Products::getOne/$1');
    $routes->get('products/view/(:num)', 'Products::view/$1');
    $routes->get('products/add', 'Products::add');
    $routes->post('products/add_save', 'Products::add_save');
    $routes->get('products/edit/(:num)', 'Products::edit/$1');
    $routes->post('products/edit_save/(:num)', 'Products::edit_save/$1');
    $routes->post('products/save', 'Products::save');
    $routes->post('products/adjust-stock', 'Products::adjustStock');
    $routes->match(['GET', 'POST'], 'products/delete/(:num)', 'Products::delete/$1');
    $routes->match(['GET', 'POST'], 'products/delete', 'Products::delete');
    $routes->post('products/bulk-delete', 'Products::bulkDelete');
    $routes->get('products/export', 'Products::export');
    $routes->get('products/export-pdf', 'Products::export_pdf');
    $routes->get('products/export_pdf', 'Products::export_pdf');
    $routes->get('products/single-pdf/(:num)', 'Products::single_pdf/$1');
    $routes->get('products/single_pdf/(:num)', 'Products::single_pdf/$1');
    $routes->get('products/variants/(:num)', 'Products::getVariants/$1');
    $routes->post('products/variants/save', 'Products::saveVariant');
    $routes->post('products/variants/delete/(:num)', 'Products::deleteVariant/$1');
    // Wave 1.1: Riwayat Audit Mutasi Stok (Full Parity with CI3 modules/stock_history)
    $routes->get('stock-history', 'StockHistory::index');
    $routes->get('stock-history/index', 'StockHistory::index');
    $routes->get('stock-history/index/(:num)', 'StockHistory::index/$1');
    $routes->post('stock-history/get-datatable', 'StockHistory::getDatatable');
    $routes->get('stock-history/get-one/(:num)', 'StockHistory::getOne/$1');
    $routes->get('stock-history/view/(:num)', 'StockHistory::view/$1');
    $routes->get('stock-history/export', 'StockHistory::export');
    $routes->get('stock-history/export-pdf', 'StockHistory::export_pdf');
    $routes->get('stock-history/export_pdf', 'StockHistory::export_pdf');
    $routes->get('stock-history/single-pdf/(:num)', 'StockHistory::single_pdf/$1');
    $routes->get('stock-history/single_pdf/(:num)', 'StockHistory::single_pdf/$1');

    // CI3 Parity Aliases for Stock History (modules/stock_history)
    $routes->get('administrator/stock_history', 'StockHistory::index');
    $routes->get('administrator/stock_history/index', 'StockHistory::index');
    $routes->get('administrator/stock_history/index/(:num)', 'StockHistory::index/$1');
    $routes->get('administrator/stock_history/view/(:num)', 'StockHistory::view/$1');
    $routes->get('administrator/stock_history/export', 'StockHistory::export');
    $routes->get('administrator/stock_history/export_pdf', 'StockHistory::export_pdf');
    $routes->get('administrator/stock_history/single_pdf/(:num)', 'StockHistory::single_pdf/$1');

    // CI3 Parity Aliases for Products
    $routes->get('administrator/products', 'Products::index');
    $routes->get('administrator/products/index', 'Products::index');
    $routes->get('administrator/products/index/(:num)', 'Products::index');
    $routes->get('administrator/products/add', 'Products::add');
    $routes->post('administrator/products/add_save', 'Products::add_save');
    $routes->get('administrator/products/edit/(:num)', 'Products::edit/$1');
    $routes->post('administrator/products/edit_save/(:num)', 'Products::edit_save/$1');
    $routes->get('administrator/products/view/(:num)', 'Products::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/products/delete/(:num)', 'Products::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/products/delete', 'Products::delete');
    $routes->get('administrator/products/export', 'Products::export');
    $routes->get('administrator/products/export_pdf', 'Products::export_pdf');
    $routes->get('administrator/products/single_pdf/(:num)', 'Products::single_pdf/$1');

    // Wave 13: Variant Konsep Konten (Full Parity with CI3 modules/variants)
    $routes->get('variants', 'Variants::index');
    $routes->post('variants/get-datatable', 'Variants::getDatatable');
    $routes->get('variants/get-one/(:num)', 'Variants::getOne/$1');
    $routes->get('variants/view/(:num)', 'Variants::view/$1');
    $routes->post('variants/save', 'Variants::save');
    $routes->match(['GET', 'POST'], 'variants/delete/(:num)', 'Variants::delete/$1');
    $routes->post('variants/bulk-delete', 'Variants::bulkDelete');
    $routes->get('variants/export', 'Variants::export');

    // CI3 Parity Aliases for Variants
    $routes->get('administrator/variants', 'Variants::index');
    $routes->get('administrator/variants/index', 'Variants::index');
    $routes->get('administrator/variants/view/(:num)', 'Variants::view/$1');
    $routes->get('administrator/variants/edit/(:num)', 'Variants::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/variants/delete/(:num)', 'Variants::delete/$1');
    $routes->get('administrator/variants/export', 'Variants::export');

    // Wave 2: Perencanaan Kampanye & Penugasan KOL (Full Parity with CI3 modules/campaigns)
    $routes->get('campaigns', 'Campaigns::index');
    $routes->post('campaigns/get-datatable', 'Campaigns::getDatatable');
    $routes->get('campaigns/get-one/(:num)', 'Campaigns::getOne/$1');
    $routes->get('campaigns/view/(:num)', 'Campaigns::view/$1');
    $routes->get('campaigns/analytics/(:num)', 'Campaigns::analytics/$1');
    $routes->post('campaigns/save', 'Campaigns::save');
    $routes->match(['GET', 'POST'], 'campaigns/delete/(:num)', 'Campaigns::delete/$1');
    $routes->post('campaigns/update-status/(:num)', 'Campaigns::updateStatus/$1');
    $routes->post('campaigns/update_status/(:num)', 'Campaigns::updateStatus/$1');
    $routes->match(['GET', 'POST'], 'campaigns/accept-applicant/(:num)', 'Campaigns::acceptApplicant/$1');
    $routes->match(['GET', 'POST'], 'campaigns/accept_applicant/(:num)', 'Campaigns::acceptApplicant/$1');
    $routes->match(['GET', 'POST'], 'campaigns/reject-applicant/(:num)', 'Campaigns::rejectApplicant/$1');
    $routes->match(['GET', 'POST'], 'campaigns/reject_applicant/(:num)', 'Campaigns::rejectApplicant/$1');
    $routes->get('campaigns/export', 'Campaigns::export');
    $routes->get('campaigns/export-single/(:num)', 'Campaigns::exportSingle/$1');
    $routes->get('campaigns/export_single/(:num)', 'Campaigns::exportSingle/$1');

    // CI3 Parity Aliases for administrator/campaigns
    $routes->get('administrator/campaigns', 'Campaigns::index');
    $routes->get('administrator/campaigns/index', 'Campaigns::index');
    $routes->post('administrator/campaigns/get-datatable', 'Campaigns::getDatatable');
    $routes->get('administrator/campaigns/view/(:num)', 'Campaigns::view/$1');
    $routes->get('administrator/campaigns/edit/(:num)', 'Campaigns::view/$1');
    $routes->get('administrator/campaigns/analytics/(:num)', 'Campaigns::analytics/$1');
    $routes->get('administrator/campaigns/export', 'Campaigns::export');
    $routes->get('administrator/campaigns/export_single/(:num)', 'Campaigns::exportSingle/$1');
    $routes->match(['GET', 'POST'], 'administrator/campaigns/delete/(:num)', 'Campaigns::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/campaigns/accept_applicant/(:num)', 'Campaigns::acceptApplicant/$1');
    $routes->match(['GET', 'POST'], 'administrator/campaigns/reject_applicant/(:num)', 'Campaigns::rejectApplicant/$1');

    // Dealing KOL (Campaign KOLs) Engine - 100% Parity with CI3 modules/campaign_kols
    $routes->get('campaign-kols', 'CampaignKols::index');
    $routes->get('campaign_kols', 'CampaignKols::index');
    $routes->post('campaign-kols/get-datatable', 'CampaignKols::getDatatable');
    $routes->post('campaign_kols/get-datatable', 'CampaignKols::getDatatable');
    $routes->get('campaign-kols/get-one/(:num)', 'CampaignKols::getOne/$1');
    $routes->get('campaign_kols/get-one/(:num)', 'CampaignKols::getOne/$1');
    $routes->get('campaign-kols/tasks-json/(:num)', 'CampaignKols::getTasksJson/$1');
    $routes->get('campaign_kols/tasks-json/(:num)', 'CampaignKols::getTasksJson/$1');
    $routes->get('campaign-kols/generate-tracking-no', 'CampaignKols::generateTrackingNo');
    $routes->get('campaign_kols/generate-tracking-no', 'CampaignKols::generateTrackingNo');
    $routes->post('campaign-kols/quick-add-kol', 'CampaignKols::quickAddKol');
    $routes->post('campaign_kols/quick-add-kol', 'CampaignKols::quickAddKol');
    $routes->get('campaign-kols/assigned-kols/(:num)', 'CampaignKols::getCampaignAssignedKols/$1');
    $routes->get('campaign_kols/assigned-kols/(:num)', 'CampaignKols::getCampaignAssignedKols/$1');
    $routes->post('campaign-kols/save', 'CampaignKols::save');
    $routes->post('campaign_kols/save', 'CampaignKols::save');
    $routes->post('campaign-kols/update-status-tasks-ajax', 'CampaignKols::updateStatusTasksAjax');
    $routes->post('campaign_kols/update-status-tasks-ajax', 'CampaignKols::updateStatusTasksAjax');
    $routes->get('campaign-kols/view/(:num)', 'CampaignKols::view/$1');
    $routes->get('campaign_kols/view/(:num)', 'CampaignKols::view/$1');
    $routes->post('campaign-kols/save-score/(:num)', 'CampaignKols::saveScore/$1');
    $routes->post('campaign_kols/save-score/(:num)', 'CampaignKols::saveScore/$1');
    $routes->match(['GET', 'POST'], 'campaign-kols/delete/(:num)', 'CampaignKols::delete/$1');
    $routes->match(['GET', 'POST'], 'campaign_kols/delete/(:num)', 'CampaignKols::delete/$1');
    $routes->post('campaign-kols/bulk-delete', 'CampaignKols::bulkDelete');
    $routes->post('campaign_kols/bulk-delete', 'CampaignKols::bulkDelete');
    $routes->get('campaign-kols/export', 'CampaignKols::export');
    $routes->get('campaign_kols/export', 'CampaignKols::export');
    $routes->post('campaign-kols/assign', 'CampaignKols::assign');
    $routes->post('campaign-kols/remove/(:num)', 'CampaignKols::remove/$1');
    $routes->post('campaign-kols/submit-review', 'CampaignKols::submitReview');
    $routes->post('campaign-kols/review-application', 'CampaignKols::reviewApplication');

    // CI3 Parity Aliases for campaign_kols
    $routes->get('administrator/campaign_kols', 'CampaignKols::index');
    $routes->get('administrator/campaign_kols/index', 'CampaignKols::index');
    $routes->get('administrator/campaign_kols/add', 'CampaignKols::index');
    $routes->post('administrator/campaign_kols/add_save', 'CampaignKols::save');
    $routes->get('administrator/campaign_kols/edit/(:num)', 'CampaignKols::view/$1');
    $routes->post('administrator/campaign_kols/edit_save/(:num)', 'CampaignKols::updateStatusTasksAjax');
    $routes->get('administrator/campaign_kols/view/(:num)', 'CampaignKols::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/campaign_kols/delete', 'CampaignKols::bulkDelete');
    $routes->match(['GET', 'POST'], 'administrator/campaign_kols/delete/(:num)', 'CampaignKols::delete/$1');
    $routes->get('administrator/campaign_kols/export', 'CampaignKols::export');
    $routes->get('administrator/campaign_kols/export_pdf', 'CampaignKols::export');
    $routes->get('administrator/campaign_kols/get_tasks_json/(:num)', 'CampaignKols::getTasksJson/$1');
    $routes->get('administrator/campaign_kols/generate_tracking_no', 'CampaignKols::generateTrackingNo');
    $routes->post('administrator/campaign_kols/quick_add_kol', 'CampaignKols::quickAddKol');
    $routes->get('administrator/campaign_kols/get_campaign_assigned_kols/(:num)', 'CampaignKols::getCampaignAssignedKols/$1');
    $routes->post('administrator/campaign_kols/update_status_tasks_ajax', 'CampaignKols::updateStatusTasksAjax');

    // Campaign KOL Variants (Alokasi Varian KOL) - 100% Parity with CI3 modules/campaign_kol_variants
    $routes->get('campaign-kol-variants', 'CampaignKolVariants::index');
    $routes->get('campaign_kol_variants', 'CampaignKolVariants::index');
    $routes->post('campaign-kol-variants/get-datatable', 'CampaignKolVariants::getDatatable');
    $routes->post('campaign_kol_variants/get-datatable', 'CampaignKolVariants::getDatatable');
    $routes->get('campaign-kol-variants/get-one/(:num)', 'CampaignKolVariants::getOne/$1');
    $routes->get('campaign_kol_variants/get-one/(:num)', 'CampaignKolVariants::getOne/$1');
    $routes->post('campaign-kol-variants/save', 'CampaignKolVariants::save');
    $routes->post('campaign_kol_variants/save', 'CampaignKolVariants::save');
    $routes->get('campaign-kol-variants/view/(:num)', 'CampaignKolVariants::view/$1');
    $routes->get('campaign_kol_variants/view/(:num)', 'CampaignKolVariants::view/$1');
    $routes->match(['GET', 'POST'], 'campaign-kol-variants/delete/(:num)', 'CampaignKolVariants::delete/$1');
    $routes->match(['GET', 'POST'], 'campaign_kol_variants/delete/(:num)', 'CampaignKolVariants::delete/$1');
    $routes->post('campaign-kol-variants/bulk-delete', 'CampaignKolVariants::bulkDelete');
    $routes->post('campaign_kol_variants/bulk-delete', 'CampaignKolVariants::bulkDelete');
    $routes->get('campaign-kol-variants/export', 'CampaignKolVariants::export');
    $routes->get('campaign_kol_variants/export', 'CampaignKolVariants::export');

    // CI3 Parity Aliases for campaign_kol_variants
    $routes->get('administrator/campaign_kol_variants', 'CampaignKolVariants::index');
    $routes->get('administrator/campaign_kol_variants/index', 'CampaignKolVariants::index');
    $routes->get('administrator/campaign_kol_variants/add', 'CampaignKolVariants::index');
    $routes->post('administrator/campaign_kol_variants/add_save', 'CampaignKolVariants::save');
    $routes->get('administrator/campaign_kol_variants/edit/(:num)', 'CampaignKolVariants::view/$1');
    $routes->post('administrator/campaign_kol_variants/edit_save/(:num)', 'CampaignKolVariants::save');
    $routes->get('administrator/campaign_kol_variants/view/(:num)', 'CampaignKolVariants::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/campaign_kol_variants/delete', 'CampaignKolVariants::bulkDelete');
    $routes->match(['GET', 'POST'], 'administrator/campaign_kol_variants/delete/(:num)', 'CampaignKolVariants::delete/$1');
    $routes->get('administrator/campaign_kol_variants/export', 'CampaignKolVariants::export');
    $routes->get('administrator/campaign_kol_variants/export_pdf', 'CampaignKolVariants::export');

    // Wave 3: Tasks Engine, Briefs, Drafts & Logistik Pengiriman Sampel
    $routes->get('tasks', 'Tasks::index');
    $routes->post('tasks/get-datatable', 'Tasks::getDatatable');
    $routes->get('tasks/get-one/(:num)', 'Tasks::getOne/$1');
    $routes->get('tasks/view/(:num)', 'Tasks::view/$1');
    $routes->post('tasks/save', 'Tasks::save');
    $routes->match(['GET', 'POST'], 'tasks/update-status/(:num)', 'Tasks::updateStatus/$1');
    $routes->match(['GET', 'POST'], 'tasks/set-status/(:num)', 'Tasks::setStatus/$1');
    $routes->match(['GET', 'POST'], 'tasks/set-status/(:num)/(:segment)', 'Tasks::setStatus/$1/$2');
    $routes->post('tasks/set-schedule/(:num)', 'Tasks::setSchedule/$1');
    $routes->post('tasks/set-live-url/(:num)', 'Tasks::setLiveUrl/$1');
    $routes->post('tasks/sync-posting-metrics/(:num)', 'Tasks::syncPostingMetrics/$1');
    $routes->post('tasks/analyze-sentiment/(:num)', 'Tasks::analyzeSentiment/$1');
    $routes->match(['GET', 'POST'], 'tasks/delete/(:num)', 'Tasks::delete/$1');
    $routes->post('tasks/bulk-delete', 'Tasks::bulkDelete');
    $routes->get('tasks/export', 'Tasks::export');

    // CI3 Parity Aliases for tasks
    $routes->get('administrator/tasks', 'Tasks::index');
    $routes->get('administrator/tasks/index', 'Tasks::index');
    $routes->get('administrator/tasks/view/(:num)', 'Tasks::view/$1');
    $routes->get('administrator/tasks/edit/(:num)', 'Tasks::view/$1');
    $routes->post('administrator/tasks/save', 'Tasks::save');
    $routes->match(['GET', 'POST'], 'administrator/tasks/delete/(:num)', 'Tasks::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/tasks/set_status/(:num)', 'Tasks::setStatus/$1');
    $routes->match(['GET', 'POST'], 'administrator/tasks/set_status/(:num)/(:segment)', 'Tasks::setStatus/$1/$2');
    $routes->get('administrator/tasks/export', 'Tasks::export');

    // Wave 3: Briefs & Guidelines (Full Parity with CI3 modules/briefs)
    $routes->get('briefs', 'Briefs::index');
    $routes->get('briefs/index', 'Briefs::index');
    $routes->get('briefs/index/(:num)', 'Briefs::index/$1');
    $routes->post('briefs/get-datatable', 'Briefs::getDatatable');
    $routes->get('briefs/get-one/(:num)', 'Briefs::getOne/$1');
    $routes->get('briefs/view/(:num)', 'Briefs::view/$1');
    $routes->get('briefs/add', 'Briefs::add');
    $routes->post('briefs/add_save', 'Briefs::add_save');
    $routes->get('briefs/edit/(:num)', 'Briefs::edit/$1');
    $routes->post('briefs/edit_save/(:num)', 'Briefs::edit_save/$1');
    $routes->post('briefs/save', 'Briefs::save');
    $routes->match(['GET', 'POST'], 'briefs/submit_review/(:num)', 'Briefs::submit_review/$1');
    $routes->match(['GET', 'POST'], 'briefs/approve/(:num)', 'Briefs::approve/$1');
    $routes->match(['GET', 'POST'], 'briefs/revision/(:num)', 'Briefs::revision/$1');
    $routes->match(['GET', 'POST'], 'briefs/delete/(:num)', 'Briefs::delete/$1');
    $routes->post('briefs/bulk-delete', 'Briefs::bulkDelete');
    $routes->get('briefs/export', 'Briefs::export');
    $routes->get('briefs/export-pdf', 'Briefs::export_pdf');
    $routes->get('briefs/single-pdf/(:num)', 'Briefs::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/briefs/*
    $routes->get('administrator/briefs', 'Briefs::index');
    $routes->get('administrator/briefs/index', 'Briefs::index');
    $routes->get('administrator/briefs/index/(:num)', 'Briefs::index/$1');
    $routes->get('administrator/briefs/view/(:num)', 'Briefs::view/$1');
    $routes->get('administrator/briefs/add', 'Briefs::add');
    $routes->post('administrator/briefs/add_save', 'Briefs::add_save');
    $routes->get('administrator/briefs/edit/(:num)', 'Briefs::edit/$1');
    $routes->post('administrator/briefs/edit_save/(:num)', 'Briefs::edit_save/$1');
    $routes->match(['GET', 'POST'], 'administrator/briefs/submit_review/(:num)', 'Briefs::submit_review/$1');
    $routes->match(['GET', 'POST'], 'administrator/briefs/approve/(:num)', 'Briefs::approve/$1');
    $routes->match(['GET', 'POST'], 'administrator/briefs/revision/(:num)', 'Briefs::revision/$1');
    $routes->match(['GET', 'POST'], 'administrator/briefs/delete/(:num)', 'Briefs::delete/$1');
    $routes->get('administrator/briefs/delete', 'Briefs::delete');
    $routes->get('administrator/briefs/export', 'Briefs::export');
    $routes->get('administrator/briefs/export_pdf', 'Briefs::export_pdf');
    $routes->get('administrator/briefs/single_pdf/(:num)', 'Briefs::single_pdf/$1');

    // Wave 3: Brief Content Histories (Full Parity with CI3 modules/brief_content_histories)
    $routes->get('brief-content-histories', 'BriefContentHistories::index');
    $routes->get('brief-content-histories/index', 'BriefContentHistories::index');
    $routes->get('brief_content_histories', 'BriefContentHistories::index');
    $routes->get('brief_content_histories/index', 'BriefContentHistories::index');
    $routes->post('brief-content-histories/get-datatable', 'BriefContentHistories::getDatatable');
    $routes->post('brief_content_histories/get-datatable', 'BriefContentHistories::getDatatable');
    $routes->get('brief-content-histories/get-one/(:num)', 'BriefContentHistories::getOne/$1');
    $routes->get('brief_content_histories/get-one/(:num)', 'BriefContentHistories::getOne/$1');
    $routes->get('brief-content-histories/view/(:num)', 'BriefContentHistories::view/$1');
    $routes->get('brief_content_histories/view/(:num)', 'BriefContentHistories::view/$1');
    $routes->match(['GET', 'POST'], 'brief-content-histories/delete/(:num)', 'BriefContentHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'brief_content_histories/delete/(:num)', 'BriefContentHistories::delete/$1');
    $routes->post('brief-content-histories/bulk-delete', 'BriefContentHistories::bulkDelete');
    $routes->post('brief_content_histories/bulk-delete', 'BriefContentHistories::bulkDelete');
    $routes->get('brief-content-histories/export', 'BriefContentHistories::export');
    $routes->get('brief_content_histories/export', 'BriefContentHistories::export');
    $routes->get('brief-content-histories/export-pdf', 'BriefContentHistories::export_pdf');
    $routes->get('brief_content_histories/export-pdf', 'BriefContentHistories::export_pdf');
    $routes->get('brief-content-histories/single-pdf/(:num)', 'BriefContentHistories::single_pdf/$1');
    $routes->get('brief_content_histories/single-pdf/(:num)', 'BriefContentHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/brief_content_histories/*
    $routes->get('administrator/brief_content_histories', 'BriefContentHistories::index');
    $routes->get('administrator/brief_content_histories/index', 'BriefContentHistories::index');
    $routes->get('administrator/brief_content_histories/index/(:num)', 'BriefContentHistories::index/$1');
    $routes->get('administrator/brief_content_histories/view/(:num)', 'BriefContentHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/brief_content_histories/delete/(:num)', 'BriefContentHistories::delete/$1');
    $routes->get('administrator/brief_content_histories/delete', 'BriefContentHistories::delete');
    $routes->get('administrator/brief_content_histories/export', 'BriefContentHistories::export');
    $routes->get('administrator/brief_content_histories/export_pdf', 'BriefContentHistories::export_pdf');
    $routes->get('administrator/brief_content_histories/single_pdf/(:num)', 'BriefContentHistories::single_pdf/$1');

    // Wave 3: Brief Approval Histories (Full Parity with CI3 modules/brief_approval_histories)
    $routes->get('brief-approval-histories', 'BriefApprovalHistories::index');
    $routes->get('brief-approval-histories/index', 'BriefApprovalHistories::index');
    $routes->get('brief_approval_histories', 'BriefApprovalHistories::index');
    $routes->get('brief_approval_histories/index', 'BriefApprovalHistories::index');
    $routes->post('brief-approval-histories/get-datatable', 'BriefApprovalHistories::getDatatable');
    $routes->post('brief_approval_histories/get-datatable', 'BriefApprovalHistories::getDatatable');
    $routes->get('brief-approval-histories/get-one/(:num)', 'BriefApprovalHistories::getOne/$1');
    $routes->get('brief_approval_histories/get-one/(:num)', 'BriefApprovalHistories::getOne/$1');
    $routes->get('brief-approval-histories/view/(:num)', 'BriefApprovalHistories::view/$1');
    $routes->get('brief_approval_histories/view/(:num)', 'BriefApprovalHistories::view/$1');
    $routes->match(['GET', 'POST'], 'brief-approval-histories/delete/(:num)', 'BriefApprovalHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'brief_approval_histories/delete/(:num)', 'BriefApprovalHistories::delete/$1');
    $routes->post('brief-approval-histories/bulk-delete', 'BriefApprovalHistories::bulkDelete');
    $routes->post('brief_approval_histories/bulk-delete', 'BriefApprovalHistories::bulkDelete');
    $routes->get('brief-approval-histories/export', 'BriefApprovalHistories::export');
    $routes->get('brief_approval_histories/export', 'BriefApprovalHistories::export');
    $routes->get('brief-approval-histories/export-pdf', 'BriefApprovalHistories::export_pdf');
    $routes->get('brief_approval_histories/export-pdf', 'BriefApprovalHistories::export_pdf');
    $routes->get('brief-approval-histories/single-pdf/(:num)', 'BriefApprovalHistories::single_pdf/$1');
    $routes->get('brief_approval_histories/single-pdf/(:num)', 'BriefApprovalHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/brief_approval_histories/*
    $routes->get('administrator/brief_approval_histories', 'BriefApprovalHistories::index');
    $routes->get('administrator/brief_approval_histories/index', 'BriefApprovalHistories::index');
    $routes->get('administrator/brief_approval_histories/index/(:num)', 'BriefApprovalHistories::index/$1');
    $routes->get('administrator/brief_approval_histories/view/(:num)', 'BriefApprovalHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/brief_approval_histories/delete/(:num)', 'BriefApprovalHistories::delete/$1');
    $routes->get('administrator/brief_approval_histories/delete', 'BriefApprovalHistories::delete');
    $routes->get('administrator/brief_approval_histories/export', 'BriefApprovalHistories::export');
    $routes->get('administrator/brief_approval_histories/export_pdf', 'BriefApprovalHistories::export_pdf');
    $routes->get('administrator/brief_approval_histories/single_pdf/(:num)', 'BriefApprovalHistories::single_pdf/$1');

    // Wave 3: KOL Approval Histories (Full Parity with CI3 modules/kol_approval_histories)
    $routes->get('kol-approval-histories', 'KolApprovalHistories::index');
    $routes->get('kol-approval-histories/index', 'KolApprovalHistories::index');
    $routes->get('kol_approval_histories', 'KolApprovalHistories::index');
    $routes->get('kol_approval_histories/index', 'KolApprovalHistories::index');
    $routes->post('kol-approval-histories/get-datatable', 'KolApprovalHistories::getDatatable');
    $routes->post('kol_approval_histories/get-datatable', 'KolApprovalHistories::getDatatable');
    $routes->get('kol-approval-histories/get-one/(:num)', 'KolApprovalHistories::getOne/$1');
    $routes->get('kol_approval_histories/get-one/(:num)', 'KolApprovalHistories::getOne/$1');
    $routes->get('kol-approval-histories/view/(:num)', 'KolApprovalHistories::view/$1');
    $routes->get('kol_approval_histories/view/(:num)', 'KolApprovalHistories::view/$1');
    $routes->match(['GET', 'POST'], 'kol-approval-histories/delete/(:num)', 'KolApprovalHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'kol_approval_histories/delete/(:num)', 'KolApprovalHistories::delete/$1');
    $routes->post('kol-approval-histories/bulk-delete', 'KolApprovalHistories::bulkDelete');
    $routes->post('kol_approval_histories/bulk-delete', 'KolApprovalHistories::bulkDelete');
    $routes->get('kol-approval-histories/export', 'KolApprovalHistories::export');
    $routes->get('kol_approval_histories/export', 'KolApprovalHistories::export');
    $routes->get('kol-approval-histories/export-pdf', 'KolApprovalHistories::export_pdf');
    $routes->get('kol_approval_histories/export-pdf', 'KolApprovalHistories::export_pdf');
    $routes->get('kol-approval-histories/single-pdf/(:num)', 'KolApprovalHistories::single_pdf/$1');
    $routes->get('kol_approval_histories/single-pdf/(:num)', 'KolApprovalHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/kol_approval_histories/*
    $routes->get('administrator/kol_approval_histories', 'KolApprovalHistories::index');
    $routes->get('administrator/kol_approval_histories/index', 'KolApprovalHistories::index');
    $routes->get('administrator/kol_approval_histories/index/(:num)', 'KolApprovalHistories::index/$1');
    $routes->get('administrator/kol_approval_histories/view/(:num)', 'KolApprovalHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_approval_histories/delete/(:num)', 'KolApprovalHistories::delete/$1');
    $routes->get('administrator/kol_approval_histories/delete', 'KolApprovalHistories::delete');
    $routes->get('administrator/kol_approval_histories/export', 'KolApprovalHistories::export');
    $routes->get('administrator/kol_approval_histories/export_pdf', 'KolApprovalHistories::export_pdf');
    $routes->get('administrator/kol_approval_histories/single_pdf/(:num)', 'KolApprovalHistories::single_pdf/$1');

    // Wave 3: Draft Review Histories (Full Parity with CI3 modules/draft_review_histories)
    $routes->get('draft-review-histories', 'DraftReviewHistories::index');
    $routes->get('draft-review-histories/index', 'DraftReviewHistories::index');
    $routes->get('draft_review_histories', 'DraftReviewHistories::index');
    $routes->get('draft_review_histories/index', 'DraftReviewHistories::index');
    $routes->post('draft-review-histories/get-datatable', 'DraftReviewHistories::getDatatable');
    $routes->post('draft_review_histories/get-datatable', 'DraftReviewHistories::getDatatable');
    $routes->get('draft-review-histories/get-one/(:num)', 'DraftReviewHistories::getOne/$1');
    $routes->get('draft_review_histories/get-one/(:num)', 'DraftReviewHistories::getOne/$1');
    $routes->get('draft-review-histories/view/(:num)', 'DraftReviewHistories::view/$1');
    $routes->get('draft_review_histories/view/(:num)', 'DraftReviewHistories::view/$1');
    $routes->match(['GET', 'POST'], 'draft-review-histories/delete/(:num)', 'DraftReviewHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'draft_review_histories/delete/(:num)', 'DraftReviewHistories::delete/$1');
    $routes->post('draft-review-histories/bulk-delete', 'DraftReviewHistories::bulkDelete');
    $routes->post('draft_review_histories/bulk-delete', 'DraftReviewHistories::bulkDelete');
    $routes->get('draft-review-histories/export', 'DraftReviewHistories::export');
    $routes->get('draft_review_histories/export', 'DraftReviewHistories::export');
    $routes->get('draft-review-histories/export-pdf', 'DraftReviewHistories::export_pdf');
    $routes->get('draft_review_histories/export-pdf', 'DraftReviewHistories::export_pdf');
    $routes->get('draft-review-histories/single-pdf/(:num)', 'DraftReviewHistories::single_pdf/$1');
    $routes->get('draft_review_histories/single-pdf/(:num)', 'DraftReviewHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/draft_review_histories/*
    $routes->get('administrator/draft_review_histories', 'DraftReviewHistories::index');
    $routes->get('administrator/draft_review_histories/index', 'DraftReviewHistories::index');
    $routes->get('administrator/draft_review_histories/index/(:num)', 'DraftReviewHistories::index/$1');
    $routes->get('administrator/draft_review_histories/view/(:num)', 'DraftReviewHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/draft_review_histories/delete/(:num)', 'DraftReviewHistories::delete/$1');
    $routes->get('administrator/draft_review_histories/delete', 'DraftReviewHistories::delete');
    $routes->get('administrator/draft_review_histories/export', 'DraftReviewHistories::export');
    $routes->get('administrator/draft_review_histories/export_pdf', 'DraftReviewHistories::export_pdf');
    $routes->get('administrator/draft_review_histories/single_pdf/(:num)', 'DraftReviewHistories::single_pdf/$1');

    // Wave 3: Task Status Histories (Full Parity with CI3 modules/task_status_histories)
    $routes->get('task-status-histories', 'TaskStatusHistories::index');
    $routes->get('task-status-histories/index', 'TaskStatusHistories::index');
    $routes->get('task_status_histories', 'TaskStatusHistories::index');
    $routes->get('task_status_histories/index', 'TaskStatusHistories::index');
    $routes->match(['GET', 'POST'], 'task-status-histories/get-datatable', 'TaskStatusHistories::getDatatable');
    $routes->match(['GET', 'POST'], 'task_status_histories/get-datatable', 'TaskStatusHistories::getDatatable');
    $routes->get('task-status-histories/get-one/(:num)', 'TaskStatusHistories::getOne/$1');
    $routes->get('task_status_histories/get-one/(:num)', 'TaskStatusHistories::getOne/$1');
    $routes->get('task-status-histories/view/(:num)', 'TaskStatusHistories::view/$1');
    $routes->get('task_status_histories/view/(:num)', 'TaskStatusHistories::view/$1');
    $routes->match(['GET', 'POST'], 'task-status-histories/delete/(:num)', 'TaskStatusHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'task_status_histories/delete/(:num)', 'TaskStatusHistories::delete/$1');
    $routes->post('task-status-histories/bulk-delete', 'TaskStatusHistories::bulkDelete');
    $routes->post('task_status_histories/bulk-delete', 'TaskStatusHistories::bulkDelete');
    $routes->get('task-status-histories/export', 'TaskStatusHistories::export');
    $routes->get('task_status_histories/export', 'TaskStatusHistories::export');
    $routes->get('task-status-histories/export-pdf', 'TaskStatusHistories::export_pdf');
    $routes->get('task_status_histories/export-pdf', 'TaskStatusHistories::export_pdf');
    $routes->get('task-status-histories/single-pdf/(:num)', 'TaskStatusHistories::single_pdf/$1');
    $routes->get('task_status_histories/single-pdf/(:num)', 'TaskStatusHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/task_status_histories/*
    $routes->get('administrator/task_status_histories', 'TaskStatusHistories::index');
    $routes->get('administrator/task_status_histories/index', 'TaskStatusHistories::index');
    $routes->get('administrator/task_status_histories/index/(:num)', 'TaskStatusHistories::index/$1');
    $routes->get('administrator/task_status_histories/view/(:num)', 'TaskStatusHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/task_status_histories/delete/(:num)', 'TaskStatusHistories::delete/$1');
    $routes->get('administrator/task_status_histories/delete', 'TaskStatusHistories::delete');
    $routes->get('administrator/task_status_histories/export', 'TaskStatusHistories::export');
    $routes->get('administrator/task_status_histories/export_pdf', 'TaskStatusHistories::export_pdf');
    $routes->get('administrator/task_status_histories/single_pdf/(:num)', 'TaskStatusHistories::single_pdf/$1');

    // Wave 3: Score Histories (Full Parity with CI3 modules/score_histories)
    $routes->get('score-histories', 'ScoreHistories::index');
    $routes->get('score-histories/index', 'ScoreHistories::index');
    $routes->get('score_histories', 'ScoreHistories::index');
    $routes->get('score_histories/index', 'ScoreHistories::index');
    $routes->match(['GET', 'POST'], 'score-histories/get-datatable', 'ScoreHistories::getDatatable');
    $routes->match(['GET', 'POST'], 'score_histories/get-datatable', 'ScoreHistories::getDatatable');
    $routes->get('score-histories/get-one/(:num)', 'ScoreHistories::getOne/$1');
    $routes->get('score_histories/get-one/(:num)', 'ScoreHistories::getOne/$1');
    $routes->get('score-histories/view/(:num)', 'ScoreHistories::view/$1');
    $routes->get('score_histories/view/(:num)', 'ScoreHistories::view/$1');
    $routes->match(['GET', 'POST'], 'score-histories/delete/(:num)', 'ScoreHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'score_histories/delete/(:num)', 'ScoreHistories::delete/$1');
    $routes->post('score-histories/bulk-delete', 'ScoreHistories::bulkDelete');
    $routes->post('score_histories/bulk-delete', 'ScoreHistories::bulkDelete');
    $routes->get('score-histories/export', 'ScoreHistories::export');
    $routes->get('score_histories/export', 'ScoreHistories::export');
    $routes->get('score-histories/export-pdf', 'ScoreHistories::export_pdf');
    $routes->get('score_histories/export-pdf', 'ScoreHistories::export_pdf');
    $routes->get('score-histories/single-pdf/(:num)', 'ScoreHistories::single_pdf/$1');
    $routes->get('score_histories/single-pdf/(:num)', 'ScoreHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/score_histories/*
    $routes->get('administrator/score_histories', 'ScoreHistories::index');
    $routes->get('administrator/score_histories/index', 'ScoreHistories::index');
    $routes->get('administrator/score_histories/index/(:num)', 'ScoreHistories::index/$1');
    $routes->get('administrator/score_histories/view/(:num)', 'ScoreHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/score_histories/delete/(:num)', 'ScoreHistories::delete/$1');
    $routes->get('administrator/score_histories/delete', 'ScoreHistories::delete');
    $routes->get('administrator/score_histories/export', 'ScoreHistories::export');
    $routes->get('administrator/score_histories/export_pdf', 'ScoreHistories::export_pdf');
    $routes->get('administrator/score_histories/single_pdf/(:num)', 'ScoreHistories::single_pdf/$1');

    // Wave 3: Task Budget Histories (Full Parity with CI3 modules/task_budget_histories)
    $routes->get('task-budget-histories', 'TaskBudgetHistories::index');
    $routes->get('task-budget-histories/index', 'TaskBudgetHistories::index');
    $routes->get('task_budget_histories', 'TaskBudgetHistories::index');
    $routes->get('task_budget_histories/index', 'TaskBudgetHistories::index');
    $routes->match(['GET', 'POST'], 'task-budget-histories/get-datatable', 'TaskBudgetHistories::getDatatable');
    $routes->match(['GET', 'POST'], 'task_budget_histories/get-datatable', 'TaskBudgetHistories::getDatatable');
    $routes->get('task-budget-histories/get-one/(:num)', 'TaskBudgetHistories::getOne/$1');
    $routes->get('task_budget_histories/get-one/(:num)', 'TaskBudgetHistories::getOne/$1');
    $routes->get('task-budget-histories/view/(:num)', 'TaskBudgetHistories::view/$1');
    $routes->get('task_budget_histories/view/(:num)', 'TaskBudgetHistories::view/$1');
    $routes->match(['GET', 'POST'], 'task-budget-histories/delete/(:num)', 'TaskBudgetHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'task_budget_histories/delete/(:num)', 'TaskBudgetHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'task-budget-histories/delete', 'TaskBudgetHistories::delete');
    $routes->match(['GET', 'POST'], 'task_budget_histories/delete', 'TaskBudgetHistories::delete');
    $routes->post('task-budget-histories/bulk-delete', 'TaskBudgetHistories::bulkDelete');
    $routes->post('task_budget_histories/bulk-delete', 'TaskBudgetHistories::bulkDelete');
    $routes->get('task-budget-histories/export', 'TaskBudgetHistories::export');
    $routes->get('task_budget_histories/export', 'TaskBudgetHistories::export');
    $routes->get('task-budget-histories/export-pdf', 'TaskBudgetHistories::export_pdf');
    $routes->get('task_budget_histories/export-pdf', 'TaskBudgetHistories::export_pdf');
    $routes->get('task-budget-histories/single-pdf/(:num)', 'TaskBudgetHistories::single_pdf/$1');
    $routes->get('task_budget_histories/single-pdf/(:num)', 'TaskBudgetHistories::single_pdf/$1');
    $routes->get('task-budget-histories/single_pdf/(:num)', 'TaskBudgetHistories::single_pdf/$1');
    $routes->get('task_budget_histories/single_pdf/(:num)', 'TaskBudgetHistories::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/task_budget_histories/*
    $routes->get('administrator/task_budget_histories', 'TaskBudgetHistories::index');
    $routes->get('administrator/task_budget_histories/index', 'TaskBudgetHistories::index');
    $routes->get('administrator/task_budget_histories/index/(:num)', 'TaskBudgetHistories::index/$1');
    $routes->get('administrator/task_budget_histories/view/(:num)', 'TaskBudgetHistories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/task_budget_histories/delete/(:num)', 'TaskBudgetHistories::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/task_budget_histories/delete', 'TaskBudgetHistories::delete');
    $routes->get('administrator/task_budget_histories/export', 'TaskBudgetHistories::export');
    $routes->get('administrator/task_budget_histories/export_pdf', 'TaskBudgetHistories::export_pdf');
    $routes->get('administrator/task_budget_histories/single_pdf/(:num)', 'TaskBudgetHistories::single_pdf/$1');

    // Wave 3: Review Draft Video (Full Parity with CI3 modules/drafts)
    $routes->get('drafts', 'Drafts::index');
    $routes->post('drafts/get-datatable', 'Drafts::getDatatable');
    $routes->get('drafts/get-one/(:num)', 'Drafts::getOne/$1');
    $routes->get('drafts/view/(:num)', 'Drafts::view/$1');
    $routes->post('drafts/save', 'Drafts::save');
    $routes->post('drafts/submit', 'Drafts::submit');
    $routes->match(['GET', 'POST'], 'drafts/submit-review/(:num)', 'Drafts::submitReview/$1');
    $routes->match(['GET', 'POST'], 'drafts/approve/(:num)', 'Drafts::approve/$1');
    $routes->match(['GET', 'POST'], 'drafts/revision/(:num)', 'Drafts::revision/$1');
    $routes->match(['GET', 'POST'], 'drafts/delete/(:num)', 'Drafts::delete/$1');
    $routes->post('drafts/bulk-delete', 'Drafts::bulkDelete');
    $routes->get('drafts/export', 'Drafts::export');

    // CI3 Parity Aliases for drafts
    $routes->get('administrator/drafts', 'Drafts::index');
    $routes->get('administrator/drafts/index', 'Drafts::index');
    $routes->get('administrator/drafts/view/(:num)', 'Drafts::view/$1');
    $routes->get('administrator/drafts/edit/(:num)', 'Drafts::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/drafts/submit/(:num)', 'Drafts::submitReview/$1');
    $routes->match(['GET', 'POST'], 'administrator/drafts/approve/(:num)', 'Drafts::approve/$1');
    $routes->match(['GET', 'POST'], 'administrator/drafts/revision/(:num)', 'Drafts::revision/$1');
    $routes->match(['GET', 'POST'], 'administrator/drafts/delete/(:num)', 'Drafts::delete/$1');
    $routes->get('administrator/drafts/export', 'Drafts::export');

    // Wave 3: Logistik & Pengiriman Sampel KOL (Full Parity with CI3 modules/kol_shipments)
    $routes->get('shipments', 'Shipments::index');
    $routes->post('shipments/get-datatable', 'Shipments::getDatatable');
    $routes->get('shipments/get-one/(:num)', 'Shipments::getOne/$1');
    $routes->get('shipments/view/(:num)', 'Shipments::view/$1');
    $routes->get('shipments/add', 'Shipments::add');
    $routes->post('shipments/add_save', 'Shipments::add_save');
    $routes->get('shipments/edit/(:num)', 'Shipments::edit/$1');
    $routes->post('shipments/edit_save/(:num)', 'Shipments::edit_save/$1');
    $routes->post('shipments/save', 'Shipments::save');
    $routes->post('shipments/update-status/(:num)', 'Shipments::updateStatus/$1');
    $routes->post('shipments/update_status', 'Shipments::update_status');
    $routes->post('shipments/bulk-update-status', 'Shipments::bulk_update_status');
    $routes->post('shipments/bulk_update_status', 'Shipments::bulk_update_status');
    $routes->match(['GET', 'POST'], 'shipments/delete/(:num)', 'Shipments::delete/$1');
    $routes->match(['GET', 'POST'], 'shipments/delete', 'Shipments::delete');
    $routes->post('shipments/bulk-delete', 'Shipments::bulkDelete');
    $routes->get('shipments/export', 'Shipments::export');
    $routes->get('shipments/export-pdf', 'Shipments::export_pdf');
    $routes->get('shipments/export_pdf', 'Shipments::export_pdf');
    $routes->get('shipments/single-pdf/(:num)', 'Shipments::single_pdf/$1');
    $routes->get('shipments/single_pdf/(:num)', 'Shipments::single_pdf/$1');

    // CI3 Parity Aliases for Kol Shipments (modules/kol_shipments)
    $routes->get('administrator/kol_shipments', 'Shipments::index');
    $routes->get('administrator/kol_shipments/index', 'Shipments::index');
    $routes->get('administrator/kol_shipments/index/(:num)', 'Shipments::index');
    $routes->get('administrator/kol_shipments/add', 'Shipments::add');
    $routes->post('administrator/kol_shipments/add_save', 'Shipments::add_save');
    $routes->get('administrator/kol_shipments/edit/(:num)', 'Shipments::edit/$1');
    $routes->post('administrator/kol_shipments/edit_save/(:num)', 'Shipments::edit_save/$1');
    $routes->get('administrator/kol_shipments/view/(:num)', 'Shipments::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_shipments/delete/(:num)', 'Shipments::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_shipments/delete', 'Shipments::delete');
    $routes->post('administrator/kol_shipments/update_status', 'Shipments::update_status');
    $routes->post('administrator/kol_shipments/bulk_update_status', 'Shipments::bulk_update_status');
    $routes->get('administrator/kol_shipments/export', 'Shipments::export');
    $routes->get('administrator/kol_shipments/export_pdf', 'Shipments::export_pdf');
    $routes->get('administrator/kol_shipments/single_pdf/(:num)', 'Shipments::single_pdf/$1');

    // Wave 3.1: Logistik & Rincian Item Sampel Pengiriman (Full Parity with CI3 modules/kol_shipment_items)
    $routes->get('shipment-items', 'ShipmentItems::index');
    $routes->get('kol-shipment-items', 'ShipmentItems::index');
    $routes->post('shipment-items/get-datatable', 'ShipmentItems::getDatatable');
    $routes->post('kol-shipment-items/get-datatable', 'ShipmentItems::getDatatable');
    $routes->get('shipment-items/get-one/(:num)', 'ShipmentItems::getOne/$1');
    $routes->get('shipment-items/view/(:num)', 'ShipmentItems::view/$1');
    $routes->get('shipment-items/add', 'ShipmentItems::add');
    $routes->post('shipment-items/add_save', 'ShipmentItems::add_save');
    $routes->get('shipment-items/edit/(:num)', 'ShipmentItems::edit/$1');
    $routes->post('shipment-items/edit_save/(:num)', 'ShipmentItems::edit_save/$1');
    $routes->match(['GET', 'POST'], 'shipment-items/delete/(:num)', 'ShipmentItems::delete/$1');
    $routes->match(['GET', 'POST'], 'shipment-items/delete', 'ShipmentItems::delete');
    $routes->post('shipment-items/bulk-delete', 'ShipmentItems::bulkDelete');
    $routes->get('shipment-items/export', 'ShipmentItems::export');
    $routes->get('shipment-items/export-pdf', 'ShipmentItems::export_pdf');
    $routes->get('shipment-items/export_pdf', 'ShipmentItems::export_pdf');
    $routes->get('shipment-items/single-pdf/(:num)', 'ShipmentItems::single_pdf/$1');
    $routes->get('shipment-items/single_pdf/(:num)', 'ShipmentItems::single_pdf/$1');

    // CI3 Parity Aliases for Kol Shipment Items (modules/kol_shipment_items)
    $routes->get('administrator/kol_shipment_items', 'ShipmentItems::index');
    $routes->get('administrator/kol_shipment_items/index', 'ShipmentItems::index');
    $routes->get('administrator/kol_shipment_items/index/(:num)', 'ShipmentItems::index');
    $routes->get('administrator/kol_shipment_items/add', 'ShipmentItems::add');
    $routes->post('administrator/kol_shipment_items/add_save', 'ShipmentItems::add_save');
    $routes->get('administrator/kol_shipment_items/edit/(:num)', 'ShipmentItems::edit/$1');
    $routes->post('administrator/kol_shipment_items/edit_save/(:num)', 'ShipmentItems::edit_save/$1');
    $routes->get('administrator/kol_shipment_items/view/(:num)', 'ShipmentItems::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_shipment_items/delete/(:num)', 'ShipmentItems::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_shipment_items/delete', 'ShipmentItems::delete');
    $routes->get('administrator/kol_shipment_items/export', 'ShipmentItems::export');
    $routes->get('administrator/kol_shipment_items/export_pdf', 'ShipmentItems::export_pdf');
    $routes->get('administrator/kol_shipment_items/single_pdf/(:num)', 'ShipmentItems::single_pdf/$1');

    // Wave 4: Postings, Metrics, Sentiment & Kalender (Full Parity with CI3 modules/postings)
    $routes->get('postings', 'Postings::index');
    $routes->post('postings/get-datatable', 'Postings::getDatatable');
    $routes->get('postings/get-one/(:num)', 'Postings::getOne/$1');
    $routes->get('postings/view/(:num)', 'Postings::view/$1');
    $routes->post('postings/save', 'Postings::save');
    $routes->post('postings/update-metrics/(:num)', 'Postings::updateMetrics/$1');
    $routes->post('postings/analyze-sentiment/(:num)', 'Postings::analyzeSentiment/$1');
    $routes->match(['GET', 'POST'], 'postings/trigger-scrape/(:num)', 'Postings::triggerScrape/$1');
    $routes->match(['GET', 'POST'], 'postings/trigger_scrape/(:num)', 'Postings::triggerScrape/$1');
    $routes->match(['GET', 'POST'], 'postings/delete/(:num)', 'Postings::delete/$1');
    $routes->post('postings/bulk-delete', 'Postings::bulkDelete');
    $routes->get('postings/export', 'Postings::export');

    // CI3 Parity Aliases for postings
    $routes->get('administrator/postings', 'Postings::index');
    $routes->get('administrator/postings/index', 'Postings::index');
    $routes->get('administrator/postings/view/(:num)', 'Postings::view/$1');
    $routes->get('administrator/postings/edit/(:num)', 'Postings::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/postings/trigger_scrape/(:num)', 'Postings::triggerScrape/$1');
    $routes->match(['GET', 'POST'], 'administrator/postings/delete/(:num)', 'Postings::delete/$1');
    $routes->get('administrator/postings/export', 'Postings::export');

    // Wave 4: Posting Metrics & Scraping Performance Snapshots (Full Parity with CI3 modules/posting_metrics)
    $routes->get('posting-metrics', 'PostingMetrics::index');
    $routes->get('posting-metrics/index', 'PostingMetrics::index');
    $routes->get('posting_metrics', 'PostingMetrics::index');
    $routes->get('posting_metrics/index', 'PostingMetrics::index');
    $routes->post('posting-metrics/get-datatable', 'PostingMetrics::getDatatable');
    $routes->post('posting_metrics/get-datatable', 'PostingMetrics::getDatatable');
    $routes->get('posting-metrics/get-one/(:num)', 'PostingMetrics::getOne/$1');
    $routes->get('posting_metrics/get-one/(:num)', 'PostingMetrics::getOne/$1');
    $routes->get('posting-metrics/view/(:num)', 'PostingMetrics::view/$1');
    $routes->get('posting_metrics/view/(:num)', 'PostingMetrics::view/$1');
    $routes->match(['GET', 'POST'], 'posting-metrics/delete/(:num)', 'PostingMetrics::delete/$1');
    $routes->match(['GET', 'POST'], 'posting_metrics/delete/(:num)', 'PostingMetrics::delete/$1');
    $routes->post('posting-metrics/bulk-delete', 'PostingMetrics::bulkDelete');
    $routes->post('posting_metrics/bulk-delete', 'PostingMetrics::bulkDelete');
    $routes->get('posting-metrics/export', 'PostingMetrics::export');
    $routes->get('posting_metrics/export', 'PostingMetrics::export');
    $routes->get('posting-metrics/export-pdf', 'PostingMetrics::export_pdf');
    $routes->get('posting_metrics/export-pdf', 'PostingMetrics::export_pdf');
    $routes->get('posting-metrics/single-pdf/(:num)', 'PostingMetrics::single_pdf/$1');
    $routes->get('posting_metrics/single-pdf/(:num)', 'PostingMetrics::single_pdf/$1');

    // CI3 Legacy Aliases: administrator/posting_metrics/*
    $routes->get('administrator/posting_metrics', 'PostingMetrics::index');
    $routes->get('administrator/posting_metrics/index', 'PostingMetrics::index');
    $routes->get('administrator/posting_metrics/index/(:num)', 'PostingMetrics::index/$1');
    $routes->get('administrator/posting_metrics/view/(:num)', 'PostingMetrics::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/posting_metrics/delete/(:num)', 'PostingMetrics::delete/$1');
    $routes->get('administrator/posting_metrics/delete', 'PostingMetrics::delete');
    $routes->get('administrator/posting_metrics/export', 'PostingMetrics::export');
    $routes->get('administrator/posting_metrics/export_pdf', 'PostingMetrics::export_pdf');
    $routes->get('administrator/posting_metrics/single_pdf/(:num)', 'PostingMetrics::single_pdf/$1');

    // Wave 4: Kalender Posting & Timeline Visualizer (Full Parity with CI3 modules/kalender)
    $routes->get('kalender', 'Kalender::index');
    $routes->get('kalender/index', 'Kalender::index');
    $routes->get('calendar', 'Kalender::index');
    $routes->get('kalender/get-events', 'Kalender::getEvents');
    $routes->get('kalender/get_events', 'Kalender::get_events');

    // CI3 Legacy Aliases: administrator/kalender/*
    $routes->get('administrator/kalender', 'Kalender::index');
    $routes->get('administrator/kalender/index', 'Kalender::index');
    $routes->get('administrator/kalender/get_events', 'Kalender::get_events');
    $routes->get('administrator/kalender/get-events', 'Kalender::getEvents');

    // Wave 7: Backoffice CMS Artikel Blog & Landing
    $routes->get('articles', 'Articles::index');
    $routes->post('articles/get-datatable', 'Articles::getDatatable');
    $routes->get('articles/get-one/(:num)', 'Articles::getOne/$1');
    $routes->post('articles/save', 'Articles::save');
    $routes->post('articles/delete/(:num)', 'Articles::delete/$1');

    // Wave 8: WhatsApp Communication Console & Broadcast (Full Parity with CI3 modules/whatsapp)
    $routes->get('whatsapp', 'Whatsapp::index');
    $routes->get('whatsapp/index', 'Whatsapp::index');
    $routes->get('whatsapp/index/(:num)', 'Whatsapp::index/$1');
    $routes->get('whatsapp/chat', 'Whatsapp::chat');
    $routes->get('whatsapp/chat/(:num)', 'Whatsapp::chat/$1');
    $routes->match(['GET', 'POST'], 'whatsapp/send', 'Whatsapp::ajaxSendMessage');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax-send-message', 'Whatsapp::ajaxSendMessage');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax_send_message', 'Whatsapp::ajaxSendMessage');
    $routes->match(['GET', 'POST'], 'whatsapp/send-media', 'Whatsapp::ajaxSendMedia');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax-send-media', 'Whatsapp::ajaxSendMedia');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax_send_media', 'Whatsapp::ajaxSendMedia');
    $routes->match(['GET', 'POST'], 'whatsapp/poll/(:num)', 'Whatsapp::ajaxPollMessages/$1');
    $routes->match(['GET', 'POST'], 'whatsapp/poll', 'Whatsapp::ajaxPollMessages');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax-poll-messages', 'Whatsapp::ajaxPollMessages');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax_poll_messages', 'Whatsapp::ajaxPollMessages');
    $routes->get('whatsapp/unread-counts', 'Whatsapp::ajaxUnreadCounts');
    $routes->get('whatsapp/ajax-unread-counts', 'Whatsapp::ajaxUnreadCounts');
    $routes->get('whatsapp/ajax_unread_counts', 'Whatsapp::ajaxUnreadCounts');
    $routes->get('whatsapp/session-status', 'Whatsapp::ajaxSessionStatus');
    $routes->get('whatsapp/ajax-session-status', 'Whatsapp::ajaxSessionStatus');
    $routes->get('whatsapp/ajax_session_status', 'Whatsapp::ajaxSessionStatus');
    $routes->match(['GET', 'POST'], 'whatsapp/start-session', 'Whatsapp::ajaxStartSession');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax-start-session', 'Whatsapp::ajaxStartSession');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax_start_session', 'Whatsapp::ajaxStartSession');
    $routes->match(['GET', 'POST'], 'whatsapp/logout-waha', 'Whatsapp::ajaxLogoutWaha');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax-logout-waha', 'Whatsapp::ajaxLogoutWaha');
    $routes->match(['GET', 'POST'], 'whatsapp/ajax_logout_waha', 'Whatsapp::ajaxLogoutWaha');
    $routes->get('whatsapp/broadcast-kols', 'Whatsapp::ajaxGetBroadcastKols');
    $routes->get('whatsapp/ajax-get-broadcast-kols', 'Whatsapp::ajaxGetBroadcastKols');
    $routes->get('whatsapp/ajax_get_broadcast_kols', 'Whatsapp::ajaxGetBroadcastKols');
    $routes->post('whatsapp/send-broadcast-item', 'Whatsapp::ajaxSendBroadcastItem');
    $routes->post('whatsapp/ajax-send-broadcast-item', 'Whatsapp::ajaxSendBroadcastItem');
    $routes->post('whatsapp/ajax_send_broadcast_item', 'Whatsapp::ajaxSendBroadcastItem');

    // CI3 Parity Aliases for WhatsApp (modules/whatsapp)
    $routes->get('administrator/whatsapp', 'Whatsapp::index');
    $routes->get('administrator/whatsapp/index', 'Whatsapp::index');
    $routes->get('administrator/whatsapp/index/(:num)', 'Whatsapp::index/$1');
    $routes->get('administrator/whatsapp/chat', 'Whatsapp::chat');
    $routes->get('administrator/whatsapp/chat/(:num)', 'Whatsapp::chat/$1');
    $routes->match(['GET', 'POST'], 'administrator/whatsapp/ajax_send_message', 'Whatsapp::ajaxSendMessage');
    $routes->match(['GET', 'POST'], 'administrator/whatsapp/ajax_send_media', 'Whatsapp::ajaxSendMedia');
    $routes->match(['GET', 'POST'], 'administrator/whatsapp/ajax_poll_messages', 'Whatsapp::ajaxPollMessages');
    $routes->match(['GET', 'POST'], 'administrator/whatsapp/ajax_poll_messages/(:num)', 'Whatsapp::ajaxPollMessages/$1');
    $routes->get('administrator/whatsapp/ajax_unread_counts', 'Whatsapp::ajaxUnreadCounts');
    $routes->get('administrator/whatsapp/ajax_session_status', 'Whatsapp::ajaxSessionStatus');
    $routes->match(['GET', 'POST'], 'administrator/whatsapp/ajax_start_session', 'Whatsapp::ajaxStartSession');
    $routes->match(['GET', 'POST'], 'administrator/whatsapp/ajax_logout_waha', 'Whatsapp::ajaxLogoutWaha');
    $routes->get('administrator/whatsapp/ajax_get_broadcast_kols', 'Whatsapp::ajaxGetBroadcastKols');
    $routes->post('administrator/whatsapp/ajax_send_broadcast_item', 'Whatsapp::ajaxSendBroadcastItem');

    // Wave 9: Monitoring Scraping & Apify Jobs (Full Parity with CI3 modules/apify_jobs)
    $routes->get('apify-jobs', 'ApifyJobs::index');
    $routes->get('apify-jobs/index', 'ApifyJobs::index');
    $routes->match(['GET', 'POST'], 'apify-jobs/get-datatable', 'ApifyJobs::getDatatable');
    $routes->get('apify-jobs/get-one/(:num)', 'ApifyJobs::getOne/$1');
    $routes->get('apify-jobs/view/(:num)', 'ApifyJobs::view/$1');
    $routes->match(['GET', 'POST'], 'apify-jobs/retry/(:num)', 'ApifyJobs::retry/$1');
    $routes->match(['GET', 'POST'], 'apify-jobs/trigger', 'ApifyJobs::triggerNew');
    $routes->match(['GET', 'POST'], 'apify-jobs/delete/(:num)', 'ApifyJobs::delete/$1');
    $routes->match(['GET', 'POST'], 'apify-jobs/delete', 'ApifyJobs::delete');
    $routes->post('apify-jobs/bulk-delete', 'ApifyJobs::bulkDelete');
    $routes->get('apify-jobs/export', 'ApifyJobs::export');
    $routes->get('apify-jobs/export-pdf', 'ApifyJobs::export_pdf');
    $routes->get('apify-jobs/export_pdf', 'ApifyJobs::export_pdf');
    $routes->get('apify-jobs/single-pdf/(:num)', 'ApifyJobs::single_pdf/$1');
    $routes->get('apify-jobs/single_pdf/(:num)', 'ApifyJobs::single_pdf/$1');

    // Snake_case aliases (apify_jobs)
    $routes->get('apify_jobs', 'ApifyJobs::index');
    $routes->get('apify_jobs/index', 'ApifyJobs::index');
    $routes->match(['GET', 'POST'], 'apify_jobs/get-datatable', 'ApifyJobs::getDatatable');
    $routes->get('apify_jobs/get-one/(:num)', 'ApifyJobs::getOne/$1');
    $routes->get('apify_jobs/view/(:num)', 'ApifyJobs::view/$1');
    $routes->match(['GET', 'POST'], 'apify_jobs/retry/(:num)', 'ApifyJobs::retry/$1');
    $routes->match(['GET', 'POST'], 'apify_jobs/trigger', 'ApifyJobs::triggerNew');
    $routes->match(['GET', 'POST'], 'apify_jobs/delete/(:num)', 'ApifyJobs::delete/$1');
    $routes->match(['GET', 'POST'], 'apify_jobs/delete', 'ApifyJobs::delete');
    $routes->post('apify_jobs/bulk-delete', 'ApifyJobs::bulkDelete');
    $routes->get('apify_jobs/export', 'ApifyJobs::export');
    $routes->get('apify_jobs/export-pdf', 'ApifyJobs::export_pdf');
    $routes->get('apify_jobs/export_pdf', 'ApifyJobs::export_pdf');
    $routes->get('apify_jobs/single-pdf/(:num)', 'ApifyJobs::single_pdf/$1');
    $routes->get('apify_jobs/single_pdf/(:num)', 'ApifyJobs::single_pdf/$1');

    // Scraping-jobs alias backwards-compatibility
    $routes->get('scraping-jobs', 'ApifyJobs::index');
    $routes->get('scraping-jobs/index', 'ApifyJobs::index');
    $routes->match(['GET', 'POST'], 'scraping-jobs/get-datatable', 'ApifyJobs::getDatatable');
    $routes->get('scraping-jobs/get-one/(:num)', 'ApifyJobs::getOne/$1');
    $routes->get('scraping-jobs/view/(:num)', 'ApifyJobs::view/$1');
    $routes->match(['GET', 'POST'], 'scraping-jobs/retry/(:num)', 'ApifyJobs::retry/$1');
    $routes->match(['GET', 'POST'], 'scraping-jobs/trigger', 'ApifyJobs::triggerNew');
    $routes->match(['GET', 'POST'], 'scraping-jobs/delete/(:num)', 'ApifyJobs::delete/$1');
    $routes->match(['GET', 'POST'], 'scraping-jobs/delete', 'ApifyJobs::delete');
    $routes->post('scraping-jobs/bulk-delete', 'ApifyJobs::bulkDelete');
    $routes->get('scraping-jobs/export', 'ApifyJobs::export');
    $routes->get('scraping-jobs/export-pdf', 'ApifyJobs::export_pdf');
    $routes->get('scraping-jobs/single-pdf/(:num)', 'ApifyJobs::single_pdf/$1');

    // CI3 Legacy Aliases (administrator/apify_jobs)
    $routes->get('administrator/apify_jobs', 'ApifyJobs::index');
    $routes->get('administrator/apify_jobs/index', 'ApifyJobs::index');
    $routes->get('administrator/apify_jobs/index/(:num)', 'ApifyJobs::index');
    $routes->get('administrator/apify_jobs/view/(:num)', 'ApifyJobs::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/apify_jobs/delete/(:num)', 'ApifyJobs::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/apify_jobs/delete', 'ApifyJobs::delete');
    $routes->get('administrator/apify_jobs/export', 'ApifyJobs::export');
    $routes->get('administrator/apify_jobs/export_pdf', 'ApifyJobs::export_pdf');
    $routes->get('administrator/apify_jobs/single_pdf/(:num)', 'ApifyJobs::single_pdf/$1');

    // Wave 12: Direktori & Performance KOL (View Only Intelligence)
    $routes->get('kols', 'Kols::index');
    $routes->post('kols/get-datatable', 'Kols::getDatatable');
    $routes->get('kols/view/(:num)', 'Kols::view/$1');
    $routes->get('kols/performance-card/(:num)', 'Kols::performanceCard/$1');
    $routes->get('kols/chart-data/(:num)', 'Kols::chartData/$1');

    // Wave 14: Kategori & Tagging KOL (Full Parity with CI3 modules/kol_categories)
    $routes->get('kol-categories', 'KolCategories::index');
    $routes->post('kol-categories/get-datatable', 'KolCategories::getDatatable');
    $routes->get('kol-categories/get-one/(:num)', 'KolCategories::getOne/$1');
    $routes->get('kol-categories/view/(:num)', 'KolCategories::view/$1');
    $routes->post('kol-categories/save', 'KolCategories::save');
    $routes->match(['GET', 'POST'], 'kol-categories/delete/(:num)', 'KolCategories::delete/$1');
    $routes->post('kol-categories/bulk-delete', 'KolCategories::bulkDelete');
    $routes->get('kol-categories/export', 'KolCategories::export');

    // CI3 Parity Aliases for kol_categories
    $routes->get('kol_categories', 'KolCategories::index');
    $routes->post('kol_categories/get-datatable', 'KolCategories::getDatatable');
    $routes->get('kol_categories/view/(:num)', 'KolCategories::view/$1');
    $routes->match(['GET', 'POST'], 'kol_categories/delete/(:num)', 'KolCategories::delete/$1');
    $routes->get('kol_categories/export', 'KolCategories::export');
    $routes->get('administrator/kol_categories', 'KolCategories::index');
    $routes->get('administrator/kol_categories/index', 'KolCategories::index');
    $routes->get('administrator/kol_categories/view/(:num)', 'KolCategories::view/$1');
    $routes->get('administrator/kol_categories/edit/(:num)', 'KolCategories::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_categories/delete/(:num)', 'KolCategories::delete/$1');
    $routes->get('administrator/kol_categories/export', 'KolCategories::export');

    // Wave 15: Akun Platform KOL (Full Parity with CI3 modules/kol_platforms)
    $routes->get('kol-platforms', 'KolPlatforms::index');
    $routes->post('kol-platforms/get-datatable', 'KolPlatforms::getDatatable');
    $routes->get('kol-platforms/get-one/(:num)', 'KolPlatforms::getOne/$1');
    $routes->get('kol-platforms/view/(:num)', 'KolPlatforms::view/$1');
    $routes->post('kol-platforms/save', 'KolPlatforms::save');
    $routes->match(['GET', 'POST'], 'kol-platforms/sync/(:num)', 'KolPlatforms::sync/$1');
    $routes->match(['GET', 'POST'], 'kol-platforms/delete/(:num)', 'KolPlatforms::delete/$1');
    $routes->post('kol-platforms/bulk-delete', 'KolPlatforms::bulkDelete');
    $routes->get('kol-platforms/export', 'KolPlatforms::export');

    // CI3 Parity Aliases for kol_platforms
    $routes->get('kol_platforms', 'KolPlatforms::index');
    $routes->post('kol_platforms/get-datatable', 'KolPlatforms::getDatatable');
    $routes->get('kol_platforms/view/(:num)', 'KolPlatforms::view/$1');
    $routes->match(['GET', 'POST'], 'kol_platforms/sync/(:num)', 'KolPlatforms::sync/$1');
    $routes->match(['GET', 'POST'], 'kol_platforms/delete/(:num)', 'KolPlatforms::delete/$1');
    $routes->get('kol_platforms/export', 'KolPlatforms::export');
    $routes->get('administrator/kol_platforms', 'KolPlatforms::index');
    $routes->get('administrator/kol_platforms/index', 'KolPlatforms::index');
    $routes->get('administrator/kol_platforms/view/(:num)', 'KolPlatforms::view/$1');
    $routes->get('administrator/kol_platforms/edit/(:num)', 'KolPlatforms::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_platforms/sync/(:num)', 'KolPlatforms::sync/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_platforms/delete/(:num)', 'KolPlatforms::delete/$1');
    $routes->get('administrator/kol_platforms/export', 'KolPlatforms::export');

    // Wave 16: Master Rate Card KOL (Full Parity with CI3 modules/kol_rate_cards)
    $routes->get('kol-rate-cards', 'KolRateCards::index');
    $routes->post('kol-rate-cards/get-datatable', 'KolRateCards::getDatatable');
    $routes->get('kol-rate-cards/get-one/(:num)', 'KolRateCards::getOne/$1');
    $routes->get('kol-rate-cards/view/(:num)', 'KolRateCards::view/$1');
    $routes->post('kol-rate-cards/save', 'KolRateCards::save');
    $routes->match(['GET', 'POST'], 'kol-rate-cards/delete/(:num)', 'KolRateCards::delete/$1');
    $routes->post('kol-rate-cards/bulk-delete', 'KolRateCards::bulkDelete');
    $routes->get('kol-rate-cards/export', 'KolRateCards::export');

    // CI3 Parity Aliases for kol_rate_cards
    $routes->get('kol_rate_cards', 'KolRateCards::index');
    $routes->post('kol_rate_cards/get-datatable', 'KolRateCards::getDatatable');
    $routes->get('kol_rate_cards/view/(:num)', 'KolRateCards::view/$1');
    $routes->match(['GET', 'POST'], 'kol_rate_cards/delete/(:num)', 'KolRateCards::delete/$1');
    $routes->get('kol_rate_cards/export', 'KolRateCards::export');
    $routes->get('administrator/kol_rate_cards', 'KolRateCards::index');
    $routes->get('administrator/kol_rate_cards/index', 'KolRateCards::index');
    $routes->get('administrator/kol_rate_cards/view/(:num)', 'KolRateCards::view/$1');
    $routes->get('administrator/kol_rate_cards/edit/(:num)', 'KolRateCards::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/kol_rate_cards/delete/(:num)', 'KolRateCards::delete/$1');
    $routes->get('administrator/kol_rate_cards/export', 'KolRateCards::export');

    // Wave 17: Varian Produk Kampanye (Full Parity with CI3 modules/campaign_variants)
    $routes->get('campaign-variants', 'CampaignVariants::index');
    $routes->post('campaign-variants/get-datatable', 'CampaignVariants::getDatatable');
    $routes->get('campaign-variants/get-one/(:num)', 'CampaignVariants::getOne/$1');
    $routes->get('campaign-variants/view/(:num)', 'CampaignVariants::view/$1');
    $routes->post('campaign-variants/save', 'CampaignVariants::save');
    $routes->match(['GET', 'POST'], 'campaign-variants/delete/(:num)', 'CampaignVariants::delete/$1');
    $routes->post('campaign-variants/bulk-delete', 'CampaignVariants::bulkDelete');
    $routes->get('campaign-variants/export', 'CampaignVariants::export');

    // CI3 Parity Aliases for campaign_variants
    $routes->get('campaign_variants', 'CampaignVariants::index');
    $routes->post('campaign_variants/get-datatable', 'CampaignVariants::getDatatable');
    $routes->get('campaign_variants/view/(:num)', 'CampaignVariants::view/$1');
    $routes->match(['GET', 'POST'], 'campaign_variants/delete/(:num)', 'CampaignVariants::delete/$1');
    $routes->get('campaign_variants/export', 'CampaignVariants::export');
    $routes->get('administrator/campaign_variants', 'CampaignVariants::index');
    $routes->get('administrator/campaign_variants/index', 'CampaignVariants::index');
    $routes->get('administrator/campaign_variants/view/(:num)', 'CampaignVariants::view/$1');
    $routes->get('administrator/campaign_variants/edit/(:num)', 'CampaignVariants::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/campaign_variants/delete/(:num)', 'CampaignVariants::delete/$1');
    $routes->get('administrator/campaign_variants/export', 'CampaignVariants::export');

    // Leaderboard & Komparasi KOL
    $routes->get('rankings', 'Dashboard::rankings');
    $routes->post('dashboard/compare-kol', 'Dashboard::compareKol');
});

// --------------------------------------------------------------------
// CAMPAIGN MANAGEMENT — AKSES STRATEGIS, EVALUASI & KEUANGAN (LEADER & SUPERADMIN)
// --------------------------------------------------------------------
$routes->group('', ['filter' => 'auth:admin,leader'], static function ($routes) {
    // Wave 5: Evaluasi Kinerja Internal Tim Operasional
    $routes->get('evaluations', 'InternalEvaluation::index');
    $routes->get('evaluations/staff', 'InternalEvaluation::staff');
    $routes->get('evaluations/export-excel', 'InternalEvaluation::exportExcel');
    $routes->get('internal-evaluation', 'InternalEvaluation::index');
    $routes->get('internal-evaluation/export-excel', 'InternalEvaluation::exportExcel');
    // Wave 6: Executive Finance Hub
    $routes->get('finance', 'FinanceHub::index');
    $routes->get('finance-hub', 'FinanceHub::index');
    $routes->post('finance/get-datatable', 'FinanceHub::getDatatable');
    $routes->post('finance-hub/get-datatable', 'FinanceHub::getDatatable');
    $routes->post('finance/reject-invoice/(:num)', 'FinanceHub::rejectInvoice/$1');
    $routes->post('finance/upload-bupot/(:num)', 'FinanceHub::uploadBupot/$1');
    $routes->get('finance/invoice-print/(:num)', 'FinanceHub::printInvoice/$1');

    // Wave 6: Invoices & Finansial
    $routes->get('invoices', 'Invoices::index');
    $routes->get('invoices/index', 'Invoices::index');
    $routes->match(['GET', 'POST'], 'invoices/get-datatable', 'Invoices::getDatatable');
    $routes->match(['GET', 'POST'], 'invoices/getDatatable', 'Invoices::getDatatable');
    $routes->get('invoices/get-one/(:num)', 'Invoices::getOne/$1');
    $routes->get('invoices/getOne/(:num)', 'Invoices::getOne/$1');
    $routes->get('invoices/view/(:num)', 'Invoices::view/$1');
    $routes->get('invoices/add', 'Invoices::add');
    $routes->post('invoices/add_save', 'Invoices::add_save');
    $routes->post('invoices/add-save', 'Invoices::add_save');
    $routes->match(['GET', 'POST'], 'invoices/create-from-deal/(:num)', 'Invoices::createFromDeal/$1');
    $routes->match(['GET', 'POST'], 'invoices/create_from_deal/(:num)', 'Invoices::createFromDeal/$1');
    $routes->match(['GET', 'POST'], 'invoices/createFromDeal/(:num)', 'Invoices::createFromDeal/$1');
    $routes->get('invoices/edit/(:num)', 'Invoices::edit/$1');
    $routes->post('invoices/edit_save/(:num)', 'Invoices::edit_save/$1');
    $routes->post('invoices/edit-save/(:num)', 'Invoices::edit_save/$1');
    $routes->match(['GET', 'POST'], 'invoices/delete/(:num)', 'Invoices::delete/$1');
    $routes->match(['GET', 'POST'], 'invoices/delete', 'Invoices::delete');
    $routes->post('invoices/bulk-delete', 'Invoices::bulkDelete');
    $routes->post('invoices/bulkDelete', 'Invoices::bulkDelete');
    $routes->get('invoices/export', 'Invoices::export');
    $routes->get('invoices/export-pdf', 'Invoices::export_pdf');
    $routes->get('invoices/export_pdf', 'Invoices::export_pdf');
    $routes->get('invoices/single-pdf/(:num)', 'Invoices::single_pdf/$1');
    $routes->get('invoices/single_pdf/(:num)', 'Invoices::single_pdf/$1');
    $routes->get('invoices/print-pdf/(:num)', 'Invoices::print_pdf/$1');
    $routes->get('invoices/print_pdf/(:num)', 'Invoices::print_pdf/$1');
    $routes->get('invoices/my-invoices', 'Invoices::my_invoices');
    $routes->get('invoices/my_invoices', 'Invoices::my_invoices');
    $routes->match(['GET', 'POST'], 'invoices/upload_invoice_file_file', 'Invoices::upload_invoice_file_file');
    $routes->match(['GET', 'POST'], 'invoices/delete_invoice_file_file/(:segment)', 'Invoices::delete_invoice_file_file/$1');
    $routes->match(['GET', 'POST'], 'invoices/delete_invoice_file_file', 'Invoices::delete_invoice_file_file');
    $routes->get('invoices/get_invoice_file_file/(:num)', 'Invoices::get_invoice_file_file/$1');
    $routes->post('invoices/verify/(:num)', 'Invoices::verify/$1');
    $routes->post('invoices/save-payment/(:num)', 'Invoices::savePayment/$1');

    // CI3 Legacy Aliases (administrator/invoices & administrator/my_invoices)
    $routes->get('administrator/invoices', 'Invoices::index');
    $routes->get('administrator/invoices/index', 'Invoices::index');
    $routes->get('administrator/invoices/index/(:num)', 'Invoices::index');
    $routes->get('administrator/invoices/add', 'Invoices::add');
    $routes->post('administrator/invoices/add_save', 'Invoices::add_save');
    $routes->match(['GET', 'POST'], 'administrator/invoices/create_from_deal/(:num)', 'Invoices::createFromDeal/$1');
    $routes->get('administrator/invoices/edit/(:num)', 'Invoices::edit/$1');
    $routes->post('administrator/invoices/edit_save/(:num)', 'Invoices::edit_save/$1');
    $routes->match(['GET', 'POST'], 'administrator/invoices/delete/(:num)', 'Invoices::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/invoices/delete', 'Invoices::delete');
    $routes->get('administrator/invoices/view/(:num)', 'Invoices::view/$1');
    $routes->get('administrator/invoices/export', 'Invoices::export');
    $routes->get('administrator/invoices/export_pdf', 'Invoices::export_pdf');
    $routes->get('administrator/invoices/single_pdf/(:num)', 'Invoices::single_pdf/$1');
    $routes->get('administrator/invoices/print_pdf/(:num)', 'Invoices::print_pdf/$1');
    $routes->get('administrator/invoices/my_invoices', 'Invoices::my_invoices');
    $routes->get('administrator/my_invoices', 'Invoices::my_invoices');
    $routes->match(['GET', 'POST'], 'administrator/invoices/upload_invoice_file_file', 'Invoices::upload_invoice_file_file');
    $routes->match(['GET', 'POST'], 'administrator/invoices/delete_invoice_file_file/(:segment)', 'Invoices::delete_invoice_file_file/$1');
    $routes->get('administrator/invoices/get_invoice_file_file/(:num)', 'Invoices::get_invoice_file_file/$1');

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

    // Wave 10: Audit Trail & Activity Logs (Full Parity with CI3 modules/audit_logs)
    $routes->get('audit-logs', 'AuditLogs::index');
    $routes->get('audit-logs/index', 'AuditLogs::index');
    $routes->match(['GET', 'POST'], 'audit-logs/get-datatable', 'AuditLogs::getDatatable');
    $routes->get('audit-logs/get-one/(:num)', 'AuditLogs::getOne/$1');
    $routes->get('audit-logs/view/(:num)', 'AuditLogs::view/$1');
    $routes->match(['GET', 'POST'], 'audit-logs/delete/(:num)', 'AuditLogs::delete/$1');
    $routes->match(['GET', 'POST'], 'audit-logs/delete', 'AuditLogs::delete');
    $routes->post('audit-logs/bulk-delete', 'AuditLogs::bulkDelete');
    $routes->get('audit-logs/export', 'AuditLogs::export');
    $routes->get('audit-logs/export-pdf', 'AuditLogs::export_pdf');
    $routes->get('audit-logs/export_pdf', 'AuditLogs::export_pdf');
    $routes->get('audit-logs/single-pdf/(:num)', 'AuditLogs::single_pdf/$1');
    $routes->get('audit-logs/single_pdf/(:num)', 'AuditLogs::single_pdf/$1');

    // Snake_case aliases (audit_logs)
    $routes->get('audit_logs', 'AuditLogs::index');
    $routes->get('audit_logs/index', 'AuditLogs::index');
    $routes->match(['GET', 'POST'], 'audit_logs/get-datatable', 'AuditLogs::getDatatable');
    $routes->get('audit_logs/get-one/(:num)', 'AuditLogs::getOne/$1');
    $routes->get('audit_logs/view/(:num)', 'AuditLogs::view/$1');
    $routes->match(['GET', 'POST'], 'audit_logs/delete/(:num)', 'AuditLogs::delete/$1');
    $routes->match(['GET', 'POST'], 'audit_logs/delete', 'AuditLogs::delete');
    $routes->post('audit_logs/bulk-delete', 'AuditLogs::bulkDelete');
    $routes->get('audit_logs/export', 'AuditLogs::export');
    $routes->get('audit_logs/export-pdf', 'AuditLogs::export_pdf');
    $routes->get('audit_logs/export_pdf', 'AuditLogs::export_pdf');
    $routes->get('audit_logs/single-pdf/(:num)', 'AuditLogs::single_pdf/$1');
    $routes->get('audit_logs/single_pdf/(:num)', 'AuditLogs::single_pdf/$1');

    // CI3 Legacy Aliases (administrator/audit_logs)
    $routes->get('administrator/audit_logs', 'AuditLogs::index');
    $routes->get('administrator/audit_logs/index', 'AuditLogs::index');
    $routes->get('administrator/audit_logs/index/(:num)', 'AuditLogs::index');
    $routes->get('administrator/audit_logs/view/(:num)', 'AuditLogs::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/audit_logs/delete/(:num)', 'AuditLogs::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/audit_logs/delete', 'AuditLogs::delete');
    $routes->get('administrator/audit_logs/export', 'AuditLogs::export');
    $routes->get('administrator/audit_logs/export_pdf', 'AuditLogs::export_pdf');
    $routes->get('administrator/audit_logs/single_pdf/(:num)', 'AuditLogs::single_pdf/$1');

    // Wave 11: Team & User Management
    $routes->get('users', 'Users::index');
    $routes->post('users/get-datatable', 'Users::getDatatable');
    $routes->get('users/get-one/(:num)', 'Users::getOne/$1');
    $routes->post('users/save', 'Users::save');
    $routes->post('users/toggle-status/(:num)', 'Users::toggleStatus/$1');

    // Wave 12: Financial Disbursements & Payments (Full Parity with CI3 modules/payments)
    $routes->get('payments', 'Payments::index');
    $routes->get('payments/index', 'Payments::index');
    $routes->match(['GET', 'POST'], 'payments/get-datatable', 'Payments::getDatatable');
    $routes->match(['GET', 'POST'], 'payments/getDatatable', 'Payments::getDatatable');
    $routes->get('payments/get-one/(:num)', 'Payments::getOne/$1');
    $routes->get('payments/getOne/(:num)', 'Payments::getOne/$1');
    $routes->get('payments/view/(:num)', 'Payments::view/$1');
    $routes->get('payments/add', 'Payments::add');
    $routes->post('payments/add_save', 'Payments::add_save');
    $routes->post('payments/add-save', 'Payments::add_save');
    $routes->get('payments/edit/(:num)', 'Payments::edit/$1');
    $routes->post('payments/edit_save/(:num)', 'Payments::edit_save/$1');
    $routes->post('payments/edit-save/(:num)', 'Payments::edit_save/$1');
    $routes->match(['GET', 'POST'], 'payments/delete/(:num)', 'Payments::delete/$1');
    $routes->match(['GET', 'POST'], 'payments/delete', 'Payments::delete');
    $routes->post('payments/bulk-delete', 'Payments::bulkDelete');
    $routes->post('payments/bulkDelete', 'Payments::bulkDelete');
    $routes->get('payments/get_invoice_balance/(:num)', 'Payments::get_invoice_balance/$1');
    $routes->get('payments/get-invoice-balance/(:num)', 'Payments::get_invoice_balance/$1');
    $routes->get('payments/export', 'Payments::export');
    $routes->get('payments/export-pdf', 'Payments::export_pdf');
    $routes->get('payments/export_pdf', 'Payments::export_pdf');
    $routes->get('payments/single-pdf/(:num)', 'Payments::single_pdf/$1');
    $routes->get('payments/single_pdf/(:num)', 'Payments::single_pdf/$1');
    $routes->get('payments/receipt-pdf/(:num)', 'Payments::receipt_pdf/$1');
    $routes->get('payments/receipt_pdf/(:num)', 'Payments::receipt_pdf/$1');
    $routes->match(['GET', 'POST'], 'payments/upload_payment_proof_file_file', 'Payments::upload_payment_proof_file_file');
    $routes->match(['GET', 'POST'], 'payments/delete_payment_proof_file_file/(:segment)', 'Payments::delete_payment_proof_file_file/$1');
    $routes->match(['GET', 'POST'], 'payments/delete_payment_proof_file_file', 'Payments::delete_payment_proof_file_file');
    $routes->get('payments/get_payment_proof_file_file/(:num)', 'Payments::get_payment_proof_file_file/$1');

    // Legacy CI3 Payments Aliases (administrator/payments/*)
    $routes->get('administrator/payments', 'Payments::index');
    $routes->get('administrator/payments/index', 'Payments::index');
    $routes->get('administrator/payments/index/(:num)', 'Payments::index');
    $routes->get('administrator/payments/add', 'Payments::add');
    $routes->post('administrator/payments/add_save', 'Payments::add_save');
    $routes->get('administrator/payments/edit/(:num)', 'Payments::edit/$1');
    $routes->post('administrator/payments/edit_save/(:num)', 'Payments::edit_save/$1');
    $routes->match(['GET', 'POST'], 'administrator/payments/delete/(:num)', 'Payments::delete/$1');
    $routes->match(['GET', 'POST'], 'administrator/payments/delete', 'Payments::delete');
    $routes->get('administrator/payments/view/(:num)', 'Payments::view/$1');
    $routes->get('administrator/payments/export', 'Payments::export');
    $routes->get('administrator/payments/export_pdf', 'Payments::export_pdf');
    $routes->get('administrator/payments/single_pdf/(:num)', 'Payments::single_pdf/$1');
    $routes->match(['GET', 'POST'], 'administrator/payments/upload_payment_proof_file_file', 'Payments::upload_payment_proof_file_file');
    $routes->match(['GET', 'POST'], 'administrator/payments/delete_payment_proof_file_file/(:segment)', 'Payments::delete_payment_proof_file_file/$1');
    $routes->match(['GET', 'POST'], 'administrator/payments/delete_payment_proof_file_file', 'Payments::delete_payment_proof_file_file');
    $routes->get('administrator/payments/get_payment_proof_file_file/(:num)', 'Payments::get_payment_proof_file_file/$1');

    $routes->get('budget', 'Invoices::index');
    $routes->get('approvals', 'Invoices::index');
});
