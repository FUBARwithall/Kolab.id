<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreHistoryModel extends Model
{
    protected $table            = 'score_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kol_id',
        'campaign_kol_id',
        'score_type',
        'score',
        'calculated_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get summary KPI metrics
     */
    public function getSummaryMetrics(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                COUNT(score_histories.id) as total_scores,
                COUNT(DISTINCT score_histories.kol_id) as total_kols,
                COALESCE(AVG(score_histories.score), 0) as avg_score,
                COALESCE(MAX(score_histories.score), 0) as max_score,
                COALESCE(MIN(score_histories.score), 0) as min_score,
                SUM(CASE WHEN score_histories.score_type = "COLLABORATION" THEN 1 ELSE 0 END) as total_collab,
                COALESCE(AVG(CASE WHEN score_histories.score_type = "COLLABORATION" THEN score_histories.score ELSE NULL END), 0) as avg_collab,
                SUM(CASE WHEN score_histories.score_type = "LIFETIME" THEN 1 ELSE 0 END) as total_lifetime,
                COALESCE(AVG(CASE WHEN score_histories.score_type = "LIFETIME" THEN score_histories.score ELSE NULL END), 0) as avg_lifetime
            ')
            ->join('kols', 'kols.id = score_histories.kol_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = score_histories.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left');

        if (!empty($filters['kol_id'])) {
            $builder->where('score_histories.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['score_type'])) {
            $builder->where('score_histories.score_type', strtoupper(trim((string)$filters['score_type'])));
        }

        if (!empty($filters['date_start'])) {
            $builder->where('score_histories.calculated_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('score_histories.calculated_at <=', $filters['date_end'] . ' 23:59:59');
        }

        $row = $builder->get()->getRowArray();

        return [
            'total_scores'   => (int)($row['total_scores'] ?? 0),
            'total_kols'     => (int)($row['total_kols'] ?? 0),
            'avg_score'      => round((float)($row['avg_score'] ?? 0), 2),
            'max_score'      => round((float)($row['max_score'] ?? 0), 2),
            'min_score'      => round((float)($row['min_score'] ?? 0), 2),
            'total_collab'   => (int)($row['total_collab'] ?? 0),
            'avg_collab'     => round((float)($row['avg_collab'] ?? 0), 2),
            'total_lifetime' => (int)($row['total_lifetime'] ?? 0),
            'avg_lifetime'   => round((float)($row['avg_lifetime'] ?? 0), 2),
        ];
    }

    /**
     * DataTables query with joins to kols, campaign_kols, campaigns
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'score_histories.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                score_histories.*,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.name as kols_name,
                kols.avatar_url as kol_avatar,
                kols.email as kol_email,
                kols.phone as kol_phone,
                campaign_kols.id as campaign_kol_id,
                campaign_kols.campaign_id as campaign_kols_campaign_id,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title
            ')
            ->join('kols', 'kols.id = score_histories.kol_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = score_histories.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left');

        if (!empty($filters['kol_id'])) {
            $builder->where('score_histories.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['score_type'])) {
            $builder->where('score_histories.score_type', strtoupper(trim((string)$filters['score_type'])));
        }

        if (!empty($filters['date_start'])) {
            $builder->where('score_histories.calculated_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('score_histories.calculated_at <=', $filters['date_end'] . ' 23:59:59');
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('kols.name', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('score_histories.score_type', $search)
                ->orLike('score_histories.score', $search)
                ->groupEnd();
        }

        $countBuilder = clone $builder;
        $filtered = $countBuilder->countAllResults(false);

        $totalBuilder = $this->db->table($this->table);
        $total = $totalBuilder->countAllResults();

        // Safe column ordering mapping
        $orderMap = [
            'id'              => 'score_histories.id',
            'kol_id'          => 'kols.name',
            'campaign_kol_id' => 'campaigns.title',
            'score_type'      => 'score_histories.score_type',
            'score'           => 'score_histories.score',
            'calculated_at'   => 'score_histories.calculated_at',
        ];

        $cleanOrderCol = $orderMap[$orderCol] ?? 'score_histories.id';
        $cleanOrderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $builder->orderBy($cleanOrderCol, $cleanOrderDir);

        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();

        return [
            'data'            => $data,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
        ];
    }

    /**
     * Get single detail with complete relations
     */
    public function getDetail(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('
                score_histories.*,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.name as kols_name,
                kols.avatar_url as kol_avatar,
                kols.email as kol_email,
                kols.phone as kol_phone,
                kols.whatsapp as kol_whatsapp,
                kols.status as kol_status,
                campaign_kols.id as campaign_kol_id,
                campaign_kols.campaign_id as campaign_kols_campaign_id,
                campaign_kols.status as dealing_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title
            ')
            ->join('kols', 'kols.id = score_histories.kol_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = score_histories.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left')
            ->where('score_histories.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all rows for export with filters
     */
    public function getAllForExport(array $filters = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                score_histories.*,
                kols.name as kol_name,
                kols.name as kols_name,
                kols.email as kol_email,
                campaigns.title as campaign_title,
                campaign_kols.campaign_id as campaign_kols_campaign_id
            ')
            ->join('kols', 'kols.id = score_histories.kol_id', 'left')
            ->join('campaign_kols', 'campaign_kols.id = score_histories.campaign_kol_id', 'left')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'left');

        if (!empty($filters['kol_id'])) {
            $builder->where('score_histories.kol_id', (int)$filters['kol_id']);
        }

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($filters['score_type'])) {
            $builder->where('score_histories.score_type', strtoupper(trim((string)$filters['score_type'])));
        }

        if (!empty($filters['date_start'])) {
            $builder->where('score_histories.calculated_at >=', $filters['date_start'] . ' 00:00:00');
        }

        if (!empty($filters['date_end'])) {
            $builder->where('score_histories.calculated_at <=', $filters['date_end'] . ' 23:59:59');
        }

        return $builder->orderBy('score_histories.id', 'DESC')->get()->getResultArray();
    }

    /**
     * Log new score entry
     */
    public function logScore(int $kolId, string $scoreType, float $score, ?int $campaignKolId = null): int
    {
        $data = [
            'kol_id'          => $kolId,
            'campaign_kol_id' => $campaignKolId,
            'score_type'      => strtoupper(trim($scoreType)),
            'score'           => $score,
            'calculated_at'   => date('Y-m-d H:i:s'),
        ];

        return (int) $this->insert($data, true);
    }
}
