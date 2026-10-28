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
     * Get published articles
     */
    public function getPublished(int $limit = 10, int $offset = 0, ?string $category = null): array
    {
        $builder = $this->where('status', 'published')
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
            ->where('status', 'published')
            ->first();
    }
}
