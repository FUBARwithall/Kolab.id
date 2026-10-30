<?php

namespace App\Models;

use CodeIgniter\Model;

class KolRateCardModel extends Model
{
    protected $table            = 'kol_rate_cards';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'platform_id',
        'kol_platform_id',
        'content_type',
        'rate',
        'rate_price',
        'currency_code',
        'negotiable',
        'is_active',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Canonical content types supported in the system
     */
    public static function getCanonicalTypes(): array
    {
        return [
            'Short Form Video' => [
                'desc'  => 'Video vertikal pendek: TikTok, Reels, Shorts (≤60-90 detik).',
                'badge' => 'bg-info-subtle text-info border-info-subtle',
                'icon'  => 'fas fa-video',
            ],
            'Feed / Carousel' => [
                'desc'  => 'Konten feed statis atau multi-slide carousel / swipe post.',
                'badge' => 'bg-primary-subtle text-primary border-primary-subtle',
                'icon'  => 'fas fa-images',
            ],
            'Live Streaming' => [
                'desc'  => 'Sesi interaktif / live shopping / co-host per sesi atau per jam.',
                'badge' => 'bg-danger-subtle text-danger border-danger-subtle',
                'icon'  => 'fas fa-broadcast-tower',
            ],
            'Long Form Video' => [
                'desc'  => 'Video horizontal mendalam, review & unboxing produk YouTube.',
                'badge' => 'bg-warning-subtle text-warning border-warning-subtle',
                'icon'  => 'fab fa-youtube',
            ],
        ];
    }

    /**
     * DataTables Server-Side AJAX query
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'kol_rate_cards.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_rate_cards.*,
                COALESCE(kol_rate_cards.rate, kol_rate_cards.rate_price) as final_rate,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                u.email as kol_email,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                platforms.base_url as platform_base_url,
                kp.username as platform_username,
                kp.profile_url as platform_profile_url,
                kp.followers as platform_followers
            ')
            ->join('kols', 'kols.id = kol_rate_cards.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('platforms', 'platforms.id = kol_rate_cards.platform_id', 'left')
            ->join('kol_platforms kp', 'kp.id = kol_rate_cards.kol_platform_id', 'left');

        if (!empty($filters['platform_id'])) {
            $builder->where('kol_rate_cards.platform_id', (int)$filters['platform_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_rate_cards.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['content_type'])) {
            $builder->like('kol_rate_cards.content_type', $filters['content_type']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $builder->where('kol_rate_cards.is_active', (int)$filters['is_active']);
        }

        if (isset($filters['negotiable']) && $filters['negotiable'] !== '') {
            $builder->where('kol_rate_cards.negotiable', (int)$filters['negotiable']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kols.name', $search)
                ->orLike('kol_rate_cards.content_type', $search)
                ->orLike('platforms.name', $search)
                ->orLike('kp.username', $search)
                ->orLike('kol_rate_cards.notes', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get single detail with joined KOL, Platform, and KOL Platform info
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                kol_rate_cards.*,
                COALESCE(kol_rate_cards.rate, kol_rate_cards.rate_price) as final_rate,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.approval_status as kol_status,
                u.email as kol_email,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                platforms.base_url as platform_base_url,
                kp.username as platform_username,
                kp.profile_url as platform_profile_url,
                kp.followers as platform_followers,
                kp.avg_views as platform_avg_views,
                kp.avg_er as platform_avg_er
            ')
            ->join('kols', 'kols.id = kol_rate_cards.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('platforms', 'platforms.id = kol_rate_cards.platform_id', 'left')
            ->join('kol_platforms kp', 'kp.id = kol_rate_cards.kol_platform_id', 'left')
            ->where('kol_rate_cards.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Check duplicate rate card for same KOL, Platform, and Content Type
     */
    public function checkExists(int $kolId, int $platformId, string $contentType, ?int $excludeId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('kol_id', $kolId)
            ->where('platform_id', $platformId)
            ->where('LOWER(content_type)', strtolower(trim($contentType)));

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get all rows for CSV Export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_rate_cards.id,
                kols.name as kol_name,
                platforms.name as platform_name,
                kp.username as platform_username,
                kol_rate_cards.content_type,
                COALESCE(kol_rate_cards.rate, kol_rate_cards.rate_price) as rate,
                kol_rate_cards.currency_code,
                kol_rate_cards.negotiable,
                kol_rate_cards.is_active,
                kol_rate_cards.notes,
                kol_rate_cards.created_at
            ')
            ->join('kols', 'kols.id = kol_rate_cards.kol_id', 'left')
            ->join('platforms', 'platforms.id = kol_rate_cards.platform_id', 'left')
            ->join('kol_platforms kp', 'kp.id = kol_rate_cards.kol_platform_id', 'left');

        if (!empty($filters['platform_id'])) {
            $builder->where('kol_rate_cards.platform_id', (int)$filters['platform_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_rate_cards.kol_id', (int)$filters['kol_id']);
        }

        return $builder->orderBy('kol_rate_cards.id', 'DESC')->get()->getResultArray();
    }
}
