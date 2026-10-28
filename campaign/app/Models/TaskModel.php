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
        'task_number',
        'title',
        'rate_amount',
        'is_rate_locked',
        'status',
        'deadline',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

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
            } elseif ($status === self::STATUS_BRIEF_SUBMITTED || $status === self::STATUS_BRIEF_REVISION) {
                $metrics['brief_in_review'] += $count;
            } elseif ($status === self::STATUS_DRAFT_SUBMITTED || $status === self::STATUS_DRAFT_REVISION) {
                $metrics['draft_in_review'] += $count;
            } elseif ($status === self::STATUS_POSTED || $status === self::STATUS_SCHEDULED || $status === self::STATUS_SCRAPING) {
                $metrics['live_posted'] += $count;
            } elseif ($status === self::STATUS_COMPLETED) {
                $metrics['completed'] += $count;
            }
        }

        return $metrics;
    }

    /**
     * DataTables query with relational joins and filters
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'tasks.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                tasks.*,
                campaign_kols.campaign_id,
                campaign_kols.deal_amount,
                campaign_kols.kol_id,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                users.avatar as kol_avatar,
                briefs.id as brief_id,
                briefs.approval_status as brief_status,
                drafts.id as draft_id,
                drafts.status as draft_status,
                drafts.version as draft_version,
                postings.id as posting_id,
                postings.live_url,
                postings.posted_at
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('briefs', 'briefs.task_id = tasks.id', 'left')
            ->join('drafts', 'drafts.task_id = tasks.id', 'left')
            ->join('postings', 'postings.task_id = tasks.id', 'left');

        // Apply tab / stage filter
        if (!empty($filters['stage'])) {
            switch ($filters['stage']) {
                case 'sow':
                    $builder->groupStart()
                        ->where('tasks.status', '')
                        ->orWhere('tasks.status', self::STATUS_DRAFT)
                        ->orWhere('tasks.status', self::STATUS_BRIEF_SUBMITTED)
                        ->orWhere('tasks.status', self::STATUS_BRIEF_REVISION)
                        ->orWhere('tasks.status', self::STATUS_BRIEF_APPROVED)
                        ->groupEnd();
                    break;
                case 'draft':
                    $builder->whereIn('tasks.status', [
                        self::STATUS_DRAFT_SUBMITTED,
                        self::STATUS_DRAFT_REVISION,
                        self::STATUS_DRAFT_APPROVED,
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
                    $builder->where('tasks.status', self::STATUS_COMPLETED);
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

        // Apply specific status filter
        if (!empty($filters['status'])) {
            $builder->where('tasks.status', $filters['status']);
        }

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('tasks.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
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
                campaign_kols.campaign_id,
                campaign_kols.deal_amount,
                campaign_kols.kol_id,
                campaigns.title as campaign_title,
                campaigns.status as campaign_status,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address,
                users.avatar as kol_avatar
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('tasks.id', $id)
            ->get()
            ->getRowArray();

        if (!$task) {
            return null;
        }

        // Brief data
        $task['brief'] = $this->db->table('briefs')
            ->where('task_id', $id)
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

        return $task;
    }

    /**
     * Update task status safely
     */
    public function updateTaskStatus(int $id, string $newStatus): bool
    {
        $validStatuses = [
            self::STATUS_DRAFT,
            self::STATUS_BRIEF_SUBMITTED,
            self::STATUS_BRIEF_REVISION,
            self::STATUS_BRIEF_APPROVED,
            self::STATUS_DRAFT_SUBMITTED,
            self::STATUS_DRAFT_REVISION,
            self::STATUS_DRAFT_APPROVED,
            self::STATUS_SCHEDULED,
            self::STATUS_POSTED,
            self::STATUS_SCRAPING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];

        if (!in_array($newStatus, $validStatuses, true)) {
            return false;
        }

        return $this->update($id, [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
