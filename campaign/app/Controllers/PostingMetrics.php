<?php

namespace App\Controllers;

use App\Models\PostingMetricModel;
use App\Models\CampaignModel;

class PostingMetrics extends BaseController
{
    protected PostingMetricModel $metricModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->metricModel = new PostingMetricModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Posting Metrics (Snapshot Performa Video Live)
     */
    public function index($offset = 0)
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'posting_id'  => $this->request->getGet('posting_id') ?: null,
        ];

        $metrics = $this->metricModel->getSummaryMetrics($filters);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();

        $db = \Config\Database::connect();
        $allPostings = $db->table('postings')
            ->select('postings.id, postings.live_url, campaigns.title as campaign_title, kols.name as kol_name')
            ->join('tasks', 'tasks.id = postings.task_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->where('postings.live_url IS NOT NULL')
            ->where('postings.live_url !=', '')
            ->orderBy('postings.id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $data = [
            'title'        => 'Posting Metrics — Histori Snapshot Performa Video KOL',
            'controller'   => 'PostingMetrics',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'allPostings'  => $allPostings,
        ];

        return view('posting_metrics/index', $data);
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
            0 => 'posting_metrics.id',
            1 => 'postings.live_url',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'posting_metrics.views',
            5 => 'posting_metrics.likes',
            6 => 'posting_metrics.comments',
            7 => 'posting_metrics.shares',
            8 => 'posting_metrics.engagement_rate',
            9 => 'posting_metrics.cpv',
            10 => 'posting_metrics.snapshot_at',
            11 => 'posting_metrics.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'posting_metrics.id';

        $filters = [
            'campaign_id' => $this->request->getPost('campaign_id') ?: ($this->request->getGet('campaign_id') ?: null),
            'posting_id'  => $this->request->getPost('posting_id') ?: ($this->request->getGet('posting_id') ?: null),
        ];

        $result = $this->metricModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $viewsVal    = (int)($row['resolved_views'] ?? 0);
            $likesVal    = (int)($row['resolved_likes'] ?? 0);
            $commentsVal = (int)($row['resolved_comments'] ?? 0);
            $sharesVal   = (int)($row['resolved_shares'] ?? 0);
            $savesVal    = (int)($row['resolved_saves'] ?? 0);
            $erVal       = (float)($row['engagement_rate'] ?? 0);
            $cpvVal      = (float)($row['resolved_cpv'] ?? 0);

            $erBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold">' . number_format($erVal, 2) . '%</span>';
            if ($erVal < 1.0) {
                $erBadge = '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">' . number_format($erVal, 2) . '%</span>';
            } elseif ($erVal > 5.0) {
                $erBadge = '<span class="badge bg-primary text-white px-2 py-1 fw-bold"><i class="fas fa-fire me-1"></i>' . number_format($erVal, 2) . '%</span>';
            }

            $liveUrlShort = !empty($row['postings_live_url']) ? esc(substr($row['postings_live_url'], 0, 45)) . (strlen($row['postings_live_url']) > 45 ? '...' : '') : '-';

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="metric-row-check form-check-input" value="' . $row['id'] . '">',
                'posting'  => '
                    <div><a href="' . esc($row['postings_live_url'] ?? '#') . '" target="_blank" class="fw-bold text-decoration-none text-primary"><i class="fas fa-external-link-alt me-1"></i>' . $liveUrlShort . '</a></div>
                    <small class="text-muted">Posting ID: #' . ($row['posting_id'] ?? '-') . ' &bull; Platform: ' . esc($row['platform_name'] ?? 'TikTok') . '</small>
                ',
                'campaign' => '
                    <div class="fw-semibold text-dark">' . esc($row['campaign_title'] ?? 'Campaign') . '</div>
                    <small class="text-muted"><i class="fas fa-tasks me-1"></i>' . esc($row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1'))) . '</small>
                ',
                'kol'      => '<div class="fw-bold text-dark">' . esc($row['kol_name'] ?? 'KOL') . '</div>',
                'views'    => '<div class="fw-bold text-dark">' . number_format($viewsVal, 0, ',', '.') . '</div>',
                'likes'    => '<div class="text-secondary">' . number_format($likesVal, 0, ',', '.') . '</div>',
                'comments' => '<div class="text-secondary">' . number_format($commentsVal, 0, ',', '.') . '</div>',
                'shares'   => '<div class="text-secondary">' . number_format($sharesVal, 0, ',', '.') . '</div>',
                'saves'    => '<div class="text-secondary">' . number_format($savesVal, 0, ',', '.') . '</div>',
                'er'       => $erBadge,
                'cpv'      => '<div class="fw-semibold text-dark">Rp ' . number_format($cpvVal, 2, ',', '.') . '</div>',
                'snapshot' => '<div>' . date('d M Y', strtotime($row['snapshot_at'] ?: $row['created_at'])) . '</div><small class="text-muted">' . date('H:i', strtotime($row['snapshot_at'] ?: $row['created_at'])) . ' WIB</small>',
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Snapshot" onclick="viewMetricModal(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        <a href="' . base_url('posting-metrics/view/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Halaman Detail"><i class="fas fa-external-link-alt"></i></a>
                        <a href="' . base_url('posting-metrics/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak / PDF Single"><i class="fas fa-print"></i></a>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Snapshot" onclick="deleteMetric(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
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
     * Get single metric snapshot details
     */
    public function getOne(int $id)
    {
        $metric = $this->metricModel->getWithDetails($id);
        if (!$metric) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data metric tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $metric]);
    }

    /**
     * Standalone detail view page
     */
    public function view(int $id)
    {
        $metric = $this->metricModel->getWithDetails($id);
        if (!$metric) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data metric tidak ditemukan']);
            }
            return redirect()->to(base_url('posting-metrics'))->with('error', 'Data metric tidak ditemukan');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'data' => $metric]);
        }

        $allSnapshots = $this->metricModel->getSnapshots((int)$metric['posting_id']);

        $data = [
            'title'        => 'Detail Metric Snapshot #' . $metric['id'],
            'controller'   => 'PostingMetrics',
            'metric'       => $metric,
            'allSnapshots' => $allSnapshots,
        ];

        return view('posting_metrics/view', $data);
    }

    /**
     * Delete single metric snapshot
     */
    public function delete($id = null)
    {
        $targetId = $id ?: ($this->request->getGet('id') ?: $this->request->getPost('id'));
        if (empty($targetId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID metric tidak diberikan']);
            }
            return redirect()->back()->with('error', 'ID metric tidak valid');
        }

        if (is_array($targetId)) {
            return $this->bulkDelete();
        }

        $metric = $this->metricModel->find($targetId);
        if (!$metric) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data metric tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Data metric tidak ditemukan');
        }

        $this->metricModel->delete($targetId);

        $msg = 'Snapshot performa posting berhasil dihapus.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('posting-metrics'))->with('message', $msg);
    }

    /**
     * Bulk delete metric snapshots
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids') ?: $this->request->getGet('id');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tidak ada metric yang dipilih untuk dihapus',
            ]);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if (!empty($id)) {
                $this->metricModel->delete($id);
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Berhasil menghapus {$deletedCount} snapshot metric.",
        ]);
    }

    /**
     * Export posting metrics to CSV / Excel
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'posting_id'  => $this->request->getGet('posting_id') ?: null,
        ];

        $data = $this->metricModel->getAllForExport($filters);
        $filename = 'posting_metrics_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, [
            'ID Snapshot',
            'Posting ID',
            'Live URL',
            'Platform',
            'Kampanye',
            'Talent KOL',
            'Task',
            'Tanggal Snapshot',
            'Views',
            'Likes',
            'Comments',
            'Shares',
            'Saves',
            'Followers',
            'Impressions',
            'Reach',
            'Engagement Rate (%)',
            'View Rate (%)',
            'CPV (IDR)',
            'CPA (IDR)',
            'Average Views',
            'Median Views',
            'Tanggal Dibuat'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['posting_id'],
                $row['postings_live_url'] ?: '-',
                $row['platform_name'] ?: 'TikTok',
                $row['campaign_title'] ?: '-',
                $row['kol_name'] ?: '-',
                $row['task_title'] ?: ('VT #' . ($row['task_number'] ?? '1')),
                $row['snapshot_at'] ?: $row['created_at'],
                (int)($row['resolved_views'] ?? 0),
                (int)($row['resolved_likes'] ?? 0),
                (int)($row['resolved_comments'] ?? 0),
                (int)($row['resolved_shares'] ?? 0),
                (int)($row['resolved_saves'] ?? 0),
                (int)($row['followers'] ?? 0),
                (int)($row['impressions'] ?? 0),
                (int)($row['reach'] ?? 0),
                number_format((float)($row['engagement_rate'] ?? 0), 2) . '%',
                number_format((float)($row['view_rate'] ?? 0), 2) . '%',
                number_format((float)($row['resolved_cpv'] ?? 0), 2),
                number_format((float)($row['cpa'] ?? 0), 2),
                number_format((float)($row['average_views'] ?? 0), 2),
                number_format((float)($row['median_views'] ?? 0), 2),
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
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'posting_id'  => $this->request->getGet('posting_id') ?: null,
        ];

        $data = $this->metricModel->getAllForExport($filters);

        return view('posting_metrics/export_pdf', [
            'title'     => 'Daftar Snapshot Posting Metrics',
            'metrics'   => $data,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }

    /**
     * Export / Print single metric snapshot (CI3 parity single_pdf)
     */
    public function single_pdf(int $id)
    {
        $metric = $this->metricModel->getWithDetails($id);
        if (!$metric) {
            return redirect()->to(base_url('posting-metrics'))->with('error', 'Metric tidak ditemukan');
        }

        return view('posting_metrics/single_pdf', [
            'title'     => 'Metric Snapshot #' . $metric['id'] . ' - Posting #' . $metric['posting_id'],
            'metric'    => $metric,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }
}
