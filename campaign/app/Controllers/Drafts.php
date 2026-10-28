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
        $this->draftModel = new DraftModel();
        $this->taskModel = new TaskModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Review Draft Video KOL
     */
    public function index()
    {
        $taskId = $this->request->getGet('task_id');

        $data = [
            'title'        => 'Review Draft Video KOL — Quality & Guideline Control',
            'controller'   => 'Drafts',
            'taskId'       => $taskId,
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('drafts/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Direct access not allowed']);
        }

        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'drafts.id',
            1 => 'tasks.title',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'drafts.version',
            5 => 'drafts.status',
            6 => 'drafts.submitted_at',
            7 => 'drafts.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'drafts.id';

        $filters = [
            'status'      => $this->request->getPost('status'),
            'campaign_id' => $this->request->getPost('campaign_id'),
            'kol_id'      => $this->request->getPost('kol_id'),
        ];

        $result = $this->draftModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = match ($row['status']) {
                DraftModel::STATUS_APPROVED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                DraftModel::STATUS_REVISION_NEEDED => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-exclamation-circle me-1"></i>REVISI DIPERLUKAN</span>',
                default => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>PENDING REVIEW</span>',
            };

            $videoBtn = '<a href="' . esc($row['video_url']) . '" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" style="font-size: 11px;"><i class="fab fa-youtube text-danger me-1"></i>Tonton Video</a>';

            $formattedData[] = [
                'id'       => $row['id'],
                'task'     => '
                    <div class="fw-bold text-dark">' . esc($row['task_title'] ?: ('Task #' . $row['task_number'])) . '</div>
                    <div class="mt-1">' . $videoBtn . '</div>
                ',
                'campaign' => '<div class="fw-semibold text-primary">' . esc($row['campaign_title']) . '</div>',
                'kol'      => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">' . esc($row['kol_whatsapp'] ?: '-') . '</small>
                ',
                'version'  => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">v' . $row['version'] . '</span>',
                'status'   => $statusBadge,
                'submitted'=> date('d M Y H:i', strtotime($row['submitted_at'])),
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail & Catatan Timestamp" onclick="viewDraft(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Review Video Draft" onclick="reviewDraftModal(' . $row['id'] . ')"><i class="fas fa-clipboard-check"></i></button>
                    </div>
                ',
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
     * Get single draft details & review history
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
     * Submit a draft video
     */
    public function submit()
    {
        $taskId   = (int) $this->request->getPost('task_id');
        $videoUrl = trim((string)$this->request->getPost('video_url'));
        $notes    = trim((string)$this->request->getPost('notes'));

        if (empty($taskId) || empty($videoUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tugas target dan Link URL video draft wajib diisi',
            ]);
        }

        if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Format URL video tidak valid (contoh: https://drive.google.com/... atau https://tiktok.com/...)',
            ]);
        }

        $draftId = $this->draftModel->submitDraft($taskId, $videoUrl, $notes);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Draft video berhasil diunggah dan diajukan untuk review tim',
            'draft_id' => $draftId,
        ]);
    }

    /**
     * Approve draft video
     */
    public function approve(int $id)
    {
        $session        = session();
        $userId         = (int) ($session->get('user_id') ?? 1);
        $notes          = trim((string)$this->request->getPost('notes'));
        $timestampNotes = $this->request->getPost('timestamp_notes');

        if (!$this->draftModel->approveDraft($id, $userId, $notes, $timestampNotes)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Gagal menyetujui draft video',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Draft video berhasil disetujui! Status tugas telah dinaikkan ke DRAFT APPROVED.',
        ]);
    }

    /**
     * Request revision for draft video with timestamp feedback
     */
    public function revision(int $id)
    {
        $session        = session();
        $userId         = (int) ($session->get('user_id') ?? 1);
        $notes          = trim((string)$this->request->getPost('notes'));
        $timestampNotes = $this->request->getPost('timestamp_notes');

        if (empty($notes) && empty($timestampNotes)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Catatan revisi atau catatan timestamp wajib diisi untuk panduan perbaikan KOL',
            ]);
        }

        if (!$this->draftModel->requestRevision($id, $userId, $notes, $timestampNotes)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Gagal meminta revisi draft video',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Catatan revisi draft berhasil dikirim ke KOL. Status tugas: DRAFT REVISION.',
        ]);
    }
}
