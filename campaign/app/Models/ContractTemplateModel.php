<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractTemplateModel extends Model
{
    protected $table            = 'contract_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'body_template',
        'content',
        'is_active',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $beforeInsert = ['syncTwinColumns'];
    protected $beforeUpdate = ['syncTwinColumns'];
    protected $afterFind    = ['enrichTwinColumns'];

    /**
     * Synchronize twin columns: `body_template` and `content`
     */
    protected function syncTwinColumns(array $data): array
    {
        if (isset($data['data'])) {
            // If content is provided but body_template is empty, mirror it
            if (isset($data['data']['content']) && !isset($data['data']['body_template'])) {
                $data['data']['body_template'] = $data['data']['content'];
            } elseif (isset($data['data']['body_template']) && !isset($data['data']['content'])) {
                $data['data']['content'] = $data['data']['body_template'];
            } elseif (isset($data['data']['content']) && isset($data['data']['body_template'])) {
                if (empty($data['data']['body_template']) && !empty($data['data']['content'])) {
                    $data['data']['body_template'] = $data['data']['content'];
                } elseif (empty($data['data']['content']) && !empty($data['data']['body_template'])) {
                    $data['data']['content'] = $data['data']['body_template'];
                }
            }

            if (!isset($data['data']['is_active'])) {
                $data['data']['is_active'] = 1;
            }
        }

        return $data;
    }

    /**
     * Enrich rows after find to ensure both `body_template` and `content` are populated
     */
    protected function enrichTwinColumns(array $data): array
    {
        if (empty($data['data'])) {
            return $data;
        }

        if (isset($data['singleton']) && $data['singleton']) {
            $row = $data['data'];
            if (is_array($row)) {
                if (empty($row['content']) && !empty($row['body_template'])) {
                    $row['content'] = $row['body_template'];
                } elseif (empty($row['body_template']) && !empty($row['content'])) {
                    $row['body_template'] = $row['content'];
                }
                $data['data'] = $row;
            } elseif (is_object($row)) {
                if (empty($row->content) && !empty($row->body_template)) {
                    $row->content = $row->body_template;
                } elseif (empty($row->body_template) && !empty($row->content)) {
                    $row->body_template = $row->content;
                }
                $data['data'] = $row;
            }
        } else {
            foreach ($data['data'] as $idx => $row) {
                if (is_array($row)) {
                    if (empty($row['content']) && !empty($row['body_template'])) {
                        $data['data'][$idx]['content'] = $row['body_template'];
                    } elseif (empty($row['body_template']) && !empty($row['content'])) {
                        $data['data'][$idx]['body_template'] = $row['content'];
                    }
                } elseif (is_object($row)) {
                    if (empty($row->content) && !empty($row->body_template)) {
                        $data['data'][$idx]->content = $row->body_template;
                    } elseif (empty($row->body_template) && !empty($row->content)) {
                        $data['data'][$idx]->body_template = $row->content;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * DataTables Query for Contract Templates
     */
    public function getDatatables(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'id', string $orderDir = 'DESC'): array
    {
        $builder = $this->builder();

        if (!empty($search)) {
            $builder->groupStart()
                ->like('title', $search)
                ->orLike('body_template', $search)
                ->orLike('content', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);

        $allowedCols = ['id', 'title', 'body_template', 'content', 'is_active', 'created_at', 'updated_at'];
        if (!in_array($orderCol, $allowedCols, true)) {
            $orderCol = 'id';
        }
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder->orderBy($orderCol, $orderDir);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->countAllResults();

        // Enrich rows
        foreach ($data as &$row) {
            if (empty($row['content']) && !empty($row['body_template'])) {
                $row['content'] = $row['body_template'];
            } elseif (empty($row['body_template']) && !empty($row['content'])) {
                $row['body_template'] = $row['content'];
            }
        }
        unset($row);

        return [
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    // ==========================================
    // CI3 Backward Compatibility Methods
    // ==========================================

    public function count_all(?string $q = null, ?string $field = null): int
    {
        $builder = $this->builder();
        $q = $this->scurity($q);
        $field = $this->scurity($field);

        if (!empty($q)) {
            if (!empty($field) && in_array($field, ['title', 'content', 'body_template', 'created_at'])) {
                $builder->like($this->table . '.' . $field, $q);
            } else {
                $builder->groupStart()
                    ->like($this->table . '.title', $q)
                    ->orLike($this->table . '.body_template', $q)
                    ->orLike($this->table . '.content', $q)
                    ->orLike($this->table . '.created_at', $q)
                    ->groupEnd();
            }
        }

        return (int) $builder->countAllResults();
    }

    public function get(?string $q = null, ?string $field = null, int $limit = 0, int $offset = 0, array $select_field = []): array
    {
        $builder = $this->builder();
        $q = $this->scurity($q);
        $field = $this->scurity($field);

        if (!empty($select_field)) {
            $builder->select(implode(',', $select_field));
        } else {
            $builder->select('*');
        }

        if (!empty($q)) {
            if (!empty($field) && in_array($field, ['title', 'content', 'body_template', 'created_at'])) {
                $builder->like($this->table . '.' . $field, $q);
            } else {
                $builder->groupStart()
                    ->like($this->table . '.title', $q)
                    ->orLike($this->table . '.body_template', $q)
                    ->orLike($this->table . '.content', $q)
                    ->orLike($this->table . '.created_at', $q)
                    ->groupEnd();
            }
        }

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        $builder->orderBy($this->table . '.' . $this->primaryKey, 'DESC');
        $rows = $builder->get()->getResult();

        foreach ($rows as $row) {
            if (empty($row->content) && !empty($row->body_template)) {
                $row->content = $row->body_template;
            } elseif (empty($row->body_template) && !empty($row->content)) {
                $row->body_template = $row->content;
            }
        }

        return $rows;
    }

    public function store(array $save_data)
    {
        $insertId = $this->insert($save_data, true);
        return $insertId ?: false;
    }

    public function change($id, array $save_data): bool
    {
        return (bool) $this->update($id, $save_data);
    }

    public function remove($id): bool
    {
        return (bool) $this->delete($id);
    }

    public function join_avaiable(): self
    {
        return $this;
    }

    public function filter_avaiable(): self
    {
        return $this;
    }

    public function scurity(?string $str): ?string
    {
        if ($str === null) {
            return null;
        }
        return trim(strip_tags($str));
    }

    /**
     * Preview helper: replace placeholders with sample/mock values
     */
    public function renderSample(string $templateContent): string
    {
        $samples = [
            '{nomor_kontrak}'   => 'KOLAB/CTR/' . date('Ym') . '/001',
            '{tanggal}'         => date('d F Y'),
            '{brand_name}'      => 'PT Kolaborasi Digital Prima',
            '{campaign_title}'  => 'Mega Promo Q4 Influencer Activation',
            '{kol_name}'        => 'Jessica Aurelia (KOL Sample)',
            '{kol_nik}'         => '3171012345670001',
            '{kol_address}'     => 'Jl. Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Selatan',
            '{kol_whatsapp}'    => '+62 812-3456-7890',
            '{deal_amount}'     => 'Rp 15.000.000,-',
            '{total_vt}'        => '3 Video TikTok + 2 IG Reels',
            '{kol_bank_name}'   => 'BCA (Bank Central Asia)',
            '{kol_bank_account}'=> '8820129384 a/n Jessica Aurelia',
        ];

        return strtr($templateContent, $samples);
    }
}
