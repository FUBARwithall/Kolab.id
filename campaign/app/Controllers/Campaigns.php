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
        $db = \Config\Database::connect();
        $data = [
            'title'      => 'Manajemen Kampanye (Campaigns)',
            'controller' => 'Campaigns',
            'summary'    => $this->campaignModel->getSummaryMetrics(),
            'products'   => $this->productModel->where('is_active', 1)->findAll(),
            'tiers'      => $db->table('tiers')->orderBy('id', 'asc')->get()->getResultArray(),
            'categories' => $db->table('categories')->orderBy('name', 'asc')->get()->getResultArray(),
            'platforms'  => $db->table('platforms')->where('is_active', 1)->orderBy('name', 'asc')->get()->getResultArray(),
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
                    <a href="' . base_url('campaigns/analytics/' . $row['id']) . '" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Analitik Finansial & Performa">
                        <i class="fas fa-chart-line"></i>
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

        $tierIds = $db->table('campaign_allowed_tiers')
            ->select('tier_id')
            ->where('campaign_id', $id)
            ->get()
            ->getResultArray();

        $categoryIds = $db->table('campaign_allowed_categories')
            ->select('category_id')
            ->where('campaign_id', $id)
            ->get()
            ->getResultArray();

        $platformIds = $db->table('campaign_required_platforms')
            ->select('platform_id')
            ->where('campaign_id', $id)
            ->get()
            ->getResultArray();

        $campaign['product_ids']  = array_column($productIds, 'product_id');
        $campaign['tier_ids']     = array_column($tierIds, 'tier_id');
        $campaign['category_ids'] = array_column($categoryIds, 'category_id');
        $campaign['platform_ids'] = array_column($platformIds, 'platform_id');

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

        // Simpan kriteria Tiers
        $tierIds = $this->request->getPost('tier_ids');
        $db->table('campaign_allowed_tiers')->where('campaign_id', $campaignId)->delete();
        if (is_array($tierIds) && !empty($tierIds)) {
            $insertTiers = [];
            foreach ($tierIds as $tId) {
                $insertTiers[] = [
                    'campaign_id' => $campaignId,
                    'tier_id'     => (int)$tId,
                ];
            }
            $db->table('campaign_allowed_tiers')->insertBatch($insertTiers);
        }

        // Simpan kriteria Kategori
        $categoryIds = $this->request->getPost('category_ids');
        $db->table('campaign_allowed_categories')->where('campaign_id', $campaignId)->delete();
        if (is_array($categoryIds) && !empty($categoryIds)) {
            $insertCats = [];
            foreach ($categoryIds as $cId) {
                $insertCats[] = [
                    'campaign_id' => $campaignId,
                    'category_id' => (int)$cId,
                ];
            }
            $db->table('campaign_allowed_categories')->insertBatch($insertCats);
        }

        // Simpan kriteria Platform
        $platformIds = $this->request->getPost('platform_ids');
        $db->table('campaign_required_platforms')->where('campaign_id', $campaignId)->delete();
        if (is_array($platformIds) && !empty($platformIds)) {
            $insertPlats = [];
            foreach ($platformIds as $plId) {
                $insertPlats[] = [
                    'campaign_id' => $campaignId,
                    'platform_id' => (int)$plId,
                ];
            }
            $db->table('campaign_required_platforms')->insertBatch($insertPlats);
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
            $msg = 'Kampanye tidak ditemukan.';
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('campaigns'))->with('error', $msg);
        }

        $this->campaignModel->delete($id);

        $msg = 'Kampanye berhasil dihapus.';
        return $this->request->isAJAX()
            ? $this->response->setJSON([
                'success' => true,
                'message' => $msg,
                'summary' => $this->campaignModel->getSummaryMetrics(),
            ])
            : redirect()->to(base_url('campaigns'))->with('success', $msg);
    }

    /**
     * Halaman Konsolidasi Finansial & Performance Analytics (CI3 Parity)
     */
    public function analytics(int $id)
    {
        $analytics = $this->campaignModel->getAnalyticsData($id);
        if (!$analytics) {
            return redirect()->to(base_url('campaigns'))->with('error', 'Kampanye tidak ditemukan.');
        }

        $data = [
            'title'      => 'Analitik Kampanye - ' . esc($analytics['campaign']['title']),
            'controller' => 'Campaigns',
            'campaign'   => $analytics['campaign'],
            'financial'  => $analytics['financial'],
            'performance'=> $analytics['performance'],
            'kols'       => $analytics['kol_analytics'],
            'chartData'  => $analytics['chart_data'],
        ];

        return view('campaigns/analytics', $data);
    }

    /**
     * Update Cepat Status Kampanye
     */
    public function updateStatus(int $id): ResponseInterface
    {
        $campaign = $this->campaignModel->find($id);
        if (!$campaign) {
            return $this->response->setJSON(['success' => false, 'message' => 'Kampanye tidak ditemukan.']);
        }

        $status = strtoupper(trim((string)$this->request->getPost('status')));
        $validStatuses = ['DRAFT', 'ACTIVE', 'COMPLETED', 'CANCELLED'];
        if (!in_array($status, $validStatuses, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Status tidak valid.']);
        }

        $this->campaignModel->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Status kampanye berhasil diubah menjadi {$status}.",
            'status'  => $status,
        ]);
    }

    /**
     * Terima Pelamar Terbuka Kampanye (CI3 Parity)
     */
    public function acceptApplicant(int $id): ResponseInterface
    {
        $db = \Config\Database::connect();
        $app = $db->table('campaign_applications')->where('id', $id)->get()->getRowArray();
        if (!$app) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lamaran kreator tidak ditemukan.']);
        }

        $dealAmount = (float)($this->request->getPost('deal_amount') ?: $app['proposed_rate']);
        $userId = session()->get('user_id') ?: 1;

        $db->transStart();

        // 1. Assign to campaign_kols
        $existing = $db->table('campaign_kols')
            ->where('campaign_id', $app['campaign_id'])
            ->where('kol_id', $app['kol_id'])
            ->get()
            ->getRowArray();

        if (!$existing) {
            $db->table('campaign_kols')->insert([
                'campaign_id'   => $app['campaign_id'],
                'kol_id'        => $app['kol_id'],
                'deal_amount'   => $dealAmount,
                'currency_code' => 'IDR',
                'status'        => 'ASSIGNED',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $db->table('campaign_kols')->where('id', $existing['id'])->update([
                'deal_amount' => $dealAmount,
                'status'      => 'ASSIGNED',
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        // 2. Mark application accepted
        $db->table('campaign_applications')->where('id', $id)->update([
            'status'      => 'ACCEPTED',
            'reviewed_by' => $userId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyetujui pelamar.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pelamar berhasil disetujui dan ditambahkan ke daftar talent kampanye.',
        ]);
    }

    /**
     * Tolak Pelamar Terbuka Kampanye (CI3 Parity)
     */
    public function rejectApplicant(int $id): ResponseInterface
    {
        $db = \Config\Database::connect();
        $app = $db->table('campaign_applications')->where('id', $id)->get()->getRowArray();
        if (!$app) {
            return $this->response->setJSON(['success' => false, 'message' => 'Lamaran tidak ditemukan.']);
        }

        $userId = session()->get('user_id') ?: 1;

        $db->table('campaign_applications')->where('id', $id)->update([
            'status'      => 'REJECTED',
            'reviewed_by' => $userId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lamaran pelamar berhasil ditolak.',
        ]);
    }

    /**
     * Export Seluruh Kampanye ke File CSV (CI3 Parity)
     */
    public function export()
    {
        $campaigns = $this->campaignModel->getAllForExport();

        $filename = 'campaigns_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'Judul Kampanye',
            'Status',
            'Total Anggaran (IDR)',
            'Total Serapan Deal (IDR)',
            'Sisa Anggaran (IDR)',
            'Serapan Anggaran (%)',
            'Target Total Views',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Jumlah KOL Terdaftar',
            'Open Post Registration',
            'Tanggal Dibuat'
        ]);

        foreach ($campaigns as $c) {
            $budget = (float)$c['budget_total'];
            $spent = (float)$c['total_spent'];
            $remaining = max(0, $budget - $spent);
            $pct = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0;

            fputcsv($output, [
                $c['id'],
                $c['title'],
                $c['status'],
                number_format($budget, 0, ',', '.'),
                number_format($spent, 0, ',', '.'),
                number_format($remaining, 0, ',', '.'),
                $pct . '%',
                number_format((int)$c['target_views'], 0, ',', '.'),
                $c['start_date'],
                $c['end_date'],
                (int)$c['total_kols'],
                $c['is_open_registration'] == 1 ? 'Terbuka' : 'Tertutup',
                $c['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export Detail 1 Kampanye beserta KOL dan Metriknya (CI3 Parity)
     */
    public function exportSingle(int $id)
    {
        $analytics = $this->campaignModel->getAnalyticsData($id);
        if (!$analytics) {
            return redirect()->to(base_url('campaigns'))->with('error', 'Kampanye tidak ditemukan.');
        }

        $c = $analytics['campaign'];
        $filename = 'campaign_' . $id . '_analytics_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header Kampanye
        fputcsv($output, ['RINGKASAN KAMPANYE']);
        fputcsv($output, ['ID Kampanye', $c['id']]);
        fputcsv($output, ['Judul Kampanye', $c['title']]);
        fputcsv($output, ['Status', $c['status']]);
        fputcsv($output, ['Total Anggaran (IDR)', number_format((float)$c['budget_total'], 0, ',', '.')]);
        fputcsv($output, ['Total Deal Terpakai (IDR)', number_format($analytics['financial']['total_deals'], 0, ',', '.')]);
        fputcsv($output, ['Total Terbayar (IDR)', number_format($analytics['financial']['total_paid'], 0, ',', '.')]);
        fputcsv($output, ['Total Views Aktual', number_format($analytics['performance']['total_views'], 0, ',', '.')]);
        fputcsv($output, ['Total Engagements', number_format($analytics['performance']['total_engagements'], 0, ',', '.')]);
        fputcsv($output, ['Rata-rata ER (%)', $analytics['performance']['avg_er'] . '%']);
        fputcsv($output, []);

        // Rincian per KOL
        fputcsv($output, [
            'ID Talent',
            'Nama KOL',
            'WhatsApp',
            'Kota Domisili',
            'Nominal Deal (IDR)',
            'Status Pembayaran',
            'Total Tugas SOW',
            'Tugas Selesai',
            'Progress Tugas (%)',
            'Total Views',
            'Total Likes',
            'Total Comments',
            'Total Engagements',
            'ER (%)',
            'CPM (IDR)',
            'CPE (IDR)'
        ]);

        $kolsList = $analytics['kol_analytics'] ?? ($analytics['kols'] ?? []);
        foreach ($kolsList as $k) {
            fputcsv($output, [
                $k['kol_id'],
                $k['kol_name'],
                $k['kol_whatsapp'] ?: '-',
                $k['kol_city'] ?: '-',
                number_format($k['deal_amount'], 0, ',', '.'),
                $k['payment_status'],
                $k['task_total'],
                $k['task_completed'],
                $k['task_progress'] . '%',
                number_format($k['views'], 0, ',', '.'),
                number_format($k['likes'], 0, ',', '.'),
                number_format($k['comments'], 0, ',', '.'),
                number_format($k['engagements'], 0, ',', '.'),
                $k['er'] . '%',
                number_format($k['cpm'], 0, ',', '.'),
                number_format($k['cpe'], 0, ',', '.')
            ]);
        }

        fclose($output);
        exit;
    }
}
