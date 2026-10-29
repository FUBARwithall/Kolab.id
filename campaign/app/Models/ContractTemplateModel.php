<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractTemplateModel extends Model
{
    protected $table            = 'contract_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'body_template',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * DataTables Query for Contract Templates
     */
    public function getDatatables(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'id', string $orderDir = 'DESC'): array
    {
        $builder = $this->builder();

        if (!empty($search)) {
            $builder->like('title', $search)
                ->orLike('body_template', $search);
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }
}
