<?php

namespace App\Controllers;

use App\Models\BriefModel;
use App\Models\TaskModel;
use App\Models\CampaignModel;

class Briefs extends BaseController
{
    protected BriefModel $briefModel;
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->briefModel = new BriefModel();
        $this->taskModel = new TaskModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Briefs & Guidelines
     */
    public function index()
    {
        $data = [
            'title'        => 'Briefs & Guidelines — Pengarahan Konten KOL',
            'controller'   => 'Briefs',
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('briefs/index', $data);
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
            0 => 'briefs.id',
            1 => 'briefs.title',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'briefs.approval_status',
            5 => 'briefs.created_at',
            6 => 'briefs.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'briefs.id';

        $filters = [
            'approval_status' => $this->request->getPost('approval_status'),
            'campaign_id'     => $this->request->getPost('campaign_id'),
        ];

        $result = $this->briefModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = match ($row['approval_status']) {
                BriefModel::STATUS_APPROVED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                BriefModel::STATUS_REVISION_NEEDED => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-exclamation-circle me-1"></i>REVISION NEEDED</span>',
                BriefModel::STATUS_REJECTED => '<span class="badge bg-dark-subtle text-dark border px-2 py-1"><i class="fas fa-times me-1"></i>REJECTED</span>',
                default => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>PENDING REVIEW</span>',
            };

            $formattedData[] = [
                'id'       => $row['id'],
                'title'    => '
                    <div class="fw-bold text-dark">' . esc($row['title']) . '</div>
                    <small class="text-muted">Task: ' . esc($row['task_title'] ?: ('VT #' . $row['task_number'])) . '</small>
                ',
                'campaign' => '<div class="fw-semibold text-primary">' . esc($row['campaign_title']) . '</div>',
                'kol'      => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">Dibuat: ' . esc($row['submitter_name'] ?? 'Admin') . '</small>
                ',
                'status'   => $statusBadge,
                'created'  => date('d M Y H:i', strtotime($row['created_at'])),
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Brief" onclick="viewBrief(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Brief" onclick="editBrief(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Review & Approval" onclick="reviewBriefModal(' . $row['id'] . ')"><i class="fas fa-clipboard-check"></i></button>
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
     * Get single brief details & approval history
     */
    public function getOne(int $id)
    {
        $brief = $this->briefModel->getWithDetails($id);
        if (!$brief) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Brief tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $brief]);
    }

    /**
     * Save / update brief
     */
    public function save()
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);

        $id            = $this->request->getPost('id');
        $taskId        = (int) $this->request->getPost('task_id');
        $title         = trim((string)$this->request->getPost('title'));
        $guidelines    = trim((string)$this->request->getPost('guidelines'));
        $dos           = trim((string)$this->request->getPost('dos'));
        $donts         = trim((string)$this->request->getPost('donts'));
        $referenceUrl  = trim((string)$this->request->getPost('reference_url'));

        if (empty($title) || empty($guidelines) || empty($taskId)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Judul brief, tugas target, dan petunjuk konten wajib diisi',
            ]);
        }

        $briefData = [
            'task_id'         => $taskId,
            'title'           => $title,
            'guidelines'      => $guidelines,
            'dos'             => $dos ?: null,
            'donts'           => $donts ?: null,
            'reference_url'   => $referenceUrl ?: null,
            'approval_status' => BriefModel::STATUS_PENDING,
            'submitted_by'    => $userId,
        ];

        if (!empty($id)) {
            $this->briefModel->update($id, $briefData);
            $briefId = (int)$id;
        } else {
            $briefId = $this->briefModel->insert($briefData, true);
        }

        // Update task status ke BRIEF_SUBMITTED
        $this->taskModel->update($taskId, [
            'status'     => TaskModel::STATUS_BRIEF_SUBMITTED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Brief berhasil disimpan dan diajukan untuk review',
            'brief_id' => $briefId,
        ]);
    }

    /**
     * Approve brief
     */
    public function approve(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);
        $notes   = trim((string)$this->request->getPost('notes'));

        if (!$this->briefModel->approveBrief($id, $userId, $notes)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Gagal menyetujui brief',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Brief berhasil disetujui (Approved)',
        ]);
    }

    /**
     * Request revision for brief
     */
    public function revision(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);
        $notes   = trim((string)$this->request->getPost('notes'));

        if (empty($notes)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Catatan revisi wajib diisi agar KOL/staff mengetahui bagian yang harus diperbaiki',
            ]);
        }

        if (!$this->briefModel->requestRevision($id, $userId, $notes)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Gagal meminta revisi brief',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Permintaan revisi brief berhasil dikirim',
        ]);
    }
}
