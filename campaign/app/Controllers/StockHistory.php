<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StockHistoryModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class StockHistory extends BaseController
{
    protected StockHistoryModel $stockHistoryModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->stockHistoryModel = new StockHistoryModel();
        $this->productModel      = new ProductModel();
    }

    /**
     * Halaman Utama Riwayat Mutasi Stok (CI3 & CI4 Parity)
     */
    public function index($offset = 0)
    {
        $productId = $this->request->getGet('product_id') ? (int)$this->request->getGet('product_id') : null;
        $type      = $this->request->getGet('type') ? strtoupper(trim((string)$this->request->getGet('type'))) : null;
        $dateFrom  = $this->request->getGet('date_from') ?: null;
        $dateTo    = $this->request->getGet('date_to') ?: null;
        $q         = $this->request->getGet('q') ?: null;

        $selectedProduct = $productId ? $this->productModel->find($productId) : null;
        $allProducts     = $this->productModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $summary         = $this->stockHistoryModel->getSummary($productId);

        $data = [
            'title'           => 'Riwayat Mutasi & Audit Stok Produk',
            'controller'      => 'StockHistory',
            'allProducts'     => $allProducts,
            'selectedProduct' => $selectedProduct,
            'summary'         => $summary,
            'filters'         => [
                'product_id' => $productId,
                'type'       => $type,
                'date_from'  => $dateFrom,
                'date_to'    => $dateTo,
                'q'          => $q,
            ],
            // Backward compatibility for CI3 views
            'stock_historys'       => $this->stockHistoryModel->getHistoryWithDetails(100, $productId),
            'stock_history_counts' => $summary['total_mutations'] ?? 0,
        ];

        return view('stock_history/index', $data);
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
            0 => 'stock_history.id',
            1 => 'stock_history.created_at',
            2 => 'products.name',
            3 => 'stock_history.type',
            4 => 'stock_history.qty',
            5 => 'stock_history.stock_before',
            6 => 'stock_history.balance_after',
            7 => 'stock_history.reference_type',
            8 => 'stock_history.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'stock_history.id';

        $filters = [
            'product_id'     => $this->request->getPost('product_id') ?: $this->request->getGet('product_id'),
            'type'           => $this->request->getPost('type') ?: $this->request->getGet('type'),
            'reference_type' => $this->request->getPost('reference_type') ?: $this->request->getGet('reference_type'),
            'date_from'      => $this->request->getPost('date_from') ?: $this->request->getGet('date_from'),
            'date_to'        => $this->request->getPost('date_to') ?: $this->request->getGet('date_to'),
        ];

        $result = $this->stockHistoryModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $type = strtoupper($row['type'] ?: ($row['change_type'] ?? ''));

            $typeBadge = match ($type) {
                'IN'         => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-arrow-down me-1"></i>MASUK (IN)</span>',
                'OUT'        => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-arrow-up me-1"></i>KELUAR (OUT)</span>',
                'ADJUSTMENT' => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-sliders-h me-1"></i>KOREKSI</span>',
                default      => '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">' . esc($type) . '</span>',
            };

            $qtySign = match ($type) {
                'IN'    => '+',
                'OUT'   => '-',
                default => '',
            };
            $qtyClass = match ($type) {
                'IN'    => 'text-success',
                'OUT'   => 'text-danger',
                default => 'text-primary',
            };

            $refBadge = '';
            if (!empty($row['reference_type'])) {
                $refBadge = '<span class="badge bg-light text-dark border me-1">' . esc($row['reference_type']) . '</span>';
            }
            if (!empty($row['shipment_tracking'])) {
                $refBadge .= '<span class="badge bg-primary-subtle text-primary font-monospace ms-1" title="Resi: ' . esc($row['shipment_tracking']) . '"><i class="fas fa-truck me-1"></i>' . esc($row['shipment_tracking']) . '</span>';
            }

            $formattedData[] = [
                'id'           => $row['id'],
                'created_at'   => '
                    <div class="fw-semibold text-dark font-monospace">' . date('d M Y', strtotime($row['created_at'])) . '</div>
                    <small class="text-muted">' . date('H:i:s', strtotime($row['created_at'])) . ' WIB</small>
                ',
                'product'      => '
                    <div class="fw-bold text-dark">' . esc($row['product_name'] ?? 'Produk #' . $row['product_id']) . ($row['variant_name'] ? ' <span class="badge bg-info-subtle text-info border border-info-subtle">' . esc($row['variant_name']) . '</span>' : '') . '</div>
                    <small class="text-muted">SKU: <code class="text-dark">' . esc($row['product_sku'] ?? '-') . '</code> &bull; Kat: ' . esc($row['product_category'] ?? 'Umum') . '</small>
                ',
                'type'         => $typeBadge,
                'qty'          => '<span class="font-monospace fw-bold fs-6 ' . $qtyClass . '">' . $qtySign . number_format((int)$row['qty'], 0, ',', '.') . '</span> <small class="text-muted">unit</small>',
                'stock_before' => '<span class="font-monospace fw-semibold text-secondary">' . number_format((int)$row['stock_before'], 0, ',', '.') . '</span>',
                'stock_after'  => '<span class="font-monospace fw-bold text-dark fs-6">' . number_format((int)$row['balance_after'], 0, ',', '.') . '</span>',
                'reference'    => '
                    <div>' . $refBadge . '</div>
                    <small class="text-secondary">' . esc($row['notes'] ?: '-') . '</small>
                ',
                'user'         => '<span class="badge bg-secondary-subtle text-secondary px-2 py-1"><i class="fas fa-user me-1"></i>' . esc($row['user_name'] ?? 'Admin') . '</span>',
                'action'       => '
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="' . base_url('stock-history/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail"><i class="fas fa-eye"></i></a>
                        <a href="' . base_url('stock-history/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak Bukti PDF"><i class="fas fa-print"></i></a>
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
     * Get single detail JSON endpoint
     */
    public function getOne(int $id): ResponseInterface
    {
        $detail = $this->stockHistoryModel->getDetail($id);
        if (!$detail) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data riwayat mutasi stok tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $detail]);
    }

    /**
     * Standalone View Detail (CI3 Parity)
     */
    public function view(int $id)
    {
        $detail = $this->stockHistoryModel->getDetail($id);
        if (!$detail) {
            return redirect()->to(base_url('stock-history'))->with('error', 'Riwayat mutasi #' . $id . ' tidak ditemukan.');
        }

        $data = [
            'title'         => 'Detail Mutasi Stok #' . $id . ' - ' . ($detail['product_name'] ?? 'Produk'),
            'controller'    => 'StockHistory',
            'item'          => $detail,
            'stock_history' => (object)$detail, // CI3 backward compatibility
        ];

        return view('stock_history/view', $data);
    }

    /**
     * Export Mutations to CSV (CI3 Parity)
     */
    public function export()
    {
        $filters = [
            'product_id' => $this->request->getGet('product_id') ? (int)$this->request->getGet('product_id') : null,
            'type'       => $this->request->getGet('type') ? strtoupper(trim((string)$this->request->getGet('type'))) : null,
            'date_from'  => $this->request->getGet('date_from') ?: null,
            'date_to'    => $this->request->getGet('date_to') ?: null,
        ];

        $items = $this->stockHistoryModel->getAllForExport($filters);
        $filename = 'stock_history_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($output, [
            'ID Mutasi',
            'Waktu Transaksi',
            'Kode SKU',
            'Nama Produk',
            'Varian',
            'Kategori',
            'Tipe Mutasi',
            'Kuantitas Mutasi',
            'Stok Sebelum',
            'Stok Sesudah',
            'Tipe Referensi',
            'No. Resi Pengiriman',
            'Ekspedisi',
            'Catatan / Alasan',
            'Operator'
        ]);

        foreach ($items as $row) {
            $type = strtoupper($row['type'] ?: ($row['change_type'] ?? ''));
            $sign = $type === 'IN' ? '+' : ($type === 'OUT' ? '-' : '');

            fputcsv($output, [
                $row['id'],
                $row['created_at'],
                $row['product_sku'] ?? '-',
                $row['product_name'] ?? '-',
                $row['variant_name'] ?: 'Standar',
                $row['product_category'] ?? '-',
                $type,
                $sign . $row['qty'],
                $row['stock_before'],
                $row['balance_after'],
                $row['reference_type'] ?? '-',
                $row['shipment_tracking'] ?? '-',
                $row['shipment_courier'] ?? '-',
                $row['notes'] ?? '-',
                $row['user_name'] ?? 'Admin'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Printable PDF Report of Stock Movements (CI3 Parity)
     */
    public function export_pdf()
    {
        $filters = [
            'product_id' => $this->request->getGet('product_id') ? (int)$this->request->getGet('product_id') : null,
            'type'       => $this->request->getGet('type') ? strtoupper(trim((string)$this->request->getGet('type'))) : null,
            'date_from'  => $this->request->getGet('date_from') ?: null,
            'date_to'    => $this->request->getGet('date_to') ?: null,
        ];

        $items   = $this->stockHistoryModel->getAllForExport($filters);
        $summary = $this->stockHistoryModel->getSummary($filters['product_id']);

        return view('stock_history/export_pdf', [
            'items'     => $items,
            'summary'   => $summary,
            'title'     => 'Rekapitulasi Audit Mutasi & Logistik Stok Sampel',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => $filters,
        ]);
    }

    /**
     * Printable Single Movement Voucher (CI3 single_pdf Parity)
     */
    public function single_pdf($id = null)
    {
        $id = (int)$id;
        $detail = $this->stockHistoryModel->getDetail($id);

        if (!$detail) {
            return redirect()->to(base_url('stock-history'))->with('error', 'Mutasi stok #' . $id . ' tidak ditemukan.');
        }

        return view('stock_history/single_pdf', [
            'item'          => $detail,
            'stock_history' => (object)$detail, // CI3 backward compatibility
            'title'         => 'Bukti Transaksi Mutasi Stok #' . $detail['id'],
            'generated'     => date('d F Y, H:i:s'),
        ]);
    }
}
