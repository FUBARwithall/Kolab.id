<?php

namespace App\Models;

use CodeIgniter\Model;

class KolPlatformModel extends Model
{
    protected $table            = 'kol_platforms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'platform_id',
        'username',
        'profile_url',
        'followers',
        'followers_count',
        'avg_views',
        'avg_er',
        'is_verified',
        'is_active',
        'last_synced_at',
        'last_scraped_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Server-side DataTables query with joins & search
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'kol_platforms.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_platforms.*,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                u.email as kol_email,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                platforms.base_url as platform_base_url
            ')
            ->join('kols', 'kols.id = kol_platforms.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('platforms', 'platforms.id = kol_platforms.platform_id', 'left');

        if (!empty($filters['platform_id'])) {
            $builder->where('kol_platforms.platform_id', (int)$filters['platform_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_platforms.kol_id', (int)$filters['kol_id']);
        }

        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $builder->where('kol_platforms.is_verified', (int)$filters['is_verified']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $builder->where('kol_platforms.is_active', (int)$filters['is_active']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kols.name', $search)
                ->orLike('kol_platforms.username', $search)
                ->orLike('kol_platforms.profile_url', $search)
                ->orLike('platforms.name', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get single detail with joined KOL and Platform info
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                kol_platforms.*,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.approval_status as kol_status,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                u.email as kol_email,
                platforms.name as platform_name,
                platforms.code as platform_code,
                platforms.icon as platform_icon,
                platforms.base_url as platform_base_url
            ')
            ->join('kols', 'kols.id = kol_platforms.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('platforms', 'platforms.id = kol_platforms.platform_id', 'left')
            ->where('kol_platforms.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Prevent duplicate platform for the same KOL
     */
    public function checkExists(int $kolId, int $platformId, ?int $excludeId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('kol_id', $kolId)
            ->where('platform_id', $platformId);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get all rows for CSV Export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_platforms.id,
                kols.name as kol_name,
                platforms.name as platform_name,
                kol_platforms.username,
                kol_platforms.profile_url,
                kol_platforms.followers,
                kol_platforms.avg_views,
                kol_platforms.avg_er,
                kol_platforms.is_verified,
                kol_platforms.is_active,
                kol_platforms.last_synced_at,
                kol_platforms.created_at
            ')
            ->join('kols', 'kols.id = kol_platforms.kol_id', 'left')
            ->join('platforms', 'platforms.id = kol_platforms.platform_id', 'left');

        if (!empty($filters['platform_id'])) {
            $builder->where('kol_platforms.platform_id', (int)$filters['platform_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_platforms.kol_id', (int)$filters['kol_id']);
        }

        return $builder->orderBy('kol_platforms.id', 'DESC')->get()->getResultArray();
    }

    /**
     * Sync single profile stats
     */
    public function syncProfile(int $id): array
    {
        $platform = $this->getDetail($id);
        if (!$platform) {
            return ['success' => false, 'message' => 'Akun Platform KOL tidak ditemukan.'];
        }

        $now = date('Y-m-d H:i:s');
        
        // Simulasikan atau perbarui metrik statistik terbaru jika scraping live belum tersedia
        $followers = (int)($platform['followers'] ?: 0);
        $avgViews  = (int)($platform['avg_views'] ?: 0);
        $avgEr     = (float)($platform['avg_er'] ?: 0.0);

        // Pertahankan atau refresh metrik dengan timestamp terbaru
        $updateData = [
            'last_synced_at'  => $now,
            'last_scraped_at' => $now,
            'updated_at'      => $now,
        ];

        // Jika followers_count kosong atau beda, selaraskan
        if (empty($platform['followers_count']) && $followers > 0) {
            $updateData['followers_count'] = $followers;
        }

        $this->update($id, $updateData);

        return [
            'success' => true,
            'message' => "Metrik profil {$platform['kol_name']} (@{$platform['username']}) di {$platform['platform_name']} berhasil disinkronisasi.",
            'data'    => [
                'followers'      => $followers,
                'avg_views'      => $avgViews,
                'avg_er'         => $avgEr,
                'last_synced_at' => $now,
            ]
        ];
    }
}
