<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CampaignKolModel;
use CodeIgniter\HTTP\ResponseInterface;

class CampaignKols extends BaseController
{
    protected CampaignKolModel $campaignKolModel;

    public function __construct()
    {
        $this->campaignKolModel = new CampaignKolModel();
    }

    /**
     * Daftarkan KOL ke Kampanye (Assign)
     */
    public function assign(): ResponseInterface
    {
        $campaignId = (int)$this->request->getPost('campaign_id');
        $kolId      = (int)$this->request->getPost('kol_id');
        $dealAmount = (float)$this->request->getPost('deal_amount');
        $totalVt    = (int)($this->request->getPost('total_vt') ?? 1);
        $notes      = trim($this->request->getPost('notes') ?? '');

        if (!$campaignId || !$kolId || $dealAmount <= 0 || $totalVt <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter penugasan KOL tidak lengkap atau tidak valid.',
            ]);
        }

        // Cek apakah KOL sudah pernah di-assign ke kampanye ini
        $exists = $this->campaignKolModel
            ->where('campaign_id', $campaignId)
            ->where('kol_id', $kolId)
            ->first();

        if ($exists) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Talent KOL ini sudah terdaftar di kampanye ini.',
            ]);
        }

        $assignedId = $this->campaignKolModel->assignKolWithTasks(
            $campaignId,
            $kolId,
            $dealAmount,
            $totalVt,
            $notes,
            [],
            session()->get('user_id') ?: 1
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Talent KOL berhasil didaftarkan dan ' . $totalVt . ' draf tugas video (VT) otomatis dibuat.',
            'assigned_id' => $assignedId,
        ]);
    }

    /**
     * Ambil daftar tugas (Tasks / VT) untuk KOL dalam kampanye
     */
    public function getTasksJson(int $campaignKolId): ResponseInterface
    {
        $db = \Config\Database::connect();
        $tasks = $db->table('tasks')
            ->where('campaign_kol_id', $campaignKolId)
            ->orderBy('task_number', 'asc')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'tasks'   => $tasks,
        ]);
    }

    /**
     * Hapus penugasan KOL dari kampanye
     */
    public function remove(int $id): ResponseInterface
    {
        $ck = $this->campaignKolModel->find($id);
        if (!$ck) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data penugasan tidak ditemukan.',
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();
        // Hapus task dan kontrak terkait
        $db->table('tasks')->where('campaign_kol_id', $id)->delete();
        $db->table('campaign_kol_variants')->where('campaign_kol_id', $id)->delete();
        $this->campaignKolModel->delete($id);
        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Penugasan KOL berhasil dihapus beserta draf tugasnya.',
        ]);
    }

    /**
     * Submit Rating & Review untuk KOL dalam Kampanye
     */
    public function submitReview(): ResponseInterface
    {
        $kolId       = (int)$this->request->getPost('kol_id');
        $campaignId  = (int)$this->request->getPost('campaign_id');
        $rating      = (int)$this->request->getPost('rating');
        $reviewText  = trim($this->request->getPost('review_text') ?? '');
        $userId      = session()->get('user_id') ?: 1;

        if (!$kolId || $rating < 1 || $rating > 5) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rating harus bernilai 1 sampai 5 bintang.',
            ]);
        }

        $db = \Config\Database::connect();
        
        $existing = $db->table('kol_reviews')
            ->where('kol_id', $kolId)
            ->where('campaign_id', $campaignId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('kol_reviews')->where('id', $existing['id'])->update([
                'rating'      => $rating,
                'review_text' => $reviewText,
                'reviewed_by' => $userId,
            ]);
            $action = 'UPDATE';
            $reviewId = $existing['id'];
        } else {
            $db->table('kol_reviews')->insert([
                'kol_id'      => $kolId,
                'campaign_id' => $campaignId ?: null,
                'rating'      => $rating,
                'review_text' => $reviewText,
                'reviewed_by' => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
            $action = 'CREATE';
            $reviewId = $db->insertID();
        }

        // Catat ke audit log
        \App\Models\AuditLogModel::log(
            $action,
            'kol_reviews',
            $reviewId,
            null,
            ['rating' => $rating, 'review_text' => $reviewText, 'kol_id' => $kolId, 'campaign_id' => $campaignId],
            $userId
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Review dan rating berhasil disimpan.',
        ]);
    }

    /**
     * Tinjau Lamaran Kampanye (Terima / Tolak)
     */
    public function reviewApplication(): ResponseInterface
    {
        $applicationId = (int)$this->request->getPost('application_id');
        $action        = strtoupper(trim($this->request->getPost('action') ?? '')); // ACCEPT or REJECT
        $dealAmount    = (float)$this->request->getPost('deal_amount');
        $totalVt       = (int)($this->request->getPost('total_vt') ?? 1);
        $notes         = trim($this->request->getPost('notes') ?? '');
        $userId        = session()->get('user_id') ?: 1;

        $db = \Config\Database::connect();
        $app = $db->table('campaign_applications')->where('id', $applicationId)->get()->getRowArray();
        if (!$app) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data lamaran tidak ditemukan.',
            ]);
        }

        if ($action === 'ACCEPT') {
            // Cek apakah KOL sudah terdaftar di kampanye ini
            $exists = $this->campaignKolModel
                ->where('campaign_id', $app['campaign_id'])
                ->where('kol_id', $app['kol_id'])
                ->first();

            if (!$exists) {
                $this->campaignKolModel->assignKolWithTasks(
                    (int)$app['campaign_id'],
                    (int)$app['kol_id'],
                    $dealAmount > 0 ? $dealAmount : (float)$app['proposed_rate'],
                    $totalVt > 0 ? $totalVt : 1,
                    $notes ?: ($app['pitch_notes'] ?? 'Diterima dari lamaran terbuka'),
                    [],
                    $userId
                );
            }

            $db->table('campaign_applications')->where('id', $applicationId)->update([
                'status'      => 'ACCEPTED',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            \App\Models\AuditLogModel::log(
                'APPROVE',
                'campaign_applications',
                $applicationId,
                ['status' => 'APPLIED'],
                ['status' => 'ACCEPTED', 'deal_amount' => $dealAmount, 'total_vt' => $totalVt],
                $userId
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lamaran berhasil diterima dan KOL otomatis ditugaskan ke kampanye.',
            ]);
        } elseif ($action === 'REJECT') {
            $db->table('campaign_applications')->where('id', $applicationId)->update([
                'status'      => 'REJECTED',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            \App\Models\AuditLogModel::log(
                'REJECT',
                'campaign_applications',
                $applicationId,
                ['status' => 'APPLIED'],
                ['status' => 'REJECTED'],
                $userId
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lamaran kampanye telah ditolak.',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Aksi peninjauan tidak valid.',
        ]);
    }
}
