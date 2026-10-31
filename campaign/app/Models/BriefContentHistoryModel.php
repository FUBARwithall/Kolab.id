<?php

namespace App\Models;

use CodeIgniter\Model;

class BriefContentHistoryModel extends Model
{
    protected $table            = 'brief_content_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'brief_id',
        'version',
        'title',
        'content',
        'content_diff',
        'snapshot_at',
        'created_by',
        'updated_by',
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
                COUNT(brief_content_histories.id) as total_snapshots,
                COUNT(DISTINCT brief_content_histories.brief_id) as total_briefs_revised,
                COALESCE(MAX(brief_content_histories.version), 1) as max_version
            ')
            ->join('briefs', 'briefs.id = brief_content_histories.brief_id', 'left');

        if (!empty($filters['brief_id'])) {
            $builder->where('brief_content_histories.brief_id', (int)$filters['brief_id']);
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_snapshots'      => (int)($row['total_snapshots'] ?? 0),
            'total_briefs_revised' => (int)($row['total_briefs_revised'] ?? 0),
            'max_version'          => (int)($row['max_version'] ?? 1),
        ];
    }

    /**
     * DataTables query with joins to briefs, tasks, campaigns, and KOLs
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'brief_content_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                brief_content_histories.*,
                briefs.title as briefs_title,
                briefs.status as brief_status,
                briefs.revision_count,
                tasks.id as task_id,
                tasks.task_number,
                tasks.title as task_title,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                COALESCE(u.name, u2.name, "Editor") as author_name
            ')
            ->join('briefs', 'briefs.id = brief_content_histories.brief_id', 'inner')
            ->join('tasks', 'tasks.id = briefs.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u', 'u.id = brief_content_histories.created_by', 'left')
            ->join('users u2', 'u2.id = brief_content_histories.updated_by', 'left');

        if (!empty($filters['brief_id'])) {
            $builder->where('brief_content_histories.brief_id', (int)$filters['brief_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('brief_content_histories.title', $search)
                ->orLike('brief_content_histories.content', $search)
                ->orLike('briefs.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('u.name', $search)
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
     * Get single brief content history with full joined details
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                brief_content_histories.*,
                briefs.title as briefs_title,
                briefs.content as current_brief_content,
                briefs.guidelines as current_brief_guidelines,
                briefs.status as brief_status,
                briefs.revision_count,
                tasks.id as task_id,
                tasks.task_number,
                tasks.title as task_title,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                COALESCE(u.name, u2.name, "Editor") as author_name
            ')
            ->join('briefs', 'briefs.id = brief_content_histories.brief_id', 'inner')
            ->join('tasks', 'tasks.id = briefs.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u', 'u.id = brief_content_histories.created_by', 'left')
            ->join('users u2', 'u2.id = brief_content_histories.updated_by', 'left')
            ->where('brief_content_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Export all brief content histories
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                brief_content_histories.*,
                briefs.title as briefs_title,
                tasks.task_number,
                tasks.title as task_title,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                COALESCE(u.name, u2.name, "Editor") as author_name
            ')
            ->join('briefs', 'briefs.id = brief_content_histories.brief_id', 'inner')
            ->join('tasks', 'tasks.id = briefs.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u', 'u.id = brief_content_histories.created_by', 'left')
            ->join('users u2', 'u2.id = brief_content_histories.updated_by', 'left');

        if (!empty($filters['brief_id'])) {
            $builder->where('brief_content_histories.brief_id', (int)$filters['brief_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        return $builder->orderBy('brief_content_histories.id', 'DESC')->get()->getResultArray();
    }
}
