<?php

namespace App\Models;

use CodeIgniter\Model;

class StockHistoryModel extends Model
{
    public const TYPE_IN         = 'IN';
    public const TYPE_OUT        = 'OUT';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

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
        'stock_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;

    /**
     * Mencatat mutasi stok
     */
    public function record(
        int $productId,
        ?int $variantId,
        string $type,
        int $qty,
        int $balanceAfter,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null,
        ?int $userId = null,
        ?int $stockBefore = null
    ): bool {
        $type = strtoupper($type);

        if ($stockBefore === null) {
            if ($type === self::TYPE_IN) {
                $stockBefore = $balanceAfter - $qty;
            } elseif ($type === self::TYPE_OUT) {
                $stockBefore = $balanceAfter + $qty;
            } else {
                $stockBefore = $balanceAfter;
            }
        }

        return (bool)$this->insert([
            'product_id'     => $productId,
            'variant_id'     => $variantId,
            'type'           => $type, // IN, OUT, ADJUSTMENT
            'qty'            => $qty,
            'stock_before'   => $stockBefore,
            'balance_after'  => $balanceAfter,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'notes'          => $notes,
            'created_by'     => $userId ?: (session()->get('user_id') ?: 1),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mencatat mutasi stok sekaligus sinkronisasi otomatis saldo stok produk & varian
     */
    public function recordMutation(
        int $productId,
        ?int $variantId,
        string $type,
        int $qty,
        ?string $notes = null,
        ?int $userId = null,
        string $refType = 'MANUAL',
        ?int $refId = null
    ): bool {
        $productModel = new ProductModel();
        $product = $productModel->find($productId);
        if (!$product) {
            return false;
        }

        $type = strtoupper($type);
        $currentStock = (int)($product['stock_qty'] ?? $product['stock'] ?? 0);

        if ($type === self::TYPE_IN) {
            $newStock = $currentStock + $qty;
        } elseif ($type === self::TYPE_OUT) {
            $newStock = max(0, $currentStock - $qty);
        } else { // ADJUSTMENT
            $newStock = $qty;
        }

        // Update product stock & stock_qty
        $productModel->update($productId, [
            'stock'     => $newStock,
            'stock_qty' => $newStock,
        ]);

        // Jika terdapat varian, update juga stok varian terkait
        if ($variantId) {
            $variantModel = new VariantModel();
            $variant = $variantModel->find($variantId);
            if ($variant) {
                $curVarStock = (int)($variant['stock_qty'] ?? $variant['stock'] ?? 0);
                if ($type === self::TYPE_IN) {
                    $newVarStock = $curVarStock + $qty;
                } elseif ($type === self::TYPE_OUT) {
                    $newVarStock = max(0, $curVarStock - $qty);
                } else {
                    $newVarStock = $qty;
                }
                $variantModel->update($variantId, [
                    'stock'     => $newVarStock,
                    'stock_qty' => $newVarStock,
                ]);
            }
        }

        return $this->record(
            $productId,
            $variantId,
            $type,
            $qty,
            $newStock,
            $refType,
            $refId,
            $notes,
            $userId,
            $currentStock
        );
    }

    /**
     * Mengambil riwayat mutasi stok lengkap dengan relasi nama produk dan user
     */
    public function getHistoryWithDetails(int $limit = 100, ?int $productId = null): array
    {
        $builder = $this->builder()
            ->select('
                stock_history.*,
                stock_history.type as change_type,
                (CASE WHEN stock_history.type = "OUT" THEN -stock_history.qty ELSE stock_history.qty END) as qty_change,
                stock_history.balance_after as stock_after,
                products.name as product_name,
                products.sku as product_sku,
                variants.name as variant_name,
                users.name as user_name
            ')
            ->join('products', 'products.id = stock_history.product_id', 'left')
            ->join('variants', 'variants.id = stock_history.variant_id', 'left')
            ->join('users', 'users.id = stock_history.created_by', 'left')
            ->orderBy('stock_history.id', 'desc');

        if ($productId !== null) {
            $builder->where('stock_history.product_id', $productId);
        }

        return $builder->limit($limit)->get()->getResultArray();
    }

    /**
     * DataTables query server-side
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'stock_history.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                stock_history.*,
                stock_history.type as change_type,
                (CASE WHEN stock_history.type = "OUT" THEN -stock_history.qty ELSE stock_history.qty END) as qty_change,
                stock_history.balance_after as stock_after,
                products.name as product_name,
                products.sku as product_sku,
                products.category as product_category,
                products.price as product_price,
                variants.name as variant_name,
                variants.sku as variant_sku,
                users.name as user_name,
                users.email as user_email,
                kol_shipments.tracking_number as shipment_tracking,
                kol_shipments.tracking_no as shipment_tracking_no,
                kol_shipments.courier_name as shipment_courier,
                kol_shipments.recipient_name as shipment_recipient
            ')
            ->join('products', 'products.id = stock_history.product_id', 'left')
            ->join('variants', 'variants.id = stock_history.variant_id', 'left')
            ->join('users', 'users.id = stock_history.created_by', 'left')
            ->join('kol_shipments', 'kol_shipments.id = stock_history.reference_id AND (stock_history.reference_type = "SHIPMENT" OR stock_history.reference_type = "SHIPMENT_ITEM")', 'left');

        if (!empty($filters['product_id'])) {
            $builder->where('stock_history.product_id', (int)$filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $builder->where('stock_history.type', strtoupper($filters['type']));
        }

        if (!empty($filters['reference_type'])) {
            $builder->where('stock_history.reference_type', $filters['reference_type']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('stock_history.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('stock_history.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.sku', $search)
                ->orLike('stock_history.notes', $search)
                ->orLike('stock_history.reference_type', $search)
                ->orLike('users.name', $search)
                ->orLike('kol_shipments.tracking_number', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $validOrderCols = [
            'stock_history.id'           => 'stock_history.id',
            'stock_history.created_at'   => 'stock_history.created_at',
            'products.name'              => 'products.name',
            'stock_history.type'         => 'stock_history.type',
            'stock_history.qty'          => 'stock_history.qty',
            'stock_history.stock_before' => 'stock_history.stock_before',
            'stock_history.balance_after'=> 'stock_history.balance_after',
        ];
        $col = $validOrderCols[$orderCol] ?? 'stock_history.id';
        $dir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder->orderBy($col, $dir);

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
     * Get single detail with complete relations
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                stock_history.*,
                stock_history.type as change_type,
                (CASE WHEN stock_history.type = "OUT" THEN -stock_history.qty ELSE stock_history.qty END) as qty_change,
                stock_history.balance_after as stock_after,
                products.name as product_name,
                products.sku as product_sku,
                products.category as product_category,
                products.price as product_price,
                products.description as product_description,
                variants.name as variant_name,
                variants.sku as variant_sku,
                users.name as user_name,
                users.email as user_email,
                kol_shipments.tracking_number as shipment_tracking,
                kol_shipments.tracking_no as shipment_tracking_no,
                kol_shipments.courier_name as shipment_courier,
                kol_shipments.recipient_name as shipment_recipient
            ')
            ->join('products', 'products.id = stock_history.product_id', 'left')
            ->join('variants', 'variants.id = stock_history.variant_id', 'left')
            ->join('users', 'users.id = stock_history.created_by', 'left')
            ->join('kol_shipments', 'kol_shipments.id = stock_history.reference_id AND (stock_history.reference_type = "SHIPMENT" OR stock_history.reference_type = "SHIPMENT_ITEM")', 'left')
            ->where('stock_history.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Dashboard Summary Metrics
     */
    public function getSummary(?int $productId = null): array
    {
        $builder = $this->db->table($this->table);
        if ($productId !== null) {
            $builder->where('product_id', $productId);
        }
        $totalMutations = $builder->countAllResults();

        $inRow = $this->db->table($this->table)->selectSum('qty', 'total_in')->where('type', self::TYPE_IN);
        if ($productId !== null) $inRow->where('product_id', $productId);
        $totalIn = (int)($inRow->get()->getRowArray()['total_in'] ?? 0);

        $outRow = $this->db->table($this->table)->selectSum('qty', 'total_out')->where('type', self::TYPE_OUT);
        if ($productId !== null) $outRow->where('product_id', $productId);
        $totalOut = (int)($outRow->get()->getRowArray()['total_out'] ?? 0);

        $adjRow = $this->db->table($this->table)->selectSum('qty', 'total_adj')->where('type', self::TYPE_ADJUSTMENT);
        if ($productId !== null) $adjRow->where('product_id', $productId);
        $totalAdj = (int)($adjRow->get()->getRowArray()['total_adj'] ?? 0);

        $uniqueProds = (int)($this->db->table($this->table)
            ->select('COUNT(DISTINCT product_id) as total')
            ->get()->getRowArray()['total'] ?? 0);

        return [
            'total_mutations'  => $totalMutations,
            'total_in'         => $totalIn,
            'total_out'        => $totalOut,
            'total_adjustment' => $totalAdj,
            'unique_products'  => $uniqueProds,
        ];
    }

    /**
     * Get all matching mutations for export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                stock_history.*,
                stock_history.type as change_type,
                (CASE WHEN stock_history.type = "OUT" THEN -stock_history.qty ELSE stock_history.qty END) as qty_change,
                stock_history.balance_after as stock_after,
                products.name as product_name,
                products.sku as product_sku,
                products.category as product_category,
                products.price as product_price,
                variants.name as variant_name,
                users.name as user_name,
                kol_shipments.tracking_number as shipment_tracking,
                kol_shipments.courier_name as shipment_courier
            ')
            ->join('products', 'products.id = stock_history.product_id', 'left')
            ->join('variants', 'variants.id = stock_history.variant_id', 'left')
            ->join('users', 'users.id = stock_history.created_by', 'left')
            ->join('kol_shipments', 'kol_shipments.id = stock_history.reference_id AND (stock_history.reference_type = "SHIPMENT" OR stock_history.reference_type = "SHIPMENT_ITEM")', 'left')
            ->orderBy('stock_history.id', 'DESC');

        if (!empty($filters['product_id'])) {
            $builder->where('stock_history.product_id', (int)$filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $builder->where('stock_history.type', strtoupper($filters['type']));
        }

        if (!empty($filters['date_from'])) {
            $builder->where('stock_history.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('stock_history.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder->get()->getResultArray();
    }
}
