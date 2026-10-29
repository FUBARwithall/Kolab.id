<?php

namespace App\Controllers;

use App\Models\ContractTemplateModel;

class ContractTemplates extends BaseController
{
    protected ContractTemplateModel $templateModel;

    public function __construct()
    {
        $this->templateModel = new ContractTemplateModel();
    }

    /**
     * Halaman Utama Master Template Kontrak
     */
    public function index()
    {
        $data = [
            'title'      => 'Master Template Kontrak Kerjasama',
            'controller' => 'ContractTemplates',
        ];

        return view('contract_templates/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Direct access not allowed']);
        }

        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'id',
            1 => 'title',
            2 => 'body_template',
            3 => 'is_active',
            4 => 'created_at',
            5 => 'id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'id';

        $result = $this->templateModel->getDatatables($start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = $row['is_active']
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>AKTIF</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">NON-AKTIF</span>';

            $snippet = esc(mb_substr(strip_tags($row['body_template']), 0, 100)) . '...';

            $formattedData[] = [
                'id'       => $row['id'],
                'title'    => '<div class="fw-bold text-dark fs-6">' . esc($row['title']) . '</div>',
                'snippet'  => '<div class="text-muted small">' . $snippet . '</div>',
                'status'   => $statusBadge,
                'created'  => !empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-',
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Preview Template" onclick="previewTemplate(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Template" onclick="editTemplate(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Template" onclick="deleteTemplate(' . $row['id'] . ')"><i class="fas fa-trash-alt"></i></button>
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
     * Get single template JSON
     */
    public function getOne(int $id)
    {
        $tpl = $this->templateModel->find($id);
        if (!$tpl) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Template tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $tpl]);
    }

    /**
     * Save (Insert / Update) Template
     */
    public function save()
    {
        $id    = (int) $this->request->getPost('id');
        $title = trim((string) $this->request->getPost('title'));
        $body  = trim((string) $this->request->getPost('body_template'));
        $active= (int) ($this->request->getPost('is_active') ?? 1);

        if (empty($title) || empty($body)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Judul dan isi klausul template wajib diisi.']);
        }

        $data = [
            'title'         => $title,
            'body_template' => $body,
            'is_active'     => $active,
        ];

        if ($id > 0) {
            $this->templateModel->update($id, $data);
            $msg = 'Template kontrak berhasil diperbarui!';
        } else {
            $id = $this->templateModel->insert($data, true);
            $msg = 'Template kontrak baru berhasil dibuat!';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
            'id'      => $id,
        ]);
    }

    /**
     * Delete Template
     */
    public function delete(int $id)
    {
        $tpl = $this->templateModel->find($id);
        if (!$tpl) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Template tidak ditemukan']);
        }

        $this->templateModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Template kontrak berhasil dihapus.',
        ]);
    }
}
