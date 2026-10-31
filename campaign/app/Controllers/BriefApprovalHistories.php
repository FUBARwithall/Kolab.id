<?php

namespace App\Controllers;

use App\Models\BriefApprovalHistoryModel;
use App\Models\BriefModel;
use App\Models\CampaignModel;
use App\Models\UserModel;

class BriefApprovalHistories extends BaseController
{
    protected BriefApprovalHistoryModel $historyModel;
    protected BriefModel $briefModel;
    protected CampaignModel $campaignModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->historyModel  = new BriefApprovalHistoryModel();
        $this->briefModel    = new BriefModel();
        $this->campaignModel = new CampaignModel();
        $this->userModel     = new UserModel();
    }

    /**
     * Halaman Utama Riwayat Approval Brief
     */
    public function index($offset = 0)
    {
        $filters = [
            'brief_id'    => $this->request->getGet('brief_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'acted_by'    => $this->request->getGet('acted_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        // CI3 Search query handling if 'q' and 'f' are provided
        $q = trim((string)$this->request->getGet('q'));
        $f = trim((string)$this->request->getGet('f'));
        if (!empty($f) && !empty($q)) {
            if ($f === 'brief_id') {
                $filters['brief_id'] = $q;
            } elseif ($f === 'status') {
                $filters['status'] = $q;
            } elseif ($f === 'acted_by') {
                $filters['acted_by'] = $q;
            }
        }

        $metrics = $this->historyModel->getSummaryMetrics($filters);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();
        $allBriefs = $this->briefModel->select('id, title, task_id')->orderBy('id', 'DESC')->findAll();
        $allUsers = $this->userModel->select('id, name, email')->orderBy('name', 'ASC')->findAll();

        $data = [
            'title'        => 'Brief Approval Histories — Riwayat Persetujuan Brief',
            'controller'   => 'BriefApprovalHistories',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'allBriefs'    => $allBriefs,
            'allUsers'     => $allUsers,
            'filters'      => $filters,
            'searchQuery'  => $q,
        ];

        return view('brief_approval_histories/index', $data);
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
            0 => 'brief_approval_histories.id',
            1 => 'briefs.title',
            2 => 'brief_approval_histories.status',
            3 => 'brief_approval_histories.note',
            4 => 'campaigns.title',
            5 => 'users.name',
            6 => 'brief_approval_histories.created_at',
            7 => 'brief_approval_histories.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'brief_approval_histories.id';

        $filters = [
            'brief_id'    => $this->request->getPost('brief_id') ?: ($this->request->getGet('brief_id') ?: null),
            'campaign_id' => $this->request->getPost('campaign_id') ?: ($this->request->getGet('campaign_id') ?: null),
            'status'      => $this->request->getPost('status') ?: ($this->request->getGet('status') ?: null),
            'acted_by'    => $this->request->getPost('acted_by') ?: ($this->request->getGet('acted_by') ?: null),
            'date_start'  => $this->request->getPost('date_start') ?: ($this->request->getGet('date_start') ?: null),
            'date_end'    => $this->request->getPost('date_end') ?: ($this->request->getGet('date_end') ?: null),
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusVal = strtoupper(trim((string)$row['status']));
            $statusBadge = match ($statusVal) {
                'APPROVED' => '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                'REVISION', 'REVISION_NEEDED' => '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-undo me-1"></i>REVISION</span>',
                'REJECTED' => '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times-circle me-1"></i>REJECTED</span>',
                default => '<span class="badge bg-secondary text-white px-2 py-1">' . esc($statusVal) . '</span>',
            };

            $notePreview = !empty($row['resolved_note'])
                ? '<div class="text-truncate" style="max-width: 280px;" title="' . esc($row['resolved_note']) . '"><i class="fas fa-comment-dots text-secondary me-1"></i>' . esc($row['resolved_note']) . '</div>'
                : '<span class="text-muted fst-italic">-</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="hist-row-check form-check-input" value="' . $row['id'] . '">',
                'brief'    => '
                    <div class="fw-bold text-dark"><a href="' . base_url('briefs/view/' . $row['brief_id']) . '" class="text-decoration-none text-dark">' . esc($row['briefs_title'] ?? ('Brief #' . $row['brief_id'])) . '</a></div>
                    <small class="text-muted"><i class="fas fa-tasks me-1"></i>Task: ' . esc($row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1'))) . '</small>
                ',
                'status'   => $statusBadge,
                'note'     => $notePreview,
                'campaign' => '
                    <div class="fw-semibold text-primary">' . esc($row['campaign_title'] ?? '-') . '</div>
                    <small class="text-secondary"><i class="fas fa-user-circle me-1"></i>' . esc($row['kol_name'] ?? 'KOL') . '</small>
                ',
                'reviewer' => '
                    <div class="fw-semibold text-dark"><i class="fas fa-user-check me-1 text-info"></i>' . esc($row['reviewer_name'] ?? 'Reviewer') . '</div>
                    <small class="text-muted">' . esc($row['reviewer_email'] ?? '') . '</small>
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
                        <a href="' . base_url('brief-approval-histories/view/' . $row['id']) . '" class="btn btn-outline-primary" title="Detail Lengkap">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="' . base_url('brief-approval-histories/single_pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary" title="Cetak Lembar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" title="Hapus Riwayat" onclick="deleteApprovalHistory(' . $row['id'] . ')">
                            <i class="fas fa-trash"></i>
                        </button>
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
     * Get single brief approval history for AJAX Modal
     */
    public function getOne(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data riwayat approval tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $hist,
        ]);
    }

    /**
     * Detail View Riwayat Approval Brief
     */
    public function view(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('brief-approval-histories'))->with('error', 'Data riwayat approval tidak ditemukan');
        }

        $allHistories = $this->historyModel->getHistoriesByBrief($hist['brief_id']);

        $data = [
            'title'        => 'Detail Approval Brief — ' . ($hist['briefs_title'] ?? ('Brief #' . $hist['brief_id'])),
            'controller'   => 'BriefApprovalHistories',
            'hist'         => $hist,
            'allHistories' => $allHistories,
        ];

        return view('brief_approval_histories/view', $data);
    }

    /**
     * Delete single history snapshot
     */
    public function delete($id = null)
    {
        $targetId = $id ?: ($this->request->getGet('id') ?: $this->request->getPost('id'));
        if (empty($targetId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID approval history tidak diberikan']);
            }
            return redirect()->back()->with('error', 'ID approval history tidak valid');
        }

        if (is_array($targetId)) {
            return $this->bulkDelete();
        }

        $hist = $this->historyModel->find($targetId);
        if (!$hist) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data approval history tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Data approval history tidak ditemukan');
        }

        $this->historyModel->delete($targetId);

        $msg = 'Riwayat approval brief berhasil dihapus.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('brief-approval-histories'))->with('message', $msg);
    }

    /**
     * Bulk delete history snapshots
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids') ?: $this->request->getGet('id');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tidak ada riwayat approval yang dipilih untuk dihapus',
            ]);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if (!empty($id)) {
                $this->historyModel->delete($id);
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Berhasil menghapus {$deletedCount} catatan riwayat approval.",
        ]);
    }

    /**
     * Export brief approval histories to CSV / Excel
     */
    public function export()
    {
        $filters = [
            'brief_id'    => $this->request->getGet('brief_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'acted_by'    => $this->request->getGet('acted_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);
        $filename = 'brief_approval_histories_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, [
            'ID Riwayat',
            'Brief ID',
            'Judul Brief',
            'Status Approval',
            'Catatan Reviewer (Note)',
            'Reviewer / Acted By',
            'Email Reviewer',
            'Kampanye',
            'Talent KOL',
            'Task / VT',
            'Waktu Review'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['brief_id'],
                $row['briefs_title'] ?? '-',
                $row['status'] ?? '-',
                $row['resolved_note'] ?? '-',
                $row['reviewer_name'] ?? 'Reviewer',
                $row['reviewer_email'] ?? '-',
                $row['campaign_title'] ?? '-',
                $row['kol_name'] ?? '-',
                $row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1')),
                $row['created_at'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Printable PDF / Summary List
     */
    public function export_pdf()
    {
        $filters = [
            'brief_id'    => $this->request->getGet('brief_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
            'acted_by'    => $this->request->getGet('acted_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);

        return view('brief_approval_histories/export_pdf', [
            'title'     => 'Daftar Riwayat Persetujuan Brief (Brief Approval Histories)',
            'histories' => $data,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }

    /**
     * Single brief approval history PDF / Printable sheet (CI3 parity single_pdf)
     */
    public function single_pdf(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('brief-approval-histories'))->with('error', 'Data riwayat approval tidak ditemukan');
        }

        return view('brief_approval_histories/single_pdf', [
            'title'     => 'Lembar Riwayat Approval - Brief #' . $hist['brief_id'],
            'hist'      => $hist,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }
}
