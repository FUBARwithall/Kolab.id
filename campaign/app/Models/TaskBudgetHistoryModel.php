<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskBudgetHistoryModel extends Model
{
    protected $table            = 'task_budget_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'old_rate',
        'new_rate',
        'reason',
        'authorized_by',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get histories with user details
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
}
