<?php

namespace App\Controllers;

use App\Models\BriefModel;
use App\Models\TaskModel;
use App\Models\CampaignModel;

class Briefs extends BaseController
{
    protected BriefModel $briefModel;
    protected TaskModel $taskModel;
    protected CampaignModel $campaignModel;

    public function __construct()
    {
        $this->briefModel = new BriefModel();
        $this->taskModel = new TaskModel();
        $this->campaignModel = new CampaignModel();
    }

    /**
     * Halaman Utama Briefs & Guidelines
     */
    public function index($offset = 0)
    {
        // CI3 parity: check if explicit redirect to tasks sow is requested
        if ($this->request->getGet('raw') === '0') {
            return redirect()->to(base_url('tasks?view=sow'));
        }

        $filters = [
            'campaign_id'     => $this->request->getGet('campaign_id') ?: null,
            'approval_status' => $this->request->getGet('approval_status') ?: ($this->request->getGet('status') ?: null),
        ];

        $metrics = $this->briefModel->getSummaryMetrics($filters);

        // Fetch tasks available for new brief assignment (tasks without a brief or all active tasks)
        $tasksBuilder = $this->taskModel->db->table('tasks')
            ->select('tasks.id, tasks.task_number, tasks.title, campaigns.title as campaign_title, kols.name as kol_name')
            ->join('campaign_kols', 'campaign_kols.id = tasks.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('tasks.id', 'DESC')
            ->limit(100);
        $availableTasks = $tasksBuilder->get()->getResultArray();

        $data = [
            'title'          => 'Briefs & Guidelines — Pengarahan Konten KOL',
            'controller'     => 'Briefs',
            'metrics'        => $metrics,
            'allCampaigns'   => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
            'availableTasks' => $availableTasks,
        ];

        return view('briefs/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
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
            0 => 'briefs.id',
            1 => 'briefs.title',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'briefs.status',
            5 => 'briefs.revision_count',
            6 => 'briefs.created_at',
            7 => 'briefs.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'briefs.id';

        $filters = [
            'approval_status' => $this->request->getPost('approval_status'),
            'campaign_id'     => $this->request->getPost('campaign_id'),
        ];

        $result = $this->briefModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusVal = strtoupper((string)($row['status'] ?: $row['approval_status']));
            $statusBadge = match ($statusVal) {
                'APPROVED' => '<span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVED</span>',
                'REVISION', 'REVISION_NEEDED', 'REVISED' => '<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-undo me-1"></i>REVISION (' . ($row['revision_count'] ?? 0) . 'x)</span>',
                'REJECTED' => '<span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-times me-1"></i>REJECTED</span>',
                'PENDING_REVIEW' => '<span class="badge bg-primary text-white px-2 py-1"><i class="fas fa-clock me-1"></i>PENDING REVIEW</span>',
                default => '<span class="badge bg-secondary text-white px-2 py-1"><i class="far fa-edit me-1"></i>DRAFT</span>',
            };

            $revBadge = '';
            if (!empty($row['revision_count']) && (int)$row['revision_count'] > 0) {
                $revBadge = '<span class="badge bg-light text-dark border ms-1" title="Jumlah Revisi"><i class="fas fa-history me-1 text-muted"></i>' . (int)$row['revision_count'] . 'x</span>';
            }

            $formattedData[] = [
                'id'       => $row['id'],
                'checkbox' => '<input type="checkbox" class="brief-row-check form-check-input" value="' . $row['id'] . '">',
                'title'    => '
                    <div class="fw-bold text-dark">' . esc($row['title']) . ' ' . $revBadge . '</div>
                    <small class="text-muted"><i class="fas fa-tasks me-1"></i>Task: ' . esc($row['task_title'] ?: ('VT #' . $row['task_number'])) . '</small>
                ',
                'campaign' => '<div class="fw-semibold text-primary"><i class="fas fa-bullhorn me-1"></i>' . esc($row['campaign_title']) . '</div>',
                'kol'      => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;"><i class="fas fa-user-circle me-1 text-secondary"></i>' . esc($row['kol_name']) . '</div>
                    <small class="text-muted">Submitter: ' . esc($row['submitter_name'] ?? 'Admin') . '</small>
                ',
                'status'   => $statusBadge,
                'created'  => '<div>' . date('d M Y', strtotime($row['created_at'])) . '</div><small class="text-muted">' . date('H:i', strtotime($row['created_at'])) . ' WIB</small>',
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="' . base_url('briefs/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Brief Page"><i class="fas fa-eye"></i></a>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Brief" onclick="editBrief(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Review & Approval" onclick="reviewBriefModal(' . $row['id'] . ')"><i class="fas fa-clipboard-check"></i></button>
                        <a href="' . base_url('briefs/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Single PDF / Cetak"><i class="fas fa-print"></i></a>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Brief" onclick="deleteBrief(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
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
     * Get single brief details, approval history & version history
     */
    public function getOne(int $id)
    {
        $brief = $this->briefModel->getWithDetails($id);
        if (!$brief) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Brief tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $brief]);
    }

    /**
     * View standalone brief detail page
     */
    public function view(int $id)
    {
        $brief = $this->briefModel->getWithDetails($id);
        if (!$brief) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Brief tidak ditemukan']);
            }
            return redirect()->to(base_url('briefs'))->with('error', 'Brief tidak ditemukan');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'data' => $brief]);
        }

        $data = [
            'title'      => 'Detail Brief: ' . $brief['title'],
            'controller' => 'Briefs',
            'brief'      => $brief,
        ];

        return view('briefs/view', $data);
    }

