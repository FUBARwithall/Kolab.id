<?php

namespace App\Models;

use CodeIgniter\Model;

class ApifyJobModel extends Model
{
    protected $table         = 'apify_jobs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'actor_type',
        'target_url',
        'status',
        'response_data',
        'started_at',
        'finished_at',
        'created_at',
    ];

    // Timestamps managed manually (no updated_at in schema)
    protected $useTimestamps  = false;

    // ---------------------------------------------------------------
    // Read helpers
    // ---------------------------------------------------------------

    /**
     * Fetch recent jobs (last N), enriched with platform + username.
     */
    public function getRecentJobs(int $limit = 50): array
    {
        $rows = $this->orderBy('id', 'DESC')->limit($limit)->findAll();
        return array_map([$this, 'enrichJob'], $rows);
    }

    /**
     * Fetch only QUEUED jobs for a background worker.
     */
    public function getQueuedJobs(int $limit = 20): array
    {
        return $this->where('status', 'QUEUED')
                    ->orderBy('id', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Stats summary: count by status.
     */
    public function getStatusSummary(): array
    {
        $rows = $this->db->query(
            "SELECT status, COUNT(*) as total FROM apify_jobs GROUP BY status"
        )->getResultArray();

        $summary = [
            'QUEUED'    => 0,
            'RUNNING'   => 0,
            'SUCCEEDED' => 0,
            'FAILED'    => 0,
        ];
        foreach ($rows as $r) {
            $summary[$r['status']] = (int) $r['total'];
        }
        $summary['total'] = array_sum($summary);
        return $summary;
    }

    // ---------------------------------------------------------------
    // Write helpers
    // ---------------------------------------------------------------

    /**
     * Queue a new scrape job.
     */
    public function enqueue(string $targetUrl, string $actorType = 'profile'): int
    {
        $this->db->table('apify_jobs')->insert([
            'actor_type'    => $actorType,
            'target_url'    => $targetUrl,
            'status'        => 'QUEUED',
            'response_data' => null,
            'started_at'    => null,
            'finished_at'   => null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->insertID();
    }

    /**
     * Update a job with result data (from webhook or polling).
     */
    public function updateJobResult(int $id, array $data): bool
    {
        return $this->db->table('apify_jobs')
                        ->where('id', $id)
                        ->update($data);
    }

    /**
     * Mark a job as RUNNING with a start timestamp.
     */
    public function markRunning(int $id): bool
    {
        return $this->updateJobResult($id, [
            'status'     => 'RUNNING',
            'started_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark a job as SUCCEEDED with result payload.
     */
    public function markSucceeded(int $id, string $responseJson): bool
    {
        return $this->updateJobResult($id, [
            'status'        => 'SUCCEEDED',
            'response_data' => $responseJson,
            'finished_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark a job as FAILED.
     */
    public function markFailed(int $id, string $reason = ''): bool
    {
        return $this->updateJobResult($id, [
            'status'        => 'FAILED',
            'response_data' => json_encode(['error' => $reason]),
            'finished_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /**
     * Detect platform and extract username from target_url.
     */
    private function enrichJob(array $job): array
    {
        $url = $job['target_url'] ?? '';

        $platform = 'other';
        if (stripos($url, 'tiktok') !== false)           $platform = 'tiktok';
        elseif (stripos($url, 'instagram') !== false)    $platform = 'instagram';
        elseif (stripos($url, 'youtube') !== false || stripos($url, 'youtu.be') !== false) $platform = 'youtube';

        $job['platform']         = $platform;
        $job['target_username']  = ltrim(basename(parse_url($url, PHP_URL_PATH) ?? $url), '@') ?: $url;

        return $job;
    }
}

