<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\CampaignKolModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class Campaigns extends BaseController
{
    protected CampaignModel $campaignModel;
    protected CampaignKolModel $campaignKolModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->campaignModel = new CampaignModel();
        $this->campaignKolModel = new CampaignKolModel();
        $this->productModel = new ProductModel();
    }

    /**
     * Halaman Utama Daftar Kampanye
     */
    public function index()
    {
        $data = [
            'title'      => 'Manajemen Kampanye (Campaigns)',
            'controller' => 'Campaigns',
            'summary'    => $this->campaignModel->getSummaryMetrics(),
            'products'   => $this->productModel->where('is_active', 1)->findAll(),
        ];

        return view('campaigns/index', $data);
    }

    /**
     * DataTables Server-Side AJAX
     */
    public function getDatatable(): ResponseInterface
    {
        $request = $this->request;
        $draw    = (int)($request->getPost('draw') ?? 1);
        $start   = (int)($request->getPost('start') ?? 0);
        $length  = (int)($request->getPost('length') ?? 10);
        
        $searchArr = $request->getPost('search');
        $searchValue = is_array($searchArr) ? ($searchArr['value'] ?? '') : '';

        $orderArr = $request->getPost('order');
        $orderColIdx = is_array($orderArr) && isset($orderArr[0]['column']) ? (int)$orderArr[0]['column'] : 0;
        $orderDir    = is_array($orderArr) && isset($orderArr[0]['dir']) ? $orderArr[0]['dir'] : 'desc';

        $columns = ['id', 'title', 'budget_total', 'total_spent', 'start_date', 'status', 'created_at'];
        $orderCol = $columns[$orderColIdx] ?? 'id';

        $filterStatus = $request->getPost('filter_status');

        $totalRecords = $this->campaignModel->where('deleted_at IS NULL')->countAllResults(false);
        $filteredRecords = $this->campaignModel->countFiltered($searchValue, $filterStatus);
        $records = $this->campaignModel->getDatatables($start, $length, $searchValue, $orderCol, $orderDir, $filterStatus);

        $data = [];
        foreach ($records as $row) {
            // Badge Status
            $statusBadge = match (strtoupper($row['status'])) {
                'ACTIVE'    => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-play-circle me-1"></i>AKTIF</span>',
                'COMPLETED' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-check-double me-1"></i>SELESAI</span>',
                'CANCELLED' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>DIBATALKAN</span>',
                default     => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-file-alt me-1"></i>DRAFT</span>',
            };

            // Progress Serapan Anggaran
            $budget = (float)$row['budget_total'];
            $spent = (float)($row['total_spent'] ?? 0);
            $pct = $budget > 0 ? min(100, round(($spent / $budget) * 100, 1)) : 0;
            $barColor = $pct > 90 ? 'bg-danger' : ($pct > 70 ? 'bg-warning' : 'bg-primary');

            $progressHtml = '
                <div class="small fw-semibold text-dark mb-1">Rp ' . number_format($spent, 0, ',', '.') . ' <span class="text-muted fw-normal">/ ' . number_format($budget, 0, ',', '.') . '</span></div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar ' . $barColor . '" role="progressbar" style="width: ' . $pct . '%;" aria-valuenow="' . $pct . '" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="text-muted text-end mt-1" style="font-size: 11px;">' . $pct . '% teralokasi (' . ($row['total_kols'] ?? 0) . ' KOL)</div>
            ';

            $periodHtml = '
                <div class="fw-semibold text-dark"><i class="far fa-calendar-alt text-primary me-1"></i>' . date('d M Y', strtotime($row['start_date'])) . '</div>
                <div class="text-muted small">s/d ' . date('d M Y', strtotime($row['end_date'])) . '</div>
            ';

            $actionBtns = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . base_url('campaigns/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Kampanye">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Kampanye" onclick="editCampaign(' . $row['id'] . ')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus / Batalkan" onclick="deleteCampaign(' . $row['id'] . ')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'id'         => $row['id'],
                'title'      => '<a href="' . base_url('campaigns/view/' . $row['id']) . '" class="text-decoration-none fw-bold text-dark d-block">' . esc($row['title']) . '</a><small class="text-muted text-truncate d-inline-block" style="max-width: 260px;">' . esc($row['description'] ?? 'Tidak ada catatan') . '</small>',
                'budget'     => $progressHtml,
                'period'     => $periodHtml,
                'status'     => $statusBadge,
                'action'     => $actionBtns,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    /**
     * Halaman Detail Kampanye dengan Tab
     */
    public function view(int $id)
    {
        $campaign = $this->campaignModel->getFullDetails($id);
        if (!$campaign) {
            return redirect()->to(base_url('campaigns'))->with('error', 'Kampanye tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $availableKols = $db->table('kols')
            ->select('kols.id, kols.name, users.avatar, kols.whatsapp, kols.city')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->where('kols.approval_status', 'APPROVED')
            ->orderBy('kols.name', 'asc')
            ->get()
            ->getResultArray();

        $data = [
            'title'         => 'Detail: ' . $campaign['title'],
            'controller'    => 'Campaigns',
            'campaign'      => $campaign,
            'availableKols' => $availableKols,
            'allProducts'   => $this->productModel->where('is_active', 1)->findAll(),
        ];

        return view('campaigns/view', $data);
    }

    /**
     * Ambil 1 data kampanye untuk modal edit (JSON)
     */
    public function getOne(int $id): ResponseInterface
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kampanye tidak ditemukan.',
            ]);
        }

        $db = \Config\Database::connect();
        $productIds = $db->table('campaign_products')
            ->select('product_id')
            ->where('campaign_id', $id)
            ->get()
            ->getResultArray();

        $campaign['product_ids'] = array_column($productIds, 'product_id');

        return $this->response->setJSON([
            'success' => true,
            'data'    => $campaign,
        ]);
    }

    /**
     * Simpan Kampanye (Tambah / Update)
     */
    public function save(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $isUpdate = !empty($id);

        $rules = [
            'title'        => 'required|min_length[3]|max_length[200]',
            'budget_total' => 'required|numeric',
            'start_date'   => 'required|valid_date',
            'end_date'     => 'required|valid_date',
            'status'       => 'required|in_list[DRAFT,ACTIVE,COMPLETED,CANCELLED]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success'  => false,
                'messages' => $this->validator->getErrors(),
            ]);
        }

        $title = trim($this->request->getPost('title'));
        $slug  = url_title($title, '-', true) . '-' . substr(md5(uniqid()), 0, 5);

        $payload = [
            'title'                => $title,
            'budget_total'         => (float)$this->request->getPost('budget_total'),
            'target_views'         => (int)($this->request->getPost('target_views') ?? 0),
            'start_date'           => $this->request->getPost('start_date'),
            'end_date'             => $this->request->getPost('end_date'),
            'status'               => strtoupper($this->request->getPost('status')),
            'is_open_registration' => (int)($this->request->getPost('is_open_registration') ?? 0),
            'description'          => trim($this->request->getPost('description') ?? ''),
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        if ($isUpdate) {
            $this->campaignModel->update($id, $payload);
            $campaignId = $id;
            $message = 'Kampanye berhasil diperbarui.';
        } else {
            $payload['slug'] = $slug;
            $payload['created_by'] = session()->get('user_id') ?: 1;
            $campaignId = $this->campaignModel->insert($payload);
            $message = 'Kampanye baru berhasil dibuat.';
        }

        // Simpan produk terkait (campaign_products)
        $productIds = $this->request->getPost('product_ids');
        $db->table('campaign_products')->where('campaign_id', $campaignId)->delete();
        if (is_array($productIds) && !empty($productIds)) {
            $insertProds = [];
            foreach ($productIds as $pId) {
                $insertProds[] = [
                    'campaign_id' => $campaignId,
                    'product_id'  => (int)$pId,
                ];
            }
            $db->table('campaign_products')->insertBatch($insertProds);
        }

        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'summary' => $this->campaignModel->getSummaryMetrics(),
        ]);
    }

    /**
     * Hapus / Batalkan Kampanye
     */
    public function delete(int $id): ResponseInterface
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kampanye tidak ditemukan.',
            ]);
        }

        $this->campaignModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kampanye berhasil dihapus.',
            'summary' => $this->campaignModel->getSummaryMetrics(),
        ]);
    }
}
