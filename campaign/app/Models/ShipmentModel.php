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
        'courier_name',
        'tracking_number',
        'shipping_status',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const STATUS_PENDING   = 'PENDING';
    public const STATUS_SHIPPED   = 'SHIPPED';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_RETURNED  = 'RETURNED';

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
            $builder->where('kol_shipments.shipping_status', $filters['status']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kol_shipments.tracking_number', $search)
                ->orLike('kol_shipments.courier_name', $search)
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
                ->select('kol_shipment_items.*, products.name as product_name, variants.name as variant_name')
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
     * Get single shipment with items and KOL destination address
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
            ->select('kol_shipment_items.*, products.name as product_name, variants.name as variant_name')
            ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
            ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
            ->where('kol_shipment_items.shipment_id', $id)
            ->get()
            ->getResultArray();

        return $shipment;
    }

    /**
     * Save shipment and items atomically
     */
    public function saveShipment(array $data, array $items = [], ?int $userId = null): int
    {
        $this->db->transStart();

        $id = !empty($data['id']) ? (int)$data['id'] : null;
        unset($data['id']);

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

            $stockHistoryModel = new StockHistoryModel();
            $productModel = new ProductModel();

            foreach ($items as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
                $qty = max(1, (int)($item['qty'] ?? 1));

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

                // If status is SHIPPED on creation/update, record stock reduction
                if (($data['shipping_status'] ?? '') === self::STATUS_SHIPPED) {
                    $stockHistoryModel->recordMutation(
                        $productId,
                        $variantId,
                        StockHistoryModel::TYPE_OUT,
                        $qty,
                        'Pemberian sampel KOL (Resi: ' . ($data['tracking_number'] ?? '-') . ')',
                        $userId,
                        'SHIPMENT',
                        $shipmentId
                    );
                }
            }
        }

        $this->db->transComplete();
        return $shipmentId;
    }
}
