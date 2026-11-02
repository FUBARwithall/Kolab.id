<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Invoice') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 850px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 40px;
        }
        .table-invoice th {
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
        }
        .table-invoice td {
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13.5px;
        }
        .bank-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
        }
        @media print {
            body { background: #ffffff; }
            .invoice-box {
                box-shadow: none;
                margin: 0;
                padding: 15px;
                max-width: 100%;
                border-radius: 0;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container py-3">
    <!-- Action Bar (Hidden in Print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 850px; margin: auto;">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">
            <i class="fas fa-times me-1"></i> Tutup Jendela
        </button>
        <button type="button" class="btn btn-primary btn-sm px-4" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <!-- Main Printable Invoice Sheet -->
    <div class="invoice-box">
        <!-- 1. Header Area -->
        <div class="d-flex justify-content-between align-items-end border-bottom pb-3 mb-4">
            <div>
                <h1 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px; font-size: 28px;">INVOICE</h1>
                <div class="font-monospace fw-bold text-muted mt-1" style="font-size: 15px;">#<?= esc($invoice->invoice_number ?? $invoice->invoice_no ?? '') ?></div>
            </div>
            <div class="text-end">
                <h3 class="fw-bold text-primary mb-0">KOLAB<span class="text-dark">.ID</span></h3>
                <div class="text-muted small">Platform Manajemen Influencer Marketing &amp; Kampanye</div>
                <div class="text-muted small">Jakarta, Indonesia &bull; support@kolab.id</div>
            </div>
        </div>

        <!-- 2. Invoice & Creator Info -->
        <div class="row g-4 mb-4">
            <div class="col-6">
                <div class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">KOL / Creator:</div>
                <h5 class="fw-bold text-dark mb-1 mt-1"><?= esc($invoice->kol_name ?? 'N/A') ?></h5>
                <div class="small text-muted">Kampanye: <strong class="text-dark"><?= esc($invoice->campaign_title ?? $invoice->campaign_name ?? 'N/A') ?></strong></div>
                <?php if (!empty($invoice->kol_npwp ?? $invoice->npwp_number)): ?>
                    <div class="small text-muted">NPWP: <?= esc($invoice->kol_npwp ?? $invoice->npwp_number) ?></div>
                <?php endif; ?>
                <?php if (!empty($invoice->kol_whatsapp)): ?>
                    <div class="small text-muted">Kontak: <?= esc($invoice->kol_whatsapp) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small py-1">Tanggal Terbit:</td>
                        <td class="text-end fw-semibold text-dark py-1">
                            <?= !empty($invoice->created_at) ? date('d M Y', strtotime($invoice->created_at)) : '-' ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small py-1">Jatuh Tempo:</td>
                        <td class="text-end fw-semibold text-dark py-1">
                            <?= !empty($invoice->due_date) ? date('d M Y', strtotime($invoice->due_date)) : '-' ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted small py-1">Status Pembayaran:</td>
                        <td class="text-end py-1">
                            <?php
                                $status = strtoupper((string)($invoice->status ?? 'SUBMITTED'));
                                $colorClass = match($status) {
                                    'PAID' => 'text-success fw-bold',
                                    'PARTIAL' => 'text-warning fw-bold',
                                    'VERIFIED', 'APPROVED' => 'text-info fw-bold',
                                    'REJECTED' => 'text-danger fw-bold',
                                    default => 'text-primary fw-bold',
                                };
                            ?>
                            <span class="<?= $colorClass ?>"><?= $status ?></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 3. Deliverables / Services Table -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-2">Deliverable Performance &amp; Services</h6>
            <div class="table-responsive">
                <table class="table table-invoice mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Deskripsi Pekerjaan / Layanan</th>
                            <th style="width: 80px;" class="text-center">Qty</th>
                            <th style="width: 170px;" class="text-end">Harga Satuan</th>
                            <th style="width: 180px;" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $subtotal = (float)($invoice->subtotal ?? 0); ?>
                        <tr>
                            <td class="text-center font-monospace">1</td>
                            <td>
                                <div class="fw-bold text-dark">Jasa Pembuatan &amp; Publikasi Konten KOL (Deliverable)</div>
                                <small class="text-muted">Kampanye: <?= esc($invoice->campaign_title ?? $invoice->campaign_name ?? 'N/A') ?></small>
                            </td>
                            <td class="text-center font-monospace"><?= esc($invoice->total_vt ?? 1) ?> VT</td>
                            <td class="text-end font-monospace">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                            <td class="text-end font-monospace fw-bold text-dark">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Payment Instructions & Summary Row -->
        <div class="row g-4 mb-4">
            <!-- Payment Instructions -->
            <div class="col-md-6">
                <h6 class="fw-bold text-dark mb-2">Instruksi Pembayaran / Rekening Tujuan</h6>
                <div class="bank-box">
                    <?php if (!empty($invoice->bank_name)): ?>
                        <div class="text-muted text-uppercase" style="font-size: 11px;">Nama Bank</div>
                        <div class="fw-bold text-dark fs-6 mb-2"><?= esc($invoice->bank_name) ?></div>

                        <div class="text-muted text-uppercase" style="font-size: 11px;">Nomor Rekening</div>
                        <div class="fw-bold font-monospace text-primary fs-5 mb-2"><?= esc($invoice->bank_account ?? $invoice->bank_account_number ?? '-') ?></div>

                        <div class="text-muted text-uppercase" style="font-size: 11px;">Atas Nama Rekening</div>
                        <div class="fw-bold text-dark"><?= esc($invoice->bank_holder ?? $invoice->bank_account_name ?? '-') ?></div>
                    <?php else: ?>
                        <div class="text-muted small fst-italic">
                            Pembayaran ditransfer ke rekening resmi yang telah terdaftar dalam sistem.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="col-md-6">
                <h6 class="fw-bold text-dark mb-2">Ringkasan Pembayaran</h6>
                <?php
                    $taxAmount = (float)($invoice->tax_amount ?? 0);
                    $grandTotal = (float)($invoice->grand_total ?? ($subtotal - $taxAmount));
                    $amountPaid = (float)($invoice->amount_paid ?? 0);
                    $balanceDue = max(0, $grandTotal - $amountPaid);
                ?>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted py-1">Gross Subtotal (DPP):</td>
                        <td class="text-end fw-bold text-dark py-1 font-monospace">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                    </tr>
                    <?php if ($taxAmount > 0): ?>
                    <tr>
                        <td class="text-danger py-1">
                            Potongan Pajak (<?= esc($invoice->tax_type ?? 'PPh') ?>):
                        </td>
                        <td class="text-end fw-bold text-danger py-1 font-monospace">- Rp <?= number_format($taxAmount, 0, ',', '.') ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="border-top">
                        <td class="fw-bold py-2 fs-6">Net Ditransfer:</td>
                        <td class="text-end fw-bold text-success py-2 fs-5 font-monospace">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                    </tr>
                    <?php if ($amountPaid > 0): ?>
                    <tr class="border-top">
                        <td class="text-muted py-1">Telah Dicairkan:</td>
                        <td class="text-end fw-bold text-primary py-1 font-monospace">Rp <?= number_format($amountPaid, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted py-1">Sisa Tagihan (Balance Due):</td>
                        <td class="text-end fw-bold <?= $balanceDue > 0 ? 'text-danger' : 'text-success' ?> py-1 font-monospace">
                            Rp <?= number_format($balanceDue, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- 5. Signature Block -->
        <div class="row mt-5 pt-4">
            <div class="col-7"></div>
            <div class="col-5 text-center">
                <div class="text-muted small mb-1">Authorized by:</div>
                <div class="fw-bold text-dark mb-5"><?= esc($invoice->kol_name ?? 'Finance &amp; Operations Dept') ?></div>
                <div style="border-bottom: 1px solid #334155; width: 70%; margin: auto;"></div>
                <div class="small fw-bold text-dark mt-2">
                    <?= esc($invoice->kol_name ?? ($invoice->creator_name ?? 'Authorized Signatory')) ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
