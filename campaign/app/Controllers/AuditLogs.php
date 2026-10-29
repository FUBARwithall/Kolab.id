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
     * Halaman Utama Audit Trail & Log Aktivitas
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
            0 => 'audit_logs.id',
            1 => 'audit_logs.created_at',
            2 => 'users.name',
            3 => 'audit_logs.action',
            4 => 'audit_logs.entity_type',
            5 => 'audit_logs.ip_address',
            6 => 'audit_logs.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'audit_logs.id';

        $filters = [
            'action'      => $this->request->getPost('action') ?: null,
            'entity_type' => $this->request->getPost('entity_type') ?: null,
            'user_id'     => $this->request->getPost('user_id') ?: null,
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
                default:
                    $actBadge = '<span class="badge bg-light text-dark border px-2 py-1">' . esc($row['action']) . '</span>';
                    break;
            }

            // Role badge
            $roleLabel = esc($row['role_label'] ?? 'User');
            $roleColor = 'secondary';
            if (($row['role_name'] ?? '') === 'admin') $roleColor = 'danger';
            elseif (($row['role_name'] ?? '') === 'leader') $roleColor = 'primary';
            elseif (($row['role_name'] ?? '') === 'staff') $roleColor = 'info';
            elseif (($row['role_name'] ?? '') === 'kol') $roleColor = 'purple';

            $userDisplay = '
                <div class="fw-semibold text-dark" style="font-size: 13.5px;">' . esc($row['user_name'] ?? 'System') . '</div>
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

            // Action
            $hasDiff = !empty($row['old_values']) || !empty($row['new_values']);
            $actions = '
                <div class="text-center">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Lihat Perubahan Data (Diff)" onclick="viewAuditDiff(' . $row['id'] . ')" ' . (!$hasDiff ? 'disabled' : '') . '>
                        <i class="fas fa-columns me-1"></i> Diff
                    </button>
                </div>
            ';

            $formattedData[] = [
                'id'          => '<span class="font-monospace text-muted">#' . $row['id'] . '</span>',
                'created_at'  => '
                    <div class="fw-semibold text-dark">' . date('d M Y', strtotime($row['created_at'])) . '</div>
                    <small class="text-muted font-monospace">' . date('H:i:s', strtotime($row['created_at'])) . '</small>
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
     * Get single log details for diff modal
     */
    public function getOne(int $id)
    {
        $db = \Config\Database::connect();
        $log = $db->table('audit_logs')
            ->select('audit_logs.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.id', $id)
            ->get()
            ->getRowArray();

        if (!$log) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Log tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $log]);
    }
}
