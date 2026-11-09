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
        'notes',
        'brief_file',
        'brief_note',
        'draft_url',
        'draft_note',
        'live_url',
        'deadline',
        'estimated_posting_date',
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
        $tasks = $this->select('
                tasks.*,
                c.id as campaign_id,
                c.title as campaign_title,
                c.status as campaign_status,
                p.name as platform_name,
                p.icon as platform_icon
            ')
            ->join('campaign_kols ck', 'ck.id = tasks.campaign_kol_id')
            ->join('campaigns c', 'c.id = ck.campaign_id')
            ->join('platforms p', 'p.id = tasks.platform_id', 'left')
            ->where('ck.kol_id', $kolId)
            ->orderBy('tasks.id', 'DESC')
            ->findAll();

        if (empty($tasks)) {
            return [];
        }

        $db = \Config\Database::connect();
        foreach ($tasks as &$task) {
            $latestDraft = $db->table('drafts')
                ->where('task_id', $task['id'])
                ->orderBy('version', 'DESC')
                ->get()
                ->getRowArray();

            $task['latest_draft']       = $latestDraft;
            $task['draft_version']      = $latestDraft ? (int) $latestDraft['version'] : 0;
            $task['next_draft_version'] = ($task['draft_version'] ?? 0) + 1;
        }

        return $tasks;
    }
}
