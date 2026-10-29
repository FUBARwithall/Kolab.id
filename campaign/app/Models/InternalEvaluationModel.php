<?php

namespace App\Models;

use CodeIgniter\Model;

class InternalEvaluationModel extends Model
{
    /**
     * Thresholds configuration
     */
    protected function getThresholds(): array
    {
        return [
            'on_time_brackets' => [
                ['min' => 90, 'points' => 40],
                ['min' => 75, 'points' => 30],
                ['min' => 50, 'points' => 20],
                ['min' => 0,  'points' => 10],
            ],
            'tat_brackets' => [
                ['max' => 2,   'points' => 30],
                ['max' => 8,   'points' => 25],
                ['max' => 24,  'points' => 15],
                ['max' => 999, 'points' => 5],
            ],
            'dispatch_brackets' => [
                ['max' => 1,   'points' => 30],
                ['max' => 3,   'points' => 25],
                ['max' => 5,   'points' => 15],
                ['max' => 999, 'points' => 5],
            ],
            'grade_a_min' => 85,
            'grade_b_min' => 65,
        ];
    }

    /**
     * Calculate points based on brackets
     */
    protected function pointsFromBrackets($value, array $brackets, string $mode = 'min'): int
    {
        if ($value === null) {
            return 0;
        }

        if ($mode === 'min') {
            foreach ($brackets as $b) {
                if ($value >= $b['min']) {
                    return $b['points'];
                }
            }
        } else {
            foreach ($brackets as $b) {
                if ($value <= $b['max']) {
                    return $b['points'];
                }
            }
        }
        return 0;
    }

    /**
     * Get operational staff KPI scorecard
     */
    public function getStaffOperationalKpis(?int $campaignId = null): array
    {
        $db = \Config\Database::connect();
        $cfg = $this->getThresholds();

        // Ambil staff yang menangani campaign / tasks
        $staffUsers = $db->table('users')
            ->select('users.id, users.name, users.email, users.avatar')
            ->join('user_roles', 'user_roles.user_id = users.id', 'inner')
            ->join('roles', 'roles.id = user_roles.role_id', 'inner')
            ->whereIn('roles.name', ['staff', 'leader', 'admin'])
            ->groupBy('users.id')
            ->get()
            ->getResultArray();

        $scorecard = [];

        foreach ($staffUsers as $st) {
            $staffId = (int)$st['id'];

            // 1. On-Time Posting Calculation
            $taskQuery = $db->table('tasks')
                ->select('tasks.id, tasks.deadline, postings.posted_at, tasks.status')
                ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
                ->join('postings', 'postings.task_id = tasks.id', 'left')
                ->where('campaign_kols.created_by', $staffId);

            if ($campaignId) {
                $taskQuery->where('campaign_kols.campaign_id', $campaignId);
            }

            $allTasks = $taskQuery->get()->getResultArray();
            $totalTasks = count($allTasks);

            $onTimeCount = 0;
            $postedCount = 0;

            foreach ($allTasks as $t) {
                if (!empty($t['posted_at'])) {
                    $postedCount++;
                    if (!empty($t['deadline'])) {
                        $deadlineTs = strtotime($t['deadline'] . ' 23:59:59');
                        $postedTs = strtotime($t['posted_at']);
                        if ($postedTs <= $deadlineTs) {
                            $onTimeCount++;
                        }
                    } else {
                        $onTimeCount++; // Default on time if no deadline set
                    }
                }
            }

            $onTimeRate = ($postedCount > 0) ? round(($onTimeCount / $postedCount) * 100, 1) : 100.0;
            $onTimePoints = $this->pointsFromBrackets($onTimeRate, $cfg['on_time_brackets'], 'min');

            // 2. Turnaround Time (TAT) Review Draft
            $draftQuery = $db->table('drafts')
                ->select('drafts.submitted_at, drafts.reviewed_at')
                ->where('drafts.reviewed_by', $staffId)
                ->where('drafts.reviewed_at IS NOT NULL', null, false);

            $reviewedDrafts = $draftQuery->get()->getResultArray();
            $totalTatHours = 0;
            $draftCount = count($reviewedDrafts);

            foreach ($reviewedDrafts as $dr) {
                $subTs = strtotime($dr['submitted_at']);
                $revTs = strtotime($dr['reviewed_at']);
                $diffHours = max(0.1, ($revTs - $subTs) / 3600);
                $totalTatHours += $diffHours;
            }

            $avgTatHours = ($draftCount > 0) ? round($totalTatHours / $draftCount, 1) : 2.0; // default 2h
            $tatPoints = $this->pointsFromBrackets($avgTatHours, $cfg['tat_brackets'], 'max');

            // 3. Dispatch Logistik Sampel
            $shipQuery = $db->table('kol_shipments')
                ->select('kol_shipments.created_at, kol_shipments.shipped_at')
                ->where('kol_shipments.shipped_at IS NOT NULL', null, false);

            $shipments = $shipQuery->get()->getResultArray();
            $totalDispatchDays = 0;
            $shipCount = count($shipments);

            foreach ($shipments as $sh) {
                $crTs = strtotime($sh['created_at']);
                $shTs = strtotime($sh['shipped_at']);
                $diffDays = max(0, ($shTs - $crTs) / 86400);
                $totalDispatchDays += $diffDays;
            }

            $avgDispatchDays = ($shipCount > 0) ? round($totalDispatchDays / $shipCount, 1) : 1.0; // default 1 day
            $dispatchPoints = $this->pointsFromBrackets($avgDispatchDays, $cfg['dispatch_brackets'], 'max');

            // Total Score & Grade
            $totalScore = $onTimePoints + $tatPoints + $dispatchPoints;

            if ($totalScore >= $cfg['grade_a_min']) {
                $grade = 'A';
                $gradeLabel = 'Unggul (Excellent)';
                $gradeClass = 'bg-success';
            } elseif ($totalScore >= $cfg['grade_b_min']) {
                $grade = 'B';
                $gradeLabel = 'Baik (Good)';
                $gradeClass = 'bg-primary';
            } else {
                $grade = 'C';
                $gradeLabel = 'Perlu Peningkatan';
                $gradeClass = 'bg-warning text-dark';
            }

            $scorecard[] = [
                'staff_id'          => $staffId,
                'staff_name'        => $st['name'],
                'staff_email'       => $st['email'],
                'staff_avatar'      => $st['avatar'],
                'total_tasks'       => $totalTasks,
                'posted_count'      => $postedCount,
                'on_time_rate'      => $onTimeRate,
                'on_time_points'    => $onTimePoints,
                'avg_tat_hours'     => $avgTatHours,
                'tat_points'        => $tatPoints,
                'avg_dispatch_days' => $avgDispatchDays,
                'dispatch_points'   => $dispatchPoints,
                'total_score'       => $totalScore,
                'grade'             => $grade,
                'grade_label'       => $gradeLabel,
                'grade_class'       => $gradeClass,
            ];
        }

        return $scorecard;
    }

