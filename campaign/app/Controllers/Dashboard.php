<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Dashboard Utama Campaign Management (Full parity with CI3 KOL-System)
     */
    public function index()
    {
        // 1. Top KPI Summary Metrics
        $totalKols       = $this->db->table('kols')->where('deleted_at IS NULL')->countAllResults();
        $activeKols      = $this->db->table('kols')->where('approval_status', 'APPROVED')->where('deleted_at IS NULL')->countAllResults();
        $activeCampaigns = $this->db->table('campaigns')->where('status', 'ACTIVE')->where('deleted_at IS NULL')->countAllResults();
        $totalCampaigns  = $this->db->table('campaigns')->where('deleted_at IS NULL')->countAllResults();

        $budgetRow = $this->db->table('campaigns')
            ->select('COALESCE(SUM(budget_total), 0) as total_budget')
            ->where('status', 'ACTIVE')
            ->where('deleted_at IS NULL')
            ->get()
            ->getRow();
        $totalBudget = $budgetRow ? (float)$budgetRow->total_budget : 0;

        $pendingBriefs   = $this->db->table('briefs')->where('approval_status', 'PENDING')->countAllResults();
        $pendingDrafts   = $this->db->table('drafts')->where('status', 'PENDING')->countAllResults();
        $pendingApprovals = $pendingBriefs + $pendingDrafts;

        $totalTasks   = $this->db->table('tasks')->countAllResults();
        $livePostings = $this->db->table('tasks')->whereIn('status', ['POSTED', 'COMPLETED'])->countAllResults();

        // 2. Financial KPIs & Cash Flow Realization
        $dealsRow = $this->db->table('campaign_kols')
            ->select('COALESCE(SUM(deal_amount), 0) as total_deals')
            ->get()
            ->getRow();
        $totalDeals = $dealsRow ? (float)$dealsRow->total_deals : 0;

        $invRow = $this->db->table('invoices')
            ->select('COALESCE(SUM(grand_total), 0) as total_invoiced')
            ->where('status !=', 'REJECTED')
            ->get()
            ->getRow();
        $totalInvoiced = $invRow ? (float)$invRow->total_invoiced : 0;

        $payRow = $this->db->table('payments')
            ->select('COALESCE(SUM(amount_paid), 0) as total_paid')
            ->get()
            ->getRow();
        $totalPaid = $payRow && $payRow->total_paid > 0 ? (float)$payRow->total_paid : 0;
        if ($totalPaid == 0) {
            $paidInvRow = $this->db->table('invoices')
                ->select('COALESCE(SUM(grand_total), 0) as total_paid')
                ->where('status', 'PAID')
                ->get()
                ->getRow();
            $totalPaid = $paidInvRow ? (float)$paidInvRow->total_paid : 0;
        }
        $totalOutstanding = max(0, $totalInvoiced - $totalPaid);
        $pctCashPaid = ($totalInvoiced > 0) ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0;

        // 3. Pending Invoices (Awaiting payment / nearing due date)
        $pendingInvoices = $this->db->query("
            SELECT i.id, i.invoice_number, i.grand_total, i.status, 
                   k.name as kol_name, c.title as campaign_name,
                   DATE_ADD(i.created_at, INTERVAL 14 DAY) as due_date,
                   DATEDIFF(DATE_ADD(i.created_at, INTERVAL 14 DAY), CURDATE()) as days_left
            FROM invoices i
            LEFT JOIN campaign_kols ck ON ck.id = i.campaign_kol_id
            LEFT JOIN kols k ON k.id = ck.kol_id
            LEFT JOIN campaigns c ON c.id = ck.campaign_id
            WHERE i.status IN ('DRAFT', 'SUBMITTED', 'VERIFIED')
            ORDER BY i.id DESC
            LIMIT 4
        ")->getResultArray();

        // 4. Upcoming Deadlines & Overdue Tasks (Next 14 Days & Overdue)
        $upcomingDeadlines = $this->db->query("
            SELECT t.id, t.title, t.deadline, t.status, 
                   k.name as kol_name, c.title as campaign_name, p.name as platform_name,
                   DATEDIFF(t.deadline, CURDATE()) as days_left
            FROM tasks t
            LEFT JOIN campaign_kols ck ON ck.id = t.campaign_kol_id
            LEFT JOIN kols k ON k.id = ck.kol_id
            LEFT JOIN campaigns c ON c.id = ck.campaign_id
            LEFT JOIN postings post ON post.task_id = t.id
            LEFT JOIN platforms p ON p.id = post.platform_id
            WHERE t.status NOT IN ('POSTED', 'COMPLETED', 'CANCELLED')
              AND t.deadline IS NOT NULL
            ORDER BY t.deadline ASC
            LIMIT 6
        ")->getResultArray();

        // 5. Expiring Contracts (Contracts within next 30 days)
        $expiringContracts = $this->db->query("
            SELECT c.id, c.contract_number, c.signed_at,
                   k.name as kol_name, camp.title as campaign_name,
                   DATE_ADD(c.created_at, INTERVAL 30 DAY) as expired_at,
                   DATEDIFF(DATE_ADD(c.created_at, INTERVAL 30 DAY), CURDATE()) as days_left
            FROM campaign_kol_contracts c
            LEFT JOIN campaign_kols ck ON ck.id = c.campaign_kol_id
            LEFT JOIN kols k ON k.id = ck.kol_id
            LEFT JOIN campaigns camp ON camp.id = ck.campaign_id
            ORDER BY c.id DESC
            LIMIT 4
        ")->getResultArray();

        // 6. Production Workflow & Funnel Counts
        $prodMonitoring  = $totalTasks;
        $prodReviewDraft = $this->db->table('tasks')->whereIn('status', ['DRAFT_SUBMITTED', 'DRAFT_REVISION'])->countAllResults();
        $prodLivePosting = $livePostings;
        $prodSchedule    = $this->db->table('tasks')->where('status', 'SCHEDULED')->orWhere('deadline IS NOT NULL')->countAllResults();

        // 7. Recent Live Postings (Activity Feed)
        $recentPostings = $this->db->query("
            SELECT p.*, t.title as task_title, plat.name as platform_name, plat.icon as platform_icon, k.name as kol_name
            FROM postings p
            LEFT JOIN tasks t ON t.id = p.task_id
            LEFT JOIN platforms plat ON plat.id = p.platform_id
            LEFT JOIN campaign_kols ck ON ck.id = t.campaign_kol_id
            LEFT JOIN kols k ON k.id = ck.kol_id
            ORDER BY p.id DESC
            LIMIT 5
        ")->getResultArray();

        // 8. Recent Campaigns
        $recentCampaigns = $this->db->table('campaigns')
            ->select('id, title, budget_total, start_date, end_date, status')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $data = [
            'title'              => 'Dashboard Campaign Management',
            'controller'         => 'Dashboard',
            'total_kols'         => $totalKols,
            'active_kols'        => $activeKols,
            'active_campaigns'   => $activeCampaigns,
            'total_campaigns'    => $totalCampaigns,
            'total_budget'       => $totalBudget,
            'pending_approvals'  => $pendingApprovals,
            'pending_briefs'     => $pendingBriefs,
            'pending_drafts'     => $pendingDrafts,
            'total_tasks'        => $totalTasks,
            'live_postings'      => $livePostings,
            'total_deals'        => $totalDeals,
            'total_invoiced'     => $totalInvoiced,
            'total_paid'         => $totalPaid,
            'total_outstanding'  => $totalOutstanding,
            'pct_cash_paid'      => $pctCashPaid,
            'pending_invoices'   => $pendingInvoices,
            'upcoming_deadlines' => $upcomingDeadlines,
            'expiring_contracts' => $expiringContracts,
            'prod_monitoring'    => $prodMonitoring,
            'prod_review_draft'  => $prodReviewDraft,
            'prod_live_posting'  => $prodLivePosting,
            'prod_schedule'      => $prodSchedule,
            'recent_postings'    => $recentPostings,
            'recentCampaigns'    => $recentCampaigns,
        ];

        return view('dashboard/index', $data);
    }

    /**
     * Leaderboard & Performa KOL Rankings (Full parity with CI3 chart method)
     */
    public function rankings()
    {
        $sql = "
            SELECT 
                k.id,
                k.name,
                k.city,
                k.approval_status,
                COALESCE(SUM(kp.followers_count), 0) as total_followers,
                COALESCE(MAX(kp.followers_count), 0) as max_followers,
                COALESCE(SUM(ck.deal_amount), 0) as total_deal_budget,
                COALESCE(SUM(pm.views_count), 0) as total_views,
                COALESCE(AVG(NULLIF(pm.likes_count / NULLIF(pm.views_count, 0), 0)), 0) as avg_er,
                COUNT(DISTINCT t.id) as completed_tasks,
                (SELECT ROUND(AVG(kr.rating), 1) FROM kol_reviews kr WHERE kr.kol_id = k.id) as avg_rating
            FROM kols k
            LEFT JOIN kol_platforms kp ON kp.kol_id = k.id
            LEFT JOIN campaign_kols ck ON ck.kol_id = k.id
            LEFT JOIN tasks t ON t.campaign_kol_id = ck.id AND t.status IN ('POSTED', 'COMPLETED')
            LEFT JOIN postings p ON p.task_id = t.id
            LEFT JOIN posting_metrics pm ON pm.posting_id = p.id
            WHERE k.deleted_at IS NULL
            GROUP BY k.id
            ORDER BY total_views DESC, completed_tasks DESC
        ";
        $leaderboard = $this->db->query($sql)->getResultArray();

        // Calculate CPV and Tier for each KOL
        foreach ($leaderboard as &$kol) {
            $views = (int)$kol['total_views'];
            $deal  = (float)$kol['total_deal_budget'];
            $kol['cpv'] = $views > 0 ? round($deal / $views, 2) : 0;

            $maxFollowers = (int)$kol['max_followers'];
            if ($maxFollowers >= 1000000) {
                $kol['tier_name'] = 'Mega';
            } elseif ($maxFollowers >= 500000) {
                $kol['tier_name'] = 'Macro';
            } elseif ($maxFollowers >= 100000) {
                $kol['tier_name'] = 'Mid';
            } elseif ($maxFollowers >= 10000) {
                $kol['tier_name'] = 'Micro';
            } else {
                $kol['tier_name'] = 'Nano';
            }
        }

        $topKol = !empty($leaderboard) ? $leaderboard[0] : null;

        // Tier distribution
        $tierCounts = ['Mega' => 0, 'Macro' => 0, 'Mid' => 0, 'Micro' => 0, 'Nano' => 0];
        foreach ($leaderboard as $k) {
            $t = $k['tier_name'];
            if (isset($tierCounts[$t])) $tierCounts[$t]++;
        }

        $data = [
            'title'        => 'Leaderboard & Performa KOL',
            'controller'   => 'Dashboard',
            'leaderboard'  => $leaderboard,
            'top_kol'      => $topKol,
            'tier_counts'  => $tierCounts,
        ];

        return view('dashboard/rankings', $data);
    }

    /**
     * AJAX: Side-by-Side Comparison of 2-5 Selected KOLs (Full parity with CI3 compare_kol)
     */
    public function compareKol()
    {
        $rawIds = $this->request->getPost('kol_ids');
        if (empty($rawIds) || !is_array($rawIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada KOL yang dipilih.']);
        }

        $ids = array_values(array_filter(array_map('intval', $rawIds), fn($v) => $v > 0));
        if (count($ids) < 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih minimal 2 KOL untuk dibandingkan.']);
        }
        if (count($ids) > 5) {
            return $this->response->setJSON(['success' => false, 'message' => 'Maksimal 5 KOL dapat dibandingkan sekaligus.']);
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            SELECT 
                k.id,
                k.name,
                k.city,
                COALESCE(SUM(kp.followers_count), 0) as total_followers,
                COALESCE(MAX(kp.followers_count), 0) as max_followers,
                COALESCE(SUM(ck.deal_amount), 0) as total_deal_budget,
                COALESCE(SUM(pm.views_count), 0) as total_views,
                COALESCE(AVG(NULLIF(pm.likes_count / NULLIF(pm.views_count, 0), 0)), 0) as avg_er,
                COUNT(DISTINCT t.id) as total_tasks,
                (SELECT ROUND(AVG(kr.rating), 1) FROM kol_reviews kr WHERE kr.kol_id = k.id) as avg_rating
            FROM kols k
            LEFT JOIN kol_platforms kp ON kp.kol_id = k.id
            LEFT JOIN campaign_kols ck ON ck.kol_id = k.id
            LEFT JOIN tasks t ON t.campaign_kol_id = ck.id AND t.status IN ('POSTED', 'COMPLETED')
            LEFT JOIN postings p ON p.task_id = t.id
            LEFT JOIN posting_metrics pm ON pm.posting_id = p.id
            WHERE k.id IN ({$placeholders})
            GROUP BY k.id
        ";
        $kols = $this->db->query($sql, $ids)->getResultArray();

        if (empty($kols)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data KOL tidak ditemukan.']);
        }

        // Determine best values for highlighting
        $viewsArr = [];
        $erArr = [];
        $cpvArr = [];
        $scoreArr = [];

        foreach ($kols as &$k) {
            $views = (int)$k['total_views'];
            $deal  = (float)$k['total_deal_budget'];
            $cpv   = $views > 0 ? round($deal / $views, 2) : 0;
            $k['cpv'] = $cpv;

            $maxFollowers = (int)$k['max_followers'];
            $k['tier_name'] = $maxFollowers >= 1000000 ? 'Mega' : ($maxFollowers >= 500000 ? 'Macro' : ($maxFollowers >= 100000 ? 'Mid' : 'Micro'));

            $viewsArr[] = $views;
            $erArr[]    = (float)$k['avg_er'];
            if ($cpv > 0) $cpvArr[] = $cpv;
            $scoreArr[] = (float)$k['avg_rating'];
        }
        unset($k);

        $maxViews = !empty($viewsArr) ? max($viewsArr) : 0;
        $maxEr    = !empty($erArr) ? max($erArr) : 0;
        $minCpv   = !empty($cpvArr) ? min($cpvArr) : 0;
        $maxScore = !empty($scoreArr) ? max($scoreArr) : 0;

        // Render HTML Card Grid
        ob_start();
        ?>
        <div class="row g-3">
            <?php foreach ($kols as $k):
                $initials = strtoupper(substr($k['name'], 0, 1) . (substr(strstr($k['name'], ' ') ?: '', 1, 1) ?: substr($k['name'], 1, 1)));
                $isBestViews = ((int)$k['total_views'] === $maxViews && $maxViews > 0);
                $isBestEr    = ((float)$k['avg_er'] === $maxEr && $maxEr > 0);
                $isBestCpv   = ($minCpv > 0 && abs($k['cpv'] - $minCpv) < 0.01);
                $isBestScore = ((float)$k['avg_rating'] === $maxScore && $maxScore > 0);
            ?>
            <div class="col">
                <div class="card border rounded-3 p-3 text-center h-100 shadow-sm">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 52px; height: 52px; font-size: 18px;">
                        <?= esc($initials) ?>
                    </div>
                    <h6 class="fw-bold text-dark text-sm mb-1"><?= esc($k['name']) ?></h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-xs mb-3">
                        Tier <?= esc($k['tier_name']) ?>
                    </span>

                    <div class="d-flex flex-column gap-2 text-xs">
                        <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-eye text-primary me-1"></i> Total Views</span>
                            <strong class="<?= $isBestViews ? 'text-success fw-bold' : 'text-dark' ?>">
                                <?= number_format($k['total_views']) ?>
                            </strong>
                        </div>
                        <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-chart-line text-info me-1"></i> Avg. ER</span>
                            <strong class="<?= $isBestEr ? 'text-success fw-bold' : 'text-dark' ?>">
                                <?= number_format($k['avg_er'] * 100, 2) ?>%
                            </strong>
                        </div>
                        <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-dollar-sign text-warning me-1"></i> CPV</span>
                            <strong class="<?= $isBestCpv ? 'text-success fw-bold' : 'text-dark' ?>">
                                <?= $k['cpv'] > 0 ? 'Rp ' . number_format($k['cpv'], 0, ',', '.') : '-' ?>
                            </strong>
                        </div>
                        <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-star text-warning me-1"></i> Rating</span>
                            <strong class="<?= $isBestScore ? 'text-success fw-bold' : 'text-dark' ?>">
                                <?= $k['avg_rating'] > 0 ? $k['avg_rating'] . ' / 5.0' : '-' ?>
                            </strong>
                        </div>
                        <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-tasks text-secondary me-1"></i> VT Selesai</span>
                            <strong class="text-dark"><?= (int)$k['total_tasks'] ?> VT</strong>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= base_url('kols/performance-card/' . $k['id']) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 text-xs">
                            <i class="fas fa-id-card-alt me-1"></i> Buka Card
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="alert alert-light border mt-3 mb-0 text-xs text-muted d-flex align-items-center">
            <i class="fas fa-info-circle text-success me-2 fs-6"></i>
            <div>Angka berwarna <strong>Hijau</strong> menandakan nilai metrik terbaik di antara KOL yang dibandingkan. CPV dihitung dari total deal budget dibagi total tayangan.</div>
        </div>
        <?php
        $html = ob_get_clean();

        return $this->response->setJSON([
            'success' => true,
            'html'    => $html,
        ]);
    }
}
