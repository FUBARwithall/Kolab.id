<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignKolModel;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

class CampaignKols extends BaseController
{
    protected CampaignKolModel $campaignKolModel;

    public function __construct()
    {
        $this->campaignKolModel = new CampaignKolModel();
    }

    /**
     * Halaman Utama Dealing KOL (Campaign Kols)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $campaignId     = $this->request->getGet('campaign_id') ?? '';
        $kolId          = $this->request->getGet('kol_id') ?? '';
        $status         = $this->request->getGet('status') ?? '';
        $shippingStatus = $this->request->getGet('shipping_status') ?? '';

        // Dropdown data
        $allCampaigns = $db->table('campaigns')
            ->select('id, title, COALESCE(name, title) as name, budget, start_date, end_date, status')
            ->orderBy('title', 'ASC')
            ->get()
            ->getResultArray();

        $allKols = $db->table('kols')
            ->select('kols.id, kols.name, kols.whatsapp, kols.city, kols.status, kols.is_blacklisted, tiers.name as tier_name')
            ->join('tiers', 'tiers.id = kols.tier_id', 'left')
            ->orderBy('kols.name', 'ASC')
            ->get()
            ->getResultArray();

        $allPlatforms = $db->table('platforms')
            ->select('id, name, code, icon')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $allVariants = $db->table('variants')
            ->select('id, name, sku')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $allProducts = $db->table('products')
            ->select('id, name, sku, stock, stock_qty, price, unit_price, category')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $metrics = $this->campaignKolModel->getSummaryMetrics($campaignId ? (int)$campaignId : null);

        $data = [
            'title'          => 'Dealing KOL (Campaign KOLs)',
            'controller'     => 'CampaignKols',
            'metrics'        => $metrics,
            'campaignId'     => $campaignId,
            'kolId'          => $kolId,
            'status'         => $status,
            'shippingStatus' => $shippingStatus,
            'allCampaigns'   => $allCampaigns,
            'allKols'        => $allKols,
            'allPlatforms'   => $allPlatforms,
            'allVariants'    => $allVariants,
            'allProducts'    => $allProducts,
        ];

        return view('campaign_kols/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable(): ResponseInterface
    {
        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        // Column mapping
        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0  => 'campaign_kols.id',
            1  => 'campaign_kols.id',
            2  => 'campaigns.title',
            3  => 'kols.name',
            4  => 'campaign_kols.status',
            5  => 'campaign_kols.deal_amount',
            6  => 'campaign_kols.jumlah_vt',
            7  => 'campaign_kols.task_count',
            8  => 'campaign_kols.shipping_status',
            9  => 'campaign_kols.collaboration_score',
            10 => 'campaign_kols.deal_date',
            11 => 'campaign_kols.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'campaign_kols.id';

        $filters = [
            'campaign_id'     => $this->request->getPost('campaign_id'),
            'kol_id'          => $this->request->getPost('kol_id'),
            'status'          => $this->request->getPost('status'),
            'shipping_status' => $this->request->getPost('shipping_status'),
        ];

        $result = $this->campaignKolModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $formattedData[] = [
                'id'                   => (int)$row['id'],
                'campaign_id'          => (int)$row['campaign_id'],
                'campaign_title'       => esc($row['campaign_title'] ?? '-'),
                'kol_id'               => (int)$row['kol_id'],
                'kol_name'             => esc($row['kol_name'] ?? '-'),
                'kol_whatsapp'         => esc($row['kol_whatsapp'] ?? ''),
                'kol_avatar'           => $row['kol_avatar'] ?? null,
                'tier_name'            => esc($row['tier_name'] ?? 'Nano'),
                'is_blacklisted'       => (int)($row['is_blacklisted'] ?? 0),
                'blacklist_reason'     => esc($row['blacklist_reason'] ?? ''),
                'status'               => $row['status'] ?? 'ASSIGNED',
                'deal_amount'          => (float)($row['deal_amount'] ?? 0),
                'deal_amount_formatted'=> 'Rp ' . number_format((float)($row['deal_amount'] ?? 0), 0, ',', '.'),
                'currency_code'        => esc($row['currency_code'] ?? 'IDR'),
                'jumlah_vt'            => (int)($row['jumlah_vt'] ?? 0),
                'task_count'           => (int)($row['task_count'] ?? 0),
                'task_completed_count' => (int)($row['task_completed_count'] ?? 0),
                'deal_date'            => $row['deal_date'] ? date('d M Y', strtotime($row['deal_date'])) : '-',
                'collaboration_score'  => $row['collaboration_score'] ? number_format((float)$row['collaboration_score'], 2) : '-',
                'shipping_status'      => $row['shipping_status'] ?? 'NOT_SENT',
                'shipping_tracking_no' => esc($row['shipping_tracking_no'] ?? ''),
                'latest_contract_status'=> $row['latest_contract_status'] ?? null,
                'latest_contract_no'   => esc($row['latest_contract_no'] ?? ''),
                'latest_invoice_status'=> $row['latest_invoice_status'] ?? null,
                'invoice_no'           => esc($row['invoice_no'] ?? ''),
                'latest_shipment_status'=> $row['latest_shipment_status'] ?? null,
                'latest_tracking_no'   => esc($row['latest_tracking_no'] ?? ''),
                'deal_scope'           => esc($row['deal_scope'] ?? ''),
                'rejection_reason'     => esc($row['rejection_reason'] ?? ''),
                'assigned_by_name'     => esc($row['assigned_by_name'] ?? '-'),
                'created_at'           => $row['created_at'] ? date('d M Y H:i', strtotime($row['created_at'])) : '-',
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
     * Get single record details as JSON
     */
    public function getOne(int $id): ResponseInterface
    {
        $data = $this->campaignKolModel->getWithDetails($id);
        if (!$data) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data Dealing KOL tidak ditemukan.',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Get tasks, shipping, and meta for Dealing KOL as JSON (CI3/CI4 Unified Parity)
     */
    public function getTasksJson(int $id): ResponseInterface
    {
        $campaignKol = $this->campaignKolModel->find($id);
        if (!$campaignKol) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data Dealing KOL tidak ditemukan.',
            ])->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $tasks = $db->table('tasks')
            ->select('tasks.*, platforms.name as platform_name, platforms.code as platform_code, platforms.icon as platform_icon, variants.name as variant_name')
            ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
            ->join('variants', 'variants.id = tasks.variant_id', 'left')
            ->where('tasks.campaign_kol_id', $id)
            ->orderBy('tasks.task_number', 'ASC')
            ->get()
            ->getResultArray();

