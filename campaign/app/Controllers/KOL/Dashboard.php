<?php

namespace App\Controllers\KOL;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $session = session();
        $userId = $session->get('user_id');

        // Look up matching KOL record by user_id
        $kol = $db->table('kols')->where('user_id', $userId)->get()->getRowArray();
        $kolId = $kol['id'] ?? null;

        $myTasksCount = 0;
        $myDraftsCount = 0;
        $myInvoicesCount = 0;

        if ($kolId) {
            $campaignKolRows = $db->table('campaign_kols')->where('kol_id', $kolId)->select('id')->get()->getResultArray();
            $campaignKolIds = array_column($campaignKolRows, 'id');

            if (!empty($campaignKolIds)) {
                $myTasksCount = $db->table('tasks')->whereIn('campaign_kol_id', $campaignKolIds)->countAllResults();
                $taskRows = $db->table('tasks')->whereIn('campaign_kol_id', $campaignKolIds)->select('id')->get()->getResultArray();
                $taskIds = array_column($taskRows, 'id');

                if (!empty($taskIds)) {
                    $myDraftsCount = $db->table('drafts')->whereIn('task_id', $taskIds)->countAllResults();
                }

                $myInvoicesCount = $db->table('invoices')->whereIn('campaign_kol_id', $campaignKolIds)->countAllResults();
            }
        }

        $openCampaigns = $db->table('campaigns')
            ->select('id, title, budget_total, start_date, end_date')
            ->where('status', 'ACTIVE')
            ->orderBy('id', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();

        $data = [
            'title'           => 'Dashboard Portal Creator',
            'controller'      => 'Portal KOL',
            'kol'             => $kol,
            'myTasksCount'    => $myTasksCount,
            'myDraftsCount'   => $myDraftsCount,
            'myInvoicesCount' => $myInvoicesCount,
            'openCampaigns'   => $openCampaigns,
        ];

        return redirect()->to('/datahub/dashboard');
    }
}
