<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskSentimentModel extends Model
{
    protected $table            = 'task_sentiments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'task_id',
        'positive_count',
        'neutral_count',
        'negative_count',
        'sentiment_score',
        'analyzed_at',
    ];

    protected $useTimestamps = false;
}
