<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Laporan Rekapitulasi Invoice') ?></title>
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
        <h5 class="fw-bold text-dark mt-1 mb-0">Laporan Rekapitulasi Tagihan &amp; Invoice KOL</h5>
        <div class="text-muted small">Dicetak pada: <?= esc($date ?? date('d F Y, H:i')) ?></div>
    </div>
    <div class="text-end">
        <div class="fw-bold">Total Record: <?= count($invoices) ?> Invoice</div>
    </div>
</div>

<table class="table table-bordered table-report">
    <thead>
        <tr>
            <th style="width: 30px;" class="text-center">#</th>
            <th>No. Invoice</th>
            <th>Kampanye</th>
            <th>Talent KOL</th>
            <th>Rekening Tujuan</th>
            <th class="text-end">Gross Subtotal</th>
            <th class="text-end">Potongan Pajak</th>
            <th class="text-end">Net Ditransfer</th>
            <th class="text-center">Status</th>
            <th class="text-end">Terbayar</th>
            <th>Jatuh Tempo</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $no = 1;
            $totalGross = 0;
            $totalTax   = 0;
            $totalNet   = 0;
            $totalPaid  = 0;
            foreach ($invoices as $inv):
                $subtotal   = (float)$inv['subtotal'];
                $taxAmount  = (float)$inv['tax_amount'];
                $grandTotal = (float)$inv['grand_total'];
                $paid       = (float)($inv['total_paid'] ?? 0);

                $totalGross += $subtotal;
                $totalTax   += $taxAmount;
                $totalNet   += $grandTotal;
                $totalPaid  += $paid;
        ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="font-monospace fw-bold text-primary"><?= esc($inv['invoice_number'] ?? $inv['invoice_no']) ?></td>
                <td><?= esc($inv['campaign_title'] ?: ($inv['campaign_name'] ?? '-')) ?></td>
                <td class="fw-semibold"><?= esc($inv['kol_name'] ?? '-') ?></td>
                <td class="small"><?= esc($inv['bank_name'] ?: '-') ?> <?= esc($inv['bank_account'] ?: '') ?></td>
                <td class="text-end font-monospace">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                <td class="text-end font-monospace text-danger">- Rp <?= number_format($taxAmount, 0, ',', '.') ?></td>
                <td class="text-end font-monospace fw-bold text-success">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                <td class="text-center fw-semibold"><?= esc($inv['status']) ?></td>
                <td class="text-end font-monospace">Rp <?= number_format($paid, 0, ',', '.') ?></td>
                <td><?= !empty($inv['due_date']) ? date('d/m/Y', strtotime($inv['due_date'])) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="fw-bold bg-light">
            <td colspan="5" class="text-end">TOTAL KESELURUHAN:</td>
            <td class="text-end font-monospace">Rp <?= number_format($totalGross, 0, ',', '.') ?></td>
            <td class="text-end font-monospace text-danger">- Rp <?= number_format($totalTax, 0, ',', '.') ?></td>
            <td class="text-end font-monospace text-success fs-6">Rp <?= number_format($totalNet, 0, ',', '.') ?></td>
            <td></td>
            <td class="text-end font-monospace text-primary">Rp <?= number_format($totalPaid, 0, ',', '.') ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
