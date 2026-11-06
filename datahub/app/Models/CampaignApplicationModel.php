<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignApplicationModel extends Model
{
    protected $table            = 'campaign_applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id',
        'kol_id',
        'pitch_notes',
        'proposed_rate',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'campaign_id' => 'required|is_natural_no_zero',
        'kol_id'      => 'required|is_natural_no_zero',
        'status'      => 'in_list[APPLIED,ACCEPTED,REJECTED]',
    ];

    /**
     * Get all applications submitted by a specific KOL
     */
    public function getApplicationsByKol(int $kolId): array
    {
        return $this->select('
                campaign_applications.*,
                c.title as campaign_title,
                c.status as campaign_status,
                c.start_date,
                c.end_date,
                c.budget_total,
                c.budget_total as budget,
                u.name as reviewer_name
            ')
            ->join('campaigns c', 'c.id = campaign_applications.campaign_id')
            ->join('users u', 'u.id = campaign_applications.reviewed_by', 'left')
            ->where('campaign_applications.kol_id', $kolId)
            ->orderBy('campaign_applications.id', 'DESC')
            ->findAll();
    }

    /**
     * Check if a KOL has already applied to a campaign
     */
    public function hasApplied(int $campaignId, int $kolId): bool
    {
        return $this->where('campaign_id', $campaignId)
            ->where('kol_id', $kolId)
            ->countAllResults() > 0;
    }
}
