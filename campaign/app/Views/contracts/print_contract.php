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
            background: #f8fafc;
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            line-height: 1.6;
        }
        .contract-paper {
            max-width: 820px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 50px 60px;
        }
        .contract-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .contract-subtitle {
            font-size: 13px;
            text-align: center;
            margin-bottom: 24px;
            color: #4b5563;
        }
        .clause-text {
            white-space: pre-wrap;
            font-size: 14px;
            text-align: justify;
        }
        .sign-box {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media print {
            body { background: #ffffff; font-size: 12pt; }
            .contract-paper { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 820px; margin: auto;">
        <a href="<?= base_url('contracts') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Kontrak
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="contract-paper">
        <!-- Logo Header -->
        <div class="text-center border-bottom pb-3 mb-4">
            <h2 class="fw-bold mb-0" style="font-family: 'Segoe UI', Arial, sans-serif; color: #1e3a8a;">KOLAB<span style="color: #0d6efd;">.ID</span></h2>
            <div style="font-size: 11px; color: #6b7280; font-family: 'Segoe UI', Arial, sans-serif;">PT KOLABORASI KREATIF DIGITAL INDONESIA &bull; JAKARTA</div>
        </div>

        <div class="contract-title">SURAT PERJANJIAN KERJASAMA KREATIF (SOW)</div>
        <div class="contract-subtitle">Nomor: <?= esc($contract['contract_number']) ?></div>

        <div class="clause-text">
<?= esc($contract['clauses_rendered']) ?>
        </div>

        <!-- Signatures Grid -->
        <div class="row pt-5 mt-4 text-center">
            <div class="col-6">
                <div class="fw-bold">PIHAK PERTAMA</div>
                <div class="small text-muted mb-2">Manajemen Kolab.id / Brand</div>
                <div class="sign-box">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="fas fa-shield-alt me-1"></i> Tervalidasi Sistem
                    </span>
                </div>
                <div class="fw-bold text-decoration-underline">KOLAB.ID MANAGEMENT</div>
                <div class="small text-muted">Legal &amp; Campaign Operations</div>
            </div>

            <div class="col-6">
                <div class="fw-bold">PIHAK KEDUA</div>
                <div class="small text-muted mb-2">Talent KOL / Influencer</div>
                <div class="sign-box">
                    <?php if (!empty($contract['signed_at'])): ?>
                        <div class="text-success small">
                            <i class="fas fa-check-circle me-1"></i> Ditandatangani Elektronik (E-Sign)<br>
                            <span class="text-muted" style="font-size: 10px;"><?= date('d F Y H:i', strtotime($contract['signed_at'])) ?> (IP: <?= esc($contract['signed_ip'] ?: '127.0.0.1') ?>)</span>
                        </div>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                            <i class="far fa-clock me-1"></i> Menunggu Tanda Tangan
                        </span>
                    <?php endif; ?>
                </div>
                <div class="fw-bold text-decoration-underline"><?= esc($contract['kol_name']) ?></div>
                <div class="small text-muted">Talent Influencer</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
