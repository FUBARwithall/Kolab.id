<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table            = 'campaign_kol_contracts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'contract_template_id',
        'template_id',
        'contract_no',
        'contract_number',
        'contract_file',
        'contract_html',
        'terms_and_conditions',
        'clauses_rendered',
        'kol_signature_image',
        'admin_signature_image',
        'signature_image',
        'status',
        'signed_at',
        'expired_at',
        'created_by',
        'signed_ip',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert = ['syncTwinColumns'];
    protected $beforeUpdate = ['syncTwinColumns'];

    // Contract Status Constants
    public const STATUS_DRAFT                  = 'DRAFT';
    public const STATUS_PENDING                = 'PENDING';
    public const STATUS_WAITING_KOL_SIGNATURE  = 'WAITING_KOL_SIGNATURE';
    public const STATUS_SENT                   = 'SENT';
    public const STATUS_KOL_REVIEWED           = 'KOL_REVIEWED';
    public const STATUS_SIGNED                 = 'SIGNED';
    public const STATUS_ACTIVE                 = 'ACTIVE';
    public const STATUS_COMPLETED              = 'COMPLETED';
    public const STATUS_EXPIRED                = 'EXPIRED';
    public const STATUS_CANCELLED              = 'CANCELLED';

    /**
     * Normalization callback to synchronize legacy and modern twin columns
     */
    protected function syncTwinColumns(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }

        // Sync contract_no <-> contract_number
        if (isset($data['data']['contract_no']) && !isset($data['data']['contract_number'])) {
            $data['data']['contract_number'] = $data['data']['contract_no'];
        } elseif (isset($data['data']['contract_number']) && !isset($data['data']['contract_no'])) {
            $data['data']['contract_no'] = $data['data']['contract_number'];
        }

        // Sync template_id <-> contract_template_id
        if (isset($data['data']['contract_template_id']) && !isset($data['data']['template_id'])) {
            $data['data']['template_id'] = $data['data']['contract_template_id'];
        } elseif (isset($data['data']['template_id']) && !isset($data['data']['contract_template_id'])) {
            $data['data']['contract_template_id'] = $data['data']['template_id'];
        }

        // Sync contract_html <-> clauses_rendered
        if (isset($data['data']['contract_html']) && !isset($data['data']['clauses_rendered'])) {
            $data['data']['clauses_rendered'] = $data['data']['contract_html'];
        } elseif (isset($data['data']['clauses_rendered']) && !isset($data['data']['contract_html'])) {
            $data['data']['contract_html'] = $data['data']['clauses_rendered'];
        }

        // Sync kol_signature_image <-> signature_image
        if (isset($data['data']['kol_signature_image']) && !isset($data['data']['signature_image'])) {
            $data['data']['signature_image'] = $data['data']['kol_signature_image'];
        } elseif (isset($data['data']['signature_image']) && !isset($data['data']['kol_signature_image'])) {
            $data['data']['kol_signature_image'] = $data['data']['signature_image'];
        }

        return $data;
    }

    /**
     * DataTables Query with Joins to Campaign, KOL, and Invoice
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'campaign_kol_contracts.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                campaign_kol_contracts.*,
                COALESCE(campaign_kol_contracts.contract_no, campaign_kol_contracts.contract_number) as display_contract_no,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaign_kols.contract_status as kol_contract_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.nik as kol_nik,
                users.avatar as kol_avatar,
                invoices.id as invoice_id,
                invoices.invoice_number,
                invoices.status as invoice_status,
                invoices.grand_total as invoice_grand_total
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_contracts.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left')
            ->join('invoices', 'invoices.campaign_kol_id = campaign_kol_contracts.campaign_kol_id AND invoices.status != "CANCELLED"', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('campaign_kol_contracts.status', $filters['status']);
        }

        if (!empty($filters['kol_id'])) {
            $builder->where('kols.id', (int)$filters['kol_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('campaign_kol_contracts.contract_number', $search)
                ->orLike('campaign_kol_contracts.contract_no', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('campaigns.name', $search)
                ->orLike('kols.name', $search)
                ->orLike('campaign_kol_contracts.status', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get Detailed Contract Information by ID
     */
    public function getContractDetails(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                campaign_kol_contracts.*,
                COALESCE(campaign_kol_contracts.contract_no, campaign_kol_contracts.contract_number) as display_contract_no,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaign_kols.contract_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                campaigns.start_date,
                campaigns.end_date,
                campaigns.description as campaign_snk,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.nik as kol_nik,
                kols.address as kol_address,
                kols.whatsapp as kol_whatsapp,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                contract_templates.title as template_title,
                contract_templates.body_template as template_body,
                creator.name as creator_name,
                invoices.id as invoice_id,
                invoices.invoice_number,
                invoices.status as invoice_status,
                invoices.grand_total as invoice_grand_total
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_contracts.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('contract_templates', 'contract_templates.id = COALESCE(campaign_kol_contracts.template_id, campaign_kol_contracts.contract_template_id)', 'left')
            ->join('users as creator', 'creator.id = campaign_kol_contracts.created_by', 'left')
            ->join('invoices', 'invoices.campaign_kol_id = campaign_kol_contracts.campaign_kol_id AND invoices.status != "CANCELLED"', 'left')
            ->where('campaign_kol_contracts.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get KPI Summary Metrics for Contracts Dashboard
     */
    public function getSummaryMetrics(): array
    {
        $all = $this->db->table($this->table)
            ->select('status, signed_at')
            ->get()
            ->getResultArray();

        $totalCount    = count($all);
        $activeCount   = 0;
        $pendingCount  = 0;
        $draftCount    = 0;

        foreach ($all as $r) {
            $st = strtoupper($r['status']);
            if (in_array($st, [self::STATUS_ACTIVE, self::STATUS_SIGNED, self::STATUS_COMPLETED])) {
                $activeCount++;
            } elseif (in_array($st, [self::STATUS_PENDING, self::STATUS_WAITING_KOL_SIGNATURE, self::STATUS_SENT])) {
                $pendingCount++;
            } else {
                $draftCount++;
            }
        }

        return [
            'total_contracts'    => $totalCount,
            'active_signed'      => $activeCount,
            'active_contracts'   => $activeCount,
            'signed_contracts'   => $activeCount,
            'waiting_signature'  => $pendingCount,
            'pending_contracts'  => $pendingCount,
            'draft_contracts'    => $draftCount,
        ];
    }

    /**
     * Get Contracts list for KOL Portal (my_contracts)
     */
    public function getKolsContracts(?int $kolId = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                campaign_kol_contracts.*,
                COALESCE(campaign_kol_contracts.contract_no, campaign_kol_contracts.contract_number) as display_contract_no,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                kols.name as kol_name,
                invoices.id as invoice_id,
                invoices.invoice_number,
                invoices.status as invoice_status,
                invoices.grand_total as invoice_grand_total
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_contracts.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('invoices', 'invoices.campaign_kol_id = campaign_kols.id AND invoices.status != "CANCELLED"', 'left');

        if ($kolId) {
            $builder->where('campaign_kols.kol_id', $kolId);
        }

        return $builder->orderBy('campaign_kol_contracts.id', 'DESC')->get()->getResultArray();
    }

    /**
     * Generate contract draft from Campaign KOL deal with dynamic template support
     */
    public function generateContract(int $campaignKolId, ?int $templateId = null, array $additionalParams = []): int
    {
        $db = $this->db;
        $ck = $db->table('campaign_kols')
            ->select('campaign_kols.*, campaigns.title as campaign_title, campaigns.name as campaign_name, campaigns.brand_name, campaigns.start_date, campaigns.end_date, campaigns.description as campaign_snk, kols.name as kol_name, kols.nik, kols.address, kols.whatsapp, kols.bank_name, kols.bank_account, kols.bank_holder')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('campaign_kols.id', $campaignKolId)
            ->get()
            ->getRowArray();

        if (!$ck) {
            return 0;
        }

        // Generate contract number CTR-YYYYMMDD-XXXX
        $today = date('Ymd');
        $lastRow = $db->table($this->table)
            ->like('contract_no', 'CTR-' . $today)
            ->orLike('contract_number', 'CTR-' . $today)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($lastRow) {
            $numStr = $lastRow['contract_no'] ?: $lastRow['contract_number'];
            $parts = explode('-', $numStr);
            $nextNo = sprintf('%04d', intval(end($parts)) + 1);
        } else {
            $nextNo = '0001';
        }
        $contractNumber = 'CTR-' . $today . '-' . $nextNo;

        // Default S&K
        $defaultSnk = "1. Pihak Kedua wajib memproduksi dan menayangkan konten promosi sesuai brief, format, dan jadwal yang disepakati.\n"
            . "2. Pihak Kedua dilarang menghapus atau mengarsipkan konten yang telah diunggah sekurang-kurangnya selama 30 (tiga puluh) hari kalender.\n"
            . "3. Pihak Pertama (Brand) berhak menggunakan materi konten untuk keperluan promosi dan whitelisting digital ads selama 90 (sembilan puluh) hari.\n"
            . "4. Pihak Kedua bersedia melakukan revisi minor maksimal sebanyak 2 (dua) kali sebelum materi konten dipublikasikan.\n"
            . "5. Honorarium diproses sesuai invoice yang diterbitkan di awal setelah penandatanganan perjanjian ini.";

        $snk = !empty($additionalParams['terms_and_conditions']) ? $additionalParams['terms_and_conditions'] : (!empty($ck['campaign_snk']) ? $ck['campaign_snk'] : $defaultSnk);

        // Find template
        $tplRow = null;
        if ($templateId) {
            $tplRow = $db->table('contract_templates')->where('id', $templateId)->get()->getRowArray();
        } else {
            $tplRow = $db->table('contract_templates')->where('is_active', 1)->orderBy('id', 'ASC')->get()->getRowArray();
        }

        $campaignTitle = $ck['campaign_title'] ?: ($ck['campaign_name'] ?? 'Kampanye KOL');
        $brandName     = $ck['brand_name'] ?: 'KOLAB.ID / Brand Partner';

        if ($tplRow && (!empty($tplRow['body_template']) || !empty($tplRow['content']))) {
            $tplContent = !empty($tplRow['body_template']) ? $tplRow['body_template'] : $tplRow['content'];

            $replacements = [
                '{nomor_kontrak}'       => $contractNumber,
                '{CONTRACT_NO}'         => $contractNumber,
                '{tanggal}'             => date('d F Y'),
                '{CURRENT_DATE}'        => date('d F Y'),
                '{brand_name}'          => $brandName,
                '{BRAND_NAME}'          => $brandName,
                '{campaign_title}'      => $campaignTitle,
                '{CAMPAIGN_NAME}'       => $campaignTitle,
                '{kol_name}'            => $ck['kol_name'],
                '{KOL_NAME}'            => $ck['kol_name'],
                '{kol_nik}'             => $ck['nik'] ?: '-',
                '{KOL_KTP}'             => $ck['nik'] ?: '-',
                '{kol_address}'         => $ck['address'] ?: '-',
                '{KOL_ADDRESS}'         => $ck['address'] ?: '-',
                '{kol_whatsapp}'        => $ck['whatsapp'] ?: '-',
                '{deal_amount}'         => 'Rp ' . number_format((float)$ck['deal_amount'], 0, ',', '.'),
                '{DEAL_AMOUNT}'         => 'Rp ' . number_format((float)$ck['deal_amount'], 0, ',', '.'),
                '{total_vt}'            => (string)$ck['total_vt'],
                '{kol_bank_name}'       => $ck['bank_name'] ?: '-',
                '{BANK_NAME}'           => $ck['bank_name'] ?: '-',
                '{kol_bank_account}'    => $ck['bank_account'] ?: '-',
                '{BANK_ACCOUNT}'        => $ck['bank_account'] ?: '-',
                '{kol_bank_holder}'     => $ck['bank_holder'] ?: '-',
                '{BANK_OWNER}'          => $ck['bank_holder'] ?: '-',
                '{TERMS_AND_CONDITIONS}'=> nl2br(htmlspecialchars($snk)),
            ];

            $clauses = str_replace(array_keys($replacements), array_values($replacements), $tplContent);
            $usedTemplateId = (int)$tplRow['id'];
        } else {
            $clauses = "PERJANJIAN KERJASAMA KREATIF KOL / INFLUENCER\nNomor: {$contractNumber}\n\n";
            $clauses .= "Pada hari ini (" . date('d F Y') . "), disepakati kerjasama pembuatan dan penayangan konten promosi antara:\n";
            $clauses .= "PIHAK PERTAMA (Brand / KOLAB.ID)\n";
            $clauses .= "Nama Kampanye : {$campaignTitle}\n";
            $clauses .= "Brand Partner : {$brandName}\n\n";
            $clauses .= "PIHAK KEDUA (Talent KOL)\n";
            $clauses .= "Nama Lengkap  : {$ck['kol_name']}\n";
            $clauses .= "NIK / No. KTP : " . ($ck['nik'] ?: '-') . "\n";
            $clauses .= "Alamat        : " . ($ck['address'] ?: '-') . "\n\n";
            $clauses .= "RUANG LINGKUP PEKERJAAN (SOW):\n";
            $clauses .= "- Jumlah Video (VT): {$ck['total_vt']} Video Promosi\n";
            $clauses .= "- Kompensasi / Fee : Rp " . number_format((float)$ck['deal_amount'], 0, ',', '.') . "\n";
            $clauses .= "- Rekening Bank    : {$ck['bank_name']} No. {$ck['bank_account']} a/n {$ck['bank_holder']}\n\n";
            $clauses .= "SYARAT DAN KETENTUAN (S&K):\n" . $snk;
            $usedTemplateId = null;
        }

        $userId = 1;
        if (session_status() === PHP_SESSION_ACTIVE && function_exists('session')) {
            try {
                $userId = session('user_id') ?? 1;
            } catch (\Throwable $e) {
                $userId = 1;
            }
        }

        $contractData = [
            'campaign_kol_id'      => $campaignKolId,
            'contract_template_id' => $usedTemplateId,
            'template_id'          => $usedTemplateId,
            'contract_no'          => $contractNumber,
            'contract_number'      => $contractNumber,
            'terms_and_conditions' => $snk,
            'contract_html'        => $clauses,
            'clauses_rendered'     => $clauses,
            'status'               => self::STATUS_DRAFT,
            'created_by'           => $userId,
            'expired_at'           => !empty($ck['end_date']) ? $ck['end_date'] : date('Y-m-d', strtotime('+90 days')),
        ];

        // Check if exists
        $existing = $this->where('campaign_kol_id', $campaignKolId)->whereNotIn('status', ['VOID', 'CANCELLED'])->first();
        if ($existing) {
            $this->update($existing['id'], $contractData);
            return (int)$existing['id'];
        }

        return (int) $this->insert($contractData, true);
    }

    /**
     * Auto Generate Initial Invoice for Contract (Feature Parity with CI3)
     */
    public function autoGenerateInvoiceForContract(int $contractId, ?int $actorUserId = null): int|bool
    {
        $contract = $this->find($contractId);
        if (!$contract) {
            return false;
        }

        $db = $this->db;
        $ck = $db->table('campaign_kols')->where('id', $contract['campaign_kol_id'])->get()->getRowArray();
        if (!$ck) {
            return false;
        }

        // Check if active invoice already exists for this deal
        $existing = $db->table('invoices')
            ->where('campaign_kol_id', $ck['id'])
            ->whereNotIn('status', [InvoiceModel::STATUS_CANCELLED])
            ->get()
            ->getRowArray();
        if ($existing) {
            return (int) $existing['id'];
        }

        $kol = $db->table('kols')->where('id', $ck['kol_id'])->get()->getRowArray();
        $subtotal = (float)($ck['deal_amount'] ?? 0);

        // Calculate Tax: PPh 21 (2.5% if NPWP exists, 3% if no NPWP)
        $taxType = 'NONE';
        $taxRatePercent = 0.00;
        $taxAmount = 0.00;

        if ($subtotal > 0) {
            if (!empty($kol['npwp']) || !empty($kol['npwp_number'])) {
                $taxType = 'PPH21';
                $taxRatePercent = 2.50;
                $taxAmount = round($subtotal * 0.025, 2);
            } else {
                $taxType = 'PPH21';
                $taxRatePercent = 3.00;
                $taxAmount = round($subtotal * 0.03, 2);
            }
        }

        $grandTotal = max(0, $subtotal - $taxAmount);

        // Generate invoice number
        $today = date('Ymd');
        $lastInvoice = $db->table('invoices')
            ->like('invoice_number', 'INV-' . $today)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice['invoice_number']);
            $nextNo = sprintf('%04d', intval(end($parts)) + 1);
        } else {
            $nextNo = '0001';
        }
        $invoiceNumber = 'INV-' . $today . '-' . $nextNo;

        $userId = $actorUserId ?? 1;

        $invoiceData = [
            'campaign_kol_id' => $ck['id'],
            'invoice_number'  => $invoiceNumber,
            'subtotal'        => $subtotal,
            'tax_type'        => $taxType,
            'tax_rate'        => $taxRatePercent,
            'tax_amount'      => $taxAmount,
            'grand_total'     => $grandTotal,
            'status'          => 'SUBMITTED',
            'due_date'        => date('Y-m-d', strtotime('+14 days')),
            'created_by'      => $userId,
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $db->table('invoices')->insert($invoiceData);
        $invoiceId = (int) $db->insertID();

        if ($invoiceId) {
            $kolName = $kol['name'] ?? 'Talent KOL';
            $ctrNo   = $contract['contract_no'] ?: ($contract['contract_number'] ?? 'Kontrak');
            $notes   = "Invoice resmi diterbitkan oleh KOL ({$kolName}) melalui Portal KOL setelah MoU {$ctrNo} aktif. Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . ", PPh 21: Rp " . number_format($taxAmount, 0, ',', '.') . ", Net: Rp " . number_format($grandTotal, 0, ',', '.');

            // Insert audit history if table exists
            if ($db->tableExists('invoice_histories')) {
                $db->table('invoice_histories')->insert([
                    'invoice_id'  => $invoiceId,
                    'from_status' => 'DRAFT',
                    'to_status'   => 'SUBMITTED',
                    'changed_by'  => $userId,
                    'notes'       => $notes,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }

            return $invoiceId;
        }

        return false;
    }

    /**
     * Get All Contracts for Export (CSV / PDF)
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                campaign_kol_contracts.*,
                COALESCE(campaign_kol_contracts.contract_no, campaign_kol_contracts.contract_number) as display_contract_no,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaigns.title as campaign_title,
                campaigns.name as campaign_name,
                campaigns.brand_name,
                kols.name as kol_name,
                kols.bank_name,
                kols.bank_account,
                kols.bank_holder,
                invoices.invoice_number,
                invoices.status as invoice_status
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_contracts.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('invoices', 'invoices.campaign_kol_id = campaign_kols.id AND invoices.status != "CANCELLED"', 'left')
            ->orderBy('campaign_kol_contracts.id', 'DESC');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('campaign_kol_contracts.status', $filters['status']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * CI3 Compatibility Helper: store
     */
    public function store(array|object $data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (empty($data['created_by'])) {
            $uid = 1;
            if (session_status() === PHP_SESSION_ACTIVE && function_exists('session')) {
                try {
                    $uid = session('user_id') ?? 1;
                } catch (\Throwable $e) {
                    $uid = 1;
                }
            }
            $data['created_by'] = $uid;
        }

        if (empty($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data, true);
    }

    /**
     * CI3 Compatibility Helper: change
     */
    public function change(int $id, array|object $data): bool
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return (bool) $this->update($id, $data);
    }

    /**
     * CI3 Compatibility Helper: remove
     */
    public function remove(int $id): bool
    {
        return (bool) $this->delete($id);
    }
}
