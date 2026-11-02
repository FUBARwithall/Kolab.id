<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Surat Perjanjian Kerjasama') ?></title>
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
            font-size: 14px;
            text-align: justify;
        }
        .sign-box {
            min-height: 90px;
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
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">
            <i class="fas fa-times me-1"></i> Tutup
        </button>
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
            <div style="font-size: 11px; color: #6b7280; font-family: 'Segoe UI', Arial, sans-serif;">PT KOLABORASI KREATIF DIGITAL INDONESIA &bull; DISBURSEMENT &amp; TALENT MANAGEMENT</div>
        </div>

        <?php
            $contractNo = $contract['display_contract_no'] ?? ($contract['contract_no'] ?: ($contract['contract_number'] ?? 'CTR-DRAFT'));
            $clauses = $contract['contract_html'] ?: ($contract['clauses_rendered'] ?? '');
        ?>

        <div class="contract-title">SURAT PERJANJIAN KERJASAMA KREATIF (MoU)</div>
        <div class="contract-subtitle font-monospace">Nomor: <?= esc($contractNo) ?></div>

        <div class="clause-text">
            <?php if (str_contains($clauses, '<p>') || str_contains($clauses, '<div>') || str_contains($clauses, '<br')): ?>
                <?= $clauses ?>
            <?php else: ?>
                <?= nl2br(esc($clauses)) ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($contract['terms_and_conditions']) && !str_contains($clauses, $contract['terms_and_conditions'])): ?>
            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-uppercase small" style="font-family: 'Segoe UI', sans-serif;">Syarat &amp; Ketentuan Khusus (S&amp;K):</h6>
                <div class="small" style="font-size: 13px; text-align: justify;">
                    <?= nl2br(esc($contract['terms_and_conditions'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Signatures Grid -->
        <div class="row pt-5 mt-4 text-center">
            <div class="col-6">
                <div class="fw-bold" style="font-size: 13px;">PIHAK PERTAMA</div>
                <div class="small text-muted mb-2">Brand / Kolab.id Agency</div>
                <div class="sign-box">
                    <?php if (!empty($contract['admin_signature_image']) && is_file(FCPATH . $contract['admin_signature_image'])): ?>
                        <img src="<?= base_url($contract['admin_signature_image']) ?>" alt="Brand Signature" style="max-height: 80px; max-width: 180px; object-fit: contain;">
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            <i class="fas fa-shield-alt me-1"></i> Terverifikasi Brand
                        </span>
                    <?php endif; ?>
                </div>
                <div class="fw-bold text-decoration-underline" style="font-size: 13.5px;"><?= esc($contract['brand_name'] ?: 'KOLAB.ID MANAGEMENT') ?></div>
                <div class="small text-muted">Authorized Representative</div>
            </div>

            <div class="col-6">
                <div class="fw-bold" style="font-size: 13px;">PIHAK KEDUA</div>
                <div class="small text-muted mb-2">Talent KOL / Influencer</div>
                <div class="sign-box">
                    <?php
                        $kolSig = $contract['kol_signature_image'] ?? ($contract['signature_image'] ?? null);
                    ?>
                    <?php if (!empty($kolSig) && is_file(FCPATH . $kolSig)): ?>
                        <img src="<?= base_url($kolSig) ?>" alt="KOL Signature" style="max-height: 80px; max-width: 180px; object-fit: contain;">
                    <?php elseif (!empty($contract['signed_at'])): ?>
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
                <div class="fw-bold text-decoration-underline" style="font-size: 13.5px;"><?= esc($contract['kol_name'] ?? 'Talent KOL') ?></div>
                <div class="small text-muted">Talent Influencer</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-5 pt-3 border-top" style="font-size: 10px;">
            Dokumen elektronik ini sah secara hukum sesuai UU ITE No. 11 Tahun 2008 Pasal 5 Ayat 1.
        </div>
    </div>
</div>

</body>
</html>
