<?php

namespace App\Models;

use CodeIgniter\Model;

class BriefModel extends Model
{
    protected $table            = 'briefs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'title',
        'guidelines',
        'dos',
        'donts',
        'reference_url',
        'approval_status',
        'revision_notes',
        'submitted_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const STATUS_PENDING         = 'PENDING';
    public const STATUS_APPROVED        = 'APPROVED';
    public const STATUS_REVISION_NEEDED = 'REVISION_NEEDED';
    public const STATUS_REJECTED        = 'REJECTED';

    /**
     * DataTables query with joins to task, campaign, and kol
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'briefs.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                briefs.*,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                users.avatar as kol_avatar,
                submitter.name as submitter_name
            ')
            ->join('tasks', 'tasks.id = briefs.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users submitter', 'submitter.id = briefs.submitted_by', 'left');

        if (!empty($filters['approval_status'])) {
            $builder->where('briefs.approval_status', $filters['approval_status']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('briefs.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('briefs.guidelines', $search)
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
     * Get single brief with full details and history
     */
    public function getWithDetails(int $id): ?array
    {
        $brief = $this->db->table($this->table)
            ->select('
                briefs.*,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                tasks.deadline as task_deadline,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                users.avatar as kol_avatar,
                submitter.name as submitter_name
            ')
            ->join('tasks', 'tasks.id = briefs.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users submitter', 'submitter.id = briefs.submitted_by', 'left')
            ->where('briefs.id', $id)
            ->get()
            ->getRowArray();

        if (!$brief) {
            return null;
        }

        // Histori approval
        $brief['history'] = $this->db->table('brief_approval_histories')
            ->select('brief_approval_histories.*, users.name as reviewer_name')
            ->join('users', 'users.id = brief_approval_histories.reviewed_by', 'left')
            ->where('brief_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $brief;
    }

    /**
     * Approve brief and update task status
     */
    public function approveBrief(int $id, int $reviewerId, string $notes = ''): bool
    {
        $brief = $this->find($id);
        if (!$brief) {
            return false;
        }

        $this->db->transStart();

        // 1. Update brief
        $this->update($id, [
            'approval_status' => self::STATUS_APPROVED,
            'revision_notes'  => null,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        // 2. Insert approval history
        $this->db->table('brief_approval_histories')->insert([
            'brief_id'    => $id,
            'reviewed_by' => $reviewerId,
            'status'      => self::STATUS_APPROVED,
            'notes'       => $notes ?: 'Brief disetujui tanpa catatan revisi.',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        // 3. Update task status
        $this->db->table('tasks')->where('id', $brief['task_id'])->update([
            'status'     => TaskModel::STATUS_BRIEF_APPROVED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Request revision for brief and update task status
     */
    public function requestRevision(int $id, int $reviewerId, string $notes): bool
    {
        $brief = $this->find($id);
        if (!$brief) {
            return false;
        }

        $this->db->transStart();

        // 1. Update brief
        $this->update($id, [
            'approval_status' => self::STATUS_REVISION_NEEDED,
            'revision_notes'  => $notes,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        // 2. Insert approval history
        $this->db->table('brief_approval_histories')->insert([
            'brief_id'    => $id,
            'reviewed_by' => $reviewerId,
            'status'      => self::STATUS_REVISION_NEEDED,
            'notes'       => $notes,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        // 3. Update task status
        $this->db->table('tasks')->where('id', $brief['task_id'])->update([
            'status'     => TaskModel::STATUS_BRIEF_REVISION,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}
