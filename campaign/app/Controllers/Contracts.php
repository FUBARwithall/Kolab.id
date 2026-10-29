<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\CampaignModel;

class Contracts extends BaseController
{
    protected ContractModel $contractModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Kontrak KOL & Komisi
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.deal_amount, campaigns.title as campaign_title, kols.name as kol_name')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'        => 'Kontrak KOL & Komisi Kerjasama',
            'controller'   => 'Contracts',
            'assignedKols' => $assignedKols,
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('contracts/index', $data);
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
            0 => 'campaign_kol_contracts.id',
            1 => 'campaign_kol_contracts.contract_number',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'campaign_kols.deal_amount',
            5 => 'campaign_kol_contracts.signed_at',
            6 => 'campaign_kol_contracts.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'campaign_kol_contracts.id';

        $filters = [
            'campaign_id' => $this->request->getPost('campaign_id'),
        ];

        $result = $this->contractModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $isSigned = !empty($row['signed_at']);
            $statusBadge = $isSigned
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-file-signature me-1"></i>SIGNED (' . date('d M Y', strtotime($row['signed_at'])) . ')</span>'
                : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>DRAFT KONTRAK</span>';

            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Baca Surat Kontrak" onclick="viewContract(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                    <a href="' . base_url('contracts/print/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak / PDF Surat Perjanjian"><i class="fas fa-print"></i></a>
                    ' . (!$isSigned ? '<button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Tandatangani Kontrak (E-Sign)" onclick="signContract(' . $row['id'] . ')"><i class="fas fa-file-signature"></i></button>' : '') . '
                </div>
            ';

            $formattedData[] = [
                'id'          => $row['id'],
                'contract_no' => '
                    <div class="fw-bold font-monospace text-dark">' . esc($row['contract_number']) . '</div>
                    <small class="text-muted">' . date('d M Y', strtotime($row['created_at'])) . '</small>
                ',
                'campaign'    => '<div class="fw-semibold text-primary">' . esc($row['campaign_title']) . '</div>',
                'kol'         => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">NIK: ' . esc($row['kol_nik'] ?: '-') . '</small>
                ',
                'fee'         => '<strong class="text-dark">Rp ' . number_format((float)$row['deal_amount'], 0, ',', '.') . '</strong> (' . $row['total_vt'] . ' VT)',
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
     * Get single contract JSON
     */
    public function getOne(int $id)
    {
        $contract = $this->contractModel->find($id);
        if (!$contract) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Kontrak tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $contract]);
    }

    /**
     * Generate contract draft
     */
    public function generate()
    {
        $campaignKolId = (int)$this->request->getPost('campaign_kol_id');
        if (empty($campaignKolId)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih penugasan KOL']);
        }

        $contractId = $this->contractModel->generateContract($campaignKolId);

        return $this->response->setJSON([
            'success'     => true,
            'message'     => 'Draft kontrak perjanjian berhasil digenerate!',
            'contract_id' => $contractId,
        ]);
    }

    /**
     * Print View / Legal PDF Layout Kontrak
     */
    public function printContract(int $id)
    {
        $db = \Config\Database::connect();
        $contract = $db->table('campaign_kol_contracts')
            ->select('
                campaign_kol_contracts.*,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaigns.title as campaign_title,
                campaigns.start_date,
                campaigns.end_date,
                kols.name as kol_name,
                kols.nik as kol_nik,
                kols.address as kol_address,
                kols.whatsapp as kol_whatsapp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_contracts.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('campaign_kol_contracts.id', $id)
            ->get()
            ->getRowArray();

        if (!$contract) {
            return redirect()->to(base_url('contracts'))->with('error', 'Kontrak tidak ditemukan.');
        }

        $data = [
            'title'    => 'Surat Perjanjian Kerjasama #' . $contract['contract_number'],
            'contract' => $contract,
        ];

        return view('contracts/print_contract', $data);
    }

    /**
     * E-Sign Action by Admin / Brand
     */
    public function signContract(int $id)
    {
        $clientIp = $this->request->getIPAddress();

        $this->contractModel->update($id, [
            'signed_at' => date('Y-m-d H:i:s'),
            'signed_ip' => $clientIp,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Surat kontrak berhasil ditandatangani secara elektronik (E-Sign)!',
        ]);
    }
}
