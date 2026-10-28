<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
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

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const STATUS_DRAFT     = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_VERIFIED  = 'VERIFIED';
    public const STATUS_PAID      = 'PAID';
    public const STATUS_REJECTED  = 'REJECTED';

    public const TAX_PPH21 = 'PPh21';
    public const TAX_PPH23 = 'PPh23';
    public const TAX_NONE  = 'NONE';

    /**
     * Generate unique sequential invoice number atomically
     * Format: INV/{YYYY}{MM}/{0001}
     */
    public function generateInvoiceNumber(): string
    {
        $db = $this->db;
        $year = (int) date('Y');
        $month = (int) date('m');

        $db->transStart();

        $seq = $db->table('invoice_sequences')
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->getRowArray();

        if ($seq) {
            $nextNumber = (int)$seq['last_number'] + 1;
            $db->table('invoice_sequences')
                ->where('id', $seq['id'])
                ->update([
                    'last_number' => $nextNumber,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
        } else {
            $nextNumber = 1;
            $db->table('invoice_sequences')->insert([
                'year'        => $year,
                'month'       => $month,
                'last_number' => $nextNumber,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        $formattedSeq = str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        return sprintf('INV/%04d%02d/%s', $year, $month, $formattedSeq);
    }

    /**
     * Calculate Tax & Grand Total (Net Payout)
     */
    public static function calculateTax(float $subtotal, string $taxType, bool $hasNpwp = true): array
    {
        $rate = 0.0000;
        if ($taxType === self::TAX_PPH21) {
            // PPh 21: 2.5% jika ber-NPWP, 3% jika non-NPWP
            $rate = $hasNpwp ? 0.0250 : 0.0300;
        } elseif ($taxType === self::TAX_PPH23) {
            // PPh 23: 2%
            $rate = 0.0200;
        }

        $taxAmount = round($subtotal * $rate, 2);
        // Grand total adalah nilai bersih yang ditransfer ke KOL
        $grandTotal = max(0, $subtotal - $taxAmount);

        return [
            'tax_type'    => $taxType,
            'tax_rate'    => $rate,
            'tax_amount'  => $taxAmount,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Summary metrics for Finance Hub
     */
    public function getSummaryMetrics(): array
    {
        $db = $this->db;
        $rows = $db->table($this->table)->select('status, SUM(grand_total) as total_val, COUNT(id) as total_count')->groupBy('status')->get()->getResultArray();

        $res = [
            'total_invoices'      => 0,
            'total_liability'     => 0.0,
            'total_paid'          => 0.0,
            'count_pending'       => 0,
            'val_pending'         => 0.0,
            'count_verified'      => 0,
            'val_verified'        => 0.0,
            'count_paid'          => 0,
        ];

        foreach ($rows as $r) {
            $st = strtoupper(trim((string)$r['status']));
            $cnt = (int)$r['total_count'];
            $val = (float)$r['total_val'];

            $res['total_invoices'] += $cnt;
            $res['total_liability'] += $val;

            if ($st === self::STATUS_SUBMITTED || $st === self::STATUS_DRAFT) {
                $res['count_pending'] += $cnt;
                $res['val_pending'] += $val;
            } elseif ($st === self::STATUS_VERIFIED) {
                $res['count_verified'] += $cnt;
                $res['val_verified'] += $val;
            } elseif ($st === self::STATUS_PAID) {
                $res['count_paid'] += $cnt;
                $res['total_paid'] += $val;
            }
        }

        return $res;
    }

    /**
     * DataTables query with relational joins
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'invoices.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                invoices.*,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.npwp,
                users.avatar as kol_avatar,
                verifier.name as verifier_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users verifier', 'verifier.id = invoices.verified_by', 'left');

        if (!empty($filters['status'])) {
            $builder->where('invoices.status', $filters['status']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kols.id', (int)$filters['kol_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('invoices.invoice_number', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
                ->orLike('kols.bank_name', $search)
                ->orLike('kols.bank_account', $search)
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
     * Get single invoice details with payments history
     */
    public function getInvoiceDetails(int $id): ?array
    {
        $invoice = $this->db->table($this->table)
            ->select('
                invoices.*,
                campaign_kols.campaign_id,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.nik as kol_nik,
                kols.npwp as kol_npwp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                users.avatar as kol_avatar,
                verifier.name as verifier_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users verifier', 'verifier.id = invoices.verified_by', 'left')
            ->where('invoices.id', $id)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            return null;
        }

        // Ambil riwayat pembayaran
        $invoice['payments'] = $this->db->table('payments')
            ->select('payments.*, users.name as payer_name')
            ->join('users', 'users.id = payments.paid_by', 'left')
            ->where('invoice_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $invoice;
    }
}
