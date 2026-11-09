<?php

namespace App\Controllers;

use App\Models\FinanceHubModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\CampaignModel;
use CodeIgniter\Exceptions\PageNotFoundException;

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
        helper(['form', 'url', 'filesystem']);
    }

    /**
     * Current authenticated user ID helper
     */
    protected function getUserId(): int
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            return (int) auth()->id();
        }
        $userId = session('user_id');
        return $userId ? (int) $userId : 1;
    }

    /**
     * Halaman Utama Executive Finance Hub
     */
    public function index($offset = 0)
    {
        $campaignId = (int)($this->request->getGet('campaign_id') ?: 0);
        $filter     = trim((string)($this->request->getGet('filter') ?: ($this->request->getGet('q') ?: '')));

        $metrics = $this->financeModel->getFinanceMetrics($campaignId ?: null);
        $allCampaigns = $this->campaignModel->select('id, title, name')->orderBy('id', 'DESC')->findAll();

        $deals = $this->financeModel->getDealsForFinance($campaignId ?: null, $filter ?: null);

        $stats = [
            'total_deals'       => $metrics['total_deals'],
            'total_deal_amount' => $metrics['total_deal_amount'],
            'total_invoiced'    => $metrics['total_invoiced'],
            'total_paid'        => $metrics['total_paid'],
            'total_unpaid'      => $metrics['outstanding'],
            'count_submitted'   => $metrics['count_submitted'],
            'count_approved'    => $metrics['count_approved'],
            'count_paid'        => $metrics['count_paid'],
        ];

        $data = [
            'title'        => 'Finance Hub — Eksekutif Keuangan, Pajak & Pencairan Dana',
            'controller'   => 'FinanceHub',
            'metrics'      => $metrics,
            'stats'        => $stats,
            'deals'        => $deals,
            'allCampaigns' => $allCampaigns,
            'campaignId'   => $campaignId ?: null,
            'filter'       => $filter,
        ];

        return view('finance_hub/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
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
        $no = $start + 1;
        foreach ($result['data'] as $row) {
            // KOL & Bank Info
            $bankStr = !empty($row['bank_name'])
                ? '<div class="small text-muted"><i class="fas fa-university me-1 text-secondary"></i>' . esc($row['bank_name']) . ' &bull; <b>' . esc($row['bank_account']) . '</b> (' . esc($row['bank_holder'] ?: $row['kol_name']) . ')</div>'
                : '<div class="small text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Belum ada rekening</div>';

            $npwpBadge = !empty($row['npwp'])
                ? '<span class="badge bg-light text-dark border px-1" style="font-size: 10px;"><i class="fas fa-id-card me-1 text-muted"></i>NPWP: ' . esc($row['npwp']) . '</span>'
                : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-1" style="font-size: 10px;"><i class="fas fa-exclamation-circle me-1"></i>Tanpa NPWP (3%)</span>';

            $kolCell = '
                <div class="fw-bold text-dark" style="font-size: 13.5px;">' . esc($row['kol_name']) . '</div>
                ' . $bankStr . '
                <div class="mt-1">' . $npwpBadge . '</div>
            ';

            // Contract Info
            $contractCell = !empty($row['contract_id'])
                ? '<div>
                    <a href="' . base_url('contracts/view/' . $row['contract_id']) . '" class="font-monospace fw-bold text-primary text-decoration-none" style="font-size: 12px;">' . esc($row['contract_number']) . '</a>
                    <div class="mt-1">' . (!empty($row['contract_signed_at']) 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-1" style="font-size: 9.5px;"><i class="fas fa-check-circle me-1"></i>Signed (' . date('d/m/y', strtotime($row['contract_signed_at'])) . ')</span>' 
                        : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1" style="font-size: 9.5px;">Draf Kontrak</span>') . '
                    </div>
                   </div>'
                : '<span class="badge bg-light text-muted border">Belum Dibuat</span>';

            // Invoice & Tax Info
            $invStatus = strtoupper($row['invoice_status'] ?? '');
            if (!$row['invoice_id']) {
                $invCell = '<span class="badge bg-light text-muted border">Belum Terbit</span>';
                $statusBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="far fa-clock me-1"></i>MENUNGGU INVOICE</span>';
            } else {
                $statusBadge = match ($invStatus) {
                    'PAID'      => '<span class="badge bg-success px-2 py-1"><i class="fas fa-check-double me-1"></i>PAID</span>',
                    'PARTIAL'   => '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-adjust me-1"></i>PARTIAL</span>',
                    'APPROVED', 'VERIFIED' => '<span class="badge bg-primary px-2 py-1"><i class="fas fa-check me-1"></i>SIAP CAIR (APPROVED)</span>',
                    'SUBMITTED' => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>SUBMITTED</span>',
                    'REJECTED'  => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times me-1"></i>REJECTED</span>',
                    default     => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">DRAFT</span>',
                };

                $taxPercent = (float)($row['tax_rate_percent'] ?: ($row['tax_rate'] * 100));
                $taxStr = esc($row['tax_type'] ?: 'Pajak') . ' (' . $taxPercent . '%): Rp ' . number_format((float)$row['tax_amount'], 0, ',', '.');
                $invCell = '
                    <div><a href="' . base_url('invoices/view/' . $row['invoice_id']) . '" class="font-monospace fw-bold text-primary text-decoration-none" style="font-size: 12.5px;"><i class="fas fa-file-invoice me-1"></i>' . esc($row['invoice_number']) . '</a></div>
                    <div class="small text-muted mt-1">DPP: Rp ' . number_format((float)$row['invoice_subtotal'], 0, ',', '.') . '</div>
                    <div class="small text-muted">' . $taxStr . '</div>
                    <div class="fw-bold text-success mt-1">Net: Rp ' . number_format((float)$row['invoice_grand_total'], 0, ',', '.') . '</div>
                ';
            }

            // Realization & Payment Cell
            $paidAmount = (float)$row['total_paid'];
            $grandTotal = (float)($row['invoice_grand_total'] ?? 0);
            $unpaidBal  = max(0, $grandTotal - $paidAmount);
            if ($paidAmount > 0) {
                $payCell = '
                    <div class="fw-bold text-success fs-6">Rp ' . number_format($paidAmount, 0, ',', '.') . '</div>
                    <small class="text-muted">' . esc($row['payment_method'] ?: 'Transfer') . ' &bull; ' . (!empty($row['paid_at']) ? date('d/m/y H:i', strtotime($row['paid_at'])) : '') . '</small>
                    <div class="small font-monospace text-secondary text-truncate" style="max-width: 140px;">' . esc($row['bank_reference'] ?: '-') . '</div>
                ';
                if ($unpaidBal > 0) {
                    $payCell .= '<div class="small text-danger fw-semibold mt-1">Sisa: Rp ' . number_format($unpaidBal, 0, ',', '.') . '</div>';
                }
            } else {
                $payCell = '<span class="text-muted small">Rp 0 (Belum Cair)</span>';
            }

            // Action Buttons
            $actions = '<div class="d-flex gap-1 justify-content-center">';
            if (!$row['invoice_id']) {
                $actions .= '<button type="button" class="btn btn-primary btn-sm rounded-2 py-1 px-2" title="Terbitkan Invoice Awal" onclick="createInvoice(' . $row['ck_id'] . ')"><i class="fas fa-plus-circle me-1"></i>Terbitkan</button>';
            } else {
                $actions .= '<a href="' . base_url('finance/invoice-print/' . $row['invoice_id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak / PDF Invoice"><i class="fas fa-print"></i></a>';
                
                if ($invStatus === 'SUBMITTED') {
                    $actions .= '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Review & Setujui Invoice (Pajak PPh)" onclick="openReviewInvoiceModal(' . $row['invoice_id'] . ', ' . (float)$row['invoice_subtotal'] . ', \'' . esc($row['npwp'] ?? '') . '\')"><i class="fas fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Tolak Invoice" onclick="rejectInvoiceModal(' . $row['invoice_id'] . ')"><i class="fas fa-times"></i></button>';
                } elseif (in_array($invStatus, ['APPROVED', 'VERIFIED', 'PARTIAL'])) {
                    $actions .= '<button type="button" class="btn btn-success btn-sm rounded-2 py-1 px-2" title="Proses Pencairan / Bayar" onclick="payInvoiceModal(' . $row['invoice_id'] . ', ' . $unpaidBal . ', \'' . esc($row['kol_name']) . '\', \'' . esc($row['bank_name']) . '\', \'' . esc($row['bank_account']) . '\', \'' . esc($row['bank_holder']) . '\', \'' . esc($row['invoice_number']) . '\')"><i class="fas fa-money-bill-wave me-1"></i>Bayar</button>';
                }
                
                $actions .= '<button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Upload Bukti Potong Pajak (Bupot)" onclick="uploadBupotModal(' . $row['invoice_id'] . ', \'' . esc($row['bupot_no'] ?? '') . '\', \'' . esc($row['bupot_file'] ?? '') . '\')"><i class="fas fa-file-invoice"></i></button>';
                $actions .= '<button type="button" class="btn btn-outline-dark btn-sm rounded-2 py-1 px-2" title="Lihat Riwayat Audit" onclick="viewInvoiceHistory(' . $row['invoice_id'] . ')"><i class="fas fa-history"></i></button>';
            }
            $actions .= '</div>';

            $campTitle = $row['campaign_title'] ?: ($row['campaign_name'] ?? 'Kampanye');
            $formattedData[] = [
                'id'          => $row['ck_id'],
                'no'          => $no++,
                'campaign'    => '<div class="fw-semibold text-dark">' . esc($campTitle) . '</div>' . (!empty($row['brand_name']) ? '<small class="text-muted d-block">' . esc($row['brand_name']) . '</small>' : '') . '<small class="badge bg-light text-muted border mt-1">' . (int)$row['total_vt'] . ' VT</small>',
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
     * Approve incoming invoice from KOL, with final tax withholding verification
     */
    public function approve_invoice()
    {
        $invoiceId       = (int)($this->request->getPost('invoice_id') ?: $this->request->getGet('invoice_id'));
        $taxType         = (string)($this->request->getPost('tax_type') ?: ($this->request->getGet('tax_type') ?: 'NONE'));
        $taxRatePercent  = (float)($this->request->getPost('tax_rate_percent') ?: ($this->request->getGet('tax_rate_percent') ?: 0.0));

        if (!$invoiceId) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID Invoice tidak valid.',
            ]);
        }

        $res = $this->financeModel->approveInvoice($invoiceId, $taxType, $taxRatePercent, $this->getUserId());

        if (!$res['success']) {
            return $this->response->setStatusCode(400)->setJSON($res);
        }

        return $this->response->setJSON($res);
    }

    public function approveInvoice(int $id = 0)
    {
        if ($id > 0 && empty($this->request->getPost('invoice_id'))) {
            $_POST['invoice_id'] = $id;
        }
        return $this->approve_invoice();
    }

    /**
     * Reject incoming invoice with mandatory reason
     */
    public function reject_invoice()
    {
        $invoiceId = (int)($this->request->getPost('invoice_id') ?: ($this->request->getGet('invoice_id') ?: 0));
        $reason    = trim((string)($this->request->getPost('rejection_reason') ?: ($this->request->getPost('reason') ?: ($this->request->getGet('rejection_reason') ?: ''))));

        if (!$invoiceId || empty($reason)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Alasan penolakan invoice wajib diisi!',
            ]);
        }

        $res = $this->financeModel->rejectInvoice($invoiceId, $reason, $this->getUserId());

        if (!$res['success']) {
            return $this->response->setStatusCode(400)->setJSON($res);
        }

        return $this->response->setJSON($res);
    }

    public function rejectInvoice(int $id = 0)
    {
        if ($id > 0 && empty($this->request->getPost('invoice_id'))) {
            $_POST['invoice_id'] = $id;
        }
        return $this->reject_invoice();
    }

    /**
     * Upload official Bukti Potong Pajak (PPh 21 / PPh 23)
     */
    public function upload_bupot()
    {
        $invoiceId = (int)($this->request->getPost('invoice_id') ?: ($this->request->getGet('invoice_id') ?: 0));
        $bupotNo   = trim((string)($this->request->getPost('bupot_no') ?: ($this->request->getGet('bupot_no') ?: '')));

        if (!$invoiceId || empty($bupotNo)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Nomor Bukti Potong wajib diisi!',
            ]);
        }

        $file = $this->request->getFile('bupot_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Berkas bukti potong (PDF/JPG/PNG) wajib diunggah!',
            ]);
        }

        $uploadDir = FCPATH . 'uploads/bukti_potong';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Format berkas tidak didukung. Harap unggah PDF, JPG, atau PNG.',
            ]);
        }

        $filename = 'bupot_' . $invoiceId . '_' . date('YmdHis') . '.' . $ext;
        $file->move($uploadDir, $filename);
        $savedPath = 'uploads/bukti_potong/' . $filename;

        $res = $this->financeModel->uploadBupot($invoiceId, $bupotNo, $savedPath, $this->getUserId());

        return $this->response->setJSON($res);
    }

    public function uploadBupot(int $id = 0)
    {
        if ($id > 0 && empty($this->request->getPost('invoice_id'))) {
            $_POST['invoice_id'] = $id;
        }
        return $this->upload_bupot();
    }

    /**
     * Concurrency-safe payment recording
     */
    public function save_payment()
    {
        $invoiceId     = (int)($this->request->getPost('invoice_id') ?: ($this->request->getGet('invoice_id') ?: 0));
        $rawAmount     = $this->request->getPost('amount') ?: ($this->request->getPost('amount_paid') ?: ($this->request->getGet('amount') ?: 0));
        $paymentDate   = $this->request->getPost('payment_date') ?: ($this->request->getGet('payment_date') ?: date('Y-m-d H:i:s'));
        $paymentMethod = $this->request->getPost('payment_method') ?: ($this->request->getGet('payment_method') ?: 'Bank Transfer');
        $notes         = $this->request->getPost('notes') ?: ($this->request->getGet('notes') ?: null);
        $bankRef       = $this->request->getPost('bank_reference') ?: ($this->request->getGet('bank_reference') ?: null);

        // Normalize amount string
        if (is_string($rawAmount)) {
            $cleanAmount = str_replace('.', '', $rawAmount);
            $cleanAmount = str_replace(',', '.', $cleanAmount);
            $amount = (float)$cleanAmount;
        } else {
            $amount = (float)$rawAmount;
        }

        if (!$invoiceId || $amount <= 0 || empty($paymentDate)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invoice, Tanggal, dan Nominal Pembayaran wajib diisi dan harus lebih besar dari 0!',
            ]);
        }

        // File upload if supplied
        $receiptFile = null;
        $file = $this->request->getFile('payment_proof_file') ?? $this->request->getFile('transfer_receipt_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/payments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower($file->getExtension());
            $filename = 'pay_' . $invoiceId . '_' . date('YmdHis') . '.' . $ext;
            $file->move($uploadDir, $filename);
            $receiptFile = 'uploads/payments/' . $filename;
        }

        $res = $this->financeModel->recordPayment(
            $invoiceId,
            $amount,
            $paymentDate,
            $paymentMethod,
            $receiptFile,
            $notes,
            $bankRef,
            $this->getUserId()
        );

        if (!$res['success']) {
            return $this->response->setStatusCode(400)->setJSON($res);
        }

        return $this->response->setJSON($res);
    }

    public function savePayment(int $id = 0)
    {
        if ($id > 0 && empty($this->request->getPost('invoice_id'))) {
            $_POST['invoice_id'] = $id;
        }
        return $this->save_payment();
    }

    /**
     * Fetch audit log history for an invoice (JSON)
     */
    public function get_invoice_history_ajax($invoiceId)
    {
        $invoiceId = (int)$invoiceId;
        $histories = $this->financeModel->getInvoiceHistory($invoiceId);

        return $this->response->setJSON([
            'success'   => true,
            'histories' => $histories,
        ]);
    }

    /**
     * Securely stream or view invoice attachment uploaded by KOL
     */
    public function view_invoice_file($invoiceId)
    {
        $invoiceId = (int)$invoiceId;
        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice || empty($invoice['invoice_file'])) {
            throw PageNotFoundException::forPageNotFound('Berkas lampiran invoice tidak ditemukan.');
        }

        $filePath = $this->resolveFilePath($invoice['invoice_file']);
        if (!$filePath || !file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('Berkas lampiran invoice tidak ditemukan di server.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'pdf'           => 'application/pdf',
            'jpg', 'jpeg'   => 'image/jpeg',
            'png'           => 'image/png',
            default         => mime_content_type($filePath) ?: 'application/octet-stream'
        };

        return $this->response
            ->setHeader('Content-Type', $contentType)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setHeader('Content-Length', (string)filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Resolves file path across local and shared sibling directories
     */
    protected function resolveFilePath(string $filepath): ?string
    {
        if (empty($filepath)) {
            return null;
        }

        $paths = [
            FCPATH . ltrim($filepath, '/\\'),
            FCPATH . 'uploads/' . ltrim($filepath, '/\\'),
            dirname(FCPATH) . '/kol-system/' . ltrim($filepath, '/\\'),
            dirname(FCPATH) . '/kol-system-card-plugin/' . ltrim($filepath, '/\\'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return FCPATH . ltrim($filepath, '/\\');
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
            'title'   => 'Invoice Resmi #' . esc($invoice['invoice_number'] ?? ($invoice['invoice_no'] ?? 'INV-' . $id)),
            'invoice' => $invoice,
        ];

        return view('finance_hub/print_invoice', $data);
    }

    public function invoice_print(int $id)
    {
        return $this->printInvoice($id);
    }

    public function print_invoice(int $id)
    {
        return $this->printInvoice($id);
    }

    /**
     * Export Finance Deals & Invoices to CSV
     */
    public function export()
    {
        $campaignId = $this->request->getGet('campaign_id') ? (int)$this->request->getGet('campaign_id') : null;
        $search     = $this->request->getGet('search') ?: $this->request->getGet('filter');

        $deals = $this->financeModel->getAllForExport([
            'campaign_id' => $campaignId,
            'search'      => $search,
        ]);

        $filename = 'Finance_Hub_Export_' . date('Ymd_His') . '.csv';
        $output   = fopen('php://temp', 'r+');

        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'No',
            'Kampanye',
            'Brand',
            'Talent KOL',
            'Bank',
            'No. Rekening',
            'Atas Nama',
            'NPWP',
            'No. Kontrak',
            'Status Kontrak',
            'No. Invoice',
            'Status Invoice',
            'Deal Amount (IDR)',
            'DPP Subtotal (IDR)',
            'Pajak PPh (IDR)',
            'Grand Total Net (IDR)',
            'Total Terbayar (IDR)',
            'Sisa Unpaid (IDR)',
            'No. Bupot',
        ]);

        $no = 1;
        foreach ($deals as $d) {
            $dealAmt  = (float)($d['deal_amount'] ?? 0);
            $subtotal = (float)($d['subtotal'] ?? 0);
            $taxAmt   = (float)($d['tax_amount'] ?? 0);
            $netTotal = (float)($d['grand_total'] ?? 0);
            $paidAmt  = (float)($d['total_paid'] ?? 0);
            $unpaid   = max(0, $netTotal > 0 ? ($netTotal - $paidAmt) : ($dealAmt - $paidAmt));

            fputcsv($output, [
                $no++,
                $d['campaign_name'] ?: '-',
                $d['brand_name'] ?: '-',
                $d['kol_name'] ?: '-',
                $d['kol_bank_name'] ?: '-',
                $d['kol_bank_account_number'] ?: '-',
                $d['kol_bank_account_name'] ?: '-',
                $d['kol_npwp_number'] ?: '-',
                $d['contract_no'] ?: '-',
                $d['contract_status'] ?: 'DRAFT',
                $d['invoice_no'] ?: '-',
                $d['invoice_status'] ?: 'NO_INVOICE',
                $dealAmt,
                $subtotal,
                $taxAmt,
                $netTotal,
                $paidAmt,
                $unpaid,
                $d['bupot_no'] ?: '-',
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($csvContent);
    }

    /**
     * Export Printable PDF Layout of Finance Hub
     */
    public function export_pdf()
    {
        $campaignId = $this->request->getGet('campaign_id') ? (int)$this->request->getGet('campaign_id') : null;
        $search     = $this->request->getGet('search') ?: $this->request->getGet('filter');

        $deals   = $this->financeModel->getAllForExport([
            'campaign_id' => $campaignId,
            'search'      => $search,
        ]);
        $metrics = $this->financeModel->getFinanceMetrics($campaignId);

        $data = [
            'title'   => 'Laporan Rekapitulasi Keuangan & Realisasi Pembayaran KOL',
            'deals'   => $deals,
            'metrics' => $metrics,
            'date'    => date('d F Y, H:i') . ' WIB',
        ];

        return view('finance_hub/export_pdf', $data);
    }
}
