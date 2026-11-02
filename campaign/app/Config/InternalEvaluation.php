<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class InternalEvaluation extends BaseConfig
{
    /**
     * SLA On-Time brackets: [min_rate => points], evaluated top-down, first match wins. Max 40 pts.
     */
    public array $on_time_brackets = [
        ['min' => 90, 'points' => 40],
        ['min' => 75, 'points' => 30],
        ['min' => 50, 'points' => 20],
        ['min' => 0,  'points' => 10],
    ];

    /**
     * Draft review TAT (hours): [max => points], top-down. Max 30 pts.
     */
    public array $tat_brackets = [
        ['max' => 2,   'points' => 30],
        ['max' => 8,   'points' => 25],
        ['max' => 24,  'points' => 15],
        ['max' => 999, 'points' => 5],
    ];

    /**
     * Sample dispatch (days, deal_date -> shipped_at): [max => points], top-down. Max 30 pts.
     */
    public array $dispatch_brackets = [
        ['max' => 1,   'points' => 30],
        ['max' => 3,   'points' => 25],
        ['max' => 5,   'points' => 15],
        ['max' => 999, 'points' => 5],
    ];

    /**
     * Staff Grade cutoffs (total 100)
     */
    public int $grade_a_min = 85;
    public int $grade_b_min = 65;

    /**
     * Drill-down cap per staff modal (perf guard)
     */
    public int $tasks_per_staff_limit = 50;

    /**
     * Staff list page size
     */
    public int $staff_page_size = 200;

    /**
     * Provisional-grade guards
     */
    public int $min_decided_postings = 5;
    public int $min_reviewed_drafts = 3;
    public string $provisional_suffix = 'Sementara';

    /**
     * Grace period after campaign end_date before a COMPLETED campaign
     * grade becomes final (waiting for Apify scraping H+3/H+7 to mature).
     */
    public int $evaluation_grace_days = 7;

    /**
     * LEADER EVALUATION CONFIGURATIONS
     */
    public array $leader_target_user_ids = [2, 1, 25];

    /**
     * Leader Total Weights: View (50) + CPV (20) + Deal (15) + Approval TAT (15) = 100
     */
    public array $leader_view_brackets = [
        ['min' => 100, 'points' => 50],
        ['min' => 80,  'points' => 40],
        ['min' => 50,  'points' => 20],
        ['min' => 0,   'points' => 10],
    ];

    public array $leader_cpv_brackets = [
        ['max' => 50,  'points' => 20],
        ['max' => 100, 'points' => 15],
        ['max' => 200, 'points' => 5],
        ['max' => 999999, 'points' => 0],
    ];

    public array $leader_deal_brackets = [
        ['min' => 50, 'points' => 15],
        ['min' => 30, 'points' => 10],
        ['min' => 10, 'points' => 5],
        ['min' => 0,  'points' => 0],
    ];

    public array $leader_approval_brackets = [
        ['max' => 2,   'points' => 15],
        ['max' => 8,   'points' => 10],
        ['max' => 24,  'points' => 5],
        ['max' => 999, 'points' => 0],
    ];

    /**
     * Leader Grade cutoffs (total 100)
     */
    public int $leader_grade_a_min = 85;
    public int $leader_grade_b_min = 65;

    /**
     * Minimum campaigns required to remove "Provisional" status for leaders
     */
    public int $min_leader_campaigns = 3;
}
