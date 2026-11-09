<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskStatusHistoryModel extends Model
{
    protected $table            = 'task_status_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'from_status',
        'to_status',
        'status',
        'changed_by',
        'notes',
        'note',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get summary KPI metrics
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                COUNT(task_status_histories.id) as total_logs,
                COUNT(DISTINCT task_status_histories.task_id) as total_tasks,
                COUNT(DISTINCT task_status_histories.changed_by) as total_actors,
                SUM(CASE WHEN UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status)) IN ("BRIEF_APPROVED", "DRAFT_APPROVED", "APPROVED_DRAFT", "COMPLETED", "DONE", "POSTED") THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status)) IN ("BRIEF_REVISION", "DRAFT_REVISION", "REVISION", "REVISION_DRAFT") THEN 1 ELSE 0 END) as total_revision,
                SUM(CASE WHEN UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status)) IN ("BRIEF_SUBMITTED", "DRAFT_SUBMITTED", "PENDING_REVIEW", "IN_REVIEW_DRAFT", "SCRAPING") THEN 1 ELSE 0 END) as total_submitted
            ')
            ->join('tasks', 'tasks.id = task_status_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left');

        if (!empty($filters['task_id'])) {
            $builder->where('task_status_histories.task_id', (int)$filters['task_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $st = strtoupper(trim((string)$filters['status']));
            $builder->groupStart()
                ->where('task_status_histories.status', $st)
                ->orWhere('task_status_histories.to_status', $st)
                ->groupEnd();
        }

        if (!empty($filters['changed_by'])) {
            $builder->where('task_status_histories.changed_by', (int)$filters['changed_by']);
        }

        if (!empty($filters['date_start'])) {
            $builder->where('task_status_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('task_status_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_logs'       => (int)($row['total_logs'] ?? 0),
            'total_tasks'      => (int)($row['total_tasks'] ?? 0),
            'total_actors'     => (int)($row['total_actors'] ?? 0),
            'total_approved'   => (int)($row['total_approved'] ?? 0),
            'total_revision'   => (int)($row['total_revision'] ?? 0),
            'total_submitted'  => (int)($row['total_submitted'] ?? 0),
        ];
    }

    /**
     * DataTables query with joins to tasks, campaign_kols, campaigns, kols, and users
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'task_status_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                task_status_histories.*,
                COALESCE(task_status_histories.note, task_status_histories.notes, "") as resolved_note,
                UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status, "DRAFT")) as resolved_status,
                tasks.id as tasks_id,
                tasks.title as task_title,
                tasks.title as tasks_title,
                tasks.task_number,
                tasks.status as task_current_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                COALESCE(users.name, "System") as changed_by_name,
                COALESCE(users.name, "System") as aauth_users_full_name,
                COALESCE(users.email, "") as changed_by_email,
                users.avatar as user_avatar
            ')
            ->join('tasks', 'tasks.id = task_status_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = task_status_histories.changed_by', 'left');

        if (!empty($filters['task_id'])) {
            $builder->where('task_status_histories.task_id', (int)$filters['task_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $st = strtoupper(trim((string)$filters['status']));
            $builder->groupStart()
                ->where('task_status_histories.status', $st)
                ->orWhere('task_status_histories.to_status', $st)
                ->groupEnd();
        }

        if (!empty($filters['changed_by'])) {
            $builder->where('task_status_histories.changed_by', (int)$filters['changed_by']);
        }

        if (!empty($filters['date_start'])) {
            $builder->where('task_status_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('task_status_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('task_status_histories.note', $search)
                ->orLike('task_status_histories.notes', $search)
                ->orLike('task_status_histories.status', $search)
                ->orLike('task_status_histories.to_status', $search)
                ->orLike('task_status_histories.from_status', $search)
                ->orLike('tasks.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('users.name', $search)
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $filtered = $countBuilder->countAllResults(false);

        $totalBuilder = $this->db->table($this->table);
        $total = $totalBuilder->countAllResults();

        // Safe column ordering mapping
        $orderMap = [
            'id'             => 'task_status_histories.id',
            'task_id'        => 'tasks.title',
            'from_status'    => 'task_status_histories.from_status',
            'to_status'      => 'task_status_histories.to_status',
            'status'         => 'task_status_histories.status',
            'changed_by'     => 'users.name',
            'note'           => 'task_status_histories.note',
            'created_at'     => 'task_status_histories.created_at',
        ];

        $cleanOrderCol = $orderMap[$orderCol] ?? 'task_status_histories.id';
        $cleanOrderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder->orderBy($cleanOrderCol, $cleanOrderDir);

        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();

        return [
            'data'            => $data,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
        ];
    }

    /**
     * Get single detail with complete relations
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                task_status_histories.*,
                COALESCE(task_status_histories.note, task_status_histories.notes, "") as resolved_note,
                UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status, "DRAFT")) as resolved_status,
                tasks.id as task_id,
                tasks.title as task_title,
                tasks.title as tasks_title,
                tasks.task_number,
                tasks.content_type,
                tasks.status as task_current_status,
                tasks.deadline as task_deadline,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                COALESCE(users.name, "System") as changed_by_name,
                COALESCE(users.name, "System") as aauth_users_full_name,
                COALESCE(users.email, "") as changed_by_email,
                users.avatar as user_avatar
            ')
            ->join('tasks', 'tasks.id = task_status_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = task_status_histories.changed_by', 'left')
            ->where('task_status_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all rows for export with filters
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                task_status_histories.*,
                COALESCE(task_status_histories.note, task_status_histories.notes, "") as resolved_note,
                UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status, "DRAFT")) as resolved_status,
                tasks.title as task_title,
                tasks.title as tasks_title,
                tasks.task_number,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                COALESCE(users.name, "System") as changed_by_name,
                COALESCE(users.name, "System") as aauth_users_full_name,
                COALESCE(users.email, "") as changed_by_email
            ')
            ->join('tasks', 'tasks.id = task_status_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = task_status_histories.changed_by', 'left');

        if (!empty($filters['task_id'])) {
            $builder->where('task_status_histories.task_id', (int)$filters['task_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $st = strtoupper(trim((string)$filters['status']));
            $builder->groupStart()
                ->where('task_status_histories.status', $st)
                ->orWhere('task_status_histories.to_status', $st)
                ->groupEnd();
        }

        if (!empty($filters['changed_by'])) {
            $builder->where('task_status_histories.changed_by', (int)$filters['changed_by']);
        }

        if (!empty($filters['date_start'])) {
            $builder->where('task_status_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('task_status_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        return $builder->orderBy('task_status_histories.id', 'DESC')->get()->getResultArray();
    }

    /**
     * Get histories with user details by Task ID (CI4 compatibility)
     */
    public function getByTaskId(int $taskId): array
    {
        return $this->db->table($this->table)
            ->select('
                task_status_histories.*,
                COALESCE(task_status_histories.note, task_status_histories.notes, "") as resolved_note,
                UPPER(COALESCE(task_status_histories.to_status, task_status_histories.status, "DRAFT")) as resolved_status,
                COALESCE(users.name, users.email, "System") as user_name,
                users.avatar as user_avatar
            ')
            ->join('users', 'users.id = task_status_histories.changed_by', 'left')
            ->where('task_status_histories.task_id', $taskId)
            ->orderBy('task_status_histories.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Record a status transition
     */
    public function logTransition(int $taskId, ?string $fromStatus, string $toStatus, ?int $userId = null, ?string $note = null): int
    {
        $data = [
            'task_id'     => $taskId,
            'from_status' => $fromStatus ? strtoupper(trim($fromStatus)) : null,
            'to_status'   => strtoupper(trim($toStatus)),
            'status'      => strtoupper(trim($toStatus)),
            'changed_by'  => $userId ?: (session('user_id') ?: 1),
            'note'        => $note,
            'notes'       => $note,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return (int) $this->insert($data, true);
    }
}
