<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\CampaignModel;
use CodeIgniter\Exceptions\PageNotFoundException;

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
     * Get Current Authenticated User ID
     */
    protected function getUserId(): int
    {
        $session = session();
        return (int) ($session->get('user_id') ?? 1);
    }

    /**
     * Get KOL ID for Current User (if logged in user is a KOL)
     */
    protected function getCurrentKolId(): ?int
    {
        $session = session();
        if ($session->has('kol_id')) {
            return (int) $session->get('kol_id');
        }

        $userId = $this->getUserId();
        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('user_id', $userId)->get()->getRowArray();
        return $kol ? (int) $kol['id'] : null;
    }

    /**
     * Check if current request is AJAX
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
     * Halaman Utama Invoices & Finance
     */
    public function index($offset = 0)
    {
        if ($this->isAjaxRequest() && $this->request->getPost('draw') !== null) {
            return $this->getDatatable();
        }

        $db = \Config\Database::connect();
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, campaigns.name as campaign_name, kols.name as kol_name, kols.npwp')
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
            'allCampaigns' => $this->campaignModel->select('id, title, name')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('invoices/index', $data);
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
                InvoiceModel::STATUS_VERIFIED, InvoiceModel::STATUS_APPROVED => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-check me-1"></i>VERIFIED (SIAP CAIR)</span>',
                InvoiceModel::STATUS_PARTIAL => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-adjust me-1"></i>SEBAGIAN (PARTIAL)</span>',
                InvoiceModel::STATUS_SUBMITTED => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>SUBMITTED</span>',
                InvoiceModel::STATUS_REJECTED => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times me-1"></i>REJECTED</span>',
                InvoiceModel::STATUS_CANCELLED => '<span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1">CANCELLED</span>',
                default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">DRAFT / UNPAID</span>',
            };

            $taxInfo = '<span class="small text-muted">' . esc($row['tax_type']) . ' (' . ((float)$row['tax_rate'] * 100) . '%): Rp ' . number_format((float)$row['tax_amount'], 0, ',', '.') . '</span>';
            $campaignTitle = esc($row['campaign_title'] ?: ($row['campaign_name'] ?? 'N/A'));

            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . base_url('invoices/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Invoice"><i class="fas fa-eye"></i></a>
                    <a href="' . base_url('invoices/edit/' . $row['id']) . '" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2" title="Edit Invoice"><i class="fas fa-edit"></i></a>
                    <a href="' . base_url('invoices/print-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak PDF"><i class="fas fa-print"></i></a>
            ';

            if ($row['status'] === InvoiceModel::STATUS_SUBMITTED) {
                $actions .= '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Verifikasi Invoice" onclick="verifyInvoice(' . $row['id'] . ')"><i class="fas fa-check"></i></button>';
            }

            if (in_array($row['status'], [InvoiceModel::STATUS_VERIFIED, InvoiceModel::STATUS_APPROVED, InvoiceModel::STATUS_PARTIAL], true)) {
                $actions .= '<button type="button" class="btn btn-success btn-sm rounded-2 py-1 px-2" title="Proses Pencairan / Bayar" onclick="payInvoiceModal(' . $row['id'] . ')"><i class="fas fa-money-bill-wave"></i></button>';
            }

            $actions .= '
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Invoice" onclick="deleteInvoice(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                </div>
            ';

            $formattedData[] = [
                'id'          => $row['id'],
                'inv_number'  => '
                    <div class="fw-bold font-monospace text-primary">' . esc($row['invoice_number']) . '</div>
                    <small class="text-muted">' . date('d M Y', strtotime($row['created_at'])) . '</small>
                ',
                'campaign'    => '<div class="fw-semibold text-dark">' . $campaignTitle . '</div>',
                'kol'         => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">' . esc($row['bank_name'] ?: '-') . ' ' . esc($row['bank_account'] ?: '') . '</small>
                ',
                'subtotal'    => 'Rp ' . number_format((float)$row['subtotal'], 0, ',', '.'),
                'tax'         => $taxInfo,
                'grand_total' => '<strong class="text-success fs-6">Rp ' . number_format((float)$row['grand_total'], 0, ',', '.') . '</strong>',
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
     * View Detail Invoice Page
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $invoice = $this->invoiceModel->getInvoiceDetails($id);
        if (!$invoice) {
            throw PageNotFoundException::forPageNotFound('Invoice tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Invoice #' . esc($invoice['invoice_number']),
            'controller' => 'Invoices',
            'invoice'    => $invoice,
        ];

        return view('invoices/view', $data);
    }

    /**
     * Form Tambah Invoice Baru
     */
    public function add()
    {
        $db = \Config\Database::connect();
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, campaigns.name as campaign_name, kols.name as kol_name, kols.npwp')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'                => 'Terbitkan Invoice Baru',
            'controller'           => 'Invoices',
            'assignedKols'         => $assignedKols,
            'generated_invoice_no' => $this->invoiceModel->generateLegacyInvoiceNumber(),
        ];

        return view('invoices/add', $data);
    }

    /**
     * Simpan Tambah Invoice Baru
     */
    public function add_save()
    {
        $rules = [
            'campaign_kol_id' => 'required',
            'subtotal'        => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            $errors = implode(', ', $this->validator->getErrors());
            if ($this->isAjaxRequest()) {
                return $this->response->setJSON(['success' => false, 'message' => $errors]);
            }
            return redirect()->back()->withInput()->with('error', $errors);
        }

        $campaignKolId = (int) $this->request->getPost('campaign_kol_id');
        $subtotal      = (float) $this->request->getPost('subtotal');
        $taxAmountReq  = $this->request->getPost('tax_amount');
        $taxType       = $this->request->getPost('tax_type') ?: InvoiceModel::TAX_PPH21;
        $dueDate       = $this->request->getPost('due_date') ?: date('Y-m-d', strtotime('+14 days'));

        $db = \Config\Database::connect();
        $ck = $db->table('campaign_kols')
            ->select('campaign_kols.*, kols.npwp')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('campaign_kols.id', $campaignKolId)
            ->get()
            ->getRowArray();

        $hasNpwp = !empty($ck['npwp']);
        $taxCalc = InvoiceModel::calculateTax($subtotal, $taxType, $hasNpwp);

        $taxAmount  = ($taxAmountReq !== null && $taxAmountReq !== '') ? (float) $taxAmountReq : $taxCalc['tax_amount'];
        $grandTotal = max(0, $subtotal - $taxAmount);

        $invNoInput = trim((string) $this->request->getPost('invoice_no'));
        if (empty($invNoInput) || $invNoInput === '[AUTO GENERATED]') {
            $invoiceNumber = $this->invoiceModel->generateLegacyInvoiceNumber();
        } else {
            $invoiceNumber = $invNoInput;
        }

        // Upload handler
        $uploadedFileName = null;
        $file = $this->request->getFile('invoice_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/invoices';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $uploadedFileName = 'uploads/invoices/' . $newName;
        }

        // Handle fine-uploader temporary copy if provided
        $fineNames = (array) $this->request->getPost('invoices_invoice_file_name');
        $fineUuids = (array) $this->request->getPost('invoices_invoice_file_uuid');
        if (!empty($fineNames)) {
            $uploadDir = FCPATH . 'uploads/invoices';
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
                        $collected[] = 'uploads/invoices/' . $destName;
                    }
                } else {
                    $collected[] = $fn;
                }
            }
            if (!empty($collected)) {
                $uploadedFileName = implode(',', $collected);
            }
        }

        $userId = $this->getUserId();

        $insertData = [
            'campaign_kol_id' => $campaignKolId,
            'invoice_number'  => $invoiceNumber,
            'subtotal'        => $subtotal,
            'tax_type'        => $taxCalc['tax_type'],
            'tax_rate'        => $taxCalc['tax_rate'],
            'tax_amount'      => $taxAmount,
            'grand_total'     => $grandTotal,
            'status'          => InvoiceModel::STATUS_SUBMITTED,
            'due_date'        => $dueDate,
            'invoice_file'    => $uploadedFileName,
            'created_by'      => $userId,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $invoiceId = $this->invoiceModel->insert($insertData, true);

        $saveType = $this->request->getPost('save_type');
        $redirectUrl = ($saveType === 'stay') ? base_url('invoices/edit/' . $invoiceId) : base_url('invoices');

        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON([
                'success'        => true,
                'message'        => 'Invoice ' . $invoiceNumber . ' berhasil dibuat.',
                'id'             => $invoiceId,
                'invoice_id'     => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'redirect'       => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', 'Invoice ' . $invoiceNumber . ' berhasil dibuat.');
    }

    /**
     * Auto generate invoice from deal (modern + legacy alias)
     */
    public function create_from_deal($campaignKolId)
    {
        return $this->createFromDeal((int) $campaignKolId);
    }

    public function createFromDeal(int $campaignKolId)
    {
        $db = \Config\Database::connect();

        // Cek jika invoice sudah pernah diterbitkan
        $existing = $db->table('invoices')
            ->where('campaign_kol_id', $campaignKolId)
            ->whereNotIn('status', [InvoiceModel::STATUS_CANCELLED])
            ->get()
            ->getRowArray();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice untuk deal penugasan ini sudah pernah diterbitkan (#' . ($existing['invoice_number'] ?? $existing['id']) . ').',
            ]);
        }

        $ck = $db->table('campaign_kols')
            ->select('campaign_kols.*, kols.npwp')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('campaign_kols.id', $campaignKolId)
            ->get()
            ->getRowArray();

        if (!$ck) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Penugasan KOL tidak ditemukan.']);
        }

        if (empty($ck['deal_amount']) || (float) $ck['deal_amount'] <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nilai deal amount kosong atau nol.']);
        }

        $subtotal = (float) $ck['deal_amount'];
        $hasNpwp  = !empty($ck['npwp']);
        $taxType  = $this->request->getPost('tax_type') ?: InvoiceModel::TAX_PPH21;

        $taxCalc = InvoiceModel::calculateTax($subtotal, $taxType, $hasNpwp);
        $invoiceNumber = $this->invoiceModel->generateLegacyInvoiceNumber();
        $userId = $this->getUserId();

        $invoiceData = [
            'campaign_kol_id' => $campaignKolId,
            'invoice_number'  => $invoiceNumber,
            'subtotal'        => $subtotal,
            'tax_type'        => $taxCalc['tax_type'],
            'tax_rate'        => $taxCalc['tax_rate'],
            'tax_amount'      => $taxCalc['tax_amount'],
            'grand_total'     => $taxCalc['grand_total'],
            'status'          => InvoiceModel::STATUS_SUBMITTED,
            'due_date'        => date('Y-m-d', strtotime('+14 days')),
            'created_by'      => $userId,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $invoiceId = $this->invoiceModel->insert($invoiceData, true);

        return $this->response->setJSON([
            'success'        => true,
            'message'        => 'Invoice ' . $invoiceNumber . ' berhasil diterbitkan otomatis dari deal!',
            'invoice_id'     => $invoiceId,
            'invoice_number' => $invoiceNumber,
            'redirect'       => base_url('invoices'),
        ]);
    }

    /**
     * Edit Invoice Form
     */
    public function edit($id = null)
    {
        $id = (int) $id;
        $invoice = $this->invoiceModel->getInvoiceDetails($id);
        if (!$invoice) {
            throw PageNotFoundException::forPageNotFound('Invoice tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, campaigns.name as campaign_name, kols.name as kol_name')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'        => 'Edit Invoice #' . esc($invoice['invoice_number']),
            'controller'   => 'Invoices',
            'invoice'      => $invoice,
            'assignedKols' => $assignedKols,
        ];

        return view('invoices/edit', $data);
    }

    /**
     * Simpan Perubahan Invoice
     */
    public function edit_save($id = null)
    {
        $id = (int) $id;
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice tidak ditemukan.']);
        }

        $rules = [
            'campaign_kol_id' => 'required',
            'subtotal'        => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            $errors = implode(', ', $this->validator->getErrors());
            if ($this->isAjaxRequest()) {
                return $this->response->setJSON(['success' => false, 'message' => $errors]);
            }
            return redirect()->back()->withInput()->with('error', $errors);
        }

        $subtotal     = (float) $this->request->getPost('subtotal');
        $taxAmount    = (float) ($this->request->getPost('tax_amount') ?? 0);
        $taxType      = $this->request->getPost('tax_type') ?: ($invoice['tax_type'] ?? InvoiceModel::TAX_PPH21);
        $dueDate      = $this->request->getPost('due_date') ?: $invoice['due_date'];
        $status       = $this->request->getPost('status') ?: $invoice['status'];
        $grandTotal   = max(0, $subtotal - $taxAmount);

        $updateData = [
            'campaign_kol_id' => (int) $this->request->getPost('campaign_kol_id'),
            'subtotal'        => $subtotal,
            'tax_amount'      => $taxAmount,
            'tax_type'        => $taxType,
            'grand_total'     => $grandTotal,
            'due_date'        => $dueDate,
            'status'          => $status,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // Invoice number update if explicitly allowed
        $invNoInput = trim((string) $this->request->getPost('invoice_no'));
        if (!empty($invNoInput) && $invNoInput !== $invoice['invoice_number']) {
            $updateData['invoice_number'] = $invNoInput;
        }

        // Upload handler for replacement
        $file = $this->request->getFile('invoice_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/invoices';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $updateData['invoice_file'] = 'uploads/invoices/' . $newName;
        }

        $fineNames = (array) $this->request->getPost('invoices_invoice_file_name');
        $fineUuids = (array) $this->request->getPost('invoices_invoice_file_uuid');
        if (!empty($fineNames)) {
            $uploadDir = FCPATH . 'uploads/invoices';
            $collected = [];
            foreach ($fineNames as $idx => $fn) {
                if (!empty($fineUuids[$idx])) {
                    $tmpPath = FCPATH . 'uploads/tmp/' . $fineUuids[$idx] . '/' . $fn;
                    $destName = date('YmdHis') . '-' . $fn;
                    if (is_file($tmpPath)) {
                        rename($tmpPath, $uploadDir . '/' . $destName);
                        $collected[] = 'uploads/invoices/' . $destName;
                    }
                } else {
                    $collected[] = $fn;
                }
            }
            if (!empty($collected)) {
                $updateData['invoice_file'] = implode(',', $collected);
            }
        }

        $this->invoiceModel->update($id, $updateData);

        $saveType = $this->request->getPost('save_type');
        $redirectUrl = ($saveType === 'stay') ? base_url('invoices/edit/' . $id) : base_url('invoices');

        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Invoice berhasil diperbarui.',
                'id'       => $id,
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', 'Invoice berhasil diperbarui.');
    }

    /**
     * Hapus Invoice (single or batch)
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
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada invoice yang dipilih untuk dihapus.']);
            }
            return redirect()->back()->with('error', 'Tidak ada invoice yang dipilih untuk dihapus.');
        }

        foreach ($ids as $delId) {
            $inv = $this->invoiceModel->find($delId);
            if ($inv && !empty($inv['invoice_file'])) {
                foreach (explode(',', $inv['invoice_file']) as $fn) {
                    $fn = trim($fn);
                    $path = FCPATH . $fn;
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
            $this->invoiceModel->delete($delId);
        }

        if ($this->isJsonOrAjax()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => count($ids) . ' invoice berhasil dihapus.',
            ]);
        }

        return redirect()->to(base_url('invoices'))->with('success', count($ids) . ' invoice berhasil dihapus.');
    }

    /**
     * Bulk Delete AJAX Endpoint
     */
    public function bulkDelete()
    {
        return $this->delete();
    }

    /**
     * Export Invoices to Excel / CSV with UTF-8 BOM
     */
    public function export()
    {
        $filters = [
            'status'      => $this->request->getGet('status'),
            'campaign_id' => $this->request->getGet('campaign_id'),
        ];

        $invoices = $this->invoiceModel->getAllForExport($filters);

        $filename = 'Invoices_Export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header CSV
        fputcsv($output, [
            'No',
            'No. Invoice',
            'Kampanye',
            'Brand',
            'Talent KOL',
            'Bank',
            'No. Rekening',
            'Pemilik Rekening',
            'NPWP',
            'Gross Subtotal (IDR)',
            'Jenis Pajak',
            'Rate Pajak',
            'Potongan Pajak (IDR)',
            'Net Ditransfer (IDR)',
            'Status',
            'Total Terbayar (IDR)',
            'Jatuh Tempo',
            'Tanggal Terbit',
        ]);

        $no = 1;
        foreach ($invoices as $inv) {
            $campaignName = $inv['campaign_title'] ?: ($inv['campaign_name'] ?? '-');
            fputcsv($output, [
                $no++,
                $inv['invoice_number'],
                $campaignName,
                $inv['brand_name'] ?: '-',
                $inv['kol_name'] ?: '-',
                $inv['bank_name'] ?: '-',
                $inv['bank_account'] ?: '-',
                $inv['bank_holder'] ?: '-',
                $inv['npwp_number'] ?: '-',
                (float) $inv['subtotal'],
                $inv['tax_type'] ?: 'PPh21',
                ((float) $inv['tax_rate'] * 100) . '%',
                (float) $inv['tax_amount'],
                (float) $inv['grand_total'],
                $inv['status'],
                (float) $inv['total_paid'],
                $inv['due_date'] ? date('Y-m-d', strtotime($inv['due_date'])) : '-',
                $inv['created_at'] ? date('Y-m-d H:i', strtotime($inv['created_at'])) : '-',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export Invoices to Printable PDF Listing
     */
    public function export_pdf()
    {
        $filters = [
            'status'      => $this->request->getGet('status'),
            'campaign_id' => $this->request->getGet('campaign_id'),
        ];

        $invoices = $this->invoiceModel->getAllForExport($filters);

        $data = [
            'title'    => 'Laporan Rekapitulasi Invoice Tagihan KOL',
            'invoices' => $invoices,
            'date'     => date('d F Y, H:i'),
        ];

        return view('invoices/export_pdf', $data);
    }

    /**
     * Single PDF View
     */
    public function single_pdf($id = null)
    {
        return $this->print_pdf($id);
    }

    /**
     * Printable Official PDF Invoice Layout
     */
    public function print_pdf($id = null)
    {
        $id = (int) $id;
        $invoice = $this->invoiceModel->getInvoiceDetails($id);
        if (!$invoice) {
            throw PageNotFoundException::forPageNotFound('Invoice tidak ditemukan.');
        }

        $data = [
            'title'   => 'Invoice Tagihan #' . esc($invoice['invoice_number']),
            'invoice' => (object) $invoice,
        ];

        return view('invoices/print_pdf', $data);
    }

    /**
     * Portal Tagihan Saya (KOL Self-Service View)
     */
    public function my_invoices()
    {
        $kolId = $this->getCurrentKolId();

        $db = \Config\Database::connect();
        $invoices = [];
        $unlinkedKol = false;

        if (!$kolId) {
            // Jika admin/staff membuka halaman ini, tampilkan semua invoice atau data preview
            $unlinkedKol = false;
            $invoices = $this->invoiceModel->getAllForExport();
        } else {
            $invoices = $this->invoiceModel->getByKol($kolId);
        }

        $data = [
            'title'        => 'Tagihan Saya — Portal Pembayaran KOL',
            'controller'   => 'Invoices',
            'invoices'     => $invoices,
            'unlinked_kol' => $unlinkedKol,
            'kol_id'       => $kolId,
        ];

        return view('invoices/my_invoices', $data);
    }

    /**
     * Fine Uploader / AJAX File Upload Handler
     */
    public function upload_invoice_file_file()
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
    public function delete_invoice_file_file($uuid = null)
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
     * Get Invoice File List
     */
    public function get_invoice_file_file($id = null)
    {
        $id = (int) $id;
        $inv = $this->invoiceModel->find($id);
        $files = [];

        if ($inv && !empty($inv['invoice_file'])) {
            foreach (explode(',', $inv['invoice_file']) as $fn) {
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

    /**
     * Verify Invoice (Staff / Leader Approval)
     */
    public function verify(int $id)
    {
        $userId = $this->getUserId();

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
     * Save Payment Disbursement
     */
    public function savePayment(int $id)
    {
        $userId = $this->getUserId();

        $inv = $this->invoiceModel->find($id);
        if (!$inv) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Invoice tidak ditemukan']);
        }

        $amount      = (float) ($this->request->getPost('amount_paid') ?: $inv['grand_total']);
        $method      = $this->request->getPost('payment_method') ?: 'BANK_TRANSFER';
        $bankRef     = trim((string) $this->request->getPost('bank_reference'));
        $receiptFile = trim((string) $this->request->getPost('transfer_receipt_file'));

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
