<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignKolModel extends Model
{
    protected $table            = 'campaign_kols';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id',
        'kol_id',
        'deal_amount',
        'total_vt',
        'contract_status',
        'payment_status',
        'notes',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'kol_id'      => 'required|integer',
        'deal_amount' => 'required|numeric',
        'total_vt'    => 'required|integer|greater_than[0]',
    ];

    /**
     * Daftarkan KOL ke Kampanye dan otomatis buatkan draft Tasks (VT)
     */
    public function assignKolWithTasks(int $campaignId, int $kolId, float $dealAmount, int $totalVt, ?string $notes = null, array $variantIds = [], ?int $userId = null): int
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $campaignKolId = $this->insert([
            'campaign_id'     => $campaignId,
            'kol_id'          => $kolId,
            'deal_amount'     => $dealAmount,
            'total_vt'        => $totalVt,
            'contract_status' => 'DRAFT',
            'payment_status'  => 'UNPAID',
            'notes'           => $notes,
            'created_by'      => $userId ?: (session()->get('user_id') ?: 1),
        ]);

        // Simpan alokasi varian produk jika ada
        if (!empty($variantIds)) {
            $variantData = [];
            foreach ($variantIds as $vId) {
                $variantData[] = [
                    'campaign_kol_id' => $campaignKolId,
                    'variant_id'      => (int)$vId,
                    'qty'             => 1,
                    'created_at'      => date('Y-m-d H:i:s'),
                ];
            }
            $db->table('campaign_kol_variants')->insertBatch($variantData);
        }

        // Otomatis buat baris tugas (Tasks / VT) sebanyak total_vt
        $taskModel = new \App\Models\TaskModel();
        $ratePerVt = $totalVt > 0 ? ($dealAmount / $totalVt) : $dealAmount;

        for ($vt = 1; $vt <= $totalVt; $vt++) {
            $db->table('tasks')->insert([
                'campaign_kol_id' => $campaignKolId,
                'task_number'     => $vt,
                'title'           => 'Konten Video VT #' . $vt,
                'rate_amount'     => $ratePerVt,
                'is_rate_locked'  => 0,
                'status'          => 'DRAFT',
                'deadline'        => null,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        return $campaignKolId;
    }
}
