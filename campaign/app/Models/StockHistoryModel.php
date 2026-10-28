<?php

namespace App\Models;

use CodeIgniter\Model;

class StockHistoryModel extends Model
{
    protected $table            = 'stock_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id',
        'variant_id',
        'type',
        'qty',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false; // created_at diset manual / otomatis via datetime

    /**
     * Mencatat mutasi stok
     */
    public function record(int $productId, ?int $variantId, string $type, int $qty, int $balanceAfter, ?string $refType = null, ?int $refId = null, ?string $notes = null, ?int $userId = null): bool
    {
        return (bool)$this->insert([
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'type'           => strtoupper($type), // IN, OUT, ADJUSTMENT
            'qty'            => $qty,
            'balance_after'  => $balanceAfter,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'notes'          => $notes,
            'created_by'     => $userId ?: (session()->get('user_id') ?: 1),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mengambil riwayat mutasi stok lengkap dengan relasi nama produk dan user
     */
    public function getHistoryWithDetails(int $limit = 100, ?int $productId = null): array
    {
        $builder = $this->builder()
            ->select('stock_history.*, products.name as product_name, products.sku as product_sku, variants.name as variant_name, users.name as user_name')
            ->join('products', 'products.id = stock_history.product_id', 'left')
            ->join('variants', 'variants.id = stock_history.variant_id', 'left')
            ->join('users', 'users.id = stock_history.created_by', 'left')
            ->orderBy('stock_history.id', 'desc');

        if ($productId !== null) {
            $builder->where('stock_history.product_id', $productId);
        }

        return $builder->limit($limit)->get()->getResultArray();
    }
}
