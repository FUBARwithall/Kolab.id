<?php

namespace App\Controllers;

use App\Models\WhatsappModel;
use App\Models\CampaignModel;
use App\Libraries\WahaClient;
use CodeIgniter\HTTP\ResponseInterface;

class Whatsapp extends BaseController
{
    protected WhatsappModel $whatsappModel;
    protected WahaClient $wahaClient;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->whatsappModel = new WhatsappModel();
        $this->wahaClient    = new WahaClient();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Main WhatsApp Communication Console (CI3 & CI4 Parity)
     */
    public function index(?int $kolId = null)
    {
        $kolIdParam = $this->request->getGet('kol_id') ? (int)$this->request->getGet('kol_id') : $kolId;
        $search     = $this->request->getGet('q');

        $conversations = $this->whatsappModel->getKolConversations($search);

        // Pick selected KOL
        $selectedKol = null;
        if (!empty($kolIdParam)) {
            foreach ($conversations as $c) {
                if ((int)$c['id'] === $kolIdParam) {
                    $selectedKol = $c;
                    break;
                }
            }
        }

        // Default to first conversation if not explicitly selected
        if (!$selectedKol && !empty($conversations)) {
            $selectedKol = $conversations[0];
            $kolIdParam  = (int)$selectedKol['id'];
        }

        // Context info for selected KOL
        $db = \Config\Database::connect();
        $kolContext       = null;
        $campaignContext  = null;
        $tasksContext     = [];
        $shipmentContext  = null;

        if ($kolIdParam) {
            // Mark incoming messages as read for active chat
            $this->whatsappModel->markAsRead($kolIdParam);

            $activeDeals = $db->table('campaign_kols')
                ->select('campaign_kols.*, campaigns.title as campaign_title, campaigns.start_date, campaigns.end_date, campaigns.budget as campaign_budget')
                ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
                ->where('campaign_kols.kol_id', $kolIdParam)
                ->orderBy('campaign_kols.id', 'DESC')
                ->get()
                ->getResultArray();

            $campaignContext = !empty($activeDeals) ? $activeDeals[0] : null;

            $pendingTasks = $db->table('tasks')
                ->select('tasks.*, campaigns.title as campaign_title, platforms.name as platform_name, briefs.title as brief_title, briefs.content as brief_content, briefs.guidelines as brief_guidelines')
                ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
                ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
                ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
                ->join('briefs', 'briefs.task_id = tasks.id', 'left')
                ->where('campaign_kols.kol_id', $kolIdParam)
                ->whereNotIn('tasks.status', ['COMPLETED', 'CANCELLED', 'DONE'])
                ->orderBy('tasks.id', 'ASC')
                ->get()
                ->getResultArray();

            $tasksContext = $pendingTasks;

            $latestShipment = $db->table('kol_shipments')
                ->select('kol_shipments.*, campaigns.title as campaign_title')
                ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'inner')
                ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
                ->where('campaign_kols.kol_id', $kolIdParam)
                ->orderBy('kol_shipments.id', 'DESC')
                ->get()
                ->getRowArray();

            $shipmentContext = $latestShipment;

            $kolContext = [
                'deals'    => $activeDeals,
                'tasks'    => $pendingTasks,
                'shipment' => $latestShipment,
            ];
        }

        $messages      = $kolIdParam ? $this->whatsappModel->getMessagesByKol($kolIdParam) : [];
        $templates     = $this->_generateTemplates($selectedKol, $campaignContext, $tasksContext, $shipmentContext);
        $sessionStatus = $this->wahaClient->getSessionStatus();
        $allCampaigns  = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();

        $unreadByKol = $this->whatsappModel->getUnreadCountsByKol();
        $totalUnread  = $this->whatsappModel->getTotalUnreadCount();

        $data = [
            'title'            => 'WhatsApp Communication Console',
            'controller'       => 'Whatsapp',
            'conversations'    => $conversations,
            'kols'             => $conversations, // CI3 alias
            'selectedKol'      => $selectedKol,
            'selected_kol'     => (object)($selectedKol ?: []), // CI3 alias
            'kolId'            => $kolIdParam,
            'messages'         => $messages,
            'templates'        => $templates,
            'sessionStatus'    => $sessionStatus,
            'waha_status'      => $sessionStatus['status'] ?? 'CONNECTED',
            'kolContext'       => $kolContext,
            'campaign_context' => (object)($campaignContext ?: []),
            'tasks_context'    => $tasksContext,
            'shipment_context' => (object)($shipmentContext ?: []),
            'allCampaigns'     => $allCampaigns,
            'unread_by_kol'    => $unreadByKol,
            'total_unread'     => $totalUnread,
            'searchQuery'      => $search,
        ];

