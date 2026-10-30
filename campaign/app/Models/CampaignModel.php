<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignModel extends Model
{
    protected $table            = 'campaigns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'name',
        'slug',
        'brand_id',
        'budget_total',
        'budget',
        'target_views',
        'start_date',
        'end_date',
        'status',
        'is_open_registration',
        'description',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title'        => 'required|min_length[3]|max_length[200]',
        'budget_total' => 'required|numeric',
        'start_date'   => 'required|valid_date',
        'end_date'     => 'required|valid_date',
        'status'       => 'required|in_list[DRAFT,ACTIVE,COMPLETED,CANCELLED]',
    ];

    /**
     * Otomatis sinkronkan status kampanye berdasarkan rentang tanggal berjalan
     */
    public function autoUpdateStatuses(): void
    {
        $today = date('Y-m-d');

        // DRAFT -> ACTIVE jika tanggal mulai sudah tiba
        $this->builder()
            ->where('status', 'DRAFT')
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->update(['status' => 'ACTIVE', 'updated_at' => date('Y-m-d H:i:s')]);

        // ACTIVE -> COMPLETED jika tanggal selesai telah lewat
        $this->builder()
            ->where('status', 'ACTIVE')
            ->where('end_date <', $today)
            ->update(['status' => 'COMPLETED', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Mengambil statistik ringkasan dashboard kampanye
     */
    public function getSummaryMetrics(): array
    {
        $this->autoUpdateStatuses();

        $totalCampaigns = $this->countAllResults(false);
        $activeCampaigns = (int)$this->where('status', 'ACTIVE')->countAllResults(false);
        $completedCampaigns = (int)$this->where('status', 'COMPLETED')->countAllResults(false);

        $db = \Config\Database::connect();
        
        // Total Budget Keseluruhan
        $budgetRow = $db->table($this->table)
            ->selectSum('budget_total', 'total_budget')
            ->where('deleted_at IS NULL')
            ->get()
            ->getRow();
        $totalBudget = (float)($budgetRow ? $budgetRow->total_budget : 0);

        // Total Dana yang Sudah Didealkan ke KOL
        $spentRow = $db->table('campaign_kols')
            ->selectSum('deal_amount', 'total_spent')
            ->get()
            ->getRow();
        $totalSpent = (float)($spentRow ? $spentRow->total_spent : 0);

        return [
            'total_campaigns'     => $totalCampaigns,
            'active_campaigns'    => $activeCampaigns,
            'completed_campaigns' => $completedCampaigns,
            'total_budget'        => $totalBudget,
            'total_spent'         => $totalSpent,
            'budget_absorption'   => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0,
        ];
    }

    /**
     * DataTables Server-Side dengan agregasi spent budget dan jumlah KOL
     */
    public function getDatatables(int $start, int $length, string $searchValue = '', string $orderColumn = 'id', string $orderDir = 'desc', ?string $filterStatus = null): array
    {
        $this->autoUpdateStatuses();

        $builder = $this->builder();
        $builder->select('campaigns.*, 
            COUNT(DISTINCT campaign_kols.id) as total_kols,
            COALESCE(SUM(campaign_kols.deal_amount), 0) as total_spent,
            COALESCE(SUM(campaign_kols.total_vt), 0) as total_target_vt')
            ->join('campaign_kols', 'campaign_kols.campaign_id = campaigns.id', 'left')
            ->where('campaigns.deleted_at IS NULL')
            ->groupBy('campaigns.id');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('campaigns.title', $searchValue)
                ->orLike('campaigns.description', $searchValue)
                ->groupEnd();
        }

        if (!empty($filterStatus)) {
            $builder->where('campaigns.status', strtoupper($filterStatus));
        }

        $allowedColumns = ['id', 'title', 'budget_total', 'start_date', 'end_date', 'status', 'created_at'];
        if (in_array($orderColumn, $allowedColumns, true)) {
            $builder->orderBy('campaigns.' . $orderColumn, $orderDir);
        } else {
            $builder->orderBy('campaigns.id', 'desc');
        }

        return $builder->get($length, $start)->getResultArray();
    }

    /**
     * Hitung total data setelah filter
     */
    public function countFiltered(string $searchValue = '', ?string $filterStatus = null): int
    {
        $builder = $this->builder();
        $builder->where('deleted_at IS NULL');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('title', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        if (!empty($filterStatus)) {
            $builder->where('status', strtoupper($filterStatus));
        }

        return $builder->countAllResults();
    }

    /**
     * Mengambil detail lengkap 1 kampanye beserta produk, KOL, dan metrik
     */
    public function getFullDetails(int $id): ?array
    {
        $campaign = $this->find($id);
        if (!$campaign) {
            return null;
        }

        $db = \Config\Database::connect();

        // 1. Produk terkait
        $campaign['products'] = $db->table('campaign_products')
            ->select('products.*')
            ->join('products', 'products.id = campaign_products.product_id', 'inner')
            ->where('campaign_products.campaign_id', $id)
            ->get()
            ->getResultArray();

        // 1b. Syarat Kriteria (Tiers, Kategori, Platform)
        $campaign['allowed_tiers'] = $db->table('campaign_allowed_tiers')
            ->select('tiers.*')
            ->join('tiers', 'tiers.id = campaign_allowed_tiers.tier_id', 'inner')
            ->where('campaign_allowed_tiers.campaign_id', $id)
            ->get()
            ->getResultArray();

        $campaign['allowed_categories'] = $db->table('campaign_allowed_categories')
            ->select('categories.*')
            ->join('categories', 'categories.id = campaign_allowed_categories.category_id', 'inner')
            ->where('campaign_allowed_categories.campaign_id', $id)
            ->get()
            ->getResultArray();

        $campaign['required_platforms'] = $db->table('campaign_required_platforms')
            ->select('platforms.*')
            ->join('platforms', 'platforms.id = campaign_required_platforms.platform_id', 'inner')
            ->where('campaign_required_platforms.campaign_id', $id)
            ->get()
            ->getResultArray();

        // 2. Daftar KOL yang di-assign beserta progress tugasnya
        $campaign['kols'] = $db->table('campaign_kols')
            ->select('campaign_kols.*, kols.name as kol_name, users.avatar, kols.whatsapp, kols.city')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('campaign_kols.campaign_id', $id)
            ->orderBy('campaign_kols.id', 'desc')
            ->get()
            ->getResultArray();

        // Cek rating & review per KOL jika ada
        foreach ($campaign['kols'] as &$ckKol) {
            $ckReview = $db->table('kol_reviews')
                ->where('kol_id', $ckKol['kol_id'])
                ->where('campaign_id', $id)
                ->get()
                ->getRowArray();
            $ckKol['review'] = $ckReview;
        }
        unset($ckKol);

        // 2b. Pelamar Terbuka (Campaign Applications)
        $campaign['applications'] = $db->table('campaign_applications ca')
            ->select('ca.*, kols.name as kol_name, users.avatar, kols.city, kols.whatsapp')
            ->join('kols', 'kols.id = ca.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('ca.campaign_id', $id)
            ->orderBy('ca.id', 'desc')
            ->get()
            ->getResultArray();

        // 3. Ringkasan Metrik Aktual dari Postingan Live
        $metricsRow = $db->table('campaign_kols')
            ->select('
                SUM(posting_metrics.views_count) as total_views,
                SUM(posting_metrics.likes_count) as total_likes,
                SUM(posting_metrics.comments_count) as total_comments,
                SUM(posting_metrics.shares_count) as total_shares,
                SUM(posting_metrics.saves_count) as total_saves,
                COUNT(postings.id) as live_postings_count
            ')
            ->join('tasks', 'tasks.campaign_kol_id = campaign_kols.id', 'inner')
            ->join('postings', 'postings.task_id = tasks.id', 'inner')
            ->join('posting_metrics', 'posting_metrics.posting_id = postings.id', 'inner')
            ->where('campaign_kols.campaign_id', $id)
            ->get()
            ->getRowArray();

        $campaign['metrics'] = $metricsRow ?: [
            'total_views'         => 0,
            'total_likes'         => 0,
            'total_comments'      => 0,
            'total_shares'        => 0,
            'total_saves'         => 0,
            'live_postings_count' => 0,
        ];

        // 4. Kalkulasi Total Spent & Sisa Budget
        $spent = 0;
        foreach ($campaign['kols'] as $k) {
            $spent += (float)$k['deal_amount'];
        }
        $campaign['total_spent'] = $spent;
        $campaign['remaining_budget'] = (float)$campaign['budget_total'] - $spent;

        return $campaign;
    }

    /**
     * Consolidated Financial & Performance Analytics for Campaign (CI3 Parity)
     */
    public function getAnalyticsData(int $id): ?array
    {
        $campaign = $this->find($id);
        if (!$campaign) {
            return null;
        }

        $db = \Config\Database::connect();

        // 1. Fetch assigned KOLs
        $kols = $db->table('campaign_kols ck')
            ->select('
                ck.*,
                k.name as kol_name,
                k.whatsapp as kol_whatsapp,
                k.city as kol_city,
                k.bank_name,
                k.bank_account,
                k.bank_holder,
                u.avatar as kol_avatar,
                u.email as kol_email
            ')
            ->join('kols k', 'k.id = ck.kol_id', 'left')
            ->join('users u', 'u.id = k.user_id', 'left')
            ->where('ck.campaign_id', $id)
            ->orderBy('ck.deal_amount', 'DESC')
            ->get()
            ->getResultArray();

        $totalBudget = (float)$campaign['budget_total'];
        $totalDeals = 0;
        $totalInvoiced = 0;
        $totalPaid = 0;
        $totalViews = 0;
        $totalLikes = 0;
        $totalComments = 0;
        $totalShares = 0;
        $totalSaves = 0;
        $totalEngagements = 0;

        $kolAnalytics = [];
        $chartLabels = [];
        $chartViews = [];
        $chartEngagements = [];
        $chartDeals = [];

        foreach ($kols as $ck) {
            $ckId = (int)$ck['id'];
            $dealAmount = (float)$ck['deal_amount'];
            $totalDeals += $dealAmount;

            // Invoices
            $invRow = $db->table('invoices')
                ->select('SUM(grand_total) as invoiced_amount, COUNT(id) as inv_count')
                ->where('campaign_kol_id', $ckId)
                ->where('status !=', 'REJECTED')
                ->get()
                ->getRowArray();
            $invoicedAmount = (float)($invRow['invoiced_amount'] ?? 0);
            $totalInvoiced += $invoicedAmount;

            // Payments
            $payRow = $db->table('payments p')
                ->select('SUM(p.amount_paid) as paid_amount, COUNT(p.id) as pay_count')
                ->join('invoices inv', 'inv.id = p.invoice_id', 'inner')
                ->where('inv.campaign_kol_id', $ckId)
                ->where('inv.status !=', 'REJECTED')
                ->get()
                ->getRowArray();
            $paidAmount = (float)($payRow['paid_amount'] ?? 0);
            $totalPaid += $paidAmount;

            // Payment Status
            if ($invoicedAmount <= 0) {
                $payStatus = 'NO_INVOICE';
            } elseif ($paidAmount >= $invoicedAmount) {
                $payStatus = 'PAID';
            } elseif ($paidAmount > 0) {
                $payStatus = 'PARTIAL';
            } else {
                $payStatus = 'UNPAID';
            }

            // Tasks
            $tasks = $db->table('tasks')
                ->select('id, status, title')
                ->where('campaign_kol_id', $ckId)
                ->get()
                ->getResultArray();
            $taskTotal = count($tasks);
            $taskCompleted = 0;
            foreach ($tasks as $t) {
                if (in_array(strtoupper($t['status']), ['POSTED', 'DONE', 'COMPLETED'])) {
                    $taskCompleted++;
                }
            }
            $taskProgress = $taskTotal > 0 ? round(($taskCompleted / $taskTotal) * 100, 1) : 0;

            // Metrics
            $metricRow = $db->table('postings pos')
                ->select('
                    SUM(pm.views_count) as views,
                    SUM(pm.likes_count) as likes,
                    SUM(pm.comments_count) as comments,
                    SUM(pm.shares_count) as shares,
                    SUM(pm.saves_count) as saves,
                    AVG(pm.actual_cpv) as avg_cpv
                ')
                ->join('tasks t', 't.id = pos.task_id', 'inner')
                ->join('posting_metrics pm', 'pm.posting_id = pos.id', 'inner')
                ->where('t.campaign_kol_id', $ckId)
                ->get()
                ->getRowArray();

            $kViews = (int)($metricRow['views'] ?? 0);
            $kLikes = (int)($metricRow['likes'] ?? 0);
            $kComments = (int)($metricRow['comments'] ?? 0);
            $kShares = (int)($metricRow['shares'] ?? 0);
            $kSaves = (int)($metricRow['saves'] ?? 0);
            $kEngagements = $kLikes + $kComments + $kShares + $kSaves;
            $kEr = $kViews > 0 ? round(($kEngagements / $kViews) * 100, 2) : 0.0;
            $kCpm = $kViews > 0 ? round(($dealAmount / $kViews) * 1000, 2) : 0.0;
            $kCpe = $kEngagements > 0 ? round($dealAmount / $kEngagements, 2) : 0.0;

            $totalViews += $kViews;
            $totalLikes += $kLikes;
            $totalComments += $kComments;
            $totalShares += $kShares;
            $totalSaves += $kSaves;
            $totalEngagements += $kEngagements;

            $kolAnalytics[] = [
                'campaign_kol_id' => $ckId,
                'kol_id'          => $ck['kol_id'],
                'kol_name'        => $ck['kol_name'],
                'kol_avatar'      => $ck['kol_avatar'],
                'kol_whatsapp'    => $ck['kol_whatsapp'],
                'kol_city'        => $ck['kol_city'],
                'deal_amount'     => $dealAmount,
                'invoiced_amount' => $invoicedAmount,
                'paid_amount'     => $paidAmount,
                'payment_status'  => $payStatus,
                'task_total'      => $taskTotal,
                'task_completed'  => $taskCompleted,
                'task_progress'   => $taskProgress,
                'views'           => $kViews,
                'likes'           => $kLikes,
                'comments'        => $kComments,
                'shares'          => $kShares,
                'saves'           => $kSaves,
                'engagements'     => $kEngagements,
                'er'              => $kEr,
                'cpm'             => $kCpm,
                'cpe'             => $kCpe,
            ];

            // Chart data
            $chartLabels[] = $ck['kol_name'];
            $chartViews[] = $kViews;
            $chartEngagements[] = $kEngagements;
            $chartDeals[] = $dealAmount;
        }

        // Global KPIs
        $overallEr = $totalViews > 0 ? round(($totalEngagements / $totalViews) * 100, 2) : 0.0;
        $overallCpm = $totalViews > 0 ? round(($totalDeals / $totalViews) * 1000, 2) : 0.0;
        $overallCpe = $totalEngagements > 0 ? round($totalDeals / $totalEngagements, 2) : 0.0;
        $targetViews = (int)($campaign['target_views'] ?? 0);
        $viewsAchievementPct = $targetViews > 0 ? min(100, round(($totalViews / $targetViews) * 100, 1)) : 0.0;
        $budgetAbsorptionPct = $totalBudget > 0 ? min(100, round(($totalDeals / $totalBudget) * 100, 1)) : 0.0;
        $unpaidBalance = max(0, $totalInvoiced - $totalPaid);

        return [
            'campaign' => $campaign,
            'financial' => [
                'total_budget'          => $totalBudget,
                'total_deals'           => $totalDeals,
                'remaining_budget'      => max(0, $totalBudget - $totalDeals),
                'budget_absorption_pct' => $budgetAbsorptionPct,
                'total_invoiced'        => $totalInvoiced,
                'total_paid'            => $totalPaid,
                'unpaid_balance'        => $unpaidBalance,
            ],
            'performance' => [
                'target_views'          => $targetViews,
                'total_views'           => $totalViews,
                'views_achievement_pct' => $viewsAchievementPct,
                'total_likes'           => $totalLikes,
                'total_comments'        => $totalComments,
                'total_shares'          => $totalShares,
                'total_saves'           => $totalSaves,
                'total_engagements'     => $totalEngagements,
                'avg_er'                => $overallEr,
                'cpm'                   => $overallCpm,
                'cpe'                   => $overallCpe,
            ],
            'kol_analytics' => $kolAnalytics,
            'chart_data'    => [
                'labels'      => $chartLabels,
                'views'       => $chartViews,
                'engagements' => $chartEngagements,
                'deals'       => $chartDeals,
            ],
        ];
    }

    /**
     * Get all campaigns formatted for CSV Export
     */
    public function getAllForExport(): array
    {
        $db = \Config\Database::connect();
        return $db->table('campaigns c')
            ->select('
                c.id,
                c.title,
                c.status,
                c.budget_total,
                c.target_views,
                c.start_date,
                c.end_date,
                c.is_open_registration,
                COALESCE(SUM(ck.deal_amount), 0) as total_spent,
                COUNT(DISTINCT ck.id) as total_kols,
                c.created_at
            ')
            ->join('campaign_kols ck', 'ck.campaign_id = c.id', 'left')
            ->where('c.deleted_at IS NULL')
            ->groupBy('c.id')
            ->orderBy('c.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
