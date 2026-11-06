<?php

namespace App\Controllers;

use App\Models\ApifyJobModel;
use App\Models\KolPlatformModel;

class Scraper extends BaseController
{
    protected $db;
    protected ApifyJobModel   $apifyModel;
    protected KolPlatformModel $platformModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->db            = \Config\Database::connect();
        $this->apifyModel    = new ApifyJobModel();
        $this->platformModel = new KolPlatformModel();
    }

    // ---------------------------------------------------------------
    // GET /scraper  — Dashboard Scraping Hub (Active Platforms per KOL)
    // ---------------------------------------------------------------
    public function index()
    {
        $q        = trim($this->request->getGet('q') ?? '');
        $platform = trim($this->request->getGet('platform') ?? '');
        $dateFrom = trim($this->request->getGet('date_from') ?? '');
        $dateTo   = trim($this->request->getGet('date_to') ?? '');

        $builder = $this->db->table('kol_platforms kp')
            ->select('
                kp.id,
                kp.kol_id,
                kp.platform_id,
                kp.username,
                kp.profile_url,
                kp.followers_count,
                kp.avg_views,
                kp.avg_er,
                kp.last_scraped_at,
                k.name as kol_name,
                p.name as platform_name,
                p.icon as platform_icon,
                LOWER(p.name) as platform_code,
                (SELECT COUNT(*) FROM kol_profile_snapshots WHERE kol_id = kp.kol_id AND platform_id = kp.platform_id) as total_posts
            ')
            ->join('kols k', 'k.id = kp.kol_id', 'left')
            ->join('platforms p', 'p.id = kp.platform_id', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('k.name', $q)
                ->orLike('kp.username', $q)
            ->groupEnd();
        }

        if ($platform !== '') {
            if (is_numeric($platform)) {
                $builder->where('kp.platform_id', (int)$platform);
            } else {
                $builder->like('p.name', $platform);
            }
        }

        if ($dateFrom !== '') {
            $builder->where('kp.last_scraped_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== '') {
            $builder->where('kp.last_scraped_at <=', $dateTo . ' 23:59:59');
        }

        $snapshots = $builder->orderBy('k.name', 'ASC')->get()->getResultArray();
        $totalPlatforms = $this->db->table('kol_platforms')->countAllResults();

        $allPlatforms = $this->db->table('platforms')->where('is_active', 1)->get()->getResultArray();
        $jobs         = $this->apifyModel->getRecentJobs(50);
        $summary      = $this->apifyModel->getStatusSummary();

        $data = [
            'title'        => 'Scraping Hub',
            'controller'   => 'Datahub',
            'snapshots'    => $snapshots,
            'total'        => $totalPlatforms,
            'allPlatforms' => $allPlatforms,
            'filter'       => [
                'kol_name'      => $q,
                'platform_code' => $platform,
                'date_from'     => $dateFrom,
                'date_to'       => $dateTo,
            ],
            'jobs'         => $jobs,
            'summary'      => $summary,
        ];

        return view('scraper/index', $data);
    }

    // ---------------------------------------------------------------
    // POST /scraper/bulk  — Bulk Scrape Selected Platforms
    // ---------------------------------------------------------------
    public function bulkScrape()
    {
        $platformIds = $this->request->getPost('kol_platform_ids');
        if (empty($platformIds)) {
            $singleId = $this->request->getPost('id');
            if ($singleId) {
                $platformIds = [$singleId];
            }
        }

        if (empty($platformIds) || !is_array($platformIds)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada platform KOL yang dipilih.']);
            }
            return redirect()->to(base_url('scraper'))->with('error', 'Pilih minimal satu platform KOL.');
        }

        $targets = $this->db->table('kol_platforms kp')
            ->select('kp.*, p.name as platform_name')
            ->join('platforms p', 'p.id = kp.platform_id', 'left')
            ->whereIn('kp.id', $platformIds)
            ->get()
            ->getResultArray();

        $snapshotModel = new \App\Models\KolProfileSnapshotModel();
        $now = date('Y-m-d H:i:s');
        $queuedCount = 0;

        foreach ($targets as $t) {
            $pName = strtolower($t['platform_name'] ?? '');
            $username = ltrim($t['username'] ?? '', '@');
            $targetUrl = match (true) {
                str_contains($pName, 'tiktok')    => "https://www.tiktok.com/@{$username}",
                str_contains($pName, 'instagram') => "https://www.instagram.com/{$username}/",
                str_contains($pName, 'youtube')   => "https://www.youtube.com/@{$username}",
                default                           => "https://www.{$pName}.com/{$username}",
            };

            $this->apifyModel->enqueue($targetUrl, 'profile');

            // Record snapshot in history
            $snapshotModel->recordSnapshot(
                (int)$t['kol_id'],
                (int)$t['platform_id'],
                (int)$t['followers_count'],
                (int)$t['avg_views'],
                (float)$t['avg_er'],
                ['source' => 'bulk_scrape_sync', 'triggered_at' => $now]
            );

            // Update last_scraped_at
            $this->db->table('kol_platforms')
                ->where('id', $t['id'])
                ->update(['last_scraped_at' => $now]);

            $queuedCount++;
        }

        $msg = "Berhasil memicu scraping background untuk {$queuedCount} platform KOL!";
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg, 'count' => $queuedCount]);
        }

        return redirect()->to(base_url('scraper'))->with('success', $msg);
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