        $kolId = (int)$campaignKol['kol_id'];
        $kolPlatforms = $db->table('kol_platforms')
            ->select('platforms.id, platforms.name, platforms.code, kol_platforms.username, kol_platforms.followers')
            ->join('platforms', 'platforms.id = kol_platforms.platform_id', 'inner')
            ->where('kol_platforms.kol_id', $kolId)
            ->where('kol_platforms.is_active', 1)
            ->get()
            ->getResultArray();

        $platforms = !empty($kolPlatforms)
            ? $kolPlatforms
            : $db->table('platforms')->where('is_active', 1)->get()->getResultArray();

        $variants = $db->table('variants')->get()->getResultArray();

        $campaign = $db->table('campaigns')->where('id', (int)$campaignKol['campaign_id'])->get()->getRowArray();
        $kol = $db->table('kols')->where('id', $kolId)->get()->getRowArray();

        // Shipment and items
        $shipment = $db->table('kol_shipments')->where('campaign_kol_id', $id)->get()->getRowArray();
        $shipmentItems = [];
        if ($shipment) {
            $shipmentItems = $db->table('kol_shipment_items')
                ->select('kol_shipment_items.*, products.name as product_name, products.sku as product_sku, products.stock as product_stock, products.price as product_price')
                ->join('products', 'products.id = kol_shipment_items.product_id', 'left')
                ->where('kol_shipment_items.shipment_id', $shipment['id'])
                ->get()
                ->getResultArray();
        }

        // Filter products associated with campaign or all active products
        $campaignId = (int)$campaignKol['campaign_id'];
        $campaignProducts = $db->table('campaign_products')
            ->select('products.*')
            ->join('products', 'products.id = campaign_products.product_id', 'inner')
            ->where('campaign_products.campaign_id', $campaignId)
            ->where('products.is_active', 1)
            ->orderBy('products.name', 'ASC')
            ->get()
            ->getResultArray();

        $products = !empty($campaignProducts)
            ? $campaignProducts
            : $db->table('products')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();

        // Calculate remaining budget
        $campaignBudget = $campaign ? (float)($campaign['budget'] ?? 0) : 0;
        $otherUsedBudget = (float)($db->table('campaign_kols')
            ->selectSum('deal_amount')
            ->where('campaign_id', $campaignId)
            ->where('id !=', $id)
            ->whereIn('status', ['ASSIGNED', 'ACTIVE', 'COMPLETED'])
            ->get()
            ->getRowArray()['deal_amount'] ?? 0);

        $campaignRemainingBudget = max(0, $campaignBudget - $otherUsedBudget);

