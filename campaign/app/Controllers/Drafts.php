<?php

namespace App\Controllers;

use App\Models\DraftModel;
use App\Models\TaskModel;
use App\Models\CampaignModel;

class Drafts extends BaseController
{
    protected DraftModel $draftModel;
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->draftModel    = new DraftModel();
        $this->taskModel     = new TaskModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Review Draft Video KOL
     * Full Parity with CI3 modules/drafts
     */
    public function index()
    {
        $taskId = $this->request->getGet('task_id');
        $db = \Config\Database::connect();

        $campaigns = $db->table('campaigns')
            ->select('id, title, COALESCE(name, title) as name')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $tasks = $db->table('tasks')
            ->select('id, task_number, title')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'        => 'Review Draft Video KOL — Quality & Guideline Control',
            'controller'   => 'Drafts',
            'taskId'       => $taskId,
            'metrics'      => $this->draftModel->getSummaryMetrics(),
            'allCampaigns' => $campaigns,
            'allTasks'     => $tasks,
        ];

        return view('drafts/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'drafts.id',
            1 => 'drafts.id',
            2 => 'tasks.title',
            3 => 'campaigns.title',
            4 => 'kols.name',
            5 => 'drafts.version',
            6 => 'drafts.status',
            7 => 'drafts.submitted_at',
            8 => 'drafts.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'drafts.id';

        $filters = [
            'status'      => $this->request->getPost('status'),
            'campaign_id' => $this->request->getPost('campaign_id'),
            'kol_id'      => $this->request->getPost('kol_id'),
            'task_id'     => $this->request->getPost('task_id'),
        ];

        $result = $this->draftModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $status = strtoupper((string)$row['status']);

            $statusBadge = match ($status) {
                'APPROVED' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                'REVISION', 'REVISION_NEEDED' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-exclamation-circle me-1"></i>REVISI</span>',
                'SUBMITTED', 'PENDING' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>SUBMITTED</span>',
                default => '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="fas fa-file me-1"></i>DRAFT</span>',
            };

            $draftUrl = $row['live_draft_url'] ?: ($row['draft_url'] ?: $row['video_url']);
            $videoBtn = !empty($draftUrl)
                ? '<a href="' . esc($draftUrl) . '" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0" style="font-size: 11px;"><i class="fas fa-play text-danger me-1"></i>Tonton Video</a>'
                : '<span class="text-muted small">-</span>';

            $checkbox = '<input type="checkbox" class="form-check-input row-checkbox" value="' . $row['id'] . '">';

            $actionBtns = '<div class="btn-group btn-group-sm" role="group">';

            // Submit for review action if DRAFT or REVISION
            if (in_array($status, ['DRAFT', 'REVISION', 'REVISION_NEEDED'])) {
                $actionBtns .= '<button type="button" class="btn btn-outline-primary btn-submit-review" data-id="' . $row['id'] . '" title="Submit Draft untuk Review"><i class="fas fa-paper-plane"></i></button>';
            }

            // Approve / Request Revision actions if SUBMITTED or PENDING
            if (in_array($status, ['SUBMITTED', 'PENDING'])) {
                $actionBtns .= '<button type="button" class="btn btn-outline-success btn-approve-draft" data-id="' . $row['id'] . '" data-name="' . esc($row['task_title'] ?: 'Task #' . $row['task_number']) . '" title="Approve Draft"><i class="fas fa-check"></i></button>';
                $actionBtns .= '<button type="button" class="btn btn-outline-warning btn-revision-draft" data-id="' . $row['id'] . '" data-name="' . esc($row['task_title'] ?: 'Task #' . $row['task_number']) . '" title="Minta Revisi"><i class="fas fa-undo"></i></button>';
            }

            // Detail View
            $actionBtns .= '<a href="' . base_url('drafts/view/' . $row['id']) . '" class="btn btn-outline-info" title="Detail &amp; Histori Review"><i class="fas fa-eye"></i></a>';

            // Edit
            $actionBtns .= '<button type="button" class="btn btn-outline-secondary btn-edit-draft" data-id="' . $row['id'] . '" title="Edit Draft"><i class="fas fa-edit"></i></button>';

            // Delete
            $actionBtns .= '<button type="button" class="btn btn-outline-danger btn-delete-draft" data-id="' . $row['id'] . '" data-name="Draft v' . $row['version'] . ' (' . esc($row['task_title'] ?: 'Task #' . $row['task_number']) . ')" title="Hapus"><i class="fas fa-trash-alt"></i></button>';

            $actionBtns .= '</div>';

            $submittedAt = !empty($row['submitted_at'])
                ? date('d M Y H:i', strtotime($row['submitted_at']))
                : (!empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-');

            $formattedData[] = [
                'checkbox'  => $checkbox,
                'id'        => $row['id'],
                'task'      => '
                    <div class="fw-bold text-dark">
                        <a href="' . base_url('drafts/view/' . $row['id']) . '" class="text-decoration-none text-dark hover-primary">
                            ' . esc($row['task_title'] ?: ('Task #' . $row['task_number'])) . '
                        </a>
                    </div>
                    <div class="mt-1 d-flex align-items-center gap-1">
                        ' . $videoBtn . '
                    </div>
                ',
                'campaign'  => '<div class="fw-semibold text-primary">' . esc($row['campaign_title']) . '</div>',
                'kol'       => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">' . esc($row['kol_whatsapp'] ?: '-') . '</small>
                ',
                'version'   => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">v' . $row['version'] . '</span>',
                'status'    => $statusBadge,
                'submitted' => $submittedAt,
                'action'    => $actionBtns,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $formattedData,
        ]);
    }

