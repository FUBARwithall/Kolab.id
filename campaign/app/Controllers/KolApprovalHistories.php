<?php

namespace App\Controllers;

use App\Models\KolApprovalHistoryModel;
use App\Models\KolModel;
use App\Models\UserModel;

class KolApprovalHistories extends BaseController
{
    protected KolApprovalHistoryModel $historyModel;
    protected KolModel $kolModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->historyModel = new KolApprovalHistoryModel();
        $this->kolModel     = new KolModel();
        $this->userModel    = new UserModel();
    }

    /**
     * Halaman Utama Riwayat Approval KOL
     */
    public function index($offset = 0)
    {
        $filters = [
            'kol_id'     => $this->request->getGet('kol_id') ?: null,
            'action'     => $this->request->getGet('action') ?: ($this->request->getGet('status') ?: null),
            'acted_by'   => $this->request->getGet('acted_by') ?: null,
            'date_start' => $this->request->getGet('date_start') ?: null,
            'date_end'   => $this->request->getGet('date_end') ?: null,
        ];

        // CI3 Search query handling if 'q' and 'f' are provided
        $q = trim((string)$this->request->getGet('q'));
        $f = trim((string)$this->request->getGet('f'));
        if (!empty($f) && !empty($q)) {
            if ($f === 'kol_id') {
                $filters['kol_id'] = $q;
            } elseif ($f === 'action' || $f === 'status') {
                $filters['action'] = $q;
            } elseif ($f === 'acted_by') {
                $filters['acted_by'] = $q;
            }
        }

        $metrics  = $this->historyModel->getSummaryMetrics($filters);
        $allKols  = $this->kolModel->select('id, name, city')->orderBy('name', 'ASC')->findAll();
        $allUsers = $this->userModel->select('id, name, email')->orderBy('name', 'ASC')->findAll();

        $data = [
            'title'       => 'KOL Approval Histories — Riwayat Persetujuan KOL',
            'controller'  => 'KolApprovalHistories',
            'metrics'     => $metrics,
            'allKols'     => $allKols,
            'allUsers'    => $allUsers,
            'filters'     => $filters,
            'searchQuery' => $q,
        ];

        return view('kol_approval_histories/index', $data);
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
            0 => 'kol_approval_histories.id',
            1 => 'kols.name',
            2 => 'kol_approval_histories.action',
            3 => 'kol_approval_histories.note',
            4 => 'users.name',
            5 => 'kol_approval_histories.created_at',
            6 => 'kol_approval_histories.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'kol_approval_histories.id';

        $filters = [
            'kol_id'     => $this->request->getPost('kol_id') ?: ($this->request->getGet('kol_id') ?: null),
            'action'     => $this->request->getPost('action') ?: ($this->request->getGet('action') ?: null),
            'acted_by'   => $this->request->getPost('acted_by') ?: ($this->request->getGet('acted_by') ?: null),
            'date_start' => $this->request->getPost('date_start') ?: ($this->request->getGet('date_start') ?: null),
            'date_end'   => $this->request->getPost('date_end') ?: ($this->request->getGet('date_end') ?: null),
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $actVal = strtoupper(trim((string)$row['resolved_action']));
            $actionBadge = match ($actVal) {
                'APPROVED' => '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                'SUBMITTED', 'PENDING' => '<span class="badge bg-primary text-white px-2 py-1"><i class="fas fa-paper-plane me-1"></i>SUBMITTED</span>',
                'REJECTED' => '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times-circle me-1"></i>REJECTED</span>',
                default => '<span class="badge bg-secondary text-white px-2 py-1">' . esc($actVal) . '</span>',
            };

            $notePreview = !empty($row['resolved_note'])
                ? '<div class="text-truncate" style="max-width: 320px;" title="' . esc($row['resolved_note']) . '"><i class="fas fa-comment-dots text-secondary me-1"></i>' . esc($row['resolved_note']) . '</div>'
                : '<span class="text-muted fst-italic">-</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="hist-row-check form-check-input" value="' . $row['id'] . '">',
                'kol'      => '
                    <div class="fw-bold text-dark"><a href="' . base_url('kols/view/' . $row['kol_id']) . '" class="text-decoration-none text-dark">' . esc($row['kol_name'] ?? ('KOL #' . $row['kol_id'])) . '</a></div>
                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-danger"></i>' . esc($row['kol_city'] ?: 'Kota -') . ' &bull; ' . esc($row['kol_whatsapp'] ?: '-') . '</small>
                ',
                'action'   => $actionBadge,
                'note'     => $notePreview,
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
                        <a href="' . base_url('kol-approval-histories/view/' . $row['id']) . '" class="btn btn-outline-primary" title="Detail Lengkap">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="' . base_url('kol-approval-histories/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary" title="Cetak Lembar PDF">
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
     * Get single kol approval history for AJAX Modal
     */
    public function getOne(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data riwayat approval KOL tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $hist,
        ]);
    }

    /**
     * Detail View Riwayat Approval KOL
     */
    public function view(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('kol-approval-histories'))->with('error', 'Data riwayat approval KOL tidak ditemukan');
        }

        $allHistories = $this->historyModel->getHistoriesByKol($hist['kol_id']);

        $data = [
            'title'        => 'Detail Approval KOL — ' . ($hist['kol_name'] ?? ('KOL #' . $hist['kol_id'])),
            'controller'   => 'KolApprovalHistories',
            'hist'         => $hist,
            'allHistories' => $allHistories,
        ];

        return view('kol_approval_histories/view', $data);
    }

    /**
     * Delete single history record
     */
    public function delete($id = null)
    {
        $targetId = $id ?: ($this->request->getGet('id') ?: $this->request->getPost('id'));
        if (empty($targetId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID riwayat tidak diberikan']);
            }
            return redirect()->back()->with('error', 'ID riwayat tidak valid');
        }

        if (is_array($targetId)) {
            return $this->bulkDelete();
        }

        $hist = $this->historyModel->find($targetId);
        if (!$hist) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data riwayat tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Data riwayat tidak ditemukan');
        }

        $this->historyModel->delete($targetId);

        $msg = 'Riwayat approval KOL berhasil dihapus.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('kol-approval-histories'))->with('message', $msg);
    }

    /**
     * Bulk delete history records
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids') ?: $this->request->getGet('id');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tidak ada riwayat yang dipilih untuk dihapus',
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
            'message' => "Berhasil menghapus {$deletedCount} catatan riwayat approval KOL.",
        ]);
    }

    /**
     * Export kol approval histories to CSV / Excel
     */
    public function export()
    {
        $filters = [
            'kol_id'     => $this->request->getGet('kol_id') ?: null,
            'action'     => $this->request->getGet('action') ?: ($this->request->getGet('status') ?: null),
            'acted_by'   => $this->request->getGet('acted_by') ?: null,
            'date_start' => $this->request->getGet('date_start') ?: null,
            'date_end'   => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);
        $filename = 'kol_approval_histories_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, [
            'ID Riwayat',
            'KOL ID',
            'Nama Talent KOL',
            'Kota',
            'Nomor WhatsApp',
            'Email',
            'Aksi / Status Approval',
            'Catatan (Note)',
            'Reviewer / Acted By',
            'Email Reviewer',
            'Waktu Review'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['kol_id'],
                $row['kol_name'] ?? '-',
                $row['kol_city'] ?? '-',
                $row['kol_whatsapp'] ?? '-',
                $row['kol_email'] ?? '-',
                $row['resolved_action'] ?? '-',
                $row['resolved_note'] ?? '-',
                $row['reviewer_name'] ?? 'Reviewer',
                $row['reviewer_email'] ?? '-',
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
            'kol_id'     => $this->request->getGet('kol_id') ?: null,
            'action'     => $this->request->getGet('action') ?: ($this->request->getGet('status') ?: null),
            'acted_by'   => $this->request->getGet('acted_by') ?: null,
            'date_start' => $this->request->getGet('date_start') ?: null,
            'date_end'   => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);

        return view('kol_approval_histories/export_pdf', [
            'title'     => 'Daftar Riwayat Persetujuan KOL (KOL Approval Histories)',
            'histories' => $data,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }

    /**
     * Single kol approval history PDF / Printable sheet (CI3 parity single_pdf)
     */
    public function single_pdf(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('kol-approval-histories'))->with('error', 'Data riwayat approval tidak ditemukan');
        }

        return view('kol_approval_histories/single_pdf', [
            'title'     => 'Lembar Riwayat Approval - KOL #' . $hist['kol_id'],
            'hist'      => $hist,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }
}
