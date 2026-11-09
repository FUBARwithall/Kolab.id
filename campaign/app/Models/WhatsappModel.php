<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappModel extends Model
{
    protected $table            = 'whatsapp_messages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'waha_message_id',
        'kol_id',
        'campaign_kol_id',
        'direction',
        'sender_name',
        'recipient_phone',
        'message_text',
        'media_url',
        'media_mime',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get list of KOLs with conversation summary and unread counters
     */
    public function getKolConversations(?string $search = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('kols')
            ->select('
                kols.id,
                kols.name,
                kols.whatsapp,
                kols.city,
                (SELECT t.name FROM tiers t JOIN kol_platforms kp ON kp.kol_id = kols.id WHERE kp.followers_count >= t.min_followers ORDER BY t.min_followers DESC LIMIT 1) as tier_name,
                users.avatar as user_avatar,
                (SELECT COUNT(ck.id) FROM campaign_kols ck WHERE ck.kol_id = kols.id) as active_deals,
                (SELECT c.title FROM campaign_kols ck JOIN campaigns c ON c.id = ck.campaign_id WHERE ck.kol_id = kols.id ORDER BY ck.id DESC LIMIT 1) as latest_campaign,
                (SELECT wm.message_text FROM whatsapp_messages wm WHERE wm.kol_id = kols.id OR wm.recipient_phone = REPLACE(REPLACE(kols.whatsapp, "+", ""), " ", "") ORDER BY wm.id DESC LIMIT 1) as last_message,
                (SELECT wm.direction FROM whatsapp_messages wm WHERE wm.kol_id = kols.id OR wm.recipient_phone = REPLACE(REPLACE(kols.whatsapp, "+", ""), " ", "") ORDER BY wm.id DESC LIMIT 1) as last_direction,
                (SELECT wm.created_at FROM whatsapp_messages wm WHERE wm.kol_id = kols.id OR wm.recipient_phone = REPLACE(REPLACE(kols.whatsapp, "+", ""), " ", "") ORDER BY wm.id DESC LIMIT 1) as last_message_time,
                (SELECT COUNT(wm2.id) FROM whatsapp_messages wm2 WHERE (wm2.kol_id = kols.id OR wm2.recipient_phone = REPLACE(REPLACE(kols.whatsapp, "+", ""), " ", "")) AND wm2.direction = "INBOUND" AND wm2.status != "READ") as unread_count
            ')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('kols.deleted_at IS NULL');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kols.name', $search)
                ->orLike('kols.whatsapp', $search)
                ->groupEnd();
        }

        return $builder->orderBy('last_message_time', 'DESC')->get()->getResultArray();
    }

    /**
     * Get message thread for a specific KOL
     */
    public function getMessagesByKol(int $kolId, int $limit = 100, int $offset = 0): array
    {
        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('id', $kolId)->get()->getRowArray();
        if (!$kol) {
            return [];
        }

        $phone = preg_replace('/[^0-9]/', '', (string)$kol['whatsapp']);
        $phoneAlt = '';
        if (str_starts_with($phone, '62')) {
            $phoneAlt = '0' . substr($phone, 2);
        } elseif (str_starts_with($phone, '0')) {
            $phoneAlt = '62' . substr($phone, 1);
        }

        $builder = $this->builder()
            ->groupStart()
                ->where('kol_id', $kolId);
        
        if (!empty($phone)) {
            $builder->orWhere('recipient_phone', $phone);
        }
        if (!empty($phoneAlt)) {
            $builder->orWhere('recipient_phone', $phoneAlt);
        }

        $builder->groupEnd()
            ->orderBy('id', 'ASC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        $rows = $builder->get()->getResultArray();
        $messages = [];
        foreach ($rows as $r) {
            $messages[] = $this->formatMessageRow($r);
        }

        return $messages;
    }

    /**
     * Retrieve new messages for polling
     */
    public function getNewMessages(int $kolId, int $lastId = 0): array
    {
        if (empty($kolId)) {
            return [];
        }

        $rows = $this->builder()
            ->where('kol_id', $kolId)
            ->where('id >', $lastId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $messages = [];
        foreach ($rows as $r) {
            $messages[] = $this->formatMessageRow($r);
        }

        return $messages;
    }

    /**
     * Get total count of unread incoming messages
     */
    public function getTotalUnreadCount(): int
    {
        return $this->builder()
            ->where('direction', 'INBOUND')
            ->where('status !=', 'READ')
            ->countAllResults();
    }

    /**
     * Get unread incoming messages grouped by kol_id
     */
    public function getUnreadCountsByKol(): array
    {
        $rows = $this->builder()
            ->select('kol_id, COUNT(*) as unread_count')
            ->where('direction', 'INBOUND')
            ->where('status !=', 'READ')
            ->where('kol_id IS NOT NULL')
            ->groupBy('kol_id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $r) {
            $counts[(int)$r['kol_id']] = (int)$r['unread_count'];
        }
        return $counts;
    }

    /**
     * Mark all incoming messages for a specific KOL as READ
     */
    public function markAsRead(int $kolId): bool
    {
        if (empty($kolId)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        return (bool)$this->builder()
            ->where('kol_id', $kolId)
            ->where('direction', 'INBOUND')
            ->where('status !=', 'READ')
            ->update([
                'status'  => 'READ',
                'read_at' => $now,
            ]);
    }

    /**
     * Update message ack status from WAHA webhook
     */
    public function updateMessageStatus(string $wahaMsgId, string $status, ?string $timestamp = null): bool
    {
        if (empty($wahaMsgId)) {
            return false;
        }

        $update = ['status' => strtoupper($status)];
        $dt = !empty($timestamp) ? date('Y-m-d H:i:s', is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp)) : date('Y-m-d H:i:s');

        if (strtoupper($status) === 'DELIVERED') {
            $update['delivered_at'] = $dt;
        } elseif (strtoupper($status) === 'READ') {
            $update['read_at'] = $dt;
        }

        return (bool)$this->builder()
            ->where('waha_message_id', $wahaMsgId)
            ->update($update);
    }

    /**
     * Intelligent matching of a phone number / JID to a registered KOL
     */
    public function findKolByPhone(string $phoneOrJid): ?array
    {
        if (empty($phoneOrJid)) {
            return null;
        }

        $clean = preg_replace('/@[a-zA-Z0-9\.\_\-]+$/', '', trim($phoneOrJid));
        $clean = preg_replace('/:[0-9]+$/', '', $clean);
        $digits = preg_replace('/[^0-9]/', '', $clean);

        if (empty($digits) || strlen($digits) < 7) {
            return null;
        }

        $short = substr($digits, -8);
        $db = \Config\Database::connect();
        return $db->table('kols')
            ->like('whatsapp', $short)
            ->where('deleted_at IS NULL')
            ->get()
            ->getRowArray();
    }

    /**
     * Format message row for presentation / JSON
     */
    public function formatMessageRow(array $r): array
    {
        $timeStr = !empty($r['sent_at']) ? date('H:i', strtotime($r['sent_at'])) : date('H:i', strtotime($r['created_at']));
        $dateStr = !empty($r['sent_at']) ? date('d M Y', strtotime($r['sent_at'])) : date('d M Y', strtotime($r['created_at']));

        $text = $r['message_text'] ?? ($r['message_body'] ?? '');

        return [
            'id'              => (int)$r['id'],
            'waha_message_id' => $r['waha_message_id'] ?? null,
            'direction'       => $r['direction'] ?? 'OUTBOUND',
            'sender_name'     => $r['sender_name'] ?? 'System',
            'text'            => $text,
            'message_text'    => $text,
            'message_body'    => $text, // CI3 alias
            'media_url'       => $r['media_url'] ?? null,
            'media_mime'      => $r['media_mime'] ?? null,
            'status'          => $r['status'] ?? 'SENT',
            'time'            => $timeStr,
            'date'            => $dateStr,
            'sent_at'         => $r['sent_at'] ?? null,
            'created_at'      => $r['created_at'] ?? null,
        ];
    }

    /**
     * Get active message templates
     */
    public function getTemplates(): array
    {
        $db = \Config\Database::connect();
        return $db->table('whatsapp_templates')
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Record new message
     */
    public function recordMessage(array $data): int
    {
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (empty($data['sent_at']) && ($data['status'] ?? 'SENT') === 'SENT') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }

        if (isset($data['message_body']) && !isset($data['message_text'])) {
            $data['message_text'] = $data['message_body'];
            unset($data['message_body']);
        }

        $this->insert($data);
        return (int)$this->getInsertID();
    }

    /**
     * Get active session
     */
    public function getSession(string $name = 'default'): ?array
    {
        $db = \Config\Database::connect();
        return $db->table('whatsapp_sessions')
            ->where('session_name', $name)
            ->get()
            ->getRowArray();
    }

    /**
     * Update session status
     */
    public function updateSession(string $name, array $data): bool
    {
        $db = \Config\Database::connect();
        $exists = $this->getSession($name);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($exists) {
            return (bool)$db->table('whatsapp_sessions')
                ->where('session_name', $name)
                ->update($data);
        }

        $data['session_name'] = $name;
        $data['created_at'] = date('Y-m-d H:i:s');
        return (bool)$db->table('whatsapp_sessions')->insert($data);
    }
}