    /**
     * Team Summary Aggregation
     */
    public function getOperationalTeamSummary(array $staffKpis): array
    {
        if (empty($staffKpis)) {
            return [
                'total_staff'      => 0,
                'avg_team_score'   => 0,
                'avg_on_time_rate' => 0,
                'avg_tat'          => 0,
                'grade_a_count'    => 0,
                'grade_b_count'    => 0,
                'grade_c_count'    => 0,
            ];
        }

        $totScore = 0;
        $totOnTime = 0;
        $totTat = 0;
        $countA = 0;
        $countB = 0;
        $countC = 0;
        $n = count($staffKpis);

        foreach ($staffKpis as $kpi) {
            $totScore += $kpi['total_score'];
            $totOnTime += $kpi['on_time_rate'];
            $totTat += $kpi['avg_tat_hours'];

            if ($kpi['grade'] === 'A') $countA++;
            elseif ($kpi['grade'] === 'B') $countB++;
            else $countC++;
        }

        return [
            'total_staff'      => $n,
            'avg_team_score'   => round($totScore / $n, 1),
            'avg_on_time_rate' => round($totOnTime / $n, 1),
            'avg_tat'          => round($totTat / $n, 1),
            'grade_a_count'    => $countA,
            'grade_b_count'    => $countB,
            'grade_c_count'    => $countC,
        ];
    }

