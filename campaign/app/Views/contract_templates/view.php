<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-file-contract text-primary me-2"></i>Detail Template Kontrak #<?= esc($tpl['id']) ?></h4>
            <p class="text-muted small mb-0"><?= esc($tpl['title']) ?></p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('contract-templates/single-pdf/' . $tpl['id']) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="fas fa-file-pdf me-1"></i> Cetak / Ekspor PDF
            </a>
            <a href="<?= base_url('contract-templates/edit/' . $tpl['id']) ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fas fa-edit me-1"></i> Edit Template
            </a>
            <a href="<?= base_url('contract-templates') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Info Overview Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="text-muted small">ID Template</div>
                    <div class="fw-bold text-dark fs-6">#<?= esc($tpl['id']) ?></div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-muted small">Status</div>
                    <div>
                        <?= !empty($tpl['is_active']) 
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>AKTIF</span>' 
                            : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">NON-AKTIF</span>' ?>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-muted small">Tanggal Dibuat</div>
                    <div class="fw-semibold text-dark"><?= !empty($tpl['created_at']) ? date('d F Y, H:i', strtotime($tpl['created_at'])) : '-' ?></div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="text-muted small">Terakhir Diperbarui</div>
                    <div class="fw-semibold text-dark"><?= !empty($tpl['updated_at']) ? date('d F Y, H:i', strtotime($tpl['updated_at'])) : '-' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Rendered Preview and Raw Template -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <ul class="nav nav-tabs card-header-tabs" id="viewTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold" id="tab-rendered-btn" data-bs-toggle="tab" data-bs-target="#tab-rendered" type="button">
                        <i class="fas fa-eye text-primary me-2"></i>Contoh Render Dokumen Realistis
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="tab-raw-btn" data-bs-toggle="tab" data-bs-target="#tab-raw" type="button">
                        <i class="fas fa-code text-secondary me-2"></i>Naskah Asli (Raw Placeholders)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="viewTabsContent">
                <!-- Rendered Sample -->
                <div class="tab-pane fade show active" id="tab-rendered">
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="fas fa-info-circle me-1 text-info"></i> Tampilan di bawah disimulasikan menggunakan data contoh profil KOL dan nomor kontrak otomatis.
                    </div>
                    <div class="p-4 bg-white border rounded shadow-sm" style="min-height: 400px; line-height: 1.8; color: #212529;">
                        <?= $sampleRendered ?>
                    </div>
                </div>

                <!-- Raw Placeholders -->
                <div class="tab-pane fade" id="tab-raw">
                    <div class="p-3 bg-light border rounded font-monospace small" style="white-space: pre-wrap; line-height: 1.6;">
<?= esc($tpl['body_template'] ?: $tpl['content']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
