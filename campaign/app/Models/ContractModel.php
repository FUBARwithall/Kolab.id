<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table            = 'campaign_kol_contracts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_kol_id',
        'template_id',
        'contract_number',
        'clauses_rendered',
        'signed_at',
        'signed_ip',
        'signature_image',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * DataTables query with joins to campaign, kol, and deal info
     */
    public function getDatatables(array $filters = [], int $start = 0, int $length = 10, string $search = '', string $orderCol = 'campaign_kol_contracts.id', string $orderDir = 'DESC'): array
    {
        $builder = $this->db->table($this->table)
            ->select('
                campaign_kol_contracts.*,
                campaign_kols.deal_amount,
                campaign_kols.total_vt,
                campaign_kols.contract_status,
                campaigns.id as campaign_id,
                campaigns.title as campaign_title,
                kols.id as kol_id,
                kols.name as kol_name,
                kols.whatsapp as kol_whatsapp,
                kols.nik as kol_nik,
                users.avatar as kol_avatar
            ')
            ->join('campaign_kols', 'campaign_kols.id = campaign_kol_contracts.campaign_kol_id', 'inner')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->join('users', 'users.id = kols.user_id', 'left');

        if (!empty($filters['campaign_id'])) {
            $builder->where('campaigns.id', (int)$filters['campaign_id']);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('campaign_kol_contracts.contract_number', $search)
                ->orLike('campaigns.title', $search)
                ->orLike('kols.name', $search)
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
     * Generate contract draft from Campaign KOL deal
     */
    public function generateContract(int $campaignKolId): int
    {
        $db = $this->db;
        $ck = $db->table('campaign_kols')
            ->select('campaign_kols.*, campaigns.title as campaign_title, campaigns.start_date, campaigns.end_date, kols.name as kol_name, kols.nik, kols.address, kols.whatsapp, kols.bank_name, kols.bank_account, kols.bank_holder')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->where('campaign_kols.id', $campaignKolId)
            ->get()
            ->getRowArray();

        if (!$ck) {
            return 0;
        }

        $contractNumber = sprintf('KTR/%s/%04d', date('Ym'), $campaignKolId);

        $clauses = "PERJANJIAN KERJASAMA KREATIF KOL / INFLUENCER\nNomor: {$contractNumber}\n\n";
        $clauses .= "Pada hari ini, disepakati kerjasama pembuatan dan penayangan konten video antara:\n";
        $clauses .= "PIHAK PERTAMA (Brand / KOLAB.ID)\n";
        $clauses .= "Nama Kampanye : {$ck['campaign_title']}\n\n";
        $clauses .= "PIHAK KEDUA (Talent KOL)\n";
        $clauses .= "Nama Lengkap  : {$ck['kol_name']}\n";
        $clauses .= "NIK           : " . ($ck['nik'] ?: '-') . "\n";
        $clauses .= "Alamat        : " . ($ck['address'] ?: '-') . "\n\n";
        $clauses .= "RUANG LINGKUP PEKERJAAN (SOW):\n";
        $clauses .= "- Jumlah Video (VT): {$ck['total_vt']} Video\n";
        $clauses .= "- Kompensasi / Fee : Rp " . number_format((float)$ck['deal_amount'], 0, ',', '.') . "\n";
        $clauses .= "- Rekening Bank    : {$ck['bank_name']} No. {$ck['bank_account']} a/n {$ck['bank_holder']}\n\n";
        $clauses .= "SYARAT DAN KETENTUAN:\n";
        $clauses .= "1. Konten video wajib melalui proses approval brief dan draft oleh tim KOLAB.ID.\n";
        $clauses .= "2. Video tayang selama masa kampanye dan tidak boleh dihapus minimal 90 hari setelah tayang.\n";
        $clauses .= "3. Pembayaran diproses setelah video tayang live dan diverifikasi tim finance.";

        $contractData = [
            'campaign_kol_id'  => $campaignKolId,
            'template_id'      => null,
            'contract_number'  => $contractNumber,
            'clauses_rendered' => $clauses,
        ];

        // Check if exists
        $existing = $this->where('campaign_kol_id', $campaignKolId)->first();
        if ($existing) {
            $this->update($existing['id'], $contractData);
            return (int)$existing['id'];
        }

        return $this->insert($contractData, true);
    }
}
