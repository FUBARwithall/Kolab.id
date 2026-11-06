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
        'content_type',
        'rate_price',
        'negotiable',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Canonical content types supported in the system
     */
    public static function getCanonicalTypes(): array
    {
        return [
            'Short Form Video' => [
                'desc'  => 'Video vertikal pendek: TikTok, Reels, Shorts (<=60-90 detik).',
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
     * Get rate cards for a KOL with platform metadata
     */
    public function getByKol(int $kolId): array
    {
        return $this->select('
                kol_rate_cards.*,
                kol_rate_cards.rate_price as final_rate,
                p.name as platform_name,
                LOWER(p.name) as platform_code,
                p.icon as platform_icon
            ')
            ->join('platforms p', 'p.id = kol_rate_cards.platform_id', 'left')
            ->where('kol_rate_cards.kol_id', $kolId)
            ->orderBy('p.name', 'ASC')
            ->orderBy('kol_rate_cards.rate_price', 'ASC')
            ->findAll();
    }
}
