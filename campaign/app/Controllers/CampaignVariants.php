<?php

namespace App\Controllers;

use App\Models\CampaignVariantModel;
use CodeIgniter\HTTP\ResponseInterface;

class CampaignVariants extends BaseController
{
    protected CampaignVariantModel $campaignVariantModel;

    public function __construct()
    {
        $this->campaignVariantModel = new CampaignVariantModel();
    }

    /**
     * Halaman Utama: Daftar Varian Produk Kampanye (Full Parity with CI3 modules/campaign_variants)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $stats = $this->campaignVariantModel->getStats();

        // Dropdown list of campaigns
        $campaigns = $db->table('campaigns')
            ->select('id, title, COALESCE(name, title) as name, status, budget_total, start_date, end_date')
            ->orderBy('title', 'ASC')
            ->get()
            ->getResultArray();

        // Dropdown list of products
        $products = $db->table('products')
            ->select('id, name, sku, unit_price, stock_qty, is_active')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        // Dropdown list of variants with their parent product
        $variants = $db->table('variants v')
            ->select('v.id, v.name, v.sku, v.stock_qty, v.additional_price, v.product_id, p.name as product_name')
            ->join('products p', 'p.id = v.product_id', 'left')
            ->orderBy('p.name', 'ASC')
            ->orderBy('v.name', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'       => 'Varian Produk Kampanye',
            'controller'  => 'CampaignVariants',
            'stats'       => $stats,
            'campaigns'   => $campaigns,
            'products'    => $products,
            'variants'    => $variants,
        ];

        return view('campaign_variants/index', $data);
    }

    /**
     * Endpoint DataTables AJAX Server-Side
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
            0 => 'campaign_variants.id',
            1 => 'campaign_variants.id',
            2 => 'campaigns.title',
            3 => 'products.name',
            4 => 'variants.name',
            5 => 'variants.sku',
            6 => 'variants.additional_price',
            7 => 'variants.stock_qty',
            8 => 'campaign_variants.created_at',
            9 => 'campaign_variants.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'campaign_variants.id';

        $filters = [
            'campaign_id' => $this->request->getPost('campaign_id') ?: null,
            'product_id'  => $this->request->getPost('product_id') ?: null,
            'variant_id'  => $this->request->getPost('variant_id') ?: null,
        ];

        $result = $this->campaignVariantModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $statusBadges = [
            'ACTIVE'    => 'bg-success',
            'COMPLETED' => 'bg-info',
            'DRAFT'     => 'bg-secondary',
            'CANCELLED' => 'bg-danger',
        ];

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusClass = $statusBadges[$row['campaign_status']] ?? 'bg-secondary';

            // Checkbox
            $checkbox = '<input type="checkbox" class="form-check-input row-checkbox" value="' . $row['id'] . '">';

            // Campaign Link & Status
            $campaignTitle = esc($row['campaign_title'] ?: $row['campaign_name'] ?: 'Kampanye #' . $row['campaign_id']);
            $campaignHtml = '
                <div class="d-flex flex-column">
                    <a href="' . base_url('campaigns/view/' . $row['campaign_id']) . '" class="fw-bold text-primary text-decoration-none hover-primary">
                        ' . $campaignTitle . '
                    </a>
                    <div class="mt-1">
                        <span class="badge ' . $statusClass . ' px-2 py-0" style="font-size: 11px;">' . esc($row['campaign_status'] ?? 'DRAFT') . '</span>
                    </div>
                </div>';

            // Product Name & SKU
            $productName = esc($row['product_name'] ?? 'N/A');
            $productSku = esc($row['product_sku'] ?? '-');
            $productHtml = '
                <div class="d-flex flex-column">
                    <span class="fw-semibold text-dark">' . $productName . '</span>
                    <small class="text-muted"><i class="fas fa-barcode me-1"></i>' . $productSku . '</small>
                </div>';

            // Variant Badge & SKU
            $variantName = esc($row['variant_name'] ?? 'N/A');
            $variantSku = esc($row['variant_sku'] ?? '-');
            $variantHtml = '
                <div class="d-flex flex-column">
                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold text-start">' . $variantName . '</span>
                    <small class="text-muted mt-1"><i class="fas fa-tag me-1"></i>' . $variantSku . '</small>
                </div>';

            // Additional Price
            $addPrice = (float)($row['variant_additional_price'] ?? 0);
            $priceFormatted = ($addPrice > 0)
                ? '+ Rp ' . number_format($addPrice, 0, ',', '.')
                : '<span class="text-muted">Standard</span>';

            // Stock
            $stock = (int)($row['variant_stock_qty'] ?? 0);
            $stockBadge = ($stock > 0)
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>' . number_format($stock) . ' unit</span>'
                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times-circle me-1"></i>Habis</span>';

            // Created At
            $createdAt = !empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-';

            // Action buttons
            $actions = '
                <div class="btn-group btn-group-sm" role="group">
                    <a href="' . base_url('campaign-variants/view/' . $row['id']) . '" class="btn btn-outline-info" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-edit" data-id="' . $row['id'] . '" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-delete" data-id="' . $row['id'] . '" data-name="' . esc($variantName) . ' (' . esc($campaignTitle) . ')" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>';

            $formattedData[] = [
                'checkbox'     => $checkbox,
                'id'           => $row['id'],
                'campaign'     => $campaignHtml,
                'product'      => $productHtml,
                'variant'      => $variantHtml,
                'additional'   => $priceFormatted,
                'stock'        => $stockBadge,
                'notes'        => esc($row['notes'] ?: '-'),
                'created_at'   => $createdAt,
                'actions'      => $actions,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data'            => $formattedData,
        ]);
    }

    /**
     * Get single record for edit modal
     */
    public function getOne(int $id)
    {
        $item = $this->campaignVariantModel->getDetail($id);
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data relasi varian kampanye tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * Simpan data Varian Kampanye (Tambah Baru atau Update)
     */
    public function save()
    {
        $id          = $this->request->getPost('id');
        $campaignId  = (int)$this->request->getPost('campaign_id');
        $variantId   = (int)$this->request->getPost('variant_id');
        $notes       = trim((string)$this->request->getPost('notes'));

        // Validation
        if (empty($campaignId) || empty($variantId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan pilih Kampanye dan Varian Produk.',
            ]);
        }

        // Duplicate check
        $excludeId = !empty($id) ? (int)$id : null;
        if ($this->campaignVariantModel->checkExists($campaignId, $variantId, $excludeId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Varian produk ini sudah terdaftar pada kampanye terpilih. Silakan pilih varian lain atau perbarui catatan.',
            ]);
        }

