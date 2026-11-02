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
        if ($isKol && $userId > 0) {
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
