<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardMetricsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Hitung total KOL aktif / approved
     */
    public function getTotalKols(): int
    {
        $count = $this->db->table('kols')
            ->where('approval_status', 'APPROVED')
            ->countAllResults();

        // Fallback jika belum ada yang status APPROVED
        if ($count === 0) {
            $count = $this->db->table('kols')->countAllResults();
        }

        return (int) $count;
    }

    /**
     * Total jangkauan followers di semua platform
     */
    public function getTotalReach(): float
    {
        $row = $this->db->table('kol_platforms')
            ->selectSum('followers_count', 'total_reach')
            ->get()
            ->getRow();

        return (float) ($row->total_reach ?? 0);
    }

    /**
     * Statistik Campaign yang sedang aktif dan total budget
     */
    public function getCampaignStats(): object
    {
        $query = $this->db->query("
            SELECT 
                COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as active_campaigns,
                COALESCE(SUM(CASE WHEN status = 'ACTIVE' THEN budget_total ELSE 0 END), 0) as active_budget
            FROM campaigns
        ");

        $res = $query->getRow();
        return $res ?: (object) ['active_campaigns' => 0, 'active_budget' => 0];
    }

    /**
     * Estimasi total delivered views dan rata-rata engagement rate
     */
    public function getContentStats(): object
    {
        $query = $this->db->query("
            SELECT 
                COALESCE(SUM(avg_views), 0) as total_views,
                COALESCE(AVG(avg_er), 0) as avg_er
            FROM kol_platforms
            WHERE avg_views > 0
        ");

        $res = $query->getRow();
        return $res ?: (object) ['total_views' => 0, 'avg_er' => 0];
    }

    /**
     * Urgent Action Center items
     */
    public function getActionItems(): array
    {
        $query = $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM tasks WHERE status IN ('DRAFT_SUBMITTED', 'DRAFT_REVISION')) as pending_drafts,
                (SELECT COUNT(*) FROM tasks WHERE deadline < CURDATE() AND status NOT IN ('POSTED', 'COMPLETED', 'CANCELLED')) as overdue_tasks,
                (SELECT COUNT(*) FROM tasks WHERE status IN ('BRIEF_SUBMITTED', 'BRIEF_REVISION')) as pending_briefs
        ");

        $res = $query->getRow();
        $pendingDrafts = (int) ($res->pending_drafts ?? 0);
        $overdueTasks  = (int) ($res->overdue_tasks ?? 0);
        $pendingBriefs = (int) ($res->pending_briefs ?? 0);
        $pendingShipments = 0;

        $totalActions = $pendingDrafts + $overdueTasks + $pendingBriefs + $pendingShipments;

        return [
            'pending_drafts'    => $pendingDrafts,
            'overdue_tasks'     => $overdueTasks,
            'pending_briefs'    => $pendingBriefs,
            'pending_shipments' => $pendingShipments,
            'total_actions'     => $totalActions,
        ];
    }

    /**
     * Komposisi KOL berdasarkan Tier (Dihitung dari rentang min_followers & max_followers tabel tiers)
     */
    public function getKolsByTier(): array
    {
        $query = $this->db->query("
            SELECT 
                t.name as tier_name,
                COUNT(DISTINCT kp.kol_id) as total
            FROM tiers t
            LEFT JOIN (
                SELECT kol_id, MAX(followers_count) as max_followers
                FROM kol_platforms
                GROUP BY kol_id
            ) kp ON kp.max_followers >= t.min_followers 
                AND (t.max_followers IS NULL OR kp.max_followers <= t.max_followers)
            GROUP BY t.id, t.name
            ORDER BY t.min_followers ASC
        ");

        return $query->getResult();
    }

    /**
     * Distribusi Platform akun sosial media
     */
    public function getPlatformDistribution(): array
    {
        $query = $this->db->query("
            SELECT 
                p.name as platform_name,
                COUNT(kp.id) as total,
                COALESCE(SUM(kp.followers_count), 0) as total_followers
            FROM platforms p
            LEFT JOIN kol_platforms kp ON kp.platform_id = p.id
            GROUP BY p.id, p.name
            ORDER BY total DESC
        ");

        return $query->getResult();
    }

    /**
     * Profil KOL yang perlu di-sync ulang (data scraper lawas > 7 hari / belum pernah di-sync)
     */
    public function getStaleKols(int $limit = 20): array
    {
        $query = $this->db->query("
            SELECT 
                kp.id,
                kp.username,
                kp.followers_count,
                kp.last_scraped_at,
                k.id as kol_id,
                k.name as kol_name,
                NULL as avatar_url,
                p.name as platform_name
            FROM kol_platforms kp
            JOIN kols k ON k.id = kp.kol_id
            JOIN platforms p ON p.id = kp.platform_id
            WHERE kp.last_scraped_at IS NULL OR kp.last_scraped_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY (kp.last_scraped_at IS NULL) DESC, kp.last_scraped_at ASC
            LIMIT ?
        ", [$limit]);

        return $query->getResult();
    }

    /**
     * Top Performers / Postings
     */
    public function getTopPerformers(int $limit = 5): array
    {
        $query = $this->db->query("
            SELECT 
                kp.id,
                kp.username,
                kp.profile_url as live_url,
                kp.avg_views as views,
                kp.avg_er as engagement_rate,
                k.name as kol_name,
                NULL as avatar_url,
                p.name as platform_name
            FROM kol_platforms kp
            JOIN kols k ON k.id = kp.kol_id
            JOIN platforms p ON p.id = kp.platform_id
            WHERE kp.avg_views > 0
            ORDER BY kp.avg_views DESC
            LIMIT ?
        ", [$limit]);

        return $query->getResult();
    }
}
