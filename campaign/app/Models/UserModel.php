<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'email',
        'password_hash',
        'phone',
        'avatar',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'phone'    => 'permit_empty|min_length[8]|max_length[20]',
        'status'   => 'in_list[active,inactive,banned]',
    ];

    /**
     * Get user along with assigned roles
     */
    public function getUserWithRoles(int $userId): ?array
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('user_roles')
            ->select('roles.id, roles.name, roles.label')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId);

        $roles = $builder->get()->getResultArray();
        $user['roles'] = array_column($roles, 'name');
        $user['role_details'] = $roles;

        return $user;
    }

    /**
     * Get DataTables list with joined roles
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'users.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                users.id,
                users.name,
                users.email,
                users.phone,
                users.avatar,
                users.status,
                users.created_at,
                roles.id as role_id,
                roles.name as role_name,
                roles.label as role_label
            ')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left')
            ->where('users.deleted_at IS NULL');

        if (!empty($filters['role_id'])) {
            $builder->where('roles.id', (int)$filters['role_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('users.status', $filters['status']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('users.name', $search)
                ->orLike('users.email', $search)
                ->orLike('users.phone', $search)
                ->orLike('roles.label', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->where('deleted_at IS NULL')->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Attempt login authentication
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->where('email', $email)->where('status', 'active')->first();
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $this->getUserWithRoles((int) $user['id']);
    }
}
