<?php

namespace App\Models;

use CodeIgniter\Model;

class DraftModel extends Model
{
    protected $table            = 'drafts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'version',
        'video_url',
        'status',
        'review_notes',
        'timestamp_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const STATUS_PENDING         = 'PENDING';
    public const STATUS_APPROVED        = 'APPROVED';
    public const STATUS_REVISION_NEEDED = 'REVISION_NEEDED';

    /**
     * DataTables query with joins to task, campaign, and kol
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'drafts.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                drafts.*,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                users.avatar as kol_avatar,
                reviewer.name as reviewer_name
            ')
            ->join('tasks', 'tasks.id = drafts.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users reviewer', 'reviewer.id = drafts.reviewed_by', 'left');

        if (!empty($filters['status'])) {
            $builder->where('drafts.status', $filters['status']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kols.id', (int)$filters['kol_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('tasks.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('drafts.video_url', $search)
                ->orLike('drafts.review_notes', $search)
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
     * Get single draft with review history
     */
    public function getWithDetails(int $id): ?array
    {
        $draft = $this->db->table($this->table)
            ->select('
                drafts.*,
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
                reviewer.name as reviewer_name
            ')
            ->join('tasks', 'tasks.id = drafts.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users reviewer', 'reviewer.id = drafts.reviewed_by', 'left')
            ->where('drafts.id', $id)
            ->get()
            ->getRowArray();

        if (!$draft) {
            return null;
        }

        // Histori review
        $draft['history'] = $this->db->table('draft_review_histories')
            ->select('draft_review_histories.*, users.name as reviewer_name')
            ->join('users', 'users.id = draft_review_histories.reviewed_by', 'left')
            ->where('draft_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $draft;
    }

    /**
     * Submit a new draft video or revised version
     */
    public function submitDraft(int $taskId, string $videoUrl, string $notes = ''): int
    {
        // Cari versi tertinggi
        $latest = $this->where('task_id', $taskId)->orderBy('version', 'DESC')->first();
        $nextVersion = $latest ? ((int)$latest['version'] + 1) : 1;

        $this->db->transStart();

        $draftId = $this->insert([
            'task_id'         => $taskId,
            'version'         => $nextVersion,
            'video_url'       => $videoUrl,
            'status'          => self::STATUS_PENDING,
            'review_notes'    => $notes ?: null,
            'timestamp_notes' => null,
            'submitted_at'    => date('Y-m-d H:i:s'),
        ], true);

        // Update task status
        $this->db->table('tasks')->where('id', $taskId)->update([
            'status'     => TaskModel::STATUS_DRAFT_SUBMITTED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
        return $draftId;
    }

    /**
     * Approve draft video
     */
    public function approveDraft(int $id, int $reviewerId, string $notes = '', ?string $timestampNotes = null): bool
    {
        $draft = $this->find($id);
        if (!$draft) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        // 1. Update draft
        $this->update($id, [
            'status'          => self::STATUS_APPROVED,
            'review_notes'    => $notes ?: 'Draft disetujui (Approved). Siap tayang/schedule.',
            'timestamp_notes' => $timestampNotes,
            'reviewed_at'     => $now,
            'reviewed_by'     => $reviewerId,
            'updated_at'      => $now,
        ]);

        // 2. Insert review history
        $this->db->table('draft_review_histories')->insert([
            'draft_id'        => $id,
            'reviewed_by'     => $reviewerId,
            'status'          => self::STATUS_APPROVED,
            'timestamp_notes' => $timestampNotes,
            'notes'           => $notes ?: 'Approved',
            'created_at'      => $now,
        ]);

        // 3. Update task status
        $this->db->table('tasks')->where('id', $draft['task_id'])->update([
            'status'     => TaskModel::STATUS_DRAFT_APPROVED,
            'updated_at' => $now,
        ]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Request revision for draft video
     */
    public function requestRevision(int $id, int $reviewerId, string $notes, ?string $timestampNotes = null): bool
    {
        $draft = $this->find($id);
        if (!$draft) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        // 1. Update draft
        $this->update($id, [
            'status'          => self::STATUS_REVISION_NEEDED,
            'review_notes'    => $notes,
            'timestamp_notes' => $timestampNotes,
            'reviewed_at'     => $now,
            'reviewed_by'     => $reviewerId,
            'updated_at'      => $now,
        ]);

        // 2. Insert review history
        $this->db->table('draft_review_histories')->insert([
            'draft_id'        => $id,
            'reviewed_by'     => $reviewerId,
            'status'          => self::STATUS_REVISION_NEEDED,
            'timestamp_notes' => $timestampNotes,
            'notes'           => $notes,
            'created_at'      => $now,
        ]);

        // 3. Update task status
        $this->db->table('tasks')->where('id', $draft['task_id'])->update([
            'status'     => TaskModel::STATUS_DRAFT_REVISION,
            'updated_at' => $now,
        ]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}
