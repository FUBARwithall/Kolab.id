<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignKolModel extends Model
{
    protected $table            = 'campaign_kols';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id',
        'kol_id',
        'deal_scope',
        'jumlah_vt',
        'total_vt',
        'deal_date',
        'deal_amount',
        'currency_code',
        'status',
        'rejection_reason',
        'task_count',
        'task_completed_count',
        'shipping_status',
        'shipped_at',
        'shipping_address',
        'shipping_tracking_no',
        'collaboration_score',
        'assigned_by',
        'assigned_at',
        'contract_status',
        'payment_status',
        'notes',
        'created_by',
        'portal_token',
        'portal_token_expires_at',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Status Enums matching CI3
    public const STATUS_ASSIGNED    = 'ASSIGNED';
    public const STATUS_NEGOTIATION = 'NEGOTIATION';
    public const STATUS_ACTIVE      = 'ACTIVE';
    public const STATUS_COMPLETED   = 'COMPLETED';
    public const STATUS_REJECTED    = 'REJECTED';
    public const STATUS_CANCELLED   = 'CANCELLED';

    /**
     * Get KPI Summary Metrics
     */
    public function getSummaryMetrics(?int $campaignId = null): array
    {
        $db = \Config\Database::connect();
        $b = $db->table($this->table);

        if ($campaignId) {
            $b->where('campaign_id', $campaignId);
        }

        $row = $b->select("
            COUNT(*) as total_deals,
            SUM(CASE WHEN status IN ('ASSIGNED', 'ACTIVE', 'COMPLETED') THEN 1 ELSE 0 END) as active_deals,
            SUM(CASE WHEN status = 'NEGOTIATION' THEN 1 ELSE 0 END) as negotiation_deals,
            SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_deals,
            SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected_deals,
            SUM(CASE WHEN status IN ('ASSIGNED', 'ACTIVE', 'COMPLETED') THEN deal_amount ELSE 0 END) as total_deal_amount,
            SUM(jumlah_vt) as total_tasks,
            AVG(NULLIF(collaboration_score, 0)) as avg_score
        ")->get()->getRowArray();

        return [
            'total_deals'        => (int)($row['total_deals'] ?? 0),
            'active_deals'       => (int)($row['active_deals'] ?? 0),
            'negotiation_deals'  => (int)($row['negotiation_deals'] ?? 0),
            'completed_deals'    => (int)($row['completed_deals'] ?? 0),
            'rejected_deals'     => (int)($row['rejected_deals'] ?? 0),
            'total_deal_amount'  => (float)($row['total_deal_amount'] ?? 0),
            'total_tasks'        => (int)($row['total_tasks'] ?? 0),
            'avg_score'          => round((float)($row['avg_score'] ?? 0), 2),
        ];
    }

    /**
     * DataTables Server-Side query with joins and subqueries
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'campaign_kols.id', string $orderDir = 'DESC'): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table)
            ->select('
                campaign_kols.*,
                COALESCE(campaigns.title, campaigns.name) as campaign_title,
                campaigns.id as campaign_id,
                campaigns.budget as campaign_budget,
                campaigns.start_date as campaign_start_date,
                campaigns.end_date as campaign_end_date,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address,
                kols.status as kol_account_status,
                kols.is_blacklisted,
                kols.blacklist_reason,
                users.avatar as kol_avatar,
                tiers.name as tier_name,
                assigned_user.name as assigned_by_name,
                (SELECT status FROM campaign_kol_contracts WHERE campaign_kol_contracts.campaign_kol_id = campaign_kols.id AND status != "CANCELLED" ORDER BY id DESC LIMIT 1) as latest_contract_status,
                (SELECT contract_no FROM campaign_kol_contracts WHERE campaign_kol_contracts.campaign_kol_id = campaign_kols.id AND status != "CANCELLED" ORDER BY id DESC LIMIT 1) as latest_contract_no,
                (SELECT invoice_number FROM invoices WHERE invoices.campaign_kol_id = campaign_kols.id ORDER BY id DESC LIMIT 1) as invoice_no,
                (SELECT status FROM invoices WHERE invoices.campaign_kol_id = campaign_kols.id ORDER BY id DESC LIMIT 1) as latest_invoice_status,
                (SELECT status FROM kol_shipments WHERE kol_shipments.campaign_kol_id = campaign_kols.id ORDER BY id DESC LIMIT 1) as latest_shipment_status,
                (SELECT tracking_no FROM kol_shipments WHERE kol_shipments.campaign_kol_id = campaign_kols.id ORDER BY id DESC LIMIT 1) as latest_tracking_no
            ')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('tiers', 'tiers.id = kols.tier_id', 'left')
            ->join('users as assigned_user', 'assigned_user.id = campaign_kols.assigned_by', 'left');

        // Apply filters
        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('campaign_kols.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('campaign_kols.status', $filters['status']);
        }

        if (!empty($filters['shipping_status'])) {
            $builder->where('campaign_kols.shipping_status', $filters['shipping_status']);
        }

        // Search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('kols.name', $search)
                ->orLike('campaign_kols.rejection_reason', $search)
                ->orLike('campaign_kols.deal_scope', $search)
                ->orLike('campaign_kols.shipping_tracking_no', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get single record with all relationships (campaign, kol, platforms, tasks, shipment, contract)
     */
    public function getWithDetails(int $id): ?array
    {
        $db = \Config\Database::connect();
        $row = $db->table($this->table)
            ->select('
                campaign_kols.*,
                COALESCE(campaigns.title, campaigns.name) as campaign_title,
                campaigns.budget as campaign_budget,
                campaigns.start_date as campaign_start_date,
                campaigns.end_date as campaign_end_date,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.city as kol_city,
                kols.address as kol_address,
                kols.postal_code as kol_postal_code,
                kols.status as kol_status,
                kols.is_blacklisted,
                kols.blacklist_reason,
                users.avatar as kol_avatar,
                tiers.name as tier_name,
                assigned_user.name as assigned_by_name
            ')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('tiers', 'tiers.id = kols.tier_id', 'left')
            ->join('users as assigned_user', 'assigned_user.id = campaign_kols.assigned_by', 'left')
            ->where('campaign_kols.id', $id)
            ->get()
            ->getRowArray();

        if (!$row) {
            return null;
        }

        // Attach Tasks
        $row['tasks'] = $db->table('tasks')
            ->select('tasks.*, platforms.name as platform_name, platforms.code as platform_code, platforms.icon as platform_icon, variants.name as variant_name')
            ->join('platforms', 'platforms.id = tasks.platform_id', 'left')
            ->join('variants', 'variants.id = tasks.variant_id', 'left')
            ->where('tasks.campaign_kol_id', $id)
            ->orderBy('tasks.task_number', 'ASC')
            ->get()
            ->getResultArray();

        // Attach KOL Platforms
        $row['platforms'] = $db->table('kol_platforms')
            ->select('kol_platforms.*, platforms.name as platform_name, platforms.code as platform_code, platforms.icon as platform_icon')
            ->join('platforms', 'platforms.id = kol_platforms.platform_id', 'inner')
            ->where('kol_platforms.kol_id', $row['kol_id'])
            ->where('kol_platforms.is_active', 1)
            ->get()
            ->getResultArray();

        // Attach Shipment & Items
        $row['shipment'] = $db->table('kol_shipments')
            ->where('campaign_kol_id', $id)
            ->get()
            ->getRowArray();

        if ($row['shipment']) {
            $row['shipment']['items'] = $db->table('kol_shipment_items')
                ->select('kol_shipment_items.*, products.name as product_name, products.sku as product_sku, products.stock as product_stock, products.price as product_price')
                ->join('products', 'products.id = kol_shipment_items.product_id', 'left')
                ->where('kol_shipment_items.shipment_id', $row['shipment']['id'])
                ->get()
                ->getResultArray();
        } else {
            $row['shipment'] = null;
        }

        // Attach Contracts
        $row['contracts'] = $db->table('campaign_kol_contracts')
            ->where('campaign_kol_id', $id)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        // Attach Invoices
        $row['invoices'] = $db->table('invoices')
            ->where('campaign_kol_id', $id)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $row;
    }

    /**
     * Auto-draft contract if deal status is ASSIGNED, ACTIVE, or COMPLETED and no contract exists yet
     */
    public function autoDraftContract(int $campaignKolId): ?int
    {
        $db = \Config\Database::connect();
        
        $existing = $db->table('campaign_kol_contracts')
            ->where('campaign_kol_id', $campaignKolId)
            ->whereNotIn('status', ['VOID', 'CANCELLED'])
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int)$existing['id'];
        }

        $year = date('Y');
        $month = date('m');
        $prefix = "CTR/{$year}/{$month}/";

        $last = $db->table('campaign_kol_contracts')
            ->like('contract_no', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $nextNum = 1;
        if ($last && !empty($last['contract_no'])) {
            $parts = explode('/', $last['contract_no']);
            $nextNum = (int)end($parts) + 1;
        }
        $contractNo = $prefix . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

        $contractData = [
            'campaign_kol_id' => $campaignKolId,
            'contract_no'     => $contractNo,
            'contract_number' => $contractNo,
            'status'          => 'DRAFT',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $db->table('campaign_kol_contracts')->insert($contractData);
        $newContractId = $db->insertID();

        // Update contract_status in campaign_kols
        $this->update($campaignKolId, ['contract_status' => 'DRAFT']);

        return $newContractId;
    }

    /**
     * Full cascading delete identical to CI3 _remove()
     */
    public function cascadeDelete(int $id): bool
    {
        $db = \Config\Database::connect();
        $ck = $this->find($id);
        if (!$ck) {
            return false;
        }

        $db->transStart();

        // 1. Delete tasks and all descendant records
        $tasks = $db->table('tasks')->where('campaign_kol_id', $id)->get()->getResultArray();
        foreach ($tasks as $t) {
            $taskId = $t['id'];

            // Postings & Metrics & Apify Jobs
            $postings = $db->table('postings')->where('task_id', $taskId)->get()->getResultArray();
            foreach ($postings as $p) {
                if ($db->tableExists('apify_jobs')) {
                    $db->table('apify_jobs')->where('posting_id', $p['id'])->delete();
                }
                if ($db->tableExists('posting_metrics')) {
                    $db->table('posting_metrics')->where('posting_id', $p['id'])->delete();
                }
            }
            $db->table('postings')->where('task_id', $taskId)->delete();

            // Drafts & Histories
            $drafts = $db->table('drafts')->where('task_id', $taskId)->get()->getResultArray();
            foreach ($drafts as $d) {
                if ($db->tableExists('draft_review_histories')) {
                    $db->table('draft_review_histories')->where('draft_id', $d['id'])->delete();
                }
            }
            $db->table('drafts')->where('task_id', $taskId)->delete();

            // Briefs & Histories
            $briefs = $db->table('briefs')->where('task_id', $taskId)->get()->getResultArray();
            foreach ($briefs as $b) {
                if ($db->tableExists('brief_approval_histories')) {
                    $db->table('brief_approval_histories')->where('brief_id', $b['id'])->delete();
                }
                if ($db->tableExists('brief_content_histories')) {
                    $db->table('brief_content_histories')->where('brief_id', $b['id'])->delete();
                }
            }
            $db->table('briefs')->where('task_id', $taskId)->delete();

            // Task histories & sentiments
            if ($db->tableExists('task_status_histories')) {
                $db->table('task_status_histories')->where('task_id', $taskId)->delete();
            }
            if ($db->tableExists('task_budget_histories')) {
                $db->table('task_budget_histories')->where('task_id', $taskId)->delete();
            }
            if ($db->tableExists('task_rate_histories')) {
                $db->table('task_rate_histories')->where('task_id', $taskId)->delete();
            }
            if ($db->tableExists('task_sentiments')) {
                $db->table('task_sentiments')->where('task_id', $taskId)->delete();
            }

            // Delete task
            $db->table('tasks')->where('id', $taskId)->delete();
        }

        // 2. Delete shipments and items
        $shipments = $db->table('kol_shipments')->where('campaign_kol_id', $id)->get()->getResultArray();
        foreach ($shipments as $s) {
            $db->table('kol_shipment_items')->where('shipment_id', $s['id'])->delete();
        }
        $db->table('kol_shipments')->where('campaign_kol_id', $id)->delete();

        // 3. Delete contracts
        $db->table('campaign_kol_contracts')->where('campaign_kol_id', $id)->delete();

        // 4. Delete junction variants & reviews
        $db->table('campaign_kol_variants')->where('campaign_kol_id', $id)->delete();
        if ($db->tableExists('score_histories')) {
            $db->table('score_histories')->where('campaign_kol_id', $id)->delete();
        }

        // 5. Delete campaign_kol record
        $this->delete($id);

        $db->transComplete();

        if ($db->transStatus() === true) {
            // Remove uploaded brief / deal_scope files
            if (!empty($ck['deal_scope'])) {
                foreach (explode(',', $ck['deal_scope']) as $filename) {
                    $filename = trim($filename);
                    if ($filename) {
                        $path = FCPATH . 'uploads/campaign_kols/' . $filename;
                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Daftarkan KOL ke Kampanye dan otomatis buatkan draft Tasks (VT)
     * Backwards-compatible with initial CI4 API
     */
    public function assignKolWithTasks(int $campaignId, int $kolId, float $dealAmount, int $totalVt, ?string $notes = null, array $variantIds = [], ?int $userId = null): int
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $campaignKolId = $this->insert([
            'campaign_id'          => $campaignId,
            'kol_id'               => $kolId,
            'deal_amount'          => $dealAmount,
            'jumlah_vt'            => $totalVt,
            'total_vt'             => $totalVt,
            'status'               => self::STATUS_ASSIGNED,
            'deal_date'            => date('Y-m-d'),
            'currency_code'        => 'IDR',
            'task_count'           => $totalVt,
            'task_completed_count' => 0,
            'contract_status'      => 'DRAFT',
            'payment_status'       => 'UNPAID',
            'notes'                => $notes,
            'assigned_by'          => $userId ?: (session()->get('user_id') ?: 1),
            'assigned_at'          => date('Y-m-d H:i:s'),
            'created_by'           => $userId ?: (session()->get('user_id') ?: 1),
        ]);

        // Simpan alokasi varian produk jika ada
        if (!empty($variantIds)) {
            $variantData = [];
            foreach ($variantIds as $vId) {
                $variantData[] = [
                    'campaign_kol_id' => $campaignKolId,
                    'variant_id'      => (int)$vId,
                    'qty'             => 1,
                    'created_at'      => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('campaign_kol_variants')->insertBatch($variantData);
        }

        // Otomatis buat baris tugas (Tasks / VT) sebanyak total_vt
        $ratePerVt = $totalVt > 0 ? ($dealAmount / $totalVt) : $dealAmount;

        for ($vt = 1; $vt <= $totalVt; $vt++) {
            $db->table('tasks')->insert([
                'campaign_kol_id' => $campaignKolId,
                'task_number'     => $vt,
                'title'           => 'Konten Video VT #' . $vt,
                'budget'          => $ratePerVt,
                'rate_amount'     => $ratePerVt,
                'is_rate_locked'  => 0,
                'status'          => 'DRAFT',
                'currency_code'   => 'IDR',
                'deadline'        => null,
                'created_by'      => $userId ?: (session()->get('user_id') ?: 1),
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            $newTaskId = $db->insertID();
            if ($newTaskId && $db->tableExists('briefs')) {
                $db->table('briefs')->insert([
                    'task_id'        => $newTaskId,
                    'title'          => 'Brief Konten Video VT #' . $vt,
                    'content'        => $notes ?: '',
                    'file_path'      => null,
                    'revision_count' => 0,
                    'status'         => 'DRAFT',
                    'created_by'     => $userId ?: (session()->get('user_id') ?: 1),
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Auto draft contract
        $this->autoDraftContract($campaignKolId);

        $db->transComplete();

        return $campaignKolId;
    }

    /**
     * Get records for CSV / Excel export
     */
    public function getAllForExport(array $filters = []): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table)
            ->select('
                campaign_kols.id,
                COALESCE(campaigns.title, campaigns.name) as campaign_name,
                kols.name as kol_name,
                campaign_kols.status,
                campaign_kols.jumlah_vt,
                campaign_kols.deal_amount,
                campaign_kols.currency_code,
                campaign_kols.deal_date,
                campaign_kols.task_count,
                campaign_kols.task_completed_count,
                campaign_kols.shipping_status,
                campaign_kols.shipping_tracking_no,
                campaign_kols.collaboration_score,
                campaign_kols.rejection_reason,
                campaign_kols.assigned_at,
                campaign_kols.created_at
            ')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaign_kols.campaign_id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('campaign_kols.status', $filters['status']);
        }

        return $builder->orderBy('campaign_kols.id', 'DESC')->get()->getResultArray();
    }
}
