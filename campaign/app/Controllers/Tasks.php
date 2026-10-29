<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Models\CampaignModel;

class Tasks extends BaseController
{
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Tasks Engine (SOW, Review Draft, Posting & Monitoring)
     */
    public function index()
    {
        $stage = $this->request->getGet('stage') ?? 'all';
        $campaignId = $this->request->getGet('campaign_id') ?? '';

        $data = [
            'title'        => 'Tasks Engine — Alur Kerja Produksi Konten VT',
            'controller'   => 'Tasks',
            'metrics'      => $this->taskModel->getSummaryMetrics(),
            'stage'        => $stage,
            'campaignId'   => $campaignId,
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('tasks/index', $data);
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
            0 => 'tasks.id',
            1 => 'tasks.title',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'tasks.deadline',
            5 => 'tasks.status',
            6 => 'tasks.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'tasks.id';

        $filters = [
            'stage'       => $this->request->getPost('stage'),
            'campaign_id' => $this->request->getPost('campaign_id'),
            'kol_id'      => $this->request->getPost('kol_id'),
            'status'      => $this->request->getPost('status'),
        ];

        $result = $this->taskModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = $this->renderStatusBadge($row['status']);
            $stageAction = $this->renderStageAction($row);

            $deadlineStr = !empty($row['deadline'])
                ? '<div class="fw-semibold text-dark"><i class="far fa-calendar-alt text-primary me-1"></i>' . date('d M Y', strtotime($row['deadline'])) . '</div>'
                : '<span class="text-muted fst-italic">Belum di-set</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'title'    => '
                    <div class="fw-bold text-dark">' . esc($row['title'] ?: ('Task #' . $row['task_number'])) . '</div>
                    <small class="text-muted">VT #' . $row['task_number'] . ' &bull; Fee: Rp ' . number_format((float)$row['rate_amount'], 0, ',', '.') . '</small>
                ',
                'campaign' => '<a href="' . base_url('campaigns/view/' . $row['campaign_id']) . '" class="text-decoration-none fw-semibold text-primary">' . esc($row['campaign_title']) . '</a>',
                'kol'      => '
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px; font-weight: bold;">
                            ' . strtoupper(substr($row['kol_name'], 0, 1)) . '
                        </div>
                        <div>
                            <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                            <small class="text-muted">' . esc($row['kol_whatsapp'] ?: '-') . '</small>
                        </div>
                    </div>
                ',
                'deadline' => $deadlineStr,
                'status'   => $statusBadge,
                'action'   => $stageAction,
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
     * Get single task JSON
     */
    public function getOne(int $id)
    {
        $task = $this->taskModel->getTaskDetails($id);
        if (!$task) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Task tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $task]);
    }