    /**
     * Get single draft details & review history (JSON)
     */
    public function getOne(int $id)
    {
        $draft = $this->draftModel->getWithDetails($id);
        if (!$draft) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Draft tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $draft]);
    }

    /**
     * Dedicated View Page: Review Draft Video Details & Full Audit Trail (CI3 Parity)
     */
    public function view(int $id)
    {
        $draft = $this->draftModel->getWithDetails($id);
        if (!$draft) {
            return redirect()->to(base_url('drafts'))->with('error', 'Draft tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Draft #' . $draft['id'] . ' &mdash; Video Review',
            'controller' => 'Drafts',
            'draft'      => $draft,
            'history'    => $draft['history'] ?? [],
        ];

        return view('drafts/view', $data);
    }

    /**
     * Save draft from direct form submission (Add / Edit)
     */
    public function save()
    {
        $id       = (int)$this->request->getPost('id');
        $taskId   = (int)$this->request->getPost('task_id');
        $version  = (int)$this->request->getPost('version') ?: 1;
        $draftUrl = trim((string)($this->request->getPost('draft_url') ?: $this->request->getPost('video_url')));
        $caption  = trim((string)$this->request->getPost('caption'));
        $hashtag  = trim((string)$this->request->getPost('hashtag'));
        $notes    = trim((string)$this->request->getPost('notes'));
        $status   = $this->request->getPost('status') ?: 'DRAFT';

        if (empty($taskId) || empty($draftUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Task ID dan Link Video Draft wajib diisi.',
            ]);
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? 1);

        $saveData = [
            'id'         => $id ?: null,
            'task_id'    => $taskId,
            'version'    => $version,
            'draft_url'  => $draftUrl,
            'video_url'  => $draftUrl,
            'caption'    => $caption ?: null,
            'hashtag'    => $hashtag ?: null,
            'notes'      => $notes ?: null,
            'status'     => $status,
            'created_by' => $userId,
        ];

        $draftId = $this->draftModel->saveDraft($saveData);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Data draft video berhasil disimpan!',
            'draft_id' => $draftId,
        ]);
    }

    /**
     * Submit a new draft video or revised version from Creator/Staff
     */
    public function submit()
    {
        $taskId   = (int) $this->request->getPost('task_id');
        $draftUrl = trim((string)($this->request->getPost('draft_url') ?: $this->request->getPost('video_url')));
        $caption  = trim((string)$this->request->getPost('caption'));
        $hashtag  = trim((string)$this->request->getPost('hashtag'));
        $notes    = trim((string)$this->request->getPost('notes'));

        if (empty($taskId) || empty($draftUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tugas target dan Link URL video draft wajib diisi',
            ]);
        }

        $session   = session();
        $createdBy = (int)($session->get('user_id') ?? 1);

        $draftId = $this->draftModel->submitDraft($taskId, $draftUrl, $notes, $caption, $hashtag, $createdBy);

        $msg = 'Draft video berhasil diunggah dan diajukan untuk review tim!';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => $msg,
                'draft_id' => $draftId,
            ]);
        }

        return redirect()->to(base_url('drafts'))->with('success', $msg);
    }

    /**
     * Submit an existing draft for Leader review (Staff action)
     */
    public function submitReview(int $id)
    {
        $session = session();
        $userId  = (int)($session->get('user_id') ?? 1);

        if (!$this->draftModel->submitForReview($id, $userId)) {
            $msg = 'Gagal mengajukan draft untuk review.';
            return $this->request->isAJAX()
                ? $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('drafts'))->with('error', $msg);
        }

        $msg = 'Draft berhasil dikirim untuk review oleh Leader.';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('drafts'))->with('success', $msg);
    }

    /**
     * Approve draft video (Leader action)
     */
    public function approve(int $id)
    {
        $session        = session();
        $userId         = (int)($session->get('user_id') ?? 1);
        $notes          = trim((string)($this->request->getPost('notes') ?: $this->request->getGet('notes')));
        $timestampNotes = $this->request->getPost('timestamp_notes');

        if (!$this->draftModel->approveDraft($id, $userId, $notes, $timestampNotes)) {
            $msg = 'Gagal menyetujui draft video';
            return $this->request->isAJAX()
                ? $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('drafts'))->with('error', $msg);
        }

        $msg = 'Draft video berhasil disetujui! Status tugas telah dinaikkan ke DRAFT APPROVED.';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('drafts'))->with('success', $msg);
    }

    /**
     * Request revision for draft video with timestamp feedback (Leader action)
     */
    public function revision(int $id)
    {
        $session        = session();
        $userId         = (int)($session->get('user_id') ?? 1);
        $notes          = trim((string)($this->request->getPost('notes') ?: ($this->request->getPost('note') ?: $this->request->getGet('note'))));
        $timestampNotes = $this->request->getPost('timestamp_notes');

        if (empty($notes) && empty($timestampNotes)) {
            $notes = 'Perlu perbaikan draft video.';
        }

        if (!$this->draftModel->requestRevision($id, $userId, $notes, $timestampNotes)) {
            $msg = 'Gagal meminta revisi draft video';
            return $this->request->isAJAX()
                ? $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('drafts'))->with('error', $msg);
        }

        $msg = 'Catatan revisi draft berhasil dikirim ke KOL. Status tugas: DRAFT REVISION.';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('drafts'))->with('warning', $msg);
    }

    /**
     * Delete draft
     */
    public function delete(int $id)
    {
        $draft = $this->draftModel->find($id);
        if (!$draft) {
            $msg = 'Draft tidak ditemukan';
            return $this->request->isAJAX()
                ? $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('drafts'))->with('error', $msg);
        }

        $this->draftModel->delete($id);

        $msg = 'Draft berhasil dihapus';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('drafts'))->with('success', $msg);
    }

    /**
     * Bulk Delete drafts
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada draft yang dipilih untuk dihapus.',
            ]);
        }

        $this->draftModel->whereIn('id', $ids)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($ids) . ' draft berhasil dihapus.',
        ]);
    }

    /**
     * Export drafts ke file CSV
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
        ];

        $data = $this->draftModel->getAllForExport($filters);

        $filename = 'drafts_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'No. VT',
            'Judul Tugas',
            'Kampanye',
            'KOL / Talent',
            'Versi',
            'Link Draft Video',
            'Caption',
            'Hashtag',
            'Catatan Reviewer',
            'Status',
            'Diunggah Oleh',
            'Reviewer',
            'Tanggal Submit',
            'Tanggal Direview',
            'Tanggal Dibuat'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['task_number'],
                $row['task_title'],
                $row['campaign_name'],
                $row['kol_name'],
                'v' . $row['version'],
                $row['draft_url'],
                $row['caption'] ?: '-',
                $row['hashtag'] ?: '-',
                $row['notes'] ?: '-',
                $row['status'] ?: 'DRAFT',
                $row['created_by_name'] ?: 'Staff',
                $row['reviewer_name'] ?: '-',
                $row['submitted_at'] ?: '-',
                $row['reviewed_at'] ?: '-',
                $row['created_at'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
