<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-signature text-primary me-2"></i>Detail Surat Kontrak &amp; MoU</h3>
        <p class="text-muted small mb-0">Rincian kesepakatan kerjasama, status tanda tangan elektronik (E-Sign), dan naskah perjanjian.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('contracts') ?>" class="text-decoration-none">Kontrak</a></li>
          <li class="breadcrumb-item active" aria-current="page">Detail #<?= esc($contract['id']) ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">
    <!-- Top Action Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a href="<?= base_url('contracts') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
      </a>
      <div class="d-flex gap-2">
        <a href="<?= base_url('contracts/print/' . $contract['id']) ?>" target="_blank" class="btn btn-primary btn-sm rounded-2 shadow-sm">
          <i class="fas fa-print me-1"></i> Cetak / Simpan PDF
        </a>
        <a href="<?= base_url('contracts/edit/' . $contract['id']) ?>" class="btn btn-warning btn-sm rounded-2 shadow-sm">
          <i class="fas fa-edit me-1"></i> Edit Kontrak
        </a>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Informasi Kontrak & Naskah Perjanjian -->
      <div class="col-lg-8">
        <!-- Card 1: Ringkasan Kontrak -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Perjanjian</h5>
            <?php
              $st = strtoupper($contract['status'] ?? 'DRAFT');
              $badgeCls = match ($st) {
                  'ACTIVE', 'SIGNED' => 'bg-success',
                  'PENDING', 'WAITING_KOL_SIGNATURE' => 'bg-warning text-dark',
                  'CANCELLED' => 'bg-danger',
                  default => 'bg-secondary'
              };
            ?>
            <span class="badge <?= $badgeCls ?> px-3 py-2 rounded-pill fs-6"><?= $st ?></span>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Nomor Surat Kontrak</div>
                <div class="fw-bold font-monospace fs-5 text-dark">
                  <?= esc($contract['display_contract_no'] ?: ($contract['contract_number'] ?? 'CTR-' . $contract['id'])) ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Nilai Kompensasi / Fee SOW</div>
                <div class="text-success fw-bold fs-4">
                  Rp <?= number_format((float)($contract['deal_amount'] ?? 0), 0, ',', '.') ?>
                  <span class="fs-6 text-muted fw-normal">(<?= esc((string)($contract['total_vt'] ?? 1)) ?> VT)</span>
                </div>
              </div>

              <div class="col-12"><hr class="my-2"></div>

              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Kampanye &amp; Brand</div>
                <div class="fw-bold text-primary fs-6"><?= esc($contract['campaign_title'] ?: ($contract['campaign_name'] ?? '-')) ?></div>
                <div class="small text-muted"><?= esc($contract['brand_name'] ?: 'Brand Partner') ?></div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Talent KOL / Influencer</div>
                <div class="fw-bold text-dark fs-6"><?= esc($contract['kol_name'] ?? '-') ?></div>
                <div class="small text-muted font-monospace">NIK: <?= esc($contract['kol_nik'] ?: '-') ?></div>
              </div>

              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Waktu Dibuat</div>
                <div class="text-dark small">
                  <i class="far fa-calendar-alt text-muted me-1"></i>
                  <?= !empty($contract['created_at']) ? date('d M Y, H:i', strtotime($contract['created_at'])) : '-' ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-bold mb-1">Waktu Tanda Tangan (E-Sign)</div>
                <div class="text-dark small">
                  <i class="fas fa-file-signature text-success me-1"></i>
                  <?= !empty($contract['signed_at']) ? date('d M Y, H:i', strtotime($contract['signed_at'])) : '<span class="text-warning">Belum ditandatangani</span>' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Naskah Naskah Perjanjian (Preview) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-secondary me-2"></i>Naskah Klausul Kontrak (MoU)</h5>
            <a href="<?= base_url('contracts/print/' . $contract['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Tampilan Cetak Legal
            </a>
          </div>
          <div class="card-body p-4 bg-light">
            <div class="p-4 bg-white rounded-2 border shadow-xs" style="max-height: 480px; overflow-y: auto; font-size: 13.5px; line-height: 1.6;">
              <?php
                $clauses = $contract['contract_html'] ?: ($contract['clauses_rendered'] ?? '');
              ?>
              <?php if (str_contains($clauses, '<p>') || str_contains($clauses, '<div>') || str_contains($clauses, '<br')): ?>
                <?= $clauses ?>
              <?php else: ?>
                <?= nl2br(esc($clauses)) ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Card 3: Syarat & Ketentuan (S&K) Khusus -->
        <?php if (!empty($contract['terms_and_conditions'])): ?>
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
              <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check text-info me-2"></i>Syarat &amp; Ketentuan Khusus (S&amp;K)</h5>
            </div>
            <div class="card-body p-4">
              <div class="p-3 bg-light rounded-2 text-dark small" style="white-space: pre-wrap; line-height: 1.6;">
                <?= esc($contract['terms_and_conditions']) ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Tanda Tangan Digital & Dokumen Tagihan -->
      <div class="col-lg-4">
        <!-- Card 4: Tanda Tangan Elektronik -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-signature text-success me-2"></i>Tanda Tangan Digital (E-Sign)</h5>
          </div>
          <div class="card-body p-3">
            <!-- Pihak Pertama / Brand -->
            <div class="mb-4 p-3 border rounded bg-light text-center">
              <div class="fw-bold small text-muted text-uppercase mb-2">Tanda Tangan Brand / Agency</div>
              <?php if (!empty($contract['admin_signature_image']) && is_file(FCPATH . $contract['admin_signature_image'])): ?>
                <img src="<?= base_url($contract['admin_signature_image']) ?>" alt="Admin Signature" class="img-fluid border rounded bg-white p-2 mb-2" style="max-height: 90px; object-fit: contain;">
                <div class="small fw-semibold text-success"><i class="fas fa-check-circle me-1"></i> Ditandatangani Brand</div>
              <?php else: ?>
                <div class="p-3 bg-white rounded border text-muted small">
                  <i class="fas fa-shield-alt text-success fa-2x mb-1 d-block"></i>
                  Terverifikasi Sistem
                </div>
              <?php endif; ?>
            </div>

            <!-- Pihak Kedua / KOL -->
            <div class="p-3 border rounded bg-light text-center">
              <div class="fw-bold small text-muted text-uppercase mb-2">Tanda Tangan Talent KOL</div>
              <?php
                $kolSig = $contract['kol_signature_image'] ?? ($contract['signature_image'] ?? null);
              ?>
              <?php if (!empty($kolSig) && is_file(FCPATH . $kolSig)): ?>
                <img src="<?= base_url($kolSig) ?>" alt="KOL Signature" class="img-fluid border rounded bg-white p-2 mb-2" style="max-height: 90px; object-fit: contain;">
                <div class="small fw-semibold text-success"><i class="fas fa-check-circle me-1"></i> Ditandatangani KOL</div>
                <small class="text-muted d-block"><?= !empty($contract['signed_at']) ? date('d M Y, H:i', strtotime($contract['signed_at'])) : '' ?></small>
              <?php else: ?>
                <div class="p-3 bg-white rounded border text-muted small">
                  <i class="far fa-clock text-warning fa-2x mb-1 d-block"></i>
                  Menunggu Tanda Tangan KOL
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Card 5: Rekening Bank Pencairan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-university text-primary me-2"></i>Rekening Pencairan Honorarium</h5>
          </div>
          <div class="card-body p-3">
            <div class="bg-light p-3 rounded-2 border">
              <div class="text-muted small text-uppercase fw-bold mb-1">Tujuan Transfer:</div>
              <div class="fw-bold text-dark"><?= esc($contract['bank_name'] ?: '-') ?></div>
              <div class="fw-bold font-monospace text-primary fs-6 mt-1"><?= esc($contract['bank_account'] ?: '-') ?></div>
              <div class="small text-muted">a/n <?= esc($contract['bank_holder'] ?: '-') ?></div>
            </div>
          </div>
        </div>

        <!-- Card 6: Tagihan Invoice Terkait -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-warning me-2"></i>Tagihan Invoice Terkait</h5>
          </div>
          <div class="card-body p-3">
            <?php if (!empty($contract['invoice_id'])): ?>
              <div class="p-3 bg-light rounded-2 border mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="font-monospace fw-bold text-dark"><?= esc($contract['invoice_number']) ?></span>
                  <span class="badge bg-primary"><?= esc($contract['invoice_status']) ?></span>
                </div>
                <div class="text-muted small">Total Tagihan:</div>
                <div class="fw-bold text-dark fs-6">Rp <?= number_format((float)($contract['invoice_grand_total'] ?? 0), 0, ',', '.') ?></div>
              </div>
              <div class="d-grid">
                <a href="<?= base_url('invoices/view/' . $contract['invoice_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
                  <i class="fas fa-eye me-1"></i> Buka Detail Invoice
                </a>
              </div>
            <?php else: ?>
              <div class="text-center py-3 text-muted">
                <i class="fas fa-receipt fa-2x mb-2 text-black-50"></i>
                <p class="small mb-2">Belum ada invoice yang diterbitkan untuk kontrak ini.</p>
                <?php if (in_array(strtoupper($contract['status']), ['ACTIVE', 'SIGNED'])): ?>
                  <button type="button" class="btn btn-success btn-sm rounded-2 shadow-sm" onclick="generateInvoiceForContract(<?= $contract['id'] ?>)">
                    <i class="fas fa-paper-plane me-1"></i> Terbitkan Invoice Awal
                  </button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function generateInvoiceForContract(contractId) {
    if (!confirm('Apakah Anda yakin ingin menerbitkan invoice awal untuk kontrak ini?')) {
        return;
    }

    $.post('<?= base_url('contracts/generate_initial_invoice_ajax') ?>', {
        contract_id: contractId,
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
    }, function(res) {
        if (res.success) {
            alert(res.message);
            window.location.reload();
        } else {
            alert(res.message || 'Gagal menerbitkan invoice.');
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON?.message || 'Terjadi kesalahan sistem.');
    });
}
</script>
<?= $this->endSection() ?>
