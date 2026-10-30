<?php

namespace App\Models;

use CodeIgniter\Model;

class DraftReviewHistoryModel extends Model
{
    protected $table            = 'draft_review_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'draft_id',
        'action',
        'acted_by',
        'note',
        'reviewed_by',
        'status',
        'timestamp_notes',
        'notes',
        'created_at',
    ];

    protected $useTimestamps = false;
}
