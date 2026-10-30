<?php

namespace App\Controllers;

use App\Models\KolPlatformModel;
use CodeIgniter\HTTP\ResponseInterface;

class KolPlatforms extends BaseController
{
    protected KolPlatformModel $kolPlatformModel;

    public function __construct()
    {
        $this->kolPlatformModel = new KolPlatformModel();
    }

    /**
     * Halaman Utama: Daftar Akun Platform KOL (Full Parity with CI3 modules/kol_platforms)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $totalAccounts  = $this->kolPlatformModel->countAllResults();
        $verifiedCount  = $db->table('kol_platforms')->where('is_verified', 1)->countAllResults();
        $activeCount    = $db->table('kol_platforms')->where('is_active', 1)->countAllResults();
        $totalFollowers = $db->table('kol_platforms')->selectSum('followers')->get()->getRowArray()['followers'] ?? 0;

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

        $data = [
            'title'      => 'Akun Platform KOL',
            'controller' => 'KolPlatforms',
            'stats'      => [
                'total_accounts'  => (int)$totalAccounts,
                'verified_count'  => (int)$verifiedCount,
                'active_count'    => (int)$activeCount,
                'total_followers' => (int)$totalFollowers,
            ],
            'kols'       => $kols,
            'platforms'  => $platforms,
        ];

        return view('kol_platforms/index', $data);
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
            0 => 'kol_platforms.id',
            1 => 'kol_platforms.id',
            2 => 'kols.name',
            3 => 'platforms.name',
            4 => 'kol_platforms.username',
            5 => 'kol_platforms.followers',
            6 => 'kol_platforms.avg_views',
            7 => 'kol_platforms.avg_er',
            8 => 'kol_platforms.is_verified',
            9 => 'kol_platforms.is_active',
            10 => 'kol_platforms.last_synced_at',
            11 => 'kol_platforms.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'kol_platforms.id';

        $filters = [
            'platform_id' => $this->request->getPost('platform_id') ?: null,
            'kol_id'      => $this->request->getPost('kol_id') ?: null,
            'is_verified' => $this->request->getPost('is_verified'),
            'is_active'   => $this->request->getPost('is_active'),
        ];

        $result = $this->kolPlatformModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $platformColors = [
            'TIKTOK'    => ['bg' => '#000000', 'color' => '#ffffff', 'badge' => 'bg-dark'],
            'INSTAGRAM' => ['bg' => '#e1306c', 'color' => '#ffffff', 'badge' => 'bg-danger'],
            'YOUTUBE'   => ['bg' => '#ff0000', 'color' => '#ffffff', 'badge' => 'bg-danger'],
            'TWITTER'   => ['bg' => '#1da1f2', 'color' => '#ffffff', 'badge' => 'bg-info text-white'],
            'FACEBOOK'  => ['bg' => '#1877f2', 'color' => '#ffffff', 'badge' => 'bg-primary'],
        ];

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $code = strtoupper($row['platform_code'] ?? '');
            $theme = $platformColors[$code] ?? ['bg' => '#6c757d', 'color' => '#ffffff', 'badge' => 'bg-secondary'];
            $icon = $row['platform_icon'] ?: 'fas fa-share-alt';

            // Build valid profile URL if missing
            $profileUrl = $row['profile_url'];
            if (empty($profileUrl)) {
                $cleanUn = ltrim($row['username'] ?? '', '@');
                $baseUrl = rtrim($row['platform_base_url'] ?? '', '/');
                if (!empty($baseUrl)) {
                    $profileUrl = $baseUrl . '/' . $cleanUn;
                }
            }

            // Platform badge with icon
            $platformBadge = '
                <span class="badge ' . $theme['badge'] . ' px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="' . esc($icon) . '"></i> ' . esc($row['platform_name'] ?: 'Unknown') . '
                </span>';

            // KOL Info
            $kolLink = '<a href="' . base_url('kol/view/' . $row['kol_id']) . '" class="fw-bold text-dark text-decoration-none hover-primary">
                ' . esc($row['kol_name'] ?? 'N/A') . '
            </a>';

            // Username with handle & profile link
            $usernameHtml = '<div class="d-flex align-items-center gap-1">
                <span class="fw-semibold text-secondary">@' . esc(ltrim($row['username'] ?? '', '@')) . '</span>';
            if (!empty($profileUrl)) {
                $usernameHtml .= ' <a href="' . esc($profileUrl) . '" target="_blank" rel="noopener noreferrer" class="text-primary small" title="Buka profil di tab baru"><i class="fas fa-external-link-alt fa-xs"></i></a>';
            }
            $usernameHtml .= '</div>';

            // Formatted Followers
            $followersFormatted = $this->formatNumber((int)($row['followers'] ?: 0));

            // Formatted Views
            $avgViewsFormatted = $this->formatNumber((int)($row['avg_views'] ?: 0));

            // Formatted ER
            $erVal = (float)($row['avg_er'] ?: 0.0);
            $erPercent = number_format($erVal > 1.0 ? $erVal : ($erVal * 100), 2) . '%';
            $erBadge = '<span class="badge bg-light text-dark border fw-bold">' . $erPercent . '</span>';

            // Verified Badge
            $verifiedBadge = $row['is_verified'] == 1
                ? '<span class="badge bg-info text-white shadow-sm" title="Centang Biru / Verified"><i class="fas fa-check-circle me-1"></i> Verified</span>'
                : '<span class="badge bg-light text-muted border">Biasa</span>';

            // Active Badge
            $statusBadge = $row['is_active'] == 1
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check me-1"></i>Aktif</span>'
                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>Nonaktif</span>';

            // Last synced
            $lastSynced = !empty($row['last_synced_at'])
                ? '<span class="small text-muted" title="' . esc($row['last_synced_at']) . '"><i class="far fa-clock me-1"></i>' . date('d M Y, H:i', strtotime($row['last_synced_at'])) . '</span>'
                : '<span class="small text-muted fst-italic">Belum pernah</span>';

            // Action Buttons
            $actions = '
                <div class="btn-group btn-group-sm">
                    <a href="' . base_url('kol-platforms/view/' . $row['id']) . '" class="btn btn-outline-info" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-edit" data-id="' . $row['id'] . '" title="Edit Akun">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sync" data-id="' . $row['id'] . '" data-name="' . esc($row['kol_name']) . '" title="Sinkronkan Metrik">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-delete" data-id="' . $row['id'] . '" data-name="' . esc($row['kol_name']) . ' - ' . esc($row['platform_name']) . '" title="Hapus Akun">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'checkbox'       => '<input type="checkbox" class="form-check-input item-check" value="' . $row['id'] . '">',
                'id'             => $row['id'],
                'kol_name'       => $kolLink,
                'platform_badge' => $platformBadge,
                'username'       => $usernameHtml,
                'followers'      => '<span class="fw-bold text-dark">' . $followersFormatted . '</span>',
                'avg_views'      => '<span class="text-secondary">' . $avgViewsFormatted . '</span>',
                'avg_er'         => $erBadge,
                'is_verified'    => $verifiedBadge,
                'status'         => $statusBadge,
                'last_synced_at' => $lastSynced,
                'actions'        => $actions,
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
        $platform = $this->kolPlatformModel->getDetail($id);
        if (!$platform) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Akun Platform KOL tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $platform,
        ]);
    }

    /**
     * Simpan data Akun Platform KOL (Tambah Baru atau Update)
     */
    public function save()
    {
        $id          = $this->request->getPost('id');
        $kolId       = (int)$this->request->getPost('kol_id');
        $platformId  = (int)$this->request->getPost('platform_id');
        $username    = trim((string)$this->request->getPost('username'));
        $profileUrl  = trim((string)$this->request->getPost('profile_url'));
        $followers   = (int)$this->request->getPost('followers');
        $avgViews    = (int)$this->request->getPost('avg_views');
        $avgEr       = (float)$this->request->getPost('avg_er');
        $isVerified  = (int)$this->request->getPost('is_verified');
        $isActive    = (int)$this->request->getPost('is_active');

        // Validation rules
        $rules = [
            'kol_id'      => 'required|numeric',
            'platform_id' => 'required|numeric',
            'username'    => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $this->validator->getErrors()),
            ]);
        }

        // Check duplicate
        $excludeId = !empty($id) ? (int)$id : null;
        if ($this->kolPlatformModel->checkExists($kolId, $platformId, $excludeId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'KOL ini sudah memiliki akun terdaftar untuk platform tersebut. Silakan edit akun yang sudah ada.',
            ]);
        }

        // Clean username
        $cleanUsername = ltrim($username, '@');

        // Auto-build profile URL if empty
        if (empty($profileUrl)) {
            $db = \Config\Database::connect();
            $plat = $db->table('platforms')->where('id', $platformId)->get()->getRowArray();
            if ($plat && !empty($plat['base_url'])) {
                $profileUrl = rtrim($plat['base_url'], '/') . '/' . $cleanUsername;
            }
        }

        $data = [
            'kol_id'          => $kolId,
            'platform_id'     => $platformId,
            'username'        => $cleanUsername,
            'profile_url'     => $profileUrl,
            'followers'       => $followers,
            'followers_count' => $followers,
            'avg_views'       => $avgViews,
            'avg_er'          => $avgEr,
            'is_verified'     => $isVerified,
            'is_active'       => $isActive,
        ];

        if (!empty($id)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->kolPlatformModel->update($id, $data);
            $msg = 'Akun Platform KOL berhasil diperbarui!';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['last_synced_at'] = date('Y-m-d H:i:s');
            $this->kolPlatformModel->insert($data);
            $msg = 'Akun Platform KOL baru berhasil ditambahkan!';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
        ]);
    }

    /**
     * Detail View Page: Menampilkan detail lengkap akun platform KOL
     */
    public function view(int $id)
    {
        $platform = $this->kolPlatformModel->getDetail($id);
        if (!$platform) {
            return redirect()->to(base_url('kol-platforms'))->with('error', 'Akun platform KOL tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Akun Platform - ' . ($platform['kol_name'] ?? 'KOL'),
            'controller' => 'KolPlatforms',
            'platform'   => $platform,
        ];

        return view('kol_platforms/view', $data);
    }

    /**
     * Sinkronisasi profil via Apify / Live Metrics
     */
    public function sync(int $id)
    {
        $result = $this->kolPlatformModel->syncProfile($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Hapus satu data Akun Platform KOL
     */
    public function delete(int $id)
    {
        $platform = $this->kolPlatformModel->find($id);
        if (!$platform) {
            $msg = 'Data akun platform tidak ditemukan.';
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => $msg])
                : redirect()->to(base_url('kol-platforms'))->with('error', $msg);
        }

        $this->kolPlatformModel->delete($id);

        $msg = 'Akun platform KOL berhasil dihapus.';
        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'message' => $msg])
            : redirect()->to(base_url('kol-platforms'))->with('success', $msg);
    }

    /**
     * Hapus massal data Akun Platform KOL
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada akun yang dipilih untuk dihapus.',
            ]);
        }

        $this->kolPlatformModel->whereIn('id', $ids)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($ids) . ' akun platform KOL berhasil dihapus.',
        ]);
    }

    /**
     * Export data Akun Platform KOL ke file CSV
     */
    public function export()
    {
        $filters = [
            'platform_id' => $this->request->getGet('platform_id') ?: null,
            'kol_id'      => $this->request->getGet('kol_id') ?: null,
        ];

        $data = $this->kolPlatformModel->getAllForExport($filters);

        $filename = 'kol_platforms_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'ID',
            'Nama KOL',
            'Platform',
            'Username',
            'URL Profil',
            'Followers',
            'Avg Views',
            'Avg Engagement Rate (%)',
            'Status Verified',
            'Status Aktif',
            'Terakhir Disinkron',
            'Dibuat Pada'
        ]);

        foreach ($data as $row) {
            $erVal = (float)($row['avg_er'] ?: 0.0);
            $erPercent = number_format($erVal > 1.0 ? $erVal : ($erVal * 100), 2) . '%';

            fputcsv($output, [
                $row['id'],
                $row['kol_name'],
                $row['platform_name'],
                '@' . ltrim($row['username'], '@'),
                $row['profile_url'],
                number_format((int)$row['followers'], 0, ',', '.'),
                number_format((int)$row['avg_views'], 0, ',', '.'),
                $erPercent,
                $row['is_verified'] == 1 ? 'Verified (Centang Biru)' : 'Biasa',
                $row['is_active'] == 1 ? 'Aktif' : 'Nonaktif',
                $row['last_synced_at'] ?: '-',
                $row['created_at'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Helper to format numbers like 1.2M, 450K
     */
    private function formatNumber(int $num): string
    {
        if ($num >= 1000000) {
            return number_format($num / 1000000, 1) . 'M';
        }
        if ($num >= 1000) {
            return number_format($num / 1000, 1) . 'K';
        }
        return number_format($num, 0, ',', '.');
    }
}
