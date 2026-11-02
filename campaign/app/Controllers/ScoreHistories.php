<?php

namespace App\Controllers;

use App\Models\ScoreHistoryModel;
use App\Models\KolModel;
use App\Models\CampaignModel;

class ScoreHistories extends BaseController
{
    protected ScoreHistoryModel $historyModel;
    protected KolModel $kolModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->historyModel  = new ScoreHistoryModel();
        $this->kolModel      = new KolModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Riwayat Skor KOL
     */
    public function index($offset = 0)
    {
        $filters = [
            'kol_id'      => $this->request->getGet('kol_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'score_type'  => $this->request->getGet('score_type') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        // CI3 Search query compatibility ('q' and 'f')
        $q = trim((string)$this->request->getGet('q'));
        $f = trim((string)$this->request->getGet('f'));
        if (!empty($f) && !empty($q)) {
            if ($f === 'kol_id') {
                $filters['kol_id'] = $q;
            } elseif ($f === 'campaign_id' || $f === 'campaign_kol_id') {
                $filters['campaign_id'] = $q;
            } elseif ($f === 'score_type') {
                $filters['score_type'] = $q;
            }
        }

        $metrics      = $this->historyModel->getSummaryMetrics($filters);
        $allKols      = $this->kolModel->select('id, name')->orderBy('name', 'ASC')->findAll();
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();
        $scoreTypes   = ['COLLABORATION', 'LIFETIME'];

        $data = [
            'title'        => 'Score Histories — Riwayat & Audit Skor Performa KOL',
            'controller'   => 'ScoreHistories',
            'metrics'      => $metrics,
            'allKols'      => $allKols,
            'allCampaigns' => $allCampaigns,
            'scoreTypes'   => $scoreTypes,
            'filters'      => $filters,
            'searchQuery'  => $q,
        ];

        return view('score_histories/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Handler
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
            0 => 'score_histories.id',
            1 => 'kol_id',
            2 => 'campaign_kol_id',
            3 => 'score_type',
            4 => 'score',
            5 => 'calculated_at',
            6 => 'score_histories.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'score_histories.id';

        $filters = [
            'kol_id'      => $this->request->getPost('kol_id') ?: ($this->request->getGet('kol_id') ?: null),
            'campaign_id' => $this->request->getPost('campaign_id') ?: ($this->request->getGet('campaign_id') ?: null),
            'score_type'  => $this->request->getPost('score_type') ?: ($this->request->getGet('score_type') ?: null),
            'date_start'  => $this->request->getPost('date_start') ?: ($this->request->getGet('date_start') ?: null),
            'date_end'    => $this->request->getPost('date_end') ?: ($this->request->getGet('date_end') ?: null),
        ];

        $result = $this->historyModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $scoreVal = (float)$row['score'];

            // Grade & Badge Styling based on score
            if ($scoreVal >= 85) {
                $badgeBg = 'success';
                $gradeLabel = 'A (Excellent)';
            } elseif ($scoreVal >= 70) {
                $badgeBg = 'primary';
                $gradeLabel = 'B (Good)';
            } elseif ($scoreVal >= 55) {
                $badgeBg = 'warning text-dark';
                $gradeLabel = 'C (Fair)';
            } else {
                $badgeBg = 'danger';
                $gradeLabel = 'D (Poor)';
            }

            // Type Badge
            $typeSt = strtoupper((string)$row['score_type']);
            if ($typeSt === 'COLLABORATION') {
                $typeBadge = '<span class="badge bg-info text-white font-monospace"><i class="fas fa-handshake me-1"></i>COLLABORATION</span>';
            } else {
                $typeBadge = '<span class="badge bg-purple text-white font-monospace" style="background-color: #6f42c1;"><i class="fas fa-chart-line me-1"></i>LIFETIME</span>';
            }

            // Campaign link or lifetime tag
            if (!empty($row['campaign_title'])) {
                $campaignHtml = '<div class="fw-semibold text-primary"><a href="' . base_url('campaigns/view/' . $row['campaign_id']) . '" class="text-decoration-none text-primary"><i class="fas fa-bullhorn me-1"></i>' . esc($row['campaign_title']) . '</a></div>';
                if (!empty($row['campaign_kol_id'])) {
                    $campaignHtml .= '<small class="text-muted"><i class="fas fa-id-badge me-1"></i>Partisipasi #' . esc($row['campaign_kol_id']) . '</small>';
                }
            } else {
                $campaignHtml = '<span class="badge bg-light text-secondary border"><i class="fas fa-globe me-1"></i>Overall / Lifetime</span>';
            }

            $scoreDisplay = '
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-' . $badgeBg . ' fs-6 px-2 py-1 fw-bold">' . number_format($scoreVal, 2) . '</span>
                    <small class="text-muted fw-semibold">(' . $gradeLabel . ')</small>
                </div>
                <div class="progress mt-1" style="height: 4px; width: 110px;">
                    <div class="progress-bar bg-' . (str_contains($badgeBg, 'warning') ? 'warning' : ($badgeBg === 'danger' ? 'danger' : ($badgeBg === 'primary' ? 'primary' : 'success'))) . '" role="progressbar" style="width: ' . min(100, max(0, $scoreVal)) . '%;"></div>
                </div>
            ';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="hist-row-check form-check-input" value="' . $row['id'] . '">',
                'kol'      => '
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; min-width: 34px;">
                            ' . strtoupper(substr(esc($row['kol_name'] ?? 'K'), 0, 1)) . '
                        </div>
                        <div>
                            <div class="fw-bold text-dark">
                                <a href="' . base_url('kols/view/' . $row['kol_id']) . '" class="text-decoration-none text-dark">' . esc($row['kol_name'] ?? ('KOL #' . $row['kol_id'])) . '</a>
                            </div>
                            <small class="text-muted"><i class="far fa-envelope me-1"></i>' . esc($row['kol_email'] ?? '-') . '</small>
                        </div>
                    </div>
                ',
                'campaign' => $campaignHtml,
                'score_type' => $typeBadge,
                'score'    => $scoreDisplay,
                'calculated_at' => '
                    <span class="text-dark fw-semibold">' . date('d M Y', strtotime($row['calculated_at'])) . '</span><br>
                    <small class="text-muted"><i class="far fa-clock me-1"></i>' . date('H:i:s', strtotime($row['calculated_at'])) . '</small>
                ',
                'actions'  => '
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info" title="Lihat Snapshot Modal" onclick="viewScoreSnapshot(' . $row['id'] . ')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . base_url('score-histories/view/' . $row['id']) . '" class="btn btn-outline-primary" title="Detail Lengkap">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="' . base_url('score-histories/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary" title="Cetak Lembar Audit">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" title="Hapus Riwayat" onclick="deleteScoreHistory(' . $row['id'] . ')">
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
     * Ambil 1 data skor untuk AJAX snapshot modal
     */
    public function getOne($id = null)
    {
        $id = (int) $id;
        $row = $this->historyModel->getDetail($id);

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data riwayat skor tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * Halaman Detail Lengkap Skor KOL
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $history = $this->historyModel->getDetail($id);

        if (!$history) {
            return redirect()->to(base_url('score-histories'))->with('error', 'Data riwayat skor tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Riwayat Skor KOL #' . $history['id'],
            'controller' => 'ScoreHistories',
            'history'    => $history,
        ];

        return view('score_histories/view', $data);
    }

    /**
     * Hapus single riwayat (CI3 and CI4 compatible)
     */
    public function delete($id = null)
    {
        $arrId = $this->request->getGet('id') ?: ($this->request->getPost('id') ?: null);

        if (!empty($id)) {
            $this->historyModel->delete((int)$id);
            return redirect()->to(base_url('score-histories'))->with('success', 'Riwayat skor KOL berhasil dihapus.');
        }

        if (is_array($arrId) && count($arrId) > 0) {
            foreach ($arrId as $singleId) {
                $this->historyModel->delete((int)$singleId);
            }
            return redirect()->to(base_url('score-histories'))->with('success', count($arrId) . ' riwayat skor berhasil dihapus.');
        }

        return redirect()->to(base_url('score-histories'))->with('error', 'Tidak ada riwayat skor yang dipilih untuk dihapus.');
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
                'message' => 'Pilih minimal satu data riwayat skor untuk dihapus.',
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
            'message' => $deletedCount . ' data riwayat skor berhasil dihapus.',
        ]);
    }

    /**
     * Export ke Excel / CSV
     */
    public function export()
    {
        $filters = [
            'kol_id'      => $this->request->getGet('kol_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'score_type'  => $this->request->getGet('score_type') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $data = $this->historyModel->getAllForExport($filters);

        $filename = 'score_histories_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'KOL ID',
            'KOL Name',
            'KOL Email',
            'Campaign KOL ID',
            'Campaign Title',
            'Score Type',
            'Score',
            'Calculated At',
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['kol_id'],
                $row['kol_name'] ?? '',
                $row['kol_email'] ?? '',
                $row['campaign_kol_id'] ?? '',
                $row['campaign_title'] ?? '',
                $row['score_type'] ?? '',
                $row['score'] ?? '',
                $row['calculated_at'] ?? '',
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
            'kol_id'      => $this->request->getGet('kol_id') ?: null,
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'score_type'  => $this->request->getGet('score_type') ?: null,
            'date_start'  => $this->request->getGet('date_start') ?: null,
            'date_end'    => $this->request->getGet('date_end') ?: null,
        ];

        $items = $this->historyModel->getAllForExport($filters);

        return view('score_histories/export_pdf', [
            'items'     => $items,
            'title'     => 'Laporan Riwayat Audit Skor Performa KOL',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => $filters,
        ]);
    }

    /**
     * Cetak Lembar Single PDF Bukti Audit Skor
     */
    public function single_pdf($id = null)
    {
        $id = (int) $id;
        $history = $this->historyModel->getDetail($id);

        if (!$history) {
            return redirect()->to(base_url('score-histories'))->with('error', 'Data riwayat skor tidak ditemukan.');
        }

        return view('score_histories/single_pdf', [
            'history'   => $history,
            'title'     => 'Lembar Audit Evaluasi Skor KOL #' . $history['id'],
            'generated' => date('d F Y, H:i:s'),
        ]);
    }
}
