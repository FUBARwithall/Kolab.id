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
        'rejection_reason',
        'due_date',
        'bupot_no',
        'invoice_file',
        'bupot_file',
        'verified_by',
        'created_by',
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
    public const STATUS_APPROVED  = 'APPROVED';
    public const STATUS_PARTIAL   = 'PARTIAL';
    public const STATUS_PAID      = 'PAID';
    public const STATUS_REJECTED  = 'REJECTED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_UNPAID    = 'UNPAID';

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
     * Generate legacy format invoice number (INV-YYYYMMDD-XXXX)
     */
    public function generateLegacyInvoiceNumber(): string
    {
        $today = date('Ymd');
        $prefix = 'INV-' . $today . '-';

        $lastRow = $this->db->table($this->table)
            ->like('invoice_number', 'INV-' . $today)
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();

        if ($lastRow && !empty($lastRow['invoice_number'])) {
            $parts = explode('-', $lastRow['invoice_number']);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . sprintf('%04d', $nextNumber);
    }

    /**
     * Calculate Tax & Grand Total (Net Payout)
     */
    public static function calculateTax(float $subtotal, string $taxType = self::TAX_PPH21, bool $hasNpwp = true): array
    {
        $rate = 0.0000;
        $upperType = strtoupper(trim($taxType));

        if ($upperType === 'PPH21' || $upperType === 'PPH 21') {
            // PPh 21: 2.5% jika ber-NPWP, 3% jika non-NPWP
            $rate = $hasNpwp ? 0.0250 : 0.0300;
            $normalizedType = self::TAX_PPH21;
        } elseif ($upperType === 'PPH23' || $upperType === 'PPH 23') {
            // PPh 23: 2.0%
            $rate = 0.0200;
            $normalizedType = self::TAX_PPH23;
        } else {
            $normalizedType = self::TAX_NONE;
        }

        $taxAmount = round($subtotal * $rate, 2);
        $grandTotal = max(0, $subtotal - $taxAmount);

        return [
            'tax_type'    => $normalizedType,
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
        $rows = $this->db->table($this->table)
            ->select('status, SUM(grand_total) as total_val, COUNT(id) as total_count')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $res = [
            'total_invoices'  => 0,
            'total_liability' => 0.0,
            'total_paid'      => 0.0,
            'count_pending'   => 0,
            'val_pending'     => 0.0,
            'count_verified'  => 0,
            'val_verified'    => 0.0,
            'count_paid'      => 0,
            'count_rejected'  => 0,
        ];

        foreach ($rows as $r) {
            $st  = strtoupper(trim((string)$r['status']));
            $cnt = (int)$r['total_count'];
            $val = (float)$r['total_val'];

            $res['total_invoices'] += $cnt;
            $res['total_liability'] += $val;

            if (in_array($st, [self::STATUS_SUBMITTED, self::STATUS_DRAFT, self::STATUS_UNPAID], true)) {
                $res['count_pending'] += $cnt;
                $res['val_pending'] += $val;
            } elseif (in_array($st, [self::STATUS_VERIFIED, self::STATUS_APPROVED, self::STATUS_PARTIAL], true)) {
                $res['count_verified'] += $cnt;
                $res['val_verified'] += $val;
            } elseif ($st === self::STATUS_PAID) {
                $res['count_paid'] += $cnt;
                $res['total_paid'] += $val;
            } elseif ($st === self::STATUS_REJECTED) {
                $res['count_rejected'] += $cnt;
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
                invoices.invoice_number as invoice_no,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.npwp,
                kols.npwp as npwp_number,
                users.avatar as kol_avatar,
                verifier.name as verifier_name,
                creator.name as creator_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users verifier', 'verifier.id = invoices.verified_by', 'left')
            ->join('users creator', 'creator.id = invoices.created_by', 'left');

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
                ->orLike('campaigns.name', $search)
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
                invoices.invoice_number as invoice_no,
                campaign_kols.campaign_id,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.nik as kol_nik,
                kols.npwp as kol_npwp,
                kols.npwp as npwp_number,
                kols.city as kol_city,
                kols.address as kol_address,
                kols.bank_name,
                kols.bank_account,
                kols.bank_account as bank_account_number,
                kols.bank_holder,
                kols.bank_holder as bank_account_name,
                users.avatar as kol_avatar,
                verifier.name as verifier_name,
                creator.name as creator_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('users verifier', 'verifier.id = invoices.verified_by', 'left')
            ->join('users creator', 'creator.id = invoices.created_by', 'left')
            ->where('invoices.id', $id)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            return null;
        }

        // Ambil riwayat pembayaran
        $payments = $this->db->table('payments')
            ->select('payments.*, payments.amount_paid as amount, users.name as payer_name')
            ->join('users', 'users.id = payments.paid_by', 'left')
            ->where('invoice_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $totalPaid = 0.0;
        foreach ($payments as $p) {
            $totalPaid += (float) ($p['amount_paid'] ?? 0);
        }

        $invoice['payments']    = $payments;
        $invoice['amount_paid'] = $totalPaid;
        $invoice['total_paid']  = $totalPaid;
        $invoice['balance_due'] = max(0, (float)$invoice['grand_total'] - $totalPaid);

        return $invoice;
    }

    /**
     * Get all invoices for export
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                invoices.*,
                invoices.invoice_number as invoice_no,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.npwp as npwp_number,
                COALESCE((SELECT SUM(amount_paid) FROM payments WHERE payments.invoice_id = invoices.id), 0) as total_paid
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('invoices.id', 'DESC');

        if (!empty($filters['status'])) {
            $builder->where('invoices.status', $filters['status']);
        }
        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get all invoices for a specific KOL (Creator Portal)
     */
    public function getByKol(int $kolId): array
    {
        return $this->db->table($this->table)
            ->select('
                invoices.*,
                invoices.invoice_number as invoice_no,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.bank_account as bank_account_number,
                kols.bank_holder as bank_account_name,
                campaign_kols.deal_amount,
                campaign_kol_contracts.id as contract_id,
                campaign_kol_contracts.contract_no,
                COALESCE((SELECT SUM(amount_paid) FROM payments WHERE payments.invoice_id = invoices.id), 0) as total_paid
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('campaign_kol_contracts', 'campaign_kol_contracts.campaign_kol_id = campaign_kols.id', 'left')
            ->where('campaign_kols.kol_id', $kolId)
            ->whereNotIn('invoices.status', [self::STATUS_CANCELLED])
            ->orderBy('invoices.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * CI3 Model Compatibility Helper: store
     */
    public function store(array $data)
    {
        if (isset($data['invoice_no']) && !isset($data['invoice_number'])) {
            $data['invoice_number'] = $data['invoice_no'];
        }
        unset($data['invoice_no']);
        return $this->insert($data, true);
    }

    /**
     * CI3 Model Compatibility Helper: change
     */
    public function change(int $id, array $data): bool
    {
        if (isset($data['invoice_no']) && !isset($data['invoice_number'])) {
            $data['invoice_number'] = $data['invoice_no'];
        }
        unset($data['invoice_no']);
        return (bool) $this->update($id, $data);
    }

    /**
     * CI3 Model Compatibility Helper: remove
     */
    public function remove(int $id): bool
    {
        return (bool) $this->delete($id);
    }
}
