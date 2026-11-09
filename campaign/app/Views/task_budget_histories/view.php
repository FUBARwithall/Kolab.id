<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-file-invoice-dollar text-success me-2"></i>Detail Penyesuaian Anggaran Task #<?= $history['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Rincian audit penyesuaian tarif konten, justifikasi alasan perubahan, dan pihak pengesah.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('task-budget-histories') ?>" class="text-decoration-none">Budget Histories</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $history['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php
      $curr = esc($history['currency_code'] ?? 'IDR');
      $oldRate = (float)$history['old_rate'];
      $newRate = (float)$history['new_rate'];
      $diff = $newRate - $oldRate;

      if ($diff > 0) {
        $pct = ($oldRate > 0) ? round(($diff / $oldRate) * 100, 1) : 100;
        $badgeClass = 'bg-success';
        $badgeText = 'Kenaikan Tarif (+' . $curr . ' ' . number_format($diff, 0, ',', '.') . ' / +' . $pct . '%)';
        $iconClass = 'fas fa-arrow-trend-up';
      } elseif ($diff < 0) {
        $pct = ($oldRate > 0) ? round((abs($diff) / $oldRate) * 100, 1) : 100;
        $badgeClass = 'bg-danger';
        $badgeText = 'Penurunan Tarif (-' . $curr . ' ' . number_format(abs($diff), 0, ',', '.') . ' / -' . $pct . '%)';
        $iconClass = 'fas fa-arrow-trend-down';
      } else {
        $badgeClass = 'bg-secondary';
        $badgeText = 'Tarif Tetap (0%)';
        $iconClass = 'fas fa-equals';
      }
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm <?= $badgeClass ?>" style="width: 52px; height: 52px; font-size: 1.25rem;">
              <i class="<?= $iconClass ?>"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark"><?= esc($history['task_title'] ?: ('Task #' . ($history['task_number'] ?? $history['task_id']))) ?></h4>
                <span class="badge <?= $badgeClass ?> px-2 py-1"><?= $badgeText ?></span>
              </div>
              <small class="text-muted">
                Kampanye: <strong><?= esc($history['campaign_title'] ?? 'Global') ?></strong> &bull; Talent: <strong class="text-primary"><?= esc($history['kol_name'] ?? 'KOL') ?></strong> &bull; Dibuat: <?= date('d M Y, H:i:s', strtotime($history['created_at'])) ?> WIB
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('task-budget-histories/single-pdf/' . $history['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar Audit PDF
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteHistory(<?= $history['id'] ?>)">
              <i class="fas fa-trash me-1"></i> Hapus Riwayat
            </button>
            <a href="<?= base_url('task-budget-histories') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Details -->
      <div class="col-lg-6">

        <!-- Informasi Task Konten -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks text-primary me-2"></i>Informasi Task Konten</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">ID Task</td>
                    <td><span class="font-monospace fw-bold text-dark">#<?= $history['task_id'] ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Judul Task</td>
                    <td><strong class="text-dark"><?= esc($history['task_title'] ?: ('Task #' . ($history['task_number'] ?? $history['task_id']))) ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Status Task Saat Ini</td>
                    <td><span class="badge bg-light text-dark border"><?= esc($history['task_status'] ?? 'DRAFT') ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Format Konten</td>
                    <td><?= esc($history['content_type'] ?? 'Short Form Video') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Tarif Terkini di Sistem</td>
                    <td><strong class="text-success"><?= $curr ?> <?= number_format((float)($history['current_task_rate'] ?? $newRate), 0, ',', '.') ?></strong></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Tenggat Waktu (Deadline)</td>
                    <td><?= !empty($history['task_deadline']) ? date('d F Y', strtotime($history['task_deadline'])) : '<span class="text-muted fst-italic">Belum ditentukan</span>' ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Informasi Kampanye & Talent -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-bullhorn text-info me-2"></i>Kampanye &amp; Talent KOL</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 170px;">Nama Kampanye</td>
                    <td>
                      <div class="fw-bold text-dark"><?= esc($history['campaign_title'] ?? 'Global') ?></div>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Talent KOL</td>
                    <td>
                      <div class="fw-bold text-dark"><?= esc($history['kol_name'] ?? 'KOL') ?></div>
                      <small class="text-muted"><?= esc($history['kol_email'] ?? '-') ?> &bull; <?= esc($history['kol_phone'] ?? '-') ?></small>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Otorisasi Oleh</td>
                    <td>
                      <div class="fw-bold text-dark"><i class="fas fa-user-shield text-info me-1"></i><?= esc($history['authorized_name'] ?? 'System') ?></div>
                      <small class="text-muted"><?= esc($history['authorized_email'] ?? '-') ?></small>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Penyesuaian</td>
                    <td><?= date('d F Y, H:i:s', strtotime($history['created_at'])) ?> WIB</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Rate Comparison & Justification -->
      <div class="col-lg-6">

        <!-- Komparasi Tarif -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-calculator text-warning me-2"></i>Komparasi Nilai Tarif &amp; Fee</h5>
          </div>
          <div class="card-body p-4">
            <div class="row align-items-center g-3 text-center">
              <div class="col-5">
                <span class="text-xs text-uppercase text-muted fw-bold d-block mb-1">Tarif Semula</span>
                <div class="fs-4 fw-bold text-muted text-decoration-line-through">
                  <?= $curr ?> <?= number_format($oldRate, 0, ',', '.') ?>
                </div>
              </div>
              <div class="col-2">
                <i class="fas fa-arrow-right fa-2x text-muted"></i>
              </div>
              <div class="col-5">
                <span class="text-xs text-uppercase text-muted fw-bold d-block mb-1">Tarif Baru Disetujui</span>
                <div class="fs-3 fw-bold text-dark">
                  <?= $curr ?> <?= number_format($newRate, 0, ',', '.') ?>
                </div>
              </div>
            </div>

            <div class="alert <?= $diff >= 0 ? 'alert-success' : 'alert-danger' ?> border-0 mt-4 mb-0 text-center rounded-3 p-3">
              <div class="fw-bold fs-6">
                <i class="<?= $iconClass ?> me-1"></i> Selisih Nilai Anggaran: <?= ($diff > 0 ? '+' : '') . $curr . ' ' . number_format($diff, 0, ',', '.') ?>
              </div>
              <div class="small mt-1 opacity-75">
                <?= $diff > 0 ? 'Terjadi penambahan kompensasi atau penyesuaian deliverables pada task ini.' : ($diff < 0 ? 'Terjadi efisiensi biaya, potongan bundling, atau negosiasi ulang fee.' : 'Tidak terdapat perubahan nilai nominal tarif.') ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Justifikasi / Alasan Perubahan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-comment-dots text-primary me-2"></i>Alasan &amp; Justifikasi Perubahan</h5>
          </div>
          <div class="card-body p-4">
            <div class="p-3 bg-light rounded-3 border">
              <p class="mb-0 text-dark font-monospace" style="white-space: pre-wrap; font-size: 13px; line-height: 1.6;"><?= esc($history['reason'] ?? 'Tidak ada alasan tertulis yang dicantumkan.') ?></p>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function deleteHistory(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus catatan riwayat penyesuaian anggaran #' + id + '? Tindakan ini tidak dapat dibatalkan.')) return;

  $.ajax({
    url: '<?= base_url('task-budget-histories/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        window.location.href = '<?= base_url('task-budget-histories') ?>';
      } else {
        alert(res.message || 'Gagal menghapus riwayat');
      }
    },
    error: function() {
      alert('Terjadi kesalahan sistem saat menghapus data.');
    }
  });
}
</script>
<?= $this->endSection() ?>
