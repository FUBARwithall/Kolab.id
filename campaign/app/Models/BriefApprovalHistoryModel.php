<?php

namespace App\Models;

use CodeIgniter\Model;

class BriefApprovalHistoryModel extends Model
{
    protected $table            = 'brief_approval_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'brief_id',
        'reviewed_by',
        'status',
        'notes',
        'created_at',
    ];

    protected $useTimestamps = false;
}
