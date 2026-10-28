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
}
