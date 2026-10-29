<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditLogModel;

class Users extends BaseController
{
    protected UserModel $userModel;
    protected AuditLogModel $auditModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditModel = new AuditLogModel();
    }

    /**
     * Halaman Utama Manajemen Tim & Pengguna Sistem
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $roles = $db->table('roles')->orderBy('id', 'ASC')->get()->getResultArray();

        // Calculate summary stats
        $totalUsers = $db->table('users')->where('deleted_at IS NULL')->countAllResults();
        $totalAdmins = $db->table('user_roles')->where('role_id', 1)->countAllResults();
        $totalLeaders = $db->table('user_roles')->where('role_id', 2)->countAllResults();
        $totalStaff = $db->table('user_roles')->where('role_id', 3)->countAllResults();
        $totalKols = $db->table('user_roles')->where('role_id', 4)->countAllResults();

        $data = [
            'title'      => 'Manajemen Tim & Pengguna Sistem',
            'controller' => 'Users',
            'roles'      => $roles,
            'stats'      => [
                'total'   => $totalUsers,
                'admins'  => $totalAdmins,
                'leaders' => $totalLeaders,
                'staff'   => $totalStaff,
                'kols'    => $totalKols,
            ],
        ];

        return view('users/index', $data);
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
            0 => 'users.id',
            1 => 'users.name',
            2 => 'roles.id',
            3 => 'users.phone',
            4 => 'users.status',
            5 => 'users.created_at',
            6 => 'users.id',
        ];

        $orderCol = $columns[$orderColIdx] ?? 'users.id';

        $filters = [
            'role_id' => $this->request->getPost('role_id') ?: null,
            'status'  => $this->request->getPost('status') ?: null,
        ];

        $result = $this->userModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            // Status badge
            switch ($row['status']) {
                case 'active':
                    $statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>AKTIF</span>';
                    break;
                case 'suspended':
                    $statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>DIBEKUKAN</span>';
                    break;
                case 'pending':
                default:
                    $statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>PENDING</span>';
                    break;
            }

            // Role badge
            $roleColor = 'secondary';
            if ($row['role_name'] === 'admin') $roleColor = 'danger';
            elseif ($row['role_name'] === 'leader') $roleColor = 'primary';
            elseif ($row['role_name'] === 'staff') $roleColor = 'info';
            elseif ($row['role_name'] === 'kol') $roleColor = 'purple';

            $roleBadge = '<span class="badge bg-' . $roleColor . '-subtle text-' . $roleColor . ' border border-' . $roleColor . '-subtle px-2 py-1 font-monospace">' . esc($row['role_label'] ?? 'User') . '</span>';

            // User Info
            $initial = strtoupper(substr($row['name'], 0, 1));
            $avatarHtml = !empty($row['avatar'])
                ? '<img src="' . esc($row['avatar']) . '" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover;">'
                : '<div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; font-size: 14px;">' . $initial . '</div>';

            $userInfo = '
                <div class="d-flex align-items-center">
                    ' . $avatarHtml . '
                    <div>
                        <div class="fw-semibold text-dark" style="font-size: 13.5px;">' . esc($row['name']) . '</div>
                        <small class="text-muted">' . esc($row['email']) . '</small>
                    </div>
                </div>
            ';

            // Actions
            $actions = '
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Akun" onclick="editUser(' . $row['id'] . ')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-' . ($row['status'] === 'active' ? 'danger' : 'success') . ' btn-sm rounded-2 py-1 px-2" title="' . ($row['status'] === 'active' ? 'Bekukan Akun' : 'Aktifkan Akun') . '" onclick="toggleUserStatus(' . $row['id'] . ', \'' . $row['status'] . '\')">
                        <i class="fas fa-' . ($row['status'] === 'active' ? 'ban' : 'check') . '"></i>
                    </button>
                </div>
            ';

            $formattedData[] = [
                'id'         => '<span class="font-monospace text-muted">#' . $row['id'] . '</span>',
                'user'       => $userInfo,
                'role'       => $roleBadge,
                'phone'      => '<span class="font-monospace text-dark small">' . esc($row['phone'] ?: '-') . '</span>',
                'status'     => $statusBadge,
                'created_at' => '<span class="text-muted small">' . ($row['created_at'] ? date('d M Y', strtotime($row['created_at'])) : '-') . '</span>',
                'action'     => $actions,
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
     * Get single user
     */
    public function getOne(int $id)
    {
        $db = \Config\Database::connect();
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pengguna tidak ditemukan']);
        }

        // Get role_id
        $ur = $db->table('user_roles')->where('user_id', $id)->get()->getRowArray();
        $user['role_id'] = $ur['role_id'] ?? null;
        unset($user['password_hash']);

        return $this->response->setJSON(['success' => true, 'data' => $user]);
    }

    /**
     * Save (Create or Update) User
     */
    public function save()
    {
        $id       = (int)$this->request->getPost('id');
        $name     = trim((string)$this->request->getPost('name'));
        $email    = trim((string)$this->request->getPost('email'));
        $phone    = trim((string)$this->request->getPost('phone'));
        $roleId   = (int)$this->request->getPost('role_id');
        $status   = $this->request->getPost('status') ?? 'active';
        $password = trim((string)$this->request->getPost('password'));

        if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Nama dan email valid wajib diisi']);
        }

        $db = \Config\Database::connect();

        // Check email duplicate
        $existing = $this->userModel->where('email', $email)->where('deleted_at IS NULL');
        if ($id > 0) {
            $existing->where('id !=', $id);
        }
        if ($existing->first()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Email sudah digunakan pengguna lain']);
        }

        $userData = [
            'name'   => $name,
            'email'  => $email,
            'phone'  => $phone,
            'status' => in_array($status, ['active', 'suspended', 'pending']) ? $status : 'active',
        ];

        if (!empty($password)) {
            $userData['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($id > 0) {
            $oldUser = $this->userModel->find($id);
            $this->userModel->update($id, $userData);

            // Update role
            if ($roleId > 0) {
                $db->table('user_roles')->where('user_id', $id)->delete();
                $db->table('user_roles')->insert(['user_id' => $id, 'role_id' => $roleId]);
            }

            // Log Audit
            $this->auditModel->record('UPDATE', 'user', $id, $oldUser, $userData);

            return $this->response->setJSON(['success' => true, 'message' => 'Data pengguna berhasil diperbarui!']);
        } else {
            if (empty($password)) {
                $userData['password_hash'] = password_hash('password123', PASSWORD_BCRYPT);
            }
            $userId = $this->userModel->insert($userData, true);

            // Assign role
            if ($roleId > 0) {
                $db->table('user_roles')->insert(['user_id' => $userId, 'role_id' => $roleId]);
            }

            // Log Audit
            $this->auditModel->record('CREATE', 'user', $userId, null, $userData);

            return $this->response->setJSON(['success' => true, 'message' => 'Pengguna baru berhasil ditambahkan!']);
        }
    }

    /**
     * Toggle status between active and suspended
     */
    public function toggleStatus(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pengguna tidak ditemukan']);
        }

        if ($id === 1) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Akun Super Administrator utama tidak dapat dibekukan']);
        }

        $newStatus = ($user['status'] === 'active') ? 'suspended' : 'active';
        $this->userModel->update($id, ['status' => $newStatus]);

        // Log Audit
        $this->auditModel->record('UPDATE', 'user', $id, ['status' => $user['status']], ['status' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status pengguna berhasil diubah menjadi: ' . strtoupper($newStatus),
        ]);
    }
}
