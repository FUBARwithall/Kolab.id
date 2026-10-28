<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-0 fw-bold text-dark fs-6">
        <i class="fas fa-users text-success me-2"></i> <?= esc($title) ?>
      </h5>
      <p class="text-muted text-xs mb-0">Total <?= count($kols) ?> data profil KOL terdata di sistem</p>
    </div>
    <?php if ($isAdmin): ?>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print()">
          <i class="fas fa-print me-1"></i> Cetak / Export
        </button>
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-add-kol">
          <i class="fas fa-plus me-1"></i> Tambah KOL
        </button>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle mb-0" id="table-kols">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width: 50px;">ID</th>
            <th>Nama Lengkap</th>
            <th>Domisili / Kota</th>
            <th>Akun Platform</th>
            <th>Kontak WhatsApp</th>
            <th>Status Akun</th>
            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($kols)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">Belum ada data KOL yang tersimpan.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($kols as $k): ?>
              <tr>
                <td class="ps-3 fw-bold text-muted">#<?= $k['id'] ?></td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success text-white me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 13px;">
                      <?= strtoupper(substr($k['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="fw-bold text-dark"><?= esc($k['name']) ?></div>
                      <div class="text-muted text-xs">Terdaftar: <?= date('d M Y', strtotime($k['created_at'] ?? 'now')) ?></div>
                    </div>
                  </div>
                </td>
                <td><?= esc($k['city'] ?? '-') ?></td>
                <td>
                  <span class="badge bg-info text-white rounded-pill px-2.5 py-1">
                    <i class="fas fa-share-alt me-1"></i> <?= (int) ($k['platform_count'] ?? 0) ?> Channels
                  </span>
                </td>
                <td>
                  <?php if ($isAdmin): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $k['whatsapp']) ?>" target="_blank" class="text-decoration-none text-success fw-bold text-xs">
                      <i class="fab fa-whatsapp me-1"></i> <?= esc($k['whatsapp']) ?>
                    </a>
                  <?php else: ?>
                    <span class="badge bg-light text-muted border">Privat</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($k['approval_status'] === 'APPROVED'): ?>
                    <span class="badge bg-success">APPROVED</span>
                  <?php elseif ($k['approval_status'] === 'PENDING'): ?>
                    <span class="badge bg-warning text-dark">PENDING</span>
                  <?php else: ?>
                    <span class="badge bg-danger"><?= esc($k['approval_status']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-3">
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info rounded-pill px-2.5 py-1 text-xs" title="Lihat Detail Profil">
                      <i class="fas fa-eye"></i> Detail
                    </button>
                    <?php if ($isAdmin): ?>
                      <button class="btn btn-outline-danger rounded-pill px-2.5 py-1 text-xs ms-1" title="Hapus Data (Superadmin)">
                        <i class="fas fa-trash"></i>
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
</div>

<?php if ($isAdmin): ?>
  <!-- Modal Tambah KOL (Superadmin Only) -->
  <div class="modal fade" id="modal-add-kol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title fs-6 fw-bold"><i class="fas fa-user-plus me-2"></i> Tambah Data KOL Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="<?= base_url('kols') ?>">
          <?= csrf_field() ?>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label text-xs fw-bold text-muted text-uppercase">Nama Lengkap KOL</label>
              <input type="text" name="name" class="form-control" placeholder="Contoh: Jessica Jane" required>
            </div>
            <div class="mb-3">
              <label class="form-label text-xs fw-bold text-muted text-uppercase">Kota Domisili</label>
              <input type="text" name="city" class="form-control" placeholder="Contoh: Jakarta Selatan" required>
            </div>
            <div class="mb-3">
              <label class="form-label text-xs fw-bold text-muted text-uppercase">Nomor WhatsApp</label>
              <input type="text" name="whatsapp" class="form-control" placeholder="08123456789" required>
            </div>
            <div class="mb-3">
              <label class="form-label text-xs fw-bold text-muted text-uppercase">Status Approval</label>
              <select name="approval_status" class="form-select">
                <option value="APPROVED" selected>APPROVED</option>
                <option value="PENDING">PENDING</option>
              </select>
            </div>
          </div>
          <div class="modal-footer bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Simpan KOL</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
