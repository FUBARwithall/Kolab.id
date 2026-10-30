<?php

namespace App\Models;

use CodeIgniter\Model;

class PostingMetricModel extends Model
{
    protected $table            = 'posting_metrics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'posting_id',
        'snapshot_at',
        'views',
        'likes',
        'comments',
        'shares',
        'saves',
        'followers',
        'engagement_rate',
        'cpv',
        'cpe',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'saves_count',
        'actual_cpv',
        'last_scraped_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get all historical snapshots for a posting (ordered chronologically)
     */
    public function getSnapshots(int $postingId): array
    {
        return $this->where('posting_id', $postingId)
            ->orderBy('snapshot_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
