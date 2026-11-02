<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-star-half-alt text-warning me-2"></i>Detail Riwayat Skor KOL</h3>
        <p class="text-muted small mb-0">Rincian evaluasi dan audit score performa talent influencer.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('score-histories') ?>" class="text-decoration-none">Score Histories</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $history['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <?php
      $scoreVal = (float)$history['score'];
      if ($scoreVal >= 85) {
        $badgeClass = 'bg-success';
        $grade = 'Grade A (Excellent)';
        $analysis = 'Performa talent luar biasa dengan ketercapaian KPI melebihi ekspektasi standar kampanye, respon komunikasi cepat, dan tingkat kepuasan brand sangat tinggi.';
      } elseif ($scoreVal >= 70) {
        $badgeClass = 'bg-primary';
        $grade = 'Grade B (Good)';
        $analysis = 'Performa talent terbukti baik dan konsisten dalam pemenuhan brief, engagement interaksi stabil, serta pengiriman materi tepat waktu.';
      } elseif ($scoreVal >= 55) {
        $badgeClass = 'bg-warning text-dark';
        $grade = 'Grade C (Fair)';
        $analysis = 'Performa cukup memadai namun memerlukan peningkatan pada ketepatan revisi konten atau optimasi engagement rate penonton.';
      } else {
        $badgeClass = 'bg-danger';
        $grade = 'Grade D (Needs Improvement)';
        $analysis = 'Performa di bawah target rata-rata evaluasi. Disarankan pendampingan lebih intensif atau evaluasi ulang sebelum kolaborasi berikutnya.';
      }

      $scoreType = strtoupper((string)$history['score_type']);
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 52px; height: 52px; background: <?= $scoreVal >= 70 ? 'linear-gradient(135deg, #28a745, #20c997)' : ($scoreVal >= 55 ? 'linear-gradient(135deg, #ffc107, #fd7e14)' : 'linear-gradient(135deg, #dc3545, #e83e8c)') ?>;">
              <i class="fas fa-award fa-lg"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark"><?= esc($history['kol_name'] ?? ('KOL #' . $history['kol_id'])) ?></h4>
                <span class="badge <?= $badgeClass ?> px-2 py-1"><?= $grade ?></span>
              </div>
              <small class="text-muted">
                Tipe Evaluasi: <strong><?= $scoreType ?></strong> &bull; Perhitungan: <?= date('d M Y, H:i:s', strtotime($history['calculated_at'])) ?> WIB
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('score-histories/single-pdf/' . $history['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar Audit PDF
            </a>
            <a href="<?= base_url('kols/view/' . $history['kol_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
              <i class="fas fa-user me-1"></i> Buka Profil KOL
            </a>
            <a href="<?= base_url('score-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Score Breakdown & Campaign Details -->
      <div class="col-lg-8">

        <!-- Score Gauge & Overview -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tachometer-alt text-primary me-2"></i>Hasil Penilaian &amp; Evaluasi</h5>
          </div>
          <div class="card-body p-4">
            <div class="row align-items-center g-4">
              <div class="col-md-5 text-center border-end">
                <span class="text-xs text-uppercase text-muted fw-bold">Skor Akhir Evaluasi</span>
                <div class="display-3 fw-bold my-2 text-<?= str_contains($badgeClass, 'warning') ? 'warning' : (str_contains($badgeClass, 'danger') ? 'danger' : (str_contains($badgeClass, 'primary') ? 'primary' : 'success')) ?>">
                  <?= number_format($scoreVal, 2) ?>
                </div>
                <span class="badge <?= $badgeClass ?> fs-6 px-3 py-1 mb-2"><?= $grade ?></span>
                <div class="progress mx-auto mt-2" style="height: 8px; max-width: 200px;">
                  <div class="progress-bar bg-<?= str_contains($badgeClass, 'warning') ? 'warning' : (str_contains($badgeClass, 'danger') ? 'danger' : (str_contains($badgeClass, 'primary') ? 'primary' : 'success')) ?>" role="progressbar" style="width: <?= min(100, max(0, $scoreVal)) ?>%;"></div>
                </div>
                <div class="text-muted small mt-2">Rentang evaluasi skor: 0.00 - 100.00</div>
              </div>

              <div class="col-md-7">
                <h6 class="fw-bold text-dark mb-2">Interpretasi &amp; Catatan Sistem:</h6>
                <div class="p-3 bg-light rounded-3 border mb-3 small text-secondary" style="line-height: 1.6;">
                  <?= $analysis ?>
                </div>
                <div class="row g-2 text-xs">
                  <div class="col-6">
                    <div class="p-2 border rounded bg-white">
                      <span class="text-muted d-block">Metode Kalkulasi:</span>
                      <strong class="text-dark"><?= $scoreType === 'COLLABORATION' ? 'Agregasi Kampanye' : 'Akumulasi Historis Lifetime' ?></strong>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-2 border rounded bg-white">
                      <span class="text-muted d-block">Status Partisipasi:</span>
                      <strong class="text-dark"><?= esc($history['dealing_status'] ?? 'Active') ?></strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Campaign Association -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bullhorn text-primary me-2"></i>Konteks Kampanye Terkait</h5>
          </div>
          <div class="card-body p-4">
            <?php if (!empty($history['campaign_title'])): ?>
              <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light">
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center p-3">
                    <i class="fas fa-bullhorn fa-lg"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-0 text-dark"><?= esc($history['campaign_title']) ?></h5>
                    <small class="text-muted">ID Kampanye: #<?= esc($history['campaign_id'] ?? '-') ?> &bull; ID Partisipasi KOL: #<?= esc($history['campaign_kol_id'] ?? '-') ?></small>
                  </div>
                </div>
                <div>
                  <a href="<?= base_url('campaigns/view/' . $history['campaign_id']) ?>" class="btn btn-outline-primary btn-sm rounded-2">
                    <i class="fas fa-external-link-alt me-1"></i> Buka Kampanye
                  </a>
                </div>
              </div>
            <?php else: ?>
              <div class="p-3 border rounded-3 bg-light text-center py-4">
                <i class="fas fa-globe text-secondary fa-2x mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Skor Evaluasi Lifetime (Global)</h6>
                <p class="text-muted small mb-0">Skor ini merupakan skor reputasi akumulatif menyeluruh sepanjang masa dan tidak terikat pada satu kampanye tunggal.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Right Column: KOL Profile & Audit Metadata -->
      <div class="col-lg-4">

        <!-- KOL Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-circle text-primary me-2"></i>Profil Talent (KOL)</h5>
          </div>
          <div class="card-body p-4 text-center">
            <div class="avatar-xl bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 fw-bold fs-2" style="width: 72px; height: 72px;">
              <?= strtoupper(substr(esc($history['kol_name'] ?? 'K'), 0, 1)) ?>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= esc($history['kol_name'] ?? ('KOL #' . $history['kol_id'])) ?></h5>
            <p class="text-muted small mb-3"><?= esc($history['kol_email'] ?? 'Tidak ada email') ?></p>

            <ul class="list-group list-group-flush text-start small mb-3">
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">ID Talent:</span>
                <span class="fw-bold">#<?= $history['kol_id'] ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">WhatsApp / No HP:</span>
                <span class="fw-semibold"><?= esc($history['kol_whatsapp'] ?: ($history['kol_phone'] ?: '-')) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Status Akun:</span>
                <span class="badge bg-success"><?= esc($history['kol_status'] ?? 'Active') ?></span>
              </li>
            </ul>

            <a href="<?= base_url('kols/view/' . $history['kol_id']) ?>" class="btn btn-outline-primary btn-sm w-100 rounded-2">
              <i class="fas fa-id-badge me-1"></i> Lihat Direktori Lengkap
            </a>
          </div>
        </div>

        <!-- Audit Metadata -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle text-info me-2"></i>Metadata Audit</h5>
          </div>
          <div class="card-body p-4">
            <ul class="list-group list-group-flush small">
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">ID Riwayat:</span>
                <span class="fw-bold">#<?= $history['id'] ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Tipe Skor:</span>
                <span class="fw-bold text-dark"><?= $scoreType ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Tanggal Perhitungan:</span>
                <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($history['calculated_at'])) ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0 py-2">
                <span class="text-muted">Waktu Tepat:</span>
                <span class="fw-semibold text-dark"><?= date('H:i:s', strtotime($history['calculated_at'])) ?> WIB</span>
              </li>
            </ul>

            <div class="mt-4 pt-3 border-top">
              <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-2" onclick="confirmDeleteScore()">
                <i class="fas fa-trash-alt me-1"></i> Hapus Riwayat Ini
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmDeleteScore() {
  Swal.fire({
    title: 'Hapus Riwayat Skor?',
    text: 'Data riwayat skor #<?= $history['id'] ?> akan dihapus permanen dari sistem.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '<?= base_url('score-histories/delete/' . $history['id']) ?>';
    }
  });
}
</script>
<?= $this->endSection() ?>
