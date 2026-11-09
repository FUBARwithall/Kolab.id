<?php

namespace App\Controllers;

use App\Models\ContractTemplateModel;

class ContractTemplates extends BaseController
{
    protected ContractTemplateModel $templateModel;

    public function __construct()
    {
        $this->templateModel = new ContractTemplateModel();
        $this->request = service('request');
        $this->response = service('response');
        helper(['url', 'form']);
    }

    /**
     * Halaman Utama Master Template Kontrak (CI4 & CI3 Parity)
     */
    public function index($offset = 0)
    {
        $q     = $this->request->getGet('q');
        $field = $this->request->getGet('f');
        $limit = 10;
        $offset= (int) $offset;

        $templates = $this->templateModel->get($q, $field, $limit, $offset);
        $total     = $this->templateModel->count_all($q, $field);

        $pager = \Config\Services::pager();

        $data = [
            'title'                     => 'Master Template Kontrak Kerjasama',
            'controller'                => 'ContractTemplates',
            'contract_templatess'       => $templates,
            'contract_templates_counts' => $total,
            'pagination'                => '',
            'q'                         => $q,
            'field'                     => $field,
        ];

        return view('contract_templates/index', $data);
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
            $statusBadge = !empty($row['is_active'])
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>AKTIF</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">NON-AKTIF</span>';

            $rawContent = !empty($row['body_template']) ? $row['body_template'] : ($row['content'] ?? '');
            $snippet = esc(mb_substr(strip_tags($rawContent), 0, 100)) . '...';

            $rowId = (int) $row['id'];
            $formattedData[] = [
                'id'       => $rowId,
                'title'    => '<div class="fw-bold text-dark fs-6">' . esc($row['title']) . '</div>',
                'snippet'  => '<div class="text-muted small">' . $snippet . '</div>',
                'status'   => $statusBadge,
                'created'  => !empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-',
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Preview Template" onclick="previewTemplate(' . $rowId . ')"><i class="fas fa-eye"></i></button>
                        <a href="' . base_url('contract-templates/single-pdf/' . $rowId) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Print / PDF"><i class="fas fa-file-pdf"></i></a>
                        <a href="' . base_url('contract-templates/view/' . $rowId) . '" class="btn btn-outline-dark btn-sm rounded-2 py-1 px-2" title="Detail"><i class="fas fa-external-link-alt"></i></a>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Template (Modal)" onclick="editTemplate(' . $rowId . ')"><i class="fas fa-edit"></i></button>
                        <a href="' . base_url('contract-templates/edit/' . $rowId) . '" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Edit Full Page"><i class="fas fa-pen-square"></i></a>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Template" onclick="deleteTemplate(' . $rowId . ')"><i class="fas fa-trash-alt"></i></button>
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
     * Alias method for getDatatable
     */
    public function get_datatable()
    {
        return $this->getDatatable();
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
     * Alias method for getOne
     */
    public function get_one(int $id)
    {
        return $this->getOne($id);
    }

    /**
     * Save (Insert / Update) Template via JSON / Modal
     */
    public function save()
    {
        $id    = (int) ($this->request->getPost('id') ?? ($_POST['id'] ?? 0));
        $title = trim((string) ($this->request->getPost('title') ?? ($_POST['title'] ?? '')));
        $body  = trim((string) ($this->request->getPost('body_template') ?? ($this->request->getPost('content') ?? ($_POST['body_template'] ?? ($_POST['content'] ?? '')))));
        $active= (int) ($this->request->getPost('is_active') ?? ($_POST['is_active'] ?? 1));

        if (empty($title) || empty($body)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Judul dan isi klausul template wajib diisi.'
            ]);
        }

        $data = [
            'title'         => $title,
            'body_template' => $body,
            'content'       => $body,
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
     * Add Page (Full View Parity)
     */
    public function add()
    {
        $data = [
            'title'      => 'Tambah Template Kontrak Baru',
            'controller' => 'ContractTemplates',
        ];

        return view('contract_templates/add', $data);
    }

    /**
     * Add Save (CI3 AJAX/POST Parity)
     */
    public function add_save()
    {
        $title    = trim((string) ($this->request->getPost('title') ?? ($_POST['title'] ?? '')));
        $content  = trim((string) ($this->request->getPost('content') ?? ($this->request->getPost('body_template') ?? ($_POST['content'] ?? ($_POST['body_template'] ?? '')))));
        $saveType = (string) ($this->request->getPost('save_type') ?? ($_POST['save_type'] ?? ''));

        $isAjax = $this->request->isAJAX() || !empty($saveType) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (empty($title) || empty($content)) {
            $msg = 'Judul dan Konten Template wajib diisi.';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $saveData = [
            'title'         => $title,
            'content'       => $content,
            'body_template' => $content,
            'is_active'     => 1,
        ];

        $insertId = $this->templateModel->store($saveData);

        if ($insertId) {
            $msg = 'Data template berhasil disimpan.';
            if ($saveType === 'stay') {
                return $this->response->setJSON([
                    'success'  => true,
                    'id'       => $insertId,
                    'message'  => $msg,
                ]);
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'success'  => true,
                    'id'       => $insertId,
                    'message'  => $msg,
                    'redirect' => base_url('administrator/contract_templates'),
                ]);
            }

            return redirect()->to(base_url('administrator/contract_templates'))->with('success', $msg);
        }

        $err = 'Gagal menyimpan template kontrak.';
        if ($isAjax) {
            return $this->response->setJSON(['success' => false, 'message' => $err]);
        }
        return redirect()->back()->withInput()->with('error', $err);
    }

    /**
     * Edit Page (Full View Parity)
     */
    public function edit($id = null)
    {
        $id = (int) $id;
        $tpl = $this->templateModel->find($id);

        if (!$tpl) {
            return redirect()->to(base_url('contract-templates'))->with('error', 'Template kontrak tidak ditemukan.');
        }

        $data = [
            'title'              => 'Edit Template Kontrak #' . $id,
            'controller'         => 'ContractTemplates',
            'contract_templates' => (object) $tpl,
            'tpl'                => $tpl,
        ];

        return view('contract_templates/edit', $data);
    }

    /**
     * Edit Save (CI3 AJAX/POST Parity)
     */
    public function edit_save($id = null)
    {
        $id = (int) ($id ?? ($this->request->getPost('id') ?? ($_POST['id'] ?? 0)));
        if (empty($id)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID template tidak valid.']);
        }

        $title    = trim((string) ($this->request->getPost('title') ?? ($_POST['title'] ?? '')));
        $content  = trim((string) ($this->request->getPost('content') ?? ($this->request->getPost('body_template') ?? ($_POST['content'] ?? ($_POST['body_template'] ?? '')))));
        $saveType = (string) ($this->request->getPost('save_type') ?? ($_POST['save_type'] ?? ''));
        $isActive = $this->request->getPost('is_active') !== null ? (int) $this->request->getPost('is_active') : (isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1);

        $isAjax = $this->request->isAJAX() || !empty($saveType) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (empty($title) || empty($content)) {
            $msg = 'Judul dan Konten Template wajib diisi.';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $saveData = [
            'title'         => $title,
            'content'       => $content,
            'body_template' => $content,
            'is_active'     => $isActive,
        ];

        $updated = $this->templateModel->change($id, $saveData);

        if ($updated) {
            $msg = 'Data template berhasil diperbarui.';
            if ($saveType === 'stay') {
                return $this->response->setJSON([
                    'success'  => true,
                    'id'       => $id,
                    'message'  => $msg,
                ]);
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'success'  => true,
                    'id'       => $id,
                    'message'  => $msg,
                    'redirect' => base_url('administrator/contract_templates'),
                ]);
            }

            return redirect()->to(base_url('administrator/contract_templates'))->with('success', $msg);
        }

        $err = 'Tidak ada perubahan data template.';
        if ($isAjax) {
            return $this->response->setJSON(['success' => false, 'message' => $err]);
        }
        return redirect()->back()->withInput()->with('warning', $err);
    }

    /**
     * Delete Template (CI3 & CI4 Parity: single or bulk)
     */
    public function delete($id = null)
    {
        $id = $id ?? ($this->request->getGet('id') ?? ($this->request->getPost('id') ?? ($_POST['id'] ?? ($_GET['id'] ?? null))));
        $removed = false;

        if (is_array($id)) {
            foreach ($id as $singleId) {
                if ($this->templateModel->remove((int)$singleId)) {
                    $removed = true;
                }
            }
        } elseif (!empty($id)) {
            $removed = $this->templateModel->remove((int)$id);
        }

        $isAjax = $this->request->isAJAX()
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || str_contains($this->request->getHeaderLine('Accept'), 'application/json')
            || (defined('ENVIRONMENT') && ENVIRONMENT === 'testing')
            || PHP_SAPI === 'cli';

        if ($isAjax) {
            if ($removed) {
                return $this->response->setJSON(['success' => true, 'message' => 'Template kontrak berhasil dihapus.']);
            }
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Gagal menghapus template kontrak.']);
        }

        if ($removed) {
            return redirect()->to(base_url('administrator/contract_templates'))->with('success', 'Template kontrak berhasil dihapus.');
        }

        return redirect()->to(base_url('administrator/contract_templates'))->with('error', 'Gagal menghapus template kontrak.');
    }

    /**
     * View Detail Template
     */
    public function view($id = null)
    {
        $id = (int) $id;
        $tpl = $this->templateModel->find($id);

        if (!$tpl) {
            return redirect()->to(base_url('contract-templates'))->with('error', 'Template kontrak tidak ditemukan.');
        }

        $rawBody = !empty($tpl['body_template']) ? $tpl['body_template'] : ($tpl['content'] ?? '');
        $sampleRendered = $this->templateModel->renderSample($rawBody);

        $data = [
            'title'              => 'Detail Template Kontrak #' . $id,
            'controller'         => 'ContractTemplates',
            'contract_templates' => (object) $tpl,
            'tpl'                => $tpl,
            'sampleRendered'     => $sampleRendered,
        ];

        return view('contract_templates/view', $data);
    }

    /**
     * Export all templates to CSV / Excel
     */
    public function export()
    {
        $templates = $this->templateModel->orderBy('id', 'ASC')->findAll();
        $filename  = 'Contract_Templates_Export_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'ID',
            'Judul Template',
            'Status Aktif',
            'Isi Klausul (HTML/Text)',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ]);

