<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table            = 'campaign_kol_contracts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'template_id',
        'contract_number',
        'clauses_rendered',
        'signed_at',
        'signed_ip',
        'signature_image',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get contracts associated with a specific KOL
     */
    public function getContractsByKol(int $kolId): array
    {
        return $this->select('
                campaign_kol_contracts.*,
                ck.deal_amount,
                ck.total_vt,
                ck.contract_status,
                c.id as campaign_id,
                c.title as campaign_title
            ')
            ->join('campaign_kols ck', 'ck.id = campaign_kol_contracts.campaign_kol_id')
            ->join('campaigns c', 'c.id = ck.campaign_id')
            ->where('ck.kol_id', $kolId)
            ->orderBy('campaign_kol_contracts.id', 'DESC')
            ->findAll();
    }
}

