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
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Direct access not allowed']);
        }

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

            // Actions
            $hasData = !empty($row['response_data']);
            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Lihat Response Data (JSON)" onclick="viewJobData(' . $row['id'] . ')" ' . (!$hasData ? 'disabled' : '') . '>
                        <i class="fas fa-code"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2" title="Scrape Ulang (Re-Queue)" onclick="retryJob(' . $row['id'] . ')">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'id'         => '<span class="font-monospace text-muted">#' . $row['id'] . '</span>',
                'type'       => $typeBadge,
                'target_url' => $urlDisplay,
                'context'    => $campaignKolDisplay,
                'status'     => $statusBadge,
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
}
