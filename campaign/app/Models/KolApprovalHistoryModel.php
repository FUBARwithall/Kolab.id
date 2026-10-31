<?php

namespace App\Models;

use CodeIgniter\Model;

class KolApprovalHistoryModel extends Model
{
    protected $table            = 'kol_approval_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'action',
        'status',
        'note',
        'notes',
        'acted_by',
        'reviewed_by',
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
                COUNT(kol_approval_histories.id) as total_logs,
                COUNT(DISTINCT kol_approval_histories.kol_id) as total_kols,
                COUNT(DISTINCT COALESCE(kol_approval_histories.acted_by, kol_approval_histories.reviewed_by)) as total_reviewers,
                SUM(CASE WHEN UPPER(COALESCE(kol_approval_histories.action, kol_approval_histories.status)) = "APPROVED" THEN 1 ELSE 0 END) as total_approved,
                SUM(CASE WHEN UPPER(COALESCE(kol_approval_histories.action, kol_approval_histories.status)) IN ("SUBMITTED", "PENDING") THEN 1 ELSE 0 END) as total_submitted,
                SUM(CASE WHEN UPPER(COALESCE(kol_approval_histories.action, kol_approval_histories.status)) = "REJECTED" THEN 1 ELSE 0 END) as total_rejected
            ')
            ->join('kols', 'kols.id = kol_approval_histories.kol_id', 'left');

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_approval_histories.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['action'])) {
            $action = strtoupper(trim((string)$filters['action']));
            $builder->groupStart()
                ->where('kol_approval_histories.action', $action)
                ->orWhere('kol_approval_histories.status', $action)
                ->groupEnd();
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_logs'       => (int)($row['total_logs'] ?? 0),
            'total_kols'       => (int)($row['total_kols'] ?? 0),
            'total_reviewers'  => (int)($row['total_reviewers'] ?? 0),
            'total_approved'   => (int)($row['total_approved'] ?? 0),
            'total_submitted'  => (int)($row['total_submitted'] ?? 0),
            'total_rejected'   => (int)($row['total_rejected'] ?? 0),
        ];
    }

    /**
     * DataTables query with joins to kols and users
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'kol_approval_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_approval_histories.*,
                COALESCE(kol_approval_histories.note, kol_approval_histories.notes, "") as resolved_note,
                UPPER(COALESCE(kol_approval_histories.action, kol_approval_histories.status, "SUBMITTED")) as resolved_action,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name,
                COALESCE(u1.email, u2.email, "") as reviewer_email,
                kols.name as kols_name,
                kols.name as kol_name,
                kols.city as kol_city,
                kols.whatsapp as kol_whatsapp,
                kols.email as kol_email,
                kols.approval_status as kol_current_status,
                kols.bank_name,
                kols.bank_account
            ')
            ->join('kols', 'kols.id = kol_approval_histories.kol_id', 'inner')
            ->join('users u1', 'u1.id = kol_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = kol_approval_histories.acted_by', 'left');

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_approval_histories.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['action'])) {
            $action = strtoupper(trim((string)$filters['action']));
            $builder->groupStart()
                ->where('kol_approval_histories.action', $action)
                ->orWhere('kol_approval_histories.status', $action)
                ->groupEnd();
        }

        if (!empty($filters['acted_by'])) {
            $userId = (int)$filters['acted_by'];
            $builder->groupStart()
                ->where('kol_approval_histories.acted_by', $userId)
                ->orWhere('kol_approval_histories.reviewed_by', $userId)
                ->groupEnd();
        }

        if (!empty($filters['date_start'])) {
            $builder->where('kol_approval_histories.created_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('kol_approval_histories.created_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kol_approval_histories.note', $search)
                ->orLike('kol_approval_histories.notes', $search)
                ->orLike('kol_approval_histories.action', $search)
                ->orLike('kol_approval_histories.status', $search)
                ->orLike('kols.name', $search)
                ->orLike('kols.city', $search)
                ->orLike('kols.whatsapp', $search)
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
     * Get single kol approval history with full joined details
     */
    public function getHistoryWithDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                kol_approval_histories.*,
                COALESCE(kol_approval_histories.note, kol_approval_histories.notes, "") as resolved_note,
                UPPER(COALESCE(kol_approval_histories.action, kol_approval_histories.status, "SUBMITTED")) as resolved_action,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name,
                COALESCE(u1.email, u2.email, "") as reviewer_email,
                kols.name as kols_name,
                kols.name as kol_name,
                kols.nik as kol_nik,
                kols.city as kol_city,
                kols.address as kol_address,
                kols.whatsapp as kol_whatsapp,
                kols.phone as kol_phone,
                kols.email as kol_email,
                kols.approval_status as kol_current_status,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.npwp,
                kols.is_blacklisted,
                kols.created_at as kol_registered_at
            ')
            ->join('kols', 'kols.id = kol_approval_histories.kol_id', 'inner')
            ->join('users u1', 'u1.id = kol_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = kol_approval_histories.acted_by', 'left')
            ->where('kol_approval_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all histories for a specific KOL
     */
    public function getHistoriesByKol(int $kolId): array
    {
        return $this->db->table($this->table)
            ->select('
                kol_approval_histories.*,
                COALESCE(kol_approval_histories.note, kol_approval_histories.notes, "") as resolved_note,
                UPPER(COALESCE(kol_approval_histories.action, kol_approval_histories.status, "SUBMITTED")) as resolved_action,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(u1.name, u2.name, "Reviewer") as aauth_users_full_name
            ')
            ->join('users u1', 'u1.id = kol_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = kol_approval_histories.acted_by', 'left')
            ->where('kol_approval_histories.kol_id', $kolId)
            ->orderBy('kol_approval_histories.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all records for export (Excel / CSV / PDF)
     */
    public function getAllForExport(array $filters = [], string $search = ''): array
    {
        $res = $this->getDatatables($filters, 0, 0, $search, 'kol_approval_histories.id', 'DESC');
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

    /**
     * Helper to record approval history
     */
    public function recordHistory(int $kolId, string $action, string $note = '', ?int $userId = null): int
    {
        $now = date('Y-m-d H:i:s');
        $this->insert([
            'kol_id'      => $kolId,
            'action'      => $action,
            'status'      => $action,
            'note'        => $note,
            'notes'       => $note,
            'acted_by'    => $userId,
            'reviewed_by' => $userId,
            'created_at'  => $now,
        ]);

        return (int)$this->getInsertID();
    }
}
