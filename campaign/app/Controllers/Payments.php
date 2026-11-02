<?php

namespace App\Controllers;

use App\Models\PaymentModel;
use App\Models\InvoiceModel;
use App\Models\CampaignModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Payments extends BaseController
{
    protected PaymentModel $paymentModel;
    protected InvoiceModel $invoiceModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->paymentModel  = new PaymentModel();
        $this->invoiceModel  = new InvoiceModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Get Current Authenticated User ID
     */
    protected function getUserId(): int
    {
        $session = session();
        return (int) ($session->get('user_id') ?? 1);
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest(): bool
    {
        return method_exists($this->request, 'isAJAX') && $this->request->isAJAX();
    }

    /**
     * Check if request expects JSON or is AJAX
     */
    protected function isJsonOrAjax(): bool
    {
        if ($this->isAjaxRequest()) {
            return true;
        }
        if (method_exists($this->request, 'getHeaderLine') && str_contains($this->request->getHeaderLine('accept'), 'json')) {
            return true;
        }
        return false;
    }

    /**
     * Halaman Utama Pembayaran & Pencairan Dana
     */
    public function index($offset = 0)
    {
        if ($this->isAjaxRequest() && $this->request->getPost('draw') !== null) {
            return $this->getDatatable();
        }

        $db = \Config\Database::connect();
        $activeInvoices = $db->table('invoices')
            ->select('
                invoices.id,
                invoices.invoice_number,
                invoices.grand_total,
                invoices.status,
                campaigns.title as campaign_title,
                kols.name as kol_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('invoices.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'          => 'Payments & Disbursements — Riwayat Pembayaran Tagihan KOL',
            'controller'     => 'Payments',
            'metrics'        => $this->paymentModel->getSummaryMetrics(),
            'activeInvoices' => $activeInvoices,
            'allCampaigns'   => $this->campaignModel->select('id, title, name')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('payments/index', $data);
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
            0 => 'payments.id',
            1 => 'payments.paid_at',
            2 => 'invoices.invoice_number',
            3 => 'kols.name',
            4 => 'payments.amount_paid',
            5 => 'payments.payment_method',
            6 => 'payments.bank_reference',
            7 => 'payer.name',
            8 => 'payments.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'payments.id';

        $filters = [
            'invoice_id'     => $this->request->getPost('invoice_id'),
            'campaign_id'    => $this->request->getPost('campaign_id'),
            'payment_method' => $this->request->getPost('payment_method'),
        ];

        $result = $this->paymentModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $receiptPreview = '-';
            if (!empty($row['transfer_receipt_file'])) {
                $fileUrl = base_url($row['transfer_receipt_file']);
                $isImg = preg_match('/\.(jpg|jpeg|png|webp)$/i', $row['transfer_receipt_file']);
                if ($isImg) {
                    $receiptPreview = '
                        <a href="' . $fileUrl . '" target="_blank" title="Lihat Bukti Transfer">
                            <img src="' . $fileUrl . '" class="rounded border shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                        </a>
                    ';
                } else {
                    $receiptPreview = '
                        <a href="' . $fileUrl . '" target="_blank" class="btn btn-xs btn-outline-primary rounded-2">
                            <i class="fas fa-file-pdf me-1"></i> Dokumen
                        </a>
                    ';
                }
            }

            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . base_url('payments/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Pembayaran"><i class="fas fa-eye"></i></a>
                    <a href="' . base_url('payments/receipt-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak Voucher"><i class="fas fa-print"></i></a>
                    <a href="' . base_url('payments/edit/' . $row['id']) . '" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2" title="Edit Pembayaran"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Pembayaran" onclick="deletePayment(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                </div>
            ';

            $formattedData[] = [
                'id'           => $row['id'],
                'date'         => '
                    <div class="fw-semibold text-dark">' . date('d M Y', strtotime($row['paid_at'])) . '</div>
                    <small class="text-muted">' . date('H:i', strtotime($row['paid_at'])) . ' WIB</small>
                ',
                'invoice'      => '
                    <a href="' . base_url('invoices/view/' . $row['invoice_id']) . '" class="font-monospace fw-bold text-primary text-decoration-none">
                        ' . esc($row['invoice_number']) . '
                    </a>
                    <div class="small text-muted">' . esc($row['campaign_title'] ?: ($row['campaign_name'] ?? 'N/A')) . '</div>
                ',
                'kol'          => '
                    <div class="fw-semibold text-dark">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">' . esc($row['bank_name'] ?: '-') . ' ' . esc($row['bank_account'] ?: '') . '</small>
                ',
                'amount'       => '<strong class="text-success fs-6">Rp ' . number_format((float)$row['amount_paid'], 0, ',', '.') . '</strong>',
                'method'       => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">' . esc($row['payment_method']) . '</span>',
                'reference'    => '<span class="font-monospace fw-semibold text-dark">' . esc($row['bank_reference'] ?: '-') . '</span>',
                'receipt'      => $receiptPreview,
                'action'       => $actions,
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
     * Get Single Payment JSON
     */
    public function getOne(int $id)
    {
        $payment = $this->paymentModel->getPaymentDetails($id);
        if (!$payment) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data pembayaran tidak ditemukan.']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $payment]);
    }

    /**
     * View Detail Payment Page
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $payment = $this->paymentModel->getPaymentDetails($id);
        if (!$payment) {
            throw PageNotFoundException::forPageNotFound('Data pembayaran tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Pencairan Pembayaran #' . esc($payment['id']),
            'controller' => 'Payments',
            'payment'    => $payment,
        ];

        return view('payments/view', $data);
    }

    /**
     * Form Tambah Pembayaran Baru
     */
    public function add()
    {
        $db = \Config\Database::connect();
        $invoices = $db->table('invoices')
            ->select('
                invoices.id,
                invoices.invoice_number,
                invoices.grand_total,
                invoices.status,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                COALESCE((SELECT SUM(amount_paid) FROM payments WHERE payments.invoice_id = invoices.id), 0) as total_paid
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->whereNotIn('invoices.status', [InvoiceModel::STATUS_CANCELLED])
            ->orderBy('invoices.id', 'DESC')
            ->get()
            ->getResultArray();

        $selectedInvId = (int) ($this->request->getGet('invoice_id') ?? 0);

        $data = [
            'title'         => 'Catat Pencairan Pembayaran Tagihan',
            'controller'    => 'Payments',
            'invoices'      => $invoices,
            'selectedInvId' => $selectedInvId,
        ];

        return view('payments/add', $data);
    }

    /**
     * Simpan Tambah Pembayaran Baru
     */
    public function add_save()
    {
        $rules = [
            'invoice_id'  => 'required',
            'amount_paid' => 'permit_empty|numeric',
            'amount'      => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            $errors = implode(', ', $this->validator->getErrors());
            if ($this->isAjaxRequest()) {
                return $this->response->setJSON(['success' => false, 'message' => $errors]);
            }
            return redirect()->back()->withInput()->with('error', $errors);
        }

        $invoiceId = (int) $this->request->getPost('invoice_id');
        $inv = $this->invoiceModel->find($invoiceId);
        if (!$inv) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice tidak ditemukan.']);
        }

        $amountReq = $this->request->getPost('amount_paid') ?: $this->request->getPost('amount');
        $amount = (float) ($amountReq ?: $inv['grand_total']);

        $method      = $this->request->getPost('payment_method') ?: 'BANK_TRANSFER';
        $bankRef     = trim((string) ($this->request->getPost('bank_reference') ?: ('TRX-' . strtoupper(substr(md5(uniqid()), 0, 8)))));
        $notes       = trim((string) $this->request->getPost('notes'));
        $paymentDate = $this->request->getPost('paid_at') ?: ($this->request->getPost('payment_date') ?: date('Y-m-d H:i:s'));

        // Handle File Upload
        $receiptFile = null;
        $file = $this->request->getFile('transfer_receipt_file') ?: $this->request->getFile('payment_proof_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/payments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $receiptFile = 'uploads/payments/' . $newName;
        }

        // Fine Uploader copy if present
        $fineNames = (array) $this->request->getPost('payments_payment_proof_file_name');
        $fineUuids = (array) $this->request->getPost('payments_payment_proof_file_uuid');
        if (!empty($fineNames)) {
            $uploadDir = FCPATH . 'uploads/payments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $collected = [];
            foreach ($fineNames as $idx => $fn) {
                if (!empty($fineUuids[$idx])) {
                    $tmpPath = FCPATH . 'uploads/tmp/' . $fineUuids[$idx] . '/' . $fn;
                    $destName = date('YmdHis') . '-' . $fn;
                    if (is_file($tmpPath)) {
                        rename($tmpPath, $uploadDir . '/' . $destName);
                        $collected[] = 'uploads/payments/' . $destName;
                    }
                } else {
                    $collected[] = $fn;
                }
            }
            if (!empty($collected)) {
                $receiptFile = implode(',', $collected);
            }
        }

        $userId = $this->getUserId();

        $paymentData = [
            'invoice_id'            => $invoiceId,
            'amount_paid'           => $amount,
            'payment_method'        => $method,
            'bank_reference'        => $bankRef,
            'notes'                 => $notes ?: null,
            'transfer_receipt_file' => $receiptFile ?: null,
            'paid_at'               => $paymentDate,
        ];

        $paymentId = $this->paymentModel->recordPayment($paymentData, $userId);

        $saveType = $this->request->getPost('save_type');
        $redirectUrl = ($saveType === 'stay') ? base_url('payments/edit/' . $paymentId) : base_url('payments');

        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Pembayaran sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dicatat!',
                'id'         => $paymentId,
                'payment_id' => $paymentId,
                'redirect'   => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', 'Pembayaran sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dicatat!');
    }

    /**
     * Edit Form
     */
    public function edit($id = null)
    {
        $id = (int) $id;
        $payment = $this->paymentModel->getPaymentDetails($id);
        if (!$payment) {
            throw PageNotFoundException::forPageNotFound('Data pembayaran tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $invoices = $db->table('invoices')
            ->select('
                invoices.id,
                invoices.invoice_number,
                invoices.grand_total,
                invoices.status,
                campaigns.title as campaign_title,
                kols.name as kol_name
            ')
            ->join('campaign_kols', 'campaign_kols.id = invoices.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('invoices.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Edit Pencairan Pembayaran #' . esc($payment['id']),
            'controller' => 'Payments',
            'payment'    => $payment,
            'invoices'   => $invoices,
        ];

        return view('payments/edit', $data);
    }

    /**
     * Simpan Perubahan Pembayaran
     */
    public function edit_save($id = null)
    {
        $id = (int) $id;
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data pembayaran tidak ditemukan.']);
        }

        $rules = [
            'invoice_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            $errors = implode(', ', $this->validator->getErrors());
            if ($this->isAjaxRequest()) {
                return $this->response->setJSON(['success' => false, 'message' => $errors]);
            }
            return redirect()->back()->withInput()->with('error', $errors);
        }

        $amountReq = $this->request->getPost('amount_paid') ?: $this->request->getPost('amount');
        $amount = (float) ($amountReq ?: $payment['amount_paid']);

        $updateData = [
            'invoice_id'     => (int) $this->request->getPost('invoice_id'),
            'amount_paid'    => $amount,
            'payment_method' => $this->request->getPost('payment_method') ?: $payment['payment_method'],
            'bank_reference' => trim((string) ($this->request->getPost('bank_reference') ?: $payment['bank_reference'])),
            'notes'          => trim((string) $this->request->getPost('notes')),
            'paid_at'        => $this->request->getPost('paid_at') ?: ($this->request->getPost('payment_date') ?: $payment['paid_at']),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        // File upload replacement
        $file = $this->request->getFile('transfer_receipt_file') ?: $this->request->getFile('payment_proof_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/payments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $updateData['transfer_receipt_file'] = 'uploads/payments/' . $newName;
        }

        $this->paymentModel->update($id, $updateData);

        // Recalculate status of old and new invoices
        $this->paymentModel->updateInvoicePaymentStatus((int)$payment['invoice_id']);
        if ((int)$updateData['invoice_id'] !== (int)$payment['invoice_id']) {
            $this->paymentModel->updateInvoicePaymentStatus((int)$updateData['invoice_id']);
        }

        $saveType = $this->request->getPost('save_type');
        $redirectUrl = ($saveType === 'stay') ? base_url('payments/edit/' . $id) : base_url('payments');

        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Data pembayaran berhasil diperbarui.',
                'id'       => $id,
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    /**
     * Hapus Pembayaran (Single or Batch)
     */
    public function delete($id = null)
    {
        $ids = [];
        if (!empty($id)) {
            $ids[] = (int) $id;
        }

        $postIds = $this->request->getPost('id') ?? $this->request->getGet('id');
        if (!empty($postIds)) {
            foreach ((array) $postIds as $i) {
                $ids[] = (int) $i;
            }
        }

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            if ($this->isAjaxRequest()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada data pembayaran yang dipilih.']);
            }
            return redirect()->back()->with('error', 'Tidak ada data pembayaran yang dipilih.');
        }

        $affectedInvoices = [];
        foreach ($ids as $delId) {
            $pay = $this->paymentModel->find($delId);
            if ($pay) {
                $affectedInvoices[] = (int)$pay['invoice_id'];
                if (!empty($pay['transfer_receipt_file'])) {
                    foreach (explode(',', $pay['transfer_receipt_file']) as $fn) {
                        $p = FCPATH . trim($fn);
                        if (is_file($p)) @unlink($p);
                    }
                }
                $this->paymentModel->delete($delId);
            }
        }

        foreach (array_unique($affectedInvoices) as $invId) {
            $this->paymentModel->updateInvoicePaymentStatus($invId);
        }

        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => count($ids) . ' pembayaran berhasil dihapus.',
            ]);
        }

        return redirect()->to(base_url('payments'))->with('success', count($ids) . ' pembayaran berhasil dihapus.');
    }

    /**
     * Bulk Delete AJAX Endpoint
     */
    public function bulkDelete()
    {
        return $this->delete();
    }

    /**
     * AJAX Get Outstanding Balance of Invoice
     */
    public function get_invoice_balance($invoiceId = null)
    {
        $invoiceId = (int) ($invoiceId ?: $this->request->getGet('invoice_id'));
        $balanceInfo = $this->paymentModel->getInvoiceBalance($invoiceId);

        if (!$balanceInfo) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Invoice tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success'        => true,
            'invoice_id'     => $balanceInfo['invoice_id'],
            'invoice_number' => $balanceInfo['invoice_number'],
            'campaign_title' => $balanceInfo['campaign_title'],
            'kol_name'       => $balanceInfo['kol_name'],
            'bank_info'      => ($balanceInfo['bank_name'] ?: '-') . ' ' . ($balanceInfo['bank_account'] ?: '') . ' (a/n ' . ($balanceInfo['bank_holder'] ?: '-') . ')',
            'grand_total'    => $balanceInfo['grand_total'],
            'total_paid'     => $balanceInfo['total_paid'],
            'balance'        => $balanceInfo['balance'],
        ]);
    }

    /**
     * Export Payments to Excel / CSV with UTF-8 BOM
     */
    public function export()
    {
        $filters = [
            'invoice_id'     => $this->request->getGet('invoice_id'),
            'campaign_id'    => $this->request->getGet('campaign_id'),
            'payment_method' => $this->request->getGet('payment_method'),
        ];

        $payments = $this->paymentModel->getAllForExport($filters);

        $filename = 'Payments_Export_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'No',
            'No. Invoice',
            'Kampanye',
            'Talent KOL',
            'Bank Tujuan',
            'No. Rekening',
            'Pemilik Rekening',
            'Nominal Pencairan (IDR)',
            'Metode Pembayaran',
            'No. Referensi Transfer',
            'Tanggal Pembayaran',
            'Diproses Oleh',
            'Catatan',
        ]);

        $no = 1;
        foreach ($payments as $p) {
            $campaignName = $p['campaign_title'] ?: ($p['campaign_name'] ?? '-');
            fputcsv($output, [
                $no++,
                $p['invoice_number'],
                $campaignName,
                $p['kol_name'] ?: '-',
                $p['bank_name'] ?: '-',
                $p['bank_account'] ?: '-',
                $p['bank_holder'] ?: '-',
                (float) ($p['amount_paid'] ?? $p['amount'] ?? 0),
                $p['payment_method'],
                $p['bank_reference'] ?: '-',
                !empty($p['paid_at']) ? date('Y-m-d H:i', strtotime($p['paid_at'])) : '-',
                $p['payer_name'] ?: 'Finance',
                $p['notes'] ?: '-',
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
     * Export Payments to Printable PDF Listing
     */
    public function export_pdf()
    {
        $filters = [
            'invoice_id'     => $this->request->getGet('invoice_id'),
            'campaign_id'    => $this->request->getGet('campaign_id'),
            'payment_method' => $this->request->getGet('payment_method'),
        ];

        $payments = $this->paymentModel->getAllForExport($filters);

        $data = [
            'title'    => 'Laporan Rekapitulasi Realisasi Pencairan Pembayaran KOL',
            'payments' => $payments,
            'date'     => date('d F Y, H:i'),
        ];

        return view('payments/export_pdf', $data);
    }

    /**
     * Single PDF View Alias
     */
    public function single_pdf($id = null)
    {
        return $this->receipt_pdf($id);
    }

    /**
     * Printable Official Payment Disbursement Voucher
     */
    public function receipt_pdf($id = null)
    {
        $id = (int) $id;
        $payment = $this->paymentModel->getPaymentDetails($id);
        if (!$payment) {
            throw PageNotFoundException::forPageNotFound('Data pembayaran tidak ditemukan.');
        }

        $data = [
            'title'   => 'Voucher Pengeluaran Dana #' . esc($payment['id']),
            'payment' => $payment,
        ];

        return view('payments/receipt_pdf', $data);
    }

    /**
     * Fine Uploader / AJAX File Upload Handler
     */
    public function upload_payment_proof_file_file()
    {
        $file = $this->request->getFile('qqfile') ?? $this->request->getFile('file');
        $uuid = $this->request->getPost('qquuid') ?? uniqid();

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $tmpDir = FCPATH . 'uploads/tmp/' . $uuid;
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }
            $origName = $file->getClientName();
            $file->move($tmpDir, $origName);

            return $this->response->setJSON([
                'success'    => true,
                'uploadName' => $origName,
                'uuid'       => $uuid,
            ]);
        }

        return $this->response->setJSON(['success' => false, 'error' => 'Gagal mengunggah file.']);
    }

    /**
     * Delete Temp/Saved File Endpoint
     */
    public function delete_payment_proof_file_file($uuid = null)
    {
        $uuid = $uuid ?: $this->request->getPost('uuid');
        if ($uuid) {
            $tmpDir = FCPATH . 'uploads/tmp/' . $uuid;
            if (is_dir($tmpDir)) {
                $files = glob($tmpDir . '/*');
                foreach ($files as $f) {
                    if (is_file($f)) @unlink($f);
                }
                @rmdir($tmpDir);
            }
        }
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Get Payment Proof File List
     */
    public function get_payment_proof_file_file($id = null)
    {
        $id = (int) $id;
        $payment = $this->paymentModel->find($id);
        $files = [];

        if ($payment && !empty($payment['transfer_receipt_file'])) {
            foreach (explode(',', $payment['transfer_receipt_file']) as $fn) {
                $fn = trim($fn);
                $files[] = [
                    'name'     => basename($fn),
                    'uuid'     => $id,
                    'endpoint' => base_url($fn),
                ];
            }
        }

        return $this->response->setJSON($files);
    }
}