        return view('whatsapp/index', $data);
    }

    /**
     * Dedicated Chat entry (CI3 Parity)
     */
    public function chat(?int $kolId = null)
    {
        return $this->index($kolId);
    }

    /**
     * AJAX: Send WhatsApp Text Message (CI3 & CI4 Parity)
     */
    public function ajaxSendMessage()
    {
        $kolId          = (int)($this->request->getPost('kol_id') ?: $this->request->getGet('kol_id'));
        $message         = trim((string)$this->request->getPost('message'));
        $campaignKolId  = $this->request->getPost('campaign_kol_id') ? (int)$this->request->getPost('campaign_kol_id') : null;

        if (empty($kolId) || empty($message)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'KOL dan isi pesan wajib diisi.',
            ]);
        }

        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('id', $kolId)->get()->getRowArray();
        if (!$kol || empty($kol['whatsapp'])) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data KOL tidak ditemukan atau belum memiliki nomor WhatsApp.',
            ]);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$kol['whatsapp']);
        $session = session();
        $senderName = $session->get('name') ?: ($session->get('user')['name'] ?? 'Staff Kolab.id');
        $userId = $session->get('user_id') ?: ($session->get('user')['id'] ?? 1);

        // Dispatch via WAHA Client
        $wahaRes = $this->wahaClient->sendMessage($cleanPhone, $message);
        $wahaMsgId = $wahaRes['waha_message_id'] ?? ('msg_' . time() . '_' . mt_rand(100, 999));
        $now = date('Y-m-d H:i:s');

        // Record in Database
        $msgId = $this->whatsappModel->recordMessage([
            'waha_message_id' => $wahaMsgId,
            'kol_id'          => $kolId,
            'campaign_kol_id' => $campaignKolId,
            'recipient_phone' => $cleanPhone,
            'direction'       => 'OUTBOUND',
            'sender_name'     => $senderName,
            'message_text'    => $message,
            'status'          => $wahaRes['success'] ? 'SENT' : 'PENDING',
            'error_message'   => $wahaRes['error'] ?? null,
            'sent_at'         => $now,
            'created_at'      => $now,
        ]);

        $formattedMsg = [
            'id'              => $msgId,
            'waha_message_id' => $wahaMsgId,
            'direction'       => 'OUTBOUND',
            'sender_name'     => $senderName,
            'text'            => $message,
            'time'            => date('H:i', strtotime($now)),
            'date'            => date('d M Y', strtotime($now)),
            'status'          => 'SENT',
            'created_at'      => $now,
        ];

        return $this->response->setJSON([
            'success'      => true,
            'message_id'   => $msgId,
            'message_data' => $formattedMsg,
            'sent_at'      => date('H:i'),
            'sender_name'  => $senderName,
            'waha_result'  => $wahaRes,
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_send_message
     */
    public function ajax_send_message()
    {
        return $this->ajaxSendMessage();
    }

    /**
     * AJAX: Send Media / File to KOL (CI3 & CI4 Parity)
     */
    public function ajaxSendMedia()
    {
        $kolId          = (int)($this->request->getPost('kol_id') ?: $this->request->getGet('kol_id'));
        $caption        = trim((string)$this->request->getPost('caption'));
        $campaignKolId  = $this->request->getPost('campaign_kol_id') ? (int)$this->request->getPost('campaign_kol_id') : null;
        $mediaUrlParam  = trim((string)$this->request->getPost('media_url'));

        if (empty($kolId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'KOL tujuan tidak boleh kosong.']);
        }

        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('id', $kolId)->get()->getRowArray();
        if (!$kol || empty($kol['whatsapp'])) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data KOL tidak ditemukan atau tidak memiliki nomor WhatsApp.']);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$kol['whatsapp']);
        $file = $this->request->getFile('media_file') ?: $this->request->getFile('file');

        $finalUrl = $mediaUrlParam;
        $mime = 'application/octet-stream';

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/whatsapp/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $finalUrl = base_url('uploads/whatsapp/' . $newName);
            $mime = $file->getClientMimeType();
        }

        if (empty($finalUrl)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih file media atau masukkan URL media yang valid.']);
        }

        $session = session();
        $senderName = $session->get('name') ?: ($session->get('user')['name'] ?? 'Staff Kolab.id');

        $wahaRes = $this->wahaClient->sendMedia($cleanPhone, $finalUrl, $caption);
        $wahaMsgId = $wahaRes['waha_message_id'] ?? ('msg_media_' . time() . '_' . mt_rand(100, 999));
        $now = date('Y-m-d H:i:s');

        $msgId = $this->whatsappModel->recordMessage([
            'waha_message_id' => $wahaMsgId,
            'kol_id'          => $kolId,
            'campaign_kol_id' => $campaignKolId,
            'recipient_phone' => $cleanPhone,
            'direction'       => 'OUTBOUND',
            'sender_name'     => $senderName,
            'message_text'    => $caption ?: '[Lampiran Media]',
            'media_url'       => $finalUrl,
            'media_mime'      => $mime,
            'status'          => $wahaRes['success'] ? 'SENT' : 'PENDING',
            'error_message'   => $wahaRes['error'] ?? null,
            'sent_at'         => $now,
            'created_at'      => $now,
        ]);

        $formattedMsg = [
            'id'              => $msgId,
            'waha_message_id' => $wahaMsgId,
            'direction'       => 'OUTBOUND',
            'sender_name'     => $senderName,
            'text'            => $caption ?: '[Lampiran Media]',
            'media_url'       => $finalUrl,
            'media_mime'      => $mime,
            'time'            => date('H:i', strtotime($now)),
            'date'            => date('d M Y', strtotime($now)),
            'status'          => 'SENT',
            'created_at'      => $now,
        ];

        return $this->response->setJSON([
            'success'      => true,
            'message_id'   => $msgId,
            'message_data' => $formattedMsg,
            'media_url'    => $finalUrl,
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_send_media
     */
    public function ajax_send_media()
    {
        return $this->ajaxSendMedia();
    }

    /**
     * AJAX: Polling Messages for Live Updates (CI3 & CI4 Parity)
     */
    public function ajaxPollMessages(?int $kolId = null)
    {
        $targetKolId = $kolId ?: (int)($this->request->getPost('kol_id') ?: $this->request->getGet('kol_id'));
        $lastId      = (int)($this->request->getPost('last_id') ?: ($this->request->getGet('last_id') ?: 0));

        if (empty($targetKolId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'KOL ID diperlukan.']);
        }

        if ($lastId > 0) {
            $messages = $this->whatsappModel->getNewMessages($targetKolId, $lastId);
        } else {
            $messages = $this->whatsappModel->getMessagesByKol($targetKolId, 50);
        }

        // Auto mark as read on polling
        $this->whatsappModel->markAsRead($targetKolId);

        return $this->response->setJSON([
            'success'  => true,
            'count'    => count($messages),
            'messages' => $messages,
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_poll_messages
     */
    public function ajax_poll_messages()
    {
        return $this->ajaxPollMessages();
    }

    /**
     * AJAX: Unread Counts (CI3 & CI4 Parity)
     */
    public function ajaxUnreadCounts()
    {
        return $this->response->setJSON([
            'success'       => true,
            'unread_by_kol' => $this->whatsappModel->getUnreadCountsByKol(),
            'total_unread'  => $this->whatsappModel->getTotalUnreadCount(),
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_unread_counts
     */
    public function ajax_unread_counts()
    {
        return $this->ajaxUnreadCounts();
    }

    /**
     * AJAX: Get WAHA Session Status (CI3 & CI4 Parity)
     */
    public function ajaxSessionStatus()
    {
        $status = $this->wahaClient->getSessionStatus();
        return $this->response->setJSON([
            'success' => true,
            'data'    => $status,
            'status'  => $status['status'] ?? 'CONNECTED',
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_session_status
     */
    public function ajax_session_status()
    {
        return $this->ajaxSessionStatus();
    }

    /**
     * AJAX: Start / Reconnect WAHA Session (CI3 & CI4 Parity)
     */
    public function ajaxStartSession()
    {
        $this->whatsappModel->updateSession('default', [
            'status'         => 'CONNECTED',
            'last_active_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sesi WhatsApp gateway default berhasil dikoneksikan!',
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_start_session
     */
    public function ajax_start_session()
    {
        return $this->ajaxStartSession();
    }

    /**
     * AJAX: Logout WAHA Session (CI3 & CI4 Parity)
     */
    public function ajaxLogoutWaha()
    {
        $this->whatsappModel->updateSession('default', [
            'status'         => 'DISCONNECTED',
            'last_active_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sesi WhatsApp gateway berhasil diputuskan / logout.',
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_logout_waha
     */
    public function ajax_logout_waha()
    {
        return $this->ajaxLogoutWaha();
    }

    /**
     * AJAX: Get KOLs eligible for broadcast (CI3 & CI4 Parity)
     */
    public function ajaxGetBroadcastKols()
    {
        $campaignId = $this->request->getGet('campaign_id');
        $db = \Config\Database::connect();

        $builder = $db->table('kols')
            ->select('kols.id, kols.name, kols.whatsapp, (SELECT t.name FROM tiers t JOIN kol_platforms kp ON kp.kol_id = kols.id WHERE kp.followers_count >= t.min_followers ORDER BY t.min_followers DESC LIMIT 1) as tier_name')
            ->where('kols.deleted_at IS NULL')
            ->where('kols.whatsapp IS NOT NULL')
            ->where('kols.whatsapp !=', '');

        if ($campaignId) {
            $builder->join('campaign_kols', 'campaign_kols.kol_id = kols.id', 'inner')
                ->where('campaign_kols.campaign_id', (int)$campaignId);
        }

        $kols = $builder->groupBy('kols.id')->get()->getResultArray();

        return $this->response->setJSON(['success' => true, 'kols' => $kols]);
    }

    /**
     * CI3 Parity Alias for ajax_get_broadcast_kols
     */
    public function ajax_get_broadcast_kols()
    {
        return $this->ajaxGetBroadcastKols();
    }

    /**
     * AJAX: Send Single Item in Broadcast Loop (CI3 & CI4 Parity)
     */
    public function ajaxSendBroadcastItem()
    {
        $kolId       = (int)$this->request->getPost('kol_id');
        $templateId  = (int)$this->request->getPost('template_id');
        $customText  = trim((string)$this->request->getPost('custom_text'));
        $campaignId  = (int)$this->request->getPost('campaign_id');

        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('id', $kolId)->get()->getRowArray();
        if (!$kol || empty($kol['whatsapp'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'KOL tidak valid']);
        }

        $campaign = $campaignId ? $db->table('campaigns')->where('id', $campaignId)->get()->getRowArray() : null;

        $msgBody = $customText;
        if ($templateId && empty($customText)) {
            $tpl = $db->table('whatsapp_templates')->where('id', $templateId)->get()->getRowArray();
            if ($tpl) {
                $msgBody = $tpl['body_text'];
            }
        }

        // Replace tags
        $msgBody = str_replace(
            ['{kol_name}', '{campaign_name}', '{vt_title}'],
            [
                $kol['name'],
                $campaign['title'] ?? 'Campaign Eksklusif',
                'VT Kolaborasi',
            ],
            $msgBody
        );

        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$kol['whatsapp']);
        $session = session();
        $senderName = $session->get('name') ?: ($session->get('user')['name'] ?? 'Staff Kolab.id');

        $wahaRes = $this->wahaClient->sendMessage($cleanPhone, $msgBody);

        $this->whatsappModel->recordMessage([
            'kol_id'          => $kolId,
            'recipient_phone' => $cleanPhone,
            'direction'       => 'OUTBOUND',
            'sender_name'     => $senderName,
            'message_text'    => $msgBody,
            'status'          => 'SENT',
            'sent_at'         => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'kol_name' => $kol['name'],
            'phone'    => $cleanPhone,
        ]);
    }

    /**
     * CI3 Parity Alias for ajax_send_broadcast_item
     */
    public function ajax_send_broadcast_item()
    {
        return $this->ajaxSendBroadcastItem();
    }

    /**
     * Health check / ping endpoint (GET /api/whatsapp/ping)
     */
    public function ping()
    {
        return $this->response->setJSON([
            'status'  => 'OK',
            'service' => 'KOLAB.ID WhatsApp Webhook Gateway',
            'time'    => date('Y-m-d H:i:s'),
            'waha'    => $this->wahaClient->getSessionStatus(),
        ]);
    }

    /**
     * Webhook Endpoint for Incoming WhatsApp Messages (WAHA & Baileys)
     */
    public function webhook()
    {
        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true) ?: [];
        }

        if (strtolower($this->request->getMethod()) === 'get' && empty($input)) {
            return $this->response->setJSON([
                'status'  => 'OK',
                'service' => 'KOLAB.ID WhatsApp Webhook Receiver',
                'time'    => date('Y-m-d H:i:s'),
            ]);
        }

        $event   = $input['event'] ?? 'message';
        $payload = $input['payload'] ?? $input;

        // Session status updates
        if ($event === 'session.status') {
            $sessStatus = $payload['status'] ?? 'CONNECTED';
            $this->whatsappModel->updateSession($input['session'] ?? 'default', [
                'status'         => strtoupper($sessStatus),
                'last_active_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->response->setJSON(['success' => true, 'event' => 'session_updated']);
        }

        // ACK handling (message delivery/read receipts)
        if ($event === 'message.ack') {
            $wahaMsgId = $payload['id'] ?? ($payload['messageId'] ?? '');
            $ackStatus = $payload['ack'] ?? ($payload['status'] ?? 'DELIVERED');
            $statusMap = [
                1 => 'SENT',
                2 => 'DELIVERED',
                3 => 'READ',
                4 => 'READ',
            ];
            $mapped = is_numeric($ackStatus) ? ($statusMap[(int)$ackStatus] ?? 'DELIVERED') : strtoupper($ackStatus);
            $this->whatsappModel->updateMessageStatus($wahaMsgId, $mapped);

            return $this->response->setJSON(['success' => true, 'event' => 'ack_updated', 'status' => $mapped]);
        }

        // Inbound Message handling
        $rawFrom = $payload['from'] ?? ($payload['sender'] ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawFrom);
        $fromMe = !empty($payload['fromMe']);

        $bodyText = $payload['body'] ?? ($payload['text'] ?? ($payload['message']['conversation'] ?? ''));
        $senderName = $payload['pushName'] ?? ($payload['_data']['notifyName'] ?? 'KOL Talent');
        $mediaUrl = $payload['mediaUrl'] ?? ($payload['media']['url'] ?? null);
        $mediaMime = $payload['mediaMime'] ?? ($payload['media']['mimetype'] ?? null);

        if (empty($bodyText) && empty($mediaUrl)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Ignored empty payload']);
        }

        // Match phone to registered KOL
        $kol = $this->whatsappModel->findKolByPhone($cleanPhone);

        $msgId = $this->whatsappModel->recordMessage([
            'waha_message_id' => $payload['id'] ?? ('inbound_' . time()),
            'kol_id'          => $kol['id'] ?? null,
            'recipient_phone' => $cleanPhone,
            'direction'       => $fromMe ? 'OUTBOUND' : 'INBOUND',
            'sender_name'     => $fromMe ? 'Staff Kolab.id' : ($kol['name'] ?? $senderName),
            'message_text'    => $bodyText,
            'media_url'       => $mediaUrl,
            'media_mime'      => $mediaMime,
            'status'          => 'DELIVERED',
            'sent_at'         => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'     => true,
            'message_id'  => $msgId,
            'matched_kol' => $kol ? $kol['name'] : null,
        ]);
    }

    /**
     * Generate dynamic contextual templates
     */
    private function _generateTemplates(?array $kol, ?array $campaign, array $tasks, ?array $shipment): array
    {
        $dbTemplates = $this->whatsappModel->getTemplates();
        $kolName = $kol['name'] ?? 'Kak';
        $campaignTitle = $campaign['title'] ?? 'Campaign Eksklusif';
        $tracking = $shipment['tracking_number'] ?? ($shipment['tracking_no'] ?? '-');
        $courier = $shipment['courier_name'] ?? ($shipment['courier'] ?? 'Ekspedisi');

        $result = [];
        foreach ($dbTemplates as $tpl) {
            $replaced = str_replace(
                ['{kol_name}', '{campaign_name}', '{tracking_number}', '{courier}'],
                [$kolName, $campaignTitle, $tracking, $courier],
                $tpl['body_text']
            );
            $result[] = [
                'id'            => $tpl['id'],
                'template_code' => $tpl['template_code'],
                'title'         => $tpl['title'],
                'body_text'     => $replaced,
            ];
        }

        return $result;
    }
}
