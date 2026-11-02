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
        $this->request = service('request');
        $this->response = service('response');
        helper(['url', 'form']);
    }

    /**
     * Halaman Utama Evaluasi Kinerja Internal (Staff & Leader)
     */
    public function index()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $campaignId = ($campaignId !== null && $campaignId !== '') ? (int)$campaignId : null;
        $tab        = (string)($this->request->getGet('tab') ?? 'staff');

        $campaigns = $this->evalModel->getCampaignsList();
        $selectedCampaign = null;
        if (!empty($campaignId)) {
            foreach ($campaigns as $c) {
                if ((int)$c->id === $campaignId) {
                    $selectedCampaign = $c;
                    break;
                }
            }
        }

        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage = 200;
        $offset = ($page - 1) * $perPage;

        $totalStaff = $this->evalModel->getStaffOperationalCount($campaignId);
        $totalPages = max(1, (int)ceil($totalStaff / $perPage));

        $staffKpis   = $this->evalModel->getStaffOperationalKpis($campaignId, $perPage, $offset);
        $teamSummary = $this->evalModel->getOperationalTeamSummary($staffKpis);

        $leaderKpis    = $this->evalModel->getLeaderKpis($campaignId, $perPage, 0);
        $leaderSummary = $this->evalModel->getLeaderTeamSummary($leaderKpis);
        $legend        = $this->evalModel->getLegend();

        $data = [
            'title'              => 'Evaluasi Kinerja Internal — KPI & Scorecard Tim',
            'controller'         => 'InternalEvaluation',
            'tab'                => $tab,
            'campaignId'         => $campaignId,
            'active_campaign_id' => $campaignId,
            'selected_campaign'  => $selectedCampaign,
            'campaigns'          => $campaigns,
            'allCampaigns'       => $campaigns,
            'page'               => $page,
            'per_page'           => $perPage,
            'total_staff'        => $totalStaff,
            'total_pages'        => $totalPages,
            'staffKpis'          => $staffKpis,
            'staff_kpis'         => $staffKpis,
            'teamSummary'        => $teamSummary,
            'team_summary'       => $teamSummary,
            'leaderKpis'         => $leaderKpis,
            'leader_kpis'        => $leaderKpis,
            'leaderSummary'      => $leaderSummary,
            'legend'             => $legend,
        ];

        return view('evaluations/index', $data);
    }

    /**
     * Evaluasi Kinerja Staff Operasional (CI3 Parity)
     */
    public function staff()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $campaignId = ($campaignId !== null && $campaignId !== '') ? (int)$campaignId : null;

        $campaigns = $this->evalModel->getCampaignsList();
        $selectedCampaign = null;
        if (!empty($campaignId)) {
            foreach ($campaigns as $c) {
                if ((int)$c->id === $campaignId) {
                    $selectedCampaign = $c;
                    break;
                }
            }
        }

        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage = 200;
        $offset = ($page - 1) * $perPage;

        $totalStaff = $this->evalModel->getStaffOperationalCount($campaignId);
        $totalPages = max(1, (int)ceil($totalStaff / $perPage));

        $staffKpis   = $this->evalModel->getStaffOperationalKpis($campaignId, $perPage, $offset);
        $teamSummary = $this->evalModel->getOperationalTeamSummary($staffKpis);
        $legend      = $this->evalModel->getLegend();

        $data = [
            'title'              => 'Evaluasi Kinerja Staff Operasional',
            'controller'         => 'InternalEvaluation',
            'active_campaign_id' => $campaignId,
            'campaignId'         => $campaignId,
            'selected_campaign'  => $selectedCampaign,
            'campaigns'          => $campaigns,
            'allCampaigns'       => $campaigns,
            'page'               => $page,
            'per_page'           => $perPage,
            'total_staff'        => $totalStaff,
            'total_pages'        => $totalPages,
            'staffKpis'          => $staffKpis,
            'staff_kpis'         => $staffKpis,
            'teamSummary'        => $teamSummary,
            'team_summary'       => $teamSummary,
            'legend'             => $legend,
            'tab'                => 'staff',
        ];

        return view('internal_evaluation/staff', $data);
    }

    /**
     * Evaluasi Kinerja Leader (CI3 Parity)
     */
    public function leader()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $campaignId = ($campaignId !== null && $campaignId !== '') ? (int)$campaignId : null;

        $campaigns = $this->evalModel->getCampaignsList();
        $selectedCampaign = null;
        if (!empty($campaignId)) {
            foreach ($campaigns as $c) {
                if ((int)$c->id === $campaignId) {
                    $selectedCampaign = $c;
                    break;
                }
            }
        }

        $leaderKpis    = $this->evalModel->getLeaderKpis($campaignId, 200, 0);
        $leaderSummary = $this->evalModel->getLeaderTeamSummary($leaderKpis);
        $legend        = $this->evalModel->getLegend();

        $data = [
            'title'              => 'Evaluasi Kinerja Leader',
            'controller'         => 'InternalEvaluation',
            'active_campaign_id' => $campaignId,
            'campaignId'         => $campaignId,
            'selected_campaign'  => $selectedCampaign,
            'campaigns'          => $campaigns,
            'allCampaigns'       => $campaigns,
            'leaderKpis'         => $leaderKpis,
            'leader_kpis'        => $leaderKpis,
            'leaderSummary'      => $leaderSummary,
            'team_summary'       => $leaderSummary,
            'legend'             => $legend,
            'tab'                => 'leader',
        ];

        return view('internal_evaluation/leader', $data);
    }

    /**
     * Backward compatibility: redirect bookmark lama ke staff_export_excel
     */
    public function export_excel()
    {
        $queryString = $this->request->getUri()->getQuery();
        return redirect()->to(base_url('administrator/internal_evaluation/staff_export_excel' . ($queryString ? '?' . $queryString : '')));
    }

    /**
     * Export Excel/HTML Staff Scorecard (CI3 Parity)
     */
    public function staff_export_excel()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $campaignId = ($campaignId !== null && $campaignId !== '') ? (int)$campaignId : null;

        $staffKpis = $this->evalModel->getStaffOperationalKpis($campaignId, 2000, 0);
        $teamSummary = $this->evalModel->getOperationalTeamSummary($staffKpis);
        $legend = $this->evalModel->getLegend();

        $campaignName = 'Semua Kampanye';
        if (!empty($campaignId)) {
            $c = $this->campaignModel->find($campaignId);
            if ($c) {
                $campaignName = $c['title'] ?? ($c['name'] ?? 'Campaign #' . $campaignId);
            }
        }

        $filename = "Evaluasi_Kinerja_Staff_" . date('Ymd_His') . ".xls";

        $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Laporan Evaluasi Kinerja Staff</title>";
        $html .= "<style>
            .table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
            .table th { background-color: #0f172a; color: #ffffff; padding: 10px; text-align: left; border: 1px solid #cbd5e1; }
            .table td { padding: 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
            h2 { font-family: Arial, sans-serif; margin-bottom: 5px; }
            p { font-family: Arial, sans-serif; font-size: 12px; margin: 4px 0; }
        </style></head><body>";

        $html .= "<h2>Laporan Evaluasi Kinerja Staff Operasional</h2>";
        $html .= "<p>Kampanye: <strong>" . htmlspecialchars($campaignName) . "</strong> | Tanggal Cetak: <strong>" . date('d F Y') . "</strong></p>";
        $html .= "<p>Rata-rata On-Time Posting: <strong>" . $teamSummary->team_on_time_rate . "%</strong> | Respon Review: <strong>" . $teamSummary->team_avg_turnaround_hours . " Jam</strong> | Lead Time Kirim Sampel: <strong>" . $teamSummary->team_avg_dispatch_days . " Hari</strong> | Task Selesai: <strong>" . $teamSummary->completed_tasks . "/" . $teamSummary->total_tasks . "</strong> | Avg CPV: <strong>Rp " . number_format($teamSummary->team_avg_cpv, 0, ',', '.') . "</strong> | Avg CPE: <strong>Rp " . number_format($teamSummary->team_avg_cpe, 0, ',', '.') . "</strong></p>";

        $html .= "<table class='table'>";
        $html .= "<tr>";
        $html .= "<th>No</th>";
        $html .= "<th>Nama Staf</th>";
        $html .= "<th>Username</th>";
        $html .= "<th>KOL Di-handle</th>";
        $html .= "<th>SLA On-Time</th>";
        $html .= "<th>Respon Review Draft</th>";
        $html .= "<th>Putaran Revisi</th>";
        $html .= "<th>Lead Time Kirim Sampel</th>";
        $html .= "<th>Task Tuntas</th>";
        $html .= "<th>CPV</th>";
        $html .= "<th>CPE</th>";
        $html .= "<th>Predikat Kinerja</th>";
        $html .= "</tr>";

        $no = 1;
        foreach ($staffKpis as $s) {
            $slaText = ($s->on_time_rate !== null) ? $s->on_time_rate . '% (' . $s->on_time_postings . '/' . $s->total_postings . ')' : '-';
            $tatText = ($s->avg_turnaround_hours !== null) ? $s->avg_turnaround_hours . ' Jam' : '-';
            $dispText = ($s->avg_dispatch_days !== null) ? $s->avg_dispatch_days . ' Hari' : '-';
            if (!empty($s->pending_shipments) && $s->pending_shipments > 0) {
                $dispText .= ' (' . $s->pending_shipments . ' blm kirim)';
            }
            $revText = ($s->avg_revision_cycles !== null) ? $s->avg_revision_cycles . 'x' : '-';
            $taskText = $s->completed_tasks . '/' . $s->total_tasks;
            $cpvText = ($s->actual_cpv > 0) ? 'Rp ' . number_format($s->actual_cpv, 0, ',', '.') : '-';
            $cpeText = ($s->actual_cpe > 0) ? 'Rp ' . number_format($s->actual_cpe, 0, ',', '.') : '-';

            $gradeText = $s->grade_label;
            if (!empty($s->is_provisional)) {
                $gradeText .= '*';
            }
            $scoreText = ($s->score !== null) ? $s->score . ' (maks ' . $s->max_earned . ')' : '-';

            $html .= "<tr>";
            $html .= "<td>{$no}</td>";
            $html .= "<td><strong>" . htmlspecialchars($s->full_name) . "</strong></td>";
            $html .= "<td>@" . htmlspecialchars($s->username) . "</td>";
            $html .= "<td>" . $s->kol_count . " KOL</td>";
            $html .= "<td>" . $slaText . "</td>";
            $html .= "<td>" . $tatText . "</td>";
            $html .= "<td>" . $revText . "</td>";
            $html .= "<td>" . $dispText . "</td>";
            $html .= "<td>" . $taskText . "</td>";
            $html .= "<td>" . $cpvText . "</td>";
            $html .= "<td>" . $cpeText . "</td>";
            $html .= "<td><strong>" . htmlspecialchars($gradeText) . "</strong><br><span>" . htmlspecialchars($scoreText) . "</span></td>";
            $html .= "</tr>";
            $no++;
        }

        if (empty($staffKpis)) {
            $html .= "<tr><td colspan='12' style='text-align:center;'>Tidak ada data staf operasional.</td></tr>";
        }
        $html .= "</table>";

        // Legend
        $html .= "<p style='margin-top:20px;'><strong>Legenda Penilaian KPI:</strong></p>";
        $html .= "<table class='table' style='margin-top:10px;'>";
        foreach ($legend['grades'] as $g) {
            $html .= "<tr><td style='width:250px;'><strong>" . htmlspecialchars($g['label']) . "</strong></td><td>" . htmlspecialchars($g['rule']) . "</td></tr>";
        }
        foreach ($legend['groups'] as $g) {
            $html .= "<tr><td><strong>" . htmlspecialchars($g['title']) . "</strong></td><td>" . htmlspecialchars($g['desc']) . "</td></tr>";
        }
        $html .= "</table>";
        $html .= "<p style='font-style:italic;'>* Nilai sementara: data masih dalam masa scraping atau belum mencapai batas sampel minimum.</p>";
        $html .= "</body></html>";

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($html);
    }

    /**
     * Export Excel/HTML Leader Scorecard (CI3 Parity)
     */
    public function leader_export_excel()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $campaignId = ($campaignId !== null && $campaignId !== '') ? (int)$campaignId : null;

        $leaderKpis = $this->evalModel->getLeaderKpis($campaignId, 2000, 0);
        $teamSummary = $this->evalModel->getLeaderTeamSummary($leaderKpis);

        $campaignName = 'Semua Kampanye';
        if (!empty($campaignId)) {
            $c = $this->campaignModel->find($campaignId);
            if ($c) {
                $campaignName = $c['title'] ?? ($c['name'] ?? 'Campaign #' . $campaignId);
            }
        }

        $filename = "Evaluasi_Kinerja_Leader_" . date('Ymd_His') . ".xls";

        $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Laporan Evaluasi Kinerja Leader</title>";
        $html .= "<style>
            .table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
            .table th { background-color: #0f172a; color: #ffffff; padding: 10px; text-align: left; border: 1px solid #cbd5e1; }
            .table td { padding: 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
            h2 { font-family: Arial, sans-serif; margin-bottom: 5px; }
            p { font-family: Arial, sans-serif; font-size: 12px; margin: 4px 0; }
        </style></head><body>";

        $html .= "<h2>Laporan Evaluasi Kinerja Leader</h2>";
        $html .= "<p>Kampanye: <strong>" . htmlspecialchars($campaignName) . "</strong> | Tanggal Cetak: <strong>" . date('d F Y') . "</strong></p>";
        $html .= "<p>Views Attainment Tim: <strong>" . $teamSummary->team_view_attainment . "%</strong> | Budget Efficiency: <strong>" . $teamSummary->team_budget_efficiency . "%</strong> | Deal Conversion: <strong>" . $teamSummary->team_deal_conversion . "%</strong> | Approval TAT: <strong>" . $teamSummary->team_avg_approval_tat . " Jam</strong></p>";

        $html .= "<table class='table'>";
        $html .= "<tr>";
        $html .= "<th>No</th>";
        $html .= "<th>Nama Leader</th>";
        $html .= "<th>Username</th>";
        $html .= "<th>Campaign Di-handle</th>";
        $html .= "<th>Views Attainment</th>";
        $html .= "<th>CPV Efficiency</th>";
        $html .= "<th>CPE</th>";
        $html .= "<th>Deal Conversion</th>";
        $html .= "<th>Approval TAT</th>";
        $html .= "<th>Predikat Kinerja</th>";
        $html .= "</tr>";

        $no = 1;
        foreach ($leaderKpis as $l) {
            $tatText = ($l->avg_turnaround_hours !== null && $l->avg_turnaround_hours > 0) ? $l->avg_turnaround_hours . ' Jam' : '-';
            $gradeText = $l->grade_label;
            $scoreText = ($l->score !== null) ? $l->score . ' (maks ' . $l->max_earned . ')' : '-';

            $html .= "<tr>";
            $html .= "<td>{$no}</td>";
            $html .= "<td><strong>" . htmlspecialchars($l->full_name) . "</strong></td>";
            $html .= "<td>@" . htmlspecialchars($l->username) . "</td>";
            $html .= "<td>" . $l->total_campaigns . " Campaign</td>";
            $html .= "<td>" . $l->view_attainment_pct . "% (" . number_format($l->actual_views, 0, ',', '.') . "/" . number_format($l->total_target_views, 0, ',', '.') . ")</td>";
            $html .= "<td>Rp " . number_format($l->actual_cpv, 0, ',', '.') . " (" . $l->budget_efficiency_pct . "% spent)</td>";
            $html .= "<td>Rp " . number_format($l->actual_cpe, 0, ',', '.') . " (" . number_format($l->actual_engagements, 0, ',', '.') . " eng)</td>";
            $html .= "<td>" . $l->deal_conversion_pct . "% (" . $l->deal_success . "/" . $l->total_negotiated . ")</td>";
            $html .= "<td>" . $tatText . "</td>";
            $html .= "<td><strong>" . htmlspecialchars($gradeText) . "</strong><br><span>" . htmlspecialchars($scoreText) . "</span></td>";
            $html .= "</tr>";
            $no++;
        }

        if (empty($leaderKpis)) {
            $html .= "<tr><td colspan='10' style='text-align:center;'>Tidak ada data leader.</td></tr>";
        }
        $html .= "</table>";
        $html .= "</body></html>";

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($html);
    }

    /**
     * Modern CSV Export (CI4 standard)
     */
    public function exportExcel()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $staffKpis = $this->evalModel->getStaffOperationalKpis($campaignId ? (int)$campaignId : null);

        $filename = 'Scorecard_KPI_Internal_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Staff ID',
            'Nama Staff',
            'Username',
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
        ]);

        foreach ($staffKpis as $row) {
            fputcsv($output, [
                $row['staff_id'],
                $row['staff_name'],
                $row['username'],
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
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($csv);
    }

    /**
     * AJAX endpoint to fetch staff scorecard
     */
    public function getStaffKpisJson()
    {
        $campaignId = $this->request->getGet('campaign_id') ?: $this->request->getPost('campaign_id');
        $staffKpis = $this->evalModel->getStaffOperationalKpis($campaignId ? (int)$campaignId : null);
        $teamSummary = $this->evalModel->getOperationalTeamSummary($staffKpis);

        return $this->response->setJSON([
            'success'     => true,
            'staffKpis'   => $staffKpis,
            'teamSummary' => $teamSummary,
        ]);
    }

    /**
     * AJAX endpoint to fetch leader scorecard
     */
    public function getLeaderKpisJson()
    {
        $campaignId = $this->request->getGet('campaign_id') ?: $this->request->getPost('campaign_id');
        $leaderKpis = $this->evalModel->getLeaderKpis($campaignId ? (int)$campaignId : null);
        $leaderSummary = $this->evalModel->getLeaderTeamSummary($leaderKpis);

        return $this->response->setJSON([
            'success'       => true,
            'leaderKpis'    => $leaderKpis,
            'leaderSummary' => $leaderSummary,
        ]);
    }
}
