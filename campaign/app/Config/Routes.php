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

    $routes->get('shipments', 'Shipments::index');
    $routes->post('shipments/get-datatable', 'Shipments::getDatatable');
    $routes->get('shipments/get-one/(:num)', 'Shipments::getOne/$1');
    $routes->post('shipments/save', 'Shipments::save');
    $routes->post('shipments/update-status/(:num)', 'Shipments::updateStatus/$1');

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

    // Wave 9: Monitoring Scraping & Apify Jobs (Full Parity with CI3 modules/apify_jobs)
    $routes->get('scraping-jobs', 'ScrapingJobs::index');
    $routes->post('scraping-jobs/get-datatable', 'ScrapingJobs::getDatatable');
    $routes->get('scraping-jobs/view/(:num)', 'ScrapingJobs::view/$1');
    $routes->get('scraping-jobs/get-one/(:num)', 'ScrapingJobs::getOne/$1');
    $routes->post('scraping-jobs/retry/(:num)', 'ScrapingJobs::retry/$1');
    $routes->post('scraping-jobs/trigger', 'ScrapingJobs::triggerNew');
    $routes->match(['GET', 'POST'], 'scraping-jobs/delete/(:num)', 'ScrapingJobs::delete/$1');
    $routes->post('scraping-jobs/bulk-delete', 'ScrapingJobs::bulkDelete');
    $routes->get('scraping-jobs/export', 'ScrapingJobs::export');

    // CI3 Parity Aliases (apify-jobs & apify_jobs)
    $routes->get('apify-jobs', 'ScrapingJobs::index');
    $routes->get('apify_jobs', 'ScrapingJobs::index');
    $routes->post('apify-jobs/get-datatable', 'ScrapingJobs::getDatatable');
    $routes->post('apify_jobs/get-datatable', 'ScrapingJobs::getDatatable');
    $routes->get('apify-jobs/view/(:num)', 'ScrapingJobs::view/$1');
    $routes->get('apify_jobs/view/(:num)', 'ScrapingJobs::view/$1');
    $routes->match(['GET', 'POST'], 'apify-jobs/delete/(:num)', 'ScrapingJobs::delete/$1');
    $routes->match(['GET', 'POST'], 'apify_jobs/delete/(:num)', 'ScrapingJobs::delete/$1');
    $routes->get('apify-jobs/export', 'ScrapingJobs::export');
    $routes->get('apify_jobs/export', 'ScrapingJobs::export');
    $routes->get('administrator/apify_jobs', 'ScrapingJobs::index');
    $routes->get('administrator/apify_jobs/view/(:num)', 'ScrapingJobs::view/$1');
    $routes->match(['GET', 'POST'], 'administrator/apify_jobs/delete/(:num)', 'ScrapingJobs::delete/$1');
    $routes->get('administrator/apify_jobs/export', 'ScrapingJobs::export');

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
