<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\InternalEvaluation as EvalConfig;

/**
 * Hybrid Evaluation Item: supports both object ($item->prop) and array ($item['prop']) access
 */
class EvaluationItem implements \ArrayAccess, \JsonSerializable, \IteratorAggregate
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }
}

class InternalEvaluationModel extends Model
{
    protected $table = 'users';

    /**
     * Thresholds configuration loaded from Config\InternalEvaluation
     */
    public function getThresholds(): array
    {
        $cfg = config('InternalEvaluation');
        if (!$cfg instanceof EvalConfig) {
            $cfg = new EvalConfig();
        }

        return [
            'on_time_brackets'       => $cfg->on_time_brackets,
            'tat_brackets'           => $cfg->tat_brackets,
            'dispatch_brackets'      => $cfg->dispatch_brackets,
            'grade_a_min'            => $cfg->grade_a_min,
            'grade_b_min'            => $cfg->grade_b_min,
            'tasks_per_staff_limit'  => $cfg->tasks_per_staff_limit,
            'staff_page_size'        => $cfg->staff_page_size,
            'min_decided_postings'   => $cfg->min_decided_postings,
            'min_reviewed_drafts'    => $cfg->min_reviewed_drafts,
            'provisional_suffix'     => $cfg->provisional_suffix,
            'evaluation_grace_days'  => $cfg->evaluation_grace_days,
            'leader_target_user_ids' => $cfg->leader_target_user_ids,
            'leader_view_brackets'   => $cfg->leader_view_brackets,
            'leader_cpv_brackets'    => $cfg->leader_cpv_brackets,
            'leader_deal_brackets'   => $cfg->leader_deal_brackets,
            'leader_approval_brackets' => $cfg->leader_approval_brackets,
            'leader_grade_a_min'     => $cfg->leader_grade_a_min,
            'leader_grade_b_min'     => $cfg->leader_grade_b_min,
            'min_leader_campaigns'   => $cfg->min_leader_campaigns,
        ];
    }

    /**
     * Calculate points based on brackets
     */
    public function pointsFromBrackets($value, array $brackets, string $mode = 'min'): int
    {
        if ($value === null) {
            return 0;
        }

        foreach ($brackets as $b) {
            if ($mode === 'min') {
                if ($value >= $b['min']) {
                    return (int)$b['points'];
                }
            } else {
                if ($value <= $b['max']) {
                    return (int)$b['points'];
                }
            }
        }
        return 0;
    }

    /**
     * Single campaign maturity check (Grace period for Apify scraping)
     */
    protected function singleCampaignMaturity(?int $campaignId, array $cfg): ?array
    {
        if (!$campaignId || $campaignId <= 0) {
            return null;
        }

        $row = $this->db->table('campaigns')
            ->select('id, status, end_date')
            ->where('id', $campaignId)
            ->get()
            ->getRow();

        if (!$row) {
            return null;
        }

        $grace = (int)($cfg['evaluation_grace_days'] ?? 7);
        $status = strtoupper(trim((string)($row->status ?? '')));

        if (!in_array($status, ['COMPLETED', 'CANCELLED'], true)) {
            return [
                'provisional' => true,
                'reason'      => 'campaign belum selesai (status: ' . ($row->status ?: '-') . ')',
            ];
        }

        if (empty($row->end_date)) {
            return [
                'provisional' => true,
                'reason'      => 'tanggal selesai belum diisi, menunggu scraping',
            ];
        }

        $endTs = strtotime($row->end_date . ' 23:59:59');
        if ($endTs === false) {
            return [
                'provisional' => true,
                'reason'      => 'tanggal selesai belum valid, menunggu scraping',
            ];
        }

        $finalTs = $endTs + max(0, $grace) * 86400;
        if (time() < $finalTs) {
            return [
                'provisional' => true,
                'reason'      => 'menunggu scraping hingga H+' . $grace . ' (final ' . date('d M Y', $finalTs) . ')',
            ];
        }

        return ['provisional' => false, 'reason' => null];
    }

    /**
     * Get list of campaigns with status priority ordering
     */
    public function get_campaigns_list(): array
    {
        return $this->getCampaignsList();
    }

    public function getCampaignsList(): array
    {
        $rows = $this->db->table('campaigns')
            ->select('id, title, name, status, start_date, end_date, budget, budget_total, target_views')
            ->orderBy("CASE WHEN status = 'ACTIVE' THEN 1 WHEN status = 'OPEN_REGISTRATION' THEN 2 WHEN status = 'COMPLETED' THEN 3 ELSE 4 END, id DESC")
            ->get()
            ->getResult();

        $result = [];
        foreach ($rows as $r) {
            $title = $r->title ?: ($r->name ?: 'Campaign #' . $r->id);
            $name  = $r->name ?: $title;
            $r->title = $title;
            $r->name  = $name;
            $result[] = new EvaluationItem((array)$r);
        }

        return $result;
    }

    /**
     * Get total operational staff count
     */
    public function get_staff_operational_count(?int $campaignId = null): int
    {
        return $this->getStaffOperationalCount($campaignId);
    }

    public function getStaffOperationalCount(?int $campaignId = null): int
    {
        $builder = $this->db->table('users u')
            ->select('u.id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where("(r.name IN ('staff', 'Operational') OR r.id = 3
                     OR u.id IN (SELECT DISTINCT COALESCE(ck2.assigned_by, ck2.created_by) FROM campaign_kols ck2 WHERE ck2.assigned_by IS NOT NULL OR ck2.created_by IS NOT NULL))", null, false);

        if ($campaignId && $campaignId > 0) {
            $builder->where("(u.id IN (
                SELECT DISTINCT COALESCE(ck.assigned_by, ck.created_by) FROM campaign_kols ck
                WHERE ck.campaign_id = {$campaignId} AND (ck.assigned_by IS NOT NULL OR ck.created_by IS NOT NULL)
                UNION
                SELECT DISTINCT t.created_by FROM tasks t
                JOIN campaign_kols ck ON ck.id = t.campaign_kol_id
                WHERE ck.campaign_id = {$campaignId} AND t.created_by IS NOT NULL
            ))", null, false);
        }