        $saveData = [
            'campaign_id' => $campaignId,
            'variant_id'  => $variantId,
            'notes'       => $notes ?: 'Varian produk sampel kampanye',
        ];

        if (!empty($id)) {
            $this->campaignVariantModel->update($id, $saveData);
            $msg = 'Varian kampanye berhasil diperbarui!';
        } else {
            $saveData['created_at'] = date('Y-m-d H:i:s');
            $this->campaignVariantModel->insert($saveData);
            $msg = 'Varian produk baru berhasil ditambahkan ke kampanye!';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Detail View Page: Menampilkan detail lengkap relasi varian dan kampanye
     */
    public function view(int $id)
    {
        $detail = $this->campaignVariantModel->getDetail($id);
        if (!$detail) {
            return redirect()->to(base_url('campaign-variants'))->with('error', 'Data varian kampanye tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Other variants assigned to the same campaign
        $otherVariants = $db->table('campaign_variants cv')
            ->select('cv.id, cv.notes, cv.created_at, v.name as variant_name, v.sku as variant_sku, v.stock_qty, v.additional_price, p.name as product_name')
            ->join('variants v', 'v.id = cv.variant_id', 'left')
            ->join('products p', 'p.id = v.product_id', 'left')
            ->where('cv.campaign_id', $detail['campaign_id'])
            ->where('cv.id !=', $id)
            ->get()
            ->getResultArray();

        $data = [
            'title'          => 'Detail Varian Kampanye - ' . ($detail['variant_name'] ?? 'Varian'),
            'controller'     => 'CampaignVariants',
            'detail'         => $detail,
            'otherVariants'  => $otherVariants,
        ];

        return view('campaign_variants/view', $data);
    }

    /**
     * Hapus satu data Campaign Variant
     */
    public function delete(int $id)
    {
        $item = $this->campaignVariantModel->find($id);
        if (!$item) {
            $msg = 'Data varian kampanye tidak ditemukan.';
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('campaign-variants'))->with('error', $msg);
        }

        $this->campaignVariantModel->delete($id);

        $msg = 'Varian kampanye berhasil dihapus.';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('campaign-variants'))->with('success', $msg);
    }

    /**
     * Hapus massal data Campaign Variants
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada varian kampanye yang dipilih untuk dihapus.',
            ]);
        }

        $this->campaignVariantModel->whereIn('id', $ids)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($ids) . ' relasi varian kampanye berhasil dihapus.',
        ]);
    }

    /**
     * Export data ke CSV
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'product_id'  => $this->request->getGet('product_id') ?: null,
        ];

        $data = $this->campaignVariantModel->getAllForExport($filters);

        $filename = 'campaign_variants_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID Relasi',
            'Nama Kampanye',
            'Status Kampanye',
            'Produk',
            'SKU Produk',
            'Nama Varian',
            'SKU Varian',
            'Harga Tambahan (IDR)',
            'Stok Unit',
            'Catatan',
            'Tanggal Didaftarkan'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['campaign_name'],
                $row['campaign_status'] ?? 'DRAFT',
                $row['product_name'] ?? '-',
                $row['product_sku'] ?? '-',
                $row['variant_name'] ?? '-',
                $row['variant_sku'] ?? '-',
                number_format((float)$row['variant_additional_price'], 0, ',', '.'),
                $row['variant_stock_qty'] ?? 0,
                $row['notes'] ?: '-',
                $row['created_at'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
