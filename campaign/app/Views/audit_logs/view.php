<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-history text-secondary me-2"></i>Detail Audit Log #<?= $log['id'] ?>
        </h3>
        <p class="text-muted small mb-0">Inspeksi jejak audit, metadata operator, alamat IP, dan komparasi nilai data (diff).</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('audit-logs') ?>" class="text-decoration-none">Audit Trail</a></li>
          <li class="breadcrumb-item active" aria-current="page">#<?= $log['id'] ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <?php
      // Action badge styling
      $actBadge = '<span class="badge bg-light text-dark border px-2 py-1">' . esc($log['action']) . '</span>';
      switch ($log['action']) {
        case 'CREATE':
          $actBadge = '<span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-plus me-1"></i>CREATE</span>';
          break;
        case 'UPDATE':
          $actBadge = '<span class="badge bg-info px-3 py-2 fs-6"><i class="fas fa-edit me-1"></i>UPDATE</span>';
          break;
        case 'DELETE':
          $actBadge = '<span class="badge bg-danger px-3 py-2 fs-6"><i class="fas fa-trash-alt me-1"></i>DELETE</span>';
          break;
        case 'APPROVE':
          $actBadge = '<span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-check-circle me-1"></i>APPROVE</span>';
          break;
        case 'REJECT':
          $actBadge = '<span class="badge bg-danger px-3 py-2 fs-6"><i class="fas fa-times-circle me-1"></i>REJECT</span>';
          break;
        case 'VERIFY':
          $actBadge = '<span class="badge bg-primary px-3 py-2 fs-6"><i class="fas fa-shield-alt me-1"></i>VERIFY</span>';
          break;
        case 'PAYMENT':
          $actBadge = '<span class="badge bg-dark px-3 py-2 fs-6"><i class="fas fa-money-bill-wave me-1"></i>PAYMENT</span>';
          break;
        case 'LOGIN':
          $actBadge = '<span class="badge bg-secondary px-3 py-2 fs-6"><i class="fas fa-sign-in-alt me-1"></i>LOGIN</span>';
          break;
        case 'LOGOUT':
          $actBadge = '<span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fas fa-sign-out-alt me-1"></i>LOGOUT</span>';
          break;
      }
    ?>

    <!-- Top Action Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-primary fw-bold shadow-sm" style="width: 52px; height: 52px; font-size: 1.25rem;">
              <i class="fas fa-fingerprint"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">Log Record #<?= $log['id'] ?></h4>
                <?= $actBadge ?>
              </div>
              <small class="text-muted">
                Entitas: <strong class="text-dark font-monospace text-uppercase"><?= esc($log['entity_type']) ?> #<?= $log['entity_id'] ?></strong> &bull; Dicatat: <?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?> WIB
              </small>
            </div>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= base_url('audit-logs/single-pdf/' . $log['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
              <i class="fas fa-print me-1"></i> Cetak Lembar Audit PDF
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-2" onclick="deleteLog(<?= $log['id'] ?>)">
              <i class="fas fa-trash me-1"></i> Hapus Log
            </button>
            <a href="<?= base_url('audit-logs') ?>" class="btn btn-secondary btn-sm rounded-2">
              <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Log
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Metadata & Operator -->
      <div class="col-lg-5">

        <!-- Info Entitas & Tindakan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-tag text-primary me-2"></i>Parameter Tindakan &amp; Entitas</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 150px;">ID Log</td>
                    <td><span class="font-monospace fw-bold text-dark">#<?= $log['id'] ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Jenis Aksi</td>
                    <td><span class="font-monospace fw-bold"><?= esc($log['action']) ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Tipe Entitas</td>
                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace text-uppercase"><?= esc($log['entity_type']) ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Target Entity ID</td>
                    <td><span class="font-monospace fw-bold text-primary">#<?= $log['entity_id'] ?></span></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Waktu Kejadian</td>
                    <td><?= date('d F Y, H:i:s', strtotime($log['created_at'])) ?> WIB</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Info Operator / Pengguna -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-shield text-info me-2"></i>Operator Pelaksana</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 150px;">Nama Pengguna</td>
                    <td>
                      <div class="fw-bold text-dark"><?= esc($log['user_name'] ?? 'System / Automated') ?></div>
                      <?php if (!empty($log['user_id'])): ?>
                        <small class="text-muted">User ID: #<?= $log['user_id'] ?></small>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Email Operator</td>
                    <td><?= esc($log['user_email'] ?? '-') ?></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">Role Jabatan</td>
                    <td>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <?= esc($log['role_label'] ?? ($log['role_name'] ?? 'Operator')) ?>
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Info Jaringan & Browser -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-network-wired text-secondary me-2"></i>Jaringan &amp; Perangkat Client</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped align-middle mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted fw-semibold" style="width: 150px;">Alamat IP</td>
                    <td><code class="text-dark fw-bold"><?= esc($log['ip_address'] ?? '127.0.0.1') ?></code></td>
                  </tr>
                  <tr>
                    <td class="text-muted fw-semibold">User Agent</td>
                    <td class="small text-muted font-monospace" style="word-break: break-all;"><?= esc($log['user_agent'] ?? '-') ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Diff Comparison Panels -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-columns text-warning me-2"></i>Komparasi Perubahan Data (Diff)</h5>
            <div class="btn-group">
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyData('old')">
                <i class="far fa-copy me-1"></i> Salin Old
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyData('new')">
                <i class="far fa-copy me-1"></i> Salin New
              </button>
            </div>
          </div>
          <div class="card-body p-3">

            <div class="row g-3">
              <!-- Old Values -->
              <div class="col-md-6">
                <div class="card border border-danger-subtle h-100">
                  <div class="card-header bg-danger-subtle text-danger fw-semibold py-2 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-minus-circle me-1"></i> Nilai Sebelumnya (Old)</span>
                  </div>
                  <div class="card-body p-0">
                    <pre id="preOldValues" class="p-3 mb-0 font-monospace small bg-white text-dark" style="max-height: 480px; overflow-y: auto; font-size: 11.5px;"><?= !empty($formattedOld) ? esc($formattedOld) : '<span class="text-muted fst-italic">— (Tidak ada data sebelumnya / Record baru) —</span>' ?></pre>
                  </div>
                </div>
              </div>

              <!-- New Values -->
              <div class="col-md-6">
                <div class="card border border-success-subtle h-100">
                  <div class="card-header bg-success-subtle text-success fw-semibold py-2 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-plus-circle me-1"></i> Nilai Baru (New)</span>
                  </div>
                  <div class="card-body p-0">
                    <pre id="preNewValues" class="p-3 mb-0 font-monospace small bg-white text-dark" style="max-height: 480px; overflow-y: auto; font-size: 11.5px;"><?= !empty($formattedNew) ? esc($formattedNew) : '<span class="text-muted fst-italic">— (Tidak ada nilai baru / Record dihapus) —</span>' ?></pre>
                  </div>
                </div>
              </div>
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
function copyData(type) {
  const text = (type === 'old') ? $('#preOldValues').text() : $('#preNewValues').text();
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(function() {
      alert('Teks ' + (type === 'old' ? 'Old Values' : 'New Values') + ' berhasil disalin!');
    });
  } else {
    const temp = $('<textarea>');
    $('body').append(temp);
    temp.val(text).select();
    document.execCommand('copy');
    temp.remove();
    alert('Teks berhasil disalin!');
  }
}

function deleteLog(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus catatan log audit #' + id + '? Tindakan ini tidak dapat dibatalkan.')) return;

  $.ajax({
    url: '<?= base_url('audit-logs/delete') ?>/' + id,
    type: 'POST',
    dataType: 'json',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    success: function(res) {
      if (res.success) {
        alert(res.message);
        window.location.href = '<?= base_url('audit-logs') ?>';
      } else {
        alert(res.message || 'Gagal menghapus log');
      }
    },
    error: function() {
      alert('Terjadi kesalahan sistem saat menghapus log.');
    }
  });
}
</script>
<?= $this->endSection() ?>
