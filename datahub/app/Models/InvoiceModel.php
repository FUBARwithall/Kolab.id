<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'invoice_number',
        'subtotal',
        'tax_type',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'status',
        'invoice_file',
        'bupot_file',
        'verified_by',
        'verified_at',
        'paid_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get invoices associated with a specific KOL
     */
    public function getInvoicesByKol(int $kolId): array
    {
        return $this->select('
                invoices.*,
                ck.deal_amount,
                c.id as campaign_id,
                c.title as campaign_title
            ')
            ->join('campaign_kols ck', 'ck.id = invoices.campaign_kol_id')
            ->join('campaigns c', 'c.id = ck.campaign_id')
            ->where('ck.kol_id', $kolId)
            ->orderBy('invoices.id', 'DESC')
            ->findAll();
    }
}

