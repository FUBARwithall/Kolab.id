<?php

namespace App\Controllers;

use App\Models\ContentVariantModel;
use CodeIgniter\HTTP\ResponseInterface;

class Variants extends BaseController
{
    protected ContentVariantModel $variantModel;

    public function __construct()
    {
        $this->variantModel = new ContentVariantModel();
    }

    /**
     * Halaman Utama: Daftar Variant Konsep Konten (CI3 Parity)
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $total    = $this->variantModel->countAllResults();
        $active   = $this->variantModel->where('is_active', 1)->countAllResults();
        $inactive = $this->variantModel->where('is_active', 0)->countAllResults();

        $data = [
            'title'      => 'Variant Konsep Konten',
            'controller' => 'Variants',
            'stats'      => [
                'total'    => $total,
                'active'   => $active,
                'inactive' => $inactive,
            ],
        ];

        return view('variants/index', $data);
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
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'ASC'));

        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'code',
            3 => 'description',
            4 => 'is_active',
            5 => 'created_at',
            6 => 'id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'id';

        $filters = [
            'is_active' => $this->request->getPost('is_active') !== '' ? $this->request->getPost('is_active') : null,
        ];

        $result = $this->variantModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = ((int)$row['is_active'] === 1)
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-times-circle me-1"></i>Inactive</span>';

            $codeDisplay = '<span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-7">' . esc($row['code']) . '</span>';

            $descDisplay = !empty($row['description'])
                ? '<span class="text-secondary small text-truncate d-inline-block" style="max-width: 280px;" title="' . esc($row['description']) . '">' . esc($row['description']) . '</span>'
                : '<span class="text-muted fst-italic small">-</span>';

            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . site_url('variants/view/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-2 py-1 px-2" title="Edit Konsep" onclick="editVariant(' . $row['id'] . ')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Konsep" onclick="deleteVariant(' . $row['id'] . ')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'id'          => '<span class="font-monospace text-muted">#' . $row['id'] . '</span>',
                'name'        => '<div class="fw-bold text-dark">' . esc($row['name']) . '</div>',
                'code'        => $codeDisplay,
                'description' => $descDisplay,
                'is_active'   => $statusBadge,
                'created_at'  => '<span class="text-muted small">' . (!empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-') . '</span>',
                'action'      => $actions,
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
     * Detail View (CI3 Parity)
     */
    public function view(int $id)
    {
        $variant = $this->variantModel->find($id);
        if (!$variant) {
            return redirect()->to(base_url('variants'))->with('error', 'Variant konsep konten tidak ditemukan');
        }

        $data = [
            'title'      => 'Detail Variant Konsep Konten #' . $id,
            'controller' => 'Variants',
            'variant'    => $variant,
        ];

        return view('variants/view', $data);
    }

    /**
     * Get Single Variant JSON (for edit modal)
     */
    public function getOne(int $id)
    {
        $variant = $this->variantModel->find($id);
        if (!$variant) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Variant tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $variant,
        ]);
    }

    /**
     * Save Variant (Create or Update)
     */
    public function save(): ResponseInterface
    {
        $id = (int)$this->request->getPost('id');
        $name = trim((string)$this->request->getPost('name'));
        $code = strtoupper(trim((string)$this->request->getPost('code')));
        $description = trim((string)$this->request->getPost('description'));
        $isActive = (int)$this->request->getPost('is_active');

        // Validation
        if (empty($name)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Nama konsep wajib diisi.',
            ]);
        }

        if (empty($code)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Kode konsep wajib diisi (contoh: VIDEO_REVIEW).',
            ]);
        }

        // Check unique code
        $existing = $this->variantModel->where('code', $code)->first();
        if ($existing && (!$id || (int)$existing['id'] !== $id)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Kode konsep "' . $code . '" sudah digunakan.',
            ]);
        }

        $data = [
            'name'        => $name,
            'code'        => $code,
            'description' => $description ?: null,
            'is_active'   => in_array($isActive, [0, 1]) ? $isActive : 1,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $this->variantModel->update($id, $data);
            $msg = 'Variant konsep konten berhasil diperbarui!';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->variantModel->insert($data, true);
            $msg = 'Variant konsep konten baru berhasil ditambahkan!';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
            'id'      => $id,
        ]);
    }

    /**
     * Delete Variant (CI3 Parity)
     */
    public function delete(int $id)
    {
        $variant = $this->variantModel->find($id);
        if (!$variant) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Variant tidak ditemukan',
                ]);
            }
            return redirect()->to(base_url('variants'))->with('error', 'Variant tidak ditemukan');
        }

        $this->variantModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Variant konsep konten #' . $id . ' berhasil dihapus',
            ]);
        }

        return redirect()->to(base_url('variants'))->with('success', 'Variant konsep konten #' . $id . ' berhasil dihapus');
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Pilih setidaknya satu varian untuk dihapus',
            ]);
        }

        $cleanIds = array_map('intval', $ids);
        $this->variantModel->whereIn('id', $cleanIds)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($cleanIds) . ' varian konsep berhasil dihapus',
        ]);
    }

    /**
     * Export to CSV (CI3 Parity)
     */
    public function export()
    {
        $variants = $this->variantModel->orderBy('id', 'ASC')->findAll();

        $filename = 'variant_konsep_konten_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID',
            'Name Konsep',
            'Code Konsep',
            'Description',
            'Status',
            'Created At',
            'Updated At',
        ]);

        foreach ($variants as $v) {
            fputcsv($output, [
                $v['id'],
                $v['name'],
                $v['code'],
                $v['description'] ?? '',
                $v['is_active'] ? 'Active' : 'Inactive',
                $v['created_at'] ?? '',
                $v['updated_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }
}
