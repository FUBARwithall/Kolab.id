<?php

namespace App\Controllers;

use App\Models\DraftReviewHistoryModel;
use App\Models\DraftModel;
use App\Models\CampaignModel;
use App\Models\UserModel;

class DraftReviewHistories extends BaseController
{
    protected DraftReviewHistoryModel $historyModel;
    protected DraftModel $draftModel;
    protected CampaignModel $campaignModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->historyModel  = new DraftReviewHistoryModel();
        $this->draftModel    = new DraftModel();
        $this->campaignModel = new CampaignModel();
        $this->userModel     = new UserModel();
    }

    /**
     * Halaman Utama Riwayat Review Draft Video
     */
    public function index($offset = 0)
    {
        $filters = [
            'draft_id'    => $this->request->getGet('draft_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'action'      => $this->request->getGet('action') ?: ($this->request->getGet('status') ?: null),
            'acted_by'    => $this->request->getGet('acted_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        // CI3 Search query handling if 'q' and 'f' are provided
        $q = trim((string)$this->request->getGet('q'));
        $f = trim((string)$this->request->getGet('f'));
        if (!empty($f) && !empty($q)) {
            if ($f === 'draft_id') {
                $filters['draft_id'] = $q;
            } elseif ($f === 'action' || $f === 'status') {
                $filters['action'] = $q;
            } elseif ($f === 'acted_by') {
                $filters['acted_by'] = $q;
            }
        }

        $metrics      = $this->historyModel->getSummaryMetrics($filters);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();
        $allDrafts    = $this->draftModel->select('id, version, task_id, status')->orderBy('id', 'DESC')->findAll();
        $allUsers     = $this->userModel->select('id, name, email')->orderBy('name', 'ASC')->findAll();

        $data = [
            'title'        => 'Draft Review Histories — Riwayat Review Video Draft',
            'controller'   => 'DraftReviewHistories',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'allDrafts'    => $allDrafts,
            'allUsers'     => $allUsers,
            'filters'      => $filters,
            'searchQuery'  => $q,
        ];

        return view('draft_review_histories/index', $data);
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
            0 => 'draft_review_histories.id',
            1 => 'drafts.id',
            2 => 'draft_review_histories.action',
            3 => 'draft_review_histories.note',
            4 => 'campaigns.title',
            5 => 'users.name',
            6 => 'draft_review_histories.created_at',
            7 => 'draft_review_histories.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'draft_review_histories.id';

        $filters = [
            'draft_id'    => $this->request->getPost('draft_id') ?: ($this->request->getGet('draft_id') ?: null),
            'campaign_id' => $this->request->getPost('campaign_id') ?: ($this->request->getGet('campaign_id') ?: null),
            'action'      => $this->request->getPost('action') ?: ($this->request->getGet('action') ?: null),
            'acted_by'    => $this->request->getPost('acted_by') ?: ($this->request->getGet('acted_by') ?: null),
            'date_start'  => $this->request->getPost('date_start') ?: ($this->request->getGet('date_start') ?: null),
            'date_end'    => $this->request->getPost('date_end') ?: ($this->request->getGet('date_end') ?: null),
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $actVal = strtoupper(trim((string)$row['resolved_action']));
            $actionBadge = match ($actVal) {
                'APPROVED' => '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                'REVISION', 'REVISION_NEEDED' => '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-undo me-1"></i>REVISION</span>',
                'SUBMITTED', 'PENDING' => '<span class="badge bg-primary text-white px-2 py-1"><i class="fas fa-paper-plane me-1"></i>SUBMITTED</span>',
                default => '<span class="badge bg-secondary text-white px-2 py-1">' . esc($actVal) . '</span>',
            };

            $notePreview = !empty($row['resolved_note'])
                ? '<div class="text-truncate" style="max-width: 280px;" title="' . esc($row['resolved_note']) . '"><i class="fas fa-comment-dots text-secondary me-1"></i>' . esc($row['resolved_note']) . '</div>'
                : '<span class="text-muted fst-italic">-</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="hist-row-check form-check-input" value="' . $row['id'] . '">',
                'draft'    => '
                    <div class="fw-bold text-dark">
                        <a href="' . base_url('drafts/view/' . $row['draft_id']) . '" class="text-decoration-none text-dark">
                            Draft #' . $row['draft_id'] . ' (v' . ($row['draft_version'] ?? '1') . ')
                        </a>
                    </div>
                    <small class="text-muted"><i class="fas fa-tasks me-1"></i>' . esc($row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1'))) . '</small>
                ',
                'action'   => $actionBadge,
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
                        <a href="' . base_url('draft-review-histories/view/' . $row['id']) . '" class="btn btn-outline-primary" title="Detail Lengkap">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="' . base_url('draft-review-histories/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary" title="Cetak Lembar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" title="Hapus Riwayat" onclick="deleteReviewHistory(' . $row['id'] . ')">
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
     * Get single draft review history for AJAX Modal
     */
    public function getOne(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data riwayat review draft tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $hist,
        ]);
    }

    /**
     * Detail View Riwayat Review Draft Video
     */
    public function view(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('draft-review-histories'))->with('error', 'Data riwayat review draft tidak ditemukan');
        }

        $allHistories = $this->historyModel->getHistoriesByDraft($hist['draft_id']);

        $data = [
            'title'        => 'Detail Review Draft #' . $hist['draft_id'] . ' (v' . ($hist['draft_version'] ?? '1') . ')',
            'controller'   => 'DraftReviewHistories',
            'hist'         => $hist,
            'allHistories' => $allHistories,
        ];

        return view('draft_review_histories/view', $data);
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

        $msg = 'Riwayat review draft berhasil dihapus.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('draft-review-histories'))->with('message', $msg);
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
            'message' => "Berhasil menghapus {$deletedCount} catatan riwayat review draft.",
        ]);
    }

    /**
     * Export draft review histories to CSV / Excel
     */
    public function export()
    {
        $filters = [
            'draft_id'    => $this->request->getGet('draft_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'action'      => $this->request->getGet('action') ?: ($this->request->getGet('status') ?: null),
            'acted_by'    => $this->request->getGet('acted_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);
        $filename = 'draft_review_histories_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, [
            'ID Riwayat',
            'Draft ID',
            'Versi Draft',
            'Tautan Video Draft',
            'Aksi / Keputusan Review',
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
                $row['draft_id'],
                'v' . ($row['draft_version'] ?? '1'),
                $row['video_url'] ?? '-',
                $row['resolved_action'] ?? '-',
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
            'draft_id'    => $this->request->getGet('draft_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'action'      => $this->request->getGet('action') ?: ($this->request->getGet('status') ?: null),
            'acted_by'    => $this->request->getGet('acted_by') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);

        return view('draft_review_histories/export_pdf', [
            'title'     => 'Daftar Riwayat Review Video Draft (Draft Review Histories)',
            'histories' => $data,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }

    /**
     * Single draft review history PDF / Printable sheet (CI3 parity single_pdf)
     */
    public function single_pdf(int $id)
    {
        $hist = $this->historyModel->getHistoryWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('draft-review-histories'))->with('error', 'Data riwayat review tidak ditemukan');
        }

        return view('draft_review_histories/single_pdf', [
            'title'     => 'Lembar Riwayat Review - Draft #' . $hist['draft_id'] . ' (v' . ($hist['draft_version'] ?? '1') . ')',
            'hist'      => $hist,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }
}
