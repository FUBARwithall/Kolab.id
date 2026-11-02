<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_id',
        'amount_paid',
        'payment_method',
        'bank_reference',
        'notes',
        'transfer_receipt_file',
        'paid_by',
        'paid_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false;

    /**
     * Record payment transaction atomically & synchronize invoice status
     */
    public function recordPayment(array $data, int $userId): int
    {
        $this->db->transStart();

        $invoiceId = (int)$data['invoice_id'];
        $amount    = (float)($data['amount_paid'] ?? $data['amount'] ?? 0);
        $now       = date('Y-m-d H:i:s');

        $insertData = [
            'invoice_id'            => $invoiceId,
            'amount_paid'           => $amount,
            'payment_method'        => $data['payment_method'] ?? 'BANK_TRANSFER',
            'bank_reference'        => $data['bank_reference'] ?? ('TRX-' . strtoupper(substr(md5(uniqid()), 0, 8))),
            'notes'                 => $data['notes'] ?? null,
            'transfer_receipt_file' => $data['transfer_receipt_file'] ?? $data['payment_proof_file'] ?? null,
            'paid_by'               => $userId,
            'paid_at'               => !empty($data['paid_at']) ? $data['paid_at'] : (!empty($data['payment_date']) ? $data['payment_date'] : $now),
            'created_at'            => $now,
            'updated_at'            => $now,
        ];

        $paymentId = $this->insert($insertData, true);

        // Update status invoice dan campaign_kols berdasarkan total pembayaran terakumulasi
        $this->updateInvoicePaymentStatus($invoiceId);

        $this->db->transComplete();
        return $paymentId;
    }

    /**
     * Recalculate and synchronize invoice & campaign_kols payment status
     */
    public function updateInvoicePaymentStatus(int $invoiceId): void
    {
        if ($invoiceId <= 0) {
            return;
        }

        $inv = $this->db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if (!$inv) {
            return;
        }

        $row = $this->db->table($this->table)
            ->selectSum('amount_paid', 'total_paid')
            ->where('invoice_id', $invoiceId)
            ->get()
            ->getRowArray();

        $totalPaid  = (float)($row['total_paid'] ?? 0);
        $grandTotal = (float)($inv['grand_total'] ?? 0);
        $now        = date('Y-m-d H:i:s');

        if ($totalPaid >= $grandTotal && $grandTotal > 0) {
            $invStatus = InvoiceModel::STATUS_PAID;
            $ckStatus  = 'PAID';
            $paidAt    = $now;
        } elseif ($totalPaid > 0) {
            $invStatus = InvoiceModel::STATUS_PARTIAL;
            $ckStatus  = 'PARTIAL';
            $paidAt    = null;
        } else {
            $invStatus = InvoiceModel::STATUS_VERIFIED;
            $ckStatus  = 'UNPAID';
            $paidAt    = null;
        }

        $updateInvoice = [
            'status'     => $invStatus,
            'updated_at' => $now,
        ];
        if ($paidAt !== null) {
            $updateInvoice['paid_at'] = $paidAt;
        }

        $this->db->table('invoices')->where('id', $invoiceId)->update($updateInvoice);

        if (!empty($inv['campaign_kol_id'])) {
            $this->db->table('campaign_kols')->where('id', $inv['campaign_kol_id'])->update([
                'payment_status' => $ckStatus,
                'updated_at'     => $now,
            ]);
        }
    }

    /**
     * Summary Metrics for Payments Dashboard
     */
    public function getSummaryMetrics(): array
    {
        $today = date('Y-m-d');

        $allRows = $this->db->table($this->table)
            ->select('amount_paid, paid_at, payment_method')
            ->get()
            ->getResultArray();

        $totalDisbursed = 0.0;
        $totalCount     = count($allRows);
        $todayDisbursed = 0.0;
        $todayCount     = 0;
        $bankTransferTotal = 0.0;

        foreach ($allRows as $r) {
            $amt = (float)$r['amount_paid'];
            $totalDisbursed += $amt;

            if (!empty($r['paid_at']) && str_starts_with($r['paid_at'], $today)) {
                $todayDisbursed += $amt;
                $todayCount++;
            }

            if (stripos($r['payment_method'], 'bank') !== false || stripos($r['payment_method'], 'transfer') !== false) {
                $bankTransferTotal += $amt;
            }
        }

        return [
            'total_disbursed'       => $totalDisbursed,
            'total_count'           => $totalCount,
            'today_disbursed'       => $todayDisbursed,
            'today_count'           => $todayCount,
            'bank_transfer_total'   => $bankTransferTotal,
        ];
    }

    /**
     * Outstanding Balance for an Invoice
     */
    public function getInvoiceBalance(int $invoiceId): ?array
    {
        $inv = $this->db->table('invoices')
            ->select('
                invoices.*,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('invoices.id', $invoiceId)
            ->get()
            ->getRowArray();

        if (!$inv) {
            return null;
        }

        $row = $this->db->table($this->table)
            ->selectSum('amount_paid', 'total_paid')
            ->where('invoice_id', $invoiceId)
            ->get()
            ->getRowArray();

        $totalPaid  = (float)($row['total_paid'] ?? 0);
        $grandTotal = (float)$inv['grand_total'];
        $balance    = max(0, $grandTotal - $totalPaid);

        return [
            'invoice_id'     => (int)$inv['id'],
            'invoice_number' => $inv['invoice_number'],
            'campaign_title' => $inv['campaign_title'] ?: ($inv['campaign_name'] ?? 'N/A'),
            'kol_name'       => $inv['kol_name'],
            'bank_name'      => $inv['bank_name'],
            'bank_account'   => $inv['bank_account'],
            'bank_holder'    => $inv['bank_holder'],
            'subtotal'       => (float)$inv['subtotal'],
            'tax_amount'     => (float)$inv['tax_amount'],
            'grand_total'    => $grandTotal,
            'total_paid'     => $totalPaid,
            'balance'        => $balance,
        ];
    }

    /**
     * DataTables Query with Relational Joins
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'payments.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                payments.*,
                payments.amount_paid as amount,
                payments.paid_at as payment_date,
                payments.transfer_receipt_file as payment_proof_file,
                invoices.invoice_number,
                invoices.invoice_number as invoices_invoice_no,
                invoices.grand_total as invoice_grand_total,
                invoices.status as invoice_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                payer.name as payer_name,
                payer.name as aauth_users_full_name
            ')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users payer', 'payer.id = payments.paid_by', 'left');

        if (!empty($filters['invoice_id'])) {
            $builder->where('payments.invoice_id', (int)$filters['invoice_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['payment_method'])) {
            $builder->where('payments.payment_method', $filters['payment_method']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('payments.bank_reference', $search)
                ->orLike('payments.notes', $search)
                ->orLike('invoices.invoice_number', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('kols.name', $search)
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
     * Get Single Payment Details
     */
    public function getPaymentDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                payments.*,
                payments.amount_paid as amount,
                payments.paid_at as payment_date,
                payments.transfer_receipt_file as payment_proof_file,
                invoices.id as invoice_id,
                invoices.invoice_number,
                invoices.invoice_number as invoices_invoice_no,
                invoices.subtotal as invoice_subtotal,
                invoices.tax_amount as invoice_tax_amount,
                invoices.grand_total as invoice_grand_total,
                invoices.status as invoice_status,
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
                payer.name as payer_name,
                payer.name as aauth_users_full_name,
                payer.email as payer_email
            ')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users payer', 'payer.id = payments.paid_by', 'left')
            ->where('payments.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Export Payments List
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                payments.*,
                payments.amount_paid as amount,
                payments.paid_at as payment_date,
                invoices.invoice_number,
                invoices.invoice_number as invoices_invoice_no,
                invoices.grand_total as invoice_grand_total,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                payer.name as payer_name
            ')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users payer', 'payer.id = payments.paid_by', 'left')
            ->orderBy('payments.id', 'DESC');

        if (!empty($filters['invoice_id'])) {
            $builder->where('payments.invoice_id', (int)$filters['invoice_id']);
        }
        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * CI3 Model Compatibility Helper: store
     */
    public function store(array|object $data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (isset($data['amount']) && !isset($data['amount_paid'])) {
            $data['amount_paid'] = $data['amount'];
        }
        if (isset($data['payment_date']) && !isset($data['paid_at'])) {
            $data['paid_at'] = $data['payment_date'];
        }
        if (isset($data['payment_proof_file']) && !isset($data['transfer_receipt_file'])) {
            $data['transfer_receipt_file'] = $data['payment_proof_file'];
        }
        if (isset($data['created_by']) && !isset($data['paid_by'])) {
            $data['paid_by'] = $data['created_by'];
        }
        if (empty($data['paid_by'])) {
            $defaultUid = 1;
            if (session_status() === PHP_SESSION_ACTIVE && function_exists('session')) {
                try {
                    $defaultUid = session('user_id') ?? 1;
                } catch (\Throwable $e) {
                    $defaultUid = 1;
                }
            }
            $data['paid_by'] = $defaultUid;
        }
        if (empty($data['paid_at'])) {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }
        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        unset($data['amount'], $data['payment_date'], $data['payment_proof_file'], $data['created_by']);

        $id = $this->insert($data, true);
        if ($id && !empty($data['invoice_id'])) {
            $this->updateInvoicePaymentStatus((int)$data['invoice_id']);
        }
        return $id;
    }

    /**
     * CI3 Model Compatibility Helper: change
     */
    public function change(int $id, array|object $data): bool
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (isset($data['amount']) && !isset($data['amount_paid'])) {
            $data['amount_paid'] = $data['amount'];
        }
        if (isset($data['payment_date']) && !isset($data['paid_at'])) {
            $data['paid_at'] = $data['payment_date'];
        }
        if (isset($data['payment_proof_file']) && !isset($data['transfer_receipt_file'])) {
            $data['transfer_receipt_file'] = $data['payment_proof_file'];
        }
        if (isset($data['created_by']) && !isset($data['paid_by'])) {
            $data['paid_by'] = $data['created_by'];
        }
        unset($data['amount'], $data['payment_date'], $data['payment_proof_file'], $data['created_by']);

        $res = (bool) $this->update($id, $data);
        if ($res) {
            $p = $this->find($id);
            if ($p && !empty($p['invoice_id'])) {
                $this->updateInvoicePaymentStatus((int)$p['invoice_id']);
            }
        }
        return $res;
    }

    /**
     * CI3 Model Compatibility Helper: remove
     */
    public function remove(int $id): bool
    {
        $p = $this->find($id);
        $res = (bool) $this->delete($id);
        if ($res && $p && !empty($p['invoice_id'])) {
            $this->updateInvoicePaymentStatus((int)$p['invoice_id']);
        }
        return $res;
    }
}
