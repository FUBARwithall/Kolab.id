<?php

namespace App\Models;

use CodeIgniter\Model;

class KolModel extends Model
{
    protected $table            = 'kols';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tier_id',
        'user_id',
        'name',
        'nik',
        'whatsapp',
        'phone',
        'email',
        'avatar_url',
        'city',
        'address',
        'postal_code',
        'bank_name',
        'bank_account',
        'bank_holder',
        'npwp',
        'npwp_file',
        'id_card_file',
        'approval_status',
        'is_blacklisted',
        'status',
        'blacklist_reason',
        'notes',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name'            => 'required|min_length[3]|max_length[150]',
        'whatsapp'        => 'permit_empty|min_length[8]|max_length[50]',
        'email'           => 'permit_empty|valid_email|max_length[150]',
        'approval_status' => 'in_list[PENDING,APPROVED,REJECTED]',
        'status'          => 'in_list[ACTIVE,INACTIVE,PENDING]',
    ];

    /**
     * Determine tier based on follower count
     */
    public function getTierByFollowers(int $followers): array
    {
        $tier = $this->db->table('tiers')
            ->where('min_followers <=', $followers)
            ->where('max_followers >=', $followers)
            ->get()
            ->getRowArray();

        if (!$tier) {
            if ($followers >= 1000000) {
                $tier = ['id' => 5, 'name' => 'Mega', 'description' => 'Selebriti / influencer nasional (>1M followers)'];
            } elseif ($followers >= 500000) {
                $tier = ['id' => 4, 'name' => 'Macro', 'description' => 'Influencer skala makro (500K - 1M)'];
            } elseif ($followers >= 100000) {
                $tier = ['id' => 3, 'name' => 'Mid-tier', 'description' => 'Influencer menengah (100K - 500K)'];
            } elseif ($followers >= 10000) {
                $tier = ['id' => 2, 'name' => 'Micro', 'description' => 'Influencer mikro (10K - 100K)'];
            } else {
                $tier = ['id' => 1, 'name' => 'Nano', 'description' => 'KOL skala nano/niche (<10K)'];
            }
        }

        return $tier;
    }

    /**
     * Get comprehensive single KOL profile details
     */
    public function getKolDetail(int $id): ?array
    {
        $kol = $this->where('id', $id)->first();
        if (!$kol) {
            return null;
        }

        // 1. Categories
        $kol['categories'] = $this->db->table('kol_categories kc')
            ->select('c.id, c.name, c.slug')
            ->join('categories c', 'c.id = kc.category_id')
            ->where('kc.kol_id', $id)
            ->get()
            ->getResultArray();

        // 2. Connected Platforms & metrics
        $kol['platforms'] = $this->db->table('kol_platforms kp')
            ->select('kp.*, p.name as platform_name, p.icon as platform_icon, LOWER(p.name) as platform_code, p.base_url')
            ->join('platforms p', 'p.id = kp.platform_id')
            ->where('kp.kol_id', $id)
            ->orderBy('kp.followers_count', 'DESC')
            ->get()
            ->getResultArray();

        // 3. Rate cards
        $kol['rate_cards'] = $this->db->table('kol_rate_cards rc')
            ->select('rc.*, p.name as platform_name, p.icon as platform_icon, LOWER(p.name) as platform_code')
            ->join('platforms p', 'p.id = rc.platform_id')
            ->where('rc.kol_id', $id)
            ->orderBy('p.name', 'ASC')
            ->orderBy('rc.rate_price', 'ASC')
            ->get()
            ->getResultArray();

        // 4. Reviews & Ratings
        $kol['reviews'] = $this->db->table('kol_reviews kr')
            ->select('kr.*, c.title as campaign_title, u.name as reviewer_name')
            ->join('campaigns c', 'c.id = kr.campaign_id', 'left')
            ->join('users u', 'u.id = kr.reviewed_by', 'left')
            ->where('kr.kol_id', $id)
            ->orderBy('kr.id', 'DESC')
            ->get()
            ->getResultArray();

        // 5. Aggregated calculations
        $totalFollowers       = 0;
        $maxFollowers         = 0;
        $totalViews           = 0;
        $totalEr              = 0;
        $activePlatformsCount = 0;

        foreach ($kol['platforms'] as $p) {
            $fCount = (int) ($p['followers_count'] ?? 0);
            $totalFollowers += $fCount;
            if ($fCount > $maxFollowers) {
                $maxFollowers = $fCount;
            }
            if ((int) $p['avg_views'] > 0) {
                $totalViews += (int) $p['avg_views'];
            }
            if ((float) $p['avg_er'] > 0) {
                $totalEr += (float) $p['avg_er'];
                $activePlatformsCount++;
            }
        }

        $kol['total_followers'] = $totalFollowers;
        $kol['max_followers']   = $maxFollowers;
        $kol['avg_views']       = $totalViews;
        $kol['avg_er']          = $activePlatformsCount > 0 ? round($totalEr / $activePlatformsCount, 2) : 0;
        $kol['tier']            = $this->getTierByFollowers($maxFollowers);

        $kol['avg_rating'] = !empty($kol['reviews'])
            ? round(array_sum(array_column($kol['reviews'], 'rating')) / count($kol['reviews']), 1)
            : 0;

        return $kol;
    }

    /**
     * Get data structured specifically for the KOL Performance Card Generator
     */
    public function getPerformanceCardData(int $id): ?array
    {
        $kol = $this->getKolDetail($id);
        if (!$kol) {
            return null;
        }

        // Monthly snapshots for 6-month growth trends
        $snapshots = $this->db->table('kol_profile_snapshots s')
            ->select('
                s.platform_id,
                p.name as platform_name,
                p.icon as platform_icon,
                LOWER(p.name) as platform_code,
                DATE_FORMAT(s.captured_at, "%b %Y") as month_label,
                DATE_FORMAT(s.captured_at, "%Y-%m") as month_key,
                AVG(s.followers) as followers,
                AVG(s.avg_views) as avg_views,
                AVG(s.avg_er) as avg_er
            ')
            ->join('platforms p', 'p.id = s.platform_id')
            ->where('s.kol_id', $id)
            ->groupBy(['s.platform_id', 'p.name', 'p.icon', 'DATE_FORMAT(s.captured_at, "%b %Y")', 'DATE_FORMAT(s.captured_at, "%Y-%m")'])
            ->orderBy('month_key', 'ASC')
            ->get()
            ->getResultArray();

        // Organize timeline for charts
        $timelineMonths    = [];
        $timelineViews     = [];
        $timelineEr        = [];
        $timelineFollowers = [];

        foreach ($snapshots as $snap) {
            $m = $snap['month_label'];
            if (!isset($timelineMonths[$m])) {
                $timelineMonths[$m]    = $m;
                $timelineViews[$m]     = 0;
                $timelineEr[$m]        = 0;
                $timelineFollowers[$m] = 0;
            }
            $timelineViews[$m]     += (int) $snap['avg_views'];
            $timelineFollowers[$m] += (int) $snap['followers'];
            $timelineEr[$m]        = max($timelineEr[$m], (float) $snap['avg_er']);
        }

        $kol['chart'] = [
            'labels'    => array_values($timelineMonths),
            'views'     => array_values($timelineViews),
            'followers' => array_values($timelineFollowers),
            'er'        => array_values($timelineEr),
        ];

        // Format rate matrix grouped by content type
        $rateMatrix = [];
        foreach ($kol['rate_cards'] as $rc) {
            $type = $rc['content_type'] ?? 'Standard';
            $rateMatrix[$type][] = $rc;
        }
        $kol['rate_matrix'] = $rateMatrix;

        return $kol;
    }
}
