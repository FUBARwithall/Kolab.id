<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class AuditLogs extends BaseController
{
    protected AuditLogModel $auditModel;

    public function __construct()
    {
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Halaman Utama Audit Trail & Log Aktivitas (CI3 & CI4 Parity)
     */
    public function index()
    {
        $db = \Config\Database::connect();
        $stats = $this->auditModel->getSummaryStats();

        // Get unique entity types and actions for filter dropdowns
        $entityTypes = $db->table('audit_logs')->distinct()->select('entity_type')->orderBy('entity_type', 'ASC')->get()->getResultArray();
        $actions = $db->table('audit_logs')->distinct()->select('action')->orderBy('action', 'ASC')->get()->getResultArray();
        $users = $db->table('users')->select('id, name, email')->orderBy('name', 'ASC')->get()->getResultArray();

        $data = [
            'title'       => 'Audit Trail & Log Aktivitas Sistem',
            'controller'  => 'AuditLogs',
            'stats'       => $stats,
            'entityTypes' => array_column($entityTypes, 'entity_type'),
            'actions'     => array_column($actions, 'action'),
            'users'       => $users,
        ];

        return view('audit_logs/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        $draw   = (int) ($this->request->getVar('draw') ?? 1);
        $start  = (int) ($this->request->getVar('start') ?? 0);
        $length = (int) ($this->request->getVar('length') ?? 10);
        $search = (string) ($this->request->getVar('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getVar('order')[0]['column'] ?? 1);
        $orderDir    = strtoupper((string) ($this->request->getVar('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'audit_logs.id', // Checkbox
            1 => 'audit_logs.id',
            2 => 'audit_logs.created_at',
            3 => 'users.name',
            4 => 'audit_logs.action',
            5 => 'audit_logs.entity_type',
            6 => 'audit_logs.ip_address',
            7 => 'audit_logs.id', // Actions
        ];

        $orderCol = $columns[$orderColIdx] ?? 'audit_logs.id';

        $filters = [
            'action'      => $this->request->getVar('action') ?: null,
            'entity_type' => $this->request->getVar('entity_type') ?: null,
            'user_id'     => $this->request->getVar('user_id') ?: null,
        ];

        $result = $this->auditModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            // Action badge
            switch ($row['action']) {
                case 'CREATE':
                    $actBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-plus me-1"></i>CREATE</span>';
                    break;
                case 'UPDATE':
                    $actBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-edit me-1"></i>UPDATE</span>';
                    break;
                case 'DELETE':
                    $actBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-trash-alt me-1"></i>DELETE</span>';
                    break;
                case 'APPROVE':
                    $actBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>APPROVE</span>';
                    break;
                case 'REJECT':
                    $actBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times-circle me-1"></i>REJECT</span>';
                    break;
                case 'VERIFY':
                    $actBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-shield-alt me-1"></i>VERIFY</span>';
                    break;
                case 'PAYMENT':
                    $actBadge = '<span class="badge bg-dark-subtle text-dark border border-dark-subtle px-2 py-1"><i class="fas fa-money-bill-wave me-1"></i>PAYMENT</span>';
                    break;
                case 'LOGIN':
                    $actBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-sign-in-alt me-1"></i>LOGIN</span>';
                    break;
                case 'LOGOUT':
                    $actBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-sign-out-alt me-1"></i>LOGOUT</span>';
                    break;
                default:
                    $actBadge = '<span class="badge bg-light text-dark border px-2 py-1">' . esc($row['action']) . '</span>';
                    break;
            }

            // Role badge
            $roleLabel = esc($row['role_label'] ?? 'Operator');
            $roleColor = 'secondary';
            if (stripos($row['role_name'] ?? '', 'admin') !== false) $roleColor = 'danger';
            elseif (stripos($row['role_name'] ?? '', 'leader') !== false) $roleColor = 'primary';
            elseif (stripos($row['role_name'] ?? '', 'staff') !== false) $roleColor = 'info';
            elseif (stripos($row['role_name'] ?? '', 'kol') !== false) $roleColor = 'purple';

            $userDisplay = '
                <div class="fw-semibold text-dark" style="font-size: 13.5px;">' . esc($row['user_name'] ?? 'System / Automated') . '</div>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <span class="badge bg-' . $roleColor . '-subtle text-' . $roleColor . ' border border-' . $roleColor . '-subtle" style="font-size: 10px;">' . $roleLabel . '</span>
                    <small class="text-muted">' . esc($row['user_email'] ?? '-') . '</small>
                </div>
            ';

            // Entity display
            $entityDisplay = '
                <div class="font-monospace fw-semibold text-dark text-uppercase">' . esc($row['entity_type']) . ' #' . $row['entity_id'] . '</div>
            ';

            // Client Info
            $ipDisplay = '
                <div class="font-monospace text-dark small"><i class="fas fa-network-wired text-muted me-1"></i>' . esc($row['ip_address'] ?? '127.0.0.1') . '</div>
                <small class="text-muted text-truncate d-inline-block" style="max-width: 180px;" title="' . esc($row['user_agent'] ?? '') . '">' . esc($row['user_agent'] ?? '-') . '</small>
            ';

            // Actions: Diff, View, Print, Delete
            $hasDiff = !empty($row['old_values']) || !empty($row['new_values']);
            $actions = '
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Lihat Perubahan (Diff)" onclick="viewAuditDiff(' . $row['id'] . ')" ' . (!$hasDiff ? 'disabled' : '') . '>
                        <i class="fas fa-columns"></i>
                    </button>
                    <a href="' . base_url('audit-logs/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Lihat Detail Lengkap">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . base_url('audit-logs/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak Lembar Audit PDF">
                        <i class="fas fa-print"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Log" onclick="deleteAuditLog(' . $row['id'] . ')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'checkbox'    => '<input type="checkbox" class="form-check-input check-audit-item" value="' . $row['id'] . '">',
                'id'          => '<span class="font-monospace text-muted fw-bold">#' . $row['id'] . '</span>',
                'created_at'  => '
                    <div class="fw-semibold text-dark">' . date('d M Y', strtotime($row['created_at'])) . '</div>
                    <small class="text-muted font-monospace">' . date('H:i:s', strtotime($row['created_at'])) . ' WIB</small>
                ',
                'user'        => $userDisplay,
                'action_type' => $actBadge,
                'entity'      => $entityDisplay,
                'client'      => $ipDisplay,
                'action'      => $actions,
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
     * Get single log details for modal diff viewer
     */
    public function getOne(int $id)
    {
        $log = $this->auditModel->getDetail($id);

        if (!$log) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Log audit tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $log]);
    }

    /**
     * Dedicated detail page for an audit log (Full CI3 Parity)
     */
    public function view(int $id)
    {
        $log = $this->auditModel->getDetail($id);
        if (!$log) {
            return redirect()->to(base_url('audit-logs'))->with('error', 'Log audit #' . $id . ' tidak ditemukan');
        }

        $formattedOld = null;
        if (!empty($log['old_values'])) {
            $decOld = json_decode($log['old_values'], true);
            $formattedOld = $decOld ? json_encode($decOld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $log['old_values'];
        }

        $formattedNew = null;
        if (!empty($log['new_values'])) {
            $decNew = json_decode($log['new_values'], true);
            $formattedNew = $decNew ? json_encode($decNew, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $log['new_values'];
        }

        $data = [
            'title'        => 'Detail Audit Log #' . $id,
            'controller'   => 'AuditLogs',
            'log'          => $log,
            'formattedOld' => $formattedOld,
            'formattedNew' => $formattedNew,
        ];

        return view('audit_logs/view', $data);
    }

    /**
     * Delete single or multiple audit logs (CI3 Parity)
     */
    public function delete($id = null)
    {
        $arrId = $this->request->getGet('id') ?: ($this->request->getPost('id') ?: null);

        if (!empty($id)) {
            $this->auditModel->delete((int)$id);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Log audit #' . $id . ' berhasil dihapus.']);
            }
            return redirect()->to(base_url('audit-logs'))->with('success', 'Log audit #' . $id . ' berhasil dihapus.');
        }

        if (is_array($arrId) && count($arrId) > 0) {
            foreach ($arrId as $singleId) {
                $this->auditModel->delete((int)$singleId);
            }
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => count($arrId) . ' log audit berhasil dihapus.']);
            }
            return redirect()->to(base_url('audit-logs'))->with('success', count($arrId) . ' log audit berhasil dihapus.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tidak ada log audit yang dipilih untuk dihapus.']);
        }
        return redirect()->to(base_url('audit-logs'))->with('error', 'Tidak ada log audit yang dipilih untuk dihapus.');
    }

    /**
     * Bulk delete audit logs via AJAX
     */
    public function bulkDelete()
    {
        $ids = $this->request->getPost('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih setidaknya satu log audit untuk dihapus']);
        }

        $cleanIds = array_map('intval', $ids);
        $this->auditModel->whereIn('id', $cleanIds)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => count($cleanIds) . ' log audit berhasil dihapus',
        ]);
    }

    /**
     * Export audit logs to CSV (CI3 Parity with XLS/CSV)
     */
    public function export()
    {
        $filters = [
            'action'      => $this->request->getGet('action') ?: null,
            'entity_type' => $this->request->getGet('entity_type') ?: null,
            'user_id'     => $this->request->getGet('user_id') ?: null,
        ];

        $logs = $this->auditModel->getAllForExport($filters);

        $filename = 'audit_logs_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Log ID',
            'Timestamp',
            'User ID',
            'Operator Name',
            'Operator Email',
            'Role',
            'Action',
            'Entity Type',
            'Entity ID',
            'IP Address',
            'User Agent',
            'Old Values',
            'New Values',
        ]);

        foreach ($logs as $l) {
            fputcsv($output, [
                $l['id'],
                $l['created_at'],
                $l['user_id'] ?? '',
                $l['user_name'] ?? 'System',
                $l['user_email'] ?? '',
                $l['role_label'] ?? '',
                $l['action'],
                $l['entity_type'],
                $l['entity_id'],
                $l['ip_address'] ?? '',
                $l['user_agent'] ?? '',
                $l['old_values'] ?? '',
                $l['new_values'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export PDF Rekapitulasi Tabel Audit Logs
     */
    public function export_pdf()
    {
        $filters = [
            'action'      => $this->request->getGet('action') ?: null,
            'entity_type' => $this->request->getGet('entity_type') ?: null,
            'user_id'     => $this->request->getGet('user_id') ?: null,
        ];

        $items = $this->auditModel->getAllForExport($filters);

        return view('audit_logs/export_pdf', [
            'items'     => $items,
            'title'     => 'Laporan Audit Trail & Rekam Jejak Sistem',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => $filters,
        ]);
    }

    /**
     * Cetak Lembar Single PDF Bukti Audit Log Record
     */
    public function single_pdf($id = null)
    {
        $id = (int) $id;
        $log = $this->auditModel->getDetail($id);

        if (!$log) {
            return redirect()->to(base_url('audit-logs'))->with('error', 'Log audit #' . $id . ' tidak ditemukan.');
        }

        return view('audit_logs/single_pdf', [
            'log'       => $log,
            'title'     => 'Lembar Audit Log Record #' . $log['id'],
            'generated' => date('d F Y, H:i:s'),
        ]);
    }
}
