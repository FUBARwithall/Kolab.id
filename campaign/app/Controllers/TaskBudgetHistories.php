<?php

namespace App\Controllers;

use App\Models\TaskBudgetHistoryModel;
use App\Models\TaskModel;
use App\Models\CampaignModel;
use App\Models\KolModel;
use App\Models\UserModel;

class TaskBudgetHistories extends BaseController
{
    protected TaskBudgetHistoryModel $historyModel;
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;
    protected KolModel $kolModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->historyModel  = new TaskBudgetHistoryModel();
        $this->taskModel     = new TaskModel();
        $this->campaignModel = new CampaignModel();
        $this->kolModel      = new KolModel();
        $this->userModel     = new UserModel();
    }

    /**
     * Halaman Utama Riwayat Perubahan Anggaran / Tarif Task (CI3 & CI4 Parity)
     */
    public function index($offset = 0)
    {
        $filters = [
            'task_id'         => $this->request->getGet('task_id') ?: null,
            'campaign_id'     => $this->request->getGet('campaign_id') ?: null,
            'kol_id'          => $this->request->getGet('kol_id') ?: null,
            'authorized_by'   => $this->request->getGet('authorized_by') ?: ($this->request->getGet('changed_by') ?: null),
            'adjustment_type' => $this->request->getGet('adjustment_type') ?: null,
            'date_start'      => $this->request->getGet('date_start') ?: null,
            'date_end'        => $this->request->getGet('date_end') ?: null,
        ];

        // CI3 Search query handling if 'q' and 'f' are provided
        $q = trim((string)$this->request->getGet('q'));
        $f = trim((string)$this->request->getGet('f'));
        if (!empty($f) && !empty($q)) {
            if ($f === 'task_id') {
                $filters['task_id'] = $q;
            } elseif ($f === 'authorized_by' || $f === 'changed_by') {
                $filters['authorized_by'] = $q;
            }
        }

        $metrics      = $this->historyModel->getSummaryMetrics($filters);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();
        $allTasks     = $this->taskModel->select('id, title, task_number, status')->orderBy('id', 'DESC')->findAll();
        $allKols      = $this->kolModel->select('id, name')->orderBy('name', 'ASC')->findAll();
        $allUsers     = $this->userModel->select('id, name, email')->orderBy('name', 'ASC')->findAll();

        $data = [
            'title'        => 'Task Budget Histories — Riwayat Penyesuaian Anggaran & Tarif Task',
            'controller'   => 'TaskBudgetHistories',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'allTasks'     => $allTasks,
            'allKols'      => $allKols,
            'allUsers'     => $allUsers,
            'filters'      => $filters,
            'searchQuery'  => $q,
        ];

        return view('task_budget_histories/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        $draw   = (int) ($this->request->getVar('draw') ?? 1);
        $start  = (int) ($this->request->getVar('start') ?? 0);
        $length = (int) ($this->request->getVar('length') ?? 10);
        $search = (string) ($this->request->getVar('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getVar('order')[0]['column'] ?? 8);
        $orderDir    = strtoupper((string) ($this->request->getVar('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'task_budget_histories.id', // Checkbox
            1 => 'task_budget_histories.id',
            2 => 'tasks.title',
            3 => 'campaigns.title',
            4 => 'task_budget_histories.old_rate',
            5 => 'task_budget_histories.new_rate',
            6 => 'users.name',
            7 => 'task_budget_histories.reason',
            8 => 'task_budget_histories.created_at',
            9 => 'task_budget_histories.id', // Actions
        ];
        $orderCol = $columns[$orderColIdx] ?? 'task_budget_histories.id';

        $filters = [
            'task_id'         => $this->request->getVar('task_id') ?: null,
            'campaign_id'     => $this->request->getVar('campaign_id') ?: null,
            'kol_id'          => $this->request->getVar('kol_id') ?: null,
            'authorized_by'   => $this->request->getVar('authorized_by') ?: ($this->request->getVar('changed_by') ?: null),
            'adjustment_type' => $this->request->getVar('adjustment_type') ?: null,
            'date_start'      => $this->request->getVar('date_start') ?: null,
            'date_end'        => $this->request->getVar('date_end') ?: null,
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $curr = esc($row['currency_code'] ?? 'IDR');
            $oldRate = (float)$row['old_rate'];
            $newRate = (float)$row['new_rate'];
            $diff = $newRate - $oldRate;

            // Delta Badge
            if ($diff > 0) {
                $pct = ($oldRate > 0) ? round(($diff / $oldRate) * 100, 1) : 100;
                $deltaBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-arrow-up me-1"></i>+' . $curr . ' ' . number_format($diff, 0, ',', '.') . ' (+' . $pct . '%)</span>';
            } elseif ($diff < 0) {
                $pct = ($oldRate > 0) ? round((abs($diff) / $oldRate) * 100, 1) : 100;
                $deltaBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-arrow-down me-1"></i>-' . $curr . ' ' . number_format(abs($diff), 0, ',', '.') . ' (-' . $pct . '%)</span>';
            } else {
                $deltaBadge = '<span class="badge bg-light text-muted border px-2 py-1"><i class="fas fa-equals me-1"></i>Sama (0%)</span>';
            }

            // Task info
            $taskTitle = esc($row['task_title'] ?: ('Task #' . ($row['task_number'] ?? $row['task_id'])));
            $taskDisplay = '
                <div class="fw-bold text-dark">' . $taskTitle . '</div>
                <small class="text-muted font-monospace">Task ID #' . $row['task_id'] . '</small>
            ';

            // Campaign & KOL info
            $campaignKolDisplay = '
                <div class="fw-semibold text-dark">' . esc($row['campaign_title'] ?? 'Global Campaign') . '</div>
                <small class="text-primary"><i class="fas fa-user-circle me-1"></i>' . esc($row['kol_name'] ?? 'KOL') . '</small>
            ';

            // Operator info
            $userDisplay = '
                <div class="fw-semibold text-dark"><i class="fas fa-user-shield text-info me-1"></i>' . esc($row['authorized_name'] ?? 'System') . '</div>
                <small class="text-muted">' . esc($row['authorized_email'] ?? '-') . '</small>
            ';

            // Reason
            $reasonText = esc($row['reason'] ?? '-');
            $reasonDisplay = '<div class="small text-truncate text-dark" style="max-width: 220px;" title="' . $reasonText . '">' . $reasonText . '</div>';

            // Actions
            $actions = '
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <a href="' . base_url('task-budget-histories/view/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Lihat Detail Riwayat Anggaran">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . base_url('task-budget-histories/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak Lembar Audit PDF">
                        <i class="fas fa-print"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Riwayat" onclick="deleteHistory(' . $row['id'] . ')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'checkbox'   => '<input type="checkbox" class="form-check-input check-history-item" value="' . $row['id'] . '">',
                'id'         => '<span class="font-monospace text-muted fw-bold">#' . $row['id'] . '</span>',
                'task'       => $taskDisplay,
                'campaign'   => $campaignKolDisplay,
                'old_rate'   => '<span class="text-muted text-decoration-line-through">' . $curr . ' ' . number_format($oldRate, 0, ',', '.') . '</span>',
                'new_rate'   => '<div><strong class="text-dark">' . $curr . ' ' . number_format($newRate, 0, ',', '.') . '</strong></div><div class="mt-1">' . $deltaBadge . '</div>',
                'authorized' => $userDisplay,
                'reason'     => $reasonDisplay,
                'created_at' => '
                    <div class="fw-semibold text-dark">' . date('d M Y', strtotime($row['created_at'])) . '</div>
                    <small class="text-muted font-monospace">' . date('H:i:s', strtotime($row['created_at'])) . ' WIB</small>
                ',
                'action'     => $actions,
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
     * Get single history item as JSON
     */
    public function getOne(int $id)
    {
        $history = $this->historyModel->getDetail($id);
        if (!$history) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Riwayat perubahan tarif tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $history]);
    }

    /**
     * View dedicated detail page for Task Budget History (CI3 Parity)
     */
    public function view(int $id)
    {
        $history = $this->historyModel->getDetail($id);
        if (!$history) {
            return redirect()->to(base_url('task-budget-histories'))->with('error', 'Riwayat anggaran #' . $id . ' tidak ditemukan');
        }

        $data = [
            'title'      => 'Detail Penyesuaian Anggaran Task #' . $id,
            'controller' => 'TaskBudgetHistories',
            'history'    => $history,
        ];

        return view('task_budget_histories/view', $data);
    }

    /**
     * Delete single or multiple records (CI3 Parity)
     */
    public function delete($id = null)
    {
        $arrId = $this->request->getGet('id') ?: ($this->request->getPost('id') ?: null);

        if (!empty($id)) {
            $this->historyModel->delete((int)$id);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Riwayat anggaran #' . $id . ' berhasil dihapus.']);
            }
            return redirect()->to(base_url('task-budget-histories'))->with('success', 'Riwayat anggaran #' . $id . ' berhasil dihapus.');
        }

        if (is_array($arrId) && count($arrId) > 0) {
            foreach ($arrId as $singleId) {
                $this->historyModel->delete((int)$singleId);
            }
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => count($arrId) . ' riwayat anggaran berhasil dihapus.']);
            }
            return redirect()->to(base_url('task-budget-histories'))->with('success', count($arrId) . ' riwayat anggaran berhasil dihapus.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tidak ada riwayat anggaran yang dipilih untuk dihapus.']);
        }
        return redirect()->to(base_url('task-budget-histories'))->with('error', 'Tidak ada riwayat anggaran yang dipilih untuk dihapus.');
    }

    /**
     * Bulk delete records via AJAX
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih setidaknya satu riwayat anggaran untuk dihapus']);
        }

        $cleanIds = array_map('intval', $ids);
        $this->historyModel->whereIn('id', $cleanIds)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($cleanIds) . ' riwayat anggaran berhasil dihapus',
        ]);
    }

    /**
     * Export budget histories to CSV (CI3 Parity with XLS/CSV)
     */
    public function export()
    {
        $filters = [
            'task_id'       => $this->request->getGet('task_id') ?: null,
            'campaign_id'   => $this->request->getGet('campaign_id') ?: null,
            'authorized_by' => $this->request->getGet('authorized_by') ?: ($this->request->getGet('changed_by') ?: null),
        ];

        $items = $this->historyModel->getAllForExport($filters);

        $filename = 'task_budget_histories_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID',
            'Task ID',
            'Task Title',
            'Campaign Title',
            'KOL Name',
            'Old Rate',
            'New Rate',
            'Currency',
            'Delta Amount',
            'Authorized By',
            'Reason',
            'Timestamp',
        ]);

        foreach ($items as $row) {
            $oldRate = (float)$row['old_rate'];
            $newRate = (float)$row['new_rate'];
            $diff = $newRate - $oldRate;

            fputcsv($output, [
                $row['id'],
                $row['task_id'],
                $row['task_title'] ?? ('Task #' . ($row['task_number'] ?? $row['task_id'])),
                $row['campaign_title'] ?? '',
                $row['kol_name'] ?? '',
                $oldRate,
                $newRate,
                $row['currency_code'] ?? 'IDR',
                $diff,
                $row['authorized_name'] ?? 'System',
                $row['reason'] ?? '',
                $row['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export PDF Rekapitulasi Tabel Task Budget Histories
     */
    public function export_pdf()
    {
        $filters = [
            'task_id'       => $this->request->getGet('task_id') ?: null,
            'campaign_id'   => $this->request->getGet('campaign_id') ?: null,
            'authorized_by' => $this->request->getGet('authorized_by') ?: ($this->request->getGet('changed_by') ?: null),
        ];

        $items = $this->historyModel->getAllForExport($filters);

        return view('task_budget_histories/export_pdf', [
            'items'     => $items,
            'title'     => 'Laporan Audit Penyesuaian Anggaran & Tarif Task',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => $filters,
        ]);
    }

    /**
     * Cetak Lembar Single PDF Bukti Penyesuaian Tarif Task
     */
    public function single_pdf($id = null)
    {
        $id = (int) $id;
        $history = $this->historyModel->getDetail($id);

        if (!$history) {
            return redirect()->to(base_url('task-budget-histories'))->with('error', 'Riwayat anggaran #' . $id . ' tidak ditemukan.');
        }

        return view('task_budget_histories/single_pdf', [
            'history'   => $history,
            'title'     => 'Lembar Audit Penyesuaian Tarif Task #' . $history['id'],
            'generated' => date('d F Y, H:i:s'),
        ]);
    }
}
