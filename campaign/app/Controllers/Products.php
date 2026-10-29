<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\StockHistoryModel;
use App\Models\VariantModel;
use CodeIgniter\HTTP\ResponseInterface;

class Products extends BaseController
{
    protected ProductModel $productModel;
    protected StockHistoryModel $stockHistoryModel;
    protected VariantModel $variantModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->stockHistoryModel = new StockHistoryModel();
        $this->variantModel = new VariantModel();
    }

    /**
     * Tampilan utama daftar produk & ringkasan metrik
     */
    public function index()
    {
        $data = [
            'title'      => 'Katalog Produk & Sampel',
            'controller' => 'Products',
            'summary'    => $this->productModel->getSummary(),
        ];

        return view('products/index', $data);
    }

    /**
     * Server-side DataTables Endpoint
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

        $columns = ['id', 'image_url', 'sku', 'name', 'unit_price', 'stock_qty', 'is_active', 'created_at'];
        $orderCol = $columns[$orderColIdx] ?? 'id';

        $filterStatus = $request->getPost('filter_status');

        $totalRecords = $this->productModel->countAllResults(false);
        $filteredRecords = $this->productModel->countFiltered($searchValue, $filterStatus);
        $records = $this->productModel->getDatatables($start, $length, $searchValue, $orderCol, $orderDir, $filterStatus);

        $data = [];
        foreach ($records as $row) {
            $statusBadge = $row['is_active'] == 1
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Aktif</span>'
                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times-circle me-1"></i>Nonaktif</span>';

            $stockBadge = '';
            if ($row['stock_qty'] <= 0) {
                $stockBadge = '<span class="badge bg-danger px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Habis (' . $row['stock_qty'] . ')</span>';
            } elseif ($row['stock_qty'] <= 10) {
                $stockBadge = '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-exclamation-circle me-1"></i>Menipis (' . $row['stock_qty'] . ')</span>';
            } else {
                $stockBadge = '<span class="badge bg-primary px-2 py-1">' . $row['stock_qty'] . ' unit</span>';
            }

            $imgSrc = !empty($row['image_url']) 
                ? (str_starts_with($row['image_url'], 'http') ? $row['image_url'] : base_url('uploads/campaign/' . $row['image_url']))
                : base_url('asset/img/no-image.png');

            $thumb = '<img src="' . esc($imgSrc) . '" alt="img" class="rounded border shadow-sm" style="width: 44px; height: 44px; object-fit: cover;" onerror="this.src=\'data:image/svg+xml;utf8,<svg xmlns=\\\'http://www.w3.org/2000/svg\\\' width=\\\'44\\\' height=\\\'44\\\' fill=\\\'%23dee2e6\\\'><rect width=\\\'100%\\\' height=\\\'100%\\\'/></svg>\';">';

            $actionBtns = '
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-2 py-1 px-2" title="Kelola Varian" onclick="openVariantsModal(' . $row['id'] . ', \'' . esc(addslashes($row['name'])) . '\')">
                        <i class="fas fa-tags"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Sesuaikan Stok" onclick="openStockModal(' . $row['id'] . ', \'' . esc(addslashes($row['name'])) . '\', ' . $row['stock_qty'] . ')">
                        <i class="fas fa-boxes"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Produk" onclick="editProduct(' . $row['id'] . ')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Produk" onclick="deleteProduct(' . $row['id'] . ')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'id'          => $row['id'],
                'thumb'       => $thumb,
                'sku'         => '<span class="fw-semibold text-secondary">' . esc($row['sku']) . '</span>',
                'name'        => '<div class="fw-bold text-dark">' . esc($row['name']) . '</div><small class="text-muted text-truncate d-inline-block" style="max-width: 280px;">' . esc($row['description'] ?? '-') . '</small>',
                'unit_price'  => '<span class="fw-semibold text-success">Rp ' . number_format((float)$row['unit_price'], 0, ',', '.') . '</span>',
                'stock'       => $stockBadge,
                'status'      => $statusBadge,
                'action'      => $actionBtns,
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
     * Ambil 1 data produk untuk modal edit (JSON)
     */
    public function getOne(int $id): ResponseInterface
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $product,
        ]);
    }

    /**
     * Simpan data produk (Tambah atau Update)
     */
    public function save(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $isUpdate = !empty($id);

        $rules = [
            'name'       => 'required|min_length[3]|max_length[150]',
            'sku'        => 'required|min_length[2]|max_length[100]|is_unique[products.sku,id,' . ($id ?: 0) . ']',
            'stock_qty'  => 'required|integer',
            'unit_price' => 'required|numeric',
            'is_active'  => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success'  => false,
                'messages' => $this->validator->getErrors(),
            ]);
        }

        $payload = [
            'name'        => trim($this->request->getPost('name')),
            'sku'         => strtoupper(trim($this->request->getPost('sku'))),
            'description' => trim($this->request->getPost('description') ?? ''),
            'stock_qty'   => (int)$this->request->getPost('stock_qty'),
            'unit_price'  => (float)$this->request->getPost('unit_price'),
            'is_active'   => (int)$this->request->getPost('is_active'),
        ];

        // Handle upload foto produk jika ada
        $imageFile = $this->request->getFile('image_file');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $uploadDir = FCPATH . '../../shared/uploads/campaign';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $newName = $imageFile->getRandomName();
            $imageFile->move($uploadDir, $newName);
            $payload['image_url'] = $newName;
        }

        if ($isUpdate) {
            $oldProduct = $this->productModel->find($id);
            $this->productModel->update($id, $payload);

            // Jika ada perubahan stok langsung via form edit, catat riwayatnya
            if ($oldProduct && $oldProduct['stock_qty'] != $payload['stock_qty']) {
                $diff = $payload['stock_qty'] - $oldProduct['stock_qty'];
                $type = $diff > 0 ? 'IN' : 'OUT';
                $this->stockHistoryModel->record(
                    $id,
                    null,
                    $type,
                    abs($diff),
                    $payload['stock_qty'],
                    'PRODUCT_EDIT',
                    $id,
                    'Penyesuaian stok saat update data produk'
                );
            }

            $message = 'Produk berhasil diperbarui.';
        } else {
            $newId = $this->productModel->insert($payload);

            // Catat stok awal jika > 0
            if ($payload['stock_qty'] > 0) {
                $this->stockHistoryModel->record(
                    $newId,
                    null,
                    'IN',
                    $payload['stock_qty'],
                    $payload['stock_qty'],
                    'INITIAL_STOCK',
                    $newId,
                    'Stok Awal Produk Baru'
                );
            }

            $message = 'Produk baru berhasil ditambahkan.';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'summary' => $this->productModel->getSummary(),
        ]);
    }

    /**
     * Penyesuaian stok mandiri (In / Out / Adjustment)
     */
    public function adjustStock(): ResponseInterface
    {
        $productId = (int)$this->request->getPost('product_id');
        $type      = strtoupper(trim($this->request->getPost('type') ?? '')); // IN, OUT, ADJUSTMENT
        $qty       = (int)$this->request->getPost('qty');
        $notes     = trim($this->request->getPost('notes') ?? '');

        if (!$productId || $qty <= 0 || !in_array($type, ['IN', 'OUT', 'ADJUSTMENT'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter penyesuaian stok tidak valid.',
            ]);
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ]);
        }

        $currentStock = (int)$product['stock_qty'];
        if ($type === 'IN') {
            $newStock = $currentStock + $qty;
        } elseif ($type === 'OUT') {
            if ($qty > $currentStock) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Jumlah keluar (' . $qty . ') melebihi stok yang tersedia (' . $currentStock . ').',
                ]);
            }
            $newStock = $currentStock - $qty;
        } else { // ADJUSTMENT (Set nilai stok aktual)
            $newStock = $qty;
        }

        // Update stok produk
        $this->productModel->update($productId, ['stock_qty' => $newStock]);

        // Catat riwayat mutasi
        $this->stockHistoryModel->record(
            $productId,
            null,
            $type,
            $qty,
            $newStock,
            'MANUAL_ADJUSTMENT',
            $productId,
            $notes ?: 'Penyesuaian stok manual'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Stok produk berhasil disesuaikan menjadi ' . $newStock . ' unit.',
            'summary' => $this->productModel->getSummary(),
        ]);
    }

    /**
     * Hapus produk
     */
    public function delete(int $id): ResponseInterface
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ]);
        }

        // Cek apakah produk sudah dipakai di kampanye
        $db = \Config\Database::connect();
        $isUsedInCampaign = $db->table('campaign_products')->where('product_id', $id)->countAllResults();

        if ($isUsedInCampaign > 0) {
            // Jika ada riwayat, ubah status jadi nonaktif agar integritas data terjaga
            $this->productModel->update($id, ['is_active' => 0]);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Produk sudah terkait dengan kampanye aktif. Status produk diubah menjadi Nonaktif (Arsip).',
                'summary' => $this->productModel->getSummary(),
            ]);
        }

        $this->productModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
            'summary' => $this->productModel->getSummary(),
        ]);
    }

    /**
     * Ambil daftar varian untuk sebuah produk (JSON)
     */
    public function getVariants(int $productId): ResponseInterface
    {
        $product = $this->productModel->find($productId);
        if (!$product) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ]);
        }

        $variants = $this->variantModel->where('product_id', $productId)->orderBy('id', 'asc')->findAll();

        return $this->response->setJSON([
            'success'  => true,
            'product'  => $product,
            'variants' => $variants,
        ]);
    }

    /**
     * Simpan varian produk (Tambah atau Update)
     */
    public function saveVariant(): ResponseInterface
    {
        $id = $this->request->getPost('id');
        $isUpdate = !empty($id);
        $productId = (int)$this->request->getPost('product_id');

        $rules = [
            'product_id'       => 'required|is_not_unique[products.id]',
            'name'             => 'required|min_length[2]|max_length[100]',
            'sku'              => 'required|min_length[2]|max_length[100]|is_unique[variants.sku,id,' . ($id ?: 0) . ']',
            'stock_qty'        => 'required|integer',
            'additional_price' => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success'  => false,
                'messages' => $this->validator->getErrors(),
            ]);
        }

        $payload = [
            'product_id'       => $productId,
            'name'             => trim($this->request->getPost('name')),
            'sku'              => strtoupper(trim($this->request->getPost('sku'))),
            'stock_qty'        => (int)$this->request->getPost('stock_qty'),
            'additional_price' => (float)($this->request->getPost('additional_price') ?? 0),
        ];

        if ($isUpdate) {
            $this->variantModel->update($id, $payload);
            $message = 'Varian berhasil diperbarui.';
        } else {
            $newVarId = $this->variantModel->insert($payload);
            if ($payload['stock_qty'] > 0) {
                $this->stockHistoryModel->record(
                    $productId,
                    $newVarId,
                    'IN',
                    $payload['stock_qty'],
                    $payload['stock_qty'],
                    'INITIAL_STOCK',
                    $newVarId,
                    'Stok Awal Varian Baru: ' . $payload['name']
                );
            }
            $message = 'Varian baru berhasil ditambahkan.';
        }

        $variants = $this->variantModel->where('product_id', $productId)->orderBy('id', 'asc')->findAll();

        return $this->response->setJSON([
            'success'  => true,
            'message'  => $message,
            'variants' => $variants,
        ]);
    }

    /**
     * Hapus varian produk
     */
    public function deleteVariant(int $id): ResponseInterface
    {
        $variant = $this->variantModel->find($id);
        if (!$variant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Varian tidak ditemukan.',
            ]);
        }

        $productId = $variant['product_id'];

        // Cek jika varian sudah terasosiasi di campaign_variants atau kol_shipment_items
        $db = \Config\Database::connect();
        $usedInShipment = $db->table('kol_shipment_items')->where('variant_id', $id)->countAllResults();
        $usedInCampaign = $db->table('campaign_variants')->where('variant_id', $id)->countAllResults();

        if ($usedInShipment > 0 || $usedInCampaign > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Varian tidak dapat dihapus karena sudah digunakan dalam kampanye atau pengiriman.',
            ]);
        }

        $this->variantModel->delete($id);

        $variants = $this->variantModel->where('product_id', $productId)->orderBy('id', 'asc')->findAll();

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Varian berhasil dihapus.',
            'variants' => $variants,
        ]);
    }
}
