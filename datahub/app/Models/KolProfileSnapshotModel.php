<?php

namespace App\Models;

use CodeIgniter\Model;

class KolProfileSnapshotModel extends Model
{
    protected $table            = 'kol_profile_snapshots';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'platform_id',
        'followers',
        'avg_views',
        'avg_er',
        'snapshot_data',
        'captured_at',
    ];

    // Dates
    protected $useTimestamps = false; // Uses captured_at manually

    /**
     * Get monthly aggregated timeline for growth charts
     */
    public function getGrowthHistory(int $kolId, int $limitMonths = 6): array
    {
        return $this->select('
                kol_profile_snapshots.platform_id,
                p.name as platform_name,
                p.icon as platform_icon,
                LOWER(p.name) as platform_code,
                DATE_FORMAT(kol_profile_snapshots.captured_at, "%b %Y") as month_label,
                DATE_FORMAT(kol_profile_snapshots.captured_at, "%Y-%m") as month_key,
                AVG(kol_profile_snapshots.followers) as followers,
                AVG(kol_profile_snapshots.avg_views) as avg_views,
                AVG(kol_profile_snapshots.avg_er) as avg_er
            ')
            ->join('platforms p', 'p.id = kol_profile_snapshots.platform_id')
            ->where('kol_profile_snapshots.kol_id', $kolId)
            ->groupBy(['kol_profile_snapshots.platform_id', 'p.name', 'p.icon', 'DATE_FORMAT(kol_profile_snapshots.captured_at, "%b %Y")', 'DATE_FORMAT(kol_profile_snapshots.captured_at, "%Y-%m")'])
            ->orderBy('month_key', 'DESC')
            ->limit($limitMonths)
            ->findAll();
    }

    /**
     * Record a new snapshot point (e.g. after scraping or sync)
     */
    public function recordSnapshot(int $kolId, int $platformId, int $followers, int $avgViews, float $avgEr, array $additionalData = []): int|string
    {
        return $this->insert([
            'kol_id'        => $kolId,
            'platform_id'   => $platformId,
            'followers'     => $followers,
            'avg_views'     => $avgViews,
            'avg_er'        => $avgEr,
            'snapshot_data' => !empty($additionalData) ? json_encode($additionalData) : null,
            'captured_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
