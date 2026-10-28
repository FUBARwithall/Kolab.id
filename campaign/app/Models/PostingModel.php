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
        'posted_at',
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
                COALESCE(SUM(posting_metrics.views_count), 0) as total_views,
                COALESCE(SUM(posting_metrics.likes_count), 0) as total_likes,
                COALESCE(SUM(posting_metrics.comments_count), 0) as total_comments,
                COALESCE(SUM(posting_metrics.shares_count), 0) as total_shares,
                COALESCE(SUM(posting_metrics.saves_count), 0) as total_saves
            ')
            ->join('posting_metrics', 'posting_metrics.posting_id = postings.id', 'left')
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
                tasks.rate_amount,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                users.avatar as kol_avatar,
                platforms.name as platform_name,
                platforms.icon as platform_icon,
                posting_metrics.views_count,
                posting_metrics.likes_count,
                posting_metrics.comments_count,
                posting_metrics.shares_count,
                posting_metrics.saves_count,
                posting_metrics.actual_cpv,
                posting_metrics.last_scraped_at,
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
            ->join('posting_metrics', 'posting_metrics.posting_id = postings.id', 'left')
            ->join('task_sentiments', 'task_sentiments.task_id = tasks.id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['platform_id'])) {
            $builder->where('postings.platform_id', (int)$filters['platform_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('postings.live_url', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('tasks.title', $search)
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
     * Get single posting with metrics & sentiment
     */
    public function getWithDetails(int $id): ?array
    {
        $posting = $this->db->table($this->table)
            ->select('
                postings.*,
                tasks.task_number,
                tasks.title as task_title,
                tasks.rate_amount,
                tasks.deadline as task_deadline,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                platforms.name as platform_name,
                platforms.icon as platform_icon,
                posting_metrics.views_count,
                posting_metrics.likes_count,
                posting_metrics.comments_count,
                posting_metrics.shares_count,
                posting_metrics.saves_count,
                posting_metrics.actual_cpv,
                posting_metrics.last_scraped_at,
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
            ->join('posting_metrics', 'posting_metrics.posting_id = postings.id', 'left')
            ->join('task_sentiments', 'task_sentiments.task_id = tasks.id', 'left')
            ->where('postings.id', $id)
            ->get()
            ->getRowArray();

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
            $this->update($id, $data);
            $postingId = $id;
        } else {
            // Check if already exists for this task_id
            $existing = $this->where('task_id', $data['task_id'])->first();
            if ($existing) {
                $this->update($existing['id'], $data);
                $postingId = (int)$existing['id'];
            } else {
                $postingId = $this->insert($data, true);
            }
        }

        // Save or update posting_metrics
        $metricModel = new PostingMetricModel();
        $existingMetric = $metricModel->where('posting_id', $postingId)->first();

        $views = (int)($metrics['views_count'] ?? ($existingMetric['views_count'] ?? 0));
        $likes = (int)($metrics['likes_count'] ?? ($existingMetric['likes_count'] ?? 0));
        $comments = (int)($metrics['comments_count'] ?? ($existingMetric['comments_count'] ?? 0));
        $shares = (int)($metrics['shares_count'] ?? ($existingMetric['shares_count'] ?? 0));
        $saves = (int)($metrics['saves_count'] ?? ($existingMetric['saves_count'] ?? 0));

        // Get task fee for CPV calculation
        $task = $this->db->table('tasks')->where('id', $data['task_id'])->get()->getRowArray();
        $fee = (float)($task['rate_amount'] ?? 0);
        $cpv = ($views > 0 && $fee > 0) ? round($fee / $views, 4) : 0.0000;

        $metricData = [
            'posting_id'      => $postingId,
            'views_count'     => $views,
            'likes_count'     => $likes,
            'comments_count'  => $comments,
            'shares_count'    => $shares,
            'saves_count'     => $saves,
            'actual_cpv'      => $cpv,
            'last_scraped_at' => date('Y-m-d H:i:s'),
        ];

        if ($existingMetric) {
            $metricModel->update($existingMetric['id'], $metricData);
        } else {
            $metricModel->insert($metricData);
        }

        // Update task status ke POSTED
        $this->db->table('tasks')->where('id', $data['task_id'])->update([
            'status'     => TaskModel::STATUS_POSTED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
        return $postingId;
    }
}
