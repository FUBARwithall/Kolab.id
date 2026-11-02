<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentModel extends Model
{
    protected $table            = 'kol_shipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'courier',
        'courier_name',
        'tracking_no',
        'tracking_number',
        'shipping_fee',
        'status',
        'shipping_status',
        'notes',
        'created_by',
        'shipped_at',
        'delivered_at',
        'kol_received_at',
        'kol_received_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert  = ['syncShipmentFields'];
    protected $beforeUpdate  = ['syncShipmentFields'];

    public const STATUS_PENDING        = 'PENDING';
    public const STATUS_PROCESSING     = 'PROCESSING';
    public const STATUS_SHIPPED        = 'SHIPPED';
    public const STATUS_DELIVERED      = 'DELIVERED';
    public const STATUS_RETURN_PENDING = 'RETURN_PENDING';
    public const STATUS_RETURNED       = 'RETURNED';

    /**
     * Dual compatibility callback to keep CI3 and CI4 shipment fields in sync
     */
    protected function syncShipmentFields(array $data): array
    {
        if (isset($data['data'])) {
            // Sync courier <-> courier_name
            if (isset($data['data']['courier_name']) && !isset($data['data']['courier'])) {
                $data['data']['courier'] = $data['data']['courier_name'];
            } elseif (isset($data['data']['courier']) && !isset($data['data']['courier_name'])) {
                $data['data']['courier_name'] = $data['data']['courier'];
            }

            // Sync tracking_number <-> tracking_no
            if (isset($data['data']['tracking_number']) && !isset($data['data']['tracking_no'])) {
                $data['data']['tracking_no'] = $data['data']['tracking_number'];
            } elseif (isset($data['data']['tracking_no']) && !isset($data['data']['tracking_number'])) {
                $data['data']['tracking_number'] = $data['data']['tracking_no'];
            }

            // Sync status <-> shipping_status
            if (isset($data['data']['shipping_status']) && !isset($data['data']['status'])) {
                $data['data']['status'] = strtoupper($data['data']['shipping_status']);
            } elseif (isset($data['data']['status']) && !isset($data['data']['shipping_status'])) {
                $data['data']['shipping_status'] = strtoupper($data['data']['status']);
            }

            // Auto-populate recipient details if campaign_kol_id is provided and fields are empty
            if (!empty($data['data']['campaign_kol_id'])) {
                $ckId = (int)$data['data']['campaign_kol_id'];
                $kol = $this->db->table('campaign_kols')
                    ->select('kols.name, kols.whatsapp, kols.address, kols.city, kols.postal_code')
                    ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
                    ->where('campaign_kols.id', $ckId)
                    ->get()
                    ->getRowArray();

                if ($kol) {
                    if (empty($data['data']['recipient_name'])) {
                        $data['data']['recipient_name'] = $kol['name'];
                    }
                    if (empty($data['data']['recipient_phone'])) {
                        $data['data']['recipient_phone'] = $kol['whatsapp'] ?? '';
                    }
                    if (empty($data['data']['shipping_address'])) {
                        $addrParts = array_filter([$kol['address'] ?? '', $kol['city'] ?? '', $kol['postal_code'] ?? '']);
                        $data['data']['shipping_address'] = implode(', ', $addrParts);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * DataTables query with joins to campaign, kol, and product items
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'kol_shipments.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_shipments.*,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address,
                kols.postal_code as kol_postal_code,
                users.avatar as kol_avatar
            ')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left');

        if (!empty($filters['status'])) {
            $builder->groupStart()
                ->where('kol_shipments.shipping_status', $filters['status'])
                ->orWhere('kol_shipments.status', $filters['status'])
                ->groupEnd();
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kol_shipments.tracking_number', $search)
                ->orLike('kol_shipments.tracking_no', $search)
                ->orLike('kol_shipments.courier_name', $search)
                ->orLike('kol_shipments.courier', $search)
                ->orLike('kol_shipments.recipient_name', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('kols.city', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();

        // Attach items summary to each shipment row
        foreach ($data as &$row) {
            $row['items'] = $this->db->table('kol_shipment_items')
                ->select('kol_shipment_items.*, products.name as product_name, products.sku as product_sku, products.price as product_price, variants.name as variant_name')
                ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
                ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
                ->where('kol_shipment_items.shipment_id', $row['id'])
                ->get()
                ->getResultArray();
        }
        unset($row);

        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get single shipment with items and destination info
     */
    public function getShipmentWithItems(int $id): ?array
    {
        $shipment = $this->db->table($this->table)
            ->select('
                kol_shipments.*,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address,
                kols.postal_code as kol_postal_code,
                users.avatar as kol_avatar
            ')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('kol_shipments.id', $id)
            ->get()
            ->getRowArray();

        if (!$shipment) {
            return null;
        }

        $shipment['items'] = $this->db->table('kol_shipment_items')
            ->select('kol_shipment_items.*, products.name as product_name, products.sku as product_sku, products.price as product_price, products.category as product_category, variants.name as variant_name')
            ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
            ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
            ->where('kol_shipment_items.shipment_id', $id)
            ->get()
            ->getResultArray();

        return $shipment;
    }

    /**
     * Alias for getShipmentWithItems
     */
    public function getDetail(int $id): ?array
    {
        return $this->getShipmentWithItems($id);
    }

    /**
     * Save shipment and items atomically
     */
    public function saveShipment(array $data, array $items = [], ?int $userId = null): int
    {
        $this->db->transStart();

        $id = !empty($data['id']) ? (int)$data['id'] : null;
        unset($data['id']);

        $oldShipment = $id ? $this->find($id) : null;

        if ($id) {
            $this->update($id, $data);
            $shipmentId = $id;
        } else {
            $shipmentId = $this->insert($data, true);
        }

        if (!empty($items)) {
            // Remove previous items if updating
            if ($id) {
                $this->db->table('kol_shipment_items')->where('shipment_id', $shipmentId)->delete();
            }

            foreach ($items as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
                $qty       = max(1, (int)($item['qty'] ?? 1));

                if ($productId <= 0) {
                    continue;
                }

                $this->db->table('kol_shipment_items')->insert([
                    'shipment_id' => $shipmentId,
                    'product_id'  => $productId,
                    'variant_id'  => $variantId,
                    'qty'         => $qty,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $newStatus = strtoupper($data['shipping_status'] ?? ($data['status'] ?? self::STATUS_PENDING));
        $oldStatus = $oldShipment ? strtoupper($oldShipment['shipping_status'] ?? ($oldShipment['status'] ?? self::STATUS_PENDING)) : self::STATUS_PENDING;

        if (!$id && in_array($newStatus, [self::STATUS_SHIPPED, self::STATUS_DELIVERED], true)) {
            $this->logStockChange($shipmentId, self::STATUS_PENDING, $newStatus, $userId);
        } elseif ($id && $oldStatus !== $newStatus) {
            $this->logStockChange($shipmentId, $oldStatus, $newStatus, $userId);
        }

        $this->db->transComplete();
        return $shipmentId;
    }

    /**
     * Stock mutation tracking when shipment status changes (CI3 Parity)
     */
    public function logStockChange(int $shipmentId, string $oldStatus, string $newStatus, ?int $userId = null): void
    {
        $outStatuses = [self::STATUS_SHIPPED, self::STATUS_DELIVERED];
        $oldIsOut = in_array(strtoupper($oldStatus), $outStatuses, true);
        $newIsOut = in_array(strtoupper($newStatus), $outStatuses, true);

        $items = $this->db->table('kol_shipment_items')->where('shipment_id', $shipmentId)->get()->getResultArray();
        if (empty($items)) {
            return;
        }

        $stockChange = 0;
        $changeType  = '';
        $notes       = '';

        // Goods went OUT (PENDING/PROCESSING/RETURNED -> SHIPPED/DELIVERED)
        if (!$oldIsOut && $newIsOut) {
            $stockChange = -1;
            $changeType  = 'OUT';
            $notes       = 'Pengiriman sampel #' . $shipmentId . ' dikirim';
        }
        // Goods came BACK (SHIPPED/DELIVERED -> PENDING/PROCESSING/RETURNED)
        elseif ($oldIsOut && !$newIsOut) {
            $stockChange = 1;
            $changeType  = 'IN';
            $notes       = (strtoupper($newStatus) === self::STATUS_RETURNED)
                ? 'Return paket pengiriman #' . $shipmentId
                : 'Pembatalan pengiriman sampel #' . $shipmentId;
        }

        if ($stockChange === 0) {
            return;
        }

        $stockHistoryModel = new StockHistoryModel();
        $productModel = new ProductModel();

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
            $qty       = (int)$item['qty'];

            $product = $productModel->find($productId);
            $stockBefore = $product ? (int)($product['stock_qty'] ?? $product['stock'] ?? 0) : 0;
            $actualChange = $stockChange * $qty;
            $stockAfter = max(0, $stockBefore + $actualChange);

            // Update product stock
            $productModel->update($productId, [
                'stock'     => $stockAfter,
                'stock_qty' => $stockAfter,
            ]);

            // Record mutation in stock_history
            $stockHistoryModel->record(
                $productId,
                $variantId,
                $changeType,
                $qty,
                $stockAfter,
                'SHIPMENT',
                $shipmentId,
                $notes,
                $userId ?: 1
            );
        }
    }

    /**
     * Get all shipments for export (CSV / PDF)
     */
    public function getAllForExport(?string $filterStatus = null, ?int $filterCampaign = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_shipments.*,
                campaigns.title as campaign_title,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address
            ')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('kol_shipments.id', 'DESC');

        if (!empty($filterStatus)) {
            $builder->groupStart()
                ->where('kol_shipments.shipping_status', $filterStatus)
                ->orWhere('kol_shipments.status', $filterStatus)
                ->groupEnd();
        }

        if (!empty($filterCampaign)) {
            $builder->where('campaigns.id', $filterCampaign);
        }

        $shipments = $builder->get()->getResultArray();

        // Attach items to each shipment
        foreach ($shipments as &$s) {
            $s['items'] = $this->db->table('kol_shipment_items')
                ->select('kol_shipment_items.*, products.name as product_name, products.sku as product_sku, products.price as product_price, variants.name as variant_name')
                ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
                ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
                ->where('kol_shipment_items.shipment_id', $s['id'])
                ->get()
                ->getResultArray();
        }
        unset($s);

        return $shipments;
    }

    /**
     * Dashboard Mini-Cards Summary Metrics
     */
    public function getSummary(): array
    {
        $total     = $this->countAllResults();
        $pending   = $this->groupStart()->where('shipping_status', self::STATUS_PENDING)->orWhere('status', self::STATUS_PENDING)->groupEnd()->countAllResults();
        $shipped   = $this->groupStart()->where('shipping_status', self::STATUS_SHIPPED)->orWhere('status', self::STATUS_SHIPPED)->groupEnd()->countAllResults();
        $delivered = $this->groupStart()->where('shipping_status', self::STATUS_DELIVERED)->orWhere('status', self::STATUS_DELIVERED)->groupEnd()->countAllResults();
        $returned  = $this->groupStart()->where('shipping_status', self::STATUS_RETURNED)->orWhere('status', self::STATUS_RETURNED)->groupEnd()->countAllResults();

        return [
            'total'     => $total,
            'pending'   => $pending,
            'shipped'   => $shipped,
            'delivered' => $delivered,
            'returned'  => $returned,
        ];
    }
}
