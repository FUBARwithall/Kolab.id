<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Laporan Rekapitulasi Pembayaran KOL') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
        }
        .header-report {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .table-report th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
            padding: 6px 8px;
        }
        .table-report td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 9.5pt; }
        }
    </style>
</head>
<body class="p-4">

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">Tutup</button>
    <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<div class="header-report d-flex justify-content-between align-items-end">
    <div>
        <h3 class="fw-bold mb-0 text-primary">KOLAB<span class="text-dark">.ID</span></h3>
        <h5 class="fw-bold text-dark mt-1 mb-0">Laporan Rekapitulasi Pencairan Pembayaran Fee KOL</h5>
        <div class="text-muted small">Dicetak pada: <?= esc($date ?? date('d F Y, H:i')) ?></div>
    </div>
    <div class="text-end">
        <?php
            $grandTotalPaid = 0;
            foreach ($payments as $p) {
                $grandTotalPaid += (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
            }
        ?>
        <div class="fw-bold">Total Transaksi: <?= count($payments) ?> Record</div>
        <div class="fw-bold text-success">Total Dana Dicairkan: Rp <?= number_format($grandTotalPaid, 0, ',', '.') ?></div>
    </div>
</div>

<table class="table table-bordered table-report">
    <thead>
        <tr>
            <th style="width: 30px;" class="text-center">#</th>
            <th style="width: 70px;">ID Transaksi</th>
            <th style="width: 90px;">Waktu Bayar</th>
            <th style="width: 100px;">No Invoice</th>
            <th>Penerima (KOL)</th>
            <th>Rekening Tujuan</th>
            <th>Kampanye</th>
            <th style="width: 110px;">Metode</th>
            <th class="text-end" style="width: 110px;">Nominal (IDR)</th>
            <th style="width: 90px;">Petugas</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($payments)): ?>
            <?php $no = 1; foreach ($payments as $p): ?>
                <?php
                    $amt = (float)($p['amount_paid'] ?? $p['amount'] ?? 0);
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="font-monospace fw-bold text-primary">PV-<?= str_pad((string)$p['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= !empty($p['paid_at']) ? date('d/m/Y H:i', strtotime($p['paid_at'])) : '-' ?></td>
                    <td class="font-monospace small"><?= esc($p['invoice_number'] ?? '-') ?></td>
                    <td class="fw-semibold"><?= esc($p['kol_name'] ?? '-') ?></td>
                    <td class="small">
                        <?= esc($p['bank_name'] ?: '-') ?><br>
                        <span class="font-monospace text-muted"><?= esc($p['bank_account'] ?: '-') ?></span>
                    </td>
                    <td class="small"><?= esc($p['campaign_title'] ?? '-') ?></td>
                    <td><?= esc($p['payment_method'] ?? '-') ?></td>
                    <td class="text-end fw-bold text-success">Rp <?= number_format($amt, 0, ',', '.') ?></td>
                    <td class="small text-muted"><?= esc($p['payer_name'] ?: 'Staff') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center text-muted py-3">Tidak ada data pembayaran yang ditemukan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="fw-bold bg-light">
            <td colspan="8" class="text-end text-uppercase">Total Pengeluaran Kas / Bank:</td>
            <td class="text-end text-success fs-6">Rp <?= number_format($grandTotalPaid, 0, ',', '.') ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="row mt-4 pt-3">
    <div class="col-8">
        <small class="text-muted">Laporan ini dibuat otomatis oleh Kolab.id dan telah dicatat dalam buku kas/bank pengeluaran.</small>
    </div>
    <div class="col-4 text-end">
        <small class="text-muted">Finance Department</small>
    </div>
</div>

</body>
</html>
