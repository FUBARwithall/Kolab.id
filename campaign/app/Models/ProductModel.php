<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'sku',
        'description',
        'stock_qty',
        'unit_price',
        'image_url',
        'is_active',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'       => 'required|min_length[3]|max_length[150]',
        'sku'        => 'required|min_length[2]|max_length[100]|is_unique[products.sku,id,{id}]',
        'stock_qty'  => 'required|integer',
        'unit_price' => 'required|numeric',
        'is_active'  => 'required|in_list[0,1]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Mengambil statistik ringkasan produk (mini dashboard)
     */
    public function getSummary(): array
    {
        $totalProducts = $this->countAllResults(false);
        $activeProducts = (int)$this->where('is_active', 1)->countAllResults(false);
        $lowStockProducts = (int)$this->where('stock_qty <=', 10)->countAllResults(false);

        $db = \Config\Database::connect();
        $stockRow = $db->table($this->table)
            ->selectSum('stock_qty', 'total_stock')
            ->get()
            ->getRow();

        return [
            'total_products'    => $totalProducts,
            'total_stock'       => (int)($stockRow ? $stockRow->total_stock : 0),
            'active_products'   => $activeProducts,
            'low_stock_products' => $lowStockProducts,
        ];
    }

    /**
     * DataTables Server-Side Query
     */
    public function getDatatables(int $start, int $length, string $searchValue = '', string $orderColumn = 'id', string $orderDir = 'desc', ?string $filterStatus = null): array
    {
        $builder = $this->builder();

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('name', $searchValue)
                ->orLike('sku', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('is_active', (int)$filterStatus);
        }

        $allowedColumns = ['id', 'image_url', 'sku', 'name', 'unit_price', 'stock_qty', 'is_active', 'created_at'];
        if (in_array($orderColumn, $allowedColumns, true)) {
            $builder->orderBy($orderColumn, $orderDir);
        } else {
            $builder->orderBy('id', 'desc');
        }

        return $builder->get($length, $start)->getResultArray();
    }

    /**
     * Hitung total data setelah filter pencarian
     */
    public function countFiltered(string $searchValue = '', ?string $filterStatus = null): int
    {
        $builder = $this->builder();

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('name', $searchValue)
                ->orLike('sku', $searchValue)
                ->orLike('description', $searchValue)
                ->groupEnd();
        }

        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('is_active', (int)$filterStatus);
        }

        return $builder->countAllResults();
    }
}
