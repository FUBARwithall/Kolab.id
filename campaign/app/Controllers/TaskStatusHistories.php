<?php

namespace App\Controllers;

use App\Models\TaskStatusHistoryModel;
use App\Models\TaskModel;
use App\Models\CampaignModel;
use App\Models\UserModel;

class TaskStatusHistories extends BaseController
{
    protected TaskStatusHistoryModel $historyModel;
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->historyModel  = new TaskStatusHistoryModel();
        $this->taskModel     = new TaskModel();
        $this->campaignModel = new CampaignModel();
        $this->userModel     = new UserModel();
    }

    /**
     * Halaman Utama Riwayat Perubahan Status Task
     */
    public function index($offset = 0)
    {
        $filters = [
            'task_id'     => $this->request->getGet('task_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: ($this->request->getGet('to_status') ?: null),
            'changed_by'  => $this->request->getGet('changed_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        // CI3 Search query handling if 'q' and 'f' are provided
        $q = trim((string)$this->request->getGet('q'));
        $f = trim((string)$this->request->getGet('f'));
        if (!empty($f) && !empty($q)) {
            if ($f === 'task_id') {
                $filters['task_id'] = $q;
            } elseif ($f === 'status' || $f === 'to_status') {
                $filters['status'] = $q;
            } elseif ($f === 'changed_by') {
                $filters['changed_by'] = $q;
            }
        }

        $metrics      = $this->historyModel->getSummaryMetrics($filters);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();
        $allTasks     = $this->taskModel->select('id, title, task_number, status')->orderBy('id', 'DESC')->findAll();
        $allUsers     = $this->userModel->select('id, name, email')->orderBy('name', 'ASC')->findAll();

        $allStatuses = [
            'DRAFT',
            'BRIEF_SUBMITTED',
            'BRIEF_REVISION',
            'BRIEF_APPROVED',
            'WAITING_DRAFT',
            'DRAFT_SUBMITTED',
            'IN_REVIEW_DRAFT',
            'DRAFT_REVISION',
            'DRAFT_APPROVED',
            'SCHEDULED',
            'POSTED',
            'SCRAPING',
            'COMPLETED',
            'DONE',
            'CANCELLED'
        ];

        $data = [
            'title'        => 'Task Status Histories — Riwayat Perubahan Status Task',
            'controller'   => 'TaskStatusHistories',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'allTasks'     => $allTasks,
            'allUsers'     => $allUsers,
            'allStatuses'  => $allStatuses,
            'filters'      => $filters,
            'searchQuery'  => $q,
        ];

        return view('task_status_histories/index', $data);
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
            0 => 'task_status_histories.id',
            1 => 'tasks.title',
            2 => 'task_status_histories.status',
            3 => 'users.name',
            4 => 'task_status_histories.note',
            5 => 'campaigns.title',
            6 => 'task_status_histories.created_at',
            7 => 'task_status_histories.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'task_status_histories.id';

        $filters = [
            'task_id'     => $this->request->getPost('task_id') ?: ($this->request->getGet('task_id') ?: null),
            'campaign_id' => $this->request->getPost('campaign_id') ?: ($this->request->getGet('campaign_id') ?: null),
            'status'      => $this->request->getPost('status') ?: ($this->request->getGet('status') ?: null),
            'changed_by'  => $this->request->getPost('changed_by') ?: ($this->request->getGet('changed_by') ?: null),
            'date_start'  => $this->request->getPost('date_start') ?: ($this->request->getGet('date_start') ?: null),
            'date_end'    => $this->request->getPost('date_end') ?: ($this->request->getGet('date_end') ?: null),
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $fromSt = !empty($row['from_status']) ? strtoupper(trim((string)$row['from_status'])) : null;
            $toSt   = strtoupper(trim((string)($row['to_status'] ?: ($row['status'] ?: 'DRAFT'))));

            $badgeColor = match ($toSt) {
                'BRIEF_APPROVED', 'DRAFT_APPROVED', 'APPROVED_DRAFT', 'COMPLETED', 'DONE' => 'success',
                'BRIEF_REVISION', 'DRAFT_REVISION', 'REVISION', 'REVISION_DRAFT' => 'warning text-dark',
                'BRIEF_SUBMITTED', 'DRAFT_SUBMITTED', 'PENDING_REVIEW', 'IN_REVIEW_DRAFT', 'WAITING_DRAFT' => 'info text-white',
                'SCHEDULED', 'POSTED' => 'primary',
                'CANCELLED' => 'danger',
                default => 'secondary',
            };

            $transitionHtml = '';
            if ($fromSt && $fromSt !== $toSt) {
                $transitionHtml = '<div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="badge bg-light text-dark border font-monospace text-xs">' . esc($fromSt) . '</span>
                    <i class="fas fa-arrow-right text-muted text-xs"></i>
                    <span class="badge bg-' . $badgeColor . ' font-monospace px-2 py-1"><i class="fas fa-tag me-1"></i>' . esc($toSt) . '</span>
                </div>';
            } else {
                $transitionHtml = '<span class="badge bg-' . $badgeColor . ' font-monospace px-2 py-1"><i class="fas fa-tag me-1"></i>' . esc($toSt) . '</span>';
            }

            $notePreview = !empty($row['resolved_note'])
                ? '<div class="text-truncate" style="max-width: 280px;" title="' . esc($row['resolved_note']) . '"><i class="fas fa-comment-dots text-secondary me-1"></i>' . esc($row['resolved_note']) . '</div>'
                : '<span class="text-muted fst-italic">-</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="hist-row-check form-check-input" value="' . $row['id'] . '">',
                'task'     => '
                    <div class="fw-bold text-dark"><a href="' . base_url('tasks/view/' . $row['task_id']) . '" class="text-decoration-none text-dark">' . esc($row['task_title'] ?: ('Task #' . $row['task_id'])) . '</a></div>
                    <small class="text-muted"><i class="fas fa-hashtag me-1"></i>Task No: ' . esc($row['task_number'] ?? '1') . ' &bull; Current: <span class="badge bg-light text-dark border">' . esc($row['task_current_status'] ?? '-') . '</span></small>
                ',
                'status'   => $transitionHtml,
                'note'     => $notePreview,
                'campaign' => '
                    <div class="fw-semibold text-primary">' . esc($row['campaign_title'] ?? '-') . '</div>
                    <small class="text-secondary"><i class="fas fa-user-circle me-1"></i>' . esc($row['kol_name'] ?? 'KOL') . '</small>
                ',
                'changed_by' => '
                    <div class="fw-semibold text-dark"><i class="fas fa-user-edit me-1 text-info"></i>' . esc($row['changed_by_name'] ?? 'System') . '</div>
                    <small class="text-muted">' . esc($row['changed_by_email'] ?? '') . '</small>
                ',
                'created_at' => '
                    <span class="text-dark fw-semibold">' . date('d M Y', strtotime($row['created_at'])) . '</span><br>
                    <small class="text-muted"><i class="far fa-clock me-1"></i>' . date('H:i:s', strtotime($row['created_at'])) . '</small>
                ',
                'actions'  => '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info" title="Lihat Snapshot Modal" onclick="viewHistorySnapshot(' . $row['id'] . ')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . base_url('task-status-histories/view/' . $row['id']) . '" class="btn btn-outline-primary" title="Detail Lengkap">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="' . base_url('task-status-histories/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary" title="Cetak Lembar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" title="Hapus Riwayat" onclick="deleteHistory(' . $row['id'] . ')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                '
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
     * Ambil 1 data riwayat untuk AJAX preview modal
     */
    public function getOne($id = null)
    {
        $id = (int) $id;
        $row = $this->historyModel->getDetail($id);

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data riwayat tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * Halaman Detail Lengkap Riwayat
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $history = $this->historyModel->getDetail($id);

        if (!$history) {
            return redirect()->to(base_url('task-status-histories'))->with('error', 'Data riwayat perubahan status tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Riwayat Perubahan Status #' . $history['id'],
            'controller' => 'TaskStatusHistories',
            'history'    => $history,
        ];

        return view('task_status_histories/view', $data);
    }

    /**
     * Hapus single riwayat (CI3 and CI4 compatible)
     */
    public function delete($id = null)
    {
        // Check if IDs provided via query string array arr_id / id[] (CI3 compatibility)
        $arrId = $this->request->getGet('id') ?: ($this->request->getPost('id') ?: null);

        if (!empty($id)) {
            $this->historyModel->delete((int)$id);
            return redirect()->to(base_url('task-status-histories'))->with('success', 'Riwayat perubahan status berhasil dihapus.');
        }

        if (is_array($arrId) && count($arrId) > 0) {
            foreach ($arrId as $singleId) {
                $this->historyModel->delete((int)$singleId);
            }
            return redirect()->to(base_url('task-status-histories'))->with('success', count($arrId) . ' riwayat berhasil dihapus.');
        }

        return redirect()->to(base_url('task-status-histories'))->with('error', 'Tidak ada data riwayat yang dipilih untuk dihapus.');
    }

    /**
     * Bulk Delete via POST AJAX
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Pilih minimal satu data riwayat untuk dihapus.',
            ]);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if ($this->historyModel->delete((int)$id)) {
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $deletedCount . ' riwayat status berhasil dihapus.',
        ]);
    }

    /**
     * Export ke Excel / CSV
     */
    public function export()
    {
        $filters = [
            'task_id'     => $this->request->getGet('task_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'changed_by'  => $this->request->getGet('changed_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);

        $filename = 'task_status_histories_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'Task ID',
            'Task Title',
            'Task Number',
            'From Status',
            'To Status',
            'Status',
            'Campaign Title',
            'KOL Name',
            'Changed By',
            'Changed By Email',
            'Note',
            'Created At',
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['task_id'],
                $row['task_title'] ?? '',
                $row['task_number'] ?? '',
                $row['from_status'] ?? '',
                $row['to_status'] ?? '',
                $row['status'] ?? '',
                $row['campaign_title'] ?? '',
                $row['kol_name'] ?? '',
                $row['changed_by_name'] ?? '',
                $row['changed_by_email'] ?? '',
                $row['resolved_note'] ?? '',
                $row['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export PDF Tabel Keseluruhan
     */
    public function export_pdf()
    {
        $filters = [
            'task_id'     => $this->request->getGet('task_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'changed_by'  => $this->request->getGet('changed_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $items = $this->historyModel->getAllForExport($filters);

        return view('task_status_histories/export_pdf', [
            'items'     => $items,
            'title'     => 'Laporan Riwayat Perubahan Status Task',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => $filters,
        ]);
    }

    /**
     * Cetak Lembar Single PDF Bukti Status Transition
     */
    public function single_pdf($id = null)
    {
        $id = (int) $id;
        $history = $this->historyModel->getDetail($id);

        if (!$history) {
            return redirect()->to(base_url('task-status-histories'))->with('error', 'Data riwayat tidak ditemukan.');
        }

        return view('task_status_histories/single_pdf', [
            'history'   => $history,
            'title'     => 'Lembar Audit Perubahan Status Task #' . $history['id'],
            'generated' => date('d F Y, H:i:s'),
        ]);
    }
}
