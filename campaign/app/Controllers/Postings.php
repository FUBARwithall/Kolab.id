<?php

namespace App\Controllers;

use App\Models\PostingModel;
use App\Models\PostingMetricModel;
use App\Models\TaskSentimentModel;
use App\Models\TaskModel;
use App\Models\CampaignModel;
use App\Libraries\SentimentClient;

class Postings extends BaseController
{
    protected PostingModel $postingModel;
    protected PostingMetricModel $metricModel;
    protected TaskSentimentModel $sentimentModel;
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;
    protected SentimentClient $sentimentClient;

    public function __construct()
    {
        $this->postingModel    = new PostingModel();
        $this->metricModel     = new PostingMetricModel();
        $this->sentimentModel  = new TaskSentimentModel();
        $this->taskModel       = new TaskModel();
        $this->campaignModel   = new CampaignModel();
        $this->sentimentClient = new SentimentClient();
    }

    /**
     * Halaman Utama Postings & Analytics
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $platforms = $db->table('platforms')->where('is_active', 1)->get()->getResultArray();

        $data = [
            'title'        => 'Postings & Analytics Hub — Pemantauan Performa Konten',
            'controller'   => 'Postings',
            'metrics'      => $this->postingModel->getSummaryMetrics(),
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
            'platforms'    => $platforms,
        ];

        return view('postings/index', $data);
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
            0 => 'postings.id',
            1 => 'postings.live_url',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'posting_metrics.views_count',
            5 => 'posting_metrics.likes_count',
            6 => 'posting_metrics.actual_cpv',
            7 => 'postings.posted_at',
            8 => 'postings.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'postings.id';

        $filters = [
            'campaign_id' => $this->request->getPost('campaign_id'),
            'platform_id' => $this->request->getPost('platform_id'),
        ];

        $result = $this->postingModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $views = (int)($row['views_count'] ?? 0);
            $likes = (int)($row['likes_count'] ?? 0);
            $comments = (int)($row['comments_count'] ?? 0);
            $shares = (int)($row['shares_count'] ?? 0);
            $saves = (int)($row['saves_count'] ?? 0);

            $interactions = $likes + $comments + $shares + $saves;
            $er = ($views > 0) ? round(($interactions / $views) * 100, 2) : 0.0;
            $cpv = (float)($row['actual_cpv'] ?? 0);

            // Platform icon badge
            $platformIcon = !empty($row['platform_icon']) ? '<i class="' . esc($row['platform_icon']) . ' me-1"></i>' : '<i class="fas fa-video me-1"></i>';
            $platformBadge = '<span class="badge bg-dark-subtle text-dark border px-2 py-1">' . $platformIcon . esc($row['platform_name'] ?: 'Video') . '</span>';

            // Sentiment badge
            $sentimentBadge = '';
            if (isset($row['sentiment_score']) && $row['sentiment_score'] !== null) {
                $score = (float)$row['sentiment_score'];
                if ($score >= 60) {
                    $sentimentBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle" title="Positif: ' . $score . '%"><i class="far fa-smile me-1"></i>' . $score . '%</span>';
                } elseif ($score <= 40) {
                    $sentimentBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Negatif: ' . $score . '%"><i class="far fa-frown me-1"></i>' . $score . '%</span>';
                } else {
                    $sentimentBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle" title="Netral: ' . $score . '%"><i class="far fa-meh me-1"></i>' . $score . '%</span>';
                }
            } else {
                $sentimentBadge = '<button type="button" class="btn btn-outline-secondary btn-sm p-0 px-1" style="font-size: 10px;" onclick="runSentimentAnalysis(' . $row['task_id'] . ')"><i class="fas fa-brain me-1"></i>Analisis</button>';
            }

            $formattedData[] = [
                'id'       => $row['id'],
                'url'      => '
                    <div class="fw-bold text-dark text-truncate" style="max-width: 250px;">' . esc($row['task_title'] ?: ('VT #' . $row['task_number'])) . '</div>
                    <div class="d-flex align-items-center gap-1 mt-1">
                        ' . $platformBadge . '
                        <a href="' . esc($row['live_url']) . '" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0" style="font-size: 11px;"><i class="fas fa-external-link-alt me-1"></i>Live</a>
                    </div>
                ',
                'campaign' => '<div class="fw-semibold text-primary">' . esc($row['campaign_title']) . '</div>',
                'kol'      => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">Fee: Rp ' . number_format((float)$row['rate_amount'], 0, ',', '.') . '</small>
                ',
                'views'    => '<div class="fw-bold text-dark fs-6">' . number_format($views) . '</div>',
                'er'       => '
                    <div class="fw-semibold text-success">' . $er . '% ER</div>
                    <small class="text-muted">' . number_format($likes) . ' likes &bull; ' . number_format($comments) . ' kom</small>
                ',
                'cpv'      => '<div class="fw-semibold text-dark">Rp ' . number_format($cpv, 2, ',', '.') . '</div>',
                'sentiment'=> $sentimentBadge,
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Performa" onclick="viewPostingDetail(' . $row['id'] . ')"><i class="fas fa-chart-line"></i></button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Update Metrik Aktual" onclick="updateMetricsModal(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>
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
     * Get single posting details JSON
     */
    public function getOne(int $id)
    {
        $posting = $this->postingModel->getWithDetails($id);
        if (!$posting) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Posting tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $posting]);
    }

    /**
     * Save posting live URL
     */
    public function save()
    {
        $taskId   = (int) $this->request->getPost('task_id');
        $postUrl  = trim((string)$this->request->getPost('post_url'));
        $postedAt = $this->request->getPost('posted_at') ?: date('Y-m-d H:i:s');
        $platform = strtolower(trim((string)$this->request->getPost('platform')));

        if (empty($taskId) || empty($postUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Task ID dan URL Postingan Live wajib diisi',
            ]);
        }

        $db = \Config\Database::connect();
        // Resolve platform id
        $platRow = $db->table('platforms')->like('name', $platform ?: 'tiktok')->get()->getRowArray();
        $platformId = $platRow ? (int)$platRow['id'] : 1;

        $postingData = [
            'task_id'     => $taskId,
            'platform_id' => $platformId,
            'live_url'    => $postUrl,
            'posted_at'   => $postedAt,
        ];

        $postingId = $this->postingModel->savePosting($postingData);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'URL postingan live berhasil disimpan! Status tugas otomatis berubah menjadi POSTED.',
            'posting_id' => $postingId,
        ]);
    }

    /**
     * Update metrics manually
     */
    public function updateMetrics(int $id)
    {
        $views    = (int) $this->request->getPost('views_count');
        $likes    = (int) $this->request->getPost('likes_count');
        $comments = (int) $this->request->getPost('comments_count');
        $shares   = (int) $this->request->getPost('shares_count');
        $saves    = (int) $this->request->getPost('saves_count');

        $posting = $this->postingModel->find($id);
        if (!$posting) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Posting tidak ditemukan']);
        }

        // Hitung CPV
        $task = $this->taskModel->find($posting['task_id']);
        $fee  = (float)($task['rate_amount'] ?? 0);
        $cpv  = ($views > 0 && $fee > 0) ? round($fee / $views, 4) : 0.0000;

        $existing = $this->metricModel->where('posting_id', $id)->first();
        $metricData = [
            'posting_id'      => $id,
            'views_count'     => $views,
            'likes_count'     => $likes,
            'comments_count'  => $comments,
            'shares_count'    => $shares,
            'saves_count'     => $saves,
            'actual_cpv'      => $cpv,
            'last_scraped_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->metricModel->update($existing['id'], $metricData);
        } else {
            $this->metricModel->insert($metricData);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Metrik performa postingan berhasil diperbarui!',
        ]);
    }

    /**
     * Run IndoBERT Sentiment Analysis
     */
    public function analyzeSentiment(int $taskId)
    {
        $posting = $this->postingModel->where('task_id', $taskId)->first();
        if (!$posting) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Belum ada URL postingan live untuk tugas ini']);
        }

        $platform = $this->sentimentClient->resolvePlatform(null, $posting['live_url']) ?: 'tiktok';
        $res = $this->sentimentClient->analyze($platform, $posting['live_url'], 200);

        if (!$res['success']) {
            return $this->response->setStatusCode(502)->setJSON([
                'success' => false,
                'message' => 'Gagal menjalankan analisis sentimen: ' . ($res['error'] ?? 'Layanan tidak merespons'),
            ]);
        }

        $data = $res['data'] ?? [];
        $pos = (int)($data['positive_count'] ?? ($data['positive'] ?? 0));
        $neu = (int)($data['neutral_count'] ?? ($data['neutral'] ?? 0));
        $neg = (int)($data['negative_count'] ?? ($data['negative'] ?? 0));
        $total = $pos + $neu + $neg;
        $score = ($total > 0) ? round(($pos / $total) * 100, 2) : 50.00;

        // Upsert ke task_sentiments
        $existing = $this->sentimentModel->where('task_id', $taskId)->first();
        $sentimentData = [
            'task_id'         => $taskId,
            'positive_count'  => $pos,
            'neutral_count'   => $neu,
            'negative_count'  => $neg,
            'sentiment_score' => $score,
            'analyzed_at'     => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->sentimentModel->update($existing['id'], $sentimentData);
        } else {
            $this->sentimentModel->insert($sentimentData);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Analisis sentimen IndoBERT berhasil diselesaikan!',
            'score'   => $score,
        ]);
    }
}
