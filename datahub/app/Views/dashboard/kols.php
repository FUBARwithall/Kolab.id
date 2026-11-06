<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<?php
if (!function_exists('format_metric_number')) {
    function format_metric_number($num) {
        if ($num === null || $num === '') return '0';
        $n = (float)$num;
        if ($n >= 1000000) return round($n / 1000000, 1) . 'M';
        if ($n >= 1000) return round($n / 1000, 1) . 'K';
        return number_format($n);
    }
}

$hasFilters = !empty($activeFilters['q']) || !empty($activeFilters['status']) || !empty($activeFilters['category']);
$filterCount = 0;
if (!empty($activeFilters['q'])) $filterCount++;
if (!empty($activeFilters['status'])) $filterCount++;
if (!empty($activeFilters['category'])) $filterCount++;
?>

<style>
  .kol-list-card {
    background: #ffffff;
    border: 0;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    overflow: hidden;
    margin-bottom: 24px;
  }
  .kol-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e7edf3;
    flex-wrap: wrap;
  }
  .kol-list-title {
    margin: 0;
    color: #172b4d;
    font-size: 20px;
    font-weight: 700;
  }
  .kol-list-subtitle {
    margin-top: 4px;
    color: #6b778c;
    font-size: 13px;
  }
  .kol-count-badge {
    display: inline-block;
    margin-left: 6px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 12px;
  }
  .filter-toolbar-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin: 16px 24px;
    padding: 14px 16px;
  }
  .kol-table thead tr th {
    background: #f6f8fb;
    color: #52606d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    border-bottom: 1px solid #e7edf3;
    padding: 12px 14px;
  }
  .kol-table tbody tr td {
    padding: 12px 14px;
    vertical-align: middle;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
  }
  .platform-pill-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 8px;
    align-items: center;
  }
  .platform-icon-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 28px;
    padding: 0 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    text-decoration: none !important;
    font-size: 12px;
    color: #334155;
    font-weight: 600;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
  }
  .platform-icon-pill:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
  }
  .kol-avatar {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 12px;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
  }
  .kol-name-link {
    font-weight: 700;
    font-size: 13.5px;
    color: #172b4d;
    text-decoration: none;
    display: inline-block;
    line-height: 1.3;
    transition: color 0.15s;
  }
  .kol-name-link:hover {
    color: #2563eb;
    text-decoration: underline;
  }

  /* Compact modern action buttons */
  .btn-action-kol {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    height: 30px;
    padding: 0 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid transparent;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    white-space: nowrap;
    background: #ffffff;
    cursor: pointer;
    box-sizing: border-box;
    line-height: 1;
  }
  .btn-action-kol i {
    font-size: 11.5px;
  }
  .btn-action-card {
    border-color: #bfdbfe;
    color: #1d4ed8;
    background: #eff6ff;
  }
  .btn-action-card:hover {
    background: #1d4ed8;
    color: #ffffff;
    border-color: #1d4ed8;
  }
  .btn-action-detail {
    border-color: #a5f3fc;
    color: #0891b2;
    background: #ecfeff;
  }
  .btn-action-detail:hover {
    background: #0891b2;
    color: #ffffff;
    border-color: #0891b2;
  }
  .btn-action-delete {
    border-color: #fecaca;
    color: #dc2626;
    background: #fef2f2;
    padding: 0 9px;
  }
  .btn-action-delete:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
  }
</style>

