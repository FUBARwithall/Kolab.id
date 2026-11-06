<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardMetricsModel;

class Dashboard extends BaseController
{
    protected $db;
    protected DashboardMetricsModel $metricsModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->metricsModel = new DashboardMetricsModel();
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

        // Basic counts
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
        if ($isKol && $userId > 0) {
            $myKol = $this->db->table('kols')
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
        }

        // Comprehensive Metrics (Aligned with card-generator plugin)
        $totalReach           = $this->metricsModel->getTotalReach();
        $campaignStats        = $this->metricsModel->getCampaignStats();
        $contentStats         = $this->metricsModel->getContentStats();
        $actionItems          = $this->metricsModel->getActionItems();
        $kolsByTier           = $this->metricsModel->getKolsByTier();
        $platformDistribution = $this->metricsModel->getPlatformDistribution();
        $staleKols            = $this->metricsModel->getStaleKols(20);
        $topPostings          = $this->metricsModel->getTopPerformers(5);

        $data = [
            'title'                 => $isKol ? 'Dashboard Kreator & Intelijen Pasar' : 'KOL Data Hub Dashboard',
            'controller'            => 'Datahub',
            'isAdmin'               => $isAdmin,
            'isKol'                 => $isKol,
            'totalKols'             => $totalKols,
            'approvedKols'          => $approvedKols,
            'totalPlatforms'        => $totalPlatforms,
            'totalTiers'            => $totalTiers,
            'totalCategories'       => $totalCategories,
            'totalRates'            => $totalRates,
            'recentKols'            => $recentKols,
            'myKol'                 => $myKol,
            // Plugin metrics data
            'total_kols'            => $totalKols,
            'total_reach'           => $totalReach,
            'campaign_stats'        => $campaignStats,
            'content_stats'         => $contentStats,
            'action_items'          => $actionItems,
            'kols_by_tier'          => $kolsByTier,
            'platform_distribution' => $platformDistribution,
            'stale_kols'            => $staleKols,
            'top_postings'          => $topPostings,
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

        // Filter parameters
        $q          = trim($this->request->getGet('q') ?? '');
        $categoryId = $this->request->getGet('category');
        $status     = $this->request->getGet('status');

        $builder = $this->db->table('kols')
            ->select('
                kols.*,
                (SELECT COUNT(*) FROM kol_platforms WHERE kol_id = kols.id) as platform_count,
                (SELECT ROUND(AVG(rating), 1) FROM kol_reviews WHERE kol_id = kols.id) as avg_rating,
                (SELECT COUNT(*) FROM kol_reviews WHERE kol_id = kols.id) as review_count,
                (SELECT MAX(followers_count) FROM kol_platforms WHERE kol_id = kols.id) as top_followers,
                (SELECT SUM(avg_views) FROM kol_platforms WHERE kol_id = kols.id) as total_views
            ');

        if (!empty($q)) {
            $builder->groupStart()
                ->like('kols.name', $q)
                ->orLike('kols.city', $q)
                ->orLike('kols.whatsapp', $q)
                ->groupEnd();
        }

        if (!empty($status)) {
            $builder->where('kols.approval_status', $status);
        }

        $kols = $builder->orderBy('kols.id', 'ASC')->get()->getResultArray();

        // Platforms per KOL mapping
        $platformsByKol = [];
        $kpRows = $this->db->table('kol_platforms kp')
            ->select('kp.*, p.name as platform_name, p.icon as platform_icon')
            ->join('platforms p', 'p.id = kp.platform_id', 'left')
            ->get()
            ->getResultArray();

        foreach ($kpRows as $r) {
            $platformsByKol[$r['kol_id']][] = $r;
        }

        // Auxiliary Master Data for Filters
        $allCategories = $this->db->table('categories')->orderBy('name', 'ASC')->get()->getResultArray();
        $allTiers      = $this->db->table('tiers')->orderBy('min_followers', 'ASC')->get()->getResultArray();
        $allPlatforms  = $this->db->table('platforms')->orderBy('name', 'ASC')->get()->getResultArray();

        $data = [
            'title'           => $isKol ? 'Direktori KOL (Market Benchmark)' : 'Master Database KOL',
            'controller'      => 'Datahub',
            'isAdmin'         => $isAdmin,
            'isKol'           => $isKol,
            'kols'            => $kols,
            'platformsByKol'  => $platformsByKol,
            'allCategories'   => $allCategories,
            'allTiers'        => $allTiers,
            'allPlatforms'    => $allPlatforms,
            'activeFilters'   => [
                'q'        => $q,
                'category' => $categoryId,
                'status'   => $status,
            ],
        ];

        return view('dashboard/kols', $data);
    }

    /**
     * Profile Snapshots (History Scraping)
     * Mengadopsi fitur snapshot log dari sistem plugin
     */
    public function snapshots()
    {
        $session = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin = in_array('admin', $userRoles, true);

        $q        = trim($this->request->getGet('q') ?? '');
        $platform = trim($this->request->getGet('platform') ?? '');
        $dateFrom = trim($this->request->getGet('date_from') ?? '');
        $dateTo   = trim($this->request->getGet('date_to') ?? '');

        $builder = $this->db->table('kol_profile_snapshots s')
            ->select('
                s.id,
                s.kol_id,
                s.platform_id,
                s.followers,
                s.followers as followers_count,
                s.avg_views,
                s.avg_views as average_views_last_30,
                s.avg_er,
                s.avg_er as average_er_last_30,
                s.snapshot_data,
                s.captured_at,
                s.captured_at as snapshot_date,
                k.name as kol_name,
                p.name as platform_name,
                p.icon as platform_icon,
                LOWER(p.name) as platform_code,
                kp.username,
                kp.profile_url
            ')
            ->join('kols k', 'k.id = s.kol_id', 'left')
            ->join('platforms p', 'p.id = s.platform_id', 'left')
            ->join('kol_platforms kp', 'kp.kol_id = s.kol_id AND kp.platform_id = s.platform_id', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('k.name', $q)
                ->orLike('kp.username', $q)
            ->groupEnd();
        }

        if ($platform !== '') {
            if (is_numeric($platform)) {
                $builder->where('s.platform_id', (int)$platform);
            } else {
                $builder->like('p.name', $platform);
            }
        }

        if ($dateFrom !== '') {
            $builder->where('s.captured_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '') {
            $builder->where('s.captured_at <=', $dateTo . ' 23:59:59');
        }

        $snapshots = $builder->orderBy('s.captured_at', 'DESC')->limit(100)->get()->getResultArray();
        $totalCount = $this->db->table('kol_profile_snapshots')->countAllResults();

        $allPlatforms = $this->db->table('platforms')->where('is_active', 1)->get()->getResultArray();

        $data = [
            'title'        => 'Profile Snapshots (History Scraping)',
            'controller'   => 'Datahub',
            'isAdmin'      => $isAdmin,
            'snapshots'    => $snapshots,
            'totalCount'   => $totalCount,
            'allPlatforms' => $allPlatforms,
            'filters'      => [
                'q'         => $q,
                'platform'  => $platform,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ];

        return view('dashboard/snapshots', $data);
    }

    /**
     * Hapus snapshot histori
     */
    public function deleteSnapshot($id)
    {
        $id = (int)$id;
        $this->db->table('kol_profile_snapshots')->where('id', $id)->delete();
        return redirect()->to(base_url('snapshots'))->with('success', 'Data snapshot histori berhasil dihapus.');
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
            ->select('kp.*, k.name as kol_name, p.name as platform_name, p.icon as platform_icon')
            ->join('kols k', 'k.id = kp.kol_id', 'left')
            ->join('platforms p', 'p.id = kp.platform_id', 'left')
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
            ->select('rc.*, k.name as kol_name, p.name as platform_name')
            ->join('kols k', 'k.id = rc.kol_id', 'left')
            ->join('platforms p', 'p.id = rc.platform_id', 'left')
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
            ->select('categories.*, (SELECT COUNT(*) FROM kol_categories WHERE category_id = categories.id) as kol_count')
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
        $tiers = $this->db->query("
            SELECT 
                t.*,
                COUNT(DISTINCT kp.kol_id) as kol_count
            FROM tiers t
            LEFT JOIN (
                SELECT kol_id, MAX(followers_count) as max_followers
                FROM kol_platforms
                GROUP BY kol_id
            ) kp ON kp.max_followers >= t.min_followers 
                AND (t.max_followers IS NULL OR kp.max_followers <= t.max_followers)
            GROUP BY t.id, t.name, t.min_followers, t.max_followers, t.description, t.created_at, t.updated_at
            ORDER BY t.min_followers ASC
        ")->getResultArray();

        $data = [
            'title'      => 'Klasifikasi Tier Influencer',
            'controller' => 'Datahub',
            'tiers'      => $tiers,
        ];

        return view('dashboard/tiers', $data);
    }

    /**
     * Settings / Konfigurasi Sistem
     */
    public function settings()
    {
        $data = [
            'title'      => 'Konfigurasi Sistem & API Keys',
            'controller' => 'Datahub',
        ];

        return view('dashboard/settings', $data);
    }

    // ====================================================================
    // DELEGATION FORWARDERS TO TALENT CONTROLLER (BACKWARDS-COMPATIBLE)
    // ====================================================================

    public function myProfile()
    {
        return (new Talent())->myProfile();
    }

    public function openCampaigns()
    {
        return (new Talent())->openCampaigns();
    }

    public function applyCampaign()
    {
        return (new Talent())->applyCampaign();
    }

    public function myApplications()
    {
        return (new Talent())->myApplications();
    }

    public function myTasks()
    {
        return (new Talent())->myTasks();
    }

    public function myContracts()
    {
        return (new Talent())->myContracts();
    }

    public function myRates()
    {
        return (new Talent())->myRates();
    }

    public function myInvoices()
    {
        return (new Talent())->myInvoices();
    }
}
