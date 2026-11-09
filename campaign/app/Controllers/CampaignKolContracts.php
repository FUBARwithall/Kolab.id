<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\CampaignModel;
use App\Models\InvoiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class CampaignKolContracts extends BaseController
{
    protected ContractModel $contractModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->campaignModel = new CampaignModel();
        helper(['form', 'url', 'filesystem']);
    }

    /**
     * Get current user ID helper
     */
    protected function getUserId(): int
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            return (int) auth()->id();
        }
        return (int) (session('user_id') ?? 1);
    }

    /**
     * Get current KOL ID linked to the logged in user
     */
    protected function getCurrentKolId(): ?int
    {
        $userId = $this->getUserId();
        $db = \Config\Database::connect();
        $kol = $db->table('kols')->where('user_id', $userId)->get()->getRowArray();
        return $kol ? (int) $kol['id'] : null;
    }

    /**
     * Index / Dashboard Kontrak
     */
    public function index($offset = 0)
    {
        $db = \Config\Database::connect();

        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, campaigns.name as campaign_name, kols.name as kol_name')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $contractTemplates = $db->table('contract_templates')
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $metrics = $this->contractModel->getSummaryMetrics();
        $allCampaigns = $db->table('campaigns')->select('id, title, name')->orderBy('id', 'DESC')->get()->getResultArray();

        $data = [
            'title'             => 'Manajemen Kontrak Kerjasama KOL & MoU',
            'controller'        => 'CampaignKolContracts',
            'assignedKols'      => $assignedKols,
            'contractTemplates' => $contractTemplates,
            'allCampaigns'      => $allCampaigns,
            'metrics'           => $metrics,
        ];

        return view('contracts/index', $data);
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
            0 => 'campaign_kol_contracts.id',
            1 => 'campaign_kol_contracts.contract_number',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'campaign_kols.deal_amount',
            5 => 'campaign_kol_contracts.status',
            6 => 'campaign_kol_contracts.signed_at',
            7 => 'campaign_kol_contracts.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'campaign_kol_contracts.id';

        $filters = [
            'campaign_id' => $this->request->getPost('campaign_id'),
            'status'      => $this->request->getPost('status'),
            'kol_id'      => $this->request->getPost('kol_id'),
        ];

        $result = $this->contractModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        $no = $start + 1;
        foreach ($result['data'] as $row) {
            $isSigned = !empty($row['signed_at']) || in_array($row['status'], [ContractModel::STATUS_SIGNED, ContractModel::STATUS_ACTIVE]);
            $ctrNo = $row['display_contract_no'] ?: ($row['contract_number'] ?: 'CTR-' . $row['id']);
            $campName = $row['campaign_title'] ?: ($row['campaign_name'] ?? 'Kampanye');

            $statusBadge = match (strtoupper($row['status'])) {
                ContractModel::STATUS_ACTIVE, ContractModel::STATUS_SIGNED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>ACTIVE (TTD Lengkap)</span>',
                ContractModel::STATUS_PENDING, ContractModel::STATUS_WAITING_KOL_SIGNATURE => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>WAITING KOL E-SIGN</span>',
                ContractModel::STATUS_CANCELLED => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>CANCELLED</span>',
                default => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="far fa-file-alt me-1"></i>DRAFT KONTRAK</span>',
            };

            // Invoice status indicator
            $invoiceInfo = '';
            if (!empty($row['invoice_id'])) {
                $invStatus = strtoupper($row['invoice_status'] ?? 'PENDING');
                $invBadgeCls = match ($invStatus) {
                    'PAID'      => 'bg-success',
                    'PARTIAL'   => 'bg-warning text-dark',
                    'VERIFIED'  => 'bg-info text-dark',
                    'APPROVED'  => 'bg-primary',
                    default     => 'bg-secondary'
                };
                $invoiceInfo = '<div class="mt-1"><a href="' . base_url('invoices/view/' . $row['invoice_id']) . '" class="badge ' . $invBadgeCls . ' text-decoration-none font-monospace small"><i class="fas fa-file-invoice me-1"></i>' . esc($row['invoice_number']) . ' (' . $invStatus . ')</a></div>';
            } elseif ($isSigned) {
                $invoiceInfo = '<div class="mt-1"><button type="button" class="btn btn-xs btn-outline-success py-0 px-2 small" onclick="generateInvoiceForContract(' . $row['id'] . ')" title="Terbitkan Invoice Awal"><i class="fas fa-paper-plane me-1"></i>Terbitkan Invoice</button></div>';
            }

            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . base_url('contracts/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Kontrak"><i class="fas fa-eye"></i></a>
                    <a href="' . base_url('contracts/print/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak / PDF Kontrak"><i class="fas fa-print"></i></a>
                    ' . (!$isSigned ? '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Tanda Tangan E-Sign Brand" onclick="adminSignContractModal(' . $row['id'] . ')"><i class="fas fa-file-signature"></i></button>' : '') . '
                    <a href="' . base_url('contracts/edit/' . $row['id']) . '" class="btn btn-outline-warning btn-sm rounded-2 py-1 px-2" title="Edit S&K Kontrak"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Kontrak" onclick="deleteContract(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
                </div>
            ';

            $formattedData[] = [
                'id'          => $row['id'],
                'no'          => $no++,
                'contract_no' => '
                    <div class="fw-bold font-monospace text-dark">' . esc($ctrNo) . '</div>
                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i>' . date('d M Y', strtotime($row['created_at'])) . '</small>
                ',
                'campaign'    => '
                    <div class="fw-semibold text-primary">' . esc($campName) . '</div>
                    ' . (!empty($row['brand_name']) ? '<small class="text-muted d-block">Brand: ' . esc($row['brand_name']) . '</small>' : '') . '
                ',
                'kol'         => '
                    <div class="fw-semibold text-dark">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted font-monospace">NIK: ' . esc($row['kol_nik'] ?: '-') . '</small>
                ',
                'fee'         => '
                    <strong class="text-success">Rp ' . number_format((float)$row['deal_amount'], 0, ',', '.') . '</strong>
                    <div class="small text-muted">' . esc((string)($row['total_vt'] ?? 1)) . ' Video (VT)</div>
                ',
                'status'      => $statusBadge . $invoiceInfo,
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
     * Get Single Contract JSON
     */
    public function getOne(int $id)
    {
        $contract = $this->contractModel->getContractDetails($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $contract]);
    }

    /**
     * View Contract Details Page
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $contract = $this->contractModel->getContractDetails($id);
        if (!$contract) {
            throw PageNotFoundException::forPageNotFound('Data surat kontrak tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Surat Kontrak Kerjasama #' . esc($contract['display_contract_no']),
            'controller' => 'CampaignKolContracts',
            'contract'   => $contract,
        ];

        return view('contracts/view', $data);
    }

    /**
     * Form Add New Contract
     */
    public function add()
    {
        $db = \Config\Database::connect();
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, campaigns.name as campaign_name, kols.name as kol_name')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $contractTemplates = $db->table('contract_templates')
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'             => 'Buat Surat Kontrak Kerjasama Baru',
            'controller'        => 'CampaignKolContracts',
            'assignedKols'      => $assignedKols,
            'contractTemplates' => $contractTemplates,
        ];

        return view('contracts/add', $data);
    }

    /**
     * Save New Contract Draft
     */
    public function add_save()
    {
        $campaignKolId = (int)$this->request->getPost('campaign_kol_id');
        $templateId    = (int)$this->request->getPost('template_id') ?: ((int)$this->request->getPost('contract_template_id') ?: null);

        if (empty($campaignKolId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih penugasan KOL terlebih dahulu.']);
            }
            return redirect()->back()->with('error', 'Pilih penugasan KOL terlebih dahulu.');
        }

        $contractId = $this->contractModel->generateContract($campaignKolId, $templateId);
        if (!$contractId) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Gagal membuat dokumen kontrak.']);
            }
            return redirect()->back()->with('error', 'Gagal membuat dokumen kontrak.');
        }

        // Handle file upload if supplied
        $file = $this->request->getFile('contract_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/campaign_kol_contracts';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $this->contractModel->update($contractId, ['contract_file' => 'uploads/campaign_kol_contracts/' . $newName]);
        }

        $saveType = $this->request->getPost('save_type');
        $redirectUrl = ($saveType === 'stay') ? base_url('contracts/edit/' . $contractId) : base_url('contracts');

        if ($this->request->isAJAX() || $saveType === 'ajax') {
            return $this->response->setJSON([
                'success'     => true,
                'message'     => 'Surat Kontrak berhasil dibuat!',
                'contract_id' => $contractId,
                'redirect'    => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', 'Surat Kontrak berhasil dibuat!');
    }

    /**
     * AJAX Generate Contract Draft (CI3 create_draft_ajax & modern generate)
     */
    public function create_draft_ajax()
    {
        return $this->generate();
    }

    public function generate()
    {
        $campaignKolId = (int)$this->request->getPost('campaign_kol_id');
        $templateId    = (int)$this->request->getPost('template_id') ?: ((int)$this->request->getPost('contract_template_id') ?: null);

        if (empty($campaignKolId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih penugasan KOL']);
        }

        $contractId = $this->contractModel->generateContract($campaignKolId, $templateId);
        $contract = $this->contractModel->find($contractId);
        $ctrNo = $contract['contract_no'] ?: ($contract['contract_number'] ?? 'CTR-' . $contractId);

        return $this->response->setJSON([
            'success'     => true,
            'message'     => 'Draft kontrak ' . esc($ctrNo) . ' berhasil dibuat!',
            'contract_id' => $contractId,
            'contract_no' => $ctrNo,
        ]);
    }

    /**
     * Edit Contract
     */
    public function edit($id = null)
    {
        $id = (int) $id;
        $contract = $this->contractModel->getContractDetails($id);
        if (!$contract) {
            throw PageNotFoundException::forPageNotFound('Data kontrak tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $contractTemplates = $db->table('contract_templates')->where('is_active', 1)->orderBy('id', 'ASC')->get()->getResultArray();

        $data = [
            'title'             => 'Edit Kontrak #' . esc($contract['display_contract_no']),
            'controller'        => 'CampaignKolContracts',
            'contract'          => $contract,
            'contractTemplates' => $contractTemplates,
        ];

        return view('contracts/edit', $data);
    }

    /**
     * Save Contract Changes
     */
    public function edit_save($id = null)
    {
        $id = (int) $id;
        $contract = $this->contractModel->find($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        $snk       = $this->request->getPost('terms_and_conditions');
        $status    = $this->request->getPost('status') ?: $contract['status'];
        $expiredAt = $this->request->getPost('expired_at');
        $html      = $this->request->getPost('contract_html') ?: $this->request->getPost('clauses_rendered');

        $updateData = [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($snk !== null) {
            $updateData['terms_and_conditions'] = trim($snk);
        }
        if (!empty($expiredAt)) {
            $updateData['expired_at'] = $expiredAt;
        }
        if (!empty($html)) {
            $updateData['contract_html'] = $html;
            $updateData['clauses_rendered'] = $html;
        }

        // Upload new contract file if provided
        $file = $this->request->getFile('contract_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/campaign_kol_contracts';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $updateData['contract_file'] = 'uploads/campaign_kol_contracts/' . $newName;
        }

        $this->contractModel->update($id, $updateData);

        $saveType = $this->request->getPost('save_type');
        $redirectUrl = ($saveType === 'stay') ? base_url('contracts/edit/' . $id) : base_url('contracts');

        if ($this->request->isAJAX() || $saveType === 'ajax') {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Perubahan kontrak berhasil disimpan!',
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', 'Perubahan kontrak berhasil disimpan!');
    }

    /**
     * Delete Contract
     */
    public function delete($id = null)
    {
        $id = (int) ($id ?: $this->request->getPost('id'));
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID Kontrak tidak valid.']);
        }

        $contract = $this->contractModel->find($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        // Remove linked files if any
        if (!empty($contract['contract_file']) && is_file(FCPATH . $contract['contract_file'])) {
            @unlink(FCPATH . $contract['contract_file']);
        }
        if (!empty($contract['kol_signature_image']) && is_file(FCPATH . $contract['kol_signature_image'])) {
            @unlink(FCPATH . $contract['kol_signature_image']);
        }
        if (!empty($contract['admin_signature_image']) && is_file(FCPATH . $contract['admin_signature_image'])) {
            @unlink(FCPATH . $contract['admin_signature_image']);
        }

        $this->contractModel->delete($id);

        if ($this->request->isAJAX() || $this->request->getPost('ajax') || $this->request->getGet('ajax')) {
            return $this->response->setJSON(['success' => true, 'message' => 'Dokumen kontrak berhasil dihapus.']);
        }

        return redirect()->to(base_url('contracts'))->with('success', 'Dokumen kontrak berhasil dihapus.');
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete()
    {
        $ids = (array) ($this->request->getPost('ids') ?? $this->request->getPost('check'));
        if (empty($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tidak ada kontrak yang dipilih.']);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->contractModel->delete($id);
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Sebanyak {$deletedCount} dokumen kontrak berhasil dihapus.",
        ]);
    }

    /**
     * KOL Portal: My Contracts (Kontrak Saya)
     */
    public function my_contracts()
    {
        $kolId = $this->getCurrentKolId();
        $contracts = $this->contractModel->getKolsContracts($kolId);

        $data = [
            'title'        => 'Surat Kontrak & MoU Saya',
            'controller'   => 'CampaignKolContracts',
            'contracts'    => $contracts,
            'unlinked_kol' => empty($kolId),
            'kol_id'       => $kolId,
        ];

        return view('contracts/my_contracts', $data);
    }

    /**
     * AJAX Get Contract HTML for modal review
     */
    public function get_contract_html_ajax($id)
    {
        $id = (int) $id;
        $contract = $this->contractModel->getContractDetails($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan']);
        }

        $html = $contract['contract_html'] ?: ($contract['clauses_rendered'] ?? '');

        return $this->response->setJSON([
            'success'              => true,
            'contract_id'          => $contract['id'],
            'contract_no'          => $contract['display_contract_no'],
            'html'                 => $html,
            'terms_and_conditions' => $contract['terms_and_conditions'],
            'kol_name'             => $contract['kol_name'],
            'deal_amount'          => $contract['deal_amount'],
            'status'               => $contract['status'],
            'admin_signature'      => $contract['admin_signature_image'],
            'kol_signature'        => $contract['kol_signature_image'] ?: $contract['signature_image'],
        ]);
    }

    /**
     * AJAX Save Terms & Conditions (S&K)
     */
    public function save_contract_snk_ajax()
    {
        $id = (int)$this->request->getPost('id');
        $snk = trim((string)$this->request->getPost('terms_and_conditions'));

        $contract = $this->contractModel->find($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        $this->contractModel->update($id, [
            'terms_and_conditions' => $snk,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Syarat & Ketentuan kontrak berhasil diperbarui.',
        ]);
    }

    /**
     * Admin / Brand E-Sign Action
     */
    public function admin_counter_sign_action()
    {
        $id = (int)$this->request->getPost('id');
        $sigData = (string)$this->request->getPost('signature');
        $snk = $this->request->getPost('terms_and_conditions');

        $contract = $this->contractModel->find($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        $uploadDir = FCPATH . 'uploads/campaign_kol_contracts';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'admin_sign_' . $id . '_' . time() . '.png';
        $filePath = $uploadDir . '/' . $fileName;

        if (!empty($sigData) && str_starts_with($sigData, 'data:image')) {
            $sigData = preg_replace('#^data:image/\w+;base64,#i', '', $sigData);
            file_put_contents($filePath, base64_decode($sigData));
            $savedFile = 'uploads/campaign_kol_contracts/' . $fileName;
        } else {
            $savedFile = $contract['admin_signature_image'];
        }

        $updateData = [
            'admin_signature_image' => $savedFile,
            'status'                => ContractModel::STATUS_PENDING,
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        if ($snk !== null && trim($snk) !== '') {
            $updateData['terms_and_conditions'] = trim($snk);
        }

        // If KOL already signed, activate
        if (!empty($contract['kol_signature_image']) || !empty($contract['signature_image'])) {
            $updateData['status'] = ContractModel::STATUS_ACTIVE;
            $updateData['signed_at'] = date('Y-m-d H:i:s');
        }

        $this->contractModel->update($id, $updateData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tanda tangan digital Brand berhasil disimpan. Dokumen siap ditandatangani oleh KOL.',
        ]);
    }

    /**
     * KOL Digital E-Signature Action (kol_sign_action & sign_contract_action)
     */
    public function kol_sign_action()
    {
        $id = (int)$this->request->getPost('id');
        $sigData = (string)$this->request->getPost('signature');

        $contract = $this->contractModel->find($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        $uploadDir = FCPATH . 'uploads/campaign_kol_contracts';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'kol_sign_' . $id . '_' . time() . '.png';
        $filePath = $uploadDir . '/' . $fileName;

        if (!empty($sigData) && str_starts_with($sigData, 'data:image')) {
            $sigData = preg_replace('#^data:image/\w+;base64,#i', '', $sigData);
            file_put_contents($filePath, base64_decode($sigData));
            $savedFile = 'uploads/campaign_kol_contracts/' . $fileName;
        } else {
            $savedFile = $contract['kol_signature_image'] ?: $contract['signature_image'];
        }

        $clientIp = $this->request->getIPAddress();

        $updateData = [
            'kol_signature_image' => $savedFile,
            'signature_image'     => $savedFile,
            'status'              => ContractModel::STATUS_ACTIVE,
            'signed_at'           => date('Y-m-d H:i:s'),
            'signed_ip'           => $clientIp,
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $this->contractModel->update($id, $updateData);

        // Update campaign_kols.contract_status to SIGNED / ACTIVE
        $db = \Config\Database::connect();
        $db->table('campaign_kols')->where('id', $contract['campaign_kol_id'])->update([
            'contract_status' => 'SIGNED',
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Surat Perjanjian Kerjasama (MoU) berhasil ditandatangani secara sah (E-Signed)!',
        ]);
    }

    public function sign_contract_action()
    {
        return $this->kol_sign_action();
    }

    public function signContract(int $id)
    {
        return $this->kol_sign_action();
    }

    /**
     * KOL Manually Issues Invoice for Active Contract
     */
    public function kol_issue_invoice_ajax()
    {
        $contractId = (int)$this->request->getPost('contract_id');
        $contract = $this->contractModel->find($contractId);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        $invoiceId = $this->contractModel->autoGenerateInvoiceForContract($contractId, $this->getUserId());
        if ($invoiceId) {
            $db = \Config\Database::connect();
            $inv = $db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
            $invNo = $inv['invoice_number'] ?? 'INV-' . $invoiceId;

            return $this->response->setJSON([
                'success'    => true,
                'message'    => "Invoice {$invNo} berhasil diterbitkan dan diajukan ke Brand!",
                'invoice_id' => $invoiceId,
                'invoice_no' => $invNo,
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Gagal menerbitkan invoice atau invoice aktif sudah ada untuk penugasan ini.',
        ]);
    }

    /**
     * Admin Manual Trigger to Generate Initial Invoice
     */
    public function generate_initial_invoice_ajax()
    {
        $contractId    = (int)$this->request->getPost('contract_id');
        $campaignKolId = (int)$this->request->getPost('campaign_kol_id');

        if ($contractId <= 0 && $campaignKolId > 0) {
            $contract = $this->contractModel->where('campaign_kol_id', $campaignKolId)->whereNotIn('status', ['VOID', 'CANCELLED'])->first();
            if ($contract) {
                $contractId = (int)$contract['id'];
            }
        }

        if ($contractId <= 0) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan.']);
        }

        $invoiceId = $this->contractModel->autoGenerateInvoiceForContract($contractId, $this->getUserId());
        if ($invoiceId) {
            $db = \Config\Database::connect();
            $inv = $db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
            $invNo = $inv['invoice_number'] ?? 'INV-' . $invoiceId;

            return $this->response->setJSON([
                'success'    => true,
                'message'    => "Invoice awal {$invNo} berhasil digenerate!",
                'invoice_id' => $invoiceId,
                'invoice_no' => $invNo,
            ]);
        }

        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Gagal membuat invoice awal atau sudah ada invoice aktif.',
        ]);
    }

    /**
     * Print View / Legal PDF Layout Kontrak
     */
    public function printContract($id = null)
    {
        $id = (int) $id;
        $contract = $this->contractModel->getContractDetails($id);
        if (!$contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Dokumen kontrak tidak ditemukan.');
        }

        $data = [
            'title'    => 'Surat Perjanjian Kerjasama #' . esc($contract['display_contract_no']),
            'contract' => $contract,
        ];

        return view('contracts/print_contract', $data);
    }

    public function print_pdf($id = null)
    {
        return $this->printContract($id);
    }

    public function download_pdf($id = null)
    {
        return $this->printContract($id);
    }

    public function single_pdf($id = null)
    {
        return $this->printContract($id);
    }

    /**
     * Export Contracts to CSV
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id'),
            'status'      => $this->request->getGet('status'),
        ];

        $contracts = $this->contractModel->getAllForExport($filters);
        $filename  = 'Contracts_Export_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'No',
            'No. Kontrak',
            'Kampanye',
            'Brand',
            'Talent KOL',
            'Nilai Kompensasi (IDR)',
            'Jumlah VT',
            'Status Kontrak',
            'Tanggal TTD',
            'Bank Tujuan',
            'No. Rekening',
            'No. Invoice Terkait',
        ]);

        $no = 1;
        foreach ($contracts as $c) {
            $ctrNo = $c['display_contract_no'] ?: ($c['contract_number'] ?: 'CTR-' . $c['id']);
            fputcsv($output, [
                $no++,
                $ctrNo,
                $c['campaign_title'] ?: ($c['campaign_name'] ?? '-'),
                $c['brand_name'] ?: '-',
                $c['kol_name'] ?: '-',
                (float)$c['deal_amount'],
                (int)($c['total_vt'] ?? 1),
                $c['status'],
                !empty($c['signed_at']) ? date('Y-m-d H:i', strtotime($c['signed_at'])) : '-',
                $c['bank_name'] ?: '-',
                $c['bank_account'] ?: '-',
                $c['invoice_number'] ?: '-',
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
     * Export Contracts to Printable PDF Listing
     */
    public function export_pdf()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id'),
            'status'      => $this->request->getGet('status'),
        ];

        $contracts = $this->contractModel->getAllForExport($filters);

        $data = [
            'title'     => 'Laporan Rekapitulasi Kontrak & MoU KOL',
            'contracts' => $contracts,
            'date'      => date('d F Y, H:i') . ' WIB',
        ];

        return view('contracts/export_pdf', $data);
    }

    /**
     * Fine Uploader file upload handler
     */
    public function upload_contract_file_file()
    {
        $file = $this->request->getFile('qqfile') ?? $this->request->getFile('file');
        $uuid = $this->request->getPost('qquuid') ?? uniqid();

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'error' => 'File tidak valid']);
        }

        $tmpDir = FCPATH . 'uploads/tmp/' . $uuid;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $newName = $file->getName();
        $file->move($tmpDir, $newName);

        return $this->response->setJSON([
            'success'      => true,
            'uploadName'   => $newName,
            'uuid'         => $uuid,
        ]);
    }

    public function delete_contract_file_file($uuid = null)
    {
        if ($uuid) {
            $tmpDir = FCPATH . 'uploads/tmp/' . $uuid;
            if (is_dir($tmpDir)) {
                delete_files($tmpDir, true);
                @rmdir($tmpDir);
            }
        }
        return $this->response->setJSON(['success' => true]);
    }

    public function get_contract_file_file($id = null)
    {
        $contract = $this->contractModel->find($id);
        if ($contract && !empty($contract['contract_file'])) {
            return $this->response->setJSON([
                'success'  => true,
                'filename' => basename($contract['contract_file']),
                'url'      => base_url($contract['contract_file']),
            ]);
        }
        return $this->response->setJSON(['success' => false]);
    }
}
