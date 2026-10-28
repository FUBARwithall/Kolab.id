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
        'slug',
        'brand_id',
        'budget_total',
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

        // 2. Daftar KOL yang di-assign beserta progress tugasnya
        $campaign['kols'] = $db->table('campaign_kols')
            ->select('campaign_kols.*, kols.name as kol_name, users.avatar, kols.whatsapp, kols.city')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('campaign_kols.campaign_id', $id)
            ->orderBy('campaign_kols.id', 'desc')
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
}
