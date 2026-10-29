<?php

namespace App\Controllers;

use App\Models\InternalEvaluationModel;
use App\Models\CampaignModel;

class InternalEvaluation extends BaseController
{
    protected InternalEvaluationModel $evalModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->evalModel = new InternalEvaluationModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Evaluasi Kinerja Internal Tim Operasional & Leader
     */
    public function index()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $staffKpis = $this->evalModel->getStaffOperationalKpis($campaignId ? (int)$campaignId : null);
        $teamSummary = $this->evalModel->getOperationalTeamSummary($staffKpis);

        $leaderKpis = $this->evalModel->getLeaderKpis($campaignId ? (int)$campaignId : null);
        $leaderSummary = $this->evalModel->getLeaderTeamSummary($leaderKpis);

        $data = [
            'title'         => 'Evaluasi Kinerja Internal — KPI & Scorecard Tim',
            'controller'    => 'InternalEvaluation',
            'staffKpis'     => $staffKpis,
            'teamSummary'   => $teamSummary,
            'leaderKpis'    => $leaderKpis,
            'leaderSummary' => $leaderSummary,
            'campaignId'    => $campaignId,
            'allCampaigns'  => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('evaluations/index', $data);
    }

    /**
     * Alias untuk evaluasi staff
     */
    public function staff()
    {
        return $this->index();
    }

    /**
     * Export scorecard to CSV
     */
    public function exportExcel()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $staffKpis = $this->evalModel->getStaffOperationalKpis($campaignId ? (int)$campaignId : null);

        $filename = 'Scorecard_KPI_Internal_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Staff ID',
            'Nama Staff',
            'Email',
            'Total Tasks',
            'Tugas Tayang',
            'On-Time Rate (%)',
            'Poin On-Time (Max 40)',
            'Rata-rata TAT Review (Jam)',
            'Poin TAT (Max 30)',
            'Rata-rata Pengiriman (Hari)',
            'Poin Logistik (Max 30)',
            'Total Skor KPI (Max 100)',
            'Grade',
            'Predikat'
        ], ',', '"', "\\");

        foreach ($staffKpis as $row) {
            fputcsv($out, [
                $row['staff_id'],
                $row['staff_name'],
                $row['staff_email'],
                $row['total_tasks'],
                $row['posted_count'],
                $row['on_time_rate'] . '%',
                $row['on_time_points'],
                $row['avg_tat_hours'] . ' jam',
                $row['tat_points'],
                $row['avg_dispatch_days'] . ' hari',
                $row['dispatch_points'],
                $row['total_score'],
                $row['grade'],
                $row['grade_label']
            ], ',', '"', "\\");
        }

        fclose($out);
        exit;
    }
}
