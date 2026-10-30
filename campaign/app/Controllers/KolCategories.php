<?php

namespace App\Controllers;

use App\Models\KolCategoryModel;
use CodeIgniter\HTTP\ResponseInterface;

class KolCategories extends BaseController
{
    protected KolCategoryModel $kolCategoryModel;

    public function __construct()
    {
        $this->kolCategoryModel = new KolCategoryModel();
    }

    /**
     * Halaman Utama: Daftar Tagging & Kategori KOL (CI3 Parity)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $totalAssignments = $this->kolCategoryModel->countAllResults();
        $totalKolsTagged  = $db->table('kol_categories')->select('COUNT(DISTINCT kol_id) as cnt')->get()->getRowArray()['cnt'] ?? 0;
        $totalCategories  = $db->table('categories')->countAllResults();

        // Get dropdown list of KOLs & Categories
        $kols = $db->table('kols')
            ->select('kols.id, kols.name, kols.whatsapp, u.email')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->orderBy('kols.name', 'ASC')
            ->get()
            ->getResultArray();
        $categories = $db->table('categories')->select('id, name, slug')->orderBy('name', 'ASC')->get()->getResultArray();

        $data = [
            'title'      => 'Kategori & Tagging KOL',
            'controller' => 'KolCategories',
            'stats'      => [
                'total_assignments' => (int)$totalAssignments,
                'total_kols_tagged'  => (int)$totalKolsTagged,
                'total_categories'   => (int)$totalCategories,
            ],
            'kols'       => $kols,
            'categories' => $categories,
        ];

        return view('kol_categories/index', $data);
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
            0 => 'kol_categories.id',
            1 => 'kols.name',
            2 => 'categories.name',
            3 => 'kol_categories.created_at',
            4 => 'kol_categories.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'kol_categories.id';

        $filters = [
            'category_id' => $this->request->getPost('category_id') ?: null,
            'kol_id'      => $this->request->getPost('kol_id') ?: null,
        ];

        $result = $this->kolCategoryModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $categoryColors = [
            'beauty-fashion' => ['bg' => '#fce4ec', 'color' => '#c2185b', 'icon' => 'fa-tshirt'],
            'tech'           => ['bg' => '#e3f2fd', 'color' => '#1976d2', 'icon' => 'fa-laptop-code'],
            'gaming'         => ['bg' => '#ede7f6', 'color' => '#512da8', 'icon' => 'fa-gamepad'],
            'automotive'     => ['bg' => '#fff3e0', 'color' => '#f57c00', 'icon' => 'fa-car'],
            'horror'         => ['bg' => '#eceff1', 'color' => '#263238', 'icon' => 'fa-ghost'],
            'culinary'       => ['bg' => '#ffebee', 'color' => '#d32f2f', 'icon' => 'fa-utensils'],
            'lifestyle'      => ['bg' => '#e8f5e9', 'color' => '#388e3c', 'icon' => 'fa-seedling'],
        ];

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $slug = strtolower($row['category_slug'] ?? '');
            $theme = $categoryColors[$slug] ?? ['bg' => '#e8f0fe', 'color' => '#1a73e8', 'icon' => 'fa-tag'];

            $categoryBadge = '
                <span class="badge border px-2 py-1 fw-semibold" style="background-color: ' . $theme['bg'] . '; color: ' . $theme['color'] . '; border-color: ' . $theme['bg'] . ' !important;">
                    <i class="fas ' . $theme['icon'] . ' me-1"></i>' . esc($row['category_name']) . '
                </span>
            ';

            $kolDisplay = '
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 13px;">
                        ' . strtoupper(substr($row['kol_name'] ?? 'K', 0, 1)) . '
                    </div>
                    <div>
                        <a href="' . site_url('kols/view/' . $row['kol_id']) . '" class="text-decoration-none fw-semibold text-dark hover-primary d-block">
                            ' . esc($row['kol_name']) . '
                        </a>
                        <small class="text-muted">' . esc($row['kol_email'] ?? '-') . '</small>
                    </div>
                </div>
            ';

            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <a href="' . site_url('kol-categories/view/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Lihat Rincian">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-2 py-1 px-2" title="Edit Kategori" onclick="editKolCategory(' . $row['id'] . ')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Kategori" onclick="deleteKolCategory(' . $row['id'] . ')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'id'         => '<span class="font-monospace text-muted">#' . $row['id'] . '</span>',
                'kol'        => $kolDisplay,
                'category'   => $categoryBadge,
                'created_at' => '<span class="text-muted small">' . (!empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-') . '</span>',
                'action'     => $actions,
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
        $detail = $this->kolCategoryModel->getDetail($id);
        if (!$detail) {
            return redirect()->to(base_url('kol-categories'))->with('error', 'Data kategori KOL tidak ditemukan');
        }

        $data = [
            'title'      => 'Detail Kategori KOL #' . $id,
            'controller' => 'KolCategories',
            'detail'     => $detail,
        ];

        return view('kol_categories/view', $data);
    }

    /**
     * Get Single Record JSON for Modal Edit
     */
    public function getOne(int $id)
    {
        $record = $this->kolCategoryModel->find($id);
        if (!$record) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $record,
        ]);
    }

    /**
     * Save KOL Category Assignment (Create or Update)
     */
    public function save(): ResponseInterface
    {
        $id         = (int)$this->request->getPost('id');
        $kolId      = (int)$this->request->getPost('kol_id');
        $categoryId = (int)$this->request->getPost('category_id');

        if (!$kolId || !$categoryId) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Harap pilih KOL dan Kategori yang valid.',
            ]);
        }

        // Check duplicate
        if ($this->kolCategoryModel->checkExists($kolId, $categoryId, $id > 0 ? $id : null)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'KOL ini sudah ditugaskan ke kategori tersebut sebelumnya.',
            ]);
        }

        $data = [
            'kol_id'      => $kolId,
            'category_id' => $categoryId,
        ];

        if ($id > 0) {
            $this->kolCategoryModel->update($id, $data);
            $msg = 'Kategori KOL berhasil diperbarui!';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $this->kolCategoryModel->insert($data, true);
            $msg = 'KOL berhasil ditambahkan ke kategori!';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
            'id'      => $id,
        ]);
    }

    /**
     * Delete Assignment (CI3 Parity)
     */
    public function delete(int $id)
    {
        $record = $this->kolCategoryModel->find($id);
        if (!$record) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ]);
            }
            return redirect()->to(base_url('kol-categories'))->with('error', 'Data tidak ditemukan');
        }

        $this->kolCategoryModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kategori KOL #' . $id . ' berhasil dihapus',
            ]);
        }

        return redirect()->to(base_url('kol-categories'))->with('success', 'Kategori KOL #' . $id . ' berhasil dihapus');
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
                'message' => 'Pilih setidaknya satu data untuk dihapus',
            ]);
        }

        $cleanIds = array_map('intval', $ids);
        $this->kolCategoryModel->whereIn('id', $cleanIds)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($cleanIds) . ' kategori KOL berhasil dihapus',
        ]);
    }

    /**
     * Export to CSV (CI3 Parity)
     */
    public function export()
    {
        $categoryId = $this->request->getGet('category_id') ?: null;
        $items = $this->kolCategoryModel->getAllForExport(['category_id' => $categoryId]);

        $filename = 'kol_categories_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID',
            'KOL ID',
            'Nama KOL',
            'Email KOL',
            'Category ID',
            'Nama Kategori',
            'Created At',
        ]);

        foreach ($items as $item) {
            fputcsv($output, [
                $item['id'],
                $item['kol_id'],
                $item['kol_name'] ?? '-',
                $item['kol_email'] ?? '-',
                $item['category_id'],
                $item['category_name'] ?? '-',
                $item['created_at'] ?? '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
