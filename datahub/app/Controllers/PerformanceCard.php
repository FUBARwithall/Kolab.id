<?php

namespace App\Controllers;

use App\Models\KolModel;
use App\Models\KolPlatformModel;
use App\Models\KolProfileSnapshotModel;
use App\Models\KolRateCardModel;
use CodeIgniter\HTTP\ResponseInterface;

class PerformanceCard extends BaseController
{
    protected KolModel $kolModel;
    protected KolPlatformModel $kolPlatformModel;
    protected KolProfileSnapshotModel $snapshotModel;
    protected KolRateCardModel $rateCardModel;
    protected string $sharedSecret;

    public function __construct()
    {
        $this->kolModel          = new KolModel();
        $this->kolPlatformModel  = new KolPlatformModel();
        $this->snapshotModel     = new KolProfileSnapshotModel();
        $this->rateCardModel     = new KolRateCardModel();
        $this->sharedSecret      = env('KOL_SHARED_SECRET', 'kolab-ci4-shared-secret-key-2026');
    }

    /**
     * Default index: Redirect talent to their own card, or show directory for admin
     */
    public function index()
    {
        $session   = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin   = in_array('admin', $userRoles, true);

        if (!$isAdmin) {
            return $this->myCard();
        }

        // For Superadmin: show index list of KOLs to pick a card
        $kols = $this->kolModel->select('
                kols.id,
                kols.name,
                kols.city,
                kols.avatar_url,
                kols.approval_status,
                (SELECT COUNT(*) FROM kol_platforms WHERE kol_id = kols.id) as platform_count,
                (SELECT SUM(followers_count) FROM kol_platforms WHERE kol_id = kols.id) as total_followers
            ')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('performance_card/index', [
            'title'      => 'KOL Performance Card Generator',
            'controller' => 'Performance Card',
            'kols'       => $kols,
            'isAdmin'    => $isAdmin,
        ]);
    }

    /**
     * Direct redirect to logged-in creator's own card
     */
    public function myCard()
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 0);

        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        $kol = $this->kolModel->where('user_id', $userId)->first();
        if ($kol) {
            return redirect()->to(base_url('performance-card/view/' . $kol['id']));
        }

        return redirect()->to(base_url('dashboard'))->with('error', 'Profil KOL belum terhubung dengan akun Anda. Silakan hubungi admin.');
    }

    /**
     * Main Performance Card View
     */
    public function view(int $kolId)
    {
        $session   = session();
        $userRoles = (array) ($session->get('roles') ?? []);
        $isAdmin   = in_array('admin', $userRoles, true);
        $userId    = (int) ($session->get('user_id') ?? 0);

        $isIframe = $this->request->getGet('is_iframe') === 'true';
        $t        = $this->request->getGet('t');
        $sig      = $this->request->getGet('sig');
        $isSigned = false;

        // Verify signed URL / token if provided
        if (!empty($t) && !empty($sig)) {
            $expectedSig = hash_hmac('sha256', $kolId . $t, $this->sharedSecret);
            if (hash_equals($expectedSig, (string) $sig)) {
                // Token valid for 2 hours
                if (abs(time() - (int) $t) <= 7200) {
                    $isSigned = true;
                }
            }
        }

        // Access Control
        if (!$isSigned) {
            if (!$isAdmin) {
                $myKol = $this->kolModel->where('user_id', $userId)->first();
                if (!$myKol || (int) $myKol['id'] !== $kolId) {
                    return redirect()->to(base_url('dashboard'))->with('error', 'Akses ditolak: Anda hanya dapat melihat Performance Card milik Anda sendiri.');
                }
            }
        }

        // Fetch comprehensive performance data
        $kol = $this->kolModel->getPerformanceCardData($kolId);
        if (!$kol) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data KOL #{$kolId} tidak ditemukan.");
        }

        // Format proxy URL helper for avatars
        $kol['safe_avatar'] = $this->formatAvatarUrl($kol['avatar_url'] ?? null);

        $data = [
            'title'      => 'Performance Card — ' . ($kol['name'] ?? 'KOL'),
            'controller' => 'Performance Card',
            'kol'        => $kol,
            'isAdmin'    => $isAdmin,
            'isIframe'   => $isIframe,
            't'          => $t,
            'sig'        => $sig,
        ];

        return view('performance_card/view', $data);
    }

    /**
     * AJAX endpoint: return monthly trend growth data as JSON
     */
    public function cardDataAjax(int $kolId): ResponseInterface
    {
        $kol = $this->kolModel->getPerformanceCardData($kolId);
        if (!$kol) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data KOL tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'chart'   => $kol['chart'] ?? [],
        ]);
    }

    /**
     * Generate shareable signed URL for embedding (e.g. in Campaign Management)
     */
    public function generateShareUrl(int $kolId): string
    {
        $t   = time();
        $sig = hash_hmac('sha256', $kolId . $t, $this->sharedSecret);

        return base_url("performance-card/view/{$kolId}?is_iframe=true&t={$t}&sig={$sig}");
    }

    /**
     * Helper to return proxy-safe avatar URL
     */
    protected function formatAvatarUrl(?string $avatarUrl): string
    {
        if (empty($avatarUrl)) {
            return base_url('assets/adminlte/dist/img/avatar5.png');
        }

        if (preg_match('/^https?:\/\//i', $avatarUrl)) {
            return base_url('proxy/avatar?url=' . urlencode($avatarUrl));
        }

        return base_url('uploads/' . ltrim($avatarUrl, '/'));
    }
}

