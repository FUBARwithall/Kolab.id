<?php

namespace App\Models;

use CodeIgniter\Model;

class DraftReviewHistoryModel extends Model
{
    protected $table            = 'draft_review_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'draft_id',
        'action',
        'status',
        'note',
        'notes',
        'acted_by',
        'reviewed_by',
        'timestamp_notes',
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
                COUNT(draft_review_histories.id) as total_logs,
                COUNT(DISTINCT draft_review_histories.draft_id) as total_drafts,
                COUNT(DISTINCT COALESCE(draft_review_histories.acted_by, draft_review_histories.reviewed_by)) as total_reviewers,
                SUM(CASE WHEN UPPER(COALESCE(draft_review_histories.action, draft_review_histories.status)) = "APPROVED" THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN UPPER(COALESCE(draft_review_histories.action, draft_review_histories.status)) IN ("REVISION", "REVISION_NEEDED") THEN 1 ELSE 0 END) as total_revision,
                SUM(CASE WHEN UPPER(COALESCE(draft_review_histories.action, draft_review_histories.status)) IN ("SUBMITTED", "PENDING") THEN 1 ELSE 0 END) as total_submitted
            ')
            ->join('drafts', 'drafts.id = draft_review_histories.draft_id', 'left')
            ->join('tasks', 'tasks.id = drafts.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left');

        if (!empty($filters['draft_id'])) {
            $builder->where('draft_review_histories.draft_id', (int)$filters['draft_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['action'])) {
            $act = strtoupper(trim((string)$filters['action']));
            if (in_array($act, ['REVISION', 'REVISION_NEEDED'])) {
                $builder->groupStart()
                    ->whereIn('draft_review_histories.action', ['REVISION', 'REVISION_NEEDED'])
                    ->orWhereIn('draft_review_histories.status', ['REVISION', 'REVISION_NEEDED'])
                    ->groupEnd();
            } else {
                $builder->groupStart()
                    ->where('draft_review_histories.action', $act)
                    ->orWhere('draft_review_histories.status', $act)
                    ->groupEnd();
            }
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_logs'       => (int)($row['total_logs'] ?? 0),
            'total_drafts'     => (int)($row['total_drafts'] ?? 0),
            'total_reviewers'  => (int)($row['total_reviewers'] ?? 0),
            'total_approved'   => (int)($row['total_approved'] ?? 0),
            'total_revision'   => (int)($row['total_revision'] ?? 0),
            'total_submitted'  => (int)($row['total_submitted'] ?? 0),
        ];
    }

    /**
     * DataTables query with joins to drafts, tasks, campaign_kols, campaigns, kols, and users
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'draft_review_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                draft_review_histories.*,
                COALESCE(draft_review_histories.note, draft_review_histories.notes, "") as resolved_note,
                UPPER(COALESCE(draft_review_histories.action, draft_review_histories.status, "SUBMITTED")) as resolved_action,
                drafts.id as drafts_id,
                drafts.version as draft_version,
                COALESCE(drafts.video_url, drafts.draft_url, "") as video_url,
                drafts.caption as draft_caption,
                drafts.status as draft_current_status,
                tasks.id as task_id,
                tasks.title as task_title,
                tasks.task_number,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name,
                COALESCE(u1.email, u2.email, "") as reviewer_email
            ')
            ->join('drafts', 'drafts.id = draft_review_histories.draft_id', 'inner')
            ->join('tasks', 'tasks.id = drafts.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u1', 'u1.id = draft_review_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = draft_review_histories.acted_by', 'left');

        if (!empty($filters['draft_id'])) {
            $builder->where('draft_review_histories.draft_id', (int)$filters['draft_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['action'])) {
            $act = strtoupper(trim((string)$filters['action']));
            if (in_array($act, ['REVISION', 'REVISION_NEEDED'])) {
                $builder->groupStart()
                    ->whereIn('draft_review_histories.action', ['REVISION', 'REVISION_NEEDED'])
                    ->orWhereIn('draft_review_histories.status', ['REVISION', 'REVISION_NEEDED'])
                    ->groupEnd();
            } else {
                $builder->groupStart()
                    ->where('draft_review_histories.action', $act)
                    ->orWhere('draft_review_histories.status', $act)
                    ->groupEnd();
            }
        }

        if (!empty($filters['acted_by'])) {
            $userId = (int)$filters['acted_by'];
            $builder->groupStart()
                ->where('draft_review_histories.acted_by', $userId)
                ->orWhere('draft_review_histories.reviewed_by', $userId)
                ->groupEnd();
        }

        if (!empty($filters['date_start'])) {
            $builder->where('draft_review_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('draft_review_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('draft_review_histories.note', $search)
                ->orLike('draft_review_histories.notes', $search)
                ->orLike('draft_review_histories.action', $search)
                ->orLike('draft_review_histories.status', $search)
                ->orLike('drafts.caption', $search)
                ->orLike('drafts.video_url', $search)
                ->orLike('tasks.title', $search)
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
     * Get single draft review history with full joined details
     */
    public function getHistoryWithDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                draft_review_histories.*,
                COALESCE(draft_review_histories.note, draft_review_histories.notes, "") as resolved_note,
                UPPER(COALESCE(draft_review_histories.action, draft_review_histories.status, "SUBMITTED")) as resolved_action,
                drafts.id as drafts_id,
                drafts.version as draft_version,
                COALESCE(drafts.video_url, drafts.draft_url, "") as video_url,
                drafts.caption as draft_caption,
                drafts.hashtag as draft_hashtag,
                drafts.status as draft_current_status,
                drafts.submitted_at as draft_submitted_at,
                tasks.id as task_id,
                tasks.title as task_title,
                tasks.task_number,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                kols.id as kol_id,
                kols.name as kol_name,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name,
                COALESCE(u1.email, u2.email, "") as reviewer_email
            ')
            ->join('drafts', 'drafts.id = draft_review_histories.draft_id', 'inner')
            ->join('tasks', 'tasks.id = drafts.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users u1', 'u1.id = draft_review_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = draft_review_histories.acted_by', 'left')
            ->where('draft_review_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all review histories for a specific draft
     */
    public function getHistoriesByDraft(int $draftId): array
    {
        return $this->db->table($this->table)
            ->select('
                draft_review_histories.*,
                COALESCE(draft_review_histories.note, draft_review_histories.notes, "") as resolved_note,
                UPPER(COALESCE(draft_review_histories.action, draft_review_histories.status, "SUBMITTED")) as resolved_action,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name
            ')
            ->join('users u1', 'u1.id = draft_review_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = draft_review_histories.acted_by', 'left')
            ->where('draft_review_histories.draft_id', $draftId)
            ->orderBy('draft_review_histories.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all records for export (Excel / CSV / PDF)
     */
    public function getAllForExport(array $filters = [], string $search = ''): array
    {
        $res = $this->getDatatables($filters, 0, 0, $search, 'draft_review_histories.id', 'DESC');
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
