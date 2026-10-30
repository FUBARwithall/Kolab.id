<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'variant_id',
        'platform_id',
        'content_type',
        'task_number',
        'title',
        'rate_amount',
        'budget',
        'cpv',
        'cpe',
        'currency_code',
        'is_rate_locked',
        'status',
        'deadline',
        'estimated_posting_date',
        'notes',
        'brief_file',
        'brief_note',
        'draft_url',
        'draft_note',
        'live_url',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Canonical CI4 Statuses
    public const STATUS_DRAFT            = 'DRAFT';
    public const STATUS_BRIEF_SUBMITTED  = 'BRIEF_SUBMITTED';
    public const STATUS_BRIEF_REVISION   = 'BRIEF_REVISION';
    public const STATUS_BRIEF_APPROVED   = 'BRIEF_APPROVED';
    public const STATUS_DRAFT_SUBMITTED  = 'DRAFT_SUBMITTED';
    public const STATUS_DRAFT_REVISION   = 'DRAFT_REVISION';
    public const STATUS_DRAFT_APPROVED   = 'DRAFT_APPROVED';
    public const STATUS_SCHEDULED        = 'SCHEDULED';
    public const STATUS_POSTED           = 'POSTED';
    public const STATUS_SCRAPING         = 'SCRAPING';
    public const STATUS_COMPLETED        = 'COMPLETED';
    public const STATUS_CANCELLED        = 'CANCELLED';

    // CI3 Legacy Status Equivalents
    public const STATUS_PENDING_REVIEW   = 'PENDING_REVIEW';
    public const STATUS_REVISION         = 'REVISION';
    public const STATUS_WAITING_DRAFT    = 'WAITING_DRAFT';
    public const STATUS_IN_REVIEW_DRAFT  = 'IN_REVIEW_DRAFT';
    public const STATUS_REVISION_DRAFT   = 'REVISION_DRAFT';
    public const STATUS_APPROVED_DRAFT   = 'APPROVED_DRAFT';
    public const STATUS_DONE             = 'DONE';

    /**
     * Map any incoming status string to canonical or compatible format
     */
    public static function normalizeStatus(string $status): string
    {
        $st = strtoupper(trim($status));
        return match ($st) {
            'PENDING_REVIEW'   => self::STATUS_BRIEF_SUBMITTED,
            'REVISION'         => self::STATUS_BRIEF_REVISION,
            'WAITING_DRAFT'    => self::STATUS_BRIEF_APPROVED,
            'IN_REVIEW_DRAFT'  => self::STATUS_DRAFT_SUBMITTED,
            'REVISION_DRAFT'   => self::STATUS_DRAFT_REVISION,
            'APPROVED_DRAFT'   => self::STATUS_DRAFT_APPROVED,
            'DONE'             => self::STATUS_COMPLETED,
            default            => $st,
        };
    }

    /**
     * Get summary metrics for tasks dashboard
     */
    public function getSummaryMetrics(): array
    {
        $db = $this->db;
        $all = $db->table($this->table)->select('status, count(id) as total')->groupBy('status')->get()->getResultArray();
        
        $metrics = [
            'total'           => 0,
            'draft'           => 0,
            'brief_in_review' => 0,
            'draft_in_review' => 0,
            'live_posted'     => 0,
            'completed'       => 0,
        ];

        foreach ($all as $row) {
            $status = strtoupper(trim((string)$row['status']));
            $count = (int)$row['total'];
            $metrics['total'] += $count;

            if ($status === '' || $status === self::STATUS_DRAFT) {
                $metrics['draft'] += $count;
            } elseif ($status === self::STATUS_BRIEF_SUBMITTED || $status === self::STATUS_BRIEF_REVISION || $status === self::STATUS_PENDING_REVIEW || $status === self::STATUS_REVISION) {
                $metrics['brief_in_review'] += $count;
            } elseif ($status === self::STATUS_DRAFT_SUBMITTED || $status === self::STATUS_DRAFT_REVISION || $status === self::STATUS_IN_REVIEW_DRAFT || $status === self::STATUS_REVISION_DRAFT) {
                $metrics['draft_in_review'] += $count;
            } elseif ($status === self::STATUS_POSTED || $status === self::STATUS_SCHEDULED || $status === self::STATUS_SCRAPING) {
                $metrics['live_posted'] += $count;
            } elseif ($status === self::STATUS_COMPLETED || $status === self::STATUS_DONE) {
                $metrics['completed'] += $count;
            }
        }

        return $metrics;
    }

    /**
     * DataTables Server-Side query with relational joins and filters
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'tasks.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                tasks.*,
                COALESCE(tasks.budget, tasks.rate_amount) as final_budget,
                COALESCE(tasks.estimated_posting_date, tasks.deadline) as final_deadline,
                campaign_kols.campaign_id,
                campaign_kols.deal_amount,
                campaign_kols.kol_id,
                COALESCE(campaigns.name, campaigns.title) as campaign_title,
                campaigns.status as campaign_status,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                users.avatar as kol_avatar,
                variants.name as variant_name,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                briefs.id as brief_id,
                briefs.approval_status as brief_status,
                drafts.id as draft_id,
                drafts.status as draft_status,
                drafts.version as draft_version,
                postings.id as posting_id,
                postings.live_url as posting_live_url,
                postings.posted_at as posting_posted_at,
                posting_metrics.views_count as metric_views,
                posting_metrics.likes_count as metric_likes,
                posting_metrics.comments_count as metric_comments,
                posting_metrics.shares_count as metric_shares,
                posting_metrics.saves_count as metric_saves,
                posting_metrics.actual_cpv as metric_cpv
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('variants', 'variants.id = tasks.variant_id', 'left')
            ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
            ->join('briefs', 'briefs.id = (SELECT MAX(b.id) FROM briefs b WHERE b.task_id = tasks.id)', 'left')
            ->join('drafts', 'drafts.id = (SELECT MAX(d.id) FROM drafts d WHERE d.task_id = tasks.id)', 'left')
            ->join('postings', 'postings.id = (SELECT MAX(p.id) FROM postings p WHERE p.task_id = tasks.id)', 'left')
            ->join('posting_metrics', 'posting_metrics.posting_id = postings.id', 'left');

        // Apply CI3 view parameter filters
        if (!empty($filters['view'])) {
            switch ($filters['view']) {
                case 'review_draft':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_DRAFT_SUBMITTED,
                        self::STATUS_DRAFT_REVISION,
                        self::STATUS_WAITING_DRAFT,
                        self::STATUS_IN_REVIEW_DRAFT,
                        self::STATUS_REVISION_DRAFT,
                    ]);
                    break;
                case 'live_posting':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_DRAFT_APPROVED,
                        self::STATUS_APPROVED_DRAFT,
                        self::STATUS_SCHEDULED,
                        self::STATUS_POSTED,
                        self::STATUS_SCRAPING,
                        self::STATUS_COMPLETED,
                        self::STATUS_DONE,
                    ]);
                    break;
                case 'sow':
                case 'brief':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_DRAFT,
                        self::STATUS_BRIEF_SUBMITTED,
                        self::STATUS_BRIEF_REVISION,
                        self::STATUS_PENDING_REVIEW,
                        self::STATUS_REVISION,
                    ]);
                    break;
                case 'monitoring':
                default:
                    // Show all tasks
                    break;
            }
        } elseif (!empty($filters['stage'])) {
            // Apply CI4 stage filter
            switch ($filters['stage']) {
                case 'sow':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_DRAFT,
                        self::STATUS_BRIEF_SUBMITTED,
                        self::STATUS_BRIEF_REVISION,
                        self::STATUS_BRIEF_APPROVED,
                        self::STATUS_PENDING_REVIEW,
                        self::STATUS_REVISION,
                    ]);
                    break;
                case 'draft':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_DRAFT_SUBMITTED,
                        self::STATUS_DRAFT_REVISION,
                        self::STATUS_DRAFT_APPROVED,
                        self::STATUS_WAITING_DRAFT,
                        self::STATUS_IN_REVIEW_DRAFT,
                        self::STATUS_REVISION_DRAFT,
                        self::STATUS_APPROVED_DRAFT,
                    ]);
                    break;
                case 'posting':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_SCHEDULED,
                        self::STATUS_POSTED,
                        self::STATUS_SCRAPING,
                    ]);
                    break;
                case 'completed':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_COMPLETED,
                        self::STATUS_DONE,
                    ]);
                    break;
            }
        }

        // Apply campaign filter
        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        // Apply KOL filter
        if (!empty($filters['kol_id'])) {
            $builder->where('campaign_kols.kol_id', (int)$filters['kol_id']);
        }

        // Apply platform filter
        if (!empty($filters['platform_id'])) {
            $builder->where('tasks.platform_id', (int)$filters['platform_id']);
        }

        // Apply variant filter
        if (!empty($filters['variant_id'])) {
            $builder->where('tasks.variant_id', (int)$filters['variant_id']);
        }

        // Apply specific status filter
        if (!empty($filters['status'])) {
            $builder->where('tasks.status', $filters['status']);
        }

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tasks.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('kols.name', $search)
                ->orLike('variants.name', $search)
                ->orLike('platforms.name', $search)
                ->orLike('tasks.status', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        // Sort and paginate
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
     * Get complete details of a single task
     */
    public function getTaskDetails(int $id): ?array
    {
        $task = $this->db->table($this->table)
            ->select('
                tasks.*,
                COALESCE(tasks.budget, tasks.rate_amount) as final_budget,
                COALESCE(tasks.estimated_posting_date, tasks.deadline) as final_deadline,
                campaign_kols.campaign_id,
                campaign_kols.deal_amount,
                campaign_kols.kol_id,
                COALESCE(campaigns.name, campaigns.title) as campaign_title,
                campaigns.status as campaign_status,
                campaigns.budget_total as campaign_budget,
                campaigns.target_views as campaign_target_views,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address,
                users.avatar as kol_avatar,
                users.email as kol_email,
                variants.name as variant_name,
                variants.sku as variant_sku,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                platforms.base_url as platform_base_url
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('variants', 'variants.id = tasks.variant_id', 'left')
            ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
            ->where('tasks.id', $id)
            ->get()
            ->getRowArray();

        if (!$task) {
            return null;
        }

        // Brief data
        $task['brief'] = $this->db->table('briefs')
            ->where('task_id', $id)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        // Drafts history
        $task['drafts'] = $this->db->table('drafts')
            ->where('task_id', $id)
            ->orderBy('version', 'DESC')
            ->get()
            ->getResultArray();

        // Live posting data
        $task['posting'] = $this->db->table('postings')
            ->where('task_id', $id)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($task['posting']) {
            $task['posting_metrics'] = $this->db->table('posting_metrics')
                ->where('posting_id', $task['posting']['id'])
                ->get()
                ->getRowArray();
        } else {
            $task['posting_metrics'] = null;
        }

        // Sentiment analysis
        $task['sentiment'] = $this->db->table('task_sentiments')
            ->where('task_id', $id)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        // Sample shipment status
        $task['shipment'] = $this->db->table('kol_shipments')
            ->where('campaign_kol_id', $task['campaign_kol_id'])
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        // Status history log
        $task['status_histories'] = (new TaskStatusHistoryModel())->getByTaskId($id);

        // Budget history log
        $task['budget_histories'] = (new TaskBudgetHistoryModel())->getByTaskId($id);

        return $task;
    }

    /**
     * Update task status safely and log audit trail
     */
    public function updateTaskStatus(int $id, string $newStatus, ?int $userId = null, ?string $notes = null): bool
    {
        $oldTask = $this->find($id);
        if (!$oldTask) {
            return false;
        }

        $fromStatus = $oldTask['status'];
        $toStatus   = self::normalizeStatus($newStatus);

        $updateData = [
            'status'     => $toStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $updated = $this->update($id, $updateData);

        if ($updated && $fromStatus !== $toStatus) {
            (new TaskStatusHistoryModel())->insert([
                'task_id'     => $id,
                'from_status' => $fromStatus,
                'to_status'   => $toStatus,
                'changed_by'  => $userId ?: 1,
                'notes'       => $notes ?: 'Pembaruan status tugas secara operasional',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return $updated;
    }

    /**
     * Update task rate / budget and log budget history
     */
    public function updateTaskRate(int $id, float $newRate, string $reason, int $userId): bool
    {
        $oldTask = $this->find($id);
        if (!$oldTask) {
            return false;
        }

        $oldRate = (float)($oldTask['rate_amount'] ?: $oldTask['budget'] ?: 0);

        $updated = $this->update($id, [
            'rate_amount' => $newRate,
            'budget'      => $newRate,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($updated && $oldRate != $newRate) {
            (new TaskBudgetHistoryModel())->insert([
                'task_id'       => $id,
                'old_rate'      => $oldRate,
                'new_rate'      => $newRate,
                'reason'        => $reason ?: 'Penyesuaian nominal tarif / budget task',
                'authorized_by' => $userId ?: 1,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $updated;
    }

    /**
     * Get all tasks for CSV Export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                tasks.id,
                tasks.task_number,
                tasks.title,
                COALESCE(campaigns.name, campaigns.title) as campaign_name,
                kols.name as kol_name,
                platforms.name as platform_name,
                variants.name as variant_name,
                COALESCE(tasks.budget, tasks.rate_amount) as final_budget,
                tasks.currency_code,
                COALESCE(tasks.estimated_posting_date, tasks.deadline) as final_deadline,
                tasks.status,
                tasks.cpv,
                tasks.cpe,
                COALESCE(postings.live_url, tasks.live_url) as live_url,
                tasks.created_at
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('variants', 'variants.id = tasks.variant_id', 'left')
            ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
            ->join('postings', 'postings.id = (SELECT MAX(p.id) FROM postings p WHERE p.task_id = tasks.id)', 'left')
            ->orderBy('tasks.id', 'DESC');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('campaign_kols.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('tasks.status', $filters['status']);
        }

        return $builder->get()->getResultArray();
    }
}
