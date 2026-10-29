<?php

namespace App\Controllers;

use App\Models\FinanceHubModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\CampaignModel;

class FinanceHub extends BaseController
{
    protected FinanceHubModel $financeModel;
    protected InvoiceModel $invoiceModel;
    protected PaymentModel $paymentModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->financeModel  = new FinanceHubModel();
        $this->invoiceModel  = new InvoiceModel();
        $this->paymentModel  = new PaymentModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Executive Finance Hub
     */
    public function index()
    {
        $campaignId = $this->request->getGet('campaign_id') ? (int)$this->request->getGet('campaign_id') : null;
        $metrics = $this->financeModel->getFinanceMetrics($campaignId);
        $allCampaigns = $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll();

        $data = [
            'title'        => 'Finance Hub — Eksekutif Keuangan, Pajak & Pencairan Dana',
            'controller'   => 'FinanceHub',
            'metrics'      => $metrics,
            'allCampaigns' => $allCampaigns,
            'campaignId'   => $campaignId,
        ];

        return view('finance_hub/index', $data);
    }

    /**
     * DataTables Server-Side AJAX
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
            0 => 'campaign_kols.id',
            1 => 'campaigns.title',
            2 => 'kols.name',
            3 => 'contracts.contract_number',
            4 => 'invoices.invoice_number',
            5 => 'campaign_kols.deal_amount',
            6 => 'invoices.status',
            7 => 'campaign_kols.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'campaign_kols.id';

        $filters = [
            'campaign_id' => $this->request->getPost('campaign_id'),
            'status'      => $this->request->getPost('status'),
        ];

        $result = $this->financeModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            // KOL & Bank Info
            $bankStr = !empty($row['bank_name'])
                ? '<div class="small text-muted"><i class="fas fa-university me-1 text-secondary"></i>' . esc($row['bank_name']) . ' &bull; <b>' . esc($row['bank_account']) . '</b> (' . esc($row['bank_holder'] ?: $row['kol_name']) . ')</div>'
                : '<div class="small text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Belum ada rekening</div>';
            
            $npwpBadge = !empty($row['npwp'])
                ? '<span class="badge bg-light text-dark border px-1" style="font-size: 10px;">NPWP: ' . esc($row['npwp']) . '</span>'
                : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-1" style="font-size: 10px;">Tanpa NPWP (3%)</span>';

            $kolCell = '
                <div class="fw-bold text-dark" style="font-size: 13.5px;">' . esc($row['kol_name']) . '</div>
                ' . $bankStr . '
                <div class="mt-1">' . $npwpBadge . '</div>
            ';

            // Contract Info
            $contractCell = !empty($row['contract_id'])
                ? '<div>
                    <span class="font-monospace fw-bold text-dark" style="font-size: 12px;">' . esc($row['contract_number']) . '</span>
                    <div class="mt-1">' . (!empty($row['contract_signed_at']) 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-1" style="font-size: 9.5px;"><i class="fas fa-check-circle me-1"></i>Signed (' . date('d/m/y', strtotime($row['contract_signed_at'])) . ')</span>' 
                        : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1" style="font-size: 9.5px;">Draf Kontrak</span>') . '
                    </div>
                   </div>'
                : '<span class="badge bg-light text-muted border">Belum Dibuat</span>';

            // Invoice & Tax Info
            $invStatus = $row['invoice_status'];
            if (!$row['invoice_id']) {
                $invCell = '<span class="badge bg-light text-muted border">Belum Terbit</span>';
                $statusBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="far fa-clock me-1"></i>MENUNGGU INVOICE</span>';
            } else {
                $statusBadge = match ($invStatus) {
                    'PAID'      => '<span class="badge bg-success px-2 py-1"><i class="fas fa-check-double me-1"></i>TERBAYAR</span>',
                    'VERIFIED'  => '<span class="badge bg-primary px-2 py-1"><i class="fas fa-check me-1"></i>SIAP CAIR</span>',
                    'SUBMITTED' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>SUBMITTED</span>',
                    'REJECTED'  => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times me-1"></i>REJECTED</span>',
                    default     => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">DRAFT</span>',
                };

                $taxStr = $row['tax_type'] . ' (' . ($row['tax_rate'] * 100) . '%): Rp ' . number_format((float)$row['tax_amount'], 0, ',', '.');
                $invCell = '
                    <div class="font-monospace fw-bold text-primary" style="font-size: 12.5px;">' . esc($row['invoice_number']) . '</div>
                    <div class="small text-muted mt-1">DPP: Rp ' . number_format((float)$row['invoice_subtotal'], 0, ',', '.') . '</div>
                    <div class="small text-muted">' . $taxStr . '</div>
                    <div class="fw-bold text-success mt-1">Net: Rp ' . number_format((float)$row['invoice_grand_total'], 0, ',', '.') . '</div>
                ';
            }

            // Realization & Payment Cell
            $paidAmount = (float)$row['total_paid'];
            if ($paidAmount > 0) {
                $payCell = '
                    <div class="fw-bold text-success fs-6">Rp ' . number_format($paidAmount, 0, ',', '.') . '</div>
                    <small class="text-muted">' . esc($row['payment_method']) . ' &bull; ' . (!empty($row['paid_at']) ? date('d/m/y H:i', strtotime($row['paid_at'])) : '') . '</small>
                    <div class="small font-monospace text-secondary text-truncate" style="max-width: 140px;">' . esc($row['bank_reference']) . '</div>
                ';
            } else {
                $payCell = '<span class="text-muted small">Rp 0 (Belum Cair)</span>';
            }

            // Action Buttons
            $actions = '<div class="d-flex gap-1 justify-content-center">';
            if (!$row['invoice_id']) {
                $actions .= '<button type="button" class="btn btn-primary btn-sm rounded-2 py-1 px-2" title="Terbitkan Invoice" onclick="createInvoice(' . $row['ck_id'] . ')"><i class="fas fa-plus-circle me-1"></i>Terbitkan</button>';
            } else {
                $actions .= '<a href="' . base_url('finance/invoice-print/' . $row['invoice_id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak / PDF Invoice"><i class="fas fa-print"></i></a>';
                
                if ($invStatus === 'SUBMITTED') {
                    $actions .= '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Verifikasi Invoice" onclick="verifyInvoice(' . $row['invoice_id'] . ')"><i class="fas fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Tolak Invoice" onclick="rejectInvoiceModal(' . $row['invoice_id'] . ')"><i class="fas fa-times"></i></button>';
                } elseif ($invStatus === 'VERIFIED') {
                    $actions .= '<button type="button" class="btn btn-success btn-sm rounded-2 py-1 px-2" title="Proses Pencairan / Bayar" onclick="payInvoiceModal(' . $row['invoice_id'] . ', ' . (float)$row['invoice_grand_total'] . ')"><i class="fas fa-money-bill-wave me-1"></i>Bayar</button>';
                }
                
                $actions .= '<button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Upload Bukti Potong Pajak (Bupot)" onclick="uploadBupotModal(' . $row['invoice_id'] . ', \'' . esc($row['bupot_file'] ?? '') . '\')"><i class="fas fa-file-invoice"></i></button>';
            }
            $actions .= '</div>';

            $formattedData[] = [
                'id'          => $row['ck_id'],
                'campaign'    => '<div class="fw-semibold text-dark">' . esc($row['campaign_title']) . '</div><small class="badge bg-light text-muted border">' . $row['total_vt'] . ' VT</small>',
                'kol'         => $kolCell,
                'contract'    => $contractCell,
                'invoice'     => $invCell,
                'deal_amount' => '<strong class="text-dark">Rp ' . number_format((float)$row['deal_amount'], 0, ',', '.') . '</strong>',
                'payment'     => $payCell,
                'status'      => $statusBadge,
                'action'      => $actions,
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
     * Reject Invoice with reason
     */
    public function rejectInvoice(int $id)
    {
        $reason = trim((string)$this->request->getPost('reason'));
        if (empty($reason)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Alasan penolakan wajib diisi.']);
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? 1);

        $db = \Config\Database::connect();
        $db->table('invoices')->where('id', $id)->update([
            'status'           => 'REJECTED',
            'rejection_reason' => $reason,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Invoice berhasil ditolak dan dikembalikan ke draft untuk direvisi.',
        ]);
    }

    /**
     * Upload Bukti Potong Pajak (Bupot)
     */
    public function uploadBupot(int $id)
    {
        $file = $this->request->getFile('bupot_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'File bukti potong tidak valid.']);
        }

        $uploadPath = ROOTPATH . 'public/uploads/bupot';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $db = \Config\Database::connect();
        $db->table('invoices')->where('id', $id)->update([
            'bupot_file' => 'uploads/bupot/' . $newName,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Bukti Potong Pajak (Bupot) berhasil diunggah!',
            'filePath' => 'uploads/bupot/' . $newName,
        ]);
    }

    /**
     * Print View / PDF Layout Invoice
     */
    public function printInvoice(int $id)
    {
        $invoice = $this->invoiceModel->getInvoiceDetails($id);
        if (!$invoice) {
            return redirect()->to(base_url('finance'))->with('error', 'Invoice tidak ditemukan.');
        }

        $data = [
            'title'   => 'Invoice Resmi #' . $invoice['invoice_number'],
            'invoice' => $invoice,
        ];

        return view('finance_hub/print_invoice', $data);
    }
}
