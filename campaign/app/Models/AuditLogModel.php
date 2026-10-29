<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * DataTables query with joins to user and roles
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'audit_logs.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                audit_logs.*,
                users.name as user_name,
                users.email as user_email,
                roles.name as role_name,
                roles.label as role_label
            ')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->join('user_roles', 'user_roles.user_id = users.id', 'left')
            ->join('roles', 'roles.id = user_roles.role_id', 'left');

        if (!empty($filters['action'])) {
            $builder->where('audit_logs.action', $filters['action']);
        }

        if (!empty($filters['entity_type'])) {
            $builder->where('audit_logs.entity_type', $filters['entity_type']);
        }

        if (!empty($filters['user_id'])) {
            $builder->where('audit_logs.user_id', (int)$filters['user_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('audit_logs.action', $search)
                ->orLike('audit_logs.entity_type', $search)
                ->orLike('audit_logs.ip_address', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->db->table($this->table)->countAllResults();

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Get aggregate statistics for audit dashboard
     */
    public function getSummaryStats(): array
    {
        $db = $this->db;
        $total       = $db->table($this->table)->countAllResults();
        $today       = $db->table($this->table)->where('created_at >=', date('Y-m-d 00:00:00'))->countAllResults();
        $approvals   = $db->table($this->table)->whereIn('action', ['APPROVE', 'VERIFY'])->countAllResults();
        $uniqueUsers = $db->table($this->table)->select('COUNT(DISTINCT user_id) as cnt')->get()->getRowArray()['cnt'] ?? 0;

        return [
            'total'        => $total,
            'today'        => $today,
            'approvals'    => $approvals,
            'unique_users' => (int)$uniqueUsers,
        ];
    }

    /**
     * System-wide helper to log user/system activity
     */
    public function record(string $action, string $entityType, int $entityId, $oldValues = null, $newValues = null, ?int $userId = null): int
    {
        $request = service('request');
        $session = session();

        $uid = $userId ?? ($session->get('user_id') ?? 1);

        $data = [
            'user_id'     => $uid,
            'action'      => strtoupper($action),
            'entity_type' => strtolower($entityType),
            'entity_id'   => $entityId,
            'old_values'  => is_array($oldValues) ? json_encode($oldValues) : $oldValues,
            'new_values'  => is_array($newValues) ? json_encode($newValues) : $newValues,
            'ip_address'  => $request ? $request->getIPAddress() : '127.0.0.1',
            'user_agent'  => $request ? (string)$request->getUserAgent() : 'CLI/Internal',
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return (int)$this->insert($data, true);
    }
}
