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
        'content',
        'file_path',
        'revision_count',
        'status',
        'submitted_at',
        'created_by',
        'guidelines',
        'dos',
        'donts',
        'reference_url',
        'approval_status',
        'revision_notes',
        'submitted_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Canonical CI4 Statuses
    public const STATUS_PENDING         = 'PENDING';
    public const STATUS_APPROVED        = 'APPROVED';
    public const STATUS_REVISION_NEEDED = 'REVISION_NEEDED';
    public const STATUS_REJECTED        = 'REJECTED';

    // CI3 Status Equivalents
    public const STATUS_CI3_DRAFT          = 'DRAFT';
    public const STATUS_CI3_PENDING_REVIEW = 'PENDING_REVIEW';
    public const STATUS_CI3_APPROVED       = 'APPROVED';
    public const STATUS_CI3_REVISION       = 'REVISION';
    public const STATUS_CI3_REJECTED       = 'REJECTED';

    /**
     * Get KPI Summary Metrics
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->join('tasks', 'tasks.id = briefs.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        $allBriefs = $builder->select('briefs.status, briefs.approval_status')->get()->getResultArray();

        $metrics = [
            'total'    => count($allBriefs),
            'pending'  => 0,
            'approved' => 0,
            'revision' => 0,
            'rejected' => 0,
        ];

        foreach ($allBriefs as $row) {
            $st = strtoupper((string)($row['status'] ?: ''));
            $appSt = strtoupper((string)($row['approval_status'] ?: ''));

            if ($st === 'APPROVED' || $appSt === 'APPROVED') {
                $metrics['approved']++;
            } elseif ($st === 'REVISION' || $st === 'REVISED' || $appSt === 'REVISION_NEEDED') {
                $metrics['revision']++;
            } elseif ($st === 'REJECTED' || $appSt === 'REJECTED') {
                $metrics['rejected']++;
            } else {
                $metrics['pending']++;
            }
        }

        return $metrics;
    }

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
                COALESCE(submitter.name, author.name, "Admin") as submitter_name
            ')
            ->join('tasks', 'tasks.id = briefs.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users submitter', 'submitter.id = briefs.submitted_by', 'left')
            ->join('users author', 'author.id = briefs.created_by', 'left');

        if (!empty($filters['approval_status'])) {
            $statusVal = $filters['approval_status'];
            if ($statusVal === 'PENDING') {
                $builder->groupStart()
                    ->where('briefs.approval_status', 'PENDING')
                    ->orWhereIn('briefs.status', ['DRAFT', 'PENDING_REVIEW', 'REVIEW_PENDING'])
                    ->groupEnd();
            } elseif ($statusVal === 'APPROVED') {
                $builder->groupStart()
                    ->where('briefs.approval_status', 'APPROVED')
                    ->orWhere('briefs.status', 'APPROVED')
                    ->groupEnd();
            } elseif ($statusVal === 'REVISION_NEEDED' || $statusVal === 'REVISION') {
                $builder->groupStart()
                    ->where('briefs.approval_status', 'REVISION_NEEDED')
                    ->orWhereIn('briefs.status', ['REVISION', 'REVISED'])
                    ->groupEnd();
            } elseif ($statusVal === 'REJECTED') {
                $builder->groupStart()
                    ->where('briefs.approval_status', 'REJECTED')
                    ->orWhere('briefs.status', 'REJECTED')
                    ->groupEnd();
            } else {
                $builder->where('briefs.approval_status', $statusVal);
            }
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('briefs.title', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('briefs.content', $search)
                ->orLike('briefs.guidelines', $search)
                ->orLike('tasks.title', $search)
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
                COALESCE(submitter.name, author.name, "Admin") as submitter_name
            ')
            ->join('tasks', 'tasks.id = briefs.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users submitter', 'submitter.id = briefs.submitted_by', 'left')
            ->join('users author', 'author.id = briefs.created_by', 'left')
            ->where('briefs.id', $id)
            ->get()
            ->getRowArray();

        if (!$brief) {
            return null;
        }

        // Histori approval
        $brief['history'] = $this->db->table('brief_approval_histories')
            ->select('
                brief_approval_histories.*,
                COALESCE(u1.name, u2.name, "Reviewer") as reviewer_name,
                COALESCE(brief_approval_histories.notes, brief_approval_histories.note, "") as full_note
            ')
            ->join('users u1', 'u1.id = brief_approval_histories.reviewed_by', 'left')
            ->join('users u2', 'u2.id = brief_approval_histories.acted_by', 'left')
            ->where('brief_id', $id)
            ->orderBy('brief_approval_histories.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Histori konten versi
        $brief['versions'] = $this->db->table('brief_content_histories')
            ->select('
                brief_content_histories.*,
                COALESCE(u.name, "Editor") as author_name
            ')
            ->join('users u', 'u.id = brief_content_histories.created_by', 'left')
            ->where('brief_id', $id)
            ->orderBy('brief_content_histories.version', 'DESC')
            ->get()
            ->getResultArray();

        return $brief;
    }

    /**
     * Staff Action: Submit brief for Leader review
     */
    public function submitReview(int $id, int $userId): bool
    {
        $brief = $this->find($id);
        if (!$brief) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->transStart();

        $this->update($id, [
            'status'          => self::STATUS_CI3_PENDING_REVIEW,
            'approval_status' => self::STATUS_PENDING,
            'submitted_at'    => $now,
            'submitted_by'    => $userId,
            'updated_at'      => $now,
        ]);

        if (!empty($brief['task_id'])) {
            $this->db->table('tasks')->where('id', $brief['task_id'])->update([
                'status'     => TaskModel::STATUS_BRIEF_SUBMITTED,
                'updated_at' => $now,
            ]);

            $this->db->table('task_status_histories')->insert([
                'task_id'    => $brief['task_id'],
                'status'     => TaskModel::STATUS_BRIEF_SUBMITTED,
                'to_status'  => TaskModel::STATUS_BRIEF_SUBMITTED,
                'changed_by' => $userId,
                'note'       => 'Brief diajukan untuk review Leader.',
                'notes'      => 'Brief diajukan untuk review Leader.',
                'created_at' => $now,
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Leader Action: Approve brief and advance task to WAITING_DRAFT
     */
    public function approveBrief(int $id, int $reviewerId, string $notes = ''): bool
    {
        $brief = $this->find($id);
        if (!$brief) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $resolvedNotes = $notes ?: 'Brief approved by Leader';

        $this->db->transStart();

        // 1. Update brief status
        $this->update($id, [
            'status'          => self::STATUS_CI3_APPROVED,
            'approval_status' => self::STATUS_APPROVED,
            'revision_notes'  => null,
            'updated_at'      => $now,
        ]);

        // 2. Insert approval history
        $this->db->table('brief_approval_histories')->insert([
            'brief_id'    => $id,
            'reviewed_by' => $reviewerId,
            'acted_by'    => $reviewerId,
            'status'      => 'APPROVED',
            'notes'       => $resolvedNotes,
            'note'        => $resolvedNotes,
            'created_at'  => $now,
        ]);

        // 3. Update task status to WAITING_DRAFT
        if (!empty($brief['task_id'])) {
            $this->db->table('tasks')->where('id', $brief['task_id'])->update([
                'status'     => TaskModel::STATUS_WAITING_DRAFT,
                'updated_at' => $now,
            ]);

            $this->db->table('task_status_histories')->insert([
                'task_id'    => $brief['task_id'],
                'status'     => TaskModel::STATUS_WAITING_DRAFT,
                'to_status'  => TaskModel::STATUS_WAITING_DRAFT,
                'changed_by' => $reviewerId,
                'note'       => 'Brief disetujui Leader. Task maju ke tahap WAITING_DRAFT.',
                'notes'      => 'Brief disetujui Leader. Task maju ke tahap WAITING_DRAFT.',
                'created_at' => $now,
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Leader Action: Request brief revision -> snapshots current brief content
     */
    public function requestRevision(int $id, int $reviewerId, string $notes): bool
    {
        $brief = $this->find($id);
        if (!$brief) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $resolvedNotes = $notes ?: 'Revision needed';

        $this->db->transStart();

        // 1. Snapshot current content before revision
        $lastVersion = $this->db->table('brief_content_histories')
            ->selectMax('version')
            ->where('brief_id', $id)
            ->get()
            ->getRow();
        $nextVersion = ($lastVersion && $lastVersion->version) ? ((int)$lastVersion->version + 1) : 1;

        $snapshotContent = !empty($brief['content']) ? $brief['content'] : ($brief['guidelines'] ?? '');
        $this->db->table('brief_content_histories')->insert([
            'brief_id'     => $id,
            'version'      => $nextVersion,
            'title'        => $brief['title'] ?? '',
            'content'      => $snapshotContent,
            'content_diff' => $resolvedNotes,
            'snapshot_at'  => $now,
            'created_by'   => $reviewerId,
            'updated_by'   => $reviewerId,
            'created_at'   => $now,
        ]);

        // 2. Update brief status and increment revision_count
        $currentRevCount = (int)($brief['revision_count'] ?? 0);
        $this->update($id, [
            'status'          => self::STATUS_CI3_REVISION,
            'approval_status' => self::STATUS_REVISION_NEEDED,
            'revision_count'  => $currentRevCount + 1,
            'revision_notes'  => $resolvedNotes,
            'updated_at'      => $now,
        ]);

        // 3. Update task status to REVISION
        if (!empty($brief['task_id'])) {
            $this->db->table('tasks')->where('id', $brief['task_id'])->update([
                'status'     => TaskModel::STATUS_REVISION,
                'brief_note' => $resolvedNotes,
                'updated_at' => $now,
            ]);

            $this->db->table('task_status_histories')->insert([
                'task_id'    => $brief['task_id'],
                'status'     => TaskModel::STATUS_REVISION,
                'to_status'  => TaskModel::STATUS_REVISION,
                'changed_by' => $reviewerId,
                'note'       => 'Brief dikembalikan untuk revisi: ' . $resolvedNotes,
                'notes'      => 'Brief dikembalikan untuk revisi: ' . $resolvedNotes,
                'created_at' => $now,
            ]);
        }

        // 4. Log to brief_approval_histories
        $this->db->table('brief_approval_histories')->insert([
            'brief_id'    => $id,
            'reviewed_by' => $reviewerId,
            'acted_by'    => $reviewerId,
            'status'      => 'REVISION',
            'notes'       => $resolvedNotes,
            'note'        => $resolvedNotes,
            'created_at'  => $now,
        ]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Export all briefs joined with task, campaign, and KOL
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                briefs.*,
                tasks.task_number,
                tasks.title as task_title,
                tasks.status as task_status,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                COALESCE(submitter.name, author.name, "Admin") as submitter_name
            ')
            ->join('tasks', 'tasks.id = briefs.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users submitter', 'submitter.id = briefs.submitted_by', 'left')
            ->join('users author', 'author.id = briefs.created_by', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $builder->groupStart()
                ->where('briefs.status', $filters['status'])
                ->orWhere('briefs.approval_status', $filters['status'])
                ->groupEnd();
        }

        return $builder->orderBy('briefs.id', 'DESC')->get()->getResultArray();
    }
}
