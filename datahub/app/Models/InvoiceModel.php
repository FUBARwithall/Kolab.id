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
        'rejection_reason',
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
                ck.campaign_id,
                ck.deal_amount,
                c.title as campaign_title,
                k.name as kol_name,
                k.bank_name,
                k.bank_account,
                k.bank_holder,
                k.npwp
            ')
            ->join('campaign_kols ck', 'ck.id = invoices.campaign_kol_id')
            ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
            ->join('kols k', 'k.id = ck.kol_id', 'left')
            ->where('ck.kol_id', $kolId)
            ->orderBy('invoices.id', 'DESC')
            ->findAll();
    }

    /**
     * Get active/assigned deals eligible for new invoice submission
     */
    public function getBillableDealsByKol(int $kolId): array
    {
        $db  = \Config\Database::connect();
        $sql = "
            SELECT 
                ck.id as campaign_kol_id,
                ck.deal_amount,
                ck.status as deal_status,
                c.id as campaign_id,
                c.title as campaign_title,
                k.name as kol_name,
                k.bank_name,
                k.bank_account,
                k.bank_holder,
                k.npwp
            FROM campaign_kols ck
            JOIN campaigns c ON c.id = ck.campaign_id
            JOIN kols k ON k.id = ck.kol_id
            WHERE ck.kol_id = ?
              AND ck.deal_amount > 0
              AND NOT EXISTS (
                  SELECT 1 FROM invoices i 
                  WHERE i.campaign_kol_id = ck.id 
                    AND i.status IN ('SUBMITTED', 'VERIFIED', 'PAID')
              )
            ORDER BY ck.id DESC
        ";
        return $db->query($sql, [$kolId])->getResultArray();
    }

    /**
     * Get single deal detail for tax calculation preview
     */
    public function getDealDetail(int $campaignKolId, int $kolId): ?array
    {
        $db  = \Config\Database::connect();
        $sql = "
            SELECT 
                ck.id as campaign_kol_id,
                ck.deal_amount,
                c.id as campaign_id,
                c.title as campaign_title,
                k.id as kol_id,
                k.name as kol_name,
                k.bank_name,
                k.bank_account,
                k.bank_holder,
                k.npwp
            FROM campaign_kols ck
            JOIN campaigns c ON c.id = ck.campaign_id
            JOIN kols k ON k.id = ck.kol_id
            WHERE ck.id = ? AND ck.kol_id = ?
            LIMIT 1
        ";
        $row = $db->query($sql, [$campaignKolId, $kolId])->getRowArray();
        return $row ?: null;
    }

    /**
     * Get single invoice detail with ownership verification
     */
    public function getInvoiceDetail(int $id, int $kolId): ?array
    {
        return $this->select('
                invoices.*,
                ck.campaign_id,
                ck.kol_id,
                ck.deal_amount,
                c.title as campaign_title,
                k.name as kol_name,
                k.phone as kol_phone,
                k.email as kol_email,
                k.bank_name,
                k.bank_account,
                k.bank_holder,
                k.npwp,
                u.name as verified_by_name
            ')
            ->join('campaign_kols ck', 'ck.id = invoices.campaign_kol_id')
            ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
            ->join('kols k', 'k.id = ck.kol_id', 'left')
            ->join('users u', 'u.id = invoices.verified_by', 'left')
            ->where('invoices.id', $id)
            ->where('ck.kol_id', $kolId)
            ->first();
    }

    /**
     * Get audit trail history of an invoice
     */
    public function getInvoiceHistories(int $invoiceId): array
    {
        $db = \Config\Database::connect();
        return $db->table('invoice_histories ih')
            ->select('ih.*, COALESCE(u.name, "Sistem") as actor_name')
            ->join('users u', 'u.id = ih.changed_by', 'left')
            ->where('ih.invoice_id', $invoiceId)
            ->orderBy('ih.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Generate next atomic monotonic invoice number
     */
    public function generateInvoiceNumber(): string
    {
        $ym    = date('Ym');
        $year  = (int) date('Y');
        $month = (int) date('n');

        $db = \Config\Database::connect();
        $db->transStart();

        $row = $db->table('invoice_sequences')
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->getRowArray();

        if ($row) {
            $next = ((int) $row['last_number']) + 1;
            $db->table('invoice_sequences')
                ->where('id', $row['id'])
                ->update(['last_number' => $next, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $next = 1;
            $db->table('invoice_sequences')->insert([
                'year'        => $year,
                'month'       => $month,
                'last_number' => 1,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        return sprintf('INV/%s/%04d', $ym, $next);
    }
}
