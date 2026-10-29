<?php

namespace App\Models;

use CodeIgniter\Model;

class ApifyJobModel extends Model
{
    protected $table            = 'apify_jobs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'posting_id',
        'actor_type',
        'target_url',
        'status',
        'response_data',
        'started_at',
        'finished_at',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * DataTables query with joins to posting, campaign, and KOL
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'apify_jobs.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                apify_jobs.*,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name
            ')
            ->join('postings', 'postings.id = apify_jobs.posting_id', 'left')
            ->join('tasks', 'tasks.id = postings.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left');

        if (!empty($filters['status'])) {
            $builder->where('apify_jobs.status', $filters['status']);
        }

        if (!empty($filters['actor_type'])) {
            $builder->where('apify_jobs.actor_type', $filters['actor_type']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('apify_jobs.target_url', $search)
                ->orLike('apify_jobs.status', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
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
     * Get aggregate statistics for dashboard badges
     */
    public function getSummaryStats(): array
    {
        $db = $this->db;
        $total     = $db->table($this->table)->countAllResults();
        $queued    = $db->table($this->table)->where('status', 'QUEUED')->countAllResults();
        $running   = $db->table($this->table)->where('status', 'RUNNING')->countAllResults();
        $succeeded = $db->table($this->table)->where('status', 'SUCCEEDED')->countAllResults();
        $failed    = $db->table($this->table)->where('status', 'FAILED')->countAllResults();

        return [
            'total'     => $total,
            'queued'    => $queued,
            'running'   => $running,
            'succeeded' => $succeeded,
            'failed'    => $failed,
        ];
    }
}
