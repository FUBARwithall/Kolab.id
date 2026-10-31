<?php

namespace App\Models;

use CodeIgniter\Model;

class PostingMetricModel extends Model
{
    protected $table            = 'posting_metrics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'posting_id',
        'snapshot_at',
        'views',
        'likes',
        'comments',
        'shares',
        'saves',
        'followers',
        'total_likes',
        'impressions',
        'reach',
        'engagement_rate',
        'view_rate',
        'cpv',
        'cpe',
        'cpa',
        'average_views',
        'median_views',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'saves_count',
        'actual_cpv',
        'last_scraped_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get summary KPI metrics
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                COUNT(posting_metrics.id) as total_snapshots,
                COALESCE(SUM(COALESCE(posting_metrics.views, posting_metrics.views_count, 0)), 0) as total_views,
                COALESCE(SUM(COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0)), 0) as total_likes,
                COALESCE(SUM(COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0)), 0) as total_comments,
                COALESCE(SUM(COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0)), 0) as total_shares,
                COALESCE(AVG(posting_metrics.engagement_rate), 0) as avg_er,
                COALESCE(AVG(COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0)), 0) as avg_cpv
            ')
            ->join('postings', 'postings.id = posting_metrics.posting_id', 'left')
            ->join('tasks', 'tasks.id = postings.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['posting_id'])) {
            $builder->where('posting_metrics.posting_id', (int)$filters['posting_id']);
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_snapshots' => (int)($row['total_snapshots'] ?? 0),
            'total_views'     => (int)($row['total_views'] ?? 0),
            'total_likes'     => (int)($row['total_likes'] ?? 0),
            'total_comments'  => (int)($row['total_comments'] ?? 0),
            'total_shares'    => (int)($row['total_shares'] ?? 0),
            'avg_er'          => round((float)($row['avg_er'] ?? 0), 2),
            'avg_cpv'         => round((float)($row['avg_cpv'] ?? 0), 2),
        ];
    }

    /**
     * DataTables query with joins to posting, task, campaign, kol, platform
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'posting_metrics.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                posting_metrics.*,
                COALESCE(posting_metrics.views, posting_metrics.views_count, 0) as resolved_views,
                COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0) as resolved_likes,
                COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0) as resolved_comments,
                COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0) as resolved_shares,
                COALESCE(posting_metrics.saves, posting_metrics.saves_count, 0) as resolved_saves,
                COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0) as resolved_cpv,
                postings.id as posting_real_id,
                postings.live_url as postings_live_url,
                postings.platform_id,
                COALESCE(postings.posted_at, postings.actual_posted_at) as posted_date,
                tasks.id as task_id,
                tasks.task_number,
                tasks.title as task_title,
                tasks.budget as task_budget,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                platforms.name as platform_name
            ')
            ->join('postings', 'postings.id = posting_metrics.posting_id', 'inner')
            ->join('tasks', 'tasks.id = postings.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left');

        if (!empty($filters['posting_id'])) {
            $builder->where('posting_metrics.posting_id', (int)$filters['posting_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kols.id', (int)$filters['kol_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('postings.live_url', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('tasks.title', $search)
                ->orLike('platforms.name', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get single metric with all joined details
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                posting_metrics.*,
                COALESCE(posting_metrics.views, posting_metrics.views_count, 0) as resolved_views,
                COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0) as resolved_likes,
                COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0) as resolved_comments,
                COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0) as resolved_shares,
                COALESCE(posting_metrics.saves, posting_metrics.saves_count, 0) as resolved_saves,
                COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0) as resolved_cpv,
                postings.id as posting_real_id,
                postings.live_url as postings_live_url,
                COALESCE(postings.posted_at, postings.actual_posted_at) as posted_date,
                tasks.id as task_id,
                tasks.task_number,
                tasks.title as task_title,
                tasks.budget as task_budget,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                platforms.name as platform_name
            ')
            ->join('postings', 'postings.id = posting_metrics.posting_id', 'inner')
            ->join('tasks', 'tasks.id = postings.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left')
            ->where('posting_metrics.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all historical snapshots for a posting (ordered chronologically)
     */
    public function getSnapshots(int $postingId): array
    {
        return $this->where('posting_id', $postingId)
            ->orderBy('snapshot_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Get all posting metrics for CSV / Excel export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                posting_metrics.*,
                COALESCE(posting_metrics.views, posting_metrics.views_count, 0) as resolved_views,
                COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0) as resolved_likes,
                COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0) as resolved_comments,
                COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0) as resolved_shares,
                COALESCE(posting_metrics.saves, posting_metrics.saves_count, 0) as resolved_saves,
                COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0) as resolved_cpv,
                postings.live_url as postings_live_url,
                tasks.task_number,
                tasks.title as task_title,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                platforms.name as platform_name
            ')
            ->join('postings', 'postings.id = posting_metrics.posting_id', 'inner')
            ->join('tasks', 'tasks.id = postings.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['posting_id'])) {
            $builder->where('posting_metrics.posting_id', (int)$filters['posting_id']);
        }

        return $builder->orderBy('posting_metrics.id', 'DESC')->get()->getResultArray();
    }
}
