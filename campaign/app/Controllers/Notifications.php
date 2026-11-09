<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Dedicated Notification Center Page
     */
    public function index()
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $userId = (int) ($session->get('user_id') ?? 0);
        $roles = (array) ($session->get('roles') ?? []);
        $isKol = in_array('kol', $roles, true);

        $kolId = $this->getMyKolId($userId);
        $lastReadTime = $this->getLastReadTime($userId);

        $notifications = $this->fetchNotifications($userId, $kolId, $roles, $lastReadTime, 200);

        $totalCount      = count($notifications);
        $unreadCount     = 0;
        $actionableCount = 0;
        $categoryCounts  = [
            'contract'    => 0,
            'vt'          => 0,
            'deadline'    => 0,
            'application' => 0,
            'shipment'    => 0,
            'invoice'     => 0,
        ];

        foreach ($notifications as $n) {
            if (!empty($n['is_unread'])) {
                $unreadCount++;
            }
            if (!empty($n['is_actionable'])) {
                $actionableCount++;
            }
            $cat = $n['category'] ?? 'other';
            if (isset($categoryCounts[$cat])) {
                $categoryCounts[$cat]++;
            }
        }

        $data = [
            'title'           => 'Pusat Pemberitahuan',
            'notifications'   => $notifications,
            'totalCount'      => $totalCount,
            'unreadCount'     => $unreadCount,
            'actionableCount' => $actionableCount,
            'categoryCounts'  => $categoryCounts,
            'lastReadTime'    => $lastReadTime,
            'isKol'           => $isKol,
        ];

        return view('notifications/index', $data);
    }

    /**
     * AJAX endpoint for Navbar Notification Dropdown
     */
    public function getInboxAjax(): ResponseInterface
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = (int) ($session->get('user_id') ?? 0);
        $roles = (array) ($session->get('roles') ?? []);
        $kolId = $this->getMyKolId($userId);
        $lastReadTime = $this->getLastReadTime($userId);

        $notifications = $this->fetchNotifications($userId, $kolId, $roles, $lastReadTime, 25);

        $unreadCount = 0;
        foreach ($notifications as $n) {
            if (!empty($n['is_unread'])) {
                $unreadCount++;
            }
        }

        return $this->response->setJSON([
            'success'       => true,
            'unread_count'  => $unreadCount,
            'total_count'   => count($notifications),
            'last_read_at'  => $lastReadTime,
            'notifications' => $notifications,
        ]);
    }

    /**
     * AJAX endpoint to mark all notifications as read
     */
    public function markAllReadAjax(): ResponseInterface
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $userId = (int) ($session->get('user_id') ?? 0);
        $now = date('Y-m-d H:i:s');

        // Persist to users table and session
        if ($userId > 0) {
            $this->db->table('users')->where('id', $userId)->update(['last_notif_read_at' => $now]);
        }
        $session->set('last_notif_read_at', $now);

        return $this->response->setJSON([
            'success'      => true,
            'last_read_at' => $now,
            'message'      => 'Semua pemberitahuan berhasil ditandai sebagai dibaca.',
        ]);
    }

    /**
     * Resolve KOL ID for user
     */
    protected function getMyKolId(int $userId): ?int
    {
        if ($userId > 0) {
            $row = $this->db->table('kols')->select('id')->where('user_id', $userId)->get()->getRowArray();
            if ($row) {
                return (int) $row['id'];
            }
        }
        return null;
    }

    /**
     * Get user's last read time
     */
    protected function getLastReadTime(int $userId): ?string
    {
        $session = session();
        $sessTime = $session->get('last_notif_read_at');
        if ($sessTime) {
            return $sessTime;
        }

        if ($userId > 0) {
            $user = $this->db->table('users')->select('last_notif_read_at')->where('id', $userId)->get()->getRowArray();
            if ($user && !empty($user['last_notif_read_at'])) {
                $session->set('last_notif_read_at', $user['last_notif_read_at']);
                return $user['last_notif_read_at'];
            }
        }

        return null;
    }

    /**
     * Fetch and standardize notifications
     */
    protected function fetchNotifications(int $userId, ?int $kolId, array $roles, ?string $lastReadTime, int $limit = 50): array
    {
        $notifications = [];
        $isKol = in_array('kol', $roles, true);
        $isAdmin = in_array('admin', $roles, true);
        $isStaff = in_array('staff', $roles, true) || in_array('leader', $roles, true);
        $today = date('Y-m-d');

        // -------------------------------------------------------------
        // A. CREATOR / TALENT NOTIFICATIONS (Based on kol_id)
        // -------------------------------------------------------------
        if ($kolId && $isKol) {
            // 1. Applications (Diterima / Ditolak)
            $apps = $this->db->table('campaign_applications ca')
                ->select('ca.id, ca.campaign_id, ca.status, ca.pitch_notes, ca.reviewed_at, ca.updated_at, c.title as campaign_name')
                ->join('campaigns c', 'c.id = ca.campaign_id', 'left')
                ->where('ca.kol_id', $kolId)
                ->orderBy('ca.updated_at', 'DESC')
                ->get($limit)
                ->getResultArray();

            foreach ($apps as $app) {
                $cName = $app['campaign_name'] ?: 'Campaign #' . $app['campaign_id'];
                $time  = $app['reviewed_at'] ?: $app['updated_at'];
                if (!$time) {
                    $time = date('Y-m-d H:i:s');
                }

                if ($app['status'] === 'ACCEPTED') {
                    $notifications[] = [
                        'id'            => 'app_' . $app['id'],
                        'category'      => 'application',
                        'type'          => 'success',
                        'icon'          => 'fas fa-check-circle text-success',
                        'badge'         => 'Apply Diterima',
                        'badge_class'   => 'bg-success',
                        'title'         => 'Pendaftaran Disetujui',
                        'message'       => 'Pendaftaran Anda untuk campaign <b>' . esc($cName) . '</b> telah disetujui oleh brand!',
                        'url'           => '/datahub/my-applications',
                        'action_text'   => 'Lihat Aplikasi',
                        'timestamp'     => $time,
                        'time_ago'      => $this->timeElapsedString($time),
                        'is_actionable' => false,
                        'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                    ];
                } elseif ($app['status'] === 'REJECTED') {
                    $notifications[] = [
                        'id'            => 'app_' . $app['id'],
                        'category'      => 'application',
                        'type'          => 'danger',
                        'icon'          => 'fas fa-times-circle text-danger',
                        'badge'         => 'Apply Belum Sesuai',
                        'badge_class'   => 'bg-danger',
                        'title'         => 'Pendaftaran Belum Sesuai',
                        'message'       => 'Pendaftaran Anda untuk campaign <b>' . esc($cName) . '</b> belum berhasil terpilih.',
                        'url'           => '/datahub/my-applications',
                        'action_text'   => 'Lihat Aplikasi',
                        'timestamp'     => $time,
                        'time_ago'      => $this->timeElapsedString($time),
                        'is_actionable' => false,
                        'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                    ];
                }
            }

            // 2. Contracts (Tanda Tangan & Kontrak Aktif)
            $contracts = $this->db->table('campaign_kol_contracts ckc')
                ->select('ckc.id, ckc.contract_no, ckc.status, ckc.created_at, ckc.updated_at, c.title as campaign_name')
                ->join('campaign_kols ck', 'ck.id = ckc.campaign_kol_id', 'inner')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->where('ck.kol_id', $kolId)
                ->orderBy('ckc.updated_at', 'DESC')
                ->get($limit)
                ->getResultArray();

            foreach ($contracts as $ctr) {
                $cName = $ctr['campaign_name'] ?: 'Campaign';
                $ctrNo = $ctr['contract_no'] ?: 'Kontrak #' . $ctr['id'];
                $time  = $ctr['updated_at'] ?: $ctr['created_at'];
                if (!$time) {
                    $time = date('Y-m-d H:i:s');
                }

                if (in_array($ctr['status'], ['WAITING_KOL_SIGNATURE', 'WAITING_KOL_SIGN', 'PENDING', 'DRAFT'], true)) {
                    $notifications[] = [
                        'id'            => 'contract_' . $ctr['id'],
                        'category'      => 'contract',
                        'type'          => 'warning',
                        'icon'          => 'fas fa-file-signature text-warning',
                        'badge'         => 'Butuh Tanda Tangan',
                        'badge_class'   => 'bg-warning text-dark',
                        'title'         => 'Kontrak Siap Ditandatangani',
                        'message'       => 'Kontrak <b>' . esc($ctrNo) . '</b> untuk campaign <b>' . esc($cName) . '</b> siap ditinjau dan ditandatangani.',
                        'url'           => '/datahub/my-contracts',
                        'action_text'   => 'Tandatangani Kontrak',
                        'timestamp'     => $time,
                        'time_ago'      => $this->timeElapsedString($time),
                        'is_actionable' => true,
                        'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                    ];
                }
            }
        }

        // -------------------------------------------------------------
        // B. BRAND / INTERNAL TEAM NOTIFICATIONS (Admin, Leader, Staff)
        // -------------------------------------------------------------
        if ($isAdmin || $isStaff) {
            // New applicants needing review
            $newApplicants = $this->db->table('campaign_applications ca')
                ->select('ca.id, ca.campaign_id, ca.kol_id, ca.status, ca.created_at, c.title as campaign_name, k.name as kol_name')
                ->join('campaigns c', 'c.id = ca.campaign_id', 'left')
                ->join('kols k', 'k.id = ca.kol_id', 'left')
                ->where('ca.status', 'APPLIED')
                ->orderBy('ca.created_at', 'DESC')
                ->get($limit)
                ->getResultArray();

            foreach ($newApplicants as $app) {
                $time = $app['created_at'] ?: date('Y-m-d H:i:s');
                $notifications[] = [
                    'id'            => 'admin_app_' . $app['id'],
                    'category'      => 'application',
                    'type'          => 'primary',
                    'icon'          => 'fas fa-user-plus text-primary',
                    'badge'         => 'Pelamar Baru',
                    'badge_class'   => 'bg-primary',
                    'title'         => 'Pendaftaran Kreator Baru',
                    'message'       => 'Kreator <b>' . esc($app['kol_name'] ?? 'KOL') . '</b> mendaftar pada campaign <b>' . esc($app['campaign_name'] ?? 'Campaign') . '</b>.',
                    'url'           => base_url('campaigns/view/' . $app['campaign_id']),
                    'action_text'   => 'Review Pelamar',
                    'timestamp'     => $time,
                    'time_ago'      => $this->timeElapsedString($time),
                    'is_actionable' => true,
                    'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                ];
            }

            // Drafts submitted needing review
            $submittedDrafts = $this->db->table('tasks t')
                ->select('t.id, t.title, t.status, t.updated_at, c.id as campaign_id, c.title as campaign_name, k.name as kol_name')
                ->join('campaign_kols ck', 'ck.id = t.campaign_kol_id', 'inner')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->join('kols k', 'k.id = ck.kol_id', 'left')
                ->whereIn('t.status', ['DRAFT_SUBMITTED', 'IN_REVIEW_DRAFT', 'PENDING_REVIEW'])
                ->orderBy('t.updated_at', 'DESC')
                ->get($limit)
                ->getResultArray();

            foreach ($submittedDrafts as $drf) {
                $time = $drf['updated_at'] ?: date('Y-m-d H:i:s');
                $notifications[] = [
                    'id'            => 'admin_draft_' . $drf['id'],
                    'category'      => 'vt',
                    'type'          => 'info',
                    'icon'          => 'fas fa-video text-info',
                    'badge'         => 'Review Draft',
                    'badge_class'   => 'bg-info',
                    'title'         => 'Draft Video Perlu Direview',
                    'message'       => 'Kreator <b>' . esc($drf['kol_name'] ?? 'KOL') . '</b> telah mengirimkan draft video untuk <b>' . esc($drf['title']) . '</b>.',
                    'url'           => base_url('tasks'),
                    'action_text'   => 'Review Video',
                    'timestamp'     => $time,
                    'time_ago'      => $this->timeElapsedString($time),
                    'is_actionable' => true,
                    'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                ];
            }

            // Invoices submitted needing payment/approval
            $submittedInvoices = $this->db->table('invoices i')
                ->select('i.id, i.invoice_number, i.grand_total, i.status, i.created_at, i.updated_at, c.title as campaign_name, k.name as kol_name')
                ->join('campaign_kols ck', 'ck.id = i.campaign_kol_id', 'inner')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->join('kols k', 'k.id = ck.kol_id', 'left')
                ->where('i.status', 'SUBMITTED')
                ->orderBy('i.updated_at', 'DESC')
                ->get($limit)
                ->getResultArray();

            foreach ($submittedInvoices as $inv) {
                $time = $inv['updated_at'] ?: $inv['created_at'];
                $notifications[] = [
                    'id'            => 'admin_inv_' . $inv['id'],
                    'category'      => 'invoice',
                    'type'          => 'warning',
                    'icon'          => 'fas fa-file-invoice-dollar text-warning',
                    'badge'         => 'Invoice Masuk',
                    'badge_class'   => 'bg-warning text-dark',
                    'title'         => 'Tagihan Invoice Baru Masuk',
                    'message'       => 'Kreator <b>' . esc($inv['kol_name'] ?? 'KOL') . '</b> mengajukan tagihan <b>' . esc($inv['invoice_number'] ?: ('#' . $inv['id'])) . '</b> sebesar Rp ' . number_format((float) ($inv['grand_total'] ?? 0), 0, ',', '.') . '.',
                    'url'           => base_url('invoices'),
                    'action_text'   => 'Buka Invoice',
                    'timestamp'     => $time,
                    'time_ago'      => $this->timeElapsedString($time),
                    'is_actionable' => true,
                    'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                ];
            }

            // Contracts signed by KOL
            $signedContracts = $this->db->table('campaign_kol_contracts ckc')
                ->select('ckc.id, ckc.contract_no, ckc.status, ckc.updated_at, c.title as campaign_name, k.name as kol_name')
                ->join('campaign_kols ck', 'ck.id = ckc.campaign_kol_id', 'inner')
                ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
                ->join('kols k', 'k.id = ck.kol_id', 'left')
                ->whereIn('ckc.status', ['SIGNED', 'KOL_REVIEWED'])
                ->orderBy('ckc.updated_at', 'DESC')
                ->get($limit)
                ->getResultArray();

            foreach ($signedContracts as $ctr) {
                $time = $ctr['updated_at'] ?: date('Y-m-d H:i:s');
                $notifications[] = [
                    'id'            => 'admin_ctr_' . $ctr['id'],
                    'category'      => 'contract',
                    'type'          => 'success',
                    'icon'          => 'fas fa-file-signature text-success',
                    'badge'         => 'Kontrak Ditandatangani',
                    'badge_class'   => 'bg-success',
                    'title'         => 'KOL Telah Menandatangani Kontrak',
                    'message'       => 'Kreator <b>' . esc($ctr['kol_name'] ?? 'KOL') . '</b> telah menandatangani kontrak <b>' . esc($ctr['contract_no'] ?: ('#' . $ctr['id'])) . '</b> (' . esc($ctr['campaign_name'] ?? 'Campaign') . ').',
                    'url'           => base_url('contracts'),
                    'action_text'   => 'Buka Kontrak',
                    'timestamp'     => $time,
                    'time_ago'      => $this->timeElapsedString($time),
                    'is_actionable' => false,
                    'is_unread'     => empty($lastReadTime) || (strtotime($time) > strtotime($lastReadTime)),
                ];
            }
        }

        // Sort all notifications by timestamp DESC
        usort($notifications, static function ($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });

        // Limit results
        return array_slice($notifications, 0, $limit);
    }

    /**
     * Relative time string helper
     */
    protected function timeElapsedString(string $datetime): string
    {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return 'Baru saja';
        }

        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Baru saja';
        }
        if ($diff < 3600) {
            $mins = (int) ($diff / 60);
            return $mins . ' menit lalu';
        }
        if ($diff < 86400) {
            $hours = (int) ($diff / 3600);
            return $hours . ' jam lalu';
        }
        if ($diff < 2592000) {
            $days = (int) ($diff / 86400);
            return $days . ' hari lalu';
        }
        if ($diff < 31536000) {
            $months = (int) ($diff / 2592000);
            return $months . ' bulan lalu';
        }

        $years = (int) ($diff / 31536000);
        return $years . ' tahun lalu';
    }
}

