<?php

namespace App\Controllers;

use App\Models\CampaignApplicationModel;
use App\Models\CampaignModel;
use App\Models\ContractModel;
use App\Models\InvoiceModel;
use App\Models\KolModel;
use App\Models\KolPlatformModel;
use App\Models\KolRateCardModel;
use App\Models\TaskModel;
use CodeIgniter\HTTP\ResponseInterface;

class Talent extends BaseController
{
    protected KolModel $kolModel;
    protected KolPlatformModel $kolPlatformModel;
    protected KolRateCardModel $rateCardModel;
    protected CampaignModel $campaignModel;
    protected CampaignApplicationModel $applicationModel;
    protected TaskModel $taskModel;
    protected ContractModel $contractModel;
    protected InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->kolModel          = new KolModel();
        $this->kolPlatformModel  = new KolPlatformModel();
        $this->rateCardModel     = new KolRateCardModel();
        $this->campaignModel     = new CampaignModel();
        $this->applicationModel  = new CampaignApplicationModel();
        $this->taskModel         = new TaskModel();
        $this->contractModel     = new ContractModel();
        $this->invoiceModel      = new InvoiceModel();
    }

    /**
     * Resolve current user's linked KOL record ID
     */
    protected function getMyKolId(): ?int
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 0);

        if ($userId > 0) {
            $kol = $this->kolModel->where('user_id', $userId)->first();
            if ($kol) {
                return (int) $kol['id'];
            }
        }

        // Fallback for demo or Superadmin preview
        $first = $this->kolModel->orderBy('id', 'ASC')->first();
        return $first ? (int) $first['id'] : null;
    }

    /**
     * Profil & Performance Card Saya
     */
    public function myProfile()
    {
        $kolId = $this->getMyKolId();
        $kol   = $kolId ? $this->kolModel->getKolDetail($kolId) : null;

        $platforms = $kolId ? $this->kolPlatformModel->getByKol($kolId) : [];
        $rates     = $kolId ? $this->rateCardModel->getByKol($kolId) : [];

        $data = [
            'title'      => 'Profil & Ruang Kerja Kreator',
            'controller' => 'Talent Space',
            'kol'        => $kol,
            'platforms'  => $platforms,
            'rates'      => $rates,
        ];

        return view('dashboard/my_profile', $data);
    }

    /**
     * Update Profil Kreator Mandiri (POST)
     */
    public function updateProfile()
    {
        $kolId = $this->getMyKolId();
        if (!$kolId) {
            return redirect()->back()->with('error', 'Profil KOL Anda tidak ditemukan.');
        }

        $postData = [
            'name'         => $this->request->getPost('name'),
            'city'         => $this->request->getPost('city'),
            'whatsapp'     => $this->request->getPost('whatsapp'),
            'phone'        => $this->request->getPost('phone'),
            'email'        => $this->request->getPost('email'),
            'address'      => $this->request->getPost('address'),
            'postal_code'  => $this->request->getPost('postal_code'),
            'bank_name'    => $this->request->getPost('bank_name'),
            'bank_account' => $this->request->getPost('bank_account'),
            'bank_holder'  => $this->request->getPost('bank_holder'),
            'npwp'         => $this->request->getPost('npwp'),
            'notes'        => $this->request->getPost('notes'),
        ];

        // Filter null/empty entries
        $updateData = array_filter($postData, static fn($val) => $val !== null);

        if ($this->kolModel->update($kolId, $updateData)) {
            return redirect()->back()->with('success', 'Profil kreator berhasil diperbarui!');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui profil: ' . implode(', ', $this->kolModel->errors()));
    }

    /**
     * Kampanye Terbuka & Peluang Kolaborasi
     */
    public function openCampaigns()
    {
        $kolId     = $this->getMyKolId();
        $campaigns = $this->campaignModel->getOpenCampaigns($kolId);

        $data = [
            'title'      => 'Kampanye Terbuka & Peluang Kolaborasi',
            'controller' => 'Talent Space',
            'campaigns'  => $campaigns,
            'kolId'      => $kolId,
        ];

        return view('talent/open_campaigns', $data);
    }

    /**
     * Ajukan Lamaran Kolaborasi (AJAX POST)
     */
    public function applyCampaign(): ResponseInterface
    {
        $kolId = $this->getMyKolId();
        if (!$kolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda harus terdaftar sebagai profil Talent KOL terlebih dahulu.',
            ]);
        }

        $campaignId   = (int) $this->request->getPost('campaign_id');
        $proposedRate = (float) $this->request->getPost('proposed_rate');
        $pitchNotes   = trim($this->request->getPost('pitch_notes') ?? '');

        if (!$campaignId || $proposedRate <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan masukkan nominal tarif penawaran yang valid.',
            ]);
        }

        $campaign = $this->campaignModel->where('id', $campaignId)->where('status', 'ACTIVE')->first();
        if (!$campaign) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kampanye tidak ditemukan atau status sudah tidak aktif menerima kreator.',
            ]);
        }

        if ($this->applicationModel->hasApplied($campaignId, $kolId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda sudah mengajukan lamaran untuk kampanye ini.',
            ]);
        }

        $this->applicationModel->insert([
            'campaign_id'   => $campaignId,
            'kol_id'        => $kolId,
            'proposed_rate' => $proposedRate,
            'pitch_notes'   => $pitchNotes,
            'status'        => 'APPLIED',
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lamaran kolaborasi berhasil diajukan! Tim brand akan meninjau penawaran Anda.',
        ]);
    }

    /**
     * Lamaran Saya (Tracking Status)
     */
    public function myApplications()
    {
        $kolId        = $this->getMyKolId();
        $applications = $kolId ? $this->applicationModel->getApplicationsByKol($kolId) : [];

        $data = [
            'title'        => 'Status Lamaran Kampanye Saya',
            'controller'   => 'Talent Space',
            'applications' => $applications,
        ];

        return view('talent/my_applications', $data);
    }

    /**
     * Tugas & Deliverables Saya
     */
    public function myTasks()
    {
        $kolId = $this->getMyKolId();
        $tasks = $kolId ? $this->taskModel->getTasksByKol($kolId) : [];

        $data = [
            'title'      => 'Daftar Tugas & Deadline Konten Saya',
            'controller' => 'Talent Space',
            'tasks'      => $tasks,
        ];

        return view('talent/my_tasks', $data);
    }

    /**
     * Kontrak Kerja Sama Saya
     */
    public function myContracts()
    {
        $kolId     = $this->getMyKolId();
        $contracts = $kolId ? $this->contractModel->getContractsByKol($kolId) : [];

        $data = [
            'title'      => 'Kontrak Kerja Sama Saya',
            'controller' => 'Talent Space',
            'contracts'  => $contracts,
        ];

        return view('talent/my_contracts', $data);
    }

    /**
     * AJAX: Ambil HTML klausul + status kontrak berdasarkan ID
     */
    public function getContractAjax(int $id): ResponseInterface
    {
        $kolId = $this->getMyKolId();
        if (! $kolId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $contracts = $this->contractModel->getContractsByKol($kolId);
        $contract  = null;
        foreach ($contracts as $c) {
            if ((int) $c['id'] === $id) { $contract = $c; break; }
        }

        if (! $contract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan']);
        }

        $isSigned     = ! empty($contract['signed_at']);
        $rawStatus    = $contract['contract_status'] ?? ($isSigned ? 'SIGNED' : 'PENDING');
        $status       = ($isSigned || in_array($rawStatus, ['SIGNED', 'ACTIVE', 'COMPLETED'])) ? 'SIGNED' : $rawStatus;

        // Signature image URL / data
        $sigImg = null;
        if (! empty($contract['signature_image'])) {
            $sigImg = str_starts_with($contract['signature_image'], 'data:')
                ? $contract['signature_image']
                : base_url($contract['signature_image']);
        }

        return $this->response->setJSON([
            'success'         => true,
            'html'            => $contract['clauses_rendered'] ?? '',
            'status'          => $status,
            'kol_name'        => session('name') ?? 'KOL / Creator',
            'signature_image' => $sigImg,
            'contract_number' => $contract['contract_number'] ?? ('CTR-' . $contract['id']),
            'signed_at'       => $contract['signed_at'] ? date('d M Y, H:i', strtotime($contract['signed_at'])) : null,
        ]);
    }

    /**
     * AJAX POST: Simpan e-signature (draw) atau upload file tanda tangan
     */
    public function signContract(): ResponseInterface
    {
        $kolId = $this->getMyKolId();
        if (! $kolId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id');
        if (! $id) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID kontrak tidak valid']);
        }

        // Pastikan kontrak milik KOL ini
        $contracts = $this->contractModel->getContractsByKol($kolId);
        $contract  = null;
        foreach ($contracts as $c) {
            if ((int) $c['id'] === $id) { $contract = $c; break; }
        }
        if (! $contract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan']);
        }
        if (! empty($contract['signed_at'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kontrak sudah ditandatangani sebelumnya']);
        }

        $signatureImage = null;

        // Option 1: draw (base64 data URL)
        $sig = $this->request->getPost('signature');
        if ($sig && str_starts_with($sig, 'data:image/')) {
            $signatureImage = $sig;
        }

        // Option 2: upload file
        $file = $this->request->getFile('contract_file');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/signatures/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $newName = 'sign_' . $id . '_' . time() . '.' . $file->getClientExtension();
            $file->move($uploadDir, $newName);
            $signatureImage = 'uploads/signatures/' . $newName;
        }

        if (! $signatureImage) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tanda tangan tidak ditemukan']);
        }

        $now = date('Y-m-d H:i:s');
        $db  = \Config\Database::connect();
        $db->table('campaign_kol_contracts')->where('id', $id)->update([
            'signature_image' => $signatureImage,
            'signed_at'       => $now,
            'signed_ip'       => $this->request->getIPAddress(),
            'updated_at'      => $now,
        ]);

        if (! empty($contract['campaign_kol_id'])) {
            $db->table('campaign_kols')->where('id', $contract['campaign_kol_id'])->update([
                'contract_status' => 'SIGNED',
                'updated_at'      => $now,
            ]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Kontrak berhasil ditandatangani']);
    }

    /**
     * Rate Card Saya & Portofolio Tarif
     */
    public function myRates()
    {
        $kolId     = $this->getMyKolId();
        $rates     = $kolId ? $this->rateCardModel->getByKol($kolId) : [];
        $platforms = $kolId ? $this->kolPlatformModel->getByKol($kolId) : [];

        $data = [
            'title'          => 'Rate Card & Portofolio Tarif Saya',
            'controller'     => 'Talent Space',
            'rates'          => $rates,
            'platforms'      => $platforms,
            'canonicalTypes' => KolRateCardModel::getCanonicalTypes(),
        ];

        return view('talent/my_rates', $data);
    }

    /**
     * Simpan / Tambah Rate Card Baru (POST)
     */
    public function saveRate()
    {
        $kolId = $this->getMyKolId();
        if (!$kolId) {
            return redirect()->back()->with('error', 'Profil KOL belum terhubung.');
        }

        $platformId  = (int) $this->request->getPost('platform_id');
        $contentType = trim($this->request->getPost('content_type') ?? '');
        $ratePrice   = (float) $this->request->getPost('rate_price');
        $negotiable  = (int) ($this->request->getPost('negotiable') ? 1 : 0);
        $notes       = trim($this->request->getPost('notes') ?? '');

        if (!$contentType || $ratePrice <= 0) {
            return redirect()->back()->with('error', 'Jenis konten dan tarif harga wajib diisi.');
        }

        $this->rateCardModel->insert([
            'kol_id'       => $kolId,
            'platform_id'  => $platformId > 0 ? $platformId : null,
            'content_type' => $contentType,
            'rate_price'   => $ratePrice,
            'negotiable'   => $negotiable,
            'notes'        => $notes,
        ]);

        return redirect()->back()->with('success', 'Rate card berhasil disimpan!');
    }

    /**
     * Hapus Rate Card (GET/POST)
     */
    public function deleteRate(int $id)
    {
        $kolId = $this->getMyKolId();
        $rate  = $this->rateCardModel->where('id', $id)->where('kol_id', $kolId)->first();

        if (!$rate) {
            return redirect()->back()->with('error', 'Rate card tidak ditemukan.');
        }

        $this->rateCardModel->delete($id);
        return redirect()->back()->with('success', 'Rate card berhasil dihapus.');
    }

    /**
     * Tagihan & Invoices Saya
     */
    public function myInvoices()
    {
        $kolId         = $this->getMyKolId();
        $invoices      = $kolId ? $this->invoiceModel->getInvoicesByKol($kolId) : [];
        $billableDeals = $kolId ? $this->invoiceModel->getBillableDealsByKol($kolId) : [];
        $kol           = $kolId ? $this->kolModel->find($kolId) : null;

        $data = [
            'title'         => 'Tagihan Saya (Invoices)',
            'controller'    => 'Talent Space',
            'invoices'      => $invoices,
            'billableDeals' => $billableDeals,
            'kol'           => $kol,
        ];

        return view('talent/my_invoices', $data);
    }

    /**
     * AJAX: Ambil rincian kerja sama & estimasi simulasi pajak
     */
    public function getDealDetailAjax(int $campaignKolId): ResponseInterface
    {
        $kolId = $this->getMyKolId();
        if (! $kolId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $deal = $this->invoiceModel->getDealDetail($campaignKolId, $kolId);
        if (! $deal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data kerja sama tidak ditemukan.']);
        }

        // Check if there is an existing active invoice
        $existing = $this->invoiceModel
            ->where('campaign_kol_id', $campaignKolId)
            ->whereIn('status', ['SUBMITTED', 'VERIFIED', 'PAID'])
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kerja sama ini sudah memiliki tagihan aktif (' . ($existing['invoice_number'] ?? '#') . ').',
            ]);
        }

        $subtotal = (float) $deal['deal_amount'];
        $hasNpwp  = ! empty(trim((string) ($deal['npwp'] ?? '')));

        // PPh 21 estimation: 2.5% with NPWP, 3.0% without NPWP
        $taxType    = 'PPh21';
        $taxRatePct = $hasNpwp ? 2.50 : 3.00;
        $taxRate    = $taxRatePct / 100;
        $taxAmount  = round($subtotal * $taxRate, 2);
        $netAmount  = max(0, $subtotal - $taxAmount);

        return $this->response->setJSON([
            'success' => true,
            'deal'    => [
                'campaign_kol_id' => (int) $deal['campaign_kol_id'],
                'campaign_title'  => $deal['campaign_title'],
                'subtotal'        => $subtotal,
                'subtotal_fmt'    => number_format($subtotal, 0, ',', '.'),
                'has_npwp'        => $hasNpwp,
                'npwp'            => $deal['npwp'] ?: '-',
                'tax_type'        => $taxType,
                'tax_rate_pct'    => $taxRatePct,
                'tax_amount'      => $taxAmount,
                'tax_amount_fmt'  => number_format($taxAmount, 0, ',', '.'),
                'net_amount'      => $netAmount,
                'net_amount_fmt'  => number_format($netAmount, 0, ',', '.'),
                'bank_name'       => $deal['bank_name'] ?? '',
                'bank_account'    => $deal['bank_account'] ?? '',
                'bank_holder'     => $deal['bank_holder'] ?? '',
            ],
        ]);
    }

    /**
     * AJAX POST: Buat & Ajukan Tagihan Baru
     */
    public function submitInvoice(): ResponseInterface
    {
        $kolId = $this->getMyKolId();
        if (! $kolId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $campaignKolId = (int) $this->request->getPost('campaign_kol_id');
        $bankName      = trim((string) $this->request->getPost('bank_name'));
        $bankAccount   = trim((string) $this->request->getPost('bank_account'));
        $bankHolder    = trim((string) $this->request->getPost('bank_holder'));

        if (! $campaignKolId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih kerja sama / campaign yang ingin ditagihkan.']);
        }

        if (empty($bankName) || empty($bankAccount) || empty($bankHolder)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data rekening bank pembayaran wajib diisi lengkap.']);
        }

        $deal = $this->invoiceModel->getDealDetail($campaignKolId, $kolId);
        if (! $deal) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kerja sama tidak ditemukan atau bukan milik Anda.']);
        }

        $subtotal = (float) $deal['deal_amount'];
        if ($subtotal <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nominal kerja sama tidak valid atau bernilai 0.']);
        }

        // Check if there is already an active invoice for this deal
        $existing = $this->invoiceModel
            ->where('campaign_kol_id', $campaignKolId)
            ->whereIn('status', ['SUBMITTED', 'VERIFIED', 'PAID'])
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kerja sama ini sudah memiliki tagihan aktif (' . ($existing['invoice_number'] ?? '#') . '). Tidak dapat membuat tagihan ganda.',
            ]);
        }

        // Update KOL bank information if changed
        $kol = $this->kolModel->find($kolId);
        $updateKol = [];
        if (($kol['bank_name'] ?? '') !== $bankName) $updateKol['bank_name'] = $bankName;
        if (($kol['bank_account'] ?? '') !== $bankAccount) $updateKol['bank_account'] = $bankAccount;
        if (($kol['bank_holder'] ?? '') !== $bankHolder) $updateKol['bank_holder'] = $bankHolder;
        if (! empty($updateKol)) {
            $this->kolModel->update($kolId, $updateKol);
        }

        // Handle optional uploaded invoice file
        $invoiceFile = null;
        $file = $this->request->getFile('invoice_file');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/invoices/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $newName = 'inv_' . time() . '_' . $file->getRandomName();
            $file->move($uploadDir, $newName);
            $invoiceFile = 'uploads/invoices/' . $newName;
        }

        // Tax calculation
        $hasNpwp    = ! empty(trim((string) ($kol['npwp'] ?? '')));
        $taxType    = 'PPh21';
        $taxRatePct = $hasNpwp ? 2.50 : 3.00;
        $taxRate    = $taxRatePct / 100;
        $taxAmount  = round($subtotal * $taxRate, 2);
        $grandTotal = max(0, $subtotal - $taxAmount);

        $invoiceNumber = $this->invoiceModel->generateInvoiceNumber();

        $invoiceData = [
            'campaign_kol_id' => $campaignKolId,
            'invoice_number'  => $invoiceNumber,
            'subtotal'        => $subtotal,
            'tax_type'        => $taxType,
            'tax_rate'        => $taxRate,
            'tax_amount'      => $taxAmount,
            'grand_total'     => $grandTotal,
            'status'          => 'SUBMITTED',
            'invoice_file'    => $invoiceFile,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $invoiceId = $this->invoiceModel->insert($invoiceData);

        // Record history
        $db = \Config\Database::connect();
        $notes = 'Invoice dibuat dan diajukan oleh Talent. Subtotal: Rp ' . number_format($subtotal, 0, ',', '.') .
                 ', Potongan Pajak (' . $taxType . ' ' . $taxRatePct . '%): Rp ' . number_format($taxAmount, 0, ',', '.') .
                 ', Net Transfer: Rp ' . number_format($grandTotal, 0, ',', '.');

        $db->table('invoice_histories')->insert([
            'invoice_id'  => $invoiceId,
            'from_status' => 'DRAFT',
            'to_status'   => 'SUBMITTED',
            'changed_by'  => session('user_id') ?? 1,
            'notes'       => $notes,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tagihan ' . $invoiceNumber . ' berhasil diajukan! Tim Finance Brand akan memverifikasi tagihan dan memproses pembayaran.',
        ]);
    }

    /**
     * AJAX: Ambil detail invoice beserta riwayat log audit
     */
    public function getInvoiceDetailAjax(int $id): ResponseInterface
    {
        $kolId = $this->getMyKolId();
        if (! $kolId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $invoice = $this->invoiceModel->getInvoiceDetail($id, $kolId);
        if (! $invoice) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invoice tidak ditemukan atau Anda tidak memiliki akses.']);
        }

        $histories = $this->invoiceModel->getInvoiceHistories($id);

        return $this->response->setJSON([
            'success'   => true,
            'invoice'   => $invoice,
            'histories' => $histories,
        ]);
    }
}

