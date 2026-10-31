<?php

namespace App\Controllers;

use App\Models\BriefContentHistoryModel;
use App\Models\BriefModel;
use App\Models\CampaignModel;

class BriefContentHistories extends BaseController
{
    protected BriefContentHistoryModel $historyModel;
    protected BriefModel $briefModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->historyModel = new BriefContentHistoryModel();
        $this->briefModel   = new BriefModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Riwayat Snapshot Konten Brief
     */
    public function index($offset = 0)
    {
        $filters = [
            'brief_id'    => $this->request->getGet('brief_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
        ];

        $metrics = $this->historyModel->getSummaryMetrics($filters);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();

        $allBriefs = $this->briefModel->select('id, title, task_id')->orderBy('id', 'DESC')->findAll();

        $data = [
            'title'        => 'Brief Content Histories — Riwayat Snapshot Versi Konten Brief',
            'controller'   => 'BriefContentHistories',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'allBriefs'    => $allBriefs,
        ];

        return view('brief_content_histories/index', $data);
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
            0 => 'brief_content_histories.id',
            1 => 'briefs.title',
            2 => 'brief_content_histories.version',
            3 => 'brief_content_histories.title',
            4 => 'campaigns.title',
            5 => 'brief_content_histories.created_by',
            6 => 'brief_content_histories.snapshot_at',
            7 => 'brief_content_histories.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'brief_content_histories.id';

        $filters = [
            'brief_id'    => $this->request->getPost('brief_id') ?: ($this->request->getGet('brief_id') ?: null),
            'campaign_id' => $this->request->getPost('campaign_id') ?: ($this->request->getGet('campaign_id') ?: null),
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $versionBadge = '<span class="badge bg-primary px-2 py-1"><i class="fas fa-code-branch me-1"></i>v' . ($row['version'] ?? 1) . '</span>';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="hist-row-check form-check-input" value="' . $row['id'] . '">',
                'brief'    => '
                    <div class="fw-bold text-dark"><a href="' . base_url('briefs/view/' . $row['brief_id']) . '" class="text-decoration-none text-dark">' . esc($row['briefs_title'] ?? ('Brief #' . $row['brief_id'])) . '</a></div>
                    <small class="text-muted"><i class="fas fa-tasks me-1"></i>Task: ' . esc($row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1'))) . '</small>
                ',
                'version'  => $versionBadge,
                'title'    => '
                    <div class="fw-semibold text-dark">' . esc($row['title'] ?: ($row['briefs_title'] ?? '-')) . '</div>
                    ' . (!empty($row['content_diff']) ? '<small class="text-danger"><i class="fas fa-comment-alt me-1"></i>Catatan: ' . esc(substr($row['content_diff'], 0, 40)) . '...</small>' : '') . '
                ',
                'campaign' => '
                    <div class="fw-semibold text-primary">' . esc($row['campaign_title'] ?? '-') . '</div>
                    <small class="text-secondary"><i class="fas fa-user-circle me-1"></i>' . esc($row['kol_name'] ?? 'KOL') . '</small>
                ',
                'author'   => '<div class="fw-semibold text-dark"><i class="fas fa-user-edit me-1 text-secondary"></i>' . esc($row['author_name'] ?? 'Editor') . '</div>',
                'snapshot' => '<div>' . date('d M Y', strtotime($row['snapshot_at'] ?: $row['created_at'])) . '</div><small class="text-muted">' . date('H:i', strtotime($row['snapshot_at'] ?: $row['created_at'])) . ' WIB</small>',
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Snapshot" onclick="viewHistModal(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        <a href="' . base_url('brief-content-histories/view/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Halaman Detail"><i class="fas fa-external-link-alt"></i></a>
                        <a href="' . base_url('brief-content-histories/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak / PDF"><i class="fas fa-print"></i></a>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Snapshot" onclick="deleteHistory(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
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
     * Get single brief content history details
     */
    public function getOne(int $id)
    {
        $hist = $this->historyModel->getWithDetails($id);
        if (!$hist) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data snapshot histori tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $hist]);
    }

    /**
     * Standalone detail view page
     */
    public function view(int $id)
    {
        $hist = $this->historyModel->getWithDetails($id);
        if (!$hist) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data snapshot histori tidak ditemukan']);
            }
            return redirect()->to(base_url('brief-content-histories'))->with('error', 'Data snapshot histori tidak ditemukan');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'data' => $hist]);
        }

        $data = [
            'title'      => 'Detail Snapshot Versi ' . $hist['version'] . ' — ' . $hist['title'],
            'controller' => 'BriefContentHistories',
            'hist'       => $hist,
        ];

        return view('brief_content_histories/view', $data);
    }

    /**
     * Delete single history snapshot
     */
    public function delete($id = null)
    {
        $targetId = $id ?: ($this->request->getGet('id') ?: $this->request->getPost('id'));
        if (empty($targetId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID snapshot tidak diberikan']);
            }
            return redirect()->back()->with('error', 'ID snapshot tidak valid');
        }

        if (is_array($targetId)) {
            return $this->bulkDelete();
        }

        $hist = $this->historyModel->find($targetId);
        if (!$hist) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data snapshot tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Data snapshot tidak ditemukan');
        }

        $this->historyModel->delete($targetId);

        $msg = 'Snapshot histori konten brief berhasil dihapus.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('brief-content-histories'))->with('message', $msg);
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
                'message' => 'Tidak ada snapshot yang dipilih untuk dihapus',
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
            'message' => "Berhasil menghapus {$deletedCount} snapshot histori konten.",
        ]);
    }

    /**
     * Export brief content histories to CSV / Excel
     */
    public function export()
    {
        $filters = [
            'brief_id'    => $this->request->getGet('brief_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);
        $filename = 'brief_content_histories_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, [
            'ID Snapshot',
            'Brief ID',
            'Judul Brief',
            'Versi',
            'Judul Snapshot',
            'Isi Konten Snapshot',
            'Catatan Revisi / Diff',
            'Kampanye',
            'Talent KOL',
            'Task',
            'Dibuat Oleh',
            'Waktu Snapshot',
            'Tanggal Dibuat'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['brief_id'],
                $row['briefs_title'] ?? '-',
                'v' . ($row['version'] ?? 1),
                $row['title'] ?? '-',
                $row['content'] ?? '-',
                $row['content_diff'] ?? '-',
                $row['campaign_title'] ?? '-',
                $row['kol_name'] ?? '-',
                $row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1')),
                $row['author_name'] ?? 'Editor',
                $row['snapshot_at'] ?: '-',
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
        ];

        $data = $this->historyModel->getAllForExport($filters);

        return view('brief_content_histories/export_pdf', [
            'title'     => 'Daftar Snapshot Versi Konten Brief',
            'histories' => $data,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }

    /**
     * Single brief content history PDF / Printable sheet (CI3 parity single_pdf)
     */
    public function single_pdf(int $id)
    {
        $hist = $this->historyModel->getWithDetails($id);
        if (!$hist) {
            return redirect()->to(base_url('brief-content-histories'))->with('error', 'Data snapshot tidak ditemukan');
        }

        return view('brief_content_histories/single_pdf', [
            'title'     => 'Snapshot v' . $hist['version'] . ' - Brief #' . $hist['brief_id'],
            'hist'      => $hist,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }
}
