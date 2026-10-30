<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentVariantModel extends Model
{
    protected $table            = 'content_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'      => 'required|min_length[2]|max_length[100]',
        'code'      => 'required|min_length[2]|max_length[50]',
        'is_active' => 'required|in_list[0,1]',
    ];

    /**
     * Server-side DataTables query
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'id', string $orderDir = 'ASC'): array
    {
        $builder = $this->db->table($this->table);

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $builder->where('is_active', (int)$filters['is_active']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('code', $search)
                ->orLike('description', $search)
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
     * Get active content variants for dropdowns
     */
    public function getActiveVariants(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