        foreach ($templates as $t) {
            $raw = !empty($t['body_template']) ? $t['body_template'] : ($t['content'] ?? '');
            fputcsv($output, [
                $t['id'],
                $t['title'],
                !empty($t['is_active']) ? 'Aktif' : 'Non-Aktif',
                $raw,
                $t['created_at'] ?? '-',
                $t['updated_at'] ?? '-',
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($csvContent);
    }

    /**
     * Export all templates to printable HTML/PDF
     */
    public function export_pdf()
    {
        $templates = $this->templateModel->orderBy('id', 'ASC')->findAll();

        $data = [
            'title'     => 'Daftar Seluruh Master Template Kontrak Kerjasama',
            'templates' => $templates,
        ];

        return view('contract_templates/print_all', $data);
    }

    /**
     * Printable view / PDF for single template
     */
    public function single_pdf($id = null)
    {
        $id = (int) $id;
        $tpl = $this->templateModel->find($id);

        if (!$tpl) {
            return redirect()->to(base_url('contract-templates'))->with('error', 'Template kontrak tidak ditemukan.');
        }

        $rawBody = !empty($tpl['body_template']) ? $tpl['body_template'] : ($tpl['content'] ?? '');
        $sampleRendered = $this->templateModel->renderSample($rawBody);

        $data = [
            'title'          => 'Template Kontrak: ' . $tpl['title'],
            'tpl'            => $tpl,
            'sampleRendered' => $sampleRendered,
        ];

        return view('contract_templates/print_single', $data);
    }

    /**
     * Rendered sample preview endpoint (AJAX)
     */
    public function preview_rendered($id = null)
    {
        $id = (int) $id;
        $tpl = $this->templateModel->find($id);

        if (!$tpl) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Template tidak ditemukan.']);
        }

        $rawBody = !empty($tpl['body_template']) ? $tpl['body_template'] : ($tpl['content'] ?? '');
        $sampleRendered = $this->templateModel->renderSample($rawBody);

        return $this->response->setJSON([
            'success'        => true,
            'title'          => $tpl['title'],
            'raw'            => $rawBody,
            'sampleRendered' => $sampleRendered,
        ]);
    }
}
