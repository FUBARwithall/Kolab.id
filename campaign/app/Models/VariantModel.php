<?php

namespace App\Models;

use CodeIgniter\Model;

class VariantModel extends Model
{
    protected $table            = 'variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id',
        'name',
        'sku',
        'stock_qty',
        'additional_price',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'product_id'       => 'required|is_not_unique[products.id]',
        'name'             => 'required|min_length[2]|max_length[100]',
        'sku'              => 'required|min_length[2]|max_length[100]|is_unique[variants.sku,id,{id}]',
        'stock_qty'        => 'required|integer',
        'additional_price' => 'permit_empty|numeric',
    ];

    /**
     * Mengambil daftar varian beserta nama produk induknya
     */
    public function getWithProduct(?int $productId = null): array
    {
        $builder = $this->builder()
            ->select('variants.*, products.name as product_name, products.sku as product_sku')
            ->join('products', 'products.id = variants.product_id', 'left');

        if ($productId !== null) {
            $builder->where('variants.product_id', $productId);
        }

        return $builder->orderBy('variants.id', 'desc')->get()->getResultArray();
    }
}