        return $this->response->setJSON([
            'success'                   => true,
            'campaign_kol'              => $campaignKol,
            'campaign_name'             => $campaign['title'] ?? ($campaign['name'] ?? ''),
            'campaign_start_date'       => $campaign['start_date'] ?? '',
            'campaign_end_date'         => $campaign['end_date'] ?? '',
            'campaign_budget'           => $campaignBudget,
            'campaign_remaining_budget' => $campaignRemainingBudget,
            'kol_name'                  => $kol['name'] ?? '',
            'kol'                       => $kol,
            'tasks'                     => $tasks,
            'platforms'                 => $platforms,
            'variants'                  => $variants,
            'shipment'                  => $shipment,
            'shipment_items'            => $shipmentItems,
            'products'                  => $products,
        ]);
    }

    /**
     * Generate unique tracking/resi number based on courier (CI3 Parity)
     */
    public function generateTrackingNo(): ResponseInterface
    {
        $courier = trim($this->request->getVar('courier') ?? '');
        $prefixMap = [
            'JNE'          => 'JNE',
            'J&T'          => 'J&T',
            'SiCepat'      => 'SCP',
            'AnterAja'     => 'ANTR',
            'GoSend'       => 'GOSEND',
            'GrabExpress'  => 'GRAB',
            'Ninja Xpress' => 'NINJA',
            'POS Indonesia'=> 'POS',
            'Lion Parcel'  => 'LION',
            'Lainnya'      => 'RESI',
        ];

        $prefix = $prefixMap[$courier] ?? (preg_replace('/[^A-Za-z0-9&]/', '', $courier) ?: 'RESI');
        $db = \Config\Database::connect();
        $uniqueCode = null;

        for ($i = 0; $i < 20; $i++) {
            $randomDigits = mt_rand(1000000, 9999999);
            $candidate = $prefix . $randomDigits;

            $exists = $db->table('kol_shipments')->where('tracking_no', $candidate)->countAllResults();
            if ($exists === 0) {
                $uniqueCode = $candidate;
                break;
            }
        }

        if (!$uniqueCode) {
            $uniqueCode = $prefix . time();
        }

        return $this->response->setJSON([
            'success'     => true,
            'tracking_no' => $uniqueCode,
            'courier'     => $courier,
        ]);
    }

    /**
     * Quick Add KOL with Name only (for instant assignment) - CI3 Parity
     */
    public function quickAddKol(): ResponseInterface
    {
        $name = trim((string)$this->request->getPost('name'));
        if (empty($name)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nama KOL tidak boleh kosong.',
            ]);
        }

        $db = \Config\Database::connect();

        // Check if KOL exists
        $existing = $db->table('kols k')
            ->select('k.*, t.name as tier_name')
            ->join('tiers t', 't.id = k.tier_id', 'left')
            ->where('LOWER(TRIM(k.name))', strtolower($name))
            ->get()
            ->getRowArray();

        if ($existing) {
            if ($existing['status'] !== 'ACTIVE') {
                $db->table('kols')->where('id', $existing['id'])->update([
                    'status'     => 'ACTIVE',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->response->setJSON([
                'success'     => true,
                'is_existing' => true,
                'message'     => 'KOL "' . $existing['name'] . '" sudah ada di sistem dan otomatis dipilih.',
                'kol'         => [
                    'id'               => (int)$existing['id'],
                    'name'             => $existing['name'],
                    'tier_name'        => !empty($existing['tier_name']) ? $existing['tier_name'] : 'Nano',
                    'is_blacklisted'   => (int)$existing['is_blacklisted'],
                    'blacklist_reason' => $existing['blacklist_reason'] ?: '',
                ],
            ]);
        }

        // Get default tier (Nano)
        $defaultTier = $db->table('tiers')
            ->orderBy('min_followers', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $tierId   = $defaultTier ? (int)$defaultTier['id'] : 1;
        $tierName = $defaultTier ? $defaultTier['name'] : 'Nano';
        $userId   = session()->get('user_id') ?: 1;

        $saveData = [
            'name'             => $name,
            'tier_id'          => $tierId,
            'status'           => 'ACTIVE',
            'is_blacklisted'   => 0,
            'blacklist_reason' => null,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        $db->table('kols')->insert($saveData);
        $kolId = $db->insertID();

        return $this->response->setJSON([
            'success'     => true,
            'is_existing' => false,
            'message'     => 'KOL "' . $name . '" berhasil ditambahkan.',
            'kol'         => [
                'id'               => (int)$kolId,
                'name'             => $name,
                'tier_name'        => $tierName,
                'is_blacklisted'   => 0,
                'blacklist_reason' => '',
            ],
        ]);
    }

    /**
     * Get assigned active KOL IDs for a given campaign (CI3 Parity)
     */
    public function getCampaignAssignedKols(int $campaignId): ResponseInterface
    {
        $db = \Config\Database::connect();
        $assigned = $db->table('campaign_kols')
            ->select('kol_id, status')
            ->where('campaign_id', $campaignId)
            ->whereNotIn('status', ['REJECTED', 'CANCELLED'])
            ->get()
            ->getResultArray();

        $campaign = $db->table('campaigns')
            ->select('id, title, COALESCE(name, title) as name, budget, start_date, end_date')
            ->where('id', $campaignId)
            ->get()
            ->getRowArray();

        $assignedKolIds = !empty($assigned)
            ? array_values(array_map('intval', array_column($assigned, 'kol_id')))
            : [];

        return $this->response->setJSON([
            'success'          => true,
            'campaign'         => $campaign,
            'assigned_kol_ids' => $assignedKolIds,
        ]);
    }

    /**
     * Save new Dealing KOL assignment (Supports Multi-KOL)
     */
    public function save(): ResponseInterface
    {
        $campaignId = (int)$this->request->getPost('campaign_id');
        $kolInput   = $this->request->getPost('kol_id');

        if (!$campaignId || empty($kolInput)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan pilih campaign dan setidaknya satu KOL.',
            ]);
        }

        $kolIds = is_array($kolInput) ? $kolInput : [$kolInput];
        $kolIds = array_values(array_filter(array_map('intval', $kolIds)));

        if (empty($kolIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pilihan Assignment KOL tidak valid.',
            ]);
        }

        $db = \Config\Database::connect();
        $status      = trim($this->request->getPost('status') ?? 'ASSIGNED');
        $jumlahVt    = max(1, (int)$this->request->getPost('jumlah_vt'));
        $dealAmountRaw = $this->request->getPost('deal_amount');
        $dealAmount  = ($dealAmountRaw !== null && $dealAmountRaw !== '')
            ? (float)preg_replace('/[^0-9]/', '', (string)$dealAmountRaw)
            : 0;

        $currencyCode = $this->request->getPost('currency_code') ?: 'IDR';
        $userId       = session()->get('user_id') ?: 1;

        // Deal scope upload handling
        $dealScopeStr = null;
        $files = $this->request->getFiles();
        if (isset($files['deal_scope']) && is_array($files['deal_scope'])) {
            $uploadedNames = [];
            $uploadDir = FCPATH . 'uploads/campaign_kols/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            foreach ($files['deal_scope'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = date('YmdHis') . '-' . $file->getRandomName();
                    $file->move($uploadDir, $newName);
                    $uploadedNames[] = $newName;
                }
            }
            if (!empty($uploadedNames)) {
                $dealScopeStr = implode(',', $uploadedNames);
            }
        }

        $createdIds   = [];
        $skippedNames = [];

        foreach ($kolIds as $kId) {
            $existingPair = $db->table('campaign_kols')
                ->where('campaign_id', $campaignId)
                ->where('kol_id', $kId)
                ->get()
                ->getRowArray();

            if ($existingPair) {
                if (!in_array($existingPair['status'], ['REJECTED', 'CANCELLED'])) {
                    $kolInfo = $db->table('kols')->select('name')->where('id', $kId)->get()->getRowArray();
                    $skippedNames[] = $kolInfo['name'] ?? ("KOL #{$kId}");
                    continue;
                }
            }

            $saveData = [
                'campaign_id'          => $campaignId,
                'kol_id'               => $kId,
                'status'               => $status,
                'jumlah_vt'            => $jumlahVt,
                'total_vt'             => $jumlahVt,
                'assigned_at'          => date('Y-m-d H:i:s'),
                'deal_date'            => in_array($status, ['ASSIGNED', 'ACTIVE', 'COMPLETED']) ? date('Y-m-d') : null,
                'deal_amount'          => $dealAmount,
                'task_count'           => $jumlahVt,
                'task_completed_count' => 0,
                'currency_code'        => $currencyCode,
                'assigned_by'          => $userId,
                'created_by'           => $userId,
                'deal_scope'           => $dealScopeStr,
            ];

            if ($existingPair) {
                $this->campaignKolModel->update($existingPair['id'], $saveData);
                $campaignKolId = $existingPair['id'];
            } else {
                $campaignKolId = $this->campaignKolModel->insert($saveData);
            }

            if ($campaignKolId) {
                $createdIds[] = $campaignKolId;

                // Create initial tasks
                if (in_array($status, ['ASSIGNED', 'ACTIVE', 'COMPLETED'])) {
                    $ratePerVt = $jumlahVt > 0 ? ($dealAmount / $jumlahVt) : $dealAmount;
                    for ($vt = 1; $vt <= $jumlahVt; $vt++) {
                        $db->table('tasks')->insert([
                            'campaign_kol_id' => $campaignKolId,
                            'task_number'     => $vt,
                            'title'           => 'Konten Video VT #' . $vt,
                            'budget'          => $ratePerVt,
                            'rate_amount'     => $ratePerVt,
                            'is_rate_locked'  => 0,
                            'status'          => 'DRAFT',
                            'currency_code'   => $currencyCode,
                            'deadline'        => null,
                            'created_by'      => $userId,
                            'created_at'      => date('Y-m-d H:i:s'),
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ]);

                        $newTaskId = $db->insertID();
                        if ($newTaskId && $db->tableExists('briefs')) {
                            $db->table('briefs')->insert([
                                'task_id'         => $newTaskId,
                                'title'           => 'Brief Task #' . $vt,
                                'content'         => '',
                                'guidelines'      => '',
                                'file_path'       => null,
                                'revision_count'  => 0,
                                'status'          => 'DRAFT',
                                'approval_status' => 'DRAFT',
                                'created_by'      => $userId,
                                'submitted_by'    => $userId,
                                'created_at'      => date('Y-m-d H:i:s'),
                                'updated_at'      => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }

                    // Auto draft contract
                    $this->campaignKolModel->autoDraftContract($campaignKolId);
                }
            }
        }

        if (count($createdIds) > 0) {
            $msg = count($createdIds) === 1
                ? '1 KOL berhasil ditugaskan ke Dealing Campaign.'
                : (count($createdIds) . ' KOL berhasil ditugaskan sekaligus ke Dealing Campaign.');

            if (!empty($skippedNames)) {
                $msg .= ' (' . count($skippedNames) . ' KOL dilewati karena sudah aktif: ' . implode(', ', $skippedNames) . ')';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $msg,
                'ids'     => $createdIds,
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => !empty($skippedNames)
                ? 'Semua KOL yang dipilih (' . implode(', ', $skippedNames) . ') sudah terdaftar aktif pada Campaign ini.'
                : 'Tidak ada KOL yang berhasil disimpan.',
        ]);
    }

    /**
     * Update Status, Task configurations, and Shipment via AJAX (Unified Submit - CI3 Parity)
     */
    public function updateStatusTasksAjax(): ResponseInterface
    {
        $id       = (int)$this->request->getPost('id');
        $status   = strtoupper(trim((string)$this->request->getPost('status')));
        $jumlahVt = (int)$this->request->getPost('jumlah_vt');

        $validStatuses = ['ASSIGNED', 'NEGOTIATION', 'ACTIVE', 'COMPLETED', 'CANCELLED', 'REJECTED'];
        if (!in_array($status, $validStatuses)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status tidak valid.',
            ]);
        }

        $campaignKol = $this->campaignKolModel->find($id);
        if (!$campaignKol) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data Dealing KOL tidak ditemukan.',
            ]);
        }

        $db = \Config\Database::connect();
        $dealAmountRaw = $this->request->getPost('deal_amount');
        $dealAmount = ($dealAmountRaw !== null && $dealAmountRaw !== '')
            ? (float)preg_replace('/[^0-9]/', '', (string)$dealAmountRaw)
            : null;

        // Check against campaign remaining budget
        $campaignId = (int)$campaignKol['campaign_id'];
        $campaign   = $db->table('campaigns')->where('id', $campaignId)->get()->getRowArray();
        $campaignBudget = $campaign ? (float)($campaign['budget'] ?? 0) : 0;

        $otherUsedBudget = (float)($db->table('campaign_kols')
            ->selectSum('deal_amount')
            ->where('campaign_id', $campaignId)
            ->where('id !=', $id)
            ->whereIn('status', ['ASSIGNED', 'ACTIVE', 'COMPLETED'])
            ->get()
            ->getRowArray()['deal_amount'] ?? 0);

        $campaignRemainingBudget = max(0, $campaignBudget - $otherUsedBudget);

        if ($status === 'NEGOTIATION') {
            $dealAmount = 0;
        }

        if ($dealAmount !== null && $dealAmount > $campaignRemainingBudget) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Deal amount (Rp ' . number_format($dealAmount, 0, ',', '.') . ') melebihi sisa budget campaign (Rp ' . number_format($campaignRemainingBudget, 0, ',', '.') . ').',
            ]);
        }

        $db->transStart();

        $allowedVtStatuses = ['NEGOTIATION', 'ASSIGNED', 'ACTIVE', 'COMPLETED'];
        $saveJumlahVt = in_array($status, $allowedVtStatuses) ? $jumlahVt : 0;

        $updateData = [
            'status'     => $status,
            'jumlah_vt'  => $saveJumlahVt,
            'total_vt'   => $saveJumlahVt,
            'task_count' => $saveJumlahVt,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'REJECTED') {
            $updateData['rejection_reason'] = trim((string)$this->request->getPost('rejection_reason'));
        } else {
            $updateData['rejection_reason'] = null;
        }

        if ($status === 'ASSIGNED' && empty($campaignKol['deal_date'])) {
            $updateData['assigned_at'] = date('Y-m-d H:i:s');
            $updateData['deal_date']   = date('Y-m-d');
        }

        if ($dealAmount !== null) {
            $updateData['deal_amount'] = $dealAmount;
        }

        $this->campaignKolModel->update($id, $updateData);

        // Auto draft contract when status is ASSIGNED, ACTIVE, or COMPLETED
        if (in_array($status, ['ASSIGNED', 'ACTIVE', 'COMPLETED'])) {
            $this->campaignKolModel->autoDraftContract($id);
        }

        // Handle tasks if status is ASSIGNED, ACTIVE, or COMPLETED
        $tasksInput = $this->request->getPost('tasks');
        $taskSyncAllowed = ['ASSIGNED', 'ACTIVE', 'COMPLETED'];

        if (in_array($status, $taskSyncAllowed) && is_array($tasksInput) && count($tasksInput) > 0) {
            $totalTaskBudget = 0;
            foreach ($tasksInput as $t) {
                if (empty($t['content_type']) || !isset($t['budget']) || $t['budget'] === '') {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Content Type dan Budget wajib diisi untuk setiap task.',
                    ]);
                }
                $b = (float)preg_replace('/[^0-9]/', '', (string)$t['budget']);
                if ($b < 0) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Budget task tidak boleh kurang dari nol.',
                    ]);
                }
                $totalTaskBudget += $b;
            }

            if ($dealAmount === null || $dealAmount <= 0 || (int)$totalTaskBudget !== (int)$dealAmount) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Total budget task (Rp ' . number_format($totalTaskBudget, 0, ',', '.') . ') harus sama dengan nilai deal kesepakatan (Rp ' . number_format((float)$dealAmount, 0, ',', '.') . ').',
                ]);
            }

            $taskNum = 1;
            $savedTaskIds = [];
            foreach ($tasksInput as $t) {
                $estDateRaw = !empty($t['estimated_posting_date']) ? trim($t['estimated_posting_date']) : date('Y-m-d');
                $estDate = date('Y-m-d');
                $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
                foreach ($formats as $fmt) {
                    $d = DateTime::createFromFormat($fmt, $estDateRaw);
                    if ($d && $d->format($fmt) === $estDateRaw) {
                        $estDate = $d->format('Y-m-d');
                        break;
                    }
                }

                $campStart = !empty($campaign['start_date']) ? substr((string)$campaign['start_date'], 0, 10) : null;
                $campEnd   = !empty($campaign['end_date']) ? substr((string)$campaign['end_date'], 0, 10) : null;

                if ($campEnd && $campEnd >= date('Y-m-d') && $estDate < date('Y-m-d')) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Estimasi posting tidak boleh di masa lalu (sebelum hari ini).',
                    ]);
                }

                if ($campStart && $campEnd) {
                    if ($estDate < $campStart || $estDate > $campEnd) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => "Estimasi posting harus di dalam periode campaign ({$campStart} s/d {$campEnd}).",
                        ]);
                    }
                }

                $variantId = !empty($t['variant_id']) ? (int)$t['variant_id'] : 0;
                if ($variantId > 0) {
                    $vExists = $db->table('variants')->where('id', $variantId)->countAllResults();
                    if ($vExists === 0) {
                        $firstV = $db->table('variants')->select('id')->limit(1)->get()->getRowArray();
                        $variantId = $firstV ? (int)$firstV['id'] : null;
                    }
                } else {
                    $firstV = $db->table('variants')->select('id')->limit(1)->get()->getRowArray();
                    $variantId = $firstV ? (int)$firstV['id'] : null;
                }

                if ($variantId) {
                    $ckvExists = $db->table('campaign_kol_variants')
                        ->where('campaign_kol_id', $id)
                        ->where('variant_id', $variantId)
                        ->countAllResults();
                    if ($ckvExists === 0) {
                        $db->table('campaign_kol_variants')->insert([
                            'campaign_kol_id' => $id,
                            'variant_id'      => $variantId,
                            'qty'             => 1,
                            'created_at'      => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                $taskPayload = [
                    'campaign_kol_id'        => $id,
                    'variant_id'             => $variantId,
                    'platform_id'            => !empty($t['platform_id']) ? (int)$t['platform_id'] : 1,
                    'content_type'           => $t['content_type'] ?? 'VT',
                    'task_number'            => $taskNum,
                    'title'                  => !empty($t['title']) ? $t['title'] : ('Task #' . $taskNum),
                    'budget'                 => (float)preg_replace('/[^0-9]/', '', (string)$t['budget']),
                    'rate_amount'            => (float)preg_replace('/[^0-9]/', '', (string)$t['budget']),
                    'currency_code'          => !empty($campaignKol['currency_code']) ? $campaignKol['currency_code'] : 'IDR',
                    'estimated_posting_date' => $estDate,
                    'notes'                  => !empty($t['notes']) ? $t['notes'] : null,
                ];

                if (!empty($t['id'])) {
                    $db->table('tasks')->where('id', $t['id'])->where('campaign_kol_id', $id)->update($taskPayload);
                    $savedTaskIds[] = (int)$t['id'];
                } else {
                    $taskPayload['status']     = 'DRAFT';
                    $taskPayload['created_by'] = session()->get('user_id') ?: 1;
                    $taskPayload['created_at'] = date('Y-m-d H:i:s');
                    $taskPayload['updated_at'] = date('Y-m-d H:i:s');
                    $db->table('tasks')->insert($taskPayload);
                    $newTaskId = $db->insertID();
                    $savedTaskIds[] = $newTaskId;

                    if ($newTaskId && $db->tableExists('briefs')) {
                        $briefTitle = $taskPayload['title'] ?: ('Brief Task #' . $taskNum);
                        $briefContent = $taskPayload['notes'] ?: '';
                        $db->table('briefs')->insert([
                            'task_id'         => $newTaskId,
                            'title'           => $briefTitle,
                            'content'         => $briefContent,
                            'guidelines'      => $briefContent,
                            'file_path'       => null,
                            'revision_count'  => 0,
                            'status'          => 'DRAFT',
                            'approval_status' => 'DRAFT',
                            'created_by'      => $taskPayload['created_by'],
                            'submitted_by'    => $taskPayload['created_by'],
                            'created_at'      => date('Y-m-d H:i:s'),
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                $taskNum++;
            }

            if (!empty($savedTaskIds)) {
                $db->table('tasks')->where('campaign_kol_id', $id)->whereNotIn('id', $savedTaskIds)->delete();
            }
            $this->campaignKolModel->update($id, ['task_count' => count($savedTaskIds)]);
        } else {
            // When status is NEGOTIATION, REJECTED, or CANCELLED, remove all tasks and junction variants
            $db->table('tasks')->where('campaign_kol_id', $id)->delete();
            $db->table('campaign_kol_variants')->where('campaign_kol_id', $id)->delete();
            $this->campaignKolModel->update($id, ['task_count' => 0]);
        }

        // Handle Shipment and Shipment Items (Step 2)
        $shipmentInput      = $this->request->getPost('shipment');
        $shipmentItemsInput = $this->request->getPost('shipment_items');

        if (in_array($status, $taskSyncAllowed) && is_array($shipmentInput) && !empty($shipmentInput['recipient_name'])) {
            $existingShipment = $db->table('kol_shipments')->where('campaign_kol_id', $id)->get()->getRowArray();
            $rawFee           = !empty($shipmentInput['shipping_fee']) ? $shipmentInput['shipping_fee'] : 0;
            $shippingFee      = (float)preg_replace('/[^0-9]/', '', (string)$rawFee);
            $shipmentStatus   = !empty($shipmentInput['status']) ? $shipmentInput['status'] : 'PENDING';

            $shipmentData = [
                'campaign_kol_id'  => $id,
                'recipient_name'   => $shipmentInput['recipient_name'],
                'recipient_phone'  => $shipmentInput['recipient_phone'] ?? null,
                'shipping_address' => !empty($shipmentInput['shipping_address']) ? $shipmentInput['shipping_address'] : '-',
                'courier'          => $shipmentInput['courier'] ?? null,
                'courier_name'     => $shipmentInput['courier'] ?? null,
                'tracking_no'      => $shipmentInput['tracking_no'] ?? null,
                'tracking_number'  => $shipmentInput['tracking_no'] ?? null,
                'shipping_fee'     => $shippingFee,
                'status'           => $shipmentStatus,
                'shipping_status'  => $shipmentStatus,
                'notes'            => $shipmentInput['notes'] ?? null,
                'updated_at'       => date('Y-m-d H:i:s'),
            ];

            if (!empty($shipmentInput['shipped_at'])) {
                $shipmentData['shipped_at'] = $shipmentInput['shipped_at'];
            }
            if (!empty($shipmentInput['delivered_at'])) {
                $shipmentData['delivered_at'] = $shipmentInput['delivered_at'];
            }

            if ($existingShipment) {
                $shipmentId = (int)$existingShipment['id'];
                $db->table('kol_shipments')->where('id', $shipmentId)->update($shipmentData);
            } else {
                $shipmentData['created_by'] = session()->get('user_id') ?: 1;
                $shipmentData['created_at'] = date('Y-m-d H:i:s');
                $db->table('kol_shipments')->insert($shipmentData);
                $shipmentId = $db->insertID();
            }

            // Sync shipping status to campaign_kols
            $campKolShipStatus = match ($shipmentStatus) {
                'SHIPPED'   => 'SENT',
                'DELIVERED' => 'RECEIVED',
                default     => 'NOT_SENT',
            };
            $this->campaignKolModel->update($id, [
                'shipping_status'      => $campKolShipStatus,
                'shipping_tracking_no' => $shipmentData['tracking_no'],
                'shipping_address'     => $shipmentData['shipping_address'],
                'shipped_at'           => $shipmentData['shipped_at'] ?? null,
            ]);

            // Handle Shipment Items
            if ($shipmentId && is_array($shipmentItemsInput)) {
                $newItemMap = [];
                foreach ($shipmentItemsInput as $item) {
                    $pId  = !empty($item['product_id']) ? (int)$item['product_id'] : 0;
                    $pQty = !empty($item['qty']) ? (int)$item['qty'] : 0;
                    if ($pId > 0 && $pQty > 0) {
                        $newItemMap[$pId] = ($newItemMap[$pId] ?? 0) + $pQty;
                    }
                }

                $db->table('kol_shipment_items')->where('shipment_id', $shipmentId)->delete();
                foreach ($newItemMap as $pId => $qty) {
                    $db->table('kol_shipment_items')->insert([
                        'shipment_id' => $shipmentId,
                        'product_id'  => $pId,
                        'qty'         => $qty,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan data (Database Transaction Error).',
            ]);
        }

        return $this->response->setJSON([
            'success'          => true,
            'message'          => 'Status, konfigurasi task, dan pengiriman produk berhasil disimpan.',
            'new_status'       => $status,
            'jumlah_vt'        => $saveJumlahVt,
            'deal_date'        => $updateData['deal_date'] ?? $campaignKol['deal_date'],
            'deal_amount'      => $updateData['deal_amount'] ?? $campaignKol['deal_amount'],
            'rejection_reason' => $updateData['rejection_reason'] ?? $campaignKol['rejection_reason'],
            'task_count'       => in_array($status, $taskSyncAllowed) && is_array($tasksInput) ? count($tasksInput) : 0,
        ]);
    }

    /**
     * View Dealing KOL Detail
     */
    public function view(int $id)
    {
        $campaignKol = $this->campaignKolModel->getWithDetails($id);
        if (!$campaignKol) {
            return redirect()->to(site_url('campaign_kols'))->with('error', 'Data Dealing KOL tidak ditemukan.');
        }

        $data = [
            'title'        => 'Detail Dealing KOL #' . $id,
            'controller'   => 'CampaignKols',
            'campaign_kol' => $campaignKol,
        ];

        return view('campaign_kols/view', $data);
    }

    /**
     * Delete Dealing KOL with full cascade
     */
    public function delete(int $id): ResponseInterface
    {
        $success = $this->campaignKolModel->cascadeDelete($id);
        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data Dealing KOL berhasil dihapus beserta seluruh alur tugasnya.',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menghapus data Dealing KOL.',
        ]);
    }

    /**
     * Bulk Delete Dealing KOLs
     */
    public function bulkDelete(): ResponseInterface
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pilih setidaknya satu data Dealing KOL.',
            ]);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if ($this->campaignKolModel->cascadeDelete((int)$id)) {
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "{$deletedCount} data Dealing KOL berhasil dihapus.",
        ]);
    }

    /**
     * Export Dealing KOLs to CSV
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id'),
            'status'      => $this->request->getGet('status'),
        ];

        $rows = $this->campaignKolModel->getAllForExport($filters);

        $filename = 'dealing_kols_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'ID',
            'Campaign Name',
            'KOL Name',
            'Status Deal',
            'Jumlah VT',
            'Deal Amount (Rp)',
            'Currency',
            'Deal Date',
            'Task Count',
            'Task Completed',
            'Shipping Status',
            'Tracking No',
            'Collaboration Score',
            'Rejection Reason',
            'Assigned At',
            'Created At',
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['campaign_name'],
                $r['kol_name'],
                $r['status'],
                $r['jumlah_vt'],
                $r['deal_amount'],
                $r['currency_code'],
                $r['deal_date'] ?? '-',
                $r['task_count'],
                $r['task_completed_count'],
                $r['shipping_status'],
                $r['shipping_tracking_no'] ?? '-',
                $r['collaboration_score'] ?? '-',
                $r['rejection_reason'] ?? '-',
                $r['assigned_at'] ?? '-',
                $r['created_at'] ?? '-',
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Daftarkan KOL ke Kampanye (Assign) - CI4 Compatibility
     */
    public function assign(): ResponseInterface
    {
        $campaignId = (int)$this->request->getPost('campaign_id');
        $kolId      = (int)$this->request->getPost('kol_id');
        $dealAmount = (float)$this->request->getPost('deal_amount');
        $totalVt    = (int)($this->request->getPost('total_vt') ?? 1);
        $notes      = trim($this->request->getPost('notes') ?? '');

        if (!$campaignId || !$kolId || $dealAmount <= 0 || $totalVt <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter penugasan KOL tidak lengkap atau tidak valid.',
            ]);
        }

        $exists = $this->campaignKolModel
            ->where('campaign_id', $campaignId)
            ->where('kol_id', $kolId)
            ->first();

        if ($exists) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Talent KOL ini sudah terdaftar di kampanye ini.',
            ]);
        }

        $assignedId = $this->campaignKolModel->assignKolWithTasks(
            $campaignId,
            $kolId,
            $dealAmount,
            $totalVt,
            $notes,
            [],
            session()->get('user_id') ?: 1
        );

        return $this->response->setJSON([
            'success'     => true,
            'message'     => 'Talent KOL berhasil didaftarkan dan ' . $totalVt . ' draf tugas video (VT) otomatis dibuat.',
            'assigned_id' => $assignedId,
        ]);
    }

    /**
     * Hapus penugasan KOL dari kampanye - CI4 Compatibility
     */
    public function remove(int $id): ResponseInterface
    {
        $ck = $this->campaignKolModel->find($id);
        if (!$ck) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data penugasan tidak ditemukan.',
            ]);
        }

        $success = $this->campaignKolModel->cascadeDelete($id);

        return $this->response->setJSON([
            'success' => $success,
            'message' => $success ? 'Penugasan KOL berhasil dihapus beserta draf tugasnya.' : 'Gagal menghapus penugasan KOL.',
        ]);
    }

    /**
     * Submit Rating & Review untuk KOL dalam Kampanye - CI4 Compatibility
     */
    public function submitReview(): ResponseInterface
    {
        $kolId      = (int)$this->request->getPost('kol_id');
        $campaignId = (int)$this->request->getPost('campaign_id');
        $rating     = (int)$this->request->getPost('rating');
        $reviewText = trim($this->request->getPost('review_text') ?? '');
        $userId     = session()->get('user_id') ?: 1;

        if (!$kolId || $rating < 1 || $rating > 5) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rating harus bernilai 1 sampai 5 bintang.',
            ]);
        }

        $db = \Config\Database::connect();
        
        $existing = $db->table('kol_reviews')
            ->where('kol_id', $kolId)
            ->where('campaign_id', $campaignId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('kol_reviews')->where('id', $existing['id'])->update([
                'rating'      => $rating,
                'review_text' => $reviewText,
                'reviewed_by' => $userId,
            ]);
            $action = 'UPDATE';
            $reviewId = $existing['id'];
        } else {
            $db->table('kol_reviews')->insert([
                'kol_id'      => $kolId,
                'campaign_id' => $campaignId ?: null,
                'rating'      => $rating,
                'review_text' => $reviewText,
                'reviewed_by' => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $action = 'CREATE';
            $reviewId = $db->insertID();
        }

        if (class_exists('\App\Models\AuditLogModel')) {
            \App\Models\AuditLogModel::log(
                $action,
                'kol_reviews',
                $reviewId,
                null,
                ['rating' => $rating, 'review_text' => $reviewText, 'kol_id' => $kolId, 'campaign_id' => $campaignId],
                $userId
            );
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Review dan rating berhasil disimpan.',
        ]);
    }

    /**
     * Tinjau Lamaran Kampanye (Terima / Tolak) - CI4 Compatibility
     */
    public function reviewApplication(): ResponseInterface
    {
        $applicationId = (int)$this->request->getPost('application_id');
        $action        = strtoupper(trim($this->request->getPost('action') ?? ''));
        $dealAmount    = (float)$this->request->getPost('deal_amount');
        $totalVt       = (int)($this->request->getPost('total_vt') ?? 1);
        $notes         = trim($this->request->getPost('notes') ?? '');
        $userId        = session()->get('user_id') ?: 1;

        $db = \Config\Database::connect();
        $app = $db->table('campaign_applications')->where('id', $applicationId)->get()->getRowArray();
        if (!$app) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data lamaran tidak ditemukan.',
            ]);
        }

        if ($action === 'ACCEPT') {
            $exists = $this->campaignKolModel
                ->where('campaign_id', $app['campaign_id'])
                ->where('kol_id', $app['kol_id'])
                ->first();

            if (!$exists) {
                $this->campaignKolModel->assignKolWithTasks(
                    (int)$app['campaign_id'],
                    (int)$app['kol_id'],
                    $dealAmount > 0 ? $dealAmount : (float)$app['proposed_rate'],
                    $totalVt > 0 ? $totalVt : 1,
                    $notes ?: ($app['pitch_notes'] ?? 'Diterima dari lamaran terbuka'),
                    [],
                    $userId
                );
            }

            $db->table('campaign_applications')->where('id', $applicationId)->update([
                'status'      => 'ACCEPTED',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lamaran berhasil diterima dan KOL otomatis ditugaskan ke kampanye.',
            ]);
        } elseif ($action === 'REJECT') {
            $db->table('campaign_applications')->where('id', $applicationId)->update([
                'status'      => 'REJECTED',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lamaran kampanye telah ditolak.',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Aksi peninjauan tidak valid.',
        ]);
    }
}
