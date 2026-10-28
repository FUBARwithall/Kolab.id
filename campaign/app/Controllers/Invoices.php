<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\CampaignModel;

class Invoices extends BaseController
{
    protected InvoiceModel $invoiceModel;
    protected PaymentModel $paymentModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->invoiceModel  = new InvoiceModel();
        $this->paymentModel  = new PaymentModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Finance & Invoices
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, kols.name as kol_name, kols.npwp')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'        => 'Finance & Invoices — Manajemen Pembayaran KOL',
            'controller'   => 'Invoices',
            'metrics'      => $this->invoiceModel->getSummaryMetrics(),
            'assignedKols' => $assignedKols,
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('invoices/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Direct access not allowed']);
        }

        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'invoices.id',
            1 => 'invoices.invoice_number',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'invoices.subtotal',
            5 => 'invoices.tax_amount',
            6 => 'invoices.grand_total',
            7 => 'invoices.status',
            8 => 'invoices.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'invoices.id';

        $filters = [
            'status'      => $this->request->getPost('status'),
            'campaign_id' => $this->request->getPost('campaign_id'),
        ];

        $result = $this->invoiceModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = match ($row['status']) {
                InvoiceModel::STATUS_PAID => '<span class="badge bg-success px-2 py-1"><i class="fas fa-check-double me-1"></i>TERBAYAR (PAID)</span>',
                InvoiceModel::STATUS_VERIFIED => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-check me-1"></i>VERIFIED (SIAP CAIR)</span>',
                InvoiceModel::STATUS_SUBMITTED => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>SUBMITTED</span>',
                InvoiceModel::STATUS_REJECTED => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times me-1"></i>REJECTED</span>',
                default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">DRAFT</span>',
            };

            $taxInfo = '<span class="small text-muted">' . $row['tax_type'] . ' (' . ($row['tax_rate'] * 100) . '%): Rp ' . number_format((float)$row['tax_amount'], 0, ',', '.') . '</span>';

            $formattedData[] = [
                'id'          => $row['id'],
                'inv_number'  => '
                    <div class="fw-bold font-monospace text-primary">' . esc($row['invoice_number']) . '</div>
                    <small class="text-muted">' . date('d M Y', strtotime($row['created_at'])) . '</small>
                ',
                'campaign'    => '<div class="fw-semibold text-dark">' . esc($row['campaign_title']) . '</div>',
                'kol'         => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">' . esc($row['bank_name'] ?: '-') . ' ' . esc($row['bank_account'] ?: '') . '</small>
                ',
                'subtotal'    => 'Rp ' . number_format((float)$row['subtotal'], 0, ',', '.'),
                'tax'         => $taxInfo,
                'grand_total' => '<strong class="text-success fs-6">Rp ' . number_format((float)$row['grand_total'], 0, ',', '.') . '</strong>',
                'status'      => $statusBadge,
                'action'      => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Invoice" onclick="viewInvoice(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        ' . ($row['status'] === InvoiceModel::STATUS_SUBMITTED ? '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Verifikasi Invoice" onclick="verifyInvoice(' . $row['id'] . ')"><i class="fas fa-check"></i></button>' : '') . '
                        ' . ($row['status'] === InvoiceModel::STATUS_VERIFIED ? '<button type="button" class="btn btn-success btn-sm rounded-2 py-1 px-2" title="Proses Pencairan / Bayar" onclick="payInvoiceModal(' . $row['id'] . ')"><i class="fas fa-money-bill-wave"></i></button>' : '') . '
                    </div>
                ',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $formattedData,
        ]);
    }

    /**
     * Get single invoice details JSON
     */
    public function getOne(int $id)
    {
        $invoice = $this->invoiceModel->getInvoiceDetails($id);
        if (!$invoice) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $invoice]);
    }

    /**
     * Create invoice directly from Campaign KOL deal
     */
    public function createFromDeal(int $campaignKolId)
    {
        $db = \Config\Database::connect();
        $ck = $db->table('campaign_kols')
            ->select('campaign_kols.*, kols.npwp')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('campaign_kols.id', $campaignKolId)
            ->get()
            ->getRowArray();

        if (!$ck) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Penugasan KOL tidak ditemukan']);
        }

        $subtotal = (float)$ck['deal_amount'];
        $hasNpwp  = !empty($ck['npwp']);
        $taxType  = $this->request->getPost('tax_type') ?: InvoiceModel::TAX_PPH21;

        $taxCalc = InvoiceModel::calculateTax($subtotal, $taxType, $hasNpwp);
        $invoiceNumber = $this->invoiceModel->generateInvoiceNumber();

        $invoiceData = [
            'campaign_kol_id' => $campaignKolId,
            'invoice_number'  => $invoiceNumber,
            'subtotal'        => $subtotal,
            'tax_type'        => $taxCalc['tax_type'],
            'tax_rate'        => $taxCalc['tax_rate'],
            'tax_amount'      => $taxCalc['tax_amount'],
            'grand_total'     => $taxCalc['grand_total'],
            'status'          => InvoiceModel::STATUS_SUBMITTED,
        ];

        $invoiceId = $this->invoiceModel->insert($invoiceData, true);

        return $this->response->setJSON([
            'success'        => true,
            'message'        => 'Invoice ' . $invoiceNumber . ' berhasil diterbitkan!',
            'invoice_id'     => $invoiceId,
            'invoice_number' => $invoiceNumber,
        ]);
    }

    /**
     * Verify invoice (staff / leader approval)
     */
    public function verify(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);

        $this->invoiceModel->update($id, [
            'status'      => InvoiceModel::STATUS_VERIFIED,
            'verified_by' => $userId,
            'verified_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Invoice berhasil diverifikasi dan siap dicairkan oleh bagian keuangan.',
        ]);
    }

    /**
     * Save payment disbursement
     */
    public function savePayment(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);

        $inv = $this->invoiceModel->find($id);
        if (!$inv) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice tidak ditemukan']);
        }

        $amount        = (float) ($this->request->getPost('amount_paid') ?: $inv['grand_total']);
        $method        = $this->request->getPost('payment_method') ?: 'BANK_TRANSFER';
        $bankRef       = trim((string)$this->request->getPost('bank_reference'));
        $receiptFile   = trim((string)$this->request->getPost('transfer_receipt_file'));

        $paymentData = [
            'invoice_id'            => $id,
            'amount_paid'           => $amount,
            'payment_method'        => $method,
            'bank_reference'        => $bankRef ?: ('TRX-' . strtoupper(substr(md5(uniqid()), 0, 8))),
            'transfer_receipt_file' => $receiptFile ?: null,
            'paid_at'               => date('Y-m-d H:i:s'),
        ];

        $paymentId = $this->paymentModel->recordPayment($paymentData, $userId);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Pembayaran invoice sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dicatat dan status lunas!',
            'payment_id' => $paymentId,
        ]);
    }
}
