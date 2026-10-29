<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Dashboard Overview (Role-Aware)
     */
    public function index()
    {
        $session = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin = in_array('admin', $userRoles, true);
        $isKol   = in_array('kol', $userRoles, true);
        $userId  = (int) ($session->get('user_id') ?? 0);

        $totalKols       = $this->db->table('kols')->countAllResults();
        $approvedKols    = $this->db->table('kols')->where('approval_status', 'APPROVED')->countAllResults();
        $totalPlatforms  = $this->db->table('kol_platforms')->countAllResults();
        $totalTiers      = $this->db->table('tiers')->countAllResults();
        $totalCategories = $this->db->table('categories')->countAllResults();
        $totalRates      = $this->db->table('kol_rate_cards')->countAllResults();

        $recentKols = $this->db->table('kols')
            ->select('id, name, city, whatsapp, approval_status, created_at')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // If user is KOL, fetch their linked KOL profile
        $myKol = null;
        if ($isKol) {
            $myKol = $this->db->table('kols')
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
        }

        $data = [
            'title'           => $isKol ? 'Dashboard Kreator & Intelijen Pasar' : 'Overview KOL Data Hub',
            'controller'      => 'Datahub',
            'isAdmin'         => $isAdmin,
            'isKol'           => $isKol,
            'totalKols'       => $totalKols,
            'approvedKols'    => $approvedKols,
            'totalPlatforms'  => $totalPlatforms,
            'totalTiers'      => $totalTiers,
            'totalCategories' => $totalCategories,
            'totalRates'      => $totalRates,
            'recentKols'      => $recentKols,
            'myKol'           => $myKol,
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Master Data KOL / Direktori (Shared: Superadmin has CRUD, KOL has Benchmark view)
     */
    public function kols()
    {
        $session = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin = in_array('admin', $userRoles, true);
        $isKol   = in_array('kol', $userRoles, true);

        $kols = $this->db->table('kols')
            ->select('
                kols.*,
                (SELECT COUNT(*) FROM kol_platforms WHERE kol_id = kols.id) as platform_count,
                (SELECT ROUND(AVG(rating), 1) FROM kol_reviews WHERE kol_id = kols.id) as avg_rating,
                (SELECT COUNT(*) FROM kol_reviews WHERE kol_id = kols.id) as review_count
            ')
            ->orderBy('kols.id', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => $isKol ? 'Direktori KOL (Market Benchmark)' : 'Master Database KOL',
            'controller' => 'Datahub',
            'isAdmin'    => $isAdmin,
            'isKol'      => $isKol,
            'kols'       => $kols,
        ];

        return view('dashboard/kols', $data);
    }

    /**
     * Akun Media Sosial & Metrik Platform
     */
    public function platforms()
    {
        $session = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin = in_array('admin', $userRoles, true);

        $platforms = $this->db->table('kol_platforms kp')
            ->select('kp.*, k.name as kol_name')
            ->join('kols k', 'k.id = kp.kol_id', 'left')
            ->orderBy('kp.followers_count', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Akun Media Sosial & Metrik Terhubung',
            'controller' => 'Datahub',
            'isAdmin'    => $isAdmin,
            'platforms'  => $platforms,
        ];

        return view('dashboard/platforms', $data);
    }

    /**
     * Rate Cards (Market Benchmark)
     */
    public function rateCards()
    {
        $session = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin = in_array('admin', $userRoles, true);
        $isKol   = in_array('kol', $userRoles, true);

        $rateCards = $this->db->table('kol_rate_cards rc')
            ->select('rc.*, k.name as kol_name')
            ->join('kols k', 'k.id = rc.kol_id', 'left')
            ->orderBy('rc.id', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => $isKol ? 'Benchmark Rate Card Industri' : 'Manajemen Rate Cards KOL',
            'controller' => 'Datahub',
            'isAdmin'    => $isAdmin,
            'isKol'      => $isKol,
            'rateCards'  => $rateCards,
        ];

        return view('dashboard/rate_cards', $data);
    }

    /**
     * Kategori & Niches
     */
    public function categories()
    {
        $categories = $this->db->table('categories')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Kategori & Niche Industri',
            'controller' => 'Datahub',
            'categories' => $categories,
        ];

        return view('dashboard/categories', $data);
    }

    /**
     * Tiers Influencer
     */
    public function tiers()
    {
        $tiers = $this->db->table('tiers')
            ->orderBy('min_followers', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Klasifikasi Tier Influencer',
            'controller' => 'Datahub',
            'tiers'      => $tiers,
        ];

        return view('dashboard/tiers', $data);
    }

    /**
     * Profil & Performance Card Saya (Khusus Akun KOL / Creator)
     */
    public function myProfile()
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 0);

        // Find linked KOL row
        $kol = $this->db->table('kols')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$kol) {
            // Fallback to first KOL for demo
            $kol = $this->db->table('kols')->orderBy('id', 'ASC')->get()->getRowArray();
        }

        $platforms = [];
        $rates = [];
        if ($kol) {
            $platforms = $this->db->table('kol_platforms')->where('kol_id', $kol['id'])->get()->getResultArray();
            $rates = $this->db->table('kol_rate_cards')->where('kol_id', $kol['id'])->get()->getResultArray();
        }

        $data = [
            'title'      => 'Profil & Performance Card Saya',
            'controller' => 'Datahub',
            'kol'        => $kol,
            'platforms'  => $platforms,
            'rates'      => $rates,
        ];

        return view('dashboard/my_profile', $data);
    }

    /**
     * Apify Scraper Engine (SUPERADMIN ONLY)
     */
    public function scraper()
    {
        $jobs = $this->db->table('apify_jobs')
            ->orderBy('id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        foreach ($jobs as &$j) {
            $url = $j['target_url'] ?? '';
            $platform = 'other';
            if (stripos($url, 'tiktok') !== false) $platform = 'tiktok';
            elseif (stripos($url, 'instagram') !== false) $platform = 'instagram';
            elseif (stripos($url, 'youtube') !== false || stripos($url, 'youtu.be') !== false) $platform = 'youtube';
            
            $j['platform'] = $platform;
            $j['target_username'] = basename(parse_url($url, PHP_URL_PATH) ?? $url);
        }

        $data = [
            'title'      => 'Apify Scraper Engine (Superadmin Only)',
            'controller' => 'Datahub',
            'jobs'       => $jobs,
        ];

        return view('dashboard/scraper', $data);
    }

    /**
     * Eksekusi Batch Apify Scraper Baru
     */
    public function runScraper()
    {
        $platform = $this->request->getPost('platform') ?? 'all';
        $batchLimit = (int) ($this->request->getPost('batch_limit') ?? 10);

        $builder = $this->db->table('kol_platforms kp')
            ->select('kp.id, kp.kol_id, kp.platform_id, kp.account_handle, p.name as platform_name')
            ->join('platforms p', 'p.id = kp.platform_id', 'inner');

        if ($platform === 'tiktok') {
            $builder->like('p.name', 'tiktok');
        } elseif ($platform === 'instagram') {
            $builder->like('p.name', 'instagram');
        }

        $targets = $builder->limit($batchLimit)->get()->getResultArray();
        $queuedCount = 0;

        foreach ($targets as $t) {
            $targetUrl = 'https://www.' . strtolower($t['platform_name']) . '.com/' . ltrim($t['account_handle'], '@');
            $this->db->table('apify_jobs')->insert([
                'actor_type'    => 'profile',
                'target_url'    => $targetUrl,
                'status'        => 'QUEUED',
                'response_data' => null,
                'started_at'    => null,
                'finished_at'   => null,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $queuedCount++;
        }

        return redirect()->to(base_url('scraper'))->with('success', "Berhasil menjadwalkan {$queuedCount} akun ke dalam antrean Apify Scraper!");
    }

    /**
     * Konfigurasi Sistem & API Keys
     */
    public function settings()
    {
        $data = [
            'title'      => 'Konfigurasi Sistem & API Keys',
            'controller' => 'Datahub',
        ];

        return view('dashboard/settings', $data);
    }

    /**
     * Helper: Mendapatkan KOL ID user yang login
     */
    private function getMyKolId(): ?int
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $kol = $this->db->table('kols')->where('user_id', $userId)->get()->getRowArray();
        if ($kol) {
            return (int) $kol['id'];
        }
        // Fallback ke profil pertama untuk demo jika belum di-link
        $first = $this->db->table('kols')->orderBy('id', 'ASC')->get()->getRowArray();
        return $first ? (int) $first['id'] : null;
    }

    /**
     * Kampanye Terbuka (KOL Creator Space)
     */
    public function openCampaigns()
    {
        $campaigns = $this->db->table('campaigns')
            ->select('campaigns.*, (SELECT COUNT(*) FROM campaign_applications WHERE campaign_id = campaigns.id) as total_applicants')
            ->where('status', 'ACTIVE')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Kampanye Terbuka & Peluang Kolaborasi',
            'controller' => 'Datahub',
            'campaigns'  => $campaigns,
        ];

        return view('talent/open_campaigns', $data);
    }

    /**
     * Lamaran Saya (KOL Creator Space)
     */
    public function myApplications()
    {
        $kolId = $this->getMyKolId();
        $applications = [];

        if ($kolId) {
            $applications = $this->db->table('campaign_applications ca')
                ->select('ca.*, c.title as campaign_title, c.start_date, c.end_date, c.budget_total')
                ->join('campaigns c', 'c.id = ca.campaign_id', 'left')
                ->where('ca.kol_id', $kolId)
                ->orderBy('ca.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'        => 'Status Lamaran Kampanye Saya',
            'controller'   => 'Datahub',
            'applications' => $applications,
        ];

        return view('talent/my_applications', $data);
    }

    /**
     * Tugas Saya (KOL Creator Space)
     */
    public function myTasks()
    {
        $kolId = $this->getMyKolId();
        $tasks = [];

        if ($kolId) {
            $tasks = $this->db->table('tasks t')
                ->select('t.*, c.title as campaign_title')
                ->join('campaign_kols ck', 'ck.id = t.campaign_kol_id', 'left')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->where('ck.kol_id', $kolId)
                ->orderBy('t.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'      => 'Daftar Tugas & Deadline Konten Saya',
            'controller' => 'Datahub',
            'tasks'      => $tasks,
        ];

        return view('talent/my_tasks', $data);
    }

    /**
     * Kontrak Saya (KOL Creator Space)
     */
    public function myContracts()
    {
        $kolId = $this->getMyKolId();
        $contracts = [];

        if ($kolId) {
            $contracts = $this->db->table('campaign_kol_contracts ckc')
                ->select('ckc.*, c.title as campaign_title')
                ->join('campaign_kols ck', 'ck.id = ckc.campaign_kol_id', 'left')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->where('ck.kol_id', $kolId)
                ->orderBy('ckc.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'      => 'Kontrak Kerja Sama Saya',
            'controller' => 'Datahub',
            'contracts'  => $contracts,
        ];

        return view('talent/my_contracts', $data);
    }

    /**
     * Rate Card Saya (KOL Creator Space)
     */
    public function myRates()
    {
        $kolId = $this->getMyKolId();
        $rates = [];

        if ($kolId) {
            $rates = $this->db->table('kol_rate_cards rc')
                ->select('rc.*, p.name as platform_name')
                ->join('platforms p', 'p.id = rc.platform_id', 'left')
                ->where('rc.kol_id', $kolId)
                ->orderBy('rc.id', 'ASC')
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'      => 'Rate Card & Portofolio Tarif Saya',
            'controller' => 'Datahub',
            'rates'      => $rates,
        ];

        return view('talent/my_rates', $data);
    }

    /**
     * Tagihan / Invoices Saya (KOL Creator Space)
     */
    public function myInvoices()
    {
        $kolId = $this->getMyKolId();
        $invoices = [];

        if ($kolId) {
            $invoices = $this->db->table('invoices inv')
                ->select('inv.*, c.title as campaign_title')
                ->join('campaign_kols ck', 'ck.id = inv.campaign_kol_id', 'left')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->where('ck.kol_id', $kolId)
                ->orderBy('inv.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'      => 'Tagihan & Riwayat Pembayaran Saya',
            'controller' => 'Datahub',
            'invoices'   => $invoices,
        ];

        return view('talent/my_invoices', $data);
    }
}
