<?php

namespace App\Models;

use CodeIgniter\Model;

class PostingModel extends Model
{
    protected $table            = 'postings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'platform_id',
        'live_url',
        'post_identifier',
        'caption',
        'thumbnail_url',
        'duration_seconds',
        'actual_posted_at',
        'posted_at_source',
        'platform_validation_status',
        'date_validation_status',
        'validation_note',
        'url_locked_at',
        'status',
        'scrape_day3_done',
        'scrape_day7_done',
        'posted_at',
        'submitted_by',
        'latest_metric_id',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get summary metrics for Postings dashboard
     */
    public function getSummaryMetrics(): array
    {
        $db = $this->db;
        $row = $db->table($this->table)
            ->select('
                COUNT(postings.id) as total_postings,
                COALESCE(SUM(COALESCE(posting_metrics.views, posting_metrics.views_count)), 0) as total_views,
                COALESCE(SUM(COALESCE(posting_metrics.likes, posting_metrics.likes_count)), 0) as total_likes,
                COALESCE(SUM(COALESCE(posting_metrics.comments, posting_metrics.comments_count)), 0) as total_comments,
                COALESCE(SUM(COALESCE(posting_metrics.shares, posting_metrics.shares_count)), 0) as total_shares,
                COALESCE(SUM(COALESCE(posting_metrics.saves, posting_metrics.saves_count)), 0) as total_saves
            ')
            ->join('posting_metrics', 'posting_metrics.id = postings.latest_metric_id', 'left')
            ->get()
            ->getRowArray();

        $totalViews = (int)($row['total_views'] ?? 0);
        $totalInteractions = (int)($row['total_likes'] ?? 0) + (int)($row['total_comments'] ?? 0) + (int)($row['total_shares'] ?? 0) + (int)($row['total_saves'] ?? 0);
        $avgEr = ($totalViews > 0) ? round(($totalInteractions / $totalViews) * 100, 2) : 0.0;

        return [
            'total_postings' => (int)($row['total_postings'] ?? 0),
            'total_views'    => $totalViews,
            'total_likes'    => (int)($row['total_likes'] ?? 0),
            'total_comments' => (int)($row['total_comments'] ?? 0),
            'total_shares'   => (int)($row['total_shares'] ?? 0),
            'total_saves'    => (int)($row['total_saves'] ?? 0),
            'avg_er'         => $avgEr,
        ];
    }

    /**
     * DataTables query with full metrics and platform info
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'postings.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                postings.*,
                tasks.task_number,
                tasks.title as task_title,
                COALESCE(tasks.budget, tasks.rate_amount) as rate_amount,
                campaigns.id as campaign_id,
                COALESCE(campaigns.name, campaigns.title) as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                users.avatar as kol_avatar,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                COALESCE(posting_metrics.views, posting_metrics.views_count, 0) as views_count,
                COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0) as likes_count,
                COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0) as comments_count,
                COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0) as shares_count,
                COALESCE(posting_metrics.saves, posting_metrics.saves_count, 0) as saves_count,
                COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0) as actual_cpv,
                COALESCE(posting_metrics.engagement_rate, 0) as engagement_rate,
                COALESCE(posting_metrics.snapshot_at, posting_metrics.last_scraped_at) as last_scraped_at,
                task_sentiments.sentiment_score,
                task_sentiments.positive_count,
                task_sentiments.neutral_count,
                task_sentiments.negative_count
            ')
            ->join('tasks', 'tasks.id = postings.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left')
            ->join('posting_metrics', 'posting_metrics.id = postings.latest_metric_id', 'left')
            ->join('task_sentiments', 'task_sentiments.task_id = tasks.id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['platform_id'])) {
            $builder->where('postings.platform_id', (int)$filters['platform_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('postings.status', $filters['status']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('postings.live_url', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
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
     * Get single posting with metrics, sentiment, snapshots, and apify jobs
     */
    public function getWithDetails(int $id): ?array
    {
        $posting = $this->db->table($this->table)
            ->select('
                postings.*,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                COALESCE(tasks.budget, tasks.rate_amount) as rate_amount,
                COALESCE(tasks.estimated_posting_date, tasks.deadline) as task_deadline,
                campaigns.id as campaign_id,
                COALESCE(campaigns.name, campaigns.title) as campaign_title,
                campaigns.budget_total as campaign_budget,
                campaigns.target_views as campaign_target_views,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                platforms.base_url as platform_base_url,
                COALESCE(posting_metrics.views, posting_metrics.views_count, 0) as views_count,
                COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0) as likes_count,
                COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0) as comments_count,
                COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0) as shares_count,
                COALESCE(posting_metrics.saves, posting_metrics.saves_count, 0) as saves_count,
                COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0) as actual_cpv,
                COALESCE(posting_metrics.engagement_rate, 0) as engagement_rate,
                COALESCE(posting_metrics.snapshot_at, posting_metrics.last_scraped_at) as last_scraped_at,
                task_sentiments.sentiment_score,
                task_sentiments.positive_count,
                task_sentiments.neutral_count,
                task_sentiments.negative_count,
                task_sentiments.analyzed_at as sentiment_analyzed_at
            ')
            ->join('tasks', 'tasks.id = postings.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left')
            ->join('posting_metrics', 'posting_metrics.id = postings.latest_metric_id', 'left')
            ->join('task_sentiments', 'task_sentiments.task_id = tasks.id', 'left')
            ->where('postings.id', $id)
            ->get()
            ->getRowArray();

        if (!$posting) {
            return null;
        }

        // Fetch all metric snapshots for decay curve (Phase 7 Parity)
        $posting['snapshots'] = $this->db->table('posting_metrics')
            ->where('posting_id', $id)
            ->orderBy('snapshot_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // Fetch Apify jobs history (Phase 7 Parity)
        $posting['apify_jobs'] = $this->db->table('apify_jobs')
            ->where('posting_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $posting;
    }

    /**
     * Save posting & initial metrics
     */
    public function savePosting(array $data, ?array $metrics = null): int
    {
        $this->db->transStart();

        $id = !empty($data['id']) ? (int)$data['id'] : null;
        unset($data['id']);

        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->update($id, $data);
            $postingId = $id;
        } else {
            // Check if already exists for this task_id
            $existing = $this->where('task_id', $data['task_id'])->first();
            if ($existing) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->update($existing['id'], $data);
                $postingId = (int)$existing['id'];
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $postingId = $this->insert($data, true);
            }
        }

        // Save or update posting_metrics snapshot
        $metricModel = new PostingMetricModel();
        $latestMetric = !empty($data['latest_metric_id']) ? $metricModel->find($data['latest_metric_id']) : null;

        $views = (int)($metrics['views_count'] ?? ($metrics['views'] ?? ($latestMetric['views_count'] ?? 0)));
        $likes = (int)($metrics['likes_count'] ?? ($metrics['likes'] ?? ($latestMetric['likes_count'] ?? 0)));
        $comments = (int)($metrics['comments_count'] ?? ($metrics['comments'] ?? ($latestMetric['comments_count'] ?? 0)));
        $shares = (int)($metrics['shares_count'] ?? ($metrics['shares'] ?? ($latestMetric['shares_count'] ?? 0)));
        $saves = (int)($metrics['saves_count'] ?? ($metrics['saves'] ?? ($latestMetric['saves_count'] ?? 0)));
        $followers = (int)($metrics['followers'] ?? ($latestMetric['followers'] ?? 0));

        // Get task fee for CPV calculation
        $task = $this->db->table('tasks')->where('id', $data['task_id'])->get()->getRowArray();
        $fee = (float)($task['rate_amount'] ?? $task['budget'] ?? 0);
        $totalInteractions = $likes + $comments + $shares + $saves;
        $er = ($views > 0) ? round(($totalInteractions / $views), 6) : 0.000000;
        $cpv = ($views > 0 && $fee > 0) ? round($fee / $views, 4) : 0.0000;
        $cpe = ($totalInteractions > 0 && $fee > 0) ? round($fee / $totalInteractions, 4) : 0.0000;

        $metricData = [
            'posting_id'      => $postingId,
            'snapshot_at'     => date('Y-m-d H:i:s'),
            'views'           => $views,
            'likes'           => $likes,
            'comments'        => $comments,
            'shares'          => $shares,
            'saves'           => $saves,
            'followers'       => $followers,
            'engagement_rate' => $er,
            'cpv'             => $cpv,
            'cpe'             => $cpe,
            'views_count'     => $views,
            'likes_count'     => $likes,
            'comments_count'  => $comments,
            'shares_count'    => $shares,
            'saves_count'     => $saves,
            'actual_cpv'      => $cpv,
            'last_scraped_at' => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $metricId = $metricModel->insert($metricData);

        // Update postings latest_metric_id and live_url
        $this->update($postingId, [
            'latest_metric_id' => $metricId,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        // Update task status ke POSTED dan sinkronkan live_url
        $this->db->table('tasks')->where('id', $data['task_id'])->update([
            'status'     => TaskModel::STATUS_POSTED,
            'live_url'   => $data['live_url'] ?? '',
            'cpv'        => $cpv,
            'cpe'        => $cpe,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
        return $postingId;
    }

    /**
     * Get all postings for CSV Export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                postings.id,
                tasks.task_number,
                tasks.title as task_title,
                COALESCE(campaigns.name, campaigns.title) as campaign_name,
                kols.name as kol_name,
                platforms.name as platform_name,
                postings.live_url,
                COALESCE(posting_metrics.views, posting_metrics.views_count, 0) as views,
                COALESCE(posting_metrics.likes, posting_metrics.likes_count, 0) as likes,
                COALESCE(posting_metrics.comments, posting_metrics.comments_count, 0) as comments,
                COALESCE(posting_metrics.shares, posting_metrics.shares_count, 0) as shares,
                COALESCE(posting_metrics.saves, posting_metrics.saves_count, 0) as saves,
                COALESCE(posting_metrics.engagement_rate, 0) as er,
                COALESCE(posting_metrics.cpv, posting_metrics.actual_cpv, 0) as cpv,
                postings.status,
                postings.posted_at,
                postings.created_at
            ')
            ->join('tasks', 'tasks.id = postings.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left')
            ->join('posting_metrics', 'posting_metrics.id = postings.latest_metric_id', 'left')
            ->orderBy('postings.id', 'DESC');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['platform_id'])) {
            $builder->where('postings.platform_id', (int)$filters['platform_id']);
        }

        return $builder->get()->getResultArray();
    }
}
