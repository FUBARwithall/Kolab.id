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
        'kol_id',
        'recipient_phone',
        'direction',
        'sender_name',
        'message_text',
        'media_url',
        'status',
        'error_message',
        'sent_at',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get list of KOLs with conversation summary
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
                (SELECT wm.created_at FROM whatsapp_messages wm WHERE wm.kol_id = kols.id OR wm.recipient_phone = REPLACE(REPLACE(kols.whatsapp, "+", ""), " ", "") ORDER BY wm.id DESC LIMIT 1) as last_message_time
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
    public function getMessagesByKol(int $kolId, int $limit = 100): array
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

        return $builder->limit($limit)->get()->getResultArray();
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
}
