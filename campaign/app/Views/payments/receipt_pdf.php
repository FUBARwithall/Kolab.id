<?php
if (is_object($payment)) {
    $payment = (array) $payment;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Bukti Pembayaran') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            line-height: 1.5;
        }
        .voucher-box {
            max-width: 820px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 40px;
            border: 1px solid #e2e8f0;
        }
        .header-voucher {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .voucher-title {
            letter-spacing: 1px;
            font-weight: 800;
        }
        .table-voucher td {
            padding: 8px 12px;
            vertical-align: top;
            border: 1px solid #e2e8f0;
            font-size: 13.5px;
        }
        .table-voucher .lbl {
            background-color: #f8fafc;
            font-weight: 600;
            color: #334155;
            width: 25%;
        }
        .amount-banner {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 16px 20px;
        }
        .sig-block {
            margin-top: 40px;
        }
        .sig-box {
            height: 90px;
            border-bottom: 1px solid #475569;
            margin-bottom: 8px;
        }
        @media print {
            body { background: #ffffff; }
            .voucher-box {
                box-shadow: none;
                margin: 0;
                padding: 15px;
                max-width: 100%;
                border-radius: 0;
                border: none;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container py-3">
    <!-- Action Bar (Hidden in Print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 820px; margin: auto;">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">
            <i class="fas fa-times me-1"></i> Tutup Jendela
        </button>
        <button type="button" class="btn btn-primary btn-sm px-4" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <!-- Main Printable Voucher -->
    <div class="voucher-box">
        <!-- Header -->
        <div class="header-voucher d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0 text-primary">KOLAB<span class="text-dark">.ID</span></h3>
                <div class="text-muted small">PT KOLABORASI INFLUENCER INDONESIA</div>
                <div class="text-muted small">Financial &amp; Disbursement Management System</div>
            </div>
            <div class="text-end">
                <h5 class="mb-0 text-dark voucher-title">BUKTI PENGELUARAN KAS / BANK</h5>
                <div class="fw-bold text-danger font-monospace mt-1 fs-6">
                    PV-<?= str_pad((string)$payment['id'], 6, '0', STR_PAD_LEFT) ?>
                </div>
                <div class="text-muted small">
                    Tanggal: <?= !empty($payment['paid_at']) ? date('d F Y', strtotime($payment['paid_at'])) : date('d F Y') ?>
                </div>
            </div>
        </div>

        <!-- Highlighted Amount Banner -->
        <?php
            $amount = (float)($payment['amount_paid'] ?? $payment['amount']);
        ?>
        <div class="amount-banner d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="text-muted small text-uppercase fw-bold d-block">Jumlah Dana yang Dibayarkan</span>
                <span class="fs-6 text-muted">Mata Uang: Indonesian Rupiah (IDR)</span>
            </div>
            <div class="text-end">
                <h2 class="mb-0 fw-bold text-success">
                    Rp <?= number_format($amount, 0, ',', '.') ?>
                </h2>
            </div>
        </div>

        <!-- Transaction Details Table -->
        <table class="table table-voucher mb-4">
            <tbody>
                <tr>
                    <td class="lbl">Dibayarkan Kepada</td>
                    <td class="fw-bold text-dark">
                        <?= esc($payment['kol_name'] ?? 'Talent KOL') ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Rekening Tujuan</td>
                    <td>
                        <strong><?= esc($payment['bank_name'] ?: 'Bank') ?></strong> &bull; 
                        <span class="font-monospace text-primary fw-bold"><?= esc($payment['bank_account'] ?: '-') ?></span>
                        (a/n <?= esc($payment['bank_holder'] ?: '-') ?>)
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Referensi Invoice</td>
                    <td>
                        <span class="badge bg-light text-dark border font-monospace me-2"><?= esc($payment['invoice_number'] ?? '-') ?></span>
                        Total Invoice: Rp <?= number_format((float)($payment['invoice_grand_total'] ?? 0), 0, ',', '.') ?> 
                        (Status: <span class="fw-semibold"><?= esc($payment['invoice_status'] ?? 'PAID') ?></span>)
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Kampanye / Project</td>
                    <td>
                        <?= esc($payment['campaign_title'] ?? 'Kampanye KOL') ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Metode Pembayaran</td>
                    <td>
                        <?= esc($payment['payment_method'] ?? 'Bank Transfer') ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">No. Referensi / TRX ID</td>
                    <td class="font-monospace">
                        <?= esc($payment['bank_reference'] ?: '-') ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Keterangan / Catatan</td>
                    <td>
                        <?= !empty($payment['notes']) ? nl2br(esc($payment['notes'])) : 'Pembayaran fee talent kampanye influencer sesuai invoice.' ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Petugas Pencatat</td>
                    <td>
                        <?= esc($payment['payer_name'] ?: 'Staff Finance') ?> &bull; <small class="text-muted">pada <?= !empty($payment['paid_at']) ? date('d/m/Y H:i', strtotime($payment['paid_at'])) : date('d/m/Y H:i') ?></small>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Bukti Gambar Jika Ada -->
        <?php
            $file = $payment['transfer_receipt_file'] ?? $payment['payment_proof_file'] ?? null;
        ?>
        <?php if (!empty($file)): ?>
            <?php
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            ?>
            <?php if ($isImage): ?>
                <div class="mb-4 p-3 border rounded bg-light">
                    <div class="fw-bold small text-muted text-uppercase mb-2">Lampiran Bukti Transfer:</div>
                    <img src="<?= base_url($file) ?>" alt="Bukti Transfer" style="max-height: 200px; max-width: 100%; object-fit: contain;" class="border rounded">
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Signature Block -->
        <div class="sig-block">
            <div class="row text-center">
                <div class="col-4">
                    <div class="text-muted small fw-semibold">Disiapkan Oleh (Finance)</div>
                    <div class="sig-box"></div>
                    <div class="fw-bold small text-dark">( <?= esc($payment['payer_name'] ?: 'Staff Finance') ?> )</div>
                </div>
                <div class="col-4">
                    <div class="text-muted small fw-semibold">Diperiksa Oleh (Spv/Accounting)</div>
                    <div class="sig-box"></div>
                    <div class="fw-bold small text-dark">( ____________________ )</div>
                </div>
                <div class="col-4">
                    <div class="text-muted small fw-semibold">Disetujui Oleh (Director/Owner)</div>
                    <div class="sig-box"></div>
                    <div class="fw-bold small text-dark">( ____________________ )</div>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-muted small mt-4 pt-3 border-top">
            Dokumen ini dihasilkan secara otomatis oleh sistem Kolab.id dan sah sebagai bukti mutasi kas/bank pengeluaran fee talent.
        </div>
    </div>
</div>

</body>
</html>