<div class="kol-list-card">
  <!-- Header Bar -->
  <div class="kol-list-header">
    <div>
      <h3 class="kol-list-title"><?= esc($title) ?></h3>
      <div class="kol-list-subtitle">
        Kelola profil, metrik platform, dan rate card KOL
        <span class="kol-count-badge"><?= count($kols) ?> Items</span>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($isAdmin): ?>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-add-kol">
          <i class="fas fa-plus me-1"></i> Tambah KOL
        </button>
      <?php endif; ?>
      <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
        <i class="fas fa-file-pdf me-1"></i> Export / Cetak
      </button>
    </div>
  </div>

  <!-- Filter & Pencarian Toolbar (Ala Card-Generator Plugin) -->
  <form method="GET" action="<?= base_url('kols') ?>">
    <div class="filter-toolbar-box">
      <div class="row g-2 align-items-end">
        <div class="col-md-4 col-sm-6">
          <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">
            <i class="fas fa-search me-1"></i> Kata Kunci
          </label>
          <input type="text" class="form-control form-control-sm" name="q" placeholder="Cari nama, kota, wa..." value="<?= esc($activeFilters['q'] ?? '') ?>">
        </div>

        <div class="col-md-3 col-sm-6">
          <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">
            <i class="fas fa-tag me-1 text-primary"></i> Kategori
          </label>
          <select class="form-select form-select-sm" name="category">
            <option value="">Semua Kategori</option>
            <?php foreach ($allCategories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($activeFilters['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                <?= esc($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3 col-sm-6">
          <label class="form-label text-xs fw-bold text-muted text-uppercase mb-1">
            <i class="fas fa-check-circle me-1 text-success"></i> Status Akun
          </label>
          <select class="form-select form-select-sm" name="status">
            <option value="">Semua Status</option>
            <option value="APPROVED" <?= ($activeFilters['status'] ?? '') === 'APPROVED' ? 'selected' : '' ?>>APPROVED</option>
            <option value="PENDING" <?= ($activeFilters['status'] ?? '') === 'PENDING' ? 'selected' : '' ?>>PENDING</option>
            <option value="REJECTED" <?= ($activeFilters['status'] ?? '') === 'REJECTED' ? 'selected' : '' ?>>REJECTED</option>
          </select>
        </div>

        <div class="col-md-2 col-sm-6 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold">
            <i class="fas fa-filter me-1"></i> Filter
          </button>
          <?php if ($hasFilters): ?>
            <a href="<?= base_url('kols') ?>" class="btn btn-outline-danger btn-sm rounded-pill" title="Reset Filter">
              <i class="fas fa-undo"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($hasFilters): ?>
        <div class="mt-2 pt-2 border-top d-flex align-items-center gap-2 text-xs">
          <span class="badge bg-primary rounded-pill"><?= $filterCount ?> Filter Aktif</span>
          <a href="<?= base_url('kols') ?>" class="text-danger fw-semibold text-decoration-none">
            <i class="fas fa-times-circle me-1"></i> Reset Semua Filter
          </a>
        </div>
      <?php endif; ?>
    </div>
  </form>

  <!-- Table Body -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 kol-table" id="table-kols">
      <thead>
        <tr>
          <th style="width: 50px;" class="text-center">ID</th>
          <th style="min-width: 220px;">Profil KOL</th>
          <th style="min-width: 250px;">Kanal Media Sosial</th>
          <th style="width: 120px;">Domisili</th>
          <th style="width: 140px;">Kontak WhatsApp</th>
          <th style="width: 110px;" class="text-center">Status</th>
          <th style="width: 190px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($kols)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <i class="fas fa-search fa-2x mb-2 d-block opacity-25"></i>
              Tidak ada data KOL yang sesuai dengan filter pencarian.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($kols as $k): ?>
            <tr>
              <td class="text-center fw-bold text-muted">#<?= $k['id'] ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="kol-avatar">
                    <?= strtoupper(substr($k['name'], 0, 1)) ?>
                  </div>
                  <div style="min-width: 0;">
                    <a href="javascript:;" class="kol-name-link" onclick='showKolDetail(<?= json_encode($k) ?>)'>
                      <?= esc($k['name']) ?>
                    </a>
                    <div class="d-flex align-items-center gap-2 mt-1 text-muted" style="font-size: 11.5px;">
                      <span><i class="far fa-calendar-alt me-1 text-muted"></i>Bergabung: <?= date('d M Y', strtotime($k['created_at'] ?? 'now')) ?></span>
                      <?php if (!empty($k['avg_rating'])): ?>
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-1.5 py-0.5 text-xs">
                          <i class="fas fa-star text-warning me-0.5"></i> <?= $k['avg_rating'] ?> (<?= $k['review_count'] ?>)
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <?php if (!empty($platformsByKol[$k['id']])): ?>
                  <div class="platform-pill-container">
                    <?php foreach ($platformsByKol[$k['id']] as $pk): ?>
                      <?php
                        $pName = strtolower($pk['platform_name'] ?? '');
                        $iconClass = match(true) {
                            str_contains($pName, 'tiktok') => 'fab fa-tiktok text-dark',
                            str_contains($pName, 'instagram') => 'fab fa-instagram text-danger',
                            str_contains($pName, 'youtube') => 'fab fa-youtube text-danger',
                            default => 'fas fa-globe text-muted',
                        };
                        $profUrl = $pk['profile_url'] ?: 'https://' . $pName . '.com/' . ltrim($pk['username'], '@');
                      ?>
                      <a href="<?= esc($profUrl) ?>" target="_blank" class="platform-icon-pill" title="<?= esc($pk['platform_name']) ?>: @<?= esc($pk['username']) ?>">
                        <i class="<?= $iconClass ?>"></i>
                        <span>@<?= esc($pk['username']) ?></span>
                        <span class="badge bg-light text-muted border text-xs" style="font-weight: 700; margin-left: 2px; padding: 2px 6px;"><?= format_metric_number($pk['followers_count'] ?? 0) ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <span class="text-muted text-xs italic">Belum ada akun</span>
                <?php endif; ?>
              </td>
              <td><?= esc($k['city'] ?: '-') ?></td>
              <td>
                <?php if ($isAdmin && !empty($k['whatsapp'])): ?>
                  <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $k['whatsapp']) ?>" target="_blank" class="text-decoration-none text-success fw-bold text-xs">
                    <i class="fab fa-whatsapp me-1"></i> <?= esc($k['whatsapp']) ?>
                  </a>
                <?php else: ?>
                  <span class="badge bg-light text-muted border text-xs">Privat</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if ($k['approval_status'] === 'APPROVED'): ?>
                  <span class="badge bg-success rounded-pill px-2.5 py-1 text-xs">APPROVED</span>
                <?php elseif ($k['approval_status'] === 'PENDING'): ?>
                  <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-xs">PENDING</span>
                <?php else: ?>
                  <span class="badge bg-danger rounded-pill px-2.5 py-1 text-xs"><?= esc($k['approval_status']) ?></span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <div class="d-inline-flex align-items-center justify-content-center gap-1">
                  <a href="<?= base_url('performance-card/view/' . $k['id']) ?>"
                     class="btn-action-kol btn-action-card"
                     title="Lihat Performance Card">
                    <i class="fas fa-id-card"></i>
                    <span>Card</span>
                  </a>
                  <button type="button"
                          class="btn-action-kol btn-action-detail"
                          title="Detail Profil KOL"
                          onclick='showKolDetail(<?= json_encode($k) ?>)'>
                    <i class="fas fa-eye"></i>
                    <span>Detail</span>
                  </button>
                  <?php if ($isAdmin): ?>
                    <button type="button"
                            class="btn-action-kol btn-action-delete"
                            title="Hapus KOL"
                            onclick="confirmDelete(<?= $k['id'] ?>, '<?= esc($k['name']) ?>')">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Detail Profil KOL -->
<div class="modal fade" id="modal-detail-kol" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light border-bottom py-3">
        <h5 class="modal-title fw-bold text-dark fs-6" id="modal-detail-title">
          <i class="fas fa-user-circle text-primary me-2"></i> Detail Profil KOL
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="modal-detail-body">
        <!-- Rendered via JS -->
      </div>
      <div class="modal-footer bg-light border-top py-2">
        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
function showKolDetail(k) {
  let html = `
    <div class="row g-3">
      <div class="col-md-6">
        <label class="text-xs text-muted text-uppercase fw-bold">Nama Lengkap</label>
        <div class="fw-bold fs-6 text-dark">${k.name || '-'}</div>
      </div>
      <div class="col-md-6">
        <label class="text-xs text-muted text-uppercase fw-bold">Nomor WhatsApp</label>
        <div class="fw-bold fs-6 text-success">${k.whatsapp || '-'}</div>
      </div>
      <div class="col-md-6">
        <label class="text-xs text-muted text-uppercase fw-bold">Domisili / Kota</label>
        <div>${k.city || '-'}</div>
      </div>
      <div class="col-md-6">
        <label class="text-xs text-muted text-uppercase fw-bold">Status Persetujuan</label>
        <div><span class="badge bg-success">${k.approval_status || 'PENDING'}</span></div>
      </div>
      <div class="col-md-12">
        <label class="text-xs text-muted text-uppercase fw-bold">Alamat Lengkap</label>
        <div class="text-muted text-sm">${k.address || 'Belum diisi'}</div>
      </div>
      <div class="col-md-6">
        <label class="text-xs text-muted text-uppercase fw-bold">Rekening Bank</label>
        <div>${k.bank_name ? k.bank_name + ' - ' + k.bank_account + ' (a.n ' + k.bank_holder + ')' : '-'}</div>
      </div>
      <div class="col-md-6">
        <label class="text-xs text-muted text-uppercase fw-bold">NPWP</label>
        <div>${k.npwp || '-'}</div>
      </div>
    </div>
  `;
  document.getElementById('modal-detail-body').innerHTML = html;
  new bootstrap.Modal(document.getElementById('modal-detail-kol')).show();
}

function confirmDelete(id, name) {
  if (confirm(`Apakah Anda yakin ingin menghapus KOL "${name}"?`)) {
    alert('Aksi hapus memerlukan persetujuan konfirmasi di server.');
  }
}
</script>
<?= $this->endSection() ?>
