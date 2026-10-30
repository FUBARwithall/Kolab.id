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
    protected $allowedFields    = [
        'user_id', 'name', 'nik', 'whatsapp', 'city', 'address', 'postal_code',
        'bank_name', 'bank_account', 'bank_holder', 'npwp', 'npwp_file',
        'id_card_file', 'approval_status', 'is_blacklisted', 'blacklist_reason',
        'notes'
    ];
    protected $useTimestamps    = true;

    /**
     * Determine tier based on follower count
     */
    public function getTierByFollowers(int $followers): array
    {
        $db = \Config\Database::connect();
        $tier = $db->table('tiers')
            ->where('min_followers <=', $followers)
            ->where('max_followers >=', $followers)
            ->get()
            ->getRowArray();

        if (!$tier) {
            if ($followers > 1000000) {
                $tier = ['id' => 5, 'name' => 'Mega', 'description' => 'Selebriti / influencer nasional'];
            } else {
                $tier = ['id' => 1, 'name' => 'Nano', 'description' => 'KOL skala mikro/niche'];
            }
        }
        return $tier;
    }

    /**
     * Get list of KOLs with aggregated metrics for table display
     */
    public function getKolsList(array $filters = []): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('kols k')
            ->select('
                k.id,
                k.name,
                k.whatsapp,
                k.city,
                k.approval_status,
                k.is_blacklisted,
                k.created_at,
                COALESCE(SUM(kp.followers_count), 0) as total_followers,
                COALESCE(MAX(kp.followers_count), 0) as max_followers,
                COALESCE(AVG(NULLIF(kp.avg_views, 0)), 0) as avg_views,
                COALESCE(AVG(NULLIF(kp.avg_er, 0)), 0) as avg_er,
                COUNT(DISTINCT kp.id) as platform_count,
                (SELECT GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") 
                 FROM kol_categories kc 
                 JOIN categories c ON c.id = kc.category_id 
                 WHERE kc.kol_id = k.id) as category_names,
                (SELECT ROUND(AVG(kr.rating), 1) FROM kol_reviews kr WHERE kr.kol_id = k.id) as avg_rating,
                (SELECT COUNT(kr.id) FROM kol_reviews kr WHERE kr.kol_id = k.id) as review_count,
                (SELECT COUNT(ck.id) FROM campaign_kols ck WHERE ck.kol_id = k.id) as campaign_count
            ')
            ->join('kol_platforms kp', 'kp.kol_id = k.id', 'left')
            ->where('k.deleted_at IS NULL')
            ->groupBy('k.id')
            ->orderBy('total_followers', 'DESC');

        if (!empty($filters['category_id'])) {
            $builder->where("k.id IN (SELECT kol_id FROM kol_categories WHERE category_id = " . (int)$filters['category_id'] . ")");
        }

        if (!empty($filters['status'])) {
            $builder->where('k.approval_status', $filters['status']);
        }

        if (isset($filters['is_blacklisted']) && $filters['is_blacklisted'] !== '') {
            $builder->where('k.is_blacklisted', (int)$filters['is_blacklisted']);
        }

        $rows = $builder->get()->getResultArray();

        // Attach platform details and tier for each KOL
        foreach ($rows as &$row) {
            $row['tier'] = $this->getTierByFollowers((int)$row['max_followers']);

            // Get platform icons/handles
            $row['platforms'] = $db->table('kol_platforms kp')
                ->select('kp.platform_id, kp.username, kp.followers_count, kp.avg_views, kp.avg_er, p.name as platform_name, p.icon as platform_icon')
                ->join('platforms p', 'p.id = kp.platform_id')
                ->where('kp.kol_id', $row['id'])
                ->orderBy('kp.followers_count', 'DESC')
                ->get()
                ->getResultArray();
        }

        return $rows;
    }

    /**
     * Get comprehensive single KOL profile details
     */
    public function getKolDetail(int $id): ?array
    {
        $db = \Config\Database::connect();
        $kol = $db->table('kols')
            ->where('id', $id)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();

        if (!$kol) {
            return null;
        }

        // 1. Categories
        $kol['categories'] = $db->table('kol_categories kc')
            ->select('c.id, c.name, c.slug')
            ->join('categories c', 'c.id = kc.category_id')
            ->where('kc.kol_id', $id)
            ->get()
            ->getResultArray();

        // 2. Connected Platforms & metrics
        $kol['platforms'] = $db->table('kol_platforms kp')
            ->select('kp.*, p.name as platform_name, p.icon as platform_icon, p.base_url')
            ->join('platforms p', 'p.id = kp.platform_id')
            ->where('kp.kol_id', $id)
            ->orderBy('kp.followers_count', 'DESC')
            ->get()
            ->getResultArray();

        // 3. Rate cards
        $kol['rate_cards'] = $db->table('kol_rate_cards rc')
            ->select('rc.*, p.name as platform_name, p.icon as platform_icon')
            ->join('platforms p', 'p.id = rc.platform_id')
            ->where('rc.kol_id', $id)
            ->orderBy('p.name', 'ASC')
            ->orderBy('rc.rate_price', 'ASC')
            ->get()
            ->getResultArray();

        // 4. Campaign Participation History
        $kol['campaigns'] = $db->table('campaign_kols ck')
            ->select('ck.*, c.title as campaign_title, c.status as campaign_status, c.start_date, c.end_date')
            ->join('campaigns c', 'c.id = ck.campaign_id')
            ->where('ck.kol_id', $id)
            ->orderBy('ck.id', 'DESC')
            ->get()
            ->getResultArray();

        // 5. Internal Reviews
        $kol['reviews'] = $db->table('kol_reviews kr')
            ->select('kr.*, c.title as campaign_title, u.name as reviewer_name')
            ->join('campaigns c', 'c.id = kr.campaign_id', 'left')
            ->join('users u', 'u.id = kr.reviewed_by', 'left')
            ->where('kr.kol_id', $id)
            ->orderBy('kr.id', 'DESC')
            ->get()
            ->getResultArray();

        // 6. Aggregate calculations
        $totalFollowers = 0;
        $maxFollowers = 0;
        $totalViews = 0;
        $totalEr = 0;
        $activePlatformsCount = 0;

        foreach ($kol['platforms'] as $p) {
            $totalFollowers += (int)$p['followers_count'];
            if ((int)$p['followers_count'] > $maxFollowers) {
                $maxFollowers = (int)$p['followers_count'];
            }
            if ((int)$p['avg_views'] > 0) {
                $totalViews += (int)$p['avg_views'];
            }
            if ((float)$p['avg_er'] > 0) {
                $totalEr += (float)$p['avg_er'];
                $activePlatformsCount++;
            }
        }

        $kol['total_followers'] = $totalFollowers;
        $kol['max_followers']   = $maxFollowers;
        $kol['avg_views']       = $totalViews;
        $kol['avg_er']          = $activePlatformsCount > 0 ? round($totalEr / $activePlatformsCount, 4) : 0;
        $kol['tier']            = $this->getTierByFollowers($maxFollowers);

        $kol['avg_rating'] = !empty($kol['reviews']) 
            ? round(array_sum(array_column($kol['reviews'], 'rating')) / count($kol['reviews']), 1) 
            : 0;

        // Estimated GMV calculation based on historical campaigns or formula
        $kol['total_gmv_estimate'] = (float)($kol['total_followers'] * 0.05 * 125000 * 0.015);

        return $kol;
    }

    /**
     * Get data structured specifically for the Executive Performance Card
     */
    public function getPerformanceCardData(int $id): ?array
    {
        $kol = $this->getKolDetail($id);
        if (!$kol) {
            return null;
        }

        $db = \Config\Database::connect();

        // Monthly snapshots for 6-month trends
        $snapshots = $db->table('kol_profile_snapshots s')
            ->select('
                s.platform_id,
                p.name as platform_name,
                p.icon as platform_icon,
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

        // Organize trend timeline
        $timelineMonths = [];
        $timelineViews = [];
        $timelineEr = [];
        $timelineFollowers = [];

        foreach ($snapshots as $snap) {
            $m = $snap['month_label'];
            if (!isset($timelineMonths[$m])) {
                $timelineMonths[$m] = $m;
                $timelineViews[$m] = 0;
                $timelineEr[$m] = 0;
                $timelineFollowers[$m] = 0;
            }
            $timelineViews[$m] += (int)$snap['avg_views'];
            $timelineFollowers[$m] += (int)$snap['followers'];
            $timelineEr[$m] = max($timelineEr[$m], (float)$snap['avg_er'] * 100);
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
            $rateMatrix[$rc['content_type']][] = $rc;
        }
        $kol['rate_matrix'] = $rateMatrix;

        return $kol;
    }
}
