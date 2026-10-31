<?php

namespace App\Models;

use CodeIgniter\Model;

class BriefApprovalHistoryModel extends Model
{
    protected $table            = 'brief_approval_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'brief_id',
        'reviewed_by',
        'acted_by',
        'status',
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
                COUNT(brief_approval_histories.id) as total_logs,
                COUNT(DISTINCT brief_approval_histories.brief_id) as total_briefs,
                COUNT(DISTINCT COALESCE(brief_approval_histories.acted_by, brief_approval_histories.reviewed_by)) as total_reviewers,
                SUM(CASE WHEN brief_approval_histories.status = "APPROVED" THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN brief_approval_histories.status IN ("REVISION", "REVISION_NEEDED") THEN 1 ELSE 0 END) as total_revision,
                SUM(CASE WHEN brief_approval_histories.status = "REJECTED" THEN 1 ELSE 0 END) as total_rejected
            ')
            ->join('briefs', 'briefs.id = brief_approval_histories.brief_id', 'left');

        if (!empty($filters['brief_id'])) {
            $builder->where('brief_approval_histories.brief_id', (int)$filters['brief_id']);
        }

        if (!empty($filters['status'])) {
            $status = strtoupper(trim((string)$filters['status']));
            if (in_array($status, ['REVISION', 'REVISION_NEEDED'])) {
                $builder->whereIn('brief_approval_histories.status', ['REVISION', 'REVISION_NEEDED']);
            } else {
                $builder->where('brief_approval_histories.status', $status);
            }
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_logs'       => (int)($row['total_logs'] ?? 0),
            'total_briefs'     => (int)($row['total_briefs'] ?? 0),
            'total_reviewers'  => (int)($row['total_reviewers'] ?? 0),
            'total_approved'   => (int)($row['total_approved'] ?? 0),
            'total_revision'   => (int)($row['total_revision'] ?? 0),
            'total_rejected'   => (int)($row['total_rejected'] ?? 0),
        ];
    }

    /**
     * DataTables query with joins to briefs, tasks, campaigns, and KOLs
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'brief_approval_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                brief_approval_histories.*,
                COALESCE(brief_approval_histories.note, brief_approval_histories.notes, "") as resolved_note,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name,
                COALESCE(u1.email, u2.email, "") as reviewer_email,
                briefs.title as briefs_title,
                briefs.title as brief_title,
                briefs.status as brief_status,
                briefs.approval_status as brief_approval_status,
                briefs.revision_count,
                tasks.id as task_id,
                tasks.task_number,
                tasks.title as task_title,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                kols.id as kol_id,
                kols.name as kol_name
            ')
            ->join('briefs', 'briefs.id = brief_approval_histories.brief_id', 'inner')
            ->join('tasks', 'tasks.id = briefs.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u1', 'u1.id = brief_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = brief_approval_histories.acted_by', 'left');

        if (!empty($filters['brief_id'])) {
            $builder->where('brief_approval_histories.brief_id', (int)$filters['brief_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $status = strtoupper(trim((string)$filters['status']));
            if (in_array($status, ['REVISION', 'REVISION_NEEDED'])) {
                $builder->whereIn('brief_approval_histories.status', ['REVISION', 'REVISION_NEEDED']);
            } else {
                $builder->where('brief_approval_histories.status', $status);
            }
        }

        if (!empty($filters['acted_by'])) {
            $userId = (int)$filters['acted_by'];
            $builder->groupStart()
                ->where('brief_approval_histories.acted_by', $userId)
                ->orWhere('brief_approval_histories.reviewed_by', $userId)
                ->groupEnd();
        }

        if (!empty($filters['date_start'])) {
            $builder->where('brief_approval_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('brief_approval_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('brief_approval_histories.note', $search)
                ->orLike('brief_approval_histories.notes', $search)
                ->orLike('brief_approval_histories.status', $search)
                ->orLike('briefs.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('u1.name', $search)
                ->orLike('u2.name', $search)
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
     * Get single brief approval history with full joined details
     */
    public function getHistoryWithDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                brief_approval_histories.*,
                COALESCE(brief_approval_histories.note, brief_approval_histories.notes, "") as resolved_note,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name,
                COALESCE(u1.email, u2.email, "") as reviewer_email,
                briefs.title as briefs_title,
                briefs.title as brief_title,
                briefs.content as brief_content,
                briefs.guidelines as brief_guidelines,
                briefs.dos as brief_dos,
                briefs.donts as brief_donts,
                briefs.reference_url as brief_reference_url,
                briefs.status as brief_status,
                briefs.approval_status as brief_approval_status,
                briefs.revision_count,
                tasks.id as task_id,
                tasks.task_number,
                tasks.title as task_title,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                kols.id as kol_id,
                kols.name as kol_name
            ')
            ->join('briefs', 'briefs.id = brief_approval_histories.brief_id', 'inner')
            ->join('tasks', 'tasks.id = briefs.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u1', 'u1.id = brief_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = brief_approval_histories.acted_by', 'left')
            ->where('brief_approval_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all histories for a brief
     */
    public function getHistoriesByBrief(int $briefId): array
    {
        return $this->db->table($this->table)
            ->select('
                brief_approval_histories.*,
                COALESCE(brief_approval_histories.note, brief_approval_histories.notes, "") as resolved_note,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name
            ')
            ->join('users u1', 'u1.id = brief_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = brief_approval_histories.acted_by', 'left')
            ->where('brief_approval_histories.brief_id', $briefId)
            ->orderBy('brief_approval_histories.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all records for export (Excel / CSV / PDF)
     */
    public function getAllForExport(array $filters = [], string $search = ''): array
    {
        $res = $this->getDatatables($filters, 0, 0, $search, 'brief_approval_histories.id', 'DESC');
        return $res['data'] ?? [];
    }

    /**
     * Delete a single history record
     */
    public function deleteHistory(int $id): bool
    {
        return (bool)$this->delete($id);
    }

    /**
     * Bulk delete history records
     */
    public function bulkDeleteHistories(array $ids): bool
    {
        $validIds = array_filter(array_map('intval', $ids));
        if (empty($validIds)) {
            return false;
        }

        return (bool)$this->whereIn('id', $validIds)->delete();
    }
}
