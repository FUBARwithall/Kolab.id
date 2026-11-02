<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskBudgetHistoryModel extends Model
{
    protected $table            = 'task_budget_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'old_rate',
        'new_rate',
        'currency_code',
        'reason',
        'authorized_by',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get summary KPI metrics for Task Budget Histories
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                COUNT(task_budget_histories.id) as total_adjustments,
                COUNT(DISTINCT task_budget_histories.task_id) as total_tasks,
                COUNT(DISTINCT task_budget_histories.authorized_by) as total_authorized_users,
                SUM(CASE WHEN task_budget_histories.new_rate > task_budget_histories.old_rate THEN 1 ELSE 0 END) as count_increase,
                SUM(CASE WHEN task_budget_histories.new_rate < task_budget_histories.old_rate THEN 1 ELSE 0 END) as count_decrease,
                SUM(CASE WHEN task_budget_histories.new_rate > task_budget_histories.old_rate THEN (task_budget_histories.new_rate - task_budget_histories.old_rate) ELSE 0 END) as total_increase_amount,
                SUM(CASE WHEN task_budget_histories.new_rate < task_budget_histories.old_rate THEN (task_budget_histories.old_rate - task_budget_histories.new_rate) ELSE 0 END) as total_decrease_amount,
                SUM(task_budget_histories.new_rate) as total_new_rate
            ')
            ->join('tasks', 'tasks.id = task_budget_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left');

        if (!empty($filters['task_id'])) {
            $builder->where('task_budget_histories.task_id', (int)$filters['task_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['authorized_by'])) {
            $builder->where('task_budget_histories.authorized_by', (int)$filters['authorized_by']);
        }

        if (!empty($filters['date_start'])) {
            $builder->where('task_budget_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('task_budget_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        $row = $builder->get()->getRowArray() ?? [];

        return [
            'total_adjustments'     => (int)($row['total_adjustments'] ?? 0),
            'total_tasks'           => (int)($row['total_tasks'] ?? 0),
            'total_authorized_users'=> (int)($row['total_authorized_users'] ?? 0),
            'count_increase'        => (int)($row['count_increase'] ?? 0),
            'count_decrease'        => (int)($row['count_decrease'] ?? 0),
            'total_increase_amount' => (float)($row['total_increase_amount'] ?? 0),
            'total_decrease_amount' => (float)($row['total_decrease_amount'] ?? 0),
            'total_new_rate'        => (float)($row['total_new_rate'] ?? 0),
        ];
    }

    /**
     * DataTables query with joins to tasks, campaign_kols, campaigns, kols, and users
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'task_budget_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->join('tasks', 'tasks.id = task_budget_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = task_budget_histories.authorized_by', 'left');

        if (!empty($filters['task_id'])) {
            $builder->where('task_budget_histories.task_id', (int)$filters['task_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kols.id', (int)$filters['kol_id']);
        }

        if (!empty($filters['authorized_by'])) {
            $builder->where('task_budget_histories.authorized_by', (int)$filters['authorized_by']);
        }

        if (!empty($filters['adjustment_type'])) {
            if ($filters['adjustment_type'] === 'increase') {
                $builder->where('task_budget_histories.new_rate > task_budget_histories.old_rate');
            } elseif ($filters['adjustment_type'] === 'decrease') {
                $builder->where('task_budget_histories.new_rate < task_budget_histories.old_rate');
            } elseif ($filters['adjustment_type'] === 'same') {
                $builder->where('task_budget_histories.new_rate = task_budget_histories.old_rate');
            }
        }

        if (!empty($filters['date_start'])) {
            $builder->where('task_budget_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('task_budget_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('tasks.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('users.name', $search)
                ->orLike('task_budget_histories.reason', $search)
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $recordsFiltered = $countBuilder->countAllResults(false);

        $builder->select('
                task_budget_histories.*,
                tasks.title as task_title,
                tasks.task_number,
                tasks.status as task_status,
                tasks.content_type,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                users.id as user_id,
                users.name as authorized_name,
                users.email as authorized_email
            ')
            ->orderBy($orderCol, $orderDir);

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
     * Get single detail for budget history view & modal
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                task_budget_histories.*,
                tasks.title as task_title,
                tasks.task_number,
                tasks.status as task_status,
                tasks.content_type,
                tasks.rate_amount as current_task_rate,
                tasks.deadline as task_deadline,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.email as kol_email,
                kols.phone as kol_phone,
                users.id as user_id,
                users.name as authorized_name,
                users.email as authorized_email
            ')
            ->join('tasks', 'tasks.id = task_budget_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = task_budget_histories.authorized_by', 'left')
            ->where('task_budget_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get histories with user details by Task ID
     */
    public function getByTaskId(int $taskId): array
    {
        return $this->db->table($this->table)
            ->select('
                task_budget_histories.*,
                COALESCE(users.name, users.email, "System") as user_name,
                users.avatar as user_avatar
            ')
            ->join('users', 'users.id = task_budget_histories.authorized_by', 'left')
            ->where('task_budget_histories.task_id', $taskId)
            ->orderBy('task_budget_histories.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all logs for export CSV & PDF
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                task_budget_histories.*,
                tasks.title as task_title,
                tasks.task_number,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                users.name as authorized_name,
                users.email as authorized_email
            ')
            ->join('tasks', 'tasks.id = task_budget_histories.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = task_budget_histories.authorized_by', 'left');

        if (!empty($filters['task_id'])) {
            $builder->where('task_budget_histories.task_id', (int)$filters['task_id']);
        }
        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }
        if (!empty($filters['authorized_by'])) {
            $builder->where('task_budget_histories.authorized_by', (int)$filters['authorized_by']);
        }

        return $builder->orderBy('task_budget_histories.id', 'DESC')->get()->getResultArray();
    }
}