    /**
     * Update task status
     */
    public function updateStatus(int $id)
    {
        $status = strtoupper(trim((string)$this->request->getPost('status')));
        if (!$this->taskModel->updateTaskStatus($id, $status)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Status tidak valid']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Status tugas berhasil diperbarui ke ' . $status]);
    }

    /**
     * Set schedule/deadline for task
     */
    public function setSchedule(int $id)
    {
        $deadline = $this->request->getPost('deadline');
        $title    = $this->request->getPost('title');

        $updateData = [];
        if ($deadline) {
            $updateData['deadline'] = $deadline;
        }
        if ($title) {
            $updateData['title'] = trim((string)$title);
        }

        if (empty($updateData)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tidak ada data diperbarui']);
        }

        $this->taskModel->update($id, $updateData);

        return $this->response->setJSON(['success' => true, 'message' => 'Jadwal & judul tugas berhasil disimpan']);
    }

    /**
     * Delete draft task
     */
    public function delete(int $id)
    {
        $task = $this->taskModel->find($id);
        if (!$task) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Tugas tidak ditemukan']);
        }

        if (!in_array($task['status'], ['', TaskModel::STATUS_DRAFT, TaskModel::STATUS_CANCELLED], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tugas yang sedang berjalan tidak dapat dihapus. Silakan batalkan statusnya terlebih dahulu.']);
        }

        $this->taskModel->delete($id);

        return $this->response->setJSON(['success' => true, 'message' => 'Tugas berhasil dihapus']);
    }

    /**
     * Helper render status badge
     */
    private function renderStatusBadge(?string $status): string
    {
        $st = strtoupper(trim((string)$status));
        return match ($st) {
            '', TaskModel::STATUS_DRAFT => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-file-alt me-1"></i>DRAFT</span>',
            TaskModel::STATUS_BRIEF_SUBMITTED => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-paper-plane me-1"></i>BRIEF SUBMITTED</span>',
            TaskModel::STATUS_BRIEF_REVISION => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-undo me-1"></i>BRIEF REVISION</span>',
            TaskModel::STATUS_BRIEF_APPROVED => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-check me-1"></i>BRIEF APPROVED</span>',
            TaskModel::STATUS_DRAFT_SUBMITTED => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-video me-1"></i>DRAFT REVIEW</span>',
            TaskModel::STATUS_DRAFT_REVISION => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-exclamation-circle me-1"></i>REVISI DRAFT</span>',
            TaskModel::STATUS_DRAFT_APPROVED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-thumbs-up me-1"></i>DRAFT APPROVED</span>',
            TaskModel::STATUS_SCHEDULED => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="far fa-clock me-1"></i>SCHEDULED</span>',
            TaskModel::STATUS_POSTED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-globe me-1"></i>POSTED</span>',
            TaskModel::STATUS_SCRAPING => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-sync fa-spin me-1"></i>SCRAPING</span>',
            TaskModel::STATUS_COMPLETED => '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-double me-1"></i>COMPLETED</span>',
            TaskModel::STATUS_CANCELLED => '<span class="badge bg-dark-subtle text-dark border px-2 py-1"><i class="fas fa-ban me-1"></i>CANCELLED</span>',
            default => '<span class="badge bg-light text-dark border px-2 py-1">' . esc($st) . '</span>',
        };
    }

    /**
     * Helper render contextual action buttons
     */
    private function renderStageAction(array $row): string
    {
        $id = $row['id'];
        $st = strtoupper(trim((string)$row['status']));

        $btns = '<div class="d-flex gap-1 justify-content-center">';

        // Button detail modal
        $btns .= '<button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail & Timeline" onclick="viewTaskDetails(' . $id . ')"><i class="fas fa-eye"></i></button>';

        // Set deadline/schedule
        $btns .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Atur Jadwal / Deadline" onclick="setScheduleModal(' . $id . ')"><i class="far fa-calendar-alt"></i></button>';

        // Contextual buttons depending on state
        if (in_array($st, ['', TaskModel::STATUS_DRAFT, TaskModel::STATUS_BRIEF_SUBMITTED, TaskModel::STATUS_BRIEF_REVISION], true)) {
            $btns .= '<a href="' . base_url('briefs?task_id=' . $id) . '" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Kelola Brief"><i class="fas fa-file-alt"></i></a>';
        }

        if (in_array($st, [TaskModel::STATUS_BRIEF_APPROVED, TaskModel::STATUS_DRAFT_SUBMITTED, TaskModel::STATUS_DRAFT_REVISION, TaskModel::STATUS_DRAFT_APPROVED], true)) {
            $btns .= '<a href="' . base_url('drafts?task_id=' . $id) . '" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2" title="Review Draft Video"><i class="fas fa-video"></i></a>';
        }

        if (in_array($st, [TaskModel::STATUS_DRAFT_APPROVED, TaskModel::STATUS_SCHEDULED, TaskModel::STATUS_POSTED], true)) {
            $btns .= '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Input URL Postingan Live" onclick="setLiveUrlModal(' . $id . ')"><i class="fas fa-external-link-alt"></i></button>';
        }

        $btns .= '</div>';
        return $btns;
    }
}
