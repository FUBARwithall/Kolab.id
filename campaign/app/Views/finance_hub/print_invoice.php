<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        .invoice-card {
            max-width: 800px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            padding: 40px;
        }
        @media print {
            body { background: #ffffff; }
            .invoice-card { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 800px; margin: auto;">
        <a href="<?= base_url('finance') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Finance Hub
        </a>
        <button type="button" class="btn btn-primary btn-sm px-3" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="invoice-card">
        <!-- Brand & Invoice Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-primary">KOLAB<span class="text-dark">.ID</span></h3>
                <div class="text-muted small">Platform Manajemen Kampanye & Influencer Marketing</div>
                <div class="text-muted small">Jakarta, Indonesia &bull; support@kolab.id</div>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-1 font-monospace"><?= esc($invoice['invoice_number']) ?></h4>
                <div class="text-muted small">Tanggal: <?= date('d F Y', strtotime($invoice['created_at'])) ?></div>
                <div class="mt-2">
                    <span class="badge bg-success px-3 py-2 text-uppercase"><?= esc($invoice['status']) ?></span>
                </div>
            </div>
        </div>

        <!-- Billed To & Campaign Info -->
        <div class="row mb-4">
            <div class="col-6">
                <span class="text-xs fw-bold text-uppercase text-muted d-block mb-1">DITERBITKAN OLEH (KOL):</span>
                <h5 class="fw-bold mb-1 text-dark"><?= esc($invoice['kol_name']) ?></h5>
                <div class="small text-muted">WhatsApp: <?= esc($invoice['kol_whatsapp'] ?? '-') ?></div>
                <div class="small text-muted">NPWP: <?= esc($invoice['kol_npwp'] ?? 'Tidak ada (PPh 3%)') ?></div>
                <div class="small text-muted">Kota: <?= esc($invoice['kol_city'] ?? 'Indonesia') ?></div>
            </div>
            <div class="col-6 text-end">
                <span class="text-xs fw-bold text-uppercase text-muted d-block mb-1">KAMPANYE TERKAIT:</span>
                <h5 class="fw-bold mb-1 text-dark"><?= esc($invoice['campaign_title']) ?></h5>
                <div class="small text-muted">Penugasan KOL #<?= esc($invoice['campaign_kol_id']) ?></div>
            </div>
        </div>

        <!-- Line Item Table -->
        <table class="table table-bordered mb-4">
            <thead class="table-light text-uppercase text-xs fw-bold text-muted">
                <tr>
                    <th>Deskripsi Layanan / SOW Konten</th>
                    <th class="text-end" style="width: 200px;">Jumlah (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="fw-bold text-dark">Jasa Pembuatan & Publikasi Konten (SOW KOL)</div>
                        <small class="text-muted">Kampanye: <?= esc($invoice['campaign_title']) ?></small>
                    </td>
                    <td class="text-end fw-semibold">
                        Rp <?= number_format((float)$invoice['subtotal'], 0, ',', '.') ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-end text-muted"><b>Dasar Pengenaan Pajak (DPP):</b></td>
                    <td class="text-end">Rp <?= number_format((float)$invoice['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="text-end text-muted">
                        <b>Potongan Pajak <?= esc($invoice['tax_type']) ?> (<?= ($invoice['tax_rate'] * 100) ?>%):</b>
                    </td>
                    <td class="text-end text-danger">
                        - Rp <?= number_format((float)$invoice['tax_amount'], 0, ',', '.') ?>
                    </td>
                </tr>
                <tr class="table-light">
                    <td class="text-end fw-bold fs-5 text-dark">Total Net Diterima:</td>
                    <td class="text-end fw-bold fs-5 text-success">
                        Rp <?= number_format((float)$invoice['grand_total'], 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Beneficiary Bank Account Info -->
        <div class="card bg-light border-0 rounded-3 p-3 mb-4">
            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-university text-primary me-2"></i>Informasi Rekening Bank Tujuan Pencairan:</h6>
            <div class="row small">
                <div class="col-sm-4 text-muted">Bank Penerima:</div>
                <div class="col-sm-8 fw-bold text-dark"><?= esc($invoice['bank_name'] ?: '-') ?></div>
                <div class="col-sm-4 text-muted">Nomor Rekening:</div>
                <div class="col-sm-8 fw-bold text-dark font-monospace"><?= esc($invoice['bank_account'] ?: '-') ?></div>
                <div class="col-sm-4 text-muted">Atas Nama:</div>
                <div class="col-sm-8 fw-bold text-dark"><?= esc($invoice['bank_holder'] ?: $invoice['kol_name']) ?></div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="row pt-4 text-center">
            <div class="col-6">
                <div class="text-muted small mb-5">Diterbitkan Oleh,</div>
                <div class="fw-bold text-dark text-decoration-underline"><?= esc($invoice['kol_name']) ?></div>
                <small class="text-muted">Talent Influencer</small>
            </div>
            <div class="col-6">
                <div class="text-muted small mb-5">Diverifikasi & Disetujui,</div>
                <div class="fw-bold text-dark text-decoration-underline"><?= esc($invoice['verifier_name'] ?: 'Tim Keuangan Kolab.id') ?></div>
                <small class="text-muted">Finance & Tax Administration</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