    /**
     * Add view
     */
    public function add()
    {
        return redirect()->to(base_url('briefs?action=add'));
    }

    /**
     * Add save endpoint (CI3 parity alias for save)
     */
    public function add_save()
    {
        return $this->save();
    }

    /**
     * Edit view
     */
    public function edit(int $id)
    {
        return redirect()->to(base_url('briefs?action=edit&id=' . $id));
    }

    /**
     * Edit save endpoint (CI3 parity alias for save)
     */
    public function edit_save(int $id)
    {
        return $this->save();
    }

    /**
     * Save / update brief
     */
    public function save()
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);

        $id            = $this->request->getPost('id');
        $taskId        = (int) $this->request->getPost('task_id');
        $title         = trim((string)$this->request->getPost('title'));
        $content       = trim((string)($this->request->getPost('content') ?: $this->request->getPost('guidelines')));
        $dos           = trim((string)$this->request->getPost('dos'));
        $donts         = trim((string)$this->request->getPost('donts'));
        $referenceUrl  = trim((string)$this->request->getPost('reference_url'));
        $status        = trim((string)($this->request->getPost('status') ?: 'DRAFT'));
        $filePath      = trim((string)$this->request->getPost('file_path'));

        // Handle file upload if present
        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/briefs', $newName);
            $filePath = 'uploads/briefs/' . $newName;
        }

        if (empty($title) || empty($content) || empty($taskId)) {
            $errMsg = 'Judul brief, tugas target, dan petunjuk/konten wajib diisi.';
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $errMsg]);
            }
            return redirect()->back()->withInput()->with('error', $errMsg);
        }

        // Map canonical approval status
        $approvalStatus = match (strtoupper($status)) {
            'APPROVED' => BriefModel::STATUS_APPROVED,
            'REVISION', 'REVISION_NEEDED', 'REVISED' => BriefModel::STATUS_REVISION_NEEDED,
            'REJECTED' => BriefModel::STATUS_REJECTED,
            default => BriefModel::STATUS_PENDING,
        };

        $briefData = [
            'task_id'         => $taskId,
            'title'           => $title,
            'content'         => $content,
            'guidelines'      => $content,
            'dos'             => $dos ?: null,
            'donts'           => $donts ?: null,
            'reference_url'   => $referenceUrl ?: null,
            'status'          => $status,
            'approval_status' => $approvalStatus,
            'file_path'       => $filePath ?: null,
            'submitted_by'    => $userId,
            'created_by'      => $userId,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if (!empty($id)) {
            $this->briefModel->update($id, $briefData);
            $briefId = (int)$id;
            $msg = 'Brief berhasil diperbarui.';
        } else {
            $briefData['created_at'] = date('Y-m-d H:i:s');
            $briefId = $this->briefModel->insert($briefData, true);
            $msg = 'Brief berhasil dibuat dan diajukan.';
        }

        // Update task status ke BRIEF_SUBMITTED jika baru diajukan
        if ($status === 'PENDING_REVIEW' || $approvalStatus === BriefModel::STATUS_PENDING) {
            $this->taskModel->update($taskId, [
                'status'     => TaskModel::STATUS_BRIEF_SUBMITTED,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => $msg,
                'brief_id' => $briefId,
            ]);
        }

        return redirect()->to(base_url('briefs'))->with('message', $msg);
    }

    /**
     * Submit brief for Leader review (Staff Action)
     */
    public function submit_review(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);

        if (!$this->briefModel->submitReview($id, $userId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengajukan brief untuk review',
                ]);
            }
            return redirect()->back()->with('error', 'Gagal mengajukan brief untuk review.');
        }

        $msg = 'Brief berhasil diajukan untuk review Leader.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('briefs'))->with('message', $msg);
    }

    /**
     * Approve brief (Leader Action) -> advances task to WAITING_DRAFT
     */
    public function approve(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);
        $notes   = trim((string)($this->request->getPost('notes') ?: ($this->request->getPost('note') ?: 'Brief disetujui tanpa catatan revisi.')));

        if (!$this->briefModel->approveBrief($id, $userId, $notes)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyetujui brief',
                ]);
            }
            return redirect()->back()->with('error', 'Gagal menyetujui brief.');
        }

        $msg = 'Brief dan Task berhasil disetujui. Siap menuju tahap draft.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('tasks?view=sow'))->with('message', $msg);
    }

    /**
     * Request brief revision (Leader Action) -> snapshots current brief content
     */
    public function revision(int $id)
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);
        $notes   = trim((string)($this->request->getPost('notes') ?: ($this->request->getPost('note') ?: ($this->request->getGet('note') ?: 'Revision needed'))));

        if (empty($notes)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Catatan revisi wajib diisi agar KOL/staff mengetahui bagian yang harus diperbaiki.',
                ]);
            }
            return redirect()->back()->with('error', 'Catatan revisi wajib diisi.');
        }

        if (!$this->briefModel->requestRevision($id, $userId, $notes)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Gagal meminta revisi brief',
                ]);
            }
            return redirect()->back()->with('error', 'Gagal meminta revisi brief.');
        }

        $msg = 'Brief dikembalikan untuk revisi. Riwayat versi lama telah disimpan secara otomatis.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('tasks?view=sow'))->with('message', $msg);
    }

    /**
     * Delete single brief
     */
    public function delete($id = null)
    {
        $targetId = $id ?: ($this->request->getGet('id') ?: $this->request->getPost('id'));
        if (empty($targetId)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID brief tidak diberikan']);
            }
            return redirect()->back()->with('error', 'ID brief tidak valid');
        }

        if (is_array($targetId)) {
            return $this->bulkDelete();
        }

        $brief = $this->briefModel->find($targetId);
        if (!$brief) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Brief tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Brief tidak ditemukan');
        }

        $this->briefModel->delete($targetId);

        $msg = 'Brief berhasil dihapus.';
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }

        return redirect()->to(base_url('briefs'))->with('message', $msg);
    }

    /**
     * Bulk delete briefs
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids') ?: $this->request->getGet('id');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tidak ada brief yang dipilih untuk dihapus',
            ]);
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if (!empty($id)) {
                $this->briefModel->delete($id);
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Berhasil menghapus {$deletedCount} brief.",
        ]);
    }

    /**
     * Export briefs to CSV / Excel
     */
    public function export()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: ($this->request->getGet('approval_status') ?: null),
        ];

        $data = $this->briefModel->getAllForExport($filters);
        $filename = 'briefs_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, [
            'ID',
            'No. VT',
            'Judul Brief',
            'Judul Tugas (Task)',
            'Kampanye',
            'KOL / Talent',
            'Petunjuk / Konten',
            'Do\'s',
            'Don\'ts',
            'Moodboard / Link Referensi',
            'Jumlah Revisi',
            'Status',
            'Submitter',
            'Tanggal Dibuat',
            'Tanggal Submit',
            'Terakhir Diperbarui'
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['task_number'] ?: '-',
                $row['title'],
                $row['task_title'] ?: '-',
                $row['campaign_title'] ?: '-',
                $row['kol_name'] ?: '-',
                $row['content'] ?: ($row['guidelines'] ?: '-'),
                $row['dos'] ?: '-',
                $row['donts'] ?: '-',
                $row['reference_url'] ?: '-',
                (int)($row['revision_count'] ?? 0),
                $row['status'] ?: ($row['approval_status'] ?: 'DRAFT'),
                $row['submitter_name'] ?: 'Admin',
                $row['created_at'] ?: '-',
                $row['submitted_at'] ?: '-',
                $row['updated_at'] ?: '-',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export briefs to printable PDF-ready page
     */
    public function export_pdf()
    {
        $filters = [
            'campaign_id' => $this->request->getGet('campaign_id') ?: null,
            'status'      => $this->request->getGet('status') ?: null,
        ];

        $data = $this->briefModel->getAllForExport($filters);

        return view('briefs/export_pdf', [
            'title'     => 'Daftar Briefs & Guidelines',
            'briefs'    => $data,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }

    /**
     * Export / Print single brief (CI3 parity single_pdf)
     */
    public function single_pdf(int $id)
    {
        $brief = $this->briefModel->getWithDetails($id);
        if (!$brief) {
            return redirect()->to(base_url('briefs'))->with('error', 'Brief tidak ditemukan');
        }

        return view('briefs/single_pdf', [
            'title'     => 'Brief #' . $brief['id'] . ' - ' . $brief['title'],
            'brief'     => $brief,
            'printedAt' => date('d M Y H:i:s'),
        ]);
    }
}
