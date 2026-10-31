<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignKolVariantModel extends Model
{
    protected $table            = 'campaign_kol_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'variant_id',
        'qty',
        'created_at',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    /**
     * Get KPI Summary Metrics
     */
    public function getSummaryMetrics(?int $campaignId = null): array
    {
        $db = \Config\Database::connect();
        $b = $db->table($this->table)
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_variants.campaign_kol_id', 'left');

        if ($campaignId) {
            $b->where('campaign_kols.campaign_id', $campaignId);
        }

        $row = $b->select('
            COUNT(campaign_kol_variants.id) as total_allocations,
            COUNT(DISTINCT campaign_kol_variants.campaign_kol_id) as unique_deals,
            COUNT(DISTINCT campaign_kol_variants.variant_id) as unique_variants,
            SUM(campaign_kol_variants.qty) as total_qty
        ')->get()->getRowArray();

        return [
            'total_allocations' => (int)($row['total_allocations'] ?? 0),
            'unique_deals'       => (int)($row['unique_deals'] ?? 0),
            'unique_variants'    => (int)($row['unique_variants'] ?? 0),
            'total_qty'          => (int)($row['total_qty'] ?? 0),
        ];
    }

    /**
     * DataTables Server-Side query with joins
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'campaign_kol_variants.id', string $orderDir = 'DESC'): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table)
            ->select('
                campaign_kol_variants.*,
                campaign_kols.campaign_id,
                campaign_kols.status as deal_status,
                campaign_kols.deal_amount,
                campaign_kols.jumlah_vt,
                COALESCE(campaigns.title, campaigns.name) as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                variants.name as variant_name,
                variants.sku as variant_sku,
                products.name as product_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_variants.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('variants', 'variants.id = campaign_kol_variants.variant_id', 'left')
            ->join('products', 'products.id = variants.product_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['campaign_kol_id'])) {
            $builder->where('campaign_kol_variants.campaign_kol_id', (int)$filters['campaign_kol_id']);
        }

        if (!empty($filters['variant_id'])) {
            $builder->where('campaign_kol_variants.variant_id', (int)$filters['variant_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('kols.name', $search)
                ->orLike('variants.name', $search)
                ->orLike('variants.sku', $search)
                ->orLike('products.name', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get single record with detailed relationships
     */
    public function getWithDetails(int $id): ?array
    {
        $db = \Config\Database::connect();
        return $db->table($this->table)
            ->select('
                campaign_kol_variants.*,
                campaign_kols.campaign_id,
                campaign_kols.status as deal_status,
                campaign_kols.deal_amount,
                campaign_kols.jumlah_vt,
                COALESCE(campaigns.title, campaigns.name) as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                variants.name as variant_name,
                variants.sku as variant_sku,
                products.name as product_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_variants.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('variants', 'variants.id = campaign_kol_variants.variant_id', 'left')
            ->join('products', 'products.id = variants.product_id', 'left')
            ->where('campaign_kol_variants.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get records for CSV export
     */
    public function getAllForExport(array $filters = []): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table)
            ->select('
                campaign_kol_variants.id,
                COALESCE(campaigns.title, campaigns.name) as campaign_name,
                kols.name as kol_name,
                variants.name as variant_name,
                variants.sku as variant_sku,
                campaign_kol_variants.qty,
                campaign_kols.status as deal_status,
                campaign_kol_variants.created_at
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_variants.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('variants', 'variants.id = campaign_kol_variants.variant_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['variant_id'])) {
            $builder->where('campaign_kol_variants.variant_id', (int)$filters['variant_id']);
        }

        return $builder->orderBy('campaign_kol_variants.id', 'DESC')->get()->getResultArray();
    }
}
