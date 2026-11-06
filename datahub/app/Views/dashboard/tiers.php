<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
  .tier-list-card {
    background: #ffffff;
    border: 0;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    overflow: hidden;
    margin-bottom: 24px;
  }
  .tier-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e7edf3;
    flex-wrap: wrap;
  }
  .tier-list-title {
    margin: 0;
    color: #172b4d;
    font-size: 20px;
    font-weight: 700;
  }
  .tier-list-subtitle {
    margin-top: 4px;
    color: #6b778c;
    font-size: 13px;
  }
  .tier-count-badge {
    display: inline-block;
    margin-left: 6px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 12px;
  }
  .tier-table thead tr th {
    background: #f6f8fb;
    color: #52606d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    border-bottom: 1px solid #e7edf3;
    padding: 12px 14px;
  }
  .tier-table tbody tr td {
    padding: 12px 14px;
    vertical-align: middle;
    font-size: 13.5px;
    border-bottom: 1px solid #f1f5f9;
  }
</style>

<div class="tier-list-card">
  <!-- Header Bar -->
  <div class="tier-list-header">
    <div>
      <h3 class="tier-list-title"><?= esc($title) ?></h3>
      <div class="tier-list-subtitle">
        Standarisasi level tier influencer berdasarkan ukuran audiens &amp; jangkauan followers
        <span class="tier-count-badge"><?= count($tiers) ?> Level Tier</span>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Cetak / Export
      </button>
    </div>
  </div>

  <!-- Table Body -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 tier-table">
      <thead>
        <tr>
          <th style="width: 60px;">ID</th>
          <th>Nama Tier</th>
          <th>Rentang Jangkauan Followers</th>
          <th>Karakteristik &amp; Deskripsi</th>
          <th>KOL Terklasifikasi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tiers)): ?>
          <tr>
            <td colspan="5" class="text-center py-5 text-muted">Belum ada data tier tersimpan.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($tiers as $t): ?>
            <?php
              $tName = strtolower($t['name'] ?? '');
              $badgeBg = match(true) {
                  str_contains($tName, 'nano')  => 'background: rgba(14, 165, 233, 0.1); color: #0284c7;',
                  str_contains($tName, 'micro') => 'background: rgba(16, 185, 129, 0.1); color: #059669;',
                  str_contains($tName, 'mid')   => 'background: rgba(99, 102, 241, 0.1); color: #4f46e5;',
                  str_contains($tName, 'macro') => 'background: rgba(245, 158, 11, 0.1); color: #d97706;',
                  str_contains($tName, 'mega')  => 'background: rgba(239, 68, 68, 0.1); color: #dc2626;',
                  default                       => 'background: rgba(100, 116, 139, 0.1); color: #475569;',
              };
            ?>
            <tr>
              <td class="fw-bold text-muted">#<?= $t['id'] ?></td>
              <td>
                <span class="badge rounded-pill px-3 py-1.5 fw-bold fs-7" style="<?= $badgeBg ?>">
                  <i class="fas fa-award me-1"></i> <?= esc($t['name']) ?> Tier
                </span>
              </td>
              <td>
                <div class="fw-bold text-dark">
                  <?= number_format($t['min_followers'] ?? 0) ?> s/d <?= !empty($t['max_followers']) ? number_format($t['max_followers']) : '&infin;' ?>
                </div>
                <div class="text-muted text-xs">Followers minimum &amp; maksimum</div>
              </td>
              <td class="text-muted text-sm">
                <?= esc($t['description'] ?: 'Tier standar industri') ?>
              </td>
              <td>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 text-xs fw-bold">
                  <i class="fas fa-users me-1"></i> <?= (int)($t['kol_count'] ?? 0) ?> KOL
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
