<?php

namespace App\Controllers;

use App\Models\KolRateCardModel;
use CodeIgniter\HTTP\ResponseInterface;

class KolRateCards extends BaseController
{
    protected KolRateCardModel $kolRateCardModel;

    public function __construct()
    {
        $this->kolRateCardModel = new KolRateCardModel();
    }

    /**
     * Halaman Utama: Daftar Rate Card KOL (Full Parity with CI3 modules/kol_rate_cards)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $totalRateCards  = $this->kolRateCardModel->countAllResults();
        $activeRateCards = $db->table('kol_rate_cards')->where('is_active', 1)->countAllResults();
        $negotiableCount = $db->table('kol_rate_cards')->where('negotiable', 1)->countAllResults();
        
        $avgRow = $db->table('kol_rate_cards')
            ->select('AVG(COALESCE(rate, rate_price)) as avg_rate')
            ->where('is_active', 1)
            ->get()
            ->getRowArray();
        $avgRate = (float)($avgRow['avg_rate'] ?? 0);

        // Get dropdown list of KOLs & Platforms
        $kols = $db->table('kols')
            ->select('kols.id, kols.name, kols.whatsapp, u.email')
            ->join('users u', 'u.id = kols.user_id', 'left')
            ->orderBy('kols.name', 'ASC')
            ->get()
            ->getResultArray();

        $platforms = $db->table('platforms')
            ->select('id, name, code, icon, base_url')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $kolPlatforms = $db->table('kol_platforms kp')
            ->select('kp.id, kp.kol_id, kp.platform_id, kp.username, kp.followers, k.name as kol_name, p.name as platform_name, p.icon as platform_icon')
            ->join('kols k', 'k.id = kp.kol_id', 'left')
            ->join('platforms p', 'p.id = kp.platform_id', 'left')
            ->where('kp.is_active', 1)
            ->orderBy('k.name', 'ASC')
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();

        $contentTypes = KolRateCardModel::getCanonicalTypes();

        $data = [
            'title'           => 'Master Rate Card KOL',
            'controller'      => 'KolRateCards',
            'stats'           => [
                'total_rate_cards'  => (int)$totalRateCards,
                'active_rate_cards' => (int)$activeRateCards,
                'avg_rate'          => $avgRate,
                'negotiable_count'  => (int)$negotiableCount,
            ],
            'kols'            => $kols,
            'platforms'       => $platforms,
            'kolPlatforms'    => $kolPlatforms,
            'contentTypes'    => $contentTypes,
        ];

        return view('kol_rate_cards/index', $data);
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
            0 => 'kol_rate_cards.id',
            1 => 'kol_rate_cards.id',
            2 => 'kols.name',
            3 => 'platforms.name',
            4 => 'kp.username',
            5 => 'kol_rate_cards.content_type',
            6 => 'final_rate',
            7 => 'kol_rate_cards.negotiable',
            8 => 'kol_rate_cards.is_active',
            9 => 'kol_rate_cards.created_at',
            10 => 'kol_rate_cards.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'kol_rate_cards.id';

        $filters = [
            'platform_id'  => $this->request->getPost('platform_id') ?: null,
            'kol_id'       => $this->request->getPost('kol_id') ?: null,
            'content_type' => $this->request->getPost('content_type') ?: null,
            'is_active'    => $this->request->getPost('is_active'),
            'negotiable'   => $this->request->getPost('negotiable'),
        ];

        $result = $this->kolRateCardModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $platformColors = [
            'TIKTOK'    => ['badge' => 'bg-dark', 'icon' => 'fab fa-tiktok'],
            'INSTAGRAM' => ['badge' => 'bg-danger', 'icon' => 'fab fa-instagram'],
            'YOUTUBE'   => ['badge' => 'bg-danger', 'icon' => 'fab fa-youtube'],
            'TWITTER'   => ['badge' => 'bg-info text-white', 'icon' => 'fab fa-x-twitter'],
            'FACEBOOK'  => ['badge' => 'bg-primary', 'icon' => 'fab fa-facebook'],
        ];

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $code = strtoupper($row['platform_code'] ?? '');
            $theme = $platformColors[$code] ?? ['badge' => 'bg-secondary', 'icon' => 'fas fa-share-alt'];

            // Platform badge
            $platformBadge = '
                <span class="badge ' . $theme['badge'] . ' px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="' . esc($row['platform_icon'] ?: $theme['icon']) . '"></i> ' . esc($row['platform_name'] ?: 'Unknown') . '
                </span>';

            // KOL Link
            $kolLink = '<a href="' . base_url('kols/view/' . $row['kol_id']) . '" class="fw-bold text-dark text-decoration-none hover-primary">
                ' . esc($row['kol_name'] ?? 'N/A') . '
            </a>';

            // Account Handle
            $username = $row['platform_username'] ? '@' . ltrim($row['platform_username'], '@') : '-';
            $profileUrl = $row['platform_profile_url'];
            if (!empty($profileUrl)) {
                $usernameHtml = '<a href="' . esc($profileUrl) . '" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none fw-semibold small">' . esc($username) . ' <i class="fas fa-external-link-alt fa-xs"></i></a>';
            } else {
                $usernameHtml = '<span class="text-secondary small fw-semibold">' . esc($username) . '</span>';
            }

            // Content Type Badge
            $ctBadge = '<span class="badge bg-light text-dark border px-2 py-1 fw-semibold">' . esc($row['content_type']) . '</span>';

            // Rate Price
            $finalRate = (float)$row['final_rate'];
            $rateFormatted = 'Rp ' . number_format($finalRate, 0, ',', '.');

            // Negotiable Badge
            $negoBadge = $row['negotiable'] == 1
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-handshake me-1"></i>Bisa Nego</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Fixed / Nett</span>';

            // Status Badge
            $statusBadge = $row['is_active'] == 1
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check me-1"></i>Aktif</span>'
                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>Nonaktif</span>';

            // Created At
            $createdAt = !empty($row['created_at'])
                ? '<span class="small text-muted">' . date('d M Y', strtotime($row['created_at'])) . '</span>'
                : '-';

            // Actions
            $actions = '
                <div class="btn-group btn-group-sm">
                    <a href="' . base_url('kol-rate-cards/view/' . $row['id']) . '" class="btn btn-outline-info" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-edit" data-id="' . $row['id'] . '" title="Edit Rate Card">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-delete" data-id="' . $row['id'] . '" data-name="' . esc($row['kol_name']) . ' - ' . esc($row['content_type']) . '" title="Hapus Rate Card">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'checkbox'     => '<input type="checkbox" class="form-check-input item-check" value="' . $row['id'] . '">',
                'id'           => $row['id'],
                'kol_name'     => $kolLink,
                'platform'     => $platformBadge,
                'username'     => $usernameHtml,
                'content_type' => $ctBadge,
                'rate'         => '<span class="fw-bold text-dark">' . $rateFormatted . '</span>',
                'negotiable'   => $negoBadge,
                'status'       => $statusBadge,
                'created_at'   => $createdAt,
                'actions'      => $actions,
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
     * Get single record for edit modal
     */
    public function getOne(int $id)
    {
        $rateCard = $this->kolRateCardModel->getDetail($id);
        if (!$rateCard) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Rate card KOL tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $rateCard,
        ]);
    }

    /**
     * Simpan data Rate Card (Tambah Baru atau Update)
     */
    public function save()
    {
        $db = \Config\Database::connect();

        $id             = $this->request->getPost('id');
        $kolPlatformId  = (int)$this->request->getPost('kol_platform_id');
        $kolId          = (int)$this->request->getPost('kol_id');
        $platformId     = (int)$this->request->getPost('platform_id');
        $contentType    = trim((string)$this->request->getPost('content_type'));
        $rate           = (float)$this->request->getPost('rate');
        $currencyCode   = strtoupper(trim((string)$this->request->getPost('currency_code') ?: 'IDR'));
        $negotiable     = (int)$this->request->getPost('negotiable');
        $isActive       = (int)$this->request->getPost('is_active');
        $notes          = trim((string)$this->request->getPost('notes'));

        // If kol_platform_id provided, sync kol_id & platform_id
        if (!empty($kolPlatformId)) {
            $kp = $db->table('kol_platforms')->where('id', $kolPlatformId)->get()->getRowArray();
            if ($kp) {
                $kolId = (int)$kp['kol_id'];
                $platformId = (int)$kp['platform_id'];
            }
        } elseif (!empty($kolId) && !empty($platformId)) {
            // lookup kol_platform_id
            $kp = $db->table('kol_platforms')
                ->where('kol_id', $kolId)
                ->where('platform_id', $platformId)
                ->get()
                ->getRowArray();
            if ($kp) {
                $kolPlatformId = (int)$kp['id'];
            }
        }

        // Validation rules
        $rules = [
            'content_type' => 'required|max_length[100]',
            'rate'         => 'required|numeric',
        ];

        if (empty($kolId) || empty($platformId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan pilih KOL dan Platform yang valid.',
            ]);
        }

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $this->validator->getErrors()),
            ]);
        }

        // Check duplicate
        $excludeId = !empty($id) ? (int)$id : null;
        if ($this->kolRateCardModel->checkExists($kolId, $platformId, $contentType, $excludeId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rate card untuk tipe konten ini sudah ada pada KOL & Platform terpilih. Silakan perbarui data yang ada.',
            ]);
        }

        $data = [
            'kol_id'          => $kolId,
            'platform_id'     => $platformId,
            'kol_platform_id' => !empty($kolPlatformId) ? $kolPlatformId : null,
            'content_type'    => $contentType,
            'rate'            => $rate,
            'rate_price'      => $rate,
            'currency_code'   => $currencyCode,
            'negotiable'      => $negotiable,
            'is_active'       => $isActive,
            'notes'           => $notes,
        ];

        if (!empty($id)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->kolRateCardModel->update($id, $data);
            $msg = 'Rate card KOL berhasil diperbarui!';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->kolRateCardModel->insert($data);
            $msg = 'Rate card KOL baru berhasil ditambahkan!';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Detail View Page: Menampilkan detail lengkap rate card
     */
    public function view(int $id)
    {
        $rateCard = $this->kolRateCardModel->getDetail($id);
        if (!$rateCard) {
            return redirect()->to(base_url('kol-rate-cards'))->with('error', 'Rate card KOL tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Rate Card - ' . ($rateCard['kol_name'] ?? 'KOL'),
            'controller' => 'KolRateCards',
            'rateCard'   => $rateCard,
        ];

        return view('kol_rate_cards/view', $data);
    }

    /**
     * Hapus satu data Rate Card
     */
    public function delete(int $id)
    {
        $rateCard = $this->kolRateCardModel->find($id);
        if (!$rateCard) {
            $msg = 'Data rate card tidak ditemukan.';
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('kol-rate-cards'))->with('error', $msg);
        }

        $this->kolRateCardModel->delete($id);

        $msg = 'Rate card KOL berhasil dihapus.';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('kol-rate-cards'))->with('success', $msg);
    }

    /**
     * Hapus massal data Rate Card
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada rate card yang dipilih untuk dihapus.',
            ]);
        }

        $this->kolRateCardModel->whereIn('id', $ids)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($ids) . ' rate card berhasil dihapus.',
        ]);
    }

    /**
     * Export data Rate Card ke file CSV
     */
    public function export()
    {
        $filters = [
            'platform_id' => $this->request->getGet('platform_id') ?: null,
            'kol_id'      => $this->request->getGet('kol_id') ?: null,
        ];

        $data = $this->kolRateCardModel->getAllForExport($filters);

        $filename = 'kol_rate_cards_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'Nama KOL',
            'Platform',
            'Username Handle',
            'Tipe Konten',
            'Tarif / Budget (IDR)',
            'Mata Uang',
            'Status Nego',
            'Status Keaktifan',
            'Catatan',
            'Tanggal Dibuat'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['kol_name'],
                $row['platform_name'],
                $row['platform_username'] ? '@' . ltrim($row['platform_username'], '@') : '-',
                $row['content_type'],
                number_format((float)$row['rate'], 0, ',', '.'),
                $row['currency_code'] ?: 'IDR',
                $row['negotiable'] == 1 ? 'Bisa Nego' : 'Fixed / Nett',
                $row['is_active'] == 1 ? 'Aktif' : 'Nonaktif',
                $row['notes'] ?: '-',
                $row['created_at'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
