<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignModel extends Model
{
    protected $table            = 'campaigns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'slug',
        'brand_id',
        'budget_total',
        'target_views',
        'start_date',
        'end_date',
        'status',
        'is_open_registration',
        'description',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get active campaigns open for KOL applications
     */
    public function getOpenCampaigns(?int $kolId = null): array
    {
        $builder = $this->select('
                campaigns.*,
                campaigns.budget_total as display_budget,
                (SELECT COUNT(*) FROM campaign_applications WHERE campaign_id = campaigns.id) as total_applicants
            ')
            ->where('campaigns.status', 'ACTIVE')
            ->where('campaigns.deleted_at IS NULL')
            ->orderBy('campaigns.id', 'DESC');

        $campaigns = $builder->findAll();

        if ($kolId && !empty($campaigns)) {
            // Check which campaigns the KOL has already applied to
            $appliedCampaignIds = $this->db->table('campaign_applications')
                ->select('campaign_id, status as application_status')
                ->where('kol_id', $kolId)
                ->get()
                ->getResultArray();

            $appliedMap = [];
            foreach ($appliedCampaignIds as $item) {
                $appliedMap[$item['campaign_id']] = $item['application_status'];
            }

            foreach ($campaigns as &$camp) {
                $camp['has_applied'] = isset($appliedMap[$camp['id']]);
                $camp['application_status'] = $appliedMap[$camp['id']] ?? null;
            }
        }

        return $campaigns;
    }
}
