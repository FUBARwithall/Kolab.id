<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Hybrid Item for Article records allowing both Object ($item->prop) and Array ($item['prop']) access
 */
class ArticleItem implements \ArrayAccess, \JsonSerializable
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __unset($key): void
    {
        unset($this->attributes[$key]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}

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
        'cover_image',
        'featured_image',
        'category',
        'author_name',
        'tags',
        'is_featured',
        'views',
        'meta_title',
        'meta_description',
        'status',
        'author_id',
        'published_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert = ['syncTwinImages'];
    protected $beforeUpdate = ['syncTwinImages'];

    protected array $fieldSearch = ['title', 'slug', 'excerpt', 'category', 'tags', 'author_name'];

    /**
     * Synchronize twin image columns (cover_image <-> featured_image)
     */
    protected function syncTwinImages(array $data): array
    {
        if (isset($data['data'])) {
            if (isset($data['data']['cover_image']) && !isset($data['data']['featured_image'])) {
                $data['data']['featured_image'] = $data['data']['cover_image'];
            } elseif (isset($data['data']['featured_image']) && !isset($data['data']['cover_image'])) {
                $data['data']['cover_image'] = $data['data']['featured_image'];
            } elseif (isset($data['data']['cover_image']) && isset($data['data']['featured_image'])) {
                if (empty($data['data']['featured_image']) && !empty($data['data']['cover_image'])) {
                    $data['data']['featured_image'] = $data['data']['cover_image'];
                } elseif (empty($data['data']['cover_image']) && !empty($data['data']['featured_image'])) {
                    $data['data']['cover_image'] = $data['data']['featured_image'];
                }
            }
        }
        return $data;
    }

    /**
     * Wrap raw row array in ArticleItem
     */
    public function wrapItem(?array $row): ?ArticleItem
    {
        if (!$row) {
            return null;
        }

        // Mirror twin images
        if (empty($row['cover_image']) && !empty($row['featured_image'])) {
            $row['cover_image'] = $row['featured_image'];
        } elseif (empty($row['featured_image']) && !empty($row['cover_image'])) {
            $row['featured_image'] = $row['cover_image'];
        }

        return new ArticleItem($row);
    }

    /**
     * Find single article returning ArticleItem
     */
    public function findItem($id): ?ArticleItem
    {
        $row = $this->find($id);
        return $this->wrapItem($row);
    }

    /**
     * Legacy CI3 count_all
     */
    public function count_all($q = null, $field = null, $status = null, $category = null): int
    {
        $builder = $this->db->table($this->table);
        $this->applyFilters($builder, $q, $field, $status, $category);
        return $builder->countAllResults();
    }

    /**
     * Legacy CI3 get
     */
    public function get($q = null, $field = null, $limit = 0, $offset = 0, $status = null, $category = null): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('articles.*');
        $this->applyFilters($builder, $q, $field, $status, $category);
        $builder->orderBy('articles.created_at', 'DESC');

        if ($limit > 0) {
            $builder->limit((int) $limit, (int) $offset);
        }

        $rows = $builder->get()->getResultArray();
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->wrapItem($row);
        }
        return $items;
    }

    /**
     * Check if slug exists (ignoring specified ID if given)
     */
    public function slug_exists(string $slug, $ignore_id = null): bool
    {
        $builder = $this->db->table($this->table)->where('slug', $slug);
        if ($ignore_id !== null && $ignore_id !== '') {
            $builder->where('id !=', (int) $ignore_id);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Get distinct categories list as array of objects with property ->category
     */
    public function categories(): array
    {
        $rows = $this->db->table($this->table)
            ->select('category')
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->get()
            ->getResultArray();

        $unique = [];
        foreach ($rows as $row) {
            $parts = explode(',', (string) ($row['category'] ?? ''));
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '' && !isset($unique[$p])) {
                    $unique[$p] = new ArticleItem(['category' => $p]);
                }
            }
        }

        ksort($unique);
        return array_values($unique);
    }

    /**
     * Legacy store
     */
    public function store(array $data)
    {
        return $this->insert($data, true);
    }

    /**
     * Legacy change
     */
    public function change($id, array $data): bool
    {
        return (bool) $this->update($id, $data);
    }

    /**
     * Legacy remove
     */
    public function remove($id): bool
    {
        return (bool) $this->delete($id);
    }

    /**
     * Increment views
     */
    public function incrementViews(int $id): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('id', $id)
            ->increment('views', 1);
    }

    /**
     * DataTables query for backoffice CMS
     */
    public function getDatatables(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'articles.id', string $orderDir = 'DESC', ?string $status = null, ?string $category = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('articles.*, users.name as author_user_name')
            ->join('users', 'users.id = articles.author_id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('articles.title', $search)
                ->orLike('articles.slug', $search)
                ->orLike('articles.category', $search)
                ->orLike('articles.tags', $search)
                ->orLike('articles.author_name', $search)
                ->groupEnd();
        }

        if (!empty($status)) {
            $builder->where('articles.status', strtoupper($status));
        }

        if (!empty($category)) {
            $builder->groupStart()
                ->where("FIND_IN_SET(" . $this->db->escape($category) . ", REPLACE(articles.category, ', ', ',')) > 0", null, false)
                ->orLike('articles.category', $category)
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
     * Get published articles for API / Public landing
     */
    public function getPublished(int $limit = 10, int $offset = 0, ?string $category = null, ?string $search = null): array
    {
        $now = date('Y-m-d H:i:s');
        $builder = $this->db->table($this->table)
            ->where('status', 'PUBLISHED')
            ->groupStart()
                ->where('published_at IS NULL')
                ->orWhere('published_at <=', $now)
            ->groupEnd();

        if (!empty($category)) {
            $builder->groupStart()
                ->where("FIND_IN_SET(" . $this->db->escape($category) . ", REPLACE(category, ', ', ',')) > 0", null, false)
                ->orLike('category', $category)
                ->groupEnd();
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('excerpt', $search)
                ->orLike('category', $search)
                ->orLike('tags', $search)
                ->groupEnd();
        }

        $builder->orderBy('COALESCE(published_at, created_at)', 'DESC', false);
        $builder->orderBy('id', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get single published article by slug
     */
    public function getBySlug(string $slug): ?array
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->table($this->table)
            ->where('slug', $slug)
            ->where('status', 'PUBLISHED')
            ->groupStart()
                ->where('published_at IS NULL')
                ->orWhere('published_at <=', $now)
            ->groupEnd()
            ->get()
            ->getRowArray();
    }

    /**
     * Helper to apply filtering conditions
     */
    protected function applyFilters($builder, $q = null, $field = null, $status = null, $category = null): void
    {
        $q     = trim((string) $q);
        $field = trim((string) $field);

        if ($q !== '') {
            $fields = $this->fieldSearch;
            if ($field !== '' && in_array($field, $this->fieldSearch, true)) {
                $fields = [$field];
            }

            $builder->groupStart();
            foreach ($fields as $idx => $f) {
                if ($idx === 0) {
                    $builder->like("articles.{$f}", $q);
                } else {
                    $builder->orLike("articles.{$f}", $q);
                }
            }
            $builder->groupEnd();
        }

        if (!empty($status)) {
            $builder->where('articles.status', strtoupper($status));
        }

        if (!empty($category)) {
            $builder->groupStart()
                ->where("FIND_IN_SET(" . $this->db->escape($category) . ", REPLACE(articles.category, ', ', ',')) > 0", null, false)
                ->orLike('articles.category', $category)
                ->groupEnd();
        }
    }
}
