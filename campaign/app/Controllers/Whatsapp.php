<?php

namespace App\Controllers;

use App\Models\WhatsappModel;
use App\Models\CampaignModel;
use App\Libraries\WahaClient;

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
     * Main WhatsApp Communication Console
     */
    public function index(?int $kolId = null)
    {
        $search = $this->request->getGet('q');
        $conversations = $this->whatsappModel->getKolConversations($search);

        // Pick selected KOL
        $selectedKol = null;
        if (!empty($kolId)) {
            foreach ($conversations as $c) {
                if ((int)$c['id'] === $kolId) {
                    $selectedKol = $c;
                    break;
                }
            }
        }

        // Default to first conversation if not selected
        if (!$selectedKol && !empty($conversations)) {
            $selectedKol = $conversations[0];
            $kolId = (int)$selectedKol['id'];
        }

        $messages = $kolId ? $this->whatsappModel->getMessagesByKol($kolId) : [];
        $templates = $this->whatsappModel->getTemplates();
        $sessionStatus = $this->wahaClient->getSessionStatus();

        // Context info for selected KOL
        $db = \Config\Database::connect();
        $kolContext = null;
        if ($kolId) {
            $activeDeals = $db->table('campaign_kols')
                ->select('campaign_kols.*, campaigns.title as campaign_title')
                ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
                ->where('campaign_kols.kol_id', $kolId)
                ->orderBy('campaign_kols.id', 'DESC')
                ->get()
                ->getResultArray();

            $pendingTasks = $db->table('tasks')
                ->select('tasks.*, campaigns.title as campaign_title')
                ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
                ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
                ->where('campaign_kols.kol_id', $kolId)
                ->whereNotIn('tasks.status', ['COMPLETED', 'CANCELLED'])
                ->orderBy('tasks.id', 'ASC')
                ->get()
                ->getResultArray();

            $latestShipment = $db->table('kol_shipments')
                ->select('kol_shipments.*, campaigns.title as campaign_title')
                ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'inner')
                ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
                ->where('campaign_kols.kol_id', $kolId)
                ->orderBy('kol_shipments.id', 'DESC')
                ->get()
                ->getRowArray();

            $kolContext = [
                'deals'    => $activeDeals,
                'tasks'    => $pendingTasks,
                'shipment' => $latestShipment,
            ];
        }

        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();

        $data = [
            'title'          => 'WhatsApp Console & Broadcast Gateway',
            'controller'     => 'Whatsapp',
            'conversations'  => $conversations,
            'selectedKol'    => $selectedKol,
            'kolId'          => $kolId,
            'messages'       => $messages,
            'templates'      => $templates,
            'sessionStatus'  => $sessionStatus,
            'kolContext'     => $kolContext,
            'allCampaigns'   => $allCampaigns,
            'searchQuery'    => $search,
        ];

        return view('whatsapp/index', $data);
    }

    /**
     * Dedicated chat entry
     */
    public function chat(?int $kolId = null)
    {
        return $this->index($kolId);
    }

    /**
     * AJAX: Send WhatsApp Text Message
     */
    public function ajaxSendMessage()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Direct access not allowed']);
        }

        $kolId   = (int)$this->request->getPost('kol_id');
        $message = trim((string)$this->request->getPost('message'));

        if (empty($kolId) || empty($message)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'KOL dan isi pesan wajib diisi.']);
        }

        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('id', $kolId)->get()->getRowArray();
        if (!$kol || empty($kol['whatsapp'])) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'KOL tidak ditemukan atau belum memiliki nomor WhatsApp.']);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$kol['whatsapp']);
        $session = session();
        $senderName = $session->get('name') ?: 'Staff Kolab.id';

        // Dispatch via WAHA Client
        $wahaRes = $this->wahaClient->sendMessage($cleanPhone, $message);

        // Record in Database
        $msgId = $this->whatsappModel->recordMessage([
            'kol_id'          => $kolId,
            'recipient_phone' => $cleanPhone,
            'direction'       => 'OUTBOUND',
            'sender_name'     => $senderName,
            'message_text'    => $message,
            'status'          => $wahaRes['success'] ? 'SENT' : 'PENDING',
            'error_message'   => $wahaRes['error'] ?? null,
            'sent_at'         => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'     => true,
            'message_id'  => $msgId,
            'sent_at'     => date('H:i'),
            'sender_name' => $senderName,
            'waha_result' => $wahaRes,
        ]);
    }

    /**
     * AJAX: Polling Messages for Live Updates
     */
    public function ajaxPollMessages(int $kolId)
    {
        $messages = $this->whatsappModel->getMessagesByKol($kolId, 50);

        $formatted = [];
        foreach ($messages as $m) {
            $formatted[] = [
                'id'           => $m['id'],
                'direction'    => $m['direction'] ?? 'OUTBOUND',
                'sender_name'  => $m['sender_name'] ?? 'System',
                'message_text' => esc($m['message_text']),
                'media_url'    => $m['media_url'] ?? null,
                'status'       => $m['status'],
                'time'         => date('H:i', strtotime($m['created_at'])),
                'date'         => date('d M Y', strtotime($m['created_at'])),
            ];
        }

        return $this->response->setJSON([
            'success'  => true,
            'count'    => count($formatted),
            'messages' => $formatted,
        ]);
    }

    /**
     * AJAX: Get WAHA Session Status
     */
    public function ajaxSessionStatus()
    {
        $status = $this->wahaClient->getSessionStatus();
        return $this->response->setJSON(['success' => true, 'data' => $status]);
    }

    /**
     * AJAX: Start / Reconnect WAHA Session
     */
    public function ajaxStartSession()
    {
        $db = \Config\Database::connect();
        $db->table('whatsapp_sessions')->upsert([
            'session_name'   => 'default',
            'status'         => 'CONNECTED',
            'last_active_at' => date('Y-m-d H:i:s'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sesi WhatsApp gateway default berhasil dikoneksikan!',
        ]);
    }

    /**
     * AJAX: Get KOLs eligible for broadcast
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
     * AJAX: Send Single Item in Broadcast Loop
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
        $senderName = session('name') ?: 'Staff Kolab.id';

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
     * Webhook Endpoint for Incoming WhatsApp Messages (WAHA & Baileys)
     */
    public function webhook()
    {
        if ($this->request->getMethod() === 'GET') {
            return $this->response->setJSON([
                'status'  => 'OK',
                'service' => 'KOLAB.ID WhatsApp Webhook Receiver',
                'time'    => date('Y-m-d H:i:s'),
            ]);
        }

        $input = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true) ?: [];
        }

        $event   = $input['event'] ?? 'message';
        $payload = $input['payload'] ?? $input;

        // Session status updates
        if ($event === 'session.status') {
            $sessStatus = $payload['status'] ?? 'CONNECTED';
            $db = \Config\Database::connect();
            $db->table('whatsapp_sessions')->upsert([
                'session_name'   => $input['session'] ?? 'default',
                'status'         => strtoupper($sessStatus),
                'last_active_at' => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            return $this->response->setJSON(['success' => true, 'event' => 'session_updated']);
        }

        // Message handling
        $rawFrom = $payload['from'] ?? ($payload['sender'] ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawFrom);
        $fromMe = !empty($payload['fromMe']);

        $bodyText = $payload['body'] ?? ($payload['text'] ?? ($payload['message']['conversation'] ?? ''));
        $senderName = $payload['pushName'] ?? ($payload['_data']['notifyName'] ?? 'KOL Talent');
        $mediaUrl = $payload['mediaUrl'] ?? ($payload['media'] ?? null);

        if (empty($bodyText) && empty($mediaUrl)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Ignored empty payload']);
        }

        // Match phone to registered KOL
        $db = \Config\Database::connect();
        $kol = null;
        if (!empty($cleanPhone)) {
            $shortPhone = substr($cleanPhone, -8);
            $kol = $db->table('kols')->like('whatsapp', $shortPhone)->get()->getRowArray();
        }

        $msgId = $this->whatsappModel->recordMessage([
            'kol_id'          => $kol['id'] ?? null,
            'recipient_phone' => $cleanPhone,
            'direction'       => $fromMe ? 'OUTBOUND' : 'INBOUND',
            'sender_name'     => $fromMe ? 'Staff Kolab.id' : ($kol['name'] ?? $senderName),
            'message_text'    => $bodyText,
            'media_url'       => $mediaUrl,
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
}
