<?php

namespace App\Models;

use CodeIgniter\Model;

class KolPlatformModel extends Model
{
    protected $table            = 'kol_platforms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'platform_id',
        'username',
        'profile_url',
        'followers_count',
        'avg_views',
        'avg_er',
        'last_scraped_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'kol_id'      => 'required|is_natural_no_zero',
        'platform_id' => 'required|is_natural_no_zero',
        'username'    => 'required|min_length[1]|max_length[100]',
    ];

    /**
     * Get platforms by KOL ID with joined platform metadata
     */
    public function getByKol(int $kolId): array
    {
        return $this->select('
                kol_platforms.*,
                p.name as platform_name,
                LOWER(p.name) as platform_code,
                p.icon as platform_icon,
                p.base_url as platform_base_url
            ')
            ->join('platforms p', 'p.id = kol_platforms.platform_id')
            ->where('kol_platforms.kol_id', $kolId)
            ->orderBy('kol_platforms.followers_count', 'DESC')
            ->findAll();
    }

    /**
     * Update scraped/synced metrics for a specific platform handle
     */
    public function updateScrapedMetrics(int $id, array $metrics): bool
    {
        $data = [
            'followers_count' => (int) ($metrics['followers_count'] ?? 0),
            'avg_views'       => (int) ($metrics['avg_views'] ?? 0),
            'avg_er'          => (float) ($metrics['avg_er'] ?? 0),
            'last_scraped_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($id, $data);
    }
}
