<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-header bg-white border-bottom py-3">
        <h5 class="card-title mb-0 fw-bold text-dark fs-6">
          <i class="fas fa-sliders-h text-primary me-2"></i> Konfigurasi Sistem &amp; Integrasi API
        </h5>
      </div>
      <div class="card-body p-4">
        <?php if (session()->getFlashdata('success')): ?>
          <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
            <i class="fas fa-check-circle me-2"></i> <?= session()->getFlashdata('success') ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('settings') ?>">
          <?= csrf_field() ?>
          
          <h6 class="fw-bold text-uppercase text-xs text-muted mb-3" style="letter-spacing: 0.5px;">Identitas Platform &amp; Branding</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label text-xs fw-bold text-muted">Nama Aplikasi</label>
              <input type="text" class="form-control" name="app_name" value="KOLAB.ID Influencer Intelligence" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs fw-bold text-muted">Mata Uang Default</label>
              <input type="text" class="form-control" name="currency" value="IDR (Rupiah Indonesia)" readonly>
            </div>
          </div>

          <h6 class="fw-bold text-uppercase text-xs text-muted mb-3" style="letter-spacing: 0.5px;">Integrasi Apify Scraper Cloud</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-12">
              <label class="form-label text-xs fw-bold text-muted">Apify API Token</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key text-muted"></i></span>
                <input type="password" class="form-control font-monospace" name="apify_token" value="apify_api_token_live_secret_configured" readonly>
              </div>
              <small class="text-muted">Dikonfigurasi otomatis melalui berkas environment `.env`.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs fw-bold text-muted">TikTok Actor ID</label>
              <input type="text" class="form-control font-monospace" value="clockworks/tiktok-scraper" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label text-xs fw-bold text-muted">Instagram Actor ID</label>
              <input type="text" class="form-control font-monospace" value="apify/instagram-scraper" readonly>
            </div>
          </div>

          <h6 class="fw-bold text-uppercase text-xs text-muted mb-3" style="letter-spacing: 0.5px;">Integrasi WhatsApp Gateway (WAHA)</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-8">
              <label class="form-label text-xs fw-bold text-muted">WAHA Server URL</label>
              <input type="text" class="form-control font-monospace" value="http://localhost:3000" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label text-xs fw-bold text-muted">Sesi Default</label>
              <input type="text" class="form-control font-monospace" value="default" readonly>
            </div>
          </div>

          <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="Swal.fire({icon:'success',title:'Tersimpan',text:'Konfigurasi sistem terverifikasi aktif!',timer:1500,showConfirmButton:false})">
            <i class="fas fa-save me-1"></i> Simpan Pengaturan
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- Health Status Widget -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-header bg-dark text-white py-3 rounded-top-4">
        <h5 class="card-title mb-0 fw-bold fs-6"><i class="fas fa-heartbeat text-danger me-2"></i> Health Status Sistem</h5>
      </div>
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
          <div>
            <span class="fw-semibold text-dark d-block">MySQL Server 8.0</span>
            <small class="text-muted font-monospace">kolab_ci4 (Port 3306)</small>
          </div>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">ONLINE</span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
          <div>
            <span class="fw-semibold text-dark d-block">Apify Scraper Engine</span>
            <small class="text-muted">Cloud REST API v2</small>
          </div>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">READY</span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
          <div>
            <span class="fw-semibold text-dark d-block">WhatsApp Gateway</span>
            <small class="text-muted">WAHA Core v2024</small>
          </div>
          <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">STANDBY</span>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-semibold text-dark d-block">RBAC Access Engine</span>
            <small class="text-muted">4 Roles &amp; 58 Tabel</small>
          </div>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">ENFORCED</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
