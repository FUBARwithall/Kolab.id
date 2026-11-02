<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Laporan Rekapitulasi Kontrak & MoU KOL') ?></title>
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
        <h5 class="fw-bold text-dark mt-1 mb-0">Laporan Rekapitulasi Kontrak &amp; MoU Talent KOL</h5>
        <div class="text-muted small">Dicetak pada: <?= esc($date ?? date('d F Y, H:i')) ?></div>
    </div>
    <div class="text-end">
        <?php
            $grandDeal = 0;
            foreach ($contracts as $c) {
                $grandDeal += (float)($c['deal_amount'] ?? 0);
            }
        ?>
        <div class="fw-bold">Total Dokumen: <?= count($contracts) ?> Kontrak</div>
        <div class="fw-bold text-success">Total Komitmen Deal: Rp <?= number_format($grandDeal, 0, ',', '.') ?></div>
    </div>
</div>

<table class="table table-bordered table-report">
    <thead>
        <tr>
            <th style="width: 30px;" class="text-center">#</th>
            <th style="width: 120px;">No. Kontrak</th>
            <th>Kampanye</th>
            <th>Brand</th>
            <th>Talent KOL</th>
            <th class="text-end" style="width: 100px;">Nilai Deal (IDR)</th>
            <th style="width: 50px;" class="text-center">VT</th>
            <th style="width: 80px;">Status</th>
            <th style="width: 85px;">Waktu TTD</th>
            <th style="width: 110px;">No. Invoice</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($contracts)): ?>
            <?php $no = 1; foreach ($contracts as $c): ?>
                <?php
                    $ctrNo = $c['display_contract_no'] ?: ($c['contract_no'] ?: ($c['contract_number'] ?? 'CTR-' . $c['id']));
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="font-monospace fw-bold text-primary"><?= esc($ctrNo) ?></td>
                    <td><?= esc($c['campaign_title'] ?: ($c['campaign_name'] ?? '-')) ?></td>
                    <td class="small"><?= esc($c['brand_name'] ?: '-') ?></td>
                    <td class="fw-semibold"><?= esc($c['kol_name'] ?? '-') ?></td>
                    <td class="text-end fw-bold text-success">Rp <?= number_format((float)($c['deal_amount'] ?? 0), 0, ',', '.') ?></td>
                    <td class="text-center"><?= esc((string)($c['total_vt'] ?? 1)) ?></td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= esc($c['status']) ?></span>
                    </td>
                    <td class="small"><?= !empty($c['signed_at']) ? date('d/m/Y', strtotime($c['signed_at'])) : '-' ?></td>
                    <td class="font-monospace small"><?= esc($c['invoice_number'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center text-muted py-3">Tidak ada data kontrak yang ditemukan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="fw-bold bg-light">
            <td colspan="5" class="text-end text-uppercase">Total Akumulasi Kompensasi SOW:</td>
            <td class="text-end text-success">Rp <?= number_format($grandDeal, 0, ',', '.') ?></td>
            <td colspan="4"></td>
        </tr>
    </tfoot>
</table>

<div class="row mt-4 pt-3">
    <div class="col-8">
        <small class="text-muted">Laporan resmi dokumen kontrak kerjasama influencer yang terdaftar pada sistem Kolab.id.</small>
    </div>
    <div class="col-4 text-end">
        <small class="text-muted">Legal &amp; Operations Dept</small>
    </div>
</div>

</body>
</html>
