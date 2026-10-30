<?php

namespace App\Controllers;

use App\Models\ApifyJobModel;
use App\Models\CampaignModel;

class ScrapingJobs extends BaseController
{
    protected ApifyJobModel $jobModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->jobModel = new ApifyJobModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Monitoring Scraping & Apify Jobs
     */
    public function index()
    {
        $stats = $this->jobModel->getSummaryStats();

        // Get unassigned/active postings for trigger modal
        $db = \Config\Database::connect();
        $postings = $db->table('postings')
            ->select('postings.id, postings.live_url, kols.name as kol_name, campaigns.title as campaign_title')
            ->join('tasks', 'tasks.id = postings.task_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('postings.id', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Monitoring Scraping & Apify Jobs',
            'controller' => 'ScrapingJobs',
            'stats'      => $stats,
            'postings'   => $postings,
        ];

        return view('scraping_jobs/index', $data);
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
            0 => 'apify_jobs.id',
            1 => 'apify_jobs.actor_type',
            2 => 'apify_jobs.target_url',
            3 => 'campaigns.title',
            4 => 'apify_jobs.status',
            5 => 'apify_jobs.created_at',
            6 => 'apify_jobs.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'apify_jobs.id';

        $filters = [
            'status'     => $this->request->getPost('status') ?: null,
            'actor_type' => $this->request->getPost('actor_type') ?: null,
        ];

        $result = $this->jobModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            // Status badge
            switch ($row['status']) {
                case 'SUCCEEDED':
                    $statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>SUCCEEDED</span>';
                    break;
                case 'RUNNING':
                    $statusBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><span class="spinner-border spinner-border-sm me-1" role="status"></span>RUNNING</span>';
                    break;
                case 'FAILED':
                    $statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>FAILED</span>';
                    break;
                case 'QUEUED':
                default:
                    $statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>QUEUED</span>';
                    break;
            }

            // Actor Type badge
            $typeBadge = ($row['actor_type'] === 'post')
                ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-play me-1"></i>Video Post</span>'
                : '<span class="badge bg-purple-subtle text-purple border border-purple-subtle px-2 py-1" style="background-color:#ede7f6;color:#673ab7;border-color:#d1c4e9;"><i class="fas fa-user-circle me-1"></i>Profile KOL</span>';

            // Target URL formatting with platform icons
            $platformIcon = '<i class="fas fa-link text-muted me-1"></i>';
            if (stripos($row['target_url'], 'tiktok.com') !== false) {
                $platformIcon = '<i class="fab fa-tiktok text-dark me-1"></i>';
            } elseif (stripos($row['target_url'], 'instagram.com') !== false) {
                $platformIcon = '<i class="fab fa-instagram text-danger me-1"></i>';
            } elseif (stripos($row['target_url'], 'youtu') !== false) {
                $platformIcon = '<i class="fab fa-youtube text-danger me-1"></i>';
            }

            $urlDisplay = '
                <div class="d-flex align-items-center">
                    ' . $platformIcon . '
                    <a href="' . esc($row['target_url']) . '" target="_blank" class="text-decoration-none font-monospace text-truncate d-inline-block text-primary" style="max-width: 320px;" title="' . esc($row['target_url']) . '">
                        ' . esc($row['target_url']) . '
                    </a>
                </div>
            ';

            // Campaign & KOL info
            $campaignKolDisplay = (!empty($row['kol_name']) || !empty($row['campaign_title']))
                ? '<div class="fw-semibold text-dark">' . esc($row['kol_name'] ?? 'KOL') . '</div><small class="text-muted">' . esc($row['campaign_title'] ?? '-') . '</small>'
                : '<span class="text-muted fst-italic">Standalone Target</span>';

            if (!empty($row['posting_id'])) {
                $campaignKolDisplay .= ' <span class="badge bg-light text-secondary border px-1" style="font-size:10px;">Post #' . $row['posting_id'] . '</span>';
            }

            // Status display with error snippet if any
            $statusCol = $statusBadge;
            if (!empty($row['error_message']) && $row['status'] === 'FAILED') {
                $statusCol .= '<div class="text-danger small mt-1 text-truncate" style="max-width: 170px; font-size: 11px;" title="' . esc($row['error_message']) . '"><i class="fas fa-exclamation-circle me-1"></i>' . esc($row['error_message']) . '</div>';
            }

            // Run ID & Attempt sub-label
            $idDisplay = '<div class="fw-bold font-monospace text-dark">#' . $row['id'] . '</div>';
            if (!empty($row['external_run_id'])) {
                $idDisplay .= '<span class="badge bg-light text-muted border font-monospace mt-1" style="font-size: 10px;" title="External Run ID">' . esc(substr($row['external_run_id'], 0, 16)) . '</span>';
            }
            if (!empty($row['attempt_number']) && $row['attempt_number'] > 1) {
                $idDisplay .= ' <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 10px;" title="Percobaan ke-' . $row['attempt_number'] . '">#' . $row['attempt_number'] . '</span>';
            }

            // Actions
            $hasData = !empty($row['response_data']);
            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . site_url('scraping-jobs/view/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Lihat Detail Job">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Lihat Response Data (JSON)" onclick="viewJobData(' . $row['id'] . ')" ' . (!$hasData ? 'disabled' : '') . '>
                        <i class="fas fa-code"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2" title="Scrape Ulang (Re-Queue)" onclick="retryJob(' . $row['id'] . ')">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Job" onclick="deleteJob(' . $row['id'] . ')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'id'         => $idDisplay,
                'type'       => $typeBadge,
                'target_url' => $urlDisplay,
                'context'    => $campaignKolDisplay,
                'status'     => $statusCol,
                'created_at' => '<span class="text-muted small">' . date('d M Y H:i', strtotime($row['created_at'])) . '</span>',
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
     * Get single job record
     */
    public function getOne(int $id)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Job tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $job]);
    }

    /**
     * Re-queue / Retry scraping job
     */
    public function retry(int $id)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Job tidak ditemukan']);
        }

        $this->jobModel->update($id, [
            'status'      => 'QUEUED',
            'started_at'  => null,
            'finished_at' => null,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Job #' . $id . ' berhasil dimasukkan kembali ke antrian (QUEUED)']);
    }

    /**
     * Trigger new scraping job
     */
    public function triggerNew()
    {
        $targetUrl = trim((string)$this->request->getPost('target_url'));
        $actorType = $this->request->getPost('actor_type') ?? 'post';
        $postingId = (int)$this->request->getPost('posting_id') ?: null;

        if (empty($targetUrl) || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Harap masukkan URL target yang valid']);
        }

        $jobId = $this->jobModel->insert([
            'posting_id'    => $postingId,
            'actor_type'    => in_array($actorType, ['post', 'profile']) ? $actorType : 'post',
            'target_url'    => $targetUrl,
            'status'        => 'QUEUED',
            'response_data' => null,
            'started_at'    => null,
            'finished_at'   => null,
            'created_at'    => date('Y-m-d H:i:s'),
        ], true);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Job scraping #' . $jobId . ' berhasil dibuat dan masuk antrian!',
            'job_id'  => $jobId,
        ]);
    }

    /**
     * View dedicated scraping job detail (CI3 Parity)
     */
    public function view(int $id)
    {
        $job = $this->jobModel->getJobDetail($id);
        if (!$job) {
            return redirect()->to(base_url('scraping-jobs'))->with('error', 'Job #' . $id . ' tidak ditemukan');
        }

        $formattedJson = null;
        if (!empty($job['response_data'])) {
            $decoded = json_decode($job['response_data'], true);
            $formattedJson = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $job['response_data'];
        }

        $data = [
            'title'         => 'Detail Apify Scraping Job #' . $id,
            'controller'    => 'ScrapingJobs',
            'job'           => $job,
            'formattedJson' => $formattedJson,
        ];

        return view('scraping_jobs/view', $data);
    }

    /**
     * Delete single job (CI3 Parity)
     */
    public function delete(int $id)
    {
        $job = $this->jobModel->find($id);
        if (!$job) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Job #' . $id . ' tidak ditemukan']);
            }
            return redirect()->to(base_url('scraping-jobs'))->with('error', 'Job #' . $id . ' tidak ditemukan');
        }

        $this->jobModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Job #' . $id . ' berhasil dihapus']);
        }

        return redirect()->to(base_url('scraping-jobs'))->with('success', 'Job #' . $id . ' berhasil dihapus');
    }

    /**
     * Bulk delete jobs
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih setidaknya satu job untuk dihapus']);
        }

        $cleanIds = array_map('intval', $ids);
        $this->jobModel->whereIn('id', $cleanIds)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($cleanIds) . ' job berhasil dihapus',
        ]);
    }

    /**
     * Export jobs to CSV (CI3 Parity with XLS/CSV export)
     */
    public function export()
    {
        $status = $this->request->getGet('status') ?: null;
        $jobs = $this->jobModel->getAllJobsForExport(['status' => $status]);

        $filename = 'apify_jobs_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Job ID',
            'Posting ID',
            'Posting Live URL',
            'External Run ID',
            'Attempt Number',
            'Actor Type',
            'Target URL',
            'Status',
            'Error Message',
            'Campaign Title',
            'KOL Name',
            'Started At',
            'Finished At',
            'Created At',
        ]);

        foreach ($jobs as $j) {
            fputcsv($output, [
                $j['id'],
                $j['posting_id'] ?? '',
                $j['postings_live_url'] ?? '',
                $j['external_run_id'] ?? '',
                $j['attempt_number'] ?? 1,
                $j['actor_type'] ?? '',
                $j['target_url'] ?? '',
                $j['status'] ?? '',
                $j['error_message'] ?? '',
                $j['campaign_title'] ?? '',
                $j['kol_name'] ?? '',
                $j['started_at'] ?? '',
                $j['finished_at'] ?? '',
                $j['created_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }
}
