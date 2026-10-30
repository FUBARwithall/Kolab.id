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
        'draft_url',
        'video_url',
        'caption',
        'hashtag',
        'notes',
        'created_by',
        'status',
        'review_notes',
        'timestamp_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Status Constants (CI3 & CI4 Parity)
    public const STATUS_DRAFT           = 'DRAFT';
    public const STATUS_SUBMITTED       = 'SUBMITTED';
    public const STATUS_PENDING         = 'PENDING';
    public const STATUS_APPROVED        = 'APPROVED';
    public const STATUS_REVISION        = 'REVISION';
    public const STATUS_REVISION_NEEDED = 'REVISION_NEEDED';

    /**
     * KPI Summary Metrics for Drafts Dashboard
     */
    public function getSummaryMetrics(): array
    {
        $db = $this->db;

        $totalDrafts = $db->table($this->table)->countAllResults();

        $submittedCount = $db->table($this->table)
            ->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_PENDING])
            ->countAllResults();

        $approvedCount = $db->table($this->table)
            ->where('status', self::STATUS_APPROVED)
            ->countAllResults();

        $revisionCount = $db->table($this->table)
            ->whereIn('status', [self::STATUS_REVISION, self::STATUS_REVISION_NEEDED])
            ->countAllResults();

        return [
            'total_drafts'    => $totalDrafts,
            'submitted_count' => $submittedCount,
            'approved_count'  => $approvedCount,
            'revision_count'  => $revisionCount,
        ];
    }

    /**
     * DataTables query with joins to task, campaign, kol, creator, and reviewer
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'drafts.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                drafts.*,
                COALESCE(drafts.draft_url, drafts.video_url) as live_draft_url,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                campaigns.id as campaign_id,
                COALESCE(campaigns.title, campaigns.name) as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                creator.name as creator_name,
                reviewer.name as reviewer_name
            ')
            ->join('tasks', 'tasks.id = drafts.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users creator', 'creator.id = drafts.created_by', 'left')
            ->join('users reviewer', 'reviewer.id = drafts.reviewed_by', 'left');

        if (!empty($filters['status'])) {
            $st = $filters['status'];
            if ($st === 'SUBMITTED' || $st === 'PENDING') {
                $builder->whereIn('drafts.status', [self::STATUS_SUBMITTED, self::STATUS_PENDING]);
            } elseif ($st === 'REVISION' || $st === 'REVISION_NEEDED') {
                $builder->whereIn('drafts.status', [self::STATUS_REVISION, self::STATUS_REVISION_NEEDED]);
            } else {
                $builder->where('drafts.status', $st);
            }
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kols.id', (int)$filters['kol_id']);
        }

        if (!empty($filters['task_id'])) {
            $builder->where('tasks.id', (int)$filters['task_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('tasks.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('kols.name', $search)
                ->orLike('drafts.draft_url', $search)
                ->orLike('drafts.video_url', $search)
                ->orLike('drafts.caption', $search)
                ->orLike('drafts.hashtag', $search)
                ->orLike('drafts.notes', $search)
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
                COALESCE(drafts.draft_url, drafts.video_url) as live_draft_url,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                tasks.deadline as task_deadline,
                campaigns.id as campaign_id,
                COALESCE(campaigns.title, campaigns.name) as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                creator.name as creator_name,
                reviewer.name as reviewer_name
            ')
            ->join('tasks', 'tasks.id = drafts.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users creator', 'creator.id = drafts.created_by', 'left')
            ->join('users reviewer', 'reviewer.id = drafts.reviewed_by', 'left')
            ->where('drafts.id', $id)
            ->get()
            ->getRowArray();

        if (!$draft) {
            return null;
        }

        // Fetch Review Histories (CI3 & CI4 Parity)
        $draft['history'] = $this->db->table('draft_review_histories')
            ->select('
                draft_review_histories.*,
                COALESCE(draft_review_histories.action, draft_review_histories.status) as action_name,
                COALESCE(draft_review_histories.note, draft_review_histories.notes) as review_note,
                users.name as reviewer_name
            ')
            ->join('users', 'users.id = COALESCE(draft_review_histories.acted_by, draft_review_histories.reviewed_by)', 'left')
            ->where('draft_id', $id)
            ->orderBy('draft_review_histories.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $draft;
    }

    /**
     * Submit a new draft video or revised version
     */
    public function submitDraft(int $taskId, string $draftUrl, string $notes = '', string $caption = '', string $hashtag = '', ?int $createdBy = null): int
    {
        $latest = $this->where('task_id', $taskId)->orderBy('version', 'DESC')->first();
        $nextVersion = $latest ? ((int)$latest['version'] + 1) : 1;

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        $draftId = $this->insert([
            'task_id'         => $taskId,
            'version'         => $nextVersion,
            'draft_url'       => $draftUrl,
            'video_url'       => $draftUrl,
            'caption'         => $caption ?: null,
            'hashtag'         => $hashtag ?: null,
            'notes'           => $notes ?: null,
            'created_by'      => $createdBy,
            'status'          => self::STATUS_SUBMITTED,
            'review_notes'    => $notes ?: null,
            'timestamp_notes' => null,
            'submitted_at'    => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ], true);

        // Update task status and draft_url
        $this->db->table('tasks')->where('id', $taskId)->update([
            'status'     => 'IN_REVIEW_DRAFT',
            'draft_url'  => $draftUrl,
            'updated_at' => $now,
        ]);

        // Insert into draft_review_histories
        $this->db->table('draft_review_histories')->insert([
            'draft_id'        => $draftId,
            'action'          => 'SUBMITTED',
            'status'          => 'PENDING',
            'note'            => 'Draft v' . $nextVersion . ' diajukan untuk review',
            'notes'           => 'Draft v' . $nextVersion . ' diajukan untuk review',
            'acted_by'        => $createdBy,
            'reviewed_by'     => $createdBy ?: 1,
            'timestamp_notes' => null,
            'created_at'      => $now,
        ]);

        // Insert into task_status_histories
        $task = $this->db->table('tasks')->where('id', $taskId)->get()->getRowArray();
        $oldStatus = $task['status'] ?? 'DRAFT';

        $this->db->table('task_status_histories')->insert([
            'task_id'     => $taskId,
            'from_status' => $oldStatus,
            'to_status'   => 'IN_REVIEW_DRAFT',
            'status'      => 'IN_REVIEW_DRAFT',
            'changed_by'  => $createdBy ?: 1,
            'notes'       => 'Draft v' . $nextVersion . ' disubmit untuk review.',
            'note'        => 'Draft v' . $nextVersion . ' disubmit untuk review.',
            'created_at'  => $now,
        ]);

        $this->db->transComplete();
        return (int)$draftId;
    }

    /**
     * Submit an existing draft for review (e.g. from DRAFT or REVISION status)
     */
    public function submitForReview(int $id, int $userId): bool
    {
        $draft = $this->find($id);
        if (!$draft) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        $this->update($id, [
            'status'       => self::STATUS_SUBMITTED,
            'submitted_at' => $now,
            'updated_at'   => $now,
        ]);

        // Insert into draft_review_histories
        $this->db->table('draft_review_histories')->insert([
            'draft_id'        => $id,
            'action'          => 'SUBMITTED',
            'status'          => 'PENDING',
            'note'            => 'Draft v' . $draft['version'] . ' disubmit untuk review',
            'notes'           => 'Draft v' . $draft['version'] . ' disubmit untuk review',
            'acted_by'        => $userId,
            'reviewed_by'     => $userId,
            'timestamp_notes' => null,
            'created_at'      => $now,
        ]);

        if (!empty($draft['task_id'])) {
            $task = $this->db->table('tasks')->where('id', $draft['task_id'])->get()->getRowArray();
            $oldStatus = $task['status'] ?? 'DRAFT';

            $this->db->table('tasks')->where('id', $draft['task_id'])->update([
                'status'     => 'IN_REVIEW_DRAFT',
                'draft_url'  => $draft['draft_url'] ?: $draft['video_url'],
                'updated_at' => $now,
            ]);

            $this->db->table('task_status_histories')->insert([
                'task_id'     => $draft['task_id'],
                'from_status' => $oldStatus,
                'to_status'   => 'IN_REVIEW_DRAFT',
                'status'      => 'IN_REVIEW_DRAFT',
                'changed_by'  => $userId,
                'notes'       => 'Draft v' . $draft['version'] . ' disubmit untuk review.',
                'note'        => 'Draft v' . $draft['version'] . ' disubmit untuk review.',
                'created_at'  => $now,
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Approve draft video (Leader/Admin Action)
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
            'action'          => 'APPROVED',
            'status'          => self::STATUS_APPROVED,
            'note'            => $notes ?: 'Draft disetujui Leader',
            'notes'           => $notes ?: 'Draft disetujui Leader',
            'acted_by'        => $reviewerId,
            'reviewed_by'     => $reviewerId,
            'timestamp_notes' => $timestampNotes,
            'created_at'      => $now,
        ]);

        // 3. Update task status
        if (!empty($draft['task_id'])) {
            $task = $this->db->table('tasks')->where('id', $draft['task_id'])->get()->getRowArray();
            $oldStatus = $task['status'] ?? 'IN_REVIEW_DRAFT';

            $this->db->table('tasks')->where('id', $draft['task_id'])->update([
                'status'     => 'APPROVED_DRAFT',
                'updated_at' => $now,
            ]);

            $this->db->table('task_status_histories')->insert([
                'task_id'     => $draft['task_id'],
                'from_status' => $oldStatus,
                'to_status'   => 'APPROVED_DRAFT',
                'status'      => 'APPROVED_DRAFT',
                'changed_by'  => $reviewerId,
                'notes'       => 'Draft disetujui Leader. Siap untuk Live Posting.',
                'note'        => 'Draft disetujui Leader. Siap untuk Live Posting.',
                'created_at'  => $now,
            ]);
        }

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
            'status'          => self::STATUS_REVISION,
            'notes'           => $notes,
            'review_notes'    => $notes,
            'timestamp_notes' => $timestampNotes,
            'reviewed_at'     => $now,
            'reviewed_by'     => $reviewerId,
            'updated_at'      => $now,
        ]);

        // 2. Insert review history
        $this->db->table('draft_review_histories')->insert([
            'draft_id'        => $id,
            'action'          => 'REVISION',
            'status'          => self::STATUS_REVISION_NEEDED,
            'note'            => $notes,
            'notes'           => $notes,
            'acted_by'        => $reviewerId,
            'reviewed_by'     => $reviewerId,
            'timestamp_notes' => $timestampNotes,
            'created_at'      => $now,
        ]);

        // 3. Update task status
        if (!empty($draft['task_id'])) {
            $task = $this->db->table('tasks')->where('id', $draft['task_id'])->get()->getRowArray();
            $oldStatus = $task['status'] ?? 'IN_REVIEW_DRAFT';

            $this->db->table('tasks')->where('id', $draft['task_id'])->update([
                'status'     => 'REVISION_DRAFT',
                'draft_note' => $notes,
                'updated_at' => $now,
            ]);

            $this->db->table('task_status_histories')->insert([
                'task_id'     => $draft['task_id'],
                'from_status' => $oldStatus,
                'to_status'   => 'REVISION_DRAFT',
                'status'      => 'REVISION_DRAFT',
                'changed_by'  => $reviewerId,
                'notes'       => 'Draft dikembalikan untuk revisi: ' . $notes,
                'note'        => 'Draft dikembalikan untuk revisi: ' . $notes,
                'created_at'  => $now,
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Save draft from direct form submission (Add/Edit)
     */
    public function saveDraft(array $data): int
    {
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        unset($data['id']);

        if (!empty($data['draft_url']) && empty($data['video_url'])) {
            $data['video_url'] = $data['draft_url'];
        } elseif (!empty($data['video_url']) && empty($data['draft_url'])) {
            $data['draft_url'] = $data['video_url'];
        }

        $now = date('Y-m-d H:i:s');

        if ($id) {
            $data['updated_at'] = $now;
            $this->update($id, $data);
            return $id;
        }

        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        if (empty($data['submitted_at'])) {
            $data['submitted_at'] = $now;
        }

        return (int)$this->insert($data, true);
    }

    /**
     * Get all drafts for CSV export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                drafts.id,
                tasks.task_number,
                tasks.title as task_title,
                COALESCE(campaigns.title, campaigns.name) as campaign_name,
                kols.name as kol_name,
                drafts.version,
                COALESCE(drafts.draft_url, drafts.video_url) as draft_url,
                drafts.caption,
                drafts.hashtag,
                COALESCE(drafts.notes, drafts.review_notes) as notes,
                drafts.status,
                creator.name as created_by_name,
                reviewer.name as reviewer_name,
                drafts.submitted_at,
                drafts.reviewed_at,
                drafts.created_at
            ')
            ->join('tasks', 'tasks.id = drafts.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users creator', 'creator.id = drafts.created_by', 'left')
            ->join('users reviewer', 'reviewer.id = drafts.reviewed_by', 'left')
            ->orderBy('drafts.id', 'DESC');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('drafts.status', $filters['status']);
        }

        return $builder->get()->getResultArray();
    }
}
