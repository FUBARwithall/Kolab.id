<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_id',
        'amount_paid',
        'payment_method',
        'bank_reference',
        'transfer_receipt_file',
        'paid_by',
        'paid_at',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Record payment transaction atomically
     */
    public function recordPayment(array $data, int $userId): int
    {
        $this->db->transStart();

        $invoiceId = (int)$data['invoice_id'];
        $amount    = (float)$data['amount_paid'];
        $now       = date('Y-m-d H:i:s');

        $data['paid_by']    = $userId;
        $data['paid_at']    = $data['paid_at'] ?: $now;
        $data['created_at'] = $now;

        $paymentId = $this->insert($data, true);

        // 1. Update invoice status ke PAID
        $this->db->table('invoices')->where('id', $invoiceId)->update([
            'status'     => InvoiceModel::STATUS_PAID,
            'paid_at'    => $now,
            'updated_at' => $now,
        ]);

        // 2. Ambil campaign_kol_id untuk update status pembayaran campaign_kol
        $inv = $this->db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if ($inv && !empty($inv['campaign_kol_id'])) {
            $this->db->table('campaign_kols')->where('id', $inv['campaign_kol_id'])->update([
                'payment_status' => 'PAID',
                'updated_at'     => $now,
            ]);
        }

        $this->db->transComplete();
        return $paymentId;
    }
}
