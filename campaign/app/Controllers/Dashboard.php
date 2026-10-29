<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Get key summary metrics for Campaign Management
        $totalCampaigns = $db->table('campaigns')->countAllResults();
        $activeCampaigns = $db->table('campaigns')->where('status', 'ACTIVE')->countAllResults();
        $totalBriefs = $db->table('briefs')->countAllResults();
        $pendingDrafts = $db->table('drafts')->where('status', 'PENDING')->countAllResults();
        $totalInvoices = $db->table('invoices')->countAllResults();

        $recentCampaigns = $db->table('campaigns')
            ->select('id, title, budget_total, start_date, end_date, status')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $data = [
            'title'           => 'Dashboard Campaign Management',
            'controller'      => 'Campaign',
            'totalCampaigns'  => $totalCampaigns,
            'activeCampaigns' => $activeCampaigns,
            'totalBriefs'     => $totalBriefs,
            'pendingDrafts'   => $pendingDrafts,
            'totalInvoices'   => $totalInvoices,
            'recentCampaigns' => $recentCampaigns,
        ];

        return view('dashboard/index', $data);
    }
}
