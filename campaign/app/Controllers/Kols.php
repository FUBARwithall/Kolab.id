<?php

namespace App\Controllers;

use App\Models\KolModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Kols extends BaseController
{
    protected KolModel $kolModel;
    protected $db;

    public function __construct()
    {
        $this->kolModel = new KolModel();
        $this->db       = \Config\Database::connect();
    }

    /**
     * Direktori & Discovery KOL (View Only)
     */
    public function index()
    {
        $categories = $this->db->table('categories')->orderBy('name', 'ASC')->get()->getResultArray();
        $tiers      = $this->db->table('tiers')->orderBy('min_followers', 'ASC')->get()->getResultArray();

        $data = [
            'title'       => 'Direktori & Performance KOL',
            'controller'  => 'kols',
            'categories'  => $categories,
            'tiers'       => $tiers,
        ];

        return view('kols/index', $data);
    }

    /**
     * JSON Endpoint for DataTables list
     */
    public function getDatatable()
    {
        $categoryId    = $this->request->getVar('category_id');
        $status        = $this->request->getVar('status');
        $isBlacklisted = $this->request->getVar('is_blacklisted');

        $filters = [];
        if (!empty($categoryId)) {
            $filters['category_id'] = $categoryId;
        }
        if (!empty($status)) {
            $filters['status'] = $status;
        }
        if ($isBlacklisted !== null && $isBlacklisted !== '') {
            $filters['is_blacklisted'] = $isBlacklisted;
        }

        $kols = $this->kolModel->getKolsList($filters);
        $data = [];

        foreach ($kols as $k) {
            // Format initials
            $name = $k['name'];
            $initials = strtoupper(substr($name, 0, 1));
            $parts = explode(' ', trim($name));
            if (count($parts) > 1) {
                $initials .= strtoupper(substr($parts[1], 0, 1));
            }

            // Format Tier Badge
            $tierName = $k['tier']['name'] ?? 'Nano';
            $tierBadgeColors = [
                'Nano'  => 'bg-secondary',
                'Micro' => 'bg-info',
                'Mid'   => 'bg-primary',
                'Macro' => 'bg-warning text-dark',
                'Mega'  => 'bg-danger',
            ];
            $tierColor = $tierBadgeColors[$tierName] ?? 'bg-secondary';
            $tierHtml = '<span class="badge ' . $tierColor . ' px-2 py-0.5 text-xs ms-1">' . esc($tierName) . '</span>';

            // KOL Profile Column
            $profileHtml = '
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 13px; flex-shrink: 0;">
                        ' . esc($initials) . '
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-6 d-flex align-items-center">
                            ' . esc($name) . '
                            ' . $tierHtml . '
                        </div>
                        <div class="text-muted text-xs">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i>' . esc($k['city'] ?: 'Indonesia') . '
                        </div>
                    </div>
                </div>';

            // Category Badges
            $catHtml = '';
            if (!empty($k['category_names'])) {
                $cats = explode(', ', $k['category_names']);
                foreach ($cats as $cat) {
                    $catHtml .= '<span class="badge bg-light text-dark border me-1 mb-1 text-xs">' . esc($cat) . '</span>';
                }
            } else {
                $catHtml = '<span class="text-muted text-xs">-</span>';
            }

            // Platforms & Channels
            $platHtml = '<div class="d-flex flex-wrap gap-1">';
            if (!empty($k['platforms'])) {
                foreach ($k['platforms'] as $p) {
                    $platHtml .= '
                        <span class="badge bg-white border text-dark d-inline-flex align-items-center px-1.5 py-1 text-xs" title="' . esc($p['platform_name'] . ': @' . $p['username']) . '">
                            <i class="' . esc($p['platform_icon']) . ' me-1 text-primary"></i>@' . esc($p['username']) . '
                        </span>';
                }
            } else {
                $platHtml .= '<span class="text-muted text-xs">Belum ada channel</span>';
            }
            $platHtml .= '</div>';

            // Followers formatting
            $totalFollowers = (int)$k['total_followers'];
            if ($totalFollowers >= 1000000) {
                $followersFormatted = round($totalFollowers / 1000000, 1) . 'M';
            } elseif ($totalFollowers >= 1000) {
                $followersFormatted = round($totalFollowers / 1000, 1) . 'K';
            } else {
                $followersFormatted = number_format($totalFollowers);
            }

            // ER formatting & badge color
            $erVal = (float)$k['avg_er'] * 100;
            if ($erVal >= 5.0) {
                $erBadge = '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-fire me-1"></i>' . number_format($erVal, 2) . '%</span>';
            } elseif ($erVal >= 2.0) {
                $erBadge = '<span class="badge bg-primary text-white px-2 py-1">' . number_format($erVal, 2) . '%</span>';
            } else {
                $erBadge = '<span class="badge bg-secondary text-white px-2 py-1">' . number_format($erVal, 2) . '%</span>';
            }

            // Rating
            $ratingVal = (float)$k['avg_rating'];
            $reviewCount = (int)$k['review_count'];
            if ($ratingVal > 0) {
                $ratingHtml = '<div class="fw-bold text-warning text-sm"><i class="fas fa-star me-1"></i>' . number_format($ratingVal, 1) . ' <span class="text-muted text-xs fw-normal">(' . $reviewCount . ')</span></div>';
            } else {
                $ratingHtml = '<span class="text-muted text-xs">Belum dinilai</span>';
            }

            // Status
            if (!empty($k['is_blacklisted'])) {
                $statusHtml = '<span class="badge bg-danger">Blacklisted</span>';
            } elseif ($k['approval_status'] === 'APPROVED') {
                $statusHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span>';
            } else {
                $statusHtml = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">' . esc($k['approval_status']) . '</span>';
            }

            // Action Buttons (Strictly View Only)
            $actionHtml = '
                <div class="d-flex gap-1 justify-content-end">
                    <a href="' . base_url('kols/view/' . $k['id']) . '" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs" title="Lihat Profil Lengkap">
                        <i class="fas fa-eye me-1"></i> Detail
                    </a>
                    <a href="' . base_url('kols/performance-card/' . $k['id']) . '" class="btn btn-xs btn-gradient-navy text-white rounded-pill px-2.5 py-1 text-xs" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);" title="Buka Executive Performance Card">
                        <i class="fas fa-id-card-alt text-warning me-1"></i> Card
                    </a>
                </div>';

            $data[] = [
                'id'         => '#' . $k['id'],
                'profile'    => $profileHtml,
                'categories' => $catHtml,
                'channels'   => $platHtml,
                'followers'  => '<div class="fw-bold text-dark text-sm">' . $followersFormatted . '</div><small class="text-muted text-xs">' . number_format($totalFollowers) . '</small>',
                'avg_er'     => $erBadge,
                'rating'     => $ratingHtml,
                'status'     => $statusHtml,
                'actions'    => $actionHtml,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * View KOL Detailed Profile (View Only)
     */
    public function view($id)
    {
        $kol = $this->kolModel->getKolDetail((int)$id);
        if (!$kol) {
            throw PageNotFoundException::forPageNotFound("Data KOL dengan ID #$id tidak ditemukan.");
        }

        $data = [
            'title'      => 'Detail Profil KOL — ' . $kol['name'],
            'controller' => 'kols',
            'kol'        => $kol,
        ];

        return view('kols/view', $data);
    }

    /**
     * Executive Performance Card for KOL
     */
    public function performanceCard($id)
    {
        $kol = $this->kolModel->getPerformanceCardData((int)$id);
        if (!$kol) {
            throw PageNotFoundException::forPageNotFound("Data KOL dengan ID #$id tidak ditemukan.");
        }

        $data = [
            'title'      => 'Performance Card — ' . $kol['name'],
            'controller' => 'kols',
            'kol'        => $kol,
        ];

        return view('kols/performance_card', $data);
    }

    /**
     * JSON Endpoint for Chart.js Trends
     */
    public function chartData($id)
    {
        $kol = $this->kolModel->getPerformanceCardData((int)$id);
        if (!$kol) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'KOL not found']);
        }

        return $this->response->setJSON($kol['chart']);
    }
}
