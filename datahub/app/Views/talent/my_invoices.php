<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
/* ===== Stat Cards ===== */
.inv-stat-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 18px 20px;
    box-shadow: 0 2px 10px rgba(23,43,77,0.06);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    height: 100%;
}
.inv-stat-card .icon-box {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-right: 15px;
    flex-shrink: 0;
}
.inv-stat-card .stat-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.inv-stat-card .stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 2px;
    line-height: 1.2;
}

/* ===== Section Card & Header ===== */
.section-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    border: none;
    overflow: hidden;
}
.section-card .card-header-custom {
    padding: 18px 24px;
    background: #fafbfc;
    border-bottom: 1.5px solid #edf2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
}
.section-subtitle {
    font-size: 13px;
    color: #64748b;
}

/* ===== Table Styling ===== */
.invoice-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}
.invoice-table thead th {
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1.5px solid #e2e8f0;
    padding: 13px 16px;
    white-space: nowrap;
}
.invoice-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #334155;
}
.invoice-table tbody tr:last-child td {
    border-bottom: none;
}
.invoice-table tbody tr:hover {
    background: #fafbfc;
}

/* ===== Status Badges ===== */
.badge-status {
    padding: 5px 11px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.badge-status-SUBMITTED { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-status-VERIFIED  { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.badge-status-PAID      { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-status-REJECTED  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.badge-status-DRAFT     { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

/* ===== Action Buttons ===== */
.btn-inv-action {
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none !important;
    transition: all 0.15s ease;
}
.btn-inv-detail {
    background: #f1f5f9;
    color: #0284c7;
    border: 1px solid #cbd5e1;
}
.btn-inv-detail:hover {
    background: #e0f2fe;
    color: #0369a1;
}
.btn-inv-file {
    background: #fff;
    color: #e11d48;
    border: 1px solid #fecdd3;
}
.btn-inv-file:hover {
    background: #ffe4e6;
}
.btn-inv-bupot {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.btn-inv-bupot:hover {
    background: #d1fae5;
}

/* ===== Timeline Audit Log ===== */
.timeline-audit {
    list-style: none;
    padding: 0;
    position: relative;
    margin-bottom: 0;
}
.timeline-audit:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 15px;
    width: 2px;
    background: #e2e8f0;
}
.timeline-audit li {
    position: relative;
    margin-bottom: 16px;
    padding-left: 36px;
}
.timeline-audit li .circle-dot {
    position: absolute;
    left: 8px;
    top: 4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #0284c7;
    border: 3px solid #ffffff;
    box-shadow: 0 0 0 1px #0284c7;
}
.timeline-audit li .time {
    font-size: 11px;
    color: #94a3b8;
}
.timeline-audit li .action-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
}
.timeline-audit li .notes {
    font-size: 12px;
    color: #475569;
    margin-top: 3px;
    background: #f8fafc;
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #f1f5f9;
}
</style>

<?php
// Hitung data statistik
$cntSubmitted = 0;
$cntVerified  = 0;
$cntPaid      = 0;
$totalPaidRp  = 0;

if (!empty($invoices)) {
    foreach ($invoices as $inv) {
        $st = $inv['status'] ?? 'DRAFT';
        if ($st === 'SUBMITTED') $cntSubmitted++;
        if ($st === 'VERIFIED')  $cntVerified++;
        if ($st === 'PAID') {
            $cntPaid++;
            $totalPaidRp += (float)($inv['grand_total'] ?? 0);
        }
    }
}
?>

<!-- 1. Stats Counter Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="inv-stat-card">
      <div class="icon-box" style="background: #fef3c7; color: #d97706;">
        <i class="fas fa-clock"></i>
      </div>
      <div>
        <div class="stat-label">Menunggu Review</div>
        <div class="stat-value"><?= $cntSubmitted ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="inv-stat-card">
      <div class="icon-box" style="background: #e0f2fe; color: #0284c7;">
        <i class="fas fa-clipboard-check"></i>
      </div>
      <div>
        <div class="stat-label">Disetujui / Siap Bayar</div>
        <div class="stat-value"><?= $cntVerified ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="inv-stat-card">
      <div class="icon-box" style="background: #dcfce7; color: #16a34a;">
        <i class="fas fa-check-circle"></i>
      </div>
      <div>
        <div class="stat-label">Tagihan Selesai</div>
        <div class="stat-value"><?= $cntPaid ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="inv-stat-card">
      <div class="icon-box" style="background: #f1f5f9; color: #334155;">
        <i class="fas fa-money-bill-wave"></i>
      </div>
      <div>
        <div class="stat-label">Total Diterima</div>
        <div class="stat-value" style="font-size: 16px;">
          Rp <?= number_format($totalPaidRp, 0, ',', '.') ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 2. Main Invoices Table Card -->
<div class="row mb-4">
  <div class="col-12">
    <div class="section-card">

      <!-- Header -->
      <div class="card-header-custom">
        <div>
          <div class="section-title">
            <i class="fas fa-receipt text-primary me-2"></i> Daftar Tagihan Saya (Invoices)
          </div>
          <div class="section-subtitle">Portal pengajuan dan pelacakan invoice honorarium konten ke Brand</div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill" style="font-size: 12px;">
            <?= count($invoices) ?> Tagihan
          </span>
          <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-3 shadow-sm" id="btn-open-create-invoice" style="background-color: #0284c7; border-color: #0284c7;">
            <i class="fas fa-plus-circle me-1"></i> Buat Tagihan Baru
          </button>
        </div>
      </div>

      <!-- Table Body -->
      <div class="p-0">
        <div class="table-responsive">
          <table class="invoice-table">
            <thead>
              <tr>
                <th style="width: 45px; text-align: center;">No</th>
                <th>Nomor Tagihan</th>
                <th>Campaign &amp; Brand</th>
                <th>Tgl Pengajuan</th>
                <th style="text-align: right;">Nilai Jasa (Gross)</th>
                <th style="text-align: right;">Pajak (PPh)</th>
                <th style="text-align: right;">Net Transfer</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center; width: 170px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($invoices)): ?>
                <?php $no = 1; foreach ($invoices as $inv): ?>
                  <?php
                    $status = $inv['status'] ?? 'DRAFT';
                    $taxType = $inv['tax_type'] ?? 'PPh21';
                    $taxPct  = ((float)($inv['tax_rate'] ?? 0)) * 100;
                  ?>
                  <tr>
                    <td style="text-align: center; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                    <td>
                      <div style="font-weight: 700; color: #0284c7;">
                        <?= esc($inv['invoice_number'] ?? ('INV-' . $inv['id'])) ?>
                      </div>
                      <?php if (!empty($inv['invoice_file'])): ?>
                        <small style="color: #64748b; font-size: 11px;">
                          <i class="fas fa-paperclip text-muted me-1"></i> Ada Berkas Fisik
                        </small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div style="font-weight: 600; color: #1e293b;"><?= esc($inv['campaign_title'] ?? 'Campaign') ?></div>
                      <small style="color: #64748b; font-size: 11.5px;">Client: Kolab.id</small>
                    </td>
                    <td style="font-size: 12.5px; color: #475569;">
                      <?= !empty($inv['created_at']) ? date('d M Y', strtotime($inv['created_at'])) : '-' ?>
                    </td>
                    <td style="text-align: right; font-weight: 600; color: #1e293b;">
                      Rp <?= number_format((float)($inv['subtotal'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td style="text-align: right; font-size: 12px; color: #dc2626;">
                      <?php if (!empty($inv['tax_amount']) && $inv['tax_amount'] > 0): ?>
                        - Rp <?= number_format((float)$inv['tax_amount'], 0, ',', '.') ?>
                        <div style="font-size: 10px; color: #64748b;"><?= esc($taxType) ?> (<?= number_format($taxPct, 1) ?>%)</div>
                      <?php else: ?>
                        <span style="color: #94a3b8;">Rp 0</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right; font-weight: 800; color: #16a34a; font-size: 13.5px;">
                      Rp <?= number_format((float)($inv['grand_total'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td style="text-align: center;">
                      <?php if ($status === 'SUBMITTED'): ?>
                        <span class="badge-status badge-status-SUBMITTED">
                          <i class="fas fa-clock"></i> Menunggu Review
                        </span>
                      <?php elseif ($status === 'VERIFIED'): ?>
                        <span class="badge-status badge-status-VERIFIED">
                          <i class="fas fa-check-circle"></i> Disetujui (Siap Bayar)
                        </span>
                      <?php elseif ($status === 'PAID'): ?>
                        <span class="badge-status badge-status-PAID">
                          <i class="fas fa-check-double"></i> Lunas
                        </span>
                      <?php elseif ($status === 'REJECTED'): ?>
                        <span class="badge-status badge-status-REJECTED">
                          <i class="fas fa-times-circle"></i> Ditolak
                        </span>
                      <?php else: ?>
                        <span class="badge-status badge-status-DRAFT">
                          <?= esc($status) ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                      <div class="d-flex align-items-center justify-content-center gap-1">
                        <!-- Detail Button -->
                        <button type="button" class="btn-inv-action btn-inv-detail btn-view-invoice-detail" data-id="<?= (int)$inv['id'] ?>" title="Lihat Riwayat & Detail">
                          <i class="fas fa-eye"></i> Detail
                        </button>
                        <!-- Bukti Potong Button (jika ada) -->
                        <?php if (!empty($inv['bupot_file'])): ?>
                          <a href="<?= base_url($inv['bupot_file']) ?>" target="_blank" class="btn-inv-action btn-inv-bupot" title="Unduh Bukti Potong Pajak (Bupot)">
                            <i class="fas fa-file-invoice"></i> Bupot
                          </a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" style="text-align: center; padding: 50px 20px; color: #94a3b8;">
                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                    <div style="font-weight: 700; color: #1e293b; font-size: 15px; margin-bottom: 4px;">Belum Ada Tagihan yang Diajukan</div>
                    <div style="font-size: 13px; color: #64748b;">Silakan klik tombol <b>"Buat Tagihan Baru"</b> untuk menagih pekerjaan yang telah selesai.</div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL: BUAT TAGIHAN BARU (Create New Invoice) -->
<!-- ============================================================================== -->
<div class="modal fade" id="modal-create-invoice" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.18);">
      <div class="modal-header" style="background: #0f172a; color: #ffffff; padding: 16px 22px;">
        <h5 class="modal-title mb-0 fw-bold" style="font-size: 16px;">
          <i class="fas fa-plus-circle text-primary me-2"></i> Buat &amp; Ajukan Tagihan Baru (Invoice)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="form-create-invoice" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body" style="padding: 24px;">

          <!-- Notice Banner -->
          <div class="alert alert-info d-flex align-items-start gap-2" style="border-radius: 6px; background-color: #f0f9ff; border-color: #bae6fd; color: #0369a1; font-size: 12.5px; margin-bottom: 20px;">
            <i class="fas fa-info-circle mt-1" style="font-size: 15px;"></i>
            <div>
              Sesuai alur penagihan vendor, Anda menerbitkan tagihan resmi kepada Brand. Brand bertindak sebagai Pemotong Pajak resmi (PPh 21/23) dan akan memverifikasi tagihan sebelum mencairkan dana.
            </div>
          </div>

          <!-- Step 1: Select Deal -->
          <div class="mb-3">
            <label class="form-label fw-bold" style="color: #1e293b; font-size: 13px;">
              1. Pilih Pekerjaan / Campaign yang Ingin Ditagihkan <span class="text-danger">*</span>
            </label>
            <select name="campaign_kol_id" id="sel_deal" class="form-select" style="height: 44px; border-radius: 6px;" required>
              <option value="">-- Pilih Kerja Sama yang Memenuhi Syarat --</option>
              <?php if (!empty($billableDeals)): ?>
                <?php foreach ($billableDeals as $bd): ?>
                  <option value="<?= (int)$bd['campaign_kol_id'] ?>"
                          data-amount="<?= (float)$bd['deal_amount'] ?>"
                          data-campaign="<?= esc($bd['campaign_title']) ?>">
                    <?= esc($bd['campaign_title']) ?> — Rp <?= number_format((float)$bd['deal_amount'], 0, ',', '.') ?>
                  </option>
                <?php endforeach; ?>
              <?php else: ?>
                <option value="" disabled>Tidak ada kerja sama aktif yang siap ditagihkan saat ini</option>
              <?php endif; ?>
            </select>
          </div>

          <!-- Tax Simulation Card -->
          <div id="box-calculation-preview" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 10px;">
              <i class="fas fa-calculator text-primary me-1"></i> Rincian Finansial &amp; Estimasi Pajak:
            </div>
            <table style="width: 100%; font-size: 13px;">
              <tr>
                <td style="color: #64748b; padding: 4px 0;">Nilai Kerja Sama (Gross Fee):</td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;" id="lbl_subtotal">Rp 0</td>
              </tr>
              <tr>
                <td style="color: #64748b; padding: 4px 0;">
                  Estimasi Potongan Pajak: <span id="lbl_tax_tag" class="badge bg-secondary-subtle text-dark" style="font-size: 11px;">-</span>
                </td>
                <td style="text-align: right; font-weight: 700; color: #dc2626;" id="lbl_tax_amount">- Rp 0</td>
              </tr>
              <tr>
                <td colspan="2"><div style="border-top: 1px solid #cbd5e1; margin: 8px 0;"></div></td>
              </tr>
              <tr style="font-size: 14.5px;">
                <td style="font-weight: 800; color: #0f172a;">Estimasi Net Transfer Diterima:</td>
                <td style="text-align: right; font-weight: 800; color: #16a34a;" id="lbl_net_amount">Rp 0</td>
              </tr>
            </table>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 8px; font-style: italic;">
              * Brand akan memotong dan menyetorkan pajak penghasilan resmi ke DJP dan mengunggah Bukti Potong ke portal ini.
            </div>
          </div>

          <!-- Step 2: Bank Account Details -->
          <div style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin-top: 20px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">
            2. Informasi Rekening Bank Penerima Dana
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Nama Bank <span class="text-danger">*</span></label>
              <input type="text" name="bank_name" id="in_bank_name" class="form-control form-control-sm" placeholder="Contoh: BCA, Mandiri, BRI" value="<?= esc($kol['bank_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Nomor Rekening <span class="text-danger">*</span></label>
              <input type="text" name="bank_account" id="in_bank_account" class="form-control form-control-sm" placeholder="Nomor rekening" value="<?= esc($kol['bank_account'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label" style="font-size: 12px; font-weight: 600; color: #334155;">Atas Nama (Pemilik Rekening) <span class="text-danger">*</span></label>
              <input type="text" name="bank_holder" id="in_bank_holder" class="form-control form-control-sm" placeholder="Nama pemilik rekening" value="<?= esc($kol['bank_holder'] ?? '') ?>" required>
            </div>
          </div>

          <!-- Step 3: Optional Physical File Attachment -->
          <div style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin-top: 20px; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">
            3. Berkas Invoice Fisik / Bertanda Tangan (Opsional)
          </div>
          <div class="mb-2">
            <label class="form-label" style="font-size: 12px; color: #475569;">
              Jika Anda memiliki berkas invoice mandiri (PDF/Scan bertanda tangan/e-meterai), unggah di sini. Bila tidak diunggah, sistem mencatat pengajuan secara digital.
            </label>
            <input type="file" name="invoice_file" id="in_invoice_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
            <span class="text-muted" style="font-size: 11px;">Format: PDF, JPG, PNG (Maks 5 MB)</span>
          </div>

        </div>

        <div class="modal-footer" style="background: #fafbfc; border-top: 1px solid #e2e8f0; padding: 14px 22px;">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 py-2 rounded-3" id="btn-submit-invoice" style="background-color: #0284c7; border-color: #0284c7;">
            <i class="fas fa-paper-plane me-1"></i> Ajukan Tagihan Sekarang
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL: DETAIL INVOICE & AUDIT TIMELINE -->
<!-- ============================================================================== -->
<div class="modal fade" id="modal-invoice-detail" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 8px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.18);">
      <div class="modal-header" style="background: #0f172a; color: #ffffff; padding: 16px 22px;">
        <h5 class="modal-title mb-0 fw-bold" style="font-size: 16px;">
          <i class="fas fa-file-invoice text-primary me-2"></i> Detail Tagihan: <span id="dt_invoice_no"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" style="padding: 24px;">

        <!-- Rejection Alert Banner -->
        <div id="dt_box_rejected" class="alert alert-danger" style="display: none; border-radius: 6px;">
          <div style="font-weight: 700; font-size: 14px; margin-bottom: 4px;">
            <i class="fas fa-times-circle me-1"></i> Tagihan Ditolak oleh Tim Finance
          </div>
          <div id="dt_rejection_reason" style="font-size: 12.5px;"></div>
        </div>

        <!-- Summary Info Grid -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <table class="table table-bordered mb-0" style="font-size: 12.5px;">
              <tr>
                <td style="width: 42%; background: #f8fafc; font-weight: 600;">Campaign</td>
                <td id="dt_campaign_name" style="font-weight: 700; color: #1e293b;">-</td>
              </tr>
              <tr>
                <td style="background: #f8fafc; font-weight: 600;">Brand / Client</td>
                <td>Kolab.id</td>
              </tr>
              <tr>
                <td style="background: #f8fafc; font-weight: 600;">Tgl Diajukan</td>
                <td id="dt_created_at">-</td>
              </tr>
              <tr>
                <td style="background: #f8fafc; font-weight: 600;">Status</td>
                <td id="dt_status_badge">-</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-bordered mb-0" style="font-size: 12.5px;">
              <tr>
                <td style="width: 42%; background: #f8fafc; font-weight: 600;">Gross (Subtotal)</td>
                <td id="dt_subtotal" style="font-weight: 700;">Rp 0</td>
              </tr>
              <tr>
                <td style="background: #f8fafc; font-weight: 600;">Potongan Pajak</td>
                <td id="dt_tax" style="color: #dc2626; font-weight: 600;">Rp 0</td>
              </tr>
              <tr>
                <td style="background: #f8fafc; font-weight: 600;">Net Transfer</td>
                <td id="dt_grand_total" style="font-weight: 800; color: #16a34a; font-size: 14px;">Rp 0</td>
              </tr>
              <tr>
                <td style="background: #f8fafc; font-weight: 600;">Bukti Potong (Bupot)</td>
                <td id="dt_bupot_status"><span class="badge bg-secondary-subtle text-secondary">Belum Diterbitkan</span></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Bank Destination Info -->
        <div style="background: #f1f5f9; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; font-size: 12.5px;">
          <div style="font-weight: 700; color: #334155; margin-bottom: 4px;">
            <i class="fas fa-university me-1 text-primary"></i> Rekening Tujuan Pembayaran:
          </div>
          <div id="dt_bank_info" style="color: #0f172a; font-weight: 600;">-</div>
        </div>

        <!-- Audit Trail Timeline -->
        <div style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">
          <i class="fas fa-history text-primary me-1"></i> Riwayat &amp; Status Log (Audit Trail):
        </div>
        <ul class="timeline-audit" id="dt_histories_list">
          <li style="color: #94a3b8; font-style: italic;">Memuat riwayat log...</li>
        </ul>

      </div>

      <div class="modal-footer" style="background: #fafbfc; border-top: 1px solid #e2e8f0; padding: 14px 22px;">
        <a href="#" id="dt_btn_download_file" target="_blank" class="btn btn-outline-danger btn-sm" style="display: none;">
          <i class="fas fa-file-pdf me-1"></i> Unduh Berkas Lampiran
        </a>
        <a href="#" id="dt_btn_download_bupot" target="_blank" class="btn btn-success btn-sm" style="display: none;">
          <i class="fas fa-certificate me-1"></i> Unduh Bukti Potong Pajak
        </a>
        <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
    'use strict';

    // 1. Open create invoice modal
    var btnOpen = document.getElementById('btn-open-create-invoice');
    if (btnOpen) {
        btnOpen.addEventListener('click', function() {
            var form = document.getElementById('form-create-invoice');
            if (form) form.reset();
            var boxCalc = document.getElementById('box-calculation-preview');
            if (boxCalc) boxCalc.style.display = 'none';

            var modalEl = document.getElementById('modal-create-invoice');
            new bootstrap.Modal(modalEl).show();
        });
    }

    // 2. Deal selection change -> Calculate tax simulation
    var selDeal = document.getElementById('sel_deal');
    if (selDeal) {
        selDeal.addEventListener('change', function() {
            var dealId = this.value;
            var boxCalc = document.getElementById('box-calculation-preview');
            if (!dealId) {
                if (boxCalc) boxCalc.style.display = 'none';
                return;
            }

            fetch('<?= base_url('my-invoices/deal-detail/') ?>' + dealId)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success && res.deal) {
                        var d = res.deal;
                        document.getElementById('lbl_subtotal').textContent = 'Rp ' + d.subtotal_fmt;
                        document.getElementById('lbl_tax_tag').textContent = d.tax_type + ' ' + d.tax_rate_pct + '% (' + (d.has_npwp ? 'NPWP: ' + d.npwp : 'Non-NPWP') + ')';
                        document.getElementById('lbl_tax_amount').textContent = '- Rp ' + d.tax_amount_fmt;
                        document.getElementById('lbl_net_amount').textContent = 'Rp ' + d.net_amount_fmt;

                        if (d.bank_name) document.getElementById('in_bank_name').value = d.bank_name;
                        if (d.bank_account) document.getElementById('in_bank_account').value = d.bank_account;
                        if (d.bank_holder) document.getElementById('in_bank_holder').value = d.bank_holder;

                        if (boxCalc) boxCalc.style.display = 'block';
                    } else {
                        if (boxCalc) boxCalc.style.display = 'none';
                        alert(res.message || 'Gagal memuat rincian kerja sama.');
                    }
                })
                .catch(function() {
                    if (boxCalc) boxCalc.style.display = 'none';
                });
        });
    }

    // 3. Submit invoice form
    var formCreate = document.getElementById('form-create-invoice');
    if (formCreate) {
        formCreate.addEventListener('submit', function(e) {
            e.preventDefault();

            var btnSubmit = document.getElementById('btn-submit-invoice');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';

            var formData = new FormData(this);

            fetch('<?= base_url('my-invoices/submit') ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Ajukan Tagihan Sekarang';

                if (res.success) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Terjadi kesalahan saat mengajukan tagihan.');
                }
            })
            .catch(function() {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Ajukan Tagihan Sekarang';
                alert('Gagal menghubungi server. Periksa koneksi Anda.');
            });
        });
    }

    // 4. View Invoice Detail Modal
    document.querySelectorAll('.btn-view-invoice-detail').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            if (!id) return;

            fetch('<?= base_url('my-invoices/detail/') ?>' + id)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success || !res.invoice) {
                        alert(res.message || 'Gagal memuat detail invoice.');
                        return;
                    }

                    var inv = res.invoice;
                    var hist = res.histories || [];

                    document.getElementById('dt_invoice_no').textContent = inv.invoice_number || ('INV-' + inv.id);
                    document.getElementById('dt_campaign_name').textContent = inv.campaign_title || '-';
                    document.getElementById('dt_created_at').textContent = inv.created_at || '-';

                    var statusMap = {
                        'SUBMITTED': '<span class="badge-status badge-status-SUBMITTED"><i class="fas fa-clock"></i> Menunggu Review</span>',
                        'VERIFIED': '<span class="badge-status badge-status-VERIFIED"><i class="fas fa-check-circle"></i> Disetujui (Siap Bayar)</span>',
                        'PAID': '<span class="badge-status badge-status-PAID"><i class="fas fa-check-double"></i> Lunas</span>',
                        'REJECTED': '<span class="badge-status badge-status-REJECTED"><i class="fas fa-times-circle"></i> Ditolak</span>',
                        'DRAFT': '<span class="badge-status badge-status-DRAFT">Draft</span>'
                    };
                    document.getElementById('dt_status_badge').innerHTML = statusMap[inv.status] || inv.status;

                    document.getElementById('dt_subtotal').textContent = 'Rp ' + Number(inv.subtotal).toLocaleString('id-ID');
                    if (Number(inv.tax_amount) > 0) {
                        var pct = Number(inv.tax_rate) * 100;
                        document.getElementById('dt_tax').textContent = '- Rp ' + Number(inv.tax_amount).toLocaleString('id-ID') + ' (' + (inv.tax_type || 'PPh21') + ' ' + pct.toFixed(1) + '%)';
                    } else {
                        document.getElementById('dt_tax').textContent = 'Rp 0';
                    }
                    document.getElementById('dt_grand_total').textContent = 'Rp ' + Number(inv.grand_total).toLocaleString('id-ID');

                    var bupotBtn = document.getElementById('dt_btn_download_bupot');
                    if (inv.bupot_file) {
                        document.getElementById('dt_bupot_status').innerHTML = '<span class="badge bg-success-subtle text-success"><i class="fas fa-check me-1"></i> Tersedia</span>';
                        bupotBtn.href = '<?= base_url() ?>' + inv.bupot_file;
                        bupotBtn.style.display = 'inline-flex';
                    } else {
                        document.getElementById('dt_bupot_status').innerHTML = '<span class="badge bg-secondary-subtle text-secondary">Belum Diterbitkan</span>';
                        bupotBtn.style.display = 'none';
                    }

                    var fileBtn = document.getElementById('dt_btn_download_file');
                    if (inv.invoice_file) {
                        fileBtn.href = '<?= base_url() ?>' + inv.invoice_file;
                        fileBtn.style.display = 'inline-flex';
                    } else {
                        fileBtn.style.display = 'none';
                    }

                    document.getElementById('dt_bank_info').textContent =
                        (inv.bank_name || '-') + ' — ' + (inv.bank_account || '-') + ' a/n ' + (inv.bank_holder || '-');

                    // Rejection banner
                    var boxRej = document.getElementById('dt_box_rejected');
                    if (inv.status === 'REJECTED') {
                        boxRej.style.display = 'block';
                        document.getElementById('dt_rejection_reason').textContent =
                            'Alasan Penolakan: ' + (inv.rejection_reason || 'Tidak ada alasan khusus dicantumkan.');
                    } else {
                        boxRej.style.display = 'none';
                    }

                    // Timeline histories
                    var hHtml = '';
                    if (hist.length > 0) {
                        for (var j = 0; j < hist.length; j++) {
                            hHtml += '<li>' +
                                '<div class="circle-dot"></div>' +
                                '<div class="time">' + (hist[j].created_at || '-') + ' &bull; ' + (hist[j].actor_name || 'Sistem') + '</div>' +
                                '<div class="action-title">' + (hist[j].to_status || 'UPDATE') + '</div>' +
                                '<div class="notes">' + (hist[j].notes || '') + '</div>' +
                            '</li>';
                        }
                    } else {
                        hHtml = '<li style="color: #94a3b8; font-style: italic;">Belum ada catatan log.</li>';
                    }
                    document.getElementById('dt_histories_list').innerHTML = hHtml;

                    new bootstrap.Modal(document.getElementById('modal-invoice-detail')).show();
                })
                .catch(function() {
                    alert('Gagal mengambil data detail invoice.');
                });
        });
    });

})();
</script>

<?= $this->endSection() ?>
