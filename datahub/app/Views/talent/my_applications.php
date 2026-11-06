<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.section-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    border: none;
}
.section-card .card-header-custom {
    background: #fff;
    border-bottom: 1.5px solid #e9eef6;
    border-radius: 10px 10px 0 0;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #172b4d;
    margin-bottom: 2px;
}
.section-subtitle {
    font-size: 13px;
    color: #6b778c;
}
.count-badge {
    background: #e8f1fb;
    color: #1f4b6e;
    font-weight: 700;
    font-size: 13px;
    border-radius: 20px;
    padding: 4px 14px;
}
.app-table thead th {
    background: #f6f8fb;
    color: #52606d;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    border-bottom: 1.5px solid #e4ebf5;
    padding: 12px 16px;
    white-space: nowrap;
}
.app-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f4fa;
    font-size: 13px;
}
.app-table tbody tr:last-child td { border-bottom: none; }
.app-table tbody tr:hover { background: #fafbfd; }
.status-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-radius: 20px;
    padding: 4px 12px;
}
.status-applied  { background: #fff8e1; color: #b45309; }
.status-accepted { background: #e3f9e5; color: #166534; }
.status-rejected { background: #fdecea; color: #c0392b; }
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state i { font-size: 48px; color: #d0daea; margin-bottom: 16px; display: block; }
.empty-state h6 { color: #172b4d; font-weight: 700; font-size: 16px; }
.empty-state p  { color: #6b778c; font-size: 13px; }
.btn-explore {
    background: #e8f1fb;
    color: #1f4b6e;
    border: none;
    border-radius: 20px;
    padding: 7px 20px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: background 0.15s;
}
.btn-explore:hover { background: #1f4b6e; color: #fff; }
</style>

<div class="row mb-4">
  <div class="col-12">
    <div class="section-card">

      <!-- Header -->
      <div class="card-header-custom">
        <div>
          <div class="section-title">
            <i class="fas fa-clipboard-list text-primary me-2"></i> Status Lamaran Kampanye Saya
          </div>
          <div class="section-subtitle">Pantau status seleksi dan penawaran kerja sama yang Anda kirimkan ke Brand</div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <span class="count-badge"><?= count($applications) ?> Lamaran</span>
          <a href="<?= base_url('open-campaigns') ?>" class="btn-explore">
            <i class="fas fa-search me-1"></i> Jelajahi Kampanye
          </a>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="app-table w-100">
          <thead>
            <tr>
              <th style="width:40px;">#</th>
              <th>Kampanye &amp; Brand</th>
              <th>Tanggal Daftar</th>
              <th>Tarif Diajukan</th>
              <th>Pitching Anda</th>
              <th>Status Review</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($applications)): ?>
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h6>Belum Ada Pendaftaran</h6>
                    <p>Anda belum mengajukan lamaran ke kampanye manapun.</p>
                    <a href="<?= base_url('open-campaigns') ?>" class="btn-explore">
                      <i class="fas fa-bullhorn me-1"></i> Lihat Kampanye Terbuka
                    </a>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($applications as $i => $app): ?>
                <tr>
                  <td class="text-muted fw-semibold"><?= $i + 1 ?></td>
                  <td>
                    <div class="fw-bold" style="color:#172b4d;"><?= esc($app['campaign_title'] ?? 'Kampanye #' . $app['campaign_id']) ?></div>
                    <small style="color:#6b778c;font-size:11px;">
                      <?php if (!empty($app['start_date'])): ?>
                        <?= esc($app['start_date']) ?> s/d <?= esc($app['end_date'] ?? '-') ?>
                      <?php endif; ?>
                    </small>
                  </td>
                  <td>
                    <div style="color:#172b4d;font-weight:600;">
                      <?= date('d M Y', strtotime($app['created_at'] ?? 'now')) ?>
                    </div>
                    <small style="color:#6b778c;font-size:11px;">
                      <?= date('H:i', strtotime($app['created_at'] ?? 'now')) ?> WIB
                    </small>
                  </td>
                  <td>
                    <span style="font-weight:700;color:#166534;font-size:14px;">
                      Rp <?= number_format((float)($app['proposed_rate'] ?? 0), 0, ',', '.') ?>
                    </span>
                  </td>
                  <td style="max-width:220px;">
                    <small style="color:#52606d;"><?= esc($app['pitch_notes'] ?? '-') ?></small>
                  </td>
                  <td>
                    <?php
                      $st = $app['status'] ?? 'APPLIED';
                      $stc = match($st) {
                          'ACCEPTED' => 'status-accepted',
                          'REJECTED' => 'status-rejected',
                          default    => 'status-applied',
                      };
                      $stLabel = match($st) {
                          'ACCEPTED' => '✓ Diterima',
                          'REJECTED' => '✗ Ditolak',
                          default    => '⏳ Menunggu',
                      };
                    ?>
                    <span class="status-badge <?= $stc ?>"><?= $stLabel ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<?= $this->endSection() ?>
