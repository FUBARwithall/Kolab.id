<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskStatusHistoryModel extends Model
{
    protected $table            = 'task_status_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
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
                task_status_histories.*,
                COALESCE(users.name, users.email, "System") as user_name,
                users.avatar as user_avatar
            ')
            ->join('users', 'users.id = task_status_histories.changed_by', 'left')
            ->where('task_status_histories.task_id', $taskId)
            ->orderBy('task_status_histories.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