        $builder->groupBy('u.id');
        return $builder->countAllResults();
    }

    /**
     * Fetch staff operational KPI scorecard
     */
    public function get_staff_operational_kpis(?int $campaignId = null, int $limit = 200, int $offset = 0): array
    {
        return $this->getStaffOperationalKpis($campaignId, $limit, $offset);
    }

    public function getStaffOperationalKpis(?int $campaignId = null, int $limit = 200, int $offset = 0): array
    {
        $cfg = $this->getThresholds();
        $cid = (int)$campaignId;
        $campWhere = ($cid > 0) ? " AND ck.campaign_id = " . $cid : "";
        $maturity = $this->singleCampaignMaturity($cid, $cfg);

        // 1. Staff query
        $staffBuilder = $this->db->table('users u')
            ->select('u.id, u.username, COALESCE(u.full_name, u.name) as full_name, u.name, u.avatar, u.email')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where("(r.name IN ('staff', 'Operational') OR r.id = 3
                     OR u.id IN (SELECT DISTINCT COALESCE(ck2.assigned_by, ck2.created_by) FROM campaign_kols ck2 WHERE ck2.assigned_by IS NOT NULL OR ck2.created_by IS NOT NULL))", null, false);

        if ($cid > 0) {
            $staffBuilder->where("(u.id IN (
                SELECT DISTINCT COALESCE(ck.assigned_by, ck.created_by) FROM campaign_kols ck
                WHERE ck.campaign_id = {$cid} AND (ck.assigned_by IS NOT NULL OR ck.created_by IS NOT NULL)
                UNION
                SELECT DISTINCT t.created_by FROM tasks t
                JOIN campaign_kols ck ON ck.id = t.campaign_kol_id
                WHERE ck.campaign_id = {$cid} AND t.created_by IS NOT NULL
            ))", null, false);
        }

        $staffBuilder->groupBy('u.id')
            ->orderBy('full_name', 'ASC')
            ->limit($limit, $offset);

        $staffList = $staffBuilder->get()->getResult();
        if (empty($staffList)) {
            return [];
        }

        $staffIds = array_map(fn($s) => (int)$s->id, $staffList);
        $idList = implode(',', $staffIds);

        // 2. KOL counts per staff
        $kolRows = $this->db->query("
            SELECT COALESCE(ck.assigned_by, ck.created_by) AS staff_id,
                   COUNT(DISTINCT ck.id) AS kol_count,
                   COALESCE(SUM(ck.deal_amount), 0) AS total_deal_amount
            FROM campaign_kols ck
            WHERE COALESCE(ck.assigned_by, ck.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(ck.assigned_by, ck.created_by)
        ")->getResult();
        $kolMap = [];
        foreach ($kolRows as $r) {
            $kolMap[(int)$r->staff_id] = $r;
        }

        // 3. Tasks & Postings aggregates per staff
        $taskRows = $this->db->query("
            SELECT COALESCE(ck.assigned_by, ck.created_by) AS staff_id,
                   COUNT(DISTINCT t.id) AS total_tasks,
                   SUM(CASE WHEN t.status = 'DONE' THEN 1 ELSE 0 END) AS completed_tasks,
                   SUM(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END) AS total_postings,
                   SUM(CASE WHEN p.date_validation_status = 'ON_TIME' OR (p.posted_at IS NOT NULL AND t.deadline IS NOT NULL AND p.posted_at <= CONCAT(t.deadline, ' 23:59:59')) THEN 1 ELSE 0 END) AS on_time_postings,
                   SUM(CASE WHEN p.date_validation_status = 'LATE' OR (p.posted_at IS NOT NULL AND t.deadline IS NOT NULL AND p.posted_at > CONCAT(t.deadline, ' 23:59:59')) THEN 1 ELSE 0 END) AS late_postings
            FROM campaign_kols ck
            JOIN tasks t ON t.campaign_kol_id = ck.id
            LEFT JOIN postings p ON p.task_id = t.id
            WHERE COALESCE(ck.assigned_by, ck.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(ck.assigned_by, ck.created_by)
        ")->getResult();
        $taskMap = [];
        foreach ($taskRows as $r) {
            $taskMap[(int)$r->staff_id] = $r;
        }

        // 3b. Actual Views, Engagements & Spent per staff
        $perfRows = $this->db->query("
            SELECT COALESCE(ck.assigned_by, ck.created_by) AS staff_id,
                   COALESCE(SUM(t.budget), 0) AS actual_spent,
                   COALESCE(SUM(COALESCE(pm.views, pm.views_count, 0)), 0) AS actual_views,
                   COALESCE(SUM(COALESCE(pm.likes, pm.likes_count, 0) + COALESCE(pm.comments, pm.comments_count, 0) + COALESCE(pm.shares, pm.shares_count, 0) + COALESCE(pm.saves, pm.saves_count, 0)), 0) AS actual_engagements
            FROM campaign_kols ck
            JOIN tasks t ON t.campaign_kol_id = ck.id
            LEFT JOIN postings p ON p.task_id = t.id
            LEFT JOIN posting_metrics pm ON pm.id = COALESCE(p.latest_metric_id, (SELECT MAX(pm2.id) FROM posting_metrics pm2 WHERE pm2.posting_id = p.id))
            WHERE COALESCE(ck.assigned_by, ck.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(ck.assigned_by, ck.created_by)
        ")->getResult();
        $perfMap = [];
        foreach ($perfRows as $r) {
            $perfMap[(int)$r->staff_id] = $r;
        }

        // 4. Drafts review TAT
        $draftRows = $this->db->query("
            SELECT staff_id,
                   COUNT(*) AS total_drafts,
                   AVG(version) AS avg_version,
                   AVG(tat_hours) AS avg_tat,
                   SUM(is_fallback) AS tat_fallback,
                   SUM(CASE WHEN is_fallback = 0 THEN 1 ELSE 0 END) AS reviewed_drafts
            FROM (
                SELECT COALESCE(ck.assigned_by, ck.created_by) AS staff_id, d.id, d.version,
                       CASE WHEN MIN(h.created_at) IS NOT NULL
                            THEN TIMESTAMPDIFF(MINUTE, d.created_at, MIN(h.created_at)) / 60
                            WHEN d.reviewed_at IS NOT NULL AND d.submitted_at IS NOT NULL
                            THEN TIMESTAMPDIFF(MINUTE, d.submitted_at, d.reviewed_at) / 60
                            ELSE GREATEST(TIMESTAMPDIFF(MINUTE, d.created_at, d.updated_at) / 60, 0.1)
                       END AS tat_hours,
                       CASE WHEN MIN(h.created_at) IS NOT NULL OR (d.reviewed_at IS NOT NULL AND d.submitted_at IS NOT NULL) THEN 0 ELSE 1 END AS is_fallback
                FROM drafts d
                JOIN tasks t ON t.id = d.task_id
                JOIN campaign_kols ck ON ck.id = t.campaign_kol_id
                LEFT JOIN draft_review_histories h ON h.draft_id = d.id AND h.action IN ('REVISION', 'APPROVED')
                WHERE COALESCE(ck.assigned_by, ck.created_by) IN ({$idList}) {$campWhere}
                  AND d.status IN ('APPROVED', 'REVISION')
                GROUP BY d.id
            ) x
            GROUP BY staff_id
        ")->getResult();
        $draftMap = [];
        foreach ($draftRows as $r) {
            $draftMap[(int)$r->staff_id] = $r;
        }

        // 5. Logistics dispatch
        $shipRows = $this->db->query("
            SELECT COALESCE(ck.assigned_by, ck.created_by) AS staff_id,
                   SUM(CASE WHEN s.shipped_at IS NOT NULL
                             AND (ck.deal_date IS NULL OR DATEDIFF(s.shipped_at, ck.deal_date) >= 0) THEN 1 ELSE 0 END) AS valid_shipments,
                   AVG(CASE WHEN s.shipped_at IS NOT NULL
                             AND (ck.deal_date IS NULL OR DATEDIFF(s.shipped_at, ck.deal_date) >= 0)
                            THEN CASE WHEN ck.deal_date IS NOT NULL THEN DATEDIFF(s.shipped_at, ck.deal_date) ELSE DATEDIFF(s.shipped_at, s.created_at) END
                            ELSE NULL END) AS avg_dispatch,
                   SUM(CASE WHEN s.shipped_at IS NULL THEN 1 ELSE 0 END) AS pending_shipments,
                   COUNT(*) AS all_shipments
            FROM kol_shipments s
            JOIN campaign_kols ck ON ck.id = s.campaign_kol_id
            WHERE COALESCE(ck.assigned_by, ck.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(ck.assigned_by, ck.created_by)
        ")->getResult();
        $shipMap = [];
        foreach ($shipRows as $r) {
            $shipMap[(int)$r->staff_id] = $r;
        }

        // 6. Drill-down task rows (capped)
        $perStaffCap = (int)($cfg['tasks_per_staff_limit'] ?? 50);
        $detailRows = $this->db->query("
            SELECT COALESCE(ck.assigned_by, ck.created_by) AS staff_id,
                   t.id AS task_id, t.title AS task_title, t.status AS task_status,
                   t.budget AS task_budget, t.estimated_posting_date, t.deadline,
                   k.id AS kol_id, k.name AS kol_name, k.avatar_url AS kol_avatar, k.email AS kol_username,
                   c.id AS campaign_id, COALESCE(c.title, c.name) AS campaign_name,
                   p.id AS posting_id, p.date_validation_status, p.actual_posted_at, p.posted_at, p.live_url,
                   COALESCE(pm.views, pm.views_count, 0) AS views,
                   COALESCE(pm.likes, pm.likes_count, 0) + COALESCE(pm.comments, pm.comments_count, 0) + COALESCE(pm.shares, pm.shares_count, 0) + COALESCE(pm.saves, pm.saves_count, 0) AS engagements
            FROM campaign_kols ck
            JOIN tasks t ON t.campaign_kol_id = ck.id
            JOIN kols k ON k.id = ck.kol_id
            JOIN campaigns c ON c.id = ck.campaign_id
            LEFT JOIN postings p ON p.task_id = t.id
            LEFT JOIN posting_metrics pm ON pm.id = COALESCE(p.latest_metric_id, (SELECT MAX(pm2.id) FROM posting_metrics pm2 WHERE pm2.posting_id = p.id))
            WHERE COALESCE(ck.assigned_by, ck.created_by) IN ({$idList}) {$campWhere}
            ORDER BY t.id DESC
            LIMIT 2000
        ")->getResult();
        $tasksMap = [];
        foreach ($detailRows as $t) {
            $sid = (int)$t->staff_id;
            if (!isset($tasksMap[$sid])) $tasksMap[$sid] = [];
            $t->cpv = ($t->views > 0 && (float)$t->task_budget > 0) ? round((float)$t->task_budget / $t->views, 2) : 0;
            $t->cpe = ($t->engagements > 0 && (float)$t->task_budget > 0) ? round((float)$t->task_budget / $t->engagements, 2) : 0;
            if (count($tasksMap[$sid]) < $perStaffCap) {
                $tasksMap[$sid][] = $t;
            }
        }

        // 7. Hydrate EvaluationItem models
        $results = [];
        foreach ($staffList as $staff) {
            $sid = (int)$staff->id;
            $username = $staff->username ?: explode('@', $staff->email)[0];
            $fullName = $staff->full_name ?: ($staff->name ?: $username);

            $k = $kolMap[$sid] ?? null;
            $kolCount = (int)($k->kol_count ?? 0);
            $totalDealAmount = (float)($k->total_deal_amount ?? 0);

            $pf = $perfMap[$sid] ?? null;
            $actualSpent = (float)($pf->actual_spent ?? 0);
            $actualViews = (int)($pf->actual_views ?? 0);
            $actualEngagements = (int)($pf->actual_engagements ?? 0);
            $actualCpv = ($actualViews > 0) ? round($actualSpent / $actualViews, 2) : 0;
            $actualCpe = ($actualEngagements > 0) ? round($actualSpent / $actualEngagements, 2) : 0;

            $t = $taskMap[$sid] ?? null;
            $totalTasks = (int)($t->total_tasks ?? 0);
            $completedTasks = (int)($t->completed_tasks ?? 0);
            $totalPostings = (int)($t->total_postings ?? 0);
            $onTimePostings = (int)($t->on_time_postings ?? 0);
            $latePostings = (int)($t->late_postings ?? 0);
            $pendingPostings = max(0, $totalPostings - $onTimePostings - $latePostings);
            $decided = $onTimePostings + $latePostings;
            $onTimeRate = ($decided > 0) ? round(($onTimePostings / $decided) * 100, 1) : null;
            $completionRate = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

            $d = $draftMap[$sid] ?? null;
            $totalDrafts = (int)($d->total_drafts ?? 0);
            $avgRevisionCycles = ($totalDrafts > 0 && $d && $d->avg_version !== null) ? round((float)$d->avg_version, 1) : null;
            $avgTatHours = ($d && $d->avg_tat !== null) ? round((float)$d->avg_tat, 1) : null;
            $reviewedDrafts = (int)($d->reviewed_drafts ?? 0);

            $s = $shipMap[$sid] ?? null;
            $totalShipments = (int)($s->all_shipments ?? 0);
            $pendingShipments = (int)($s->pending_shipments ?? 0);
            $avgDispatchDays = ($s && $s->avg_dispatch !== null) ? round((float)$s->avg_dispatch, 1) : null;

            // Strict PENDING check
            if ($kolCount === 0 && $totalTasks === 0) {
                $itemData = [
                    'id'                     => $sid,
                    'staff_id'               => $sid,
                    'username'               => $username,
                    'full_name'              => $fullName,
                    'staff_name'             => $fullName,
                    'name'                   => $fullName,
                    'avatar'                 => $staff->avatar,
                    'staff_avatar'           => $staff->avatar,
                    'email'                  => $staff->email,
                    'staff_email'            => $staff->email,
                    'kol_count'              => 0,
                    'total_deal_amount'      => 0,
                    'total_tasks'            => 0,
                    'completed_tasks'        => 0,
                    'total_postings'         => 0,
                    'posted_count'           => 0,
                    'on_time_postings'       => 0,
                    'late_postings'          => 0,
                    'pending_postings'       => 0,
                    'decided_postings'       => 0,
                    'on_time_rate'           => 0,
                    'on_time_points'         => 0,
                    'avg_turnaround_hours'   => 0,
                    'avg_tat_hours'          => 0,
                    'tat_points'             => 0,
                    'avg_dispatch_days'      => 0,
                    'dispatch_points'        => 0,
                    'score'                  => null,
                    'total_score'            => 0,
                    'max_earned'             => 0,
                    'grade'                  => 'PENDING',
                    'grade_label'            => 'Belum Bertugas',
                    'grade_badge'            => 'badge-soft-slate',
                    'grade_class'            => 'badge-secondary',
                    'is_provisional'         => false,
                    'provisional_reasons'    => [],
                    'excluded'               => true,
                    'actual_spent'           => 0,
                    'actual_views'           => 0,
                    'actual_engagements'     => 0,
                    'actual_cpv'             => 0,
                    'actual_cpe'             => 0,
                    'tasks'                  => [],
                ];
                $results[] = new EvaluationItem($itemData);
                continue;
            }

            // Points calculation
            $missing = [];
            $score = 0;
            $maxEarned = 0;

            if ($onTimeRate !== null) {
                $onTimePoints = $this->pointsFromBrackets($onTimeRate, $cfg['on_time_brackets'], 'min');
                $score += $onTimePoints;
                $maxEarned += 40;
            } else {
                $onTimePoints = 0;
                $missing[] = 'on_time';
            }

            if ($avgTatHours !== null) {
                $tatPoints = $this->pointsFromBrackets($avgTatHours, $cfg['tat_brackets'], 'max');
                $score += $tatPoints;
                $maxEarned += 30;
            } else {
                $tatPoints = 0;
                $missing[] = 'tat';
            }

            if ($avgDispatchDays !== null) {
                $dispatchPoints = $this->pointsFromBrackets($avgDispatchDays, $cfg['dispatch_brackets'], 'max');
                $score += $dispatchPoints;
                $maxEarned += 30;
            } else {
                $dispatchPoints = 0;
                $missing[] = 'dispatch';
            }

            // Provisional guard
            $provReasons = [];
            if ($maturity !== null) {
                if (!empty($maturity['provisional']) && !empty($maturity['reason'])) {
                    $provReasons[] = $maturity['reason'];
                }
            } else {
                $minPosts = (int)($cfg['min_decided_postings'] ?? 5);
                $minDrafts = (int)($cfg['min_reviewed_drafts'] ?? 3);
                if ($decided < $minPosts) {
                    $provReasons[] = "baru {$decided} dari {$minPosts} konten dinilai";
                }
                if ($reviewedDrafts < $minDrafts) {
                    $provReasons[] = "baru {$reviewedDrafts} dari {$minDrafts} draf diulas";
                }
            }
            if (!empty($missing)) {
                $dimNames = ['on_time' => 'ketepatan tayang', 'tat' => 'balasan draf', 'dispatch' => 'kirim sampel'];
                $readable = array_map(fn($m) => $dimNames[$m] ?? $m, $missing);
                $provReasons[] = 'belum ada data: ' . implode(', ', $readable);
            }
            $isProvisional = !empty($provReasons);

            if ($score >= $cfg['grade_a_min']) {
                $grade = 'A';
                $gradeLabel = 'Grade A (Sangat Baik)';
                $gradeBadge = 'badge-soft-emerald';
                $gradeClass = 'bg-success';
            } elseif ($score >= $cfg['grade_b_min']) {
                $grade = 'B';
                $gradeLabel = 'Grade B (Baik)';
                $gradeBadge = 'badge-soft-blue';
                $gradeClass = 'bg-primary';
            } else {
                $grade = 'C';
                $gradeLabel = 'Grade C (Perlu Evaluasi)';
                $gradeBadge = 'badge-soft-rose';
                $gradeClass = 'bg-warning text-dark';
            }

            if ($isProvisional) {
                $suffix = $cfg['provisional_suffix'] ?? 'Sementara';
                $gradeLabel .= ' (' . $suffix . ')';
            }

            $itemData = [
                'id'                     => $sid,
                'staff_id'               => $sid,
                'username'               => $username,
                'full_name'              => $fullName,
                'staff_name'             => $fullName,
                'name'                   => $fullName,
                'avatar'                 => $staff->avatar,
                'staff_avatar'           => $staff->avatar,
                'email'                  => $staff->email,
                'staff_email'            => $staff->email,
                'kol_count'              => $kolCount,
                'total_deal_amount'      => $totalDealAmount,
                'total_tasks'            => $totalTasks,
                'completed_tasks'        => $completedTasks,
                'total_postings'         => $totalPostings,
                'posted_count'           => $totalPostings,
                'on_time_postings'       => $onTimePostings,
                'late_postings'          => $latePostings,
                'pending_postings'       => $pendingPostings,
                'decided_postings'       => $decided,
                'on_time_rate'           => $onTimeRate ?? 0,
                'on_time_points'         => $onTimePoints,
                'avg_turnaround_hours'   => $avgTatHours ?? 0,
                'avg_tat_hours'          => $avgTatHours ?? 0,
                'tat_points'             => $tatPoints,
                'avg_revision_cycles'    => $avgRevisionCycles,
                'reviewed_drafts'        => $reviewedDrafts,
                'total_drafts'           => $totalDrafts,
                'avg_dispatch_days'      => $avgDispatchDays ?? 0,
                'dispatch_points'        => $dispatchPoints,
                'total_shipments'        => $totalShipments,
                'pending_shipments'      => $pendingShipments,
                'score'                  => $score,
                'total_score'            => $score,
                'max_earned'             => $maxEarned,
                'grade'                  => $grade,
                'grade_label'            => $gradeLabel,
                'grade_badge'            => $gradeBadge,
                'grade_class'            => $gradeClass,
                'is_provisional'         => $isProvisional,
                'provisional_reasons'    => $provReasons,
                'excluded'               => false,
                'actual_spent'           => $actualSpent,
                'actual_views'           => $actualViews,
                'actual_engagements'     => $actualEngagements,
                'actual_cpv'             => $actualCpv,
                'actual_cpe'             => $actualCpe,
                'task_completion_rate'   => $completionRate,
                'tasks'                  => $tasksMap[$sid] ?? [],
            ];

            $results[] = new EvaluationItem($itemData);
        }

        return $results;
    }

    /**
     * Aggregate Operational Team Summary
     */
    public function get_operational_team_summary(array $staffKpis = []): EvaluationItem
    {
        return $this->getOperationalTeamSummary($staffKpis);
    }

    public function getOperationalTeamSummary(array $staffKpis = []): EvaluationItem
    {
        $totalStaff = count($staffKpis);
        $activeStaff = 0;
        $pendingStaff = 0;
        $provisionalStaff = 0;
        $totalKols = 0;
        $totalTasks = 0;
        $completedTasks = 0;
        $totalPostings = 0;
        $onTimePostings = 0;
        $latePostings = 0;
        $decidedPostings = 0;

        $turnaroundSum = 0;
        $turnaroundCount = 0;
        $dispatchSum = 0;
        $dispatchCount = 0;
        $totalSpent = 0;
        $totalViews = 0;
        $totalEngagements = 0;

        $totScore = 0;
        $totOnTime = 0;
        $totTat = 0;
        $countA = 0;
        $countB = 0;
        $countC = 0;

        foreach ($staffKpis as $s) {
            if (!empty($s['excluded'])) {
                $pendingStaff++;
                continue;
            }

            if (($s['kol_count'] ?? 0) > 0 || ($s['total_tasks'] ?? 0) > 0) {
                $activeStaff++;
            }
            if (!empty($s['is_provisional'])) {
                $provisionalStaff++;
            }

            $totalKols += (int)($s['kol_count'] ?? 0);
            $totalTasks += (int)($s['total_tasks'] ?? 0);
            $completedTasks += (int)($s['completed_tasks'] ?? 0);
            $totalPostings += (int)($s['total_postings'] ?? 0);
            $onTimePostings += (int)($s['on_time_postings'] ?? 0);
            $latePostings += (int)($s['late_postings'] ?? 0);
            $decidedPostings += (int)($s['decided_postings'] ?? 0);

            $totalSpent += (float)($s['actual_spent'] ?? 0);
            $totalViews += (int)($s['actual_views'] ?? 0);
            $totalEngagements += (int)($s['actual_engagements'] ?? 0);

            if (($s['avg_turnaround_hours'] ?? null) !== null && $s['avg_turnaround_hours'] > 0) {
                $turnaroundSum += (float)$s['avg_turnaround_hours'];
                $turnaroundCount++;
            }
            if (($s['avg_dispatch_days'] ?? null) !== null && $s['avg_dispatch_days'] > 0) {
                $dispatchSum += (float)$s['avg_dispatch_days'];
                $dispatchCount++;
            }

            $totScore += (float)($s['total_score'] ?? $s['score'] ?? 0);
            $totOnTime += (float)($s['on_time_rate'] ?? 0);
            $totTat += (float)($s['avg_turnaround_hours'] ?? 0);

            if (($s['grade'] ?? '') === 'A') $countA++;
            elseif (($s['grade'] ?? '') === 'B') $countB++;
            else $countC++;
        }

        $activeDiv = max(1, $activeStaff);
        $teamOnTimeRate = ($decidedPostings > 0) ? round(($onTimePostings / $decidedPostings) * 100, 1) : 0;
        $teamAvgTat = ($turnaroundCount > 0) ? round($turnaroundSum / $turnaroundCount, 1) : 0;
        $teamAvgDispatch = ($dispatchCount > 0) ? round($dispatchSum / $dispatchCount, 1) : 0;
        $taskCompletionRate = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        $avgScore = round($totScore / $activeDiv, 1);

        $summaryData = [
            'total_staff'                => $totalStaff,
            'active_staff'               => $activeStaff,
            'pending_staff'              => $pendingStaff,
            'provisional_staff'          => $provisionalStaff,
            'total_kols'                 => $totalKols,
            'total_tasks'                => $totalTasks,
            'completed_tasks'            => $completedTasks,
            'total_postings'             => $totalPostings,
            'on_time_postings'           => $onTimePostings,
            'late_postings'              => $latePostings,
            'decided_postings'           => $decidedPostings,
            'team_on_time_rate'          => $teamOnTimeRate,
            'avg_on_time_rate'           => $teamOnTimeRate,
            'team_avg_turnaround_hours'  => $teamAvgTat,
            'avg_tat'                    => $teamAvgTat,
            'team_avg_dispatch_days'     => $teamAvgDispatch,
            'task_completion_rate'       => $taskCompletionRate,
            'total_spent'                => $totalSpent,
            'total_views'                => $totalViews,
            'total_engagements'          => $totalEngagements,
            'team_avg_cpv'               => ($totalViews > 0) ? round($totalSpent / $totalViews, 2) : 0,
            'team_avg_cpe'               => ($totalEngagements > 0) ? round($totalSpent / $totalEngagements, 2) : 0,
            'avg_team_score'             => $avgScore,
            'grade_a_count'              => $countA,
            'grade_b_count'              => $countB,
            'grade_c_count'              => $countC,
        ];

        return new EvaluationItem($summaryData);
    }

    /**
     * Legend describing scoring brackets & grade definitions
     */
    public function get_legend(): array
    {
        return $this->getLegend();
    }

    public function getLegend(): array
    {
        $cfg = $this->getThresholds();

        $fmtRate = function(array $brackets) {
            $parts = [];
            $prevMin = null;
            foreach ($brackets as $b) {
                $min = (int)$b['min'];
                $pts = (int)$b['points'];
                if ($prevMin === null) {
                    $parts[] = $min . '% ke atas = ' . $pts . ' poin';
                } elseif ($min <= 0) {
                    $parts[] = 'di bawah ' . $prevMin . '% = ' . $pts . ' poin';
                } else {
                    $parts[] = $min . '% sampai di bawah ' . $prevMin . '% = ' . $pts . ' poin';
                }
                $prevMin = $min;
            }
            return implode('; ', $parts);
        };

        $fmtSpeed = function(array $brackets, string $unit) {
            $parts = [];
            $prevMax = null;
            foreach ($brackets as $b) {
                $pts = (int)$b['points'];
                $isLast = ($b['max'] >= 999);
                if ($prevMax === null && !$isLast) {
                    $parts[] = 'sampai ' . $b['max'] . ' ' . $unit . ' = ' . $pts . ' poin';
                } elseif ($isLast) {
                    $parts[] = 'lebih dari ' . $prevMax . ' ' . $unit . ' = ' . $pts . ' poin';
                } else {
                    $parts[] = 'lebih dari ' . $prevMax . ' sampai ' . $b['max'] . ' ' . $unit . ' = ' . $pts . ' poin';
                }
                if (!$isLast) {
                    $prevMax = $b['max'];
                }
            }
            return implode('; ', $parts);
        };

        $gradeAMin = (int)$cfg['grade_a_min'];
        $gradeBMin = (int)$cfg['grade_b_min'];
        $minDecided = (int)$cfg['min_decided_postings'];
        $minDrafts = (int)$cfg['min_reviewed_drafts'];
        $graceDays = (int)($cfg['evaluation_grace_days'] ?? 7);

        return [
            'grades' => [
                ['label' => 'Grade A (Sangat Baik)', 'badge' => 'badge-soft-emerald', 'rule' => 'Skor ' . $gradeAMin . ' ke atas'],
                ['label' => 'Grade B (Baik)', 'badge' => 'badge-soft-blue', 'rule' => 'Skor ' . $gradeBMin . ' sampai di bawah ' . $gradeAMin],
                ['label' => 'Grade C (Perlu Evaluasi)', 'badge' => 'badge-soft-rose', 'rule' => 'Skor di bawah ' . $gradeBMin],
                ['label' => 'Belum Bertugas', 'badge' => 'badge-soft-slate', 'rule' => 'Belum menangani KOL dan belum ada task, tidak ikut rata-rata tim'],
                ['label' => 'Nilai Sementara*', 'badge' => 'badge-soft-amber', 'rule' => 'Campaign belum selesai / masih dalam masa scraping H+' . $graceDays . ' setelah end date; di mode Semua Kampanye juga jika data masih sedikit (kurang dari ' . $minDecided . ' konten dinilai / kurang dari ' . $minDrafts . ' draf diulas) atau ada bagian yang belum ada datanya'],
            ],
            'groups' => [
                [
                    'title' => 'Ketepatan Waktu Tayang (bobot 40 poin) - Staff',
                    'desc'  => 'Berapa banyak konten yang tayang tepat waktu dari yang sudah dinilai. ' . $fmtRate($cfg['on_time_brackets']) . '.',
                ],
                [
                    'title' => 'Kecepatan Membalas Draf (bobot 30 poin) - Staff',
                    'desc'  => 'Rata-rata waktu staf membalas draf dalam jam. ' . $fmtSpeed($cfg['tat_brackets'], 'jam') . '.',
                ],
                [
                    'title' => 'Kecepatan Kirim Sampel (bobot 30 poin) - Staff',
                    'desc'  => 'Rata-rata hari dari kesepakatan sampai paket dikirim. ' . $fmtSpeed($cfg['dispatch_brackets'], 'hari') . '.',
                ],
                [
                    'title' => 'Views Attainment (bobot 50 poin) - Leader',
                    'desc'  => 'Pencapaian jumlah tayangan dibandingkan target campaign. ' . $fmtRate($cfg['leader_view_brackets'] ?? []) . '.',
                ],
                [
                    'title' => 'CPV Efficiency (bobot 20 poin) - Leader',
                    'desc'  => 'Efisiensi Cost Per View. Makin rendah CPV makin baik. ' . $fmtSpeed($cfg['leader_cpv_brackets'] ?? [], 'Rupiah/View') . '.',
                ],
                [
                    'title' => 'Deal Conversion (bobot 15 poin) - Leader',
                    'desc'  => 'Persentase KOL yang berhasil mencapai deal dari total negosiasi. ' . $fmtRate($cfg['leader_deal_brackets'] ?? []) . '.',
                ],
                [
                    'title' => 'Approval TAT Leader (bobot 15 poin) - Leader',
                    'desc'  => 'Kecepatan leader menyetujui draf konten dalam jam. ' . $fmtSpeed($cfg['leader_approval_brackets'] ?? [], 'jam') . '.',
                ],
            ],
            'minimums' => [
                'decided'    => $minDecided,
                'drafts'     => $minDrafts,
                'grace_days' => $graceDays,
            ],
        ];
    }

    /**
     * Get Leader Count
     */
    public function get_leader_count(?int $campaignId = null): int
    {
        return $this->getLeaderCount($campaignId);
    }

    public function getLeaderCount(?int $campaignId = null): int
    {
        $cfg = $this->getThresholds();
        $targetIds = $cfg['leader_target_user_ids'] ?? [2, 1, 25];

        $builder = $this->db->table('users u')
            ->select('u.id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where("(r.name IN ('leader', 'admin') OR r.id IN (1, 2) OR u.id IN (" . implode(',', $targetIds) . ")
                     OR u.id IN (SELECT DISTINCT pic_user_id FROM campaigns WHERE pic_user_id IS NOT NULL))", null, false);

        if ($campaignId && $campaignId > 0) {
            $builder->where("(u.id IN (SELECT COALESCE(pic_user_id, created_by) FROM campaigns WHERE id = {$campaignId}))", null, false);
        }

        $builder->groupBy('u.id');
        return $builder->countAllResults();
    }

    /**
     * Get Leader KPI Scorecard
     */
    public function get_leader_kpis(?int $campaignId = null, int $limit = 200, int $offset = 0): array
    {
        return $this->getLeaderKpis($campaignId, $limit, $offset);
    }

    public function getLeaderKpis(?int $campaignId = null, int $limit = 200, int $offset = 0): array
    {
        $cfg = $this->getThresholds();
        $cid = (int)$campaignId;
        $campWhere = ($cid > 0) ? " AND c.id = " . $cid : "";
        $targetIds = $cfg['leader_target_user_ids'] ?? [2, 1, 25];

        $maturity = $this->singleCampaignMaturity($cid, $cfg);

        // 1. Leader list
        $leaderBuilder = $this->db->table('users u')
            ->select('u.id, u.username, COALESCE(u.full_name, u.name) as full_name, u.name, u.avatar, u.email')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->where("(r.name IN ('leader', 'admin') OR r.id IN (1, 2) OR u.id IN (" . implode(',', $targetIds) . ")
                     OR u.id IN (SELECT DISTINCT pic_user_id FROM campaigns WHERE pic_user_id IS NOT NULL))", null, false);

        if ($cid > 0) {
            $leaderBuilder->where("(u.id IN (SELECT COALESCE(pic_user_id, created_by) FROM campaigns WHERE id = {$cid}))", null, false);
        }

        $leaderBuilder->groupBy('u.id')
            ->orderBy('full_name', 'ASC')
            ->limit($limit, $offset);

        $leaderList = $leaderBuilder->get()->getResult();
        if (empty($leaderList)) {
            return [];
        }

        $leaderIds = array_map(fn($l) => (int)$l->id, $leaderList);
        $idList = implode(',', $leaderIds);

        // 2. Campaign Aggregates per Leader
        $campRows = $this->db->query("
            SELECT COALESCE(c.pic_user_id, c.created_by) AS leader_id,
                   COUNT(DISTINCT c.id) AS total_campaigns,
                   COALESCE(SUM(COALESCE(c.budget_total, c.budget, 0)), 0) AS total_budget,
                   COALESCE(SUM(c.target_views), 0) AS total_target_views
            FROM campaigns c
            WHERE COALESCE(c.pic_user_id, c.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(c.pic_user_id, c.created_by)
        ")->getResult();
        $campMap = [];
        foreach ($campRows as $r) {
            $campMap[(int)$r->leader_id] = $r;
        }

        // 3. Actual Spent & Views
        $actualRows = $this->db->query("
            SELECT COALESCE(c.pic_user_id, c.created_by) AS leader_id,
                   COALESCE(SUM(t.budget), 0) AS actual_spent,
                   COALESCE(SUM(COALESCE(pm.views, pm.views_count, 0)), 0) AS actual_views,
                   COALESCE(SUM(COALESCE(pm.likes, pm.likes_count, 0) + COALESCE(pm.comments, pm.comments_count, 0) + COALESCE(pm.shares, pm.shares_count, 0) + COALESCE(pm.saves, pm.saves_count, 0)), 0) AS actual_engagements,
                   SUM(CASE WHEN pm.id IS NOT NULL THEN 1 ELSE 0 END) AS tracked_postings
            FROM campaigns c
            JOIN campaign_kols ck ON ck.campaign_id = c.id
            JOIN tasks t ON t.campaign_kol_id = ck.id
            LEFT JOIN postings p ON p.task_id = t.id
            LEFT JOIN posting_metrics pm ON pm.id = COALESCE(p.latest_metric_id, (SELECT MAX(pm2.id) FROM posting_metrics pm2 WHERE pm2.posting_id = p.id))
            WHERE COALESCE(c.pic_user_id, c.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(c.pic_user_id, c.created_by)
        ")->getResult();
        $actualMap = [];
        foreach ($actualRows as $r) {
            $actualMap[(int)$r->leader_id] = $r;
        }

        // 4. Deal Conversion
        $dealRows = $this->db->query("
            SELECT COALESCE(c.pic_user_id, c.created_by) AS leader_id,
                   COUNT(ck.id) AS total_negotiated,
                   SUM(CASE WHEN ck.status IN ('ACTIVE', 'COMPLETED') THEN 1 ELSE 0 END) AS deal_success
            FROM campaigns c
            JOIN campaign_kols ck ON ck.campaign_id = c.id
            WHERE COALESCE(c.pic_user_id, c.created_by) IN ({$idList}) {$campWhere}
            GROUP BY COALESCE(c.pic_user_id, c.created_by)
        ")->getResult();
        $dealMap = [];
        foreach ($dealRows as $r) {
            $dealMap[(int)$r->leader_id] = $r;
        }

        // 5. Approval TAT
        $tatRows = $this->db->query("
            SELECT leader_id,
                   AVG(tat_hours) AS avg_tat,
                   SUM(is_fallback) AS tat_fallback,
                   COUNT(*) AS reviewed_items
            FROM (
                SELECT COALESCE(c.pic_user_id, c.created_by) AS leader_id, d.id,
                       CASE WHEN MIN(h.created_at) IS NOT NULL
                            THEN TIMESTAMPDIFF(MINUTE, d.created_at, MIN(h.created_at)) / 60
                            WHEN d.reviewed_at IS NOT NULL AND d.submitted_at IS NOT NULL
                            THEN TIMESTAMPDIFF(MINUTE, d.submitted_at, d.reviewed_at) / 60
                            ELSE GREATEST(TIMESTAMPDIFF(MINUTE, d.created_at, d.updated_at) / 60, 0.1)
                       END AS tat_hours,
                       CASE WHEN MIN(h.created_at) IS NOT NULL OR (d.reviewed_at IS NOT NULL AND d.submitted_at IS NOT NULL) THEN 0 ELSE 1 END AS is_fallback
                FROM drafts d
                JOIN tasks t ON t.id = d.task_id
                JOIN campaign_kols ck ON ck.id = t.campaign_kol_id
                JOIN campaigns c ON c.id = ck.campaign_id
                LEFT JOIN draft_review_histories h ON h.draft_id = d.id AND h.action IN ('REVISION', 'APPROVED')
                WHERE COALESCE(c.pic_user_id, c.created_by) IN ({$idList}) {$campWhere}
                  AND d.status IN ('APPROVED', 'REVISION')
                GROUP BY d.id
            ) x
            GROUP BY leader_id
        ")->getResult();
        $tatMap = [];
        foreach ($tatRows as $r) {
            $tatMap[(int)$r->leader_id] = $r;
        }

        // 6. Campaign drill-down per leader
        $campDetailRows = $this->db->query("
            SELECT COALESCE(c.pic_user_id, c.created_by) AS leader_id,
                   c.id AS campaign_id, COALESCE(c.title, c.name) AS campaign_name, c.status,
                   c.target_views, COALESCE(c.budget_total, c.budget, 0) AS budget,
                   c.target_cpv, c.target_cpe, c.target_er,
                   COALESCE(SUM(COALESCE(pm.views, pm.views_count, 0)), 0) AS actual_views,
                   COALESCE(SUM(COALESCE(pm.likes, pm.likes_count, 0) + COALESCE(pm.comments, pm.comments_count, 0) + COALESCE(pm.shares, pm.shares_count, 0) + COALESCE(pm.saves, pm.saves_count, 0)), 0) AS actual_engagements,
                   COALESCE(SUM(t.budget), 0) AS actual_spent
            FROM campaigns c
            LEFT JOIN campaign_kols ck ON ck.campaign_id = c.id
            LEFT JOIN tasks t ON t.campaign_kol_id = ck.id
            LEFT JOIN postings p ON p.task_id = t.id
            LEFT JOIN posting_metrics pm ON pm.id = COALESCE(p.latest_metric_id, (SELECT MAX(pm2.id) FROM posting_metrics pm2 WHERE pm2.posting_id = p.id))
            WHERE COALESCE(c.pic_user_id, c.created_by) IN ({$idList}) {$campWhere}
            GROUP BY c.id
            ORDER BY c.id DESC
            LIMIT 500
        ")->getResult();
        $campDetailMap = [];
        foreach ($campDetailRows as $row) {
            $lid = (int)$row->leader_id;
            if (!isset($campDetailMap[$lid])) $campDetailMap[$lid] = [];
            $row->actual_cpv = ($row->actual_views > 0) ? round($row->actual_spent / $row->actual_views, 2) : 0;
            $row->actual_cpe = ($row->actual_engagements > 0) ? round($row->actual_spent / $row->actual_engagements, 2) : 0;
            $campDetailMap[$lid][] = $row;
        }

        // 7. Hydrate Leader Items
        $results = [];
        foreach ($leaderList as $ldr) {
            $lid = (int)$ldr->id;
            $username = $ldr->username ?: explode('@', $ldr->email)[0];
            $fullName = $ldr->full_name ?: ($ldr->name ?: $username);

            $c = $campMap[$lid] ?? null;
            $totalCampaigns = (int)($c->total_campaigns ?? 0);
            $totalBudget = (float)($c->total_budget ?? 0);
            $totalTargetViews = (int)($c->total_target_views ?? 0);

            $a = $actualMap[$lid] ?? null;
            $actualSpent = (float)($a->actual_spent ?? 0);
            $actualViews = (int)($a->actual_views ?? 0);
            $actualEngagements = (int)($a->actual_engagements ?? 0);
            $trackedPostings = (int)($a->tracked_postings ?? 0);

            $d = $dealMap[$lid] ?? null;
            $totalNegotiated = (int)($d->total_negotiated ?? 0);
            $dealSuccess = (int)($d->deal_success ?? 0);

            $t = $tatMap[$lid] ?? null;
            $avgTatHours = ($t && $t->avg_tat !== null) ? round((float)$t->avg_tat, 1) : null;

            // Ratios
            $actualCpv = ($actualViews > 0) ? round($actualSpent / $actualViews, 2) : 0;
            $actualCpe = ($actualEngagements > 0) ? round($actualSpent / $actualEngagements, 2) : 0;
            $totalTargetCpv = ($totalTargetViews > 0 && $totalBudget > 0) ? round($totalBudget / $totalTargetViews, 2) : 0;
            $viewAttainmentPct = ($totalTargetViews > 0) ? round(($actualViews / $totalTargetViews) * 100, 1) : 0;
            $budgetEfficiencyPct = ($totalBudget > 0) ? round(($actualSpent / $totalBudget) * 100, 1) : 0;
            $dealConversionPct = ($totalNegotiated > 0) ? round(($dealSuccess / $totalNegotiated) * 100, 1) : 0;

            if ($totalCampaigns === 0) {
                $itemData = [
                    'id'                    => $lid,
                    'leader_id'             => $lid,
                    'username'              => $username,
                    'full_name'             => $fullName,
                    'leader_name'           => $fullName,
                    'name'                  => $fullName,
                    'avatar'                => $ldr->avatar,
                    'leader_avatar'         => $ldr->avatar,
                    'email'                 => $ldr->email,
                    'leader_email'          => $ldr->email,
                    'total_campaigns'       => 0,
                    'total_budget'          => 0,
                    'total_target_views'    => 0,
                    'target_views'          => 0,
                    'actual_spent'          => 0,
                    'actual_views'          => 0,
                    'actual_engagements'    => 0,
                    'tracked_postings'      => 0,
                    'total_negotiated'      => 0,
                    'deal_success'          => 0,
                    'avg_turnaround_hours'  => 0,
                    'actual_cpv'            => 0,
                    'actual_cpe'            => 0,
                    'cpv'                   => 0,
                    'view_attainment_pct'   => 0,
                    'view_rate'             => 0,
                    'budget_efficiency_pct' => 0,
                    'budget_rate'           => 0,
                    'deal_conversion_pct'   => 0,
                    'score'                 => null,
                    'total_score'           => 0,
                    'max_earned'            => 0,
                    'grade'                 => 'PENDING',
                    'grade_label'           => 'Belum Ada Campaign',
                    'grade_badge'           => 'badge-soft-slate',
                    'grade_class'           => 'badge-secondary',
                    'is_provisional'        => false,
                    'provisional_reasons'   => [],
                    'excluded'              => true,
                    'campaigns'             => [],
                ];
                $results[] = new EvaluationItem($itemData);
                continue;
            }

            // Scoring
            $missing = [];
            $score = 0;
            $maxEarned = 0;

            // View Attainment (Max 50)
            if ($viewAttainmentPct > 0 || $totalTargetViews > 0) {
                $viewPoints = $this->pointsFromBrackets($viewAttainmentPct, $cfg['leader_view_brackets'] ?? [], 'min');
                $score += $viewPoints;
                $maxEarned += 50;
            } else {
                $viewPoints = 0;
                $missing[] = 'views';
            }

            // CPV Efficiency (Max 20)
            if ($actualCpv > 0 || $actualSpent > 0) {
                $cpvPoints = $this->pointsFromBrackets($actualCpv, $cfg['leader_cpv_brackets'] ?? [], 'max');
                $score += $cpvPoints;
                $maxEarned += 20;
            } else {
                $cpvPoints = 0;
                $missing[] = 'cpv';
            }

            // Deal Conversion (Max 15)
            if ($totalNegotiated > 0) {
                $dealPoints = $this->pointsFromBrackets($dealConversionPct, $cfg['leader_deal_brackets'] ?? [], 'min');
                $score += $dealPoints;
                $maxEarned += 15;
            } else {
                $dealPoints = 0;
                $missing[] = 'deal';
            }

            // Approval TAT (Max 15)
            if ($avgTatHours !== null) {
                $approvalPoints = $this->pointsFromBrackets($avgTatHours, $cfg['leader_approval_brackets'] ?? [], 'max');
                $score += $approvalPoints;
                $maxEarned += 15;
            } else {
                $approvalPoints = 0;
                $missing[] = 'tat';
            }

            // Provisional guard
            $provReasons = [];
            if ($maturity !== null) {
                if (!empty($maturity['provisional']) && !empty($maturity['reason'])) {
                    $provReasons[] = $maturity['reason'];
                }
            } else {
                $minCamp = (int)($cfg['min_leader_campaigns'] ?? 3);
                if ($totalCampaigns < $minCamp) {
                    $provReasons[] = "baru {$totalCampaigns} dari {$minCamp} campaign minimum";
                }
            }
            if (!empty($missing)) {
                $dimNames = ['views' => 'Views Attainment', 'cpv' => 'CPV Efficiency', 'deal' => 'Deal Conversion', 'tat' => 'Approval TAT'];
                $readable = array_map(fn($m) => $dimNames[$m] ?? $m, $missing);
                $provReasons[] = 'belum ada data: ' . implode(', ', $readable);
            }
            $isProvisional = !empty($provReasons);

            $gradeAMin = (int)($cfg['leader_grade_a_min'] ?? 85);
            $gradeBMin = (int)($cfg['leader_grade_b_min'] ?? 65);
            if ($score >= $gradeAMin) {
                $grade = 'A';
                $gradeLabel = 'Grade A (Sangat Baik)';
                $gradeBadge = 'badge-soft-emerald';
                $gradeClass = 'bg-success';
            } elseif ($score >= $gradeBMin) {
                $grade = 'B';
                $gradeLabel = 'Grade B (Baik)';
                $gradeBadge = 'badge-soft-blue';
                $gradeClass = 'bg-primary';
            } else {
                $grade = 'C';
                $gradeLabel = 'Grade C (Perlu Evaluasi)';
                $gradeBadge = 'badge-soft-rose';
                $gradeClass = 'bg-warning text-dark';
            }

            if ($isProvisional) {
                $suffix = $cfg['provisional_suffix'] ?? 'Sementara';
                $gradeLabel .= ' (' . $suffix . ')';
            }

            $itemData = [
                'id'                    => $lid,
                'leader_id'             => $lid,
                'username'              => $username,
                'full_name'             => $fullName,
                'leader_name'           => $fullName,
                'name'                  => $fullName,
                'avatar'                => $ldr->avatar,
                'leader_avatar'         => $ldr->avatar,
                'email'                 => $ldr->email,
                'leader_email'          => $ldr->email,
                'total_campaigns'       => $totalCampaigns,
                'total_budget'          => $totalBudget,
                'total_target_views'    => $totalTargetViews,
                'target_views'          => $totalTargetViews,
                'actual_spent'          => $actualSpent,
                'actual_views'          => $actualViews,
                'actual_engagements'    => $actualEngagements,
                'tracked_postings'      => $trackedPostings,
                'total_negotiated'      => $totalNegotiated,
                'deal_success'          => $dealSuccess,
                'avg_turnaround_hours'  => $avgTatHours ?? 0,
                'actual_cpv'            => $actualCpv,
                'actual_cpe'            => $actualCpe,
                'cpv'                   => $actualCpv,
                'view_attainment_pct'   => $viewAttainmentPct,
                'view_rate'             => $viewAttainmentPct,
                'view_points'           => $viewPoints,
                'budget_efficiency_pct' => $budgetEfficiencyPct,
                'budget_rate'           => $budgetEfficiencyPct,
                'budget_points'         => $cpvPoints,
                'deal_conversion_pct'   => $dealConversionPct,
                'deal_points'           => $dealPoints,
                'approval_points'       => $approvalPoints,
                'score'                 => $score,
                'total_score'           => $score,
                'max_earned'            => $maxEarned,
                'grade'                 => $grade,
                'grade_label'           => $gradeLabel,
                'grade_badge'           => $gradeBadge,
                'grade_class'           => $gradeClass,
                'is_provisional'        => $isProvisional,
                'provisional_reasons'   => $provReasons,
                'excluded'              => false,
                'campaigns'             => $campDetailMap[$lid] ?? [],
            ];

            $results[] = new EvaluationItem($itemData);
        }

        return $results;
    }

    /**
     * Aggregate Leader Team Summary
     */
    public function get_leader_team_summary(array $leaderKpis = []): EvaluationItem
    {
        return $this->getLeaderTeamSummary($leaderKpis);
    }

    public function getLeaderTeamSummary(array $leaderKpis = []): EvaluationItem
    {
        $totalLeaders = count($leaderKpis);
        $activeLeaders = 0;
        $provisionalLeaders = 0;

        $totalViews = 0;
        $targetViews = 0;
        $totalSpent = 0;
        $totalBudget = 0;
        $totalEngagements = 0;
        $dealSuccess = 0;
        $dealTotal = 0;

        $tatSum = 0;
        $tatCount = 0;
        $totScore = 0;

        foreach ($leaderKpis as $l) {
            if (!empty($l['excluded'])) {
                continue;
            }

            $activeLeaders++;
            if (!empty($l['is_provisional'])) {
                $provisionalLeaders++;
            }

            $totalViews += (int)($l['actual_views'] ?? 0);
            $targetViews += (int)($l['total_target_views'] ?? $l['target_views'] ?? 0);
            $totalSpent += (float)($l['actual_spent'] ?? 0);
            $totalBudget += (float)($l['total_budget'] ?? 0);
            $totalEngagements += (int)($l['actual_engagements'] ?? 0);

            $dealSuccess += (int)($l['deal_success'] ?? 0);
            $dealTotal += (int)($l['total_negotiated'] ?? 0);

            if (($l['avg_turnaround_hours'] ?? null) !== null && $l['avg_turnaround_hours'] > 0) {
                $tatSum += (float)$l['avg_turnaround_hours'];
                $tatCount++;
            }

            $totScore += (float)($l['total_score'] ?? $l['score'] ?? 0);
        }

        $activeDiv = max(1, $activeLeaders);
        $viewAttainment = ($targetViews > 0) ? round(($totalViews / $targetViews) * 100, 1) : 0;
        $budgetEfficiency = ($totalBudget > 0) ? round(($totalSpent / $totalBudget) * 100, 1) : 0;
        $dealConversion = ($dealTotal > 0) ? round(($dealSuccess / $dealTotal) * 100, 1) : 0;
        $avgTat = ($tatCount > 0) ? round($tatSum / $tatCount, 1) : 0;

        $summaryData = [
            'total_leaders'          => $totalLeaders,
            'active_leaders'         => $activeLeaders,
            'provisional_leaders'    => $provisionalLeaders,
            'total_views'            => $totalViews,
            'tot_actual_views'       => $totalViews,
            'target_views'           => $targetViews,
            'total_target_views'     => $targetViews,
            'total_spent'            => $totalSpent,
            'total_budget'           => $totalBudget,
            'total_engagements'      => $totalEngagements,
            'deal_success'           => $dealSuccess,
            'deal_total'             => $dealTotal,
            'team_view_attainment'   => $viewAttainment,
            'avg_view_rate'          => $viewAttainment,
            'team_budget_efficiency' => $budgetEfficiency,
            'avg_budget_rate'        => $budgetEfficiency,
            'team_avg_cpv'           => ($totalViews > 0) ? round($totalSpent / $totalViews, 2) : 0,
            'team_avg_cpe'           => ($totalEngagements > 0) ? round($totalSpent / $totalEngagements, 2) : 0,
            'team_target_cpv'        => ($targetViews > 0 && $totalBudget > 0) ? round($totalBudget / $targetViews, 2) : 0,
            'team_deal_conversion'   => $dealConversion,
            'team_avg_approval_tat'  => $avgTat,
            'avg_leader_score'       => round($totScore / $activeDiv, 1),
        ];

        return new EvaluationItem($summaryData);
    }
}
