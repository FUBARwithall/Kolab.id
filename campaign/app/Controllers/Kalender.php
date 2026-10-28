<?php

namespace App\Controllers;

use App\Models\CampaignModel;

class Kalender extends BaseController
{
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Kalender Timeline Produksi & Posting
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();
        $allKols = $db->table('kols')->select('id, name')->orderBy('name', 'ASC')->get()->getResultArray();
        $platforms = $db->table('platforms')->where('is_active', 1)->get()->getResultArray();

        $data = [
            'title'        => 'Kalender Produksi & Timeline Penayangan VT',
            'controller'   => 'Kalender',
            'allCampaigns' => $allCampaigns,
            'allKols'      => $allKols,
            'platforms'    => $platforms,
        ];

        return view('kalender/index', $data);
    }

    /**
     * JSON Endpoint untuk FullCalendar v6
     */
    public function getEvents()
    {
        $start       = $this->request->getGet('start');
        $end         = $this->request->getGet('end');
        $campaignId  = $this->request->getGet('campaign_id');
        $kolId       = $this->request->getGet('kol_id');
        $platformId  = $this->request->getGet('platform_id');
        $status      = $this->request->getGet('status');

        $db = \Config\Database::connect();
        $builder = $db->table('tasks')
            ->select('
                tasks.*,
                campaign_kols.campaign_id,
                campaigns.title as campaign_title,
                campaigns.start_date as campaign_start,
                campaigns.end_date as campaign_end,
                kols.name as kol_name,
                platforms.name as platform_name,
                postings.live_url
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('postings', 'postings.task_id = tasks.id', 'left')
            ->join('platforms', 'platforms.id = postings.platform_id', 'left')
            ->where('tasks.status !=', 'CANCELLED');

        if ($campaignId) {
            $builder->where('campaign_kols.campaign_id', (int)$campaignId);
        }
        if ($kolId) {
            $builder->where('campaign_kols.kol_id', (int)$kolId);
        }
        if ($status) {
            $builder->where('tasks.status', $status);
        }

        $tasks = $builder->get()->getResultArray();
        $events = [];

        foreach ($tasks as $t) {
            $date = $t['deadline'];
            if (empty($date)) {
                continue;
            }

            $st = strtoupper(trim((string)$t['status']));
            $color = match ($st) {
                'POSTED', 'COMPLETED' => '#198754', // Green
                'DRAFT_SUBMITTED', 'DRAFT_REVISION' => '#ffc107', // Yellow
                'BRIEF_SUBMITTED', 'BRIEF_APPROVED' => '#0dcaf0', // Cyan
                'SCHEDULED' => '#0d6efd', // Blue
                default => '#6c757d', // Grey
            };

            $title = esc($t['kol_name']) . ' - ' . esc($t['title'] ?: ('VT #' . $t['task_number']));

            $events[] = [
                'id'          => 'task_' . $t['id'],
                'title'       => $title,
                'start'       => $date,
                'allDay'      => true,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor'   => ($st === 'DRAFT_SUBMITTED' || $st === 'DRAFT_REVISION' || $st === 'BRIEF_SUBMITTED') ? '#000' : '#fff',
                'extendedProps' => [
                    'taskId'        => $t['id'],
                    'campaignTitle' => $t['campaign_title'],
                    'kolName'       => $t['kol_name'],
                    'status'        => $t['status'],
                    'liveUrl'       => $t['live_url'] ?? '',
                ],
            ];
        }

        return $this->response->setJSON($events);
    }
}
