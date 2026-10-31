<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignKolVariantModel;
use CodeIgniter\HTTP\ResponseInterface;

class CampaignKolVariants extends BaseController
{
    protected CampaignKolVariantModel $ckvModel;

    public function __construct()
    {
        $this->ckvModel = new CampaignKolVariantModel();
    }

    /**
     * Halaman Utama Alokasi Varian KOL (Campaign Kol Variants)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $campaignId    = $this->request->getGet('campaign_id') ?? '';
        $campaignKolId = $this->request->getGet('campaign_kol_id') ?? '';
        $variantId     = $this->request->getGet('variant_id') ?? '';

        $allCampaigns = $db->table('campaigns')
            ->select('id, title, COALESCE(name, title) as name')
            ->orderBy('title', 'ASC')
            ->get()
            ->getResultArray();

        $allDealings = $db->table('campaign_kols ck')
            ->select('ck.id, ck.campaign_id, ck.kol_id, ck.status, COALESCE(c.title, c.name) as campaign_title, k.name as kol_name')
            ->join('campaigns c', 'c.id = ck.campaign_id', 'left')
            ->join('kols k', 'k.id = ck.kol_id', 'left')
            ->whereNotIn('ck.status', ['REJECTED', 'CANCELLED'])
            ->orderBy('c.title', 'ASC')
            ->orderBy('k.name', 'ASC')
            ->get()
            ->getResultArray();

        $allVariants = $db->table('variants v')
            ->select('v.id, v.name, v.sku, p.name as product_name')
            ->join('products p', 'p.id = v.product_id', 'left')
            ->orderBy('v.name', 'ASC')
            ->get()
            ->getResultArray();

        $metrics = $this->ckvModel->getSummaryMetrics($campaignId ? (int)$campaignId : null);

        $data = [
            'title'         => 'Alokasi Varian KOL (Campaign KOL Variants)',
            'controller'    => 'CampaignKolVariants',
            'metrics'       => $metrics,
            'campaignId'    => $campaignId,
            'campaignKolId' => $campaignKolId,
            'variantId'     => $variantId,
            'allCampaigns'  => $allCampaigns,
            'allDealings'   => $allDealings,
            'allVariants'   => $allVariants,
        ];

        return view('campaign_kol_variants/index', $data);
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

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'campaign_kol_variants.id',
            1 => 'campaign_kol_variants.id',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'variants.name',
            5 => 'campaign_kol_variants.qty',
            6 => 'campaign_kol_variants.created_at',
            7 => 'campaign_kol_variants.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'campaign_kol_variants.id';

        $filters = [
            'campaign_id'     => $this->request->getPost('campaign_id'),
            'campaign_kol_id' => $this->request->getPost('campaign_kol_id'),
            'variant_id'      => $this->request->getPost('variant_id'),
        ];

        $result = $this->ckvModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $formattedData[] = [
                'id'              => (int)$row['id'],
                'campaign_kol_id' => (int)$row['campaign_kol_id'],
                'campaign_id'     => (int)$row['campaign_id'],
                'campaign_title'  => esc($row['campaign_title'] ?? '-'),
                'kol_id'          => (int)($row['kol_id'] ?? 0),
                'kol_name'        => esc($row['kol_name'] ?? '-'),
                'kol_whatsapp'    => esc($row['kol_whatsapp'] ?? ''),
                'deal_status'     => $row['deal_status'] ?? 'ASSIGNED',
                'variant_id'      => (int)$row['variant_id'],
                'variant_name'    => esc($row['variant_name'] ?? '-'),
                'variant_sku'     => esc($row['variant_sku'] ?? '-'),
                'product_name'    => esc($row['product_name'] ?? '-'),
                'qty'             => (int)($row['qty'] ?? 1),
                'created_at'      => $row['created_at'] ? date('d M Y H:i', strtotime($row['created_at'])) : '-',
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
        $data = $this->ckvModel->getWithDetails($id);
        if (!$data) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data alokasi varian KOL tidak ditemukan.',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Save (Insert / Update) Allocation
     */
    public function save(): ResponseInterface
    {
        $id            = (int)$this->request->getPost('id');
        $campaignKolId = (int)$this->request->getPost('campaign_kol_id');
        $variantId     = (int)$this->request->getPost('variant_id');
        $qty           = max(1, (int)($this->request->getPost('qty') ?? 1));

        if (!$campaignKolId || !$variantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan pilih Assigned KOL dan Konsep Varian Konten.',
            ]);
        }

        $db = \Config\Database::connect();

        // Check unique pair constraint (campaign_kol_id, variant_id)
        $builder = $db->table('campaign_kol_variants')
            ->where('campaign_kol_id', $campaignKolId)
            ->where('variant_id', $variantId);

        if ($id > 0) {
            $builder->where('id !=', $id);
        }

        $existing = $builder->get()->getRowArray();
        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Varian konten ini sudah dialokasikan pada Dealing KOL yang sama.',
            ]);
        }

        $payload = [
            'campaign_kol_id' => $campaignKolId,
            'variant_id'      => $variantId,
            'qty'             => $qty,
        ];

        if ($id > 0) {
            $this->ckvModel->update($id, $payload);
            $msg = 'Alokasi varian KOL berhasil diperbarui.';
            $savedId = $id;
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $savedId = $this->ckvModel->insert($payload);
            $msg = 'Alokasi varian KOL berhasil ditambahkan.';
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => $msg,
            'saved_id' => $savedId,
        ]);
    }

    /**
     * View detail page
     */
    public function view(int $id)
    {
        $item = $this->ckvModel->getWithDetails($id);
        if (!$item) {
            return redirect()->to(site_url('campaign-kol-variants'))->with('error', 'Data alokasi tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Alokasi Varian KOL #' . $id,
            'controller' => 'CampaignKolVariants',
            'item'       => $item,
        ];

        return view('campaign_kol_variants/view', $data);
    }

    /**
     * Delete single record
     */
    public function delete(int $id): ResponseInterface
    {
        $ckv = $this->ckvModel->find($id);
        if (!$ckv) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data alokasi tidak ditemukan.',
            ]);
        }

        $this->ckvModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Alokasi varian KOL berhasil dihapus.',
        ]);
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(): ResponseInterface
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pilih setidaknya satu data alokasi varian.',
            ]);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if ($this->ckvModel->delete((int)$id)) {
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "{$deletedCount} data alokasi varian KOL berhasil dihapus.",
        ]);
    }

    /**
     * Export to CSV
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id'),
            'variant_id'  => $this->request->getGet('variant_id'),
        ];

        $rows = $this->ckvModel->getAllForExport($filters);

        $filename = 'campaign_kol_variants_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'ID',
            'Campaign Name',
            'KOL Name',
            'Konsep Varian Konten',
            'SKU',
            'Qty',
            'Status Deal',
            'Created At',
        ]);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['campaign_name'],
                $r['kol_name'],
                $r['variant_name'],
                $r['variant_sku'] ?? '-',
                $r['qty'],
                $r['deal_status'],
                $r['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}
