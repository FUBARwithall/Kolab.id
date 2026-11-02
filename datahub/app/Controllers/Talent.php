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
        $kolId    = $this->getMyKolId();
        $invoices = $kolId ? $this->invoiceModel->getInvoicesByKol($kolId) : [];

        $data = [
            'title'      => 'Tagihan & Riwayat Pembayaran Saya',
            'controller' => 'Talent Space',
            'invoices'   => $invoices,
        ];

        return view('talent/my_invoices', $data);
    }
}

