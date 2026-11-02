<?php

namespace App\Controllers;

use App\Models\ShipmentItemModel;
use App\Models\ShipmentModel;
use App\Models\ProductModel;
use App\Models\VariantModel;
use App\Models\StockHistoryModel;
use CodeIgniter\HTTP\ResponseInterface;

class ShipmentItems extends BaseController
{
    protected ShipmentItemModel $shipmentItemModel;
    protected ShipmentModel $shipmentModel;
    protected ProductModel $productModel;
    protected VariantModel $variantModel;
    protected StockHistoryModel $stockHistoryModel;

    public function __construct()
    {
        $this->shipmentItemModel = new ShipmentItemModel();
        $this->shipmentModel     = new ShipmentModel();
        $this->productModel      = new ProductModel();
        $this->variantModel      = new VariantModel();
        $this->stockHistoryModel = new StockHistoryModel();
    }

    /**
     * Halaman Utama Daftar Item Sampel Pengiriman (CI3 Parity)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $allShipments = $db->table('kol_shipments')
            ->select('kol_shipments.id, kol_shipments.tracking_number, kol_shipments.courier_name, kol_shipments.recipient_name, campaigns.title as campaign_title')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->orderBy('kol_shipments.id', 'DESC')
            ->get()
            ->getResultArray();

        $allProducts = $this->productModel->where('is_active', 1)->findAll();

        $data = [
            'title'        => 'Item Barang Sampel Pengiriman KOL',
            'controller'   => 'ShipmentItems',
            'allShipments' => $allShipments,
            'allProducts'  => $allProducts,
            'summary'      => $this->shipmentItemModel->getSummary(),
        ];

        return view('shipment_items/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint (CI3 & CI4 Parity)
     */
    public function getDatatable()
    {
        $draw   = (int) ($this->request->getPost('draw') ?? ($this->request->getGet('draw') ?? 1));
        $start  = (int) ($this->request->getPost('start') ?? ($this->request->getGet('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? ($this->request->getGet('length') ?? 10));
        $search = (string) ($this->request->getPost('search')['value'] ?? ($this->request->getGet('search')['value'] ?? ''));

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'kol_shipment_items.id',
            1 => 'kol_shipment_items.shipment_id',
            2 => 'products.name',
            3 => 'kol_shipment_items.qty',
            4 => 'products.price',
            5 => 'kol_shipments.shipping_status',
            6 => 'kol_shipment_items.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'kol_shipment_items.id';

        $filters = [
            'shipment_id' => $this->request->getPost('shipment_id') ?: $this->request->getGet('shipment_id'),
            'product_id'  => $this->request->getPost('product_id') ?: $this->request->getGet('product_id'),
        ];

        $result = $this->shipmentItemModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $tracking = esc($row['tracking_number'] ?: ($row['tracking_no'] ?: 'Draft #' . $row['shipment_id']));
            $courier  = esc($row['courier_name'] ?: ($row['courier'] ?: '-'));
            $recipient= esc($row['recipient_name'] ?: ($row['kol_name'] ?? '-'));
            $status   = strtoupper($row['shipping_status'] ?: ($row['status'] ?: 'PENDING'));

            $statusBadge = match ($status) {
                'DELIVERED' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-double me-1"></i>DELIVERED</span>',
                'SHIPPED'   => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-truck me-1"></i>SHIPPED</span>',
                'RETURNED'  => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-undo me-1"></i>RETURNED</span>',
                default     => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>' . $status . '</span>',
            };

            $price = (float)($row['product_price'] ?? 0);
            $qty   = (int)$row['qty'];
            $subtotal = $qty * $price;

            $formattedData[] = [
                'id'       => $row['id'],
                'shipment' => '
                    <div class="fw-bold font-monospace text-dark">' . $tracking . '</div>
                    <small class="text-muted">' . $courier . ' &bull; Penerima: <strong>' . $recipient . '</strong></small>
                ',
                'product'  => '
                    <div class="fw-semibold text-dark">' . esc($row['product_name']) . ($row['variant_name'] ? ' <span class="badge bg-info-subtle text-info border border-info-subtle">' . esc($row['variant_name']) . '</span>' : '') . '</div>
                    <small class="text-muted">SKU: <code class="text-dark">' . esc($row['product_sku'] ?? '-') . '</code> &bull; Kat: ' . esc($row['product_category'] ?? 'Umum') . '</small>
                ',
                'qty'      => '<span class="font-monospace fw-bold fs-6 text-dark">' . $qty . '</span> <small class="text-muted">pcs</small>',
                'valuation'=> '
                    <div class="font-monospace fw-semibold text-primary">Rp ' . number_format($subtotal, 0, ',', '.') . '</div>
                    <small class="text-muted">@ Rp ' . number_format($price, 0, ',', '.') . '</small>
                ',
                'status'   => $statusBadge,
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="' . base_url('shipment-items/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail"><i class="fas fa-eye"></i></a>
                        <a href="' . base_url('shipment-items/edit/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="' . base_url('shipment-items/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak PDF"><i class="fas fa-print"></i></a>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus" onclick="deleteShipmentItem(' . $row['id'] . ')"><i class="fas fa-trash"></i></button>
                    </div>
                ',
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
     * Get single detail JSON
     */
    public function getOne(int $id): ResponseInterface
    {
        $item = $this->shipmentItemModel->getDetail($id);
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data item pengiriman tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $item]);
    }

    /**
     * Standalone View Detail (CI3 Parity)
     */
    public function view(int $id)
    {
        $item = $this->shipmentItemModel->getDetail($id);
        if (!$item) {
            return redirect()->to(base_url('shipment-items'))->with('error', 'Item pengiriman #' . $id . ' tidak ditemukan.');
        }

        $data = [
            'title'              => 'Detail Item Pengiriman #' . $id . ' - ' . $item['product_name'],
            'controller'         => 'ShipmentItems',
            'item'               => $item,
            'kol_shipment_items' => (object)$item, // CI3 backward compatibility
        ];

        return view('shipment_items/view', $data);
    }

    /**
     * Standalone Add Form (CI3 Parity)
     */
    public function add()
    {
        $db = \Config\Database::connect();
        $allShipments = $db->table('kol_shipments')
            ->select('kol_shipments.id, kol_shipments.tracking_number, kol_shipments.courier_name, kol_shipments.recipient_name, campaigns.title as campaign_title')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->orderBy('kol_shipments.id', 'DESC')
            ->get()
            ->getResultArray();

        $allProducts = $this->productModel->where('is_active', 1)->findAll();
        foreach ($allProducts as &$prod) {
            $prod['variants'] = $this->variantModel->where('product_id', $prod['id'])->findAll();
        }
        unset($prod);

        $data = [
            'title'        => 'Tambah Item Barang Sampel Baru',
            'controller'   => 'ShipmentItems',
            'allShipments' => $allShipments,
            'allProducts'  => $allProducts,
        ];

        return view('shipment_items/add', $data);
    }

    /**
     * Standalone Add Save (CI3 Parity with stock deduction)
     */
    public function add_save()
    {
        $rules = [
            'shipment_id' => 'required|integer',
            'product_id'  => 'required|integer',
            'variant_id'  => 'permit_empty|integer',
            'qty'         => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode(', ', $this->validator->getErrors()),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $shipmentId = (int)$this->request->getPost('shipment_id');
        $productId  = (int)$this->request->getPost('product_id');
        $variantId  = !empty($this->request->getPost('variant_id')) ? (int)$this->request->getPost('variant_id') : null;
        $qty        = max(1, (int)$this->request->getPost('qty'));

        $payload = [
            'shipment_id' => $shipmentId,
            'product_id'  => $productId,
            'variant_id'  => $variantId,
            'qty'         => $qty,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $newId = $this->shipmentItemModel->insert($payload, true);

        // CI3 Parity: Auto-deduct product stock
        $product = $this->productModel->find($productId);
        if ($product) {
            $oldStock = (int)($product['stock_qty'] ?? $product['stock'] ?? 0);
            $newStock = max(0, $oldStock - $qty);
            $this->productModel->update($productId, [
                'stock'     => $newStock,
                'stock_qty' => $newStock,
            ]);

            $session = session();
            $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

            $this->stockHistoryModel->record(
                $productId,
                $variantId,
                'OUT',
                $qty,
                $newStock,
                'SHIPMENT_ITEM',
                $newId,
                'Pengalokasian sampel produk ke shipment #' . $shipmentId,
                $userId
            );
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'id'       => $newId,
                'message'  => 'Item sampel berhasil ditambahkan ke pengiriman.',
                'redirect' => base_url('shipment-items'),
            ]);
        }

        if ($this->request->getPost('save_type') === 'stay') {
            return redirect()->to(base_url('shipment-items/edit/' . $newId))->with('success', 'Item berhasil disimpan.');
        }

        return redirect()->to(base_url('shipment-items'))->with('success', 'Item sampel berhasil ditambahkan.');
    }

    /**
     * Standalone Edit Form (CI3 Parity)
     */
    public function edit(int $id)
    {
        $item = $this->shipmentItemModel->getDetail($id);
        if (!$item) {
            return redirect()->to(base_url('shipment-items'))->with('error', 'Item pengiriman #' . $id . ' tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $allShipments = $db->table('kol_shipments')
            ->select('kol_shipments.id, kol_shipments.tracking_number, kol_shipments.courier_name, kol_shipments.recipient_name, campaigns.title as campaign_title')
            ->join('campaign_kols', 'campaign_kols.id = kol_shipments.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->orderBy('kol_shipments.id', 'DESC')
            ->get()
            ->getResultArray();

        $allProducts = $this->productModel->where('is_active', 1)->findAll();
        foreach ($allProducts as &$prod) {
            $prod['variants'] = $this->variantModel->where('product_id', $prod['id'])->findAll();
        }
        unset($prod);

        $data = [
            'title'              => 'Edit Item Sampel #' . $id,
            'controller'         => 'ShipmentItems',
            'item'               => $item,
            'kol_shipment_items' => (object)$item, // CI3 backward compatibility
            'allShipments'       => $allShipments,
            'allProducts'        => $allProducts,
        ];

        return view('shipment_items/edit', $data);
    }

    /**
     * Standalone Edit Save (CI3 Parity with stock diff adjustment)
     */
    public function edit_save(int $id)
    {
        $oldItem = $this->shipmentItemModel->find($id);
        if (!$oldItem) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Item pengiriman tidak ditemukan.']);
            }
            return redirect()->to(base_url('shipment-items'))->with('error', 'Item pengiriman tidak ditemukan.');
        }

        $rules = [
            'shipment_id' => 'required|integer',
            'product_id'  => 'required|integer',
            'variant_id'  => 'permit_empty|integer',
            'qty'         => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode(', ', $this->validator->getErrors()),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $shipmentId = (int)$this->request->getPost('shipment_id');
        $productId  = (int)$this->request->getPost('product_id');
        $variantId  = !empty($this->request->getPost('variant_id')) ? (int)$this->request->getPost('variant_id') : null;
        $newQty     = max(1, (int)$this->request->getPost('qty'));
        $oldQty     = (int)$oldItem['qty'];
        $oldProdId  = (int)$oldItem['product_id'];

        $payload = [
            'shipment_id' => $shipmentId,
            'product_id'  => $productId,
            'variant_id'  => $variantId,
            'qty'         => $newQty,
        ];

        $this->shipmentItemModel->update($id, $payload);

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        // CI3 Stock Diff Adjustment Logic
        if ($oldProdId === $productId) {
            $diff = $newQty - $oldQty;
            if ($diff !== 0) {
                $p = $this->productModel->find($productId);
                $oldStock = (int)($p['stock_qty'] ?? $p['stock'] ?? 0);
                $newStock = max(0, $oldStock - $diff);
                $this->productModel->update($productId, [
                    'stock'     => $newStock,
                    'stock_qty' => $newStock,
                ]);

                $this->stockHistoryModel->record(
                    $productId,
                    $variantId,
                    ($diff > 0 ? 'OUT' : 'IN'),
                    abs($diff),
                    $newStock,
                    'SHIPMENT_ITEM_EDIT',
                    $id,
                    'Penyesuaian qty item shipment #' . $shipmentId,
                    $userId
                );
            }
        } else {
            // Product changed: refund old, deduct new
            $oldP = $this->productModel->find($oldProdId);
            if ($oldP) {
                $restoredStock = (int)($oldP['stock_qty'] ?? $oldP['stock'] ?? 0) + $oldQty;
                $this->productModel->update($oldProdId, ['stock' => $restoredStock, 'stock_qty' => $restoredStock]);
            }

            $newP = $this->productModel->find($productId);
            if ($newP) {
                $deductedStock = max(0, (int)($newP['stock_qty'] ?? $newP['stock'] ?? 0) - $newQty);
                $this->productModel->update($productId, ['stock' => $deductedStock, 'stock_qty' => $deductedStock]);
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'id'       => $id,
                'message'  => 'Item pengiriman berhasil diperbarui.',
                'redirect' => base_url('shipment-items'),
            ]);
        }

        if ($this->request->getPost('save_type') === 'stay') {
            return redirect()->to(base_url('shipment-items/edit/' . $id))->with('success', 'Perubahan berhasil disimpan.');
        }

        return redirect()->to(base_url('shipment-items'))->with('success', 'Item pengiriman berhasil diperbarui.');
    }

    /**
     * Delete Item (CI3 Parity with stock refund)
     */
    public function delete($id = null)
    {
        $arrId = $this->request->getGet('id') ?: ($this->request->getPost('id') ?: null);
        if (empty($id) && !empty($arrId) && !is_array($arrId)) {
            $id = (int)$arrId;
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        if (!empty($id)) {
            $item = $this->shipmentItemModel->find((int)$id);
            if (!$item) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Item pengiriman tidak ditemukan.']);
                }
                return redirect()->to(base_url('shipment-items'))->with('error', 'Item pengiriman tidak ditemukan.');
            }

            // Restore product stock
            $p = $this->productModel->find((int)$item['product_id']);
            if ($p) {
                $restoredStock = (int)($p['stock_qty'] ?? $p['stock'] ?? 0) + (int)$item['qty'];
                $this->productModel->update((int)$item['product_id'], [
                    'stock'     => $restoredStock,
                    'stock_qty' => $restoredStock,
                ]);

                $this->stockHistoryModel->record(
                    (int)$item['product_id'],
                    $item['variant_id'],
                    'IN',
                    (int)$item['qty'],
                    $restoredStock,
                    'SHIPMENT_ITEM_DELETE',
                    (int)$id,
                    'Pengembalian stok pembatalan item shipment #' . $item['shipment_id'],
                    $userId
                );
            }

            $this->shipmentItemModel->delete((int)$id);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Item pengiriman berhasil dihapus & stok dikembalikan.',
                    'summary' => $this->shipmentItemModel->getSummary(),
                ]);
            }
            return redirect()->to(base_url('shipment-items'))->with('success', 'Item pengiriman berhasil dihapus & stok dikembalikan.');
        }

        if (is_array($arrId) && count($arrId) > 0) {
            foreach ($arrId as $singleId) {
                $it = $this->shipmentItemModel->find((int)$singleId);
                if ($it) {
                    $p = $this->productModel->find((int)$it['product_id']);
                    if ($p) {
                        $restoredStock = (int)($p['stock_qty'] ?? $p['stock'] ?? 0) + (int)$it['qty'];
                        $this->productModel->update((int)$it['product_id'], [
                            'stock'     => $restoredStock,
                            'stock_qty' => $restoredStock,
                        ]);
                    }
                    $this->shipmentItemModel->delete((int)$singleId);
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => count($arrId) . ' item berhasil dihapus & stok dikembalikan.',
                    'summary' => $this->shipmentItemModel->getSummary(),
                ]);
            }
            return redirect()->to(base_url('shipment-items'))->with('success', count($arrId) . ' item berhasil dihapus & stok dikembalikan.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tidak ada item yang dipilih.']);
        }
        return redirect()->to(base_url('shipment-items'))->with('error', 'Tidak ada item yang dipilih.');
    }

    /**
     * Bulk Delete AJAX Endpoint
     */
    public function bulkDelete(): ResponseInterface
    {
        $ids = $this->request->getPost('ids') ?: $this->request->getPost('id');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih setidaknya satu item untuk dihapus.']);
        }

        $deletedCount = 0;
        foreach ($ids as $singleId) {
            $singleId = (int)$singleId;
            if (!$singleId) continue;

            $it = $this->shipmentItemModel->find($singleId);
            if ($it) {
                $p = $this->productModel->find((int)$it['product_id']);
                if ($p) {
                    $restoredStock = (int)($p['stock_qty'] ?? $p['stock'] ?? 0) + (int)$it['qty'];
                    $this->productModel->update((int)$it['product_id'], [
                        'stock'     => $restoredStock,
                        'stock_qty' => $restoredStock,
                    ]);
                }
                $this->shipmentItemModel->delete($singleId);
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success'       => true,
            'message'       => $deletedCount . ' item berhasil dihapus & stok dikembalikan.',
            'deleted_count' => $deletedCount,
            'summary'       => $this->shipmentItemModel->getSummary(),
        ]);
    }

    /**
     * Export to CSV (CI3 Parity)
     */
    public function export()
    {
        $filterShipment = $this->request->getGet('shipment_id') ? (int)$this->request->getGet('shipment_id') : null;
        $filterProduct  = $this->request->getGet('product_id') ? (int)$this->request->getGet('product_id') : null;

        $items = $this->shipmentItemModel->getAllForExport($filterShipment, $filterProduct);
        $filename = 'kol_shipment_items_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($output, [
            'ID Item',
            'ID Shipment',
            'No. Resi / Tracking',
            'Ekspedisi',
            'Kampanye',
            'Penerima (KOL)',
            'Kode SKU',
            'Nama Produk',
            'Varian',
            'Kategori',
            'Jumlah (Qty)',
            'Harga Satuan (Rp)',
            'Total Valuasi (Rp)',
            'Status Pengiriman',
            'Waktu Ditambahkan'
        ]);

        foreach ($items as $row) {
            $price = (float)($row['product_price'] ?? 0);
            $qty   = (int)$row['qty'];
            $sub   = $qty * $price;

            fputcsv($output, [
                $row['id'],
                $row['shipment_id'],
                $row['tracking_number'] ?: ($row['tracking_no'] ?: '-'),
                $row['courier_name'] ?: ($row['courier'] ?: '-'),
                $row['campaign_title'] ?? '-',
                $row['recipient_name'] ?: ($row['kol_name'] ?? '-'),
                $row['product_sku'] ?? '-',
                $row['product_name'] ?? '-',
                $row['variant_name'] ?: 'Standar',
                $row['product_category'] ?? '-',
                $qty,
                $price,
                $sub,
                $row['shipping_status'] ?: ($row['status'] ?: 'PENDING'),
                $row['created_at'] ?? '-'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Printable PDF Report of Shipment Items Catalog (CI3 Parity)
     */
    public function export_pdf()
    {
        $filterShipment = $this->request->getGet('shipment_id') ? (int)$this->request->getGet('shipment_id') : null;
        $filterProduct  = $this->request->getGet('product_id') ? (int)$this->request->getGet('product_id') : null;

        $items = $this->shipmentItemModel->getAllForExport($filterShipment, $filterProduct);

        return view('shipment_items/export_pdf', [
            'items'     => $items,
            'title'     => 'Rekapitulasi Item Sampel Produk dalam Pengiriman KOL',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => [
                'shipment_id' => $filterShipment,
                'product_id'  => $filterProduct,
            ],
        ]);
    }

    /**
     * Printable Single Item Specification Sheet (CI3 single_pdf Parity)
     */
    public function single_pdf($id = null)
    {
        $id = (int)$id;
        $item = $this->shipmentItemModel->getDetail($id);

        if (!$item) {
            return redirect()->to(base_url('shipment-items'))->with('error', 'Item pengiriman #' . $id . ' tidak ditemukan.');
        }

        return view('shipment_items/single_pdf', [
            'item'               => $item,
            'kol_shipment_items' => (object)$item, // CI3 backward compatibility
            'title'              => 'Lembar Detail Item Pengiriman #' . $item['id'] . ' - ' . $item['product_name'],
            'generated'          => date('d F Y, H:i:s'),
        ]);
    }
}
