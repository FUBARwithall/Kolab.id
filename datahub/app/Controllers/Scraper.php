<?php

namespace App\Controllers;

use App\Models\ApifyJobModel;
use App\Models\KolPlatformModel;

class Scraper extends BaseController
{
    protected ApifyJobModel   $apifyModel;
    protected KolPlatformModel $platformModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->apifyModel    = new ApifyJobModel();
        $this->platformModel = new KolPlatformModel();
    }

    // ---------------------------------------------------------------
    // GET /scraper  — Dashboard Apify Scraper Engine
    // ---------------------------------------------------------------
    public function index()
    {
        $jobs    = $this->apifyModel->getRecentJobs(50);
        $summary = $this->apifyModel->getStatusSummary();

        $data = [
            'title'      => 'Apify Scraper Engine',
            'controller' => 'Datahub',
            'jobs'       => $jobs,
            'summary'    => $summary,
        ];

        return view('scraper/index', $data);
    }

    // ---------------------------------------------------------------
    // POST /scraper/run  — Enqueue Batch Jobs
    // ---------------------------------------------------------------
    public function runScraper()
    {
        $platform   = $this->request->getPost('platform') ?? 'all';
        $batchLimit = (int) ($this->request->getPost('batch_limit') ?? 10);
        $actorType  = $this->request->getPost('actor_type') ?? 'profile';

        // Validate actor_type
        if (!in_array($actorType, ['profile', 'post'])) {
            $actorType = 'profile';
        }

        $builder = $this->db->table('kol_platforms kp')
            ->select('kp.id, kp.kol_id, kp.platform_id, kp.username, p.name as platform_name')
            ->join('platforms p', 'p.id = kp.platform_id', 'inner')
            ->where('kp.username IS NOT NULL')
            ->where("kp.username != ''");

        if ($platform === 'tiktok') {
            $builder->like('p.name', 'tiktok');
        } elseif ($platform === 'instagram') {
            $builder->like('p.name', 'instagram');
        } elseif ($platform === 'youtube') {
            $builder->like('p.name', 'youtube');
        }

        $targets     = $builder->limit($batchLimit)->get()->getResultArray();
        $queuedCount = 0;

        foreach ($targets as $t) {
            $platformName = strtolower($t['platform_name']);
            $username     = ltrim($t['username'] ?? '', '@');

            // Build canonical URL per platform
            $targetUrl = match (true) {
                str_contains($platformName, 'tiktok')    => "https://www.tiktok.com/@{$username}",
                str_contains($platformName, 'instagram') => "https://www.instagram.com/{$username}/",
                str_contains($platformName, 'youtube')   => "https://www.youtube.com/@{$username}",
                default                                  => "https://www.{$platformName}.com/{$username}",
            };

            $this->apifyModel->enqueue($targetUrl, $actorType);
            $queuedCount++;
        }

        return redirect()->to(base_url('scraper'))
                         ->with('success', "Berhasil menambahkan {$queuedCount} job scraping ke antrean!");
    }

    // ---------------------------------------------------------------
    // GET /scraper/job-status/(:num)  — AJAX: single job status
    // ---------------------------------------------------------------
    public function jobStatus(int $id)
    {
        $job = $this->apifyModel->find($id);

        if (!$job) {
            return $this->response->setStatusCode(404)
                                  ->setJSON(['error' => 'Job not found']);
        }

        return $this->response->setJSON([
            'id'          => $job['id'],
            'status'      => $job['status'],
            'started_at'  => $job['started_at'],
            'finished_at' => $job['finished_at'],
        ]);
    }

    // ---------------------------------------------------------------
    // POST /scraper/webhook/(:segment)  — Apify webhook callback
    // ---------------------------------------------------------------
    public function webhook(string $token = '')
    {
        // Validate shared token to prevent unauthorized pushes
        $expectedToken = env('KOL_SHARED_SECRET', 'kolab-ci4-shared-secret-key-2026');
        if ($token !== md5($expectedToken)) {
            return $this->response->setStatusCode(403)
                                  ->setJSON(['error' => 'Unauthorized']);
        }

        $body = $this->request->getBody();
        $payload = json_decode($body, true);

        if (!$payload || empty($payload['job_id'])) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(['error' => 'Invalid payload']);
        }

        $jobId   = (int) $payload['job_id'];
        $status  = strtoupper($payload['status'] ?? 'SUCCEEDED');
        $results = $payload['results'] ?? [];

        if ($status === 'SUCCEEDED' && !empty($results)) {
            $this->apifyModel->markSucceeded($jobId, json_encode($results));

            // Write snapshot for each result if it has metrics
            $snapshotModel = new \App\Models\KolProfileSnapshotModel();
            foreach ($results as $r) {
                if (!empty($r['kol_id']) && !empty($r['platform_id'])) {
                    $snapshotModel->recordSnapshot(
                        (int) $r['kol_id'],
                        (int) $r['platform_id'],
                        (int) ($r['followers'] ?? 0),
                        (int) ($r['avg_views']  ?? 0),
                        (float) ($r['avg_er']   ?? 0),
                        $r
                    );
                }
            }
        } else {
            $this->apifyModel->markFailed($jobId, $payload['error'] ?? 'Unknown error');
        }

        return $this->response->setJSON(['ok' => true]);
    }
}

