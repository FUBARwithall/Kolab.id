<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table            = 'articles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'category',
        'status',
        'author_id',
        'published_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get published articles for API / Public landing
     */
    public function getPublished(int $limit = 10, int $offset = 0, ?string $category = null): array
    {
        $builder = $this->whereIn('status', ['published', 'PUBLISHED'])
            ->orderBy('published_at', 'DESC');

        if ($category) {
            $builder->where('category', $category);
        }

        return $builder->findAll($limit, $offset);
    }

    /**
     * Get single article by slug
     */
    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)
            ->whereIn('status', ['published', 'PUBLISHED'])
            ->first();
    }

    /**
     * DataTables query for backoffice CMS
     */
    public function getDatatables(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'articles.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('articles.*, users.name as author_name')
            ->join('users', 'users.id = articles.author_id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('articles.title', $search)
                ->orLike('articles.category', $search)
                ->orLike('articles.status', $search)
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
}
