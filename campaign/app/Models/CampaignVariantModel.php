<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignVariantModel extends Model
{
    protected $table            = 'campaign_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id',
        'variant_id',
        'notes',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * DataTables Server-Side AJAX Query
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'campaign_variants.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                campaign_variants.*,
                COALESCE(campaigns.name, campaigns.title) as campaign_name,
                campaigns.title as campaign_title,
                campaigns.status as campaign_status,
                campaigns.start_date as campaign_start_date,
                campaigns.end_date as campaign_end_date,
                variants.name as variant_name,
                variants.sku as variant_sku,
                variants.additional_price as variant_additional_price,
                variants.stock_qty as variant_stock_qty,
                products.id as product_id,
                products.name as product_name,
                products.sku as product_sku,
                products.unit_price as product_unit_price,
                products.image_url as product_image_url
            ')
            ->join('campaigns', 'campaigns.id = campaign_variants.campaign_id', 'left')
            ->join('variants', 'variants.id = campaign_variants.variant_id', 'left')
            ->join('products', 'products.id = variants.product_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_variants.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['product_id'])) {
            $builder->where('variants.product_id', (int)$filters['product_id']);
        }

        if (!empty($filters['variant_id'])) {
            $builder->where('campaign_variants.variant_id', (int)$filters['variant_id']);
        }

        // Total count before global search
        $totalRecords = (clone $builder)->countAllResults();

        // Global search filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('variants.name', $search)
                ->orLike('variants.sku', $search)
                ->orLike('products.name', $search)
                ->orLike('campaign_variants.notes', $search)
                ->groupEnd();
        }

        // Filtered count
        $filteredRecords = (clone $builder)->countAllResults();

        // Order & Pagination
        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();

        return [
            'total'    => $totalRecords,
            'filtered' => $filteredRecords,
            'data'     => $data,
        ];
    }

    /**
     * Get detail single record with joins
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                campaign_variants.*,
                COALESCE(campaigns.name, campaigns.title) as campaign_name,
                campaigns.title as campaign_title,
                campaigns.slug as campaign_slug,
                campaigns.status as campaign_status,
                campaigns.budget_total as campaign_budget,
                campaigns.start_date as campaign_start_date,
                campaigns.end_date as campaign_end_date,
                campaigns.description as campaign_description,
                variants.name as variant_name,
                variants.sku as variant_sku,
                variants.additional_price as variant_additional_price,
                variants.stock_qty as variant_stock_qty,
                products.id as product_id,
                products.name as product_name,
                products.sku as product_sku,
                products.unit_price as product_unit_price,
                products.description as product_description,
                products.image_url as product_image_url
            ')
            ->join('campaigns', 'campaigns.id = campaign_variants.campaign_id', 'left')
            ->join('variants', 'variants.id = campaign_variants.variant_id', 'left')
            ->join('products', 'products.id = variants.product_id', 'left')
            ->where('campaign_variants.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Anti-duplicate validation: a variant should only be linked once per campaign
     */
    public function checkExists(int $campaignId, int $variantId, ?int $excludeId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('campaign_id', $campaignId)
            ->where('variant_id', $variantId);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get all rows for CSV Export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                campaign_variants.id,
                COALESCE(campaigns.name, campaigns.title) as campaign_name,
                campaigns.status as campaign_status,
                products.name as product_name,
                products.sku as product_sku,
                variants.name as variant_name,
                variants.sku as variant_sku,
                variants.additional_price as variant_additional_price,
                variants.stock_qty as variant_stock_qty,
                campaign_variants.notes,
                campaign_variants.created_at
            ')
            ->join('campaigns', 'campaigns.id = campaign_variants.campaign_id', 'left')
            ->join('variants', 'variants.id = campaign_variants.variant_id', 'left')
            ->join('products', 'products.id = variants.product_id', 'left')
            ->orderBy('campaign_variants.id', 'DESC');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_variants.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['product_id'])) {
            $builder->where('variants.product_id', (int)$filters['product_id']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Summary KPI Statistics
     */
    public function getStats(): array
    {
        $totalRelations = $this->countAllResults();

        $campaignsCount = $this->db->table($this->table)
            ->select('COUNT(DISTINCT campaign_id) as total')
            ->get()
            ->getRowArray()['total'] ?? 0;

        $variantsCount = $this->db->table($this->table)
            ->select('COUNT(DISTINCT variant_id) as total')
            ->get()
            ->getRowArray()['total'] ?? 0;

        $avgPerCampaign = ($campaignsCount > 0) ? round($totalRelations / $campaignsCount, 1) : 0;

        return [
            'total_relations'   => (int)$totalRelations,
            'total_campaigns'   => (int)$campaignsCount,
            'total_variants'    => (int)$variantsCount,
            'avg_per_campaign'  => (float)$avgPerCampaign,
        ];
    }
}