    /**
     * Scorecard KPI Khusus Leader & Campaign Strategist
     */
    public function getLeaderKpis(?int $campaignId = null): array
    {
        $db = $this->db;

        // Fetch leaders (role_id 2) and superadmin (role_id 1)
        $leaders = $db->table('users')
            ->select('users.id, users.name, users.email, users.avatar')
            ->join('user_roles', 'user_roles.user_id = users.id')
            ->whereIn('user_roles.role_id', [1, 2])
            ->where('users.deleted_at IS NULL')
            ->get()
            ->getResultArray();

        $scorecard = [];
        foreach ($leaders as $ld) {
            $lid = (int)$ld['id'];

            // Campaigns managed
            $campBuilder = $db->table('campaigns');
            if ($campaignId) {
                $campBuilder->where('id', $campaignId);
            }
            $campaigns = $campBuilder->get()->getResultArray();
            $totalCampaigns = count($campaigns);

            $totalBudget = 0;
            $targetViews = 0;
            $campIds = [];
            foreach ($campaigns as $c) {
                $totalBudget += (float)$c['budget_total'];
                $targetViews += (int)$c['target_views'];
                $campIds[] = (int)$c['id'];
            }

            $actualSpent = 0;
            $actualViews = 0;
            $actualEngagements = 0;

            if (!empty($campIds)) {
                $spentRow = $db->table('campaign_kols')
                    ->select('COALESCE(SUM(deal_amount), 0) as spent')
                    ->whereIn('campaign_id', $campIds)
                    ->get()
                    ->getRowArray();
                $actualSpent = (float)($spentRow['spent'] ?? 0);

                $viewsRow = $db->table('posting_metrics pm')
                    ->select('COALESCE(SUM(pm.views_count), 0) as tot_views, COALESCE(SUM(pm.likes_count + pm.comments_count + pm.shares_count), 0) as tot_eng')
                    ->join('postings p', 'p.id = pm.posting_id')
                    ->join('tasks t', 't.id = p.task_id')
                    ->join('campaign_kols ck', 'ck.id = t.campaign_kol_id')
                    ->whereIn('ck.campaign_id', $campIds)
                    ->get()
                    ->getRowArray();
                $actualViews = (int)($viewsRow['tot_views'] ?? 0);
                $actualEngagements = (int)($viewsRow['tot_eng'] ?? 0);
            }

            $viewRate = ($targetViews > 0) ? min(150, round(($actualViews / $targetViews) * 100, 1)) : 100;
            $budgetRate = ($totalBudget > 0) ? round(($actualSpent / $totalBudget) * 100, 1) : 0;
            $cpv = ($actualViews > 0) ? round($actualSpent / $actualViews, 2) : 0;

            // Poin View Achievement (Max 40)
            if ($viewRate >= 100) $viewPoints = 40;
            elseif ($viewRate >= 80) $viewPoints = 30;
            elseif ($viewRate >= 60) $viewPoints = 20;
            else $viewPoints = 10;

            // Poin Budget Efficiency (Max 35)
            if ($budgetRate >= 80 && $budgetRate <= 100) $budgetPoints = 35;
            elseif ($budgetRate > 100 && $budgetRate <= 110) $budgetPoints = 25;
            elseif ($budgetRate >= 50 && $budgetRate < 80) $budgetPoints = 25;
            else $budgetPoints = 15;

            // Poin Execution & Engagement (Max 25)
            $engPoints = ($actualEngagements > 10000) ? 25 : (($actualEngagements > 2000) ? 20 : 15);

            $totalScore = $viewPoints + $budgetPoints + $engPoints;

            if ($totalScore >= 85) {
                $grade = 'A';
                $gradeLabel = 'Sangat Memuaskan';
                $gradeClass = 'success';
            } elseif ($totalScore >= 65) {
                $grade = 'B';
                $gradeLabel = 'Memenuhi Target';
                $gradeClass = 'primary';
            } else {
                $grade = 'C';
                $gradeLabel = 'Perlu Evaluasi';
                $gradeClass = 'warning';
            }

            $scorecard[] = [
                'leader_id'          => $lid,
                'leader_name'        => $ld['name'],
                'leader_email'       => $ld['email'],
                'leader_avatar'      => $ld['avatar'],
                'total_campaigns'    => $totalCampaigns,
                'total_budget'       => $totalBudget,
                'actual_spent'       => $actualSpent,
                'budget_rate'        => $budgetRate,
                'budget_points'      => $budgetPoints,
                'target_views'       => $targetViews,
                'actual_views'       => $actualViews,
                'view_rate'          => $viewRate,
                'view_points'        => $viewPoints,
                'actual_engagements' => $actualEngagements,
                'eng_points'         => $engPoints,
                'cpv'                => $cpv,
                'total_score'        => $totalScore,
                'grade'              => $grade,
                'grade_label'        => $gradeLabel,
                'grade_class'        => $gradeClass,
            ];
        }

        return $scorecard;
    }

    /**
     * Leader KPI Team Summary
     */
    public function getLeaderTeamSummary(array $leaderKpis): array
    {
        if (empty($leaderKpis)) {
            return [
                'total_leaders'    => 0,
                'avg_leader_score' => 0,
                'avg_view_rate'    => 0,
                'avg_budget_rate'  => 0,
                'tot_actual_views' => 0,
            ];
        }

        $totScore = 0;
        $totViewRate = 0;
        $totBudgetRate = 0;
        $totViews = 0;
        $n = count($leaderKpis);

        foreach ($leaderKpis as $k) {
            $totScore += $k['total_score'];
            $totViewRate += $k['view_rate'];
            $totBudgetRate += $k['budget_rate'];
            $totViews += $k['actual_views'];
        }

        return [
            'total_leaders'    => $n,
            'avg_leader_score' => round($totScore / $n, 1),
            'avg_view_rate'    => round($totViewRate / $n, 1),
            'avg_budget_rate'  => round($totBudgetRate / $n, 1),
            'tot_actual_views' => $totViews,
        ];
    }
}
