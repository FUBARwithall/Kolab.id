<?php

namespace App\Models;

use CodeIgniter\Model;

class KolCategoryModel extends Model
{
    protected $table            = 'kol_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'category_id',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * DataTables Server-Side AJAX query
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'kol_categories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_categories.*,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                u.email as kol_email,
                categories.name as category_name,
                categories.slug as category_slug,
                (SELECT COUNT(kp.id) FROM kol_platforms kp WHERE kp.kol_id = kols.id) as platform_count
            ')
            ->join('kols', 'kols.id = kol_categories.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('categories', 'categories.id = kol_categories.category_id', 'left');

        if (!empty($filters['category_id'])) {
            $builder->where('kol_categories.category_id', (int)$filters['category_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kol_categories.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kols.name', $search)
                ->orLike('u.email', $search)
                ->orLike('categories.name', $search)
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
     * Get single detail with joined KOL and Category info
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                kol_categories.*,
                kols.name as kol_name,
                kols.whatsapp as kol_phone,
                u.email as kol_email,
                categories.name as category_name,
                categories.slug as category_slug,
                categories.description as category_desc
            ')
            ->join('kols', 'kols.id = kol_categories.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('categories', 'categories.id = kol_categories.category_id', 'left')
            ->where('kol_categories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Check if a KOL is already assigned to a category
     */
    public function checkExists(int $kolId, int $categoryId, ?int $excludeId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('kol_id', $kolId)
            ->where('category_id', $categoryId);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get all assignments for export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_categories.id,
                kol_categories.kol_id,
                kols.name as kol_name,
                u.email as kol_email,
                kol_categories.category_id,
                categories.name as category_name,
                kol_categories.created_at
            ')
            ->join('kols', 'kols.id = kol_categories.kol_id', 'left')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->join('categories', 'categories.id = kol_categories.category_id', 'left')
            ->orderBy('kol_categories.id', 'DESC');

        if (!empty($filters['category_id'])) {
            $builder->where('kol_categories.category_id', (int)$filters['category_id']);
        }

        return $builder->get()->getResultArray();
    }
}
