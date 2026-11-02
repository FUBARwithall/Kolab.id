<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceHubModel extends Model
{
    protected $table            = 'campaign_kols';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['campaign_id', 'kol_id', 'deal_amount', 'total_vt', 'status', 'contract_status'];

    /**
     * Get aggregate KPI metrics for Finance Workspace
     */
    public function getFinanceMetrics(?int $campaignId = null): array
    {
        $db = \Config\Database::connect();

        // 1. Total Deals & Total Committed Budget
        $dealBuilder = $db->table('campaign_kols');
        if ($campaignId) {
            $dealBuilder->where('campaign_id', $campaignId);
        }
        $dealRes = $dealBuilder->select('COUNT(*) as total_deals, COALESCE(SUM(deal_amount), 0) as total_deal_amount')->get()->getRowArray();

        // 2. Invoices metrics
        $invBuilder = $db->table('invoices')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->whereNotIn('invoices.status', ['CANCELLED', 'REJECTED']);
        if ($campaignId) {
            $invBuilder->where('campaign_kols.campaign_id', $campaignId);
        }
        $invRes = $invBuilder->select('
            COUNT(*) as total_invoices,
            COALESCE(SUM(invoices.subtotal), 0) as total_subtotal,
            COALESCE(SUM(invoices.tax_amount), 0) as total_tax,
            COALESCE(SUM(invoices.grand_total), 0) as total_invoiced
        ')->get()->getRowArray();

        // Invoices status counts
        $statusCountsBuilder = $db->table('invoices')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner');
        if ($campaignId) {
            $statusCountsBuilder->where('campaign_kols.campaign_id', $campaignId);
        }
        $allInvs = $statusCountsBuilder->select('invoices.status')->get()->getResultArray();
        $countSubmitted = 0;
        $countApproved  = 0;
        $countPaid      = 0;
        foreach ($allInvs as $iv) {
            $st = strtoupper($iv['status']);
            if ($st === 'SUBMITTED') {
                $countSubmitted++;
            } elseif (in_array($st, ['APPROVED', 'VERIFIED', 'PARTIAL'])) {
                $countApproved++;
            } elseif ($st === 'PAID') {
                $countPaid++;
            }
        }

        // 3. Total Paid / Disbursed
        $payBuilder = $db->table('payments')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'inner')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner');
        if ($campaignId) {
            $payBuilder->where('campaign_kols.campaign_id', $campaignId);
        }
        $payRes = $payBuilder->select('COALESCE(SUM(payments.amount_paid), 0) as total_paid, COUNT(*) as total_transactions')->get()->getRowArray();

        $totalDealAmount = (float)($dealRes['total_deal_amount'] ?? 0);
        $totalPaid       = (float)($payRes['total_paid'] ?? 0);
        $totalInvoiced   = (float)($invRes['total_invoiced'] ?? 0);
        $totalTax        = (float)($invRes['total_tax'] ?? 0);
        $outstanding     = max(0, $totalInvoiced > 0 ? ($totalInvoiced - $totalPaid) : ($totalDealAmount - $totalPaid));

        return [
            'total_deals'        => (int)($dealRes['total_deals'] ?? 0),
            'total_deal_amount'  => $totalDealAmount,
            'total_invoices'     => (int)($invRes['total_invoices'] ?? 0),
            'total_invoiced'     => $totalInvoiced,
            'total_tax'          => $totalTax,
            'total_paid'         => $totalPaid,
            'total_transactions' => (int)($payRes['total_transactions'] ?? 0),
            'outstanding'        => $outstanding,
            'total_unpaid'       => $outstanding,
            'count_submitted'    => $countSubmitted,
            'count_approved'     => $countApproved,
            'count_paid'         => $countPaid,
        ];
    }

    /**
     * Get Deals query with joins matching CI3 index()
     */
    public function getDealsForFinance(?int $campaignId = null, ?string $filter = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('campaign_kols')
            ->select('
                campaign_kols.id as campaign_kol_id,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaign_kols.status as deal_status,
                campaign_kols.contract_status,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.avatar_url as kol_avatar,
                kols.bank_name as kol_bank_name,
                kols.bank_account as kol_bank_account_number,
                kols.bank_holder as kol_bank_account_name,
                kols.npwp as kol_npwp_number,
                kols.nik as kol_ktp_number,
                campaigns.id as campaign_id,
                campaigns.title as campaign_name,
                campaigns.brand_name,
                contracts.id as contract_id,
                COALESCE(contracts.contract_no, contracts.contract_number) as contract_no,
                contracts.contract_file,
                contracts.status as contract_status,
                contracts.signed_at as contract_signed_at,
                invoices.id as invoice_id,
                COALESCE(invoices.invoice_no, invoices.invoice_number) as invoice_no,
                invoices.invoice_number,
                invoices.due_date,
                invoices.status as invoice_status,
                invoices.grand_total,
                invoices.subtotal,
                invoices.tax_amount,
                invoices.tax_type,
                invoices.tax_rate,
                invoices.tax_rate_percent,
                invoices.bupot_no,
                invoices.bupot_file,
                invoices.bupot_uploaded_at,
                invoices.rejection_reason,
                invoices.invoice_file,
                invoices.created_at as invoice_created_at,
                COALESCE(p.total_paid, 0) as total_paid
            ')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('campaign_kol_contracts as contracts', 'contracts.id = (SELECT MAX(c.id) FROM campaign_kol_contracts c WHERE c.campaign_kol_id = campaign_kols.id AND c.status != "CANCELLED")', 'left')
            ->join('invoices', 'invoices.id = (SELECT MAX(iv.id) FROM invoices iv WHERE iv.campaign_kol_id = campaign_kols.id)', 'left')
            ->join('
                (SELECT invoice_id, SUM(amount_paid) as total_paid 
                 FROM payments GROUP BY invoice_id) as p', 'p.invoice_id = invoices.id', 'left');

        if ($campaignId) {
            $builder->where('campaign_kols.campaign_id', $campaignId);
        }

        if (!empty($filter)) {
            $builder->groupStart()
                ->like('kols.name', $filter)
                ->orLike('campaigns.title', $filter)
                ->orLike('campaigns.name', $filter)
                ->orLike('contracts.contract_number', $filter)
                ->orLike('contracts.contract_no', $filter)
                ->orLike('invoices.invoice_number', $filter)
                ->groupEnd();
        }

        $builder->orderBy('campaign_kols.id', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * DataTables Query for Unified Finance Workspace
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'campaign_kols.id', string $orderDir = 'DESC'): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('campaign_kols')
            ->select('
                campaign_kols.id as ck_id,
                campaign_kols.campaign_id,
                campaign_kols.kol_id,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaign_kols.status as deal_status,
                campaign_kols.contract_status,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.npwp,
                kols.nik,
                contracts.id as contract_id,
                COALESCE(contracts.contract_no, contracts.contract_number) as contract_number,
                contracts.signed_at as contract_signed_at,
                contracts.status as contract_status_text,
                invoices.id as invoice_id,
                COALESCE(invoices.invoice_no, invoices.invoice_number) as invoice_number,
                invoices.subtotal as invoice_subtotal,
                invoices.tax_type,
                invoices.tax_rate,
                invoices.tax_rate_percent,
                invoices.tax_amount,
                invoices.grand_total as invoice_grand_total,
                invoices.status as invoice_status,
                invoices.rejection_reason,
                invoices.bupot_no,
                invoices.bupot_file,
                invoices.invoice_file,
                COALESCE(p.total_paid, 0) as total_paid,
                p.payment_method,
                p.bank_reference,
                p.transfer_receipt_file,
                p.paid_at
            ')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('campaign_kol_contracts as contracts', 'contracts.id = (SELECT MAX(c.id) FROM campaign_kol_contracts c WHERE c.campaign_kol_id = campaign_kols.id AND c.status != "CANCELLED")', 'left')
            ->join('invoices', 'invoices.id = (SELECT MAX(iv.id) FROM invoices iv WHERE iv.campaign_kol_id = campaign_kols.id)', 'left')
            ->join('
                (SELECT invoice_id, SUM(amount_paid) as total_paid, MAX(payment_method) as payment_method, MAX(bank_reference) as bank_reference, MAX(transfer_receipt_file) as transfer_receipt_file, MAX(paid_at) as paid_at 
                 FROM payments GROUP BY invoice_id) as p', 'p.invoice_id = invoices.id', 'left');

        // Apply campaign filter
        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'NO_INVOICE':
                    $builder->where('invoices.id IS NULL', null, false);
                    break;
                case 'SUBMITTED':
                    $builder->where('invoices.status', 'SUBMITTED');
                    break;
                case 'VERIFIED':
                case 'APPROVED':
                    $builder->whereIn('invoices.status', ['VERIFIED', 'APPROVED']);
                    break;
                case 'PARTIAL':
                    $builder->where('invoices.status', 'PARTIAL');
                    break;
                case 'PAID':
                    $builder->where('invoices.status', 'PAID');
                    break;
                case 'REJECTED':
                    $builder->where('invoices.status', 'REJECTED');
                    break;
            }
        }

        // Apply search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('kols.name', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('invoices.invoice_number', $search)
                ->orLike('contracts.contract_number', $search)
                ->orLike('kols.bank_name', $search)
                ->orLike('kols.bank_account', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        // Sorting & pagination
        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $db->table('campaign_kols')->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Approve incoming invoice from KOL, with final tax withholding verification
     */
    public function approveInvoice(int $invoiceId, string $taxType = 'NONE', float $taxRatePercent = 0.0, int $adminId = 1): array
    {
        $db = \Config\Database::connect();
        $invoice = $db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice tidak ditemukan.'];
        }

        if (!in_array($invoice['status'], ['SUBMITTED', 'DRAFT'])) {
            return ['success' => false, 'message' => 'Tagihan sudah diproses atau status telah berubah (status saat ini: ' . $invoice['status'] . ').'];
        }

        $subtotal = (float)$invoice['subtotal'];
        if ($subtotal <= 0 && !empty($invoice['grand_total'])) {
            $subtotal = (float)$invoice['grand_total'];
        }

        $taxAmount  = round($subtotal * ($taxRatePercent / 100), 2);
        $grandTotal = max(0, $subtotal - $taxAmount);

        // Standardize tax_type for database enum
        $enumTaxType = match (strtoupper($taxType)) {
            'PPH21', 'PPH 21' => 'PPh21',
            'PPH23', 'PPH 23' => 'PPh23',
            default           => 'NONE'
        };

        $updateData = [
            'status'           => 'APPROVED',
            'tax_type'         => $enumTaxType,
            'tax_rate'         => $taxRatePercent / 100,
            'tax_rate_percent' => $taxRatePercent,
            'tax_amount'       => $taxAmount,
            'grand_total'      => $grandTotal,
            'subtotal'         => $subtotal,
            'verified_by'      => $adminId,
            'approved_by'      => $adminId,
            'verified_at'      => date('Y-m-d H:i:s'),
            'approved_at'      => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        $db->table('invoices')->where('id', $invoiceId)->update($updateData);

        // Audit History
        $invNo = $invoice['invoice_number'] ?: ($invoice['invoice_no'] ?? 'INV-' . $invoiceId);
        $notes = 'Invoice disetujui oleh Finance. Verifikasi Pajak: ' . $enumTaxType . ' ' . $taxRatePercent . '%, Potongan Pajak: Rp ' . number_format($taxAmount, 0, ',', '.') . ', Net Transfer: Rp ' . number_format($grandTotal, 0, ',', '.');
        
        $db->table('invoice_histories')->insert([
            'invoice_id'    => $invoiceId,
            'from_status'   => $invoice['status'],
            'to_status'     => 'APPROVED',
            'action'        => 'APPROVED',
            'notes'         => $notes,
            'changed_by'    => $adminId,
            'actor_user_id' => $adminId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return [
            'success'     => true,
            'message'     => 'Invoice ' . $invNo . ' berhasil disetujui! Status tagihan kini APPROVED (Siap Dibayar).',
            'grand_total' => $grandTotal,
            'tax_amount'  => $taxAmount,
        ];
    }

    /**
     * Reject incoming invoice with reason
     */
    public function rejectInvoice(int $invoiceId, string $reason, int $adminId = 1): array
    {
        $db = \Config\Database::connect();
        $invoice = $db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice tidak ditemukan.'];
        }

        if (empty(trim($reason))) {
            return ['success' => false, 'message' => 'Alasan penolakan invoice wajib diisi!'];
        }

        $db->table('invoices')->where('id', $invoiceId)->update([
            'status'           => 'REJECTED',
            'rejection_reason' => trim($reason),
            'rejected_by'      => $adminId,
            'rejected_at'      => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        // Audit History
        $db->table('invoice_histories')->insert([
            'invoice_id'    => $invoiceId,
            'from_status'   => $invoice['status'],
            'to_status'     => 'REJECTED',
            'action'        => 'REJECTED',
            'notes'         => 'Invoice ditolak oleh Finance. Alasan: ' . trim($reason),
            'changed_by'    => $adminId,
            'actor_user_id' => $adminId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'message' => 'Invoice berhasil ditolak. Alasan penolakan telah dicatat dan dapat dilihat oleh KOL untuk revisi.',
        ];
    }

    /**
     * Upload official Bukti Potong Pajak (PPh 21 / PPh 23)
     */
    public function uploadBupot(int $invoiceId, string $bupotNo, string $filePath, int $adminId = 1): array
    {
        $db = \Config\Database::connect();
        $invoice = $db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice tidak ditemukan.'];
        }

        $db->table('invoices')->where('id', $invoiceId)->update([
            'bupot_no'          => $bupotNo,
            'bupot_file'        => $filePath,
            'bupot_uploaded_at' => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        // Audit History
        $db->table('invoice_histories')->insert([
            'invoice_id'    => $invoiceId,
            'from_status'   => $invoice['status'],
            'to_status'     => $invoice['status'],
            'action'        => 'BUPOT_UPLOADED',
            'notes'         => 'Bukti Potong resmi diunggah dengan No: ' . $bupotNo,
            'changed_by'    => $adminId,
            'actor_user_id' => $adminId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return [
            'success'  => true,
            'message'  => 'Bukti Potong ' . $bupotNo . ' berhasil diunggah! Dokumen kini dapat diakses oleh KOL.',
            'filePath' => $filePath,
        ];
    }

    /**
     * Concurrency-safe payment recording with transaction and row lock
     */
    public function recordPayment(int $invoiceId, float $amount, string $paymentDate, string $paymentMethod = 'Bank Transfer', ?string $receiptFile = null, ?string $notes = null, ?string $bankReference = null, int $adminId = 1): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Lock invoice row FOR UPDATE
        $invoice = $db->query("SELECT * FROM invoices WHERE id = ? FOR UPDATE", [$invoiceId])->getRowArray();
        if (!$invoice) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Invoice tidak ditemukan.'];
        }

        if (!in_array($invoice['status'], ['APPROVED', 'VERIFIED', 'PARTIAL'])) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Tagihan belum disetujui (status saat ini: ' . $invoice['status'] . '). Pembayaran hanya dapat dicatat untuk invoice yang berstatus APPROVED, VERIFIED, atau PARTIAL.',
            ];
        }

        // 2. Compute current total paid under lock
        $paidRow = $db->query("SELECT COALESCE(SUM(amount_paid), 0) as paid FROM payments WHERE invoice_id = ?", [$invoiceId])->getRowArray();
        $totalPaid = (float)($paidRow ? $paidRow['paid'] : 0);
        $grandTotal = (float)$invoice['grand_total'];
        $unpaidBalance = max(0, round($grandTotal - $totalPaid, 2));

        if ($amount > ($unpaidBalance + 0.01)) {
            $db->transRollback();
            return [
                'success' => false,
                'message' => 'Nominal transfer (Rp ' . number_format($amount, 0, ',', '.') . ') melebihi sisa unpaid balance (Rp ' . number_format($unpaidBalance, 0, ',', '.') . ').',
            ];
        }

        $paymentData = [
            'invoice_id'            => $invoiceId,
            'amount_paid'           => $amount,
            'payment_method'        => $paymentMethod ?: 'Bank Transfer',
            'bank_reference'        => $bankReference ?: 'TRX-' . time(),
            'notes'                 => $notes,
            'transfer_receipt_file' => $receiptFile,
            'paid_by'               => $adminId,
            'paid_at'               => date('Y-m-d H:i:s', strtotime($paymentDate)),
            'created_at'            => date('Y-m-d H:i:s'),
        ];

        $db->table('payments')->insert($paymentData);
        $paymentId = (int)$db->insertID();

        // 3. Compute new status
        $newTotalPaid = $totalPaid + $amount;
        $newStatus = ($newTotalPaid >= ($grandTotal - 0.01)) ? 'PAID' : 'PARTIAL';

        $db->table('invoices')->where('id', $invoiceId)->update([
            'status'     => $newStatus,
            'paid_at'    => ($newStatus === 'PAID') ? date('Y-m-d H:i:s') : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // 4. Audit Log
        $db->table('invoice_histories')->insert([
            'invoice_id'    => $invoiceId,
            'from_status'   => $invoice['status'],
            'to_status'     => $newStatus,
            'action'        => 'PAYMENT_RECORDED',
            'notes'         => 'Pembayaran sebesar Rp ' . number_format($amount, 0, ',', '.') . ' dicatat via ' . ($paymentMethod ?: 'Bank Transfer') . '. Status invoice kini: ' . $newStatus,
            'changed_by'    => $adminId,
            'actor_user_id' => $adminId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Gagal mencatat transaksi pembayaran ke database.'];
        }

        return [
            'success'        => true,
            'message'        => 'Pembayaran sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dicatat! Status invoice: ' . $newStatus,
            'payment_id'     => $paymentId,
            'new_status'     => $newStatus,
            'new_total_paid' => $newTotalPaid,
        ];
    }

    /**
     * Fetch audit log history for an invoice
     */
    public function getInvoiceHistory(int $invoiceId): array
    {
        $db = \Config\Database::connect();
        return $db->table('invoice_histories ih')
            ->select('ih.*, COALESCE(u.name, "System") as actor_name')
            ->join('users u', 'u.id = COALESCE(ih.changed_by, ih.actor_user_id)', 'left')
            ->where('ih.invoice_id', $invoiceId)
            ->orderBy('ih.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Export all deals for CSV / PDF
     */
    public function getAllForExport(array $filters = []): array
    {
        $campaignId = !empty($filters['campaign_id']) ? (int)$filters['campaign_id'] : null;
        $search     = !empty($filters['search']) ? (string)$filters['search'] : null;
        return $this->getDealsForFinance($campaignId, $search);
    }
}
