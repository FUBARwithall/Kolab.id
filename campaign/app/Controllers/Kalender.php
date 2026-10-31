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
     * Halaman Utama Kalender Visualisasi Timeline Campaign & Jadwal Posting VT KOL
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $allCampaigns = $this->campaignModel->select('id, title, title as name')->orderBy('id', 'DESC')->findAll();
        $allKols = $db->table('kols')->select('id, name')->orderBy('name', 'ASC')->get()->getResultArray();
        
        // Fetch platforms (fallback to kol_platforms or platforms)
        $platforms = [];
        if ($db->tableExists('platforms')) {
            $platforms = $db->table('platforms')->select('id, name')->orderBy('name', 'ASC')->get()->getResultArray();
        }

        $data = [
            'title'        => 'Kalender Jadwal — Visualisasi Timeline Campaign & Jadwal Posting VT KOL',
            'controller'   => 'Kalender',
            'allCampaigns' => $allCampaigns,
            'campaigns'    => $allCampaigns,
            'allKols'      => $allKols,
            'kols'         => $allKols,
            'platforms'    => $platforms,
        ];

        return view('kalender/index', $data);
    }

    /**
     * CI3 parity alias for getEvents
     */
    public function get_events()
    {
        return $this->getEvents();
    }

    /**
     * JSON Endpoint untuk Kalender Timeline Events (Full Parity dengan CI3 modules/kalender)
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
                campaign_kols.deal_scope as sow_file,
                campaign_kols.campaign_id as real_campaign_id,
                campaigns.id as campaign_id_col,
                campaigns.title as campaign_name,
                campaigns.start_date as campaign_start_date,
                campaigns.end_date as campaign_end_date,
                kols.id as real_kol_id,
                kols.name as kol_name,
                platforms.name as platform_name,
                variants.name as variant_name,
                postings.live_url as posting_live_url,
                briefs.content as brief_content,
                briefs.status as brief_status,
                briefs.title as brief_title
            ')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
            ->join('variants', 'variants.id = tasks.variant_id', 'left')
            ->join('postings', 'postings.task_id = tasks.id', 'left')
            ->join('briefs', 'briefs.task_id = tasks.id', 'left');

        if ($start) {
            // Trim timezone if FullCalendar ISO string
            $startDate = substr($start, 0, 10);
            $builder->groupStart()
                ->where('tasks.estimated_posting_date >=', $startDate)
                ->orGroupStart()
                    ->where('tasks.estimated_posting_date IS NULL')
                    ->where('tasks.deadline >=', $startDate)
                ->groupEnd()
            ->groupEnd();
        }

        if ($end) {
            $endDate = substr($end, 0, 10);
            $builder->groupStart()
                ->where('tasks.estimated_posting_date <=', $endDate)
                ->orGroupStart()
                    ->where('tasks.estimated_posting_date IS NULL')
                    ->where('tasks.deadline <=', $endDate)
                ->groupEnd()
            ->groupEnd();
        }

        if ($campaignId) {
            $builder->where('campaign_kols.campaign_id', (int)$campaignId);
        }

        if ($kolId) {
            $builder->where('campaign_kols.kol_id', (int)$kolId);
        }

        if ($platformId) {
            $builder->where('tasks.platform_id', (int)$platformId);
        }

        if ($status) {
            if ($status === 'POSTED_LIVE') {
                $builder->whereIn('tasks.status', ['POSTED', 'DONE', 'SUCCESS', 'COMPLETED']);
            } elseif ($status === 'NOT_POSTED') {
                $builder->whereNotIn('tasks.status', ['POSTED', 'DONE', 'SUCCESS', 'COMPLETED', 'CANCELLED', 'REJECTED']);
            } else {
                $builder->where('tasks.status', $status);
            }
        }

        // Exclude cancelled and rejected tasks from the calendar globally unless specifically filtered
        if (empty($status) || ($status !== 'CANCELLED' && $status !== 'REJECTED')) {
            $builder->whereNotIn('tasks.status', ['CANCELLED', 'REJECTED']);
        }

        $tasks = $builder->get()->getResultArray();
        $events = [];
        $todayTs = strtotime(date('Y-m-d'));

        foreach ($tasks as $task) {
            $statusUpper = strtoupper(trim((string)($task['status'] ?? 'DRAFT')));

            // Status color mapping
            if (in_array($statusUpper, ['POSTED', 'DONE', 'SUCCESS', 'COMPLETED'])) {
                $color = '#16a34a'; // Green (Sudah Post Video Live)
            } elseif (in_array($statusUpper, ['CANCELLED', 'REJECTED'])) {
                $color = '#dc2626'; // Bright Red (Dibatalkan)
            } elseif (in_array($statusUpper, ['DRAFT_SUBMITTED', 'DRAFT_REVISION', 'IN_REVIEW_DRAFT', 'REVISION_DRAFT'])) {
                $color = '#f59e0b'; // Amber / Orange (Proses Draft)
            } else {
                $color = '#0284c7'; // Blue (Belum Upload Video Live)
            }

            // Target posting date: prioritize estimated_posting_date, fallback to deadline
            $postingDate = !empty($task['estimated_posting_date']) ? $task['estimated_posting_date'] : (!empty($task['deadline']) ? $task['deadline'] : null);
            if (!$postingDate) {
                continue;
            }

            // Calculate deadline urgency
            $taskDateTs = strtotime($postingDate);
            $diffDays   = ($taskDateTs && $todayTs) ? (int) floor(($taskDateTs - $todayTs) / 86400) : 0;

            $isCompleted = in_array($statusUpper, ['POSTED', 'DONE', 'SUCCESS', 'COMPLETED']);
            $isCancelled = in_array($statusUpper, ['CANCELLED', 'REJECTED']);

            if ($isCompleted) {
                $deadlineType       = 'completed';
                $deadlineLabel      = 'Posted';
                $deadlineBadgeBg    = '#dcfce7';
                $deadlineBadgeColor = '#15803d';
            } elseif ($isCancelled) {
                $deadlineType       = 'cancelled';
                $deadlineLabel      = 'Cancelled';
                $deadlineBadgeBg    = '#fee2e2';
                $deadlineBadgeColor = '#b91c1c';
            } elseif ($diffDays < 0) {
                $deadlineType       = 'overdue';
                $deadlineLabel      = 'Lewat ' . abs($diffDays) . 'h';
                $deadlineBadgeBg    = '#fee2e2';
                $deadlineBadgeColor = '#b91c1c';
            } elseif ($diffDays == 0) {
                $deadlineType       = 'today';
                $deadlineLabel      = 'Hari Ini';
                $deadlineBadgeBg    = '#fef3c7';
                $deadlineBadgeColor = '#b45309';
            } elseif ($diffDays <= 3) {
                $deadlineType       = 'urgent';
                $deadlineLabel      = 'H-' . $diffDays;
                $deadlineBadgeBg    = '#fef3c7';
                $deadlineBadgeColor = '#b45309';
            } else {
                $deadlineType       = 'normal';
                $deadlineLabel      = 'H-' . $diffDays;
                $deadlineBadgeBg    = '#e0f2fe';
                $deadlineBadgeColor = '#0369a1';
            }

            $campaignStr = $task['campaign_name'] ?: 'Campaign';
            $taskKolStr  = $task['title'] ?: ($task['kol_name'] ?: 'KOL');
            $title       = '[' . $campaignStr . '] - ' . $taskKolStr;
            $liveUrl     = !empty($task['live_url']) ? $task['live_url'] : (!empty($task['posting_live_url']) ? $task['posting_live_url'] : '');

            // Brief/SOW: prioritize campaign_kols.deal_scope, then tasks.brief_file
            $sowFile       = !empty($task['sow_file']) ? $task['sow_file'] : '';
            $briefTaskFile = !empty($task['brief_file']) ? $task['brief_file'] : '';
            $briefFileUrl  = '';
            $briefSource   = '';

            if (!empty($sowFile)) {
                $briefFileUrl = base_url('uploads/campaign_kols/' . $sowFile);
                $briefSource  = 'SOW dari Dealing Kol';
            } elseif (!empty($briefTaskFile)) {
                $briefFileUrl = base_url('uploads/tasks/' . $briefTaskFile);
                $briefSource  = 'Brief dari Task';
            }

            // Draft: get latest draft from drafts table, fallback to tasks.draft_url
            $draftUrl = $task['draft_url'] ?: '';
            if (empty($draftUrl)) {
                $latestDraft = $db->table('drafts')
                    ->select('draft_url')
                    ->where('task_id', $task['id'])
                    ->orderBy('version', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                if ($latestDraft && !empty($latestDraft['draft_url'])) {
                    $draftUrl = $latestDraft['draft_url'];
                }
            }

            $taskDetailUrl = base_url('tasks/view/' . $task['id']);

            $eventProps = [
                'task_id'              => $task['id'],
                'taskId'               => $task['id'],
                'task_number'          => $task['task_number'],
                'taskNumber'           => $task['task_number'],
                'raw_title'            => $task['title'] ?: ('VT #' . $task['task_number']),
                'kol_name'             => $task['kol_name'] ?: '-',
                'kolName'              => $task['kol_name'] ?: '-',
                'kol_id'               => $task['real_kol_id'],
                'campaign_name'        => $task['campaign_name'] ?: '-',
                'campaignTitle'        => $task['campaign_name'] ?: '-',
                'campaign_id'          => $task['real_campaign_id'],
                'campaign_start_date'  => $task['campaign_start_date'],
                'campaign_end_date'    => $task['campaign_end_date'],
                'platform_name'        => $task['platform_name'] ?: 'Platform',
                'variant_name'         => $task['variant_name'] ?: '-',
                'content_type'         => $task['content_type'] ?? '-',
                'status_raw'           => $task['status'],
                'status'               => $task['status'],
                'rate_formatted'       => ($task['currency_code'] ?? 'IDR') . ' ' . number_format((float)($task['budget'] ?? 0), 0, ',', '.'),
                'notes'                => $task['notes'] ?? '',
                'brief_file'           => $sowFile ?: $briefTaskFile,
                'brief_file_url'       => $briefFileUrl,
                'briefFileUrl'         => $briefFileUrl,
                'brief_source'         => $briefSource,
                'brief_note'           => $task['brief_note'] ?: ($task['brief_title'] ?: ''),
                'draft_url'            => $draftUrl,
                'draftUrl'             => $draftUrl,
                'draft_note'           => $task['draft_note'] ?? '',
                'live_url'             => $liveUrl,
                'liveUrl'              => $liveUrl,
                'deadline_type'        => $deadlineType,
                'deadline_label'       => $deadlineLabel,
                'deadline_badge_bg'    => $deadlineBadgeBg,
                'deadline_badge_color' => $deadlineBadgeColor,
                'diff_days'            => $diffDays,
                'url'                  => $taskDetailUrl,
            ];

            $events[] = array_merge([
                'id'              => $task['id'],
                'title'           => $title,
                'start'           => $postingDate,
                'allDay'          => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'url'             => $taskDetailUrl,
                'description'     => 'Status: ' . $task['status'],
                'extendedProps'   => $eventProps,
            ], $eventProps);
        }

        // Multi-day Campaign Period Banner Events
        $campaignsBg = [];
        $campaignPalettes = [
            ['bg' => '#eef2ff', 'border' => '#6366f1', 'text' => '#4338ca'], // Indigo
            ['bg' => '#f0fdf4', 'border' => '#16a34a', 'text' => '#166534'], // Emerald
            ['bg' => '#f5f3ff', 'border' => '#8b5cf6', 'text' => '#5b21b6'], // Violet
            ['bg' => '#fff7ed', 'border' => '#f97316', 'text' => '#9a3412'], // Orange
            ['bg' => '#fdf2f8', 'border' => '#ec4899', 'text' => '#9d174d'], // Pink
            ['bg' => '#f0fdfa', 'border' => '#14b8a6', 'text' => '#115e59'], // Teal
            ['bg' => '#f8fafc', 'border' => '#475569', 'text' => '#1e293b']  // Slate
        ];

        foreach ($tasks as $task) {
            $cId = $task['real_campaign_id'];
            if (!empty($cId) && !isset($campaignsBg[$cId])) {
                if (!empty($task['campaign_start_date']) && !empty($task['campaign_end_date'])) {
                    $campaignsBg[$cId] = true;
                    $colorIdx = (int)$cId % count($campaignPalettes);
                    $palette = $campaignPalettes[$colorIdx];

                    $campBannerProps = [
                        'is_campaign_banner' => true,
                        'isCampaignBanner'   => true,
                        'campaign_id'        => $cId,
                        'campaignId'         => $cId,
                        'campaign_name'      => $task['campaign_name'],
                        'campaignTitle'      => $task['campaign_name'],
                        'camp_bg'            => $palette['bg'],
                        'camp_border'        => $palette['border'],
                        'camp_text'          => $palette['text'],
                    ];

                    $events[] = array_merge([
                        'id'                 => 'camp_' . $cId,
                        'title'              => 'Periode: ' . $task['campaign_name'],
                        'start'              => $task['campaign_start_date'],
                        'end'                => date('Y-m-d', strtotime($task['campaign_end_date'] . ' +1 day')),
                        'allDay'             => true,
                        'backgroundColor'    => $palette['bg'],
                        'borderColor'        => $palette['border'],
                        'textColor'          => $palette['text'],
                        'extendedProps'      => $campBannerProps,
                    ], $campBannerProps);
                }
            }
        }

        return $this->response->setJSON($events);
    }
}
