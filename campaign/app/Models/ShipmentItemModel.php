<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentItemModel extends Model
{
    protected $table            = 'kol_shipment_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'shipment_id',
        'product_id',
        'variant_id',
        'qty',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * DataTables query joining shipments, products, variants, and KOL recipients
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'kol_shipment_items.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_shipment_items.*,
                products.name as product_name,
                products.sku as product_sku,
                products.price as product_price,
                products.category as product_category,
                variants.name as variant_name,
                variants.sku as variant_sku,
                kol_shipments.tracking_number,
                kol_shipments.tracking_no,
                kol_shipments.courier_name,
                kol_shipments.courier,
                kol_shipments.shipping_status,
                kol_shipments.status,
                kol_shipments.recipient_name,
                kols.name as kol_name,
                campaigns.title as campaign_title
            ')
            ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
            ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
            ->join('kol_shipments', 'kol_shipments.id = kol_shipment_items.shipment_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left');

        if (!empty($filters['shipment_id'])) {
            $builder->where('kol_shipment_items.shipment_id', (int)$filters['shipment_id']);
        }

        if (!empty($filters['product_id'])) {
            $builder->where('kol_shipment_items.product_id', (int)$filters['product_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.sku', $search)
                ->orLike('kol_shipments.tracking_number', $search)
                ->orLike('kol_shipments.tracking_no', $search)
                ->orLike('kol_shipments.recipient_name', $search)
                ->orLike('kols.name', $search)
                ->orLike('campaigns.title', $search)
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
     * Get single detail with complete product and shipment relations
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                kol_shipment_items.*,
                products.name as product_name,
                products.sku as product_sku,
                products.price as product_price,
                products.category as product_category,
                products.description as product_description,
                variants.name as variant_name,
                variants.sku as variant_sku,
                variants.additional_price as variant_additional_price,
                kol_shipments.tracking_number,
                kol_shipments.tracking_no,
                kol_shipments.courier_name,
                kol_shipments.courier,
                kol_shipments.shipping_status,
                kol_shipments.status,
                kol_shipments.recipient_name,
                kol_shipments.recipient_phone,
                kol_shipments.shipping_address,
                kol_shipments.shipped_at,
                kol_shipments.delivered_at,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title
            ')
            ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
            ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
            ->join('kol_shipments', 'kol_shipments.id = kol_shipment_items.shipment_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->where('kol_shipment_items.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all items for export
     */
    public function getAllForExport(?int $filterShipment = null, ?int $filterProduct = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                kol_shipment_items.*,
                products.name as product_name,
                products.sku as product_sku,
                products.price as product_price,
                products.category as product_category,
                variants.name as variant_name,
                kol_shipments.tracking_number,
                kol_shipments.tracking_no,
                kol_shipments.courier_name,
                kol_shipments.courier,
                kol_shipments.shipping_status,
                kol_shipments.status,
                kol_shipments.recipient_name,
                kols.name as kol_name,
                campaigns.title as campaign_title
            ')
            ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
            ->join('variants', 'variants.id = kol_shipment_items.variant_id', 'left')
            ->join('kol_shipments', 'kol_shipments.id = kol_shipment_items.shipment_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->orderBy('kol_shipment_items.id', 'DESC');

        if (!empty($filterShipment)) {
            $builder->where('kol_shipment_items.shipment_id', $filterShipment);
        }

        if (!empty($filterProduct)) {
            $builder->where('kol_shipment_items.product_id', $filterProduct);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Dashboard Summary Metrics
     */
    public function getSummary(): array
    {
        $totalRows   = $this->countAllResults();
        $totalQty    = (int)($this->selectSum('qty')->get()->getRowArray()['qty'] ?? 0);
        $uniqueProds = $this->db->table($this->table)->select('COUNT(DISTINCT product_id) as total')->get()->getRowArray()['total'] ?? 0;
        $uniqueShips = $this->db->table($this->table)->select('COUNT(DISTINCT shipment_id) as total')->get()->getRowArray()['total'] ?? 0;

        $valuationRow = $this->db->table($this->table)
            ->select('SUM(kol_shipment_items.qty * products.price) as total_val')
            ->join('products', 'products.id = kol_shipment_items.product_id', 'inner')
            ->get()->getRowArray();
        $totalValuation = (float)($valuationRow['total_val'] ?? 0);

        return [
            'total_rows'             => $totalRows,
            'total_items'            => $totalRows,
            'total_qty'              => $totalQty,
            'total_valuation'        => $totalValuation,
            'unique_products'        => (int)$uniqueProds,
            'total_products_sent'    => (int)$uniqueProds,
            'unique_shipments'       => (int)$uniqueShips,
            'total_shipments_served' => (int)$uniqueShips,
        ];
    }
}
