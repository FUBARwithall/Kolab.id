<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
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

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get tasks assigned to a specific KOL
     */
    public function getTasksByKol(int $kolId): array
    {
        return $this->select('
                tasks.*,
                c.id as campaign_id,
                c.title as campaign_title,
                c.status as campaign_status
            ')
            ->join('campaign_kols ck', 'ck.id = tasks.campaign_kol_id')
            ->join('campaigns c', 'c.id = ck.campaign_id')
            ->where('ck.kol_id', $kolId)
            ->orderBy('tasks.id', 'DESC')
            ->findAll();
    }
}

