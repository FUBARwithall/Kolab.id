<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceHubModel extends Model
{
    protected $table            = 'campaign_kols';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

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

        // 2. Total Invoiced & Total Tax
        $invBuilder = $db->table('invoices')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->whereNotIn('invoices.status', ['REJECTED']);
        if ($campaignId) {
            $invBuilder->where('campaign_kols.campaign_id', $campaignId);
        }
        $invRes = $invBuilder->select('
            COUNT(*) as total_invoices,
            COALESCE(SUM(invoices.subtotal), 0) as total_subtotal,
            COALESCE(SUM(invoices.tax_amount), 0) as total_tax,
            COALESCE(SUM(invoices.grand_total), 0) as total_invoiced
        ')->get()->getRowArray();

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
        $outstanding     = max(0, $totalDealAmount - $totalPaid);

        return [
            'total_deals'        => (int)($dealRes['total_deals'] ?? 0),
            'total_deal_amount'  => $totalDealAmount,
            'total_invoices'     => (int)($invRes['total_invoices'] ?? 0),
            'total_invoiced'     => $totalInvoiced,
            'total_tax'          => $totalTax,
            'total_paid'         => $totalPaid,
            'total_transactions' => (int)($payRes['total_transactions'] ?? 0),
            'outstanding'        => $outstanding,
        ];
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
                campaigns.title as campaign_title,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                kols.npwp,
                contracts.id as contract_id,
                contracts.contract_number,
                contracts.signed_at as contract_signed_at,
                invoices.id as invoice_id,
                invoices.invoice_number,
                invoices.subtotal as invoice_subtotal,
                invoices.tax_type,
                invoices.tax_rate,
                invoices.tax_amount,
                invoices.grand_total as invoice_grand_total,
                invoices.status as invoice_status,
                invoices.rejection_reason,
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
            ->join('campaign_kol_contracts as contracts', 'contracts.id = (SELECT MAX(c.id) FROM campaign_kol_contracts c WHERE c.campaign_kol_id = campaign_kols.id)', 'left')
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
                    $builder->where('invoices.status', 'VERIFIED');
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
}
