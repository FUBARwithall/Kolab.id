<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check text-primary me-2"></i>Detail Riwayat Approval KOL</h3>
        <p class="text-muted small mb-0">Rincian catatan keputusan verifikasi dan persetujuan profil talent KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('kol-approval-histories') ?>" class="text-decoration-none">KOL Approval Histories</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $hist['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <?php 
              $act = strtoupper(trim((string)$hist['resolved_action']));
              $badgeClass = match ($act) {
                'APPROVED' => 'bg-success text-white',
                'SUBMITTED', 'PENDING' => 'bg-primary text-white',
                'REJECTED' => 'bg-danger text-white',
                default => 'bg-secondary text-white'
              };
            ?>
            <span class="badge <?= $badgeClass ?> px-3 py-2 fs-6 rounded-pill">
              <?= esc($act) ?>
            </span>
            <div>
              <h4 class="fw-bold mb-0 text-dark"><?= esc($hist['kol_name'] ?? ('KOL #' . $hist['kol_id'])) ?></h4>
              <small class="text-muted">Target KOL ID: #<?= $hist['kol_id'] ?> &bull; Review Dilakukan: <?= date('d M Y H:i:s', strtotime($hist['created_at'])) ?> WIB</small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('kol-approval-histories/single-pdf/' . $hist['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar PDF
            </a>
            <a href="<?= base_url('kols/view/' . $hist['kol_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-external-link-alt me-1"></i> Buka Profil KOL
            </a>
            <a href="<?= base_url('kol-approval-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Reviewer Notes & KOL Profile -->
      <div class="col-lg-8">
        
        <!-- Catatan Reviewer -->
        <?php 
          $alertClass = match ($act) {
            'APPROVED' => 'alert-success border-success-subtle',
            'SUBMITTED', 'PENDING' => 'alert-info border-info-subtle',
            'REJECTED' => 'alert-danger border-danger-subtle',
            default => 'alert-secondary border-secondary-subtle'
          };
        ?>
        <div class="alert <?= $alertClass ?> border shadow-sm rounded-3 mb-4 p-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0"><i class="fas fa-comment-dots me-2"></i>Catatan Reviewer / Leader:</h6>
            <span class="badge <?= $badgeClass ?>"><?= $act ?></span>
          </div>
          <div class="small" style="white-space: pre-line; line-height: 1.6;">
            <?= !empty($hist['resolved_note']) ? esc($hist['resolved_note']) : '<em class="text-muted">Tidak ada catatan review yang dilampirkan.</em>' ?>
          </div>
        </div>

        <!-- Profil Lengkap KOL -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-circle text-primary me-2"></i>Detail Profil Talent KOL</h5>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <small class="text-muted d-block">Nama Lengkap Talent</small>
                <strong class="text-dark fs-6"><?= esc($hist['kol_name'] ?? '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">Nomor Induk Kependudukan (NIK)</small>
                <strong class="text-dark"><?= esc($hist['kol_nik'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">WhatsApp</small>
                <strong class="text-dark"><i class="fab fa-whatsapp text-success me-1"></i><?= esc($hist['kol_whatsapp'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">Email</small>
                <strong class="text-dark"><i class="far fa-envelope text-primary me-1"></i><?= esc($hist['kol_email'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">Kota Domisili</small>
                <strong class="text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($hist['kol_city'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">Nomor Telepon Lain</small>
                <strong class="text-dark"><?= esc($hist['kol_phone'] ?: '-') ?></strong>
              </div>
              <div class="col-12">
                <small class="text-muted d-block">Alamat Lengkap</small>
                <div class="bg-light p-2 rounded text-secondary"><?= esc($hist['kol_address'] ?: '-') ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Data Finansial & Perbankan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-money-check-alt text-success me-2"></i>Informasi Rekening &amp; Pajak</h5>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <small class="text-muted d-block">Bank Penerima</small>
                <strong class="text-dark"><?= esc($hist['bank_name'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">Nomor Rekening</small>
                <strong class="text-dark"><?= esc($hist['bank_account'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">Nama Pemilik Rekening</small>
                <strong class="text-dark"><?= esc($hist['bank_holder'] ?: '-') ?></strong>
              </div>
              <div class="col-md-6">
                <small class="text-muted d-block">NPWP</small>
                <strong class="text-dark"><?= esc($hist['npwp'] ?: '-') ?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Riwayat Review Lainnya untuk KOL Ini -->
        <?php if (!empty($allHistories) && count($allHistories) > 1): ?>
          <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
              <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-primary me-2"></i>Semua Riwayat Review untuk Talent Ini (<?= count($allHistories) ?> Log)</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-3">ID</th>
                      <th>Aksi / Status</th>
                      <th>Catatan Review</th>
                      <th>Reviewer</th>
                      <th>Waktu Review</th>
                      <th class="text-end pe-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allHistories as $oh): ?>
                      <tr class="<?= $oh['id'] == $hist['id'] ? 'table-primary bg-opacity-10 fw-bold' : '' ?>">
                        <td class="ps-3">#<?= $oh['id'] ?></td>
                        <td>
                          <?php
                            $oact = strtoupper(trim((string)$oh['resolved_action']));
                            $obadge = match ($oact) {
                              'APPROVED' => 'bg-success',
                              'SUBMITTED', 'PENDING' => 'bg-primary',
                              'REJECTED' => 'bg-danger',
                              default => 'bg-secondary'
                            };
                          ?>
                          <span class="badge <?= $obadge ?> px-2 py-1"><?= $oact ?></span>
                        </td>
                        <td>
                          <div class="text-truncate" style="max-width: 250px;" title="<?= esc($oh['resolved_note']) ?>">
                            <?= esc($oh['resolved_note'] ?: '-') ?>
                          </div>
                        </td>
                        <td><?= esc($oh['reviewer_name'] ?? 'Reviewer') ?></td>
                        <td><?= date('d M Y H:i', strtotime($oh['created_at'])) ?></td>
                        <td class="text-end pe-3">
                          <?php if ($oh['id'] != $hist['id']): ?>
                            <a href="<?= base_url('kol-approval-histories/view/' . $oh['id']) ?>" class="btn btn-outline-primary btn-xs py-0 px-2 rounded">Buka</a>
                          <?php else: ?>
                            <span class="badge bg-primary text-white">Aktif</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Right Column: Metadata Details -->
      <div class="col-lg-4">
        
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Target &amp; Peninjau</h6>
          </div>
          <div class="card-body p-3">
            <table class="table table-sm table-borderless mb-0">
              <tr>
                <td class="text-muted" style="width: 140px;">Nama Talent KOL</td>
                <td class="fw-bold text-dark"><?= esc($hist['kol_name'] ?? ('KOL #' . $hist['kol_id'])) ?></td>
              </tr>
              <tr>
                <td class="text-muted">Kota</td>
                <td class="fw-semibold text-primary"><?= esc($hist['kol_city'] ?: '-') ?></td>
              </tr>
              <tr>
                <td class="text-muted">Status Akun Terkini</td>
                <td>
                  <span class="badge bg-light text-dark border px-2 py-1"><?= esc($hist['kol_current_status'] ?? 'PENDING') ?></span>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Status Blacklist</td>
                <td>
                  <?php if (!empty($hist['is_blacklisted'])): ?>
                    <span class="badge bg-danger text-white">Blacklisted</span>
                  <?php else: ?>
                    <span class="badge bg-success-subtle text-success">Normal</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Reviewer / Leader</td>
                <td>
                  <div class="fw-semibold text-dark"><i class="fas fa-user-check me-1 text-info"></i><?= esc($hist['reviewer_name'] ?? 'Reviewer') ?></div>
                  <?php if (!empty($hist['reviewer_email'])): ?>
                    <small class="text-muted"><?= esc($hist['reviewer_email']) ?></small>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Aksi / Keputusan</td>
                <td><span class="badge <?= $badgeClass ?> px-2 py-1"><?= $act ?></span></td>
              </tr>
              <tr>
                <td class="text-muted">Waktu Review</td>
                <td><?= date('d M Y H:i:s', strtotime($hist['created_at'])) ?> WIB</td>
              </tr>
            </table>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>
