<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-handshake text-primary me-2"></i>Dealing KOL</h3>
        <p class="text-muted small mb-0">Manajemen kesepakatan kerjasama kampanye, penugasan KOL, alur tugas konten VT, dan pengiriman sampel produk.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('campaign-kols/export') ?>" class="btn btn-outline-success btn-sm rounded-2 px-3 me-2" id="btnExportCsv" title="Export CSV Data Dealing KOL">
          <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalAddDealing()">
          <i class="fas fa-plus me-1"></i> Tugaskan KOL
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-handshake fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Total Deals</div>
              <h4 class="mb-0 fw-bold text-dark"><?= number_format($metrics['total_deals'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-check-double fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Active Deals</div>
              <h4 class="mb-0 fw-bold text-success"><?= number_format($metrics['active_deals'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="fas fa-comments fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Negotiation</div>
              <h4 class="mb-0 fw-bold text-warning"><?= number_format($metrics['negotiation_deals'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-video fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Total Tasks</div>
              <h4 class="mb-0 fw-bold text-info"><?= number_format($metrics['total_tasks'] ?? 0) ?></h4>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-dark-subtle text-dark p-3 me-3">
              <i class="fas fa-coins fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Deal Amount</div>
              <h5 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Rp <?= number_format(($metrics['total_deal_amount'] ?? 0) / 1000000, 1, ',', '.') ?>M</h5>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3">
              <i class="fas fa-star fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small text-uppercase fw-semibold" style="font-size: 11px;">Avg Score</div>
              <h4 class="mb-0 fw-bold text-dark"><?= $metrics['avg_score'] > 0 ? number_format($metrics['avg_score'], 1) : '-' ?></h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <form id="filterForm" class="row g-2 align-items-center">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Cari Campaign / KOL</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
              <input type="text" class="form-control border-start-0" id="filterSearch" placeholder="Ketik kata kunci...">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Campaign</label>
            <select class="form-select form-select-sm" id="filterCampaign">
              <option value="">Semua Campaign</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($campaignId == $c['id']) ? 'selected' : '' ?>>
                  <?= esc($c['title'] ?? $c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Filter KOL</label>
            <select class="form-select form-select-sm" id="filterKol">
              <option value="">Semua KOL</option>
              <?php foreach ($allKols as $k): ?>
                <option value="<?= $k['id'] ?>" <?= ($kolId == $k['id']) ? 'selected' : '' ?>>
                  <?= esc($k['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Status Deal</label>
            <select class="form-select form-select-sm" id="filterStatus">
              <option value="">Semua Status</option>
              <option value="NEGOTIATION">NEGOTIATION</option>
              <option value="ASSIGNED">ASSIGNED</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="COMPLETED">COMPLETED</option>
              <option value="CANCELLED">CANCELLED</option>
              <option value="REJECTED">REJECTED</option>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2 align-self-end mt-2 mt-md-0">
            <button type="button" class="btn btn-primary btn-sm rounded-2 w-100" onclick="applyFilter()">
              <i class="fas fa-filter me-1"></i> Filter
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 w-100" onclick="resetFilter()">
              <i class="fas fa-undo me-1"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle rounded-2" type="button" id="bulkActionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-cogs me-1"></i> Tindakan Massal
            </button>
            <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="bulkActionDropdown">
              <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="bulkDelete()"><i class="fas fa-trash-alt me-2"></i> Hapus yang Dipilih</a></li>
            </ul>
          </div>
          <span id="selectedCountBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1" style="display: none;">0 Dipilih</span>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableCampaignKols" style="width: 100%;">
            <thead class="table-light text-uppercase text-secondary small fw-semibold">
              <tr>
                <th width="35" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAll">
                </th>
                <th width="50">#ID</th>
                <th>Campaign</th>
                <th>Assignment KOL</th>
                <th width="120">Status Deal</th>
                <th width="120">Deal Amount</th>
                <th width="80" class="text-center">VT</th>
                <th width="80" class="text-center">Tasks</th>
                <th width="130">Sampel / Resi</th>
                <th width="130">Finance</th>
                <th width="80" class="text-center">Score</th>
                <th width="110">Deal Date</th>
                <th width="100" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: TUGASKAN KOL KE CAMPAIGN (BARU)   -->
<!-- ========================================== -->
<div class="modal fade" id="modalAssignKol" tabindex="-1" aria-labelledby="modalAssignKolLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fw-bold" id="modalAssignKolLabel"><i class="fas fa-user-plus me-2"></i>Tugaskan KOL ke Campaign</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formAssignKol" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label fw-semibold">Pilih Campaign <span class="text-danger">*</span></label>
              <select class="form-select" name="campaign_id" id="assign_campaign_id" required onchange="onCampaignSelected(this.value)">
                <option value="">-- Pilih Campaign --</option>
                <?php foreach ($allCampaigns as $c): ?>
                  <option value="<?= $c['id'] ?>" data-budget="<?= $c['budget'] ?>" data-start="<?= $c['start_date'] ?>" data-end="<?= $c['end_date'] ?>">
                    <?= esc($c['title'] ?? $c['name']) ?> (Budget: Rp <?= number_format($c['budget'], 0, ',', '.') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0">Pilih Assignment KOL <span class="text-danger">*</span></label>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold" onclick="openQuickAddKolModal()">
                  <i class="fas fa-plus-circle me-1"></i> Quick Add KOL Baru
                </button>
              </div>
              <select class="form-select" name="kol_id[]" id="assign_kol_ids" multiple size="5" required>
                <?php foreach ($allKols as $k): ?>
                  <option value="<?= $k['id'] ?>">
                    <?= esc($k['name']) ?> (<?= esc($k['tier_name'] ?? 'Nano') ?>) <?= $k['is_blacklisted'] ? ' - [BLACKLISTED]' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text small">Tahan tombol <kbd>Ctrl</kbd> (Windows) atau <kbd>Cmd</kbd> (Mac) untuk memilih lebih dari satu KOL sekaligus.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Status Awal</label>
              <select class="form-select" name="status" id="assign_status">
                <option value="ASSIGNED">ASSIGNED (Resmi Deal)</option>
                <option value="NEGOTIATION">NEGOTIATION (Penjajakan)</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Jumlah Konten Video (VT)</label>
              <input type="number" class="form-control" name="jumlah_vt" id="assign_jumlah_vt" min="1" max="50" value="1" required>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Nilai Deal (Rp)</label>
              <input type="text" class="form-control" name="deal_amount" id="assign_deal_amount" placeholder="0" oninput="formatRupiahInput(this)" value="0">
            </div>

            <div class="col-md-12">
              <label class="form-label fw-semibold">Upload Brief / SOW (Opsional)</label>
              <input type="file" class="form-control" name="deal_scope[]" id="assign_deal_scope" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              <div class="form-text small">Mendukung format PDF, Word, atau Gambar (Maks 10MB).</div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSubmitAssign">
            <i class="fas fa-save me-1"></i> Simpan Penugasan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: UNIFIED 2-STEP WIZARD (STATUS, TASKS & SHIPPING)       -->
<!-- ============================================================== -->
<div class="modal fade" id="modalWizardDeal" tabindex="-1" aria-labelledby="modalWizardDealLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-3">
        <div>
          <h5 class="modal-title fw-bold" id="modalWizardDealLabel"><i class="fas fa-sliders-h me-2"></i>Konfigurasi Deal, Tasks &amp; Pengiriman Sampel</h5>
          <div class="text-white-50 small mt-1">
            <span id="wizardCampaignTitle" class="fw-semibold text-white">Campaign</span> &bull; 
            <span id="wizardKolName" class="fw-semibold text-info">KOL</span>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0">
        <!-- Wizard Navigation Tabs -->
        <ul class="nav nav-pills nav-fill bg-light border-bottom p-2 fw-semibold" id="wizardTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active py-2 rounded-2" id="step1-tab" data-bs-toggle="pill" data-bs-target="#step1-content" type="button" role="tab">
              <i class="fas fa-tasks me-2"></i>Langkah 1: Status &amp; Alokasi Task VT
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link py-2 rounded-2" id="step2-tab" data-bs-toggle="pill" data-bs-target="#step2-content" type="button" role="tab">
              <i class="fas fa-shipping-fast me-2"></i>Langkah 2: Pengiriman Sampel Produk
            </button>
          </li>
        </ul>

        <div id="wizardLoadingSpinner" class="text-center py-5" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat data...</span>
          </div>
          <div class="text-muted small mt-2">Mengambil data kesepakatan...</div>
        </div>

        <form id="formWizardSubmit">
          <?= csrf_field() ?>
          <input type="hidden" name="id" id="wizard_deal_id">

          <div class="tab-content p-4" id="wizardTabContent">
            
            <!-- STEP 1: STATUS & TASKS -->
            <div class="tab-pane fade show active" id="step1-content" role="tabpanel">
              <div class="row g-3 mb-4">
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Status Kesepakatan <span class="text-danger">*</span></label>
                  <select class="form-select fw-bold" name="status" id="wizard_status" required onchange="onWizardStatusChange(this.value)">
                    <option value="NEGOTIATION">NEGOTIATION</option>
                    <option value="ASSIGNED">ASSIGNED</option>
                    <option value="ACTIVE">ACTIVE</option>
                    <option value="COMPLETED">COMPLETED</option>
                    <option value="CANCELLED">CANCELLED</option>
                    <option value="REJECTED">REJECTED</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Jumlah Konten Video (VT)</label>
                  <input type="number" class="form-control fw-bold" name="jumlah_vt" id="wizard_jumlah_vt" min="1" max="50" value="1" onchange="renderWizardTaskRows()">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Total Nilai Deal (Rp) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control fw-bold text-success" name="deal_amount" id="wizard_deal_amount" placeholder="0" oninput="formatRupiahInput(this); updateBudgetTracker();">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Sisa Budget Campaign</label>
                  <div class="form-control bg-light border-0 fw-semibold text-muted" id="wizard_remaining_budget_label">Rp 0</div>
                </div>

                <div class="col-md-12" id="rejection_reason_container" style="display: none;">
                  <label class="form-label fw-semibold text-danger">Alasan Penolakan (Rejection Reason) <span class="text-danger">*</span></label>
                  <textarea class="form-control border-danger" name="rejection_reason" id="wizard_rejection_reason" rows="2" placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
              </div>

              <!-- Budget Tracker Banner -->
              <div class="p-3 bg-light rounded-3 border mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2" id="wizardBudgetBanner">
                <div>
                  <span class="text-muted small">Total Budget Task:</span>
                  <strong class="ms-1" id="sumTaskBudgetLabel">Rp 0</strong>
                </div>
                <div>
                  <span class="text-muted small">Status Sinkronisasi:</span>
                  <span class="badge bg-secondary ms-1" id="budgetSyncBadge">Belum Sesuai</span>
                </div>
              </div>

              <!-- Task Configurations Table -->
              <div class="table-responsive border rounded-3">
                <table class="table table-sm table-bordered align-middle mb-0" id="tableWizardTasks">
                  <thead class="table-light text-uppercase text-secondary small">
                    <tr>
                      <th width="40" class="text-center">#VT</th>
                      <th width="160">Platform</th>
                      <th width="130">Content Type</th>
                      <th>Varian Produk</th>
                      <th width="150">Budget VT (Rp)</th>
                      <th width="150">Estimasi Posting</th>
                      <th>Catatan / Brief Ringkas</th>
                    </tr>
                  </thead>
                  <tbody id="wizardTasksTbody"></tbody>
                </table>
              </div>
            </div>

            <!-- STEP 2: SHIPPING SAMPLE PRODUCTS -->
            <div class="tab-pane fade" id="step2-content" role="tabpanel">
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Nama Penerima <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="shipment[recipient_name]" id="shipment_recipient_name">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">No. HP Penerima / WhatsApp</label>
                  <input type="text" class="form-control" name="shipment[recipient_phone]" id="shipment_recipient_phone">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Status Pengiriman</label>
                  <select class="form-select fw-semibold" name="shipment[status]" id="shipment_status">
                    <option value="PENDING">PENDING (Menunggu Pengiriman)</option>
                    <option value="PROCESSING">PROCESSING (Dikemas)</option>
                    <option value="SHIPPED">SHIPPED (Dalam Pengiriman)</option>
                    <option value="DELIVERED">DELIVERED (Telah Diterima)</option>
                    <option value="RETURNED">RETURNED (Dikembalikan)</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold">Kurir Ekspedisi</label>
                  <select class="form-select" name="shipment[courier]" id="shipment_courier" onchange="autoGenerateTrackingIfEmpty()">
                    <option value="">-- Pilih Kurir --</option>
                    <option value="JNE">JNE</option>
                    <option value="J&T">J&amp;T Express</option>
                    <option value="SiCepat">SiCepat</option>
                    <option value="AnterAja">AnterAja</option>
                    <option value="GoSend">GoSend</option>
                    <option value="GrabExpress">GrabExpress</option>
                    <option value="Ninja Xpress">Ninja Xpress</option>
                    <option value="POS Indonesia">POS Indonesia</option>
                    <option value="Lion Parcel">Lion Parcel</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                </div>
                <div class="col-md-5">
                  <label class="form-label fw-semibold">Nomor Resi / Tracking</label>
                  <div class="input-group">
                    <input type="text" class="form-control fw-bold" name="shipment[tracking_no]" id="shipment_tracking_no" placeholder="Contoh: JNE12345678">
                    <button class="btn btn-outline-secondary" type="button" onclick="generateResiBtn()" title="Auto Generate Nomor Resi">
                      <i class="fas fa-magic"></i> Auto
                    </button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Ongkos Kirim (Rp)</label>
                  <input type="text" class="form-control" name="shipment[shipping_fee]" id="shipment_shipping_fee" placeholder="0" oninput="formatRupiahInput(this)">
                </div>

                <div class="col-md-12">
                  <label class="form-label fw-semibold">Alamat Lengkap Pengiriman</label>
                  <textarea class="form-control" name="shipment[shipping_address]" id="shipment_shipping_address" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kecamatan, kota, kode pos..."></textarea>
                </div>
              </div>

              <!-- Shipment Items Table -->
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open text-primary me-2"></i>Daftar Sampel Produk yang Dikirim</h6>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-2" onclick="addShipmentItemRow()">
                  <i class="fas fa-plus me-1"></i> Tambah Item
                </button>
              </div>

              <div class="table-responsive border rounded-3">
                <table class="table table-sm table-bordered align-middle mb-0" id="tableShipmentItems">
                  <thead class="table-light text-uppercase text-secondary small">
                    <tr>
                      <th>Pilih Produk Sampel</th>
                      <th width="120" class="text-center">Stok Gudang</th>
                      <th width="120" class="text-center">Jumlah (Qty)</th>
                      <th width="50" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyShipmentItems"></tbody>
                </table>
              </div>
            </div>

          </div>

          <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-primary rounded-2" id="btnWizardNextTab" onclick="switchWizardTab()">
                Lanjut: Sampel Produk <i class="fas fa-arrow-right ms-1"></i>
              </button>
              <button type="submit" class="btn btn-success rounded-2 px-4 shadow-sm" id="btnSaveWizard">
                <i class="fas fa-check-circle me-1"></i> Simpan Konfigurasi
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: QUICK ADD KOL                     -->
<!-- ========================================== -->
<div class="modal fade" id="modalQuickAddKol" tabindex="-1" aria-labelledby="modalQuickAddKolLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2">
        <h6 class="modal-title fw-bold" id="modalQuickAddKolLabel"><i class="fas fa-user-plus me-2"></i>Quick Add KOL Baru</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formQuickAddKol">
        <?= csrf_field() ?>
        <div class="modal-body p-4">
          <label class="form-label fw-semibold">Nama Lengkap / Username Talent <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="name" id="quick_kol_name" placeholder="Contoh: Jessica Jane" required autocomplete="off">
          <div class="form-text small">KOL baru akan otomatis ditambahkan dengan Tier Nano dan status Aktif.</div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary btn-sm rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3" id="btnSubmitQuickAdd">
            <i class="fas fa-check me-1"></i> Tambahkan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let table;
let currentDealData = null;
const availableProducts = <?= json_encode($allProducts) ?>;
const availablePlatforms = <?= json_encode($allPlatforms) ?>;
const availableVariants = <?= json_encode($allVariants) ?>;

$(document).ready(function() {
  initDataTable();

  // Check all listener
  $('#checkAll').on('change', function() {
    $('.row-checkbox').prop('checked', $(this).prop('checked'));
    updateSelectedCount();
  });

  $(document).on('change', '.row-checkbox', function() {
    updateSelectedCount();
  });

  // Handle Form Assign Submit
  $('#formAssignKol').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSubmitAssign');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

    const formData = new FormData(this);

    $.ajax({
      url: '<?= site_url('campaign-kols/save') ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Penugasan');
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: res.message,
            timer: 2000,
            showConfirmButton: false
          });
          $('#modalAssignKol').modal('hide');
          $('#formAssignKol')[0].reset();
          reloadTable();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: res.message
          });
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Penugasan');
        Swal.fire({
          icon: 'error',
          title: 'Error Server',
          text: 'Terjadi kesalahan sistem saat menyimpan data.'
        });
      }
    });
  });

  // Handle Form Quick Add KOL Submit
  $('#formQuickAddKol').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSubmitQuickAdd');
    btn.prop('disabled', true);

    $.ajax({
      url: '<?= site_url('campaign-kols/quick-add-kol') ?>',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false);
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'KOL Tersedia',
            text: res.message,
            timer: 1500,
            showConfirmButton: false
          });
          // Add to select options and select it
          const newOpt = new Option(res.kol.name + ' (' + res.kol.tier_name + ')', res.kol.id, true, true);
          $('#assign_kol_ids').append(newOpt).trigger('change');
          $('#modalQuickAddKol').modal('hide');
          $('#formQuickAddKol')[0].reset();
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: res.message
          });
        }
      },
      error: function() {
        btn.prop('disabled', false);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Gagal menambahkan KOL.'
        });
      }
    });
  });

  // Handle Form Wizard Submit
  $('#formWizardSubmit').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSaveWizard');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      url: '<?= site_url('campaign-kols/update-status-tasks-ajax') ?>',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Simpan Konfigurasi');
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil Disimpan',
            text: res.message,
            timer: 2000,
            showConfirmButton: false
          });
          $('#modalWizardDeal').modal('hide');
          reloadTable();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            text: res.message
          });
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Simpan Konfigurasi');
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Terjadi kesalahan sistem saat menyimpan konfigurasi.'
        });
      }
    });
  });
});

function initDataTable() {
  table = $('#tableCampaignKols').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= site_url('campaign-kols/get-datatable') ?>',
      type: 'POST',
      data: function(d) {
        d.campaign_id = $('#filterCampaign').val();
        d.kol_id = $('#filterKol').val();
        d.status = $('#filterStatus').val();
        d.search.value = $('#filterSearch').val();
      }
    },
    order: [[1, 'desc']],
    columns: [
      {
        data: 'id',
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function(data) {
          return '<input type="checkbox" class="form-check-input row-checkbox" value="' + data + '">';
        }
      },
      { data: 'id', className: 'text-muted small fw-bold' },
      {
        data: 'campaign_title',
        render: function(data, type, row) {
          return '<div class="fw-bold text-dark">' + data + '</div>' +
                 '<span class="text-muted small">Assigned: ' + (row.assigned_by_name || '-') + '</span>';
        }
      },
      {
        data: 'kol_name',
        render: function(data, type, row) {
          let badge = '<span class="badge bg-secondary-subtle text-secondary border ms-1">' + (row.tier_name || 'Nano') + '</span>';
          if (row.is_blacklisted) {
            badge += ' <span class="badge bg-danger-subtle text-danger border">BLACKLISTED</span>';
          }
          return '<div class="fw-bold text-dark d-flex align-items-center">' +
                 '<i class="fas fa-user-circle fa-lg text-primary me-2"></i>' +
                 '<span>' + data + '</span>' + badge + '</div>' +
                 '<span class="text-muted small">' + (row.kol_whatsapp ? ('WA: ' + row.kol_whatsapp) : '') + '</span>';
        }
      },
      {
        data: 'status',
        render: function(data, type, row) {
          let badgeClass = 'bg-secondary';
          if (data === 'ACTIVE') badgeClass = 'bg-success';
          else if (data === 'ASSIGNED') badgeClass = 'bg-info text-dark';
          else if (data === 'NEGOTIATION') badgeClass = 'bg-warning text-dark';
          else if (data === 'COMPLETED') badgeClass = 'bg-primary';
          else if (data === 'REJECTED') badgeClass = 'bg-danger';

          return '<a href="javascript:void(0)" onclick="openWizard(' + row.id + ')" class="text-decoration-none" title="Klik untuk ubah status">' +
                 '<span class="badge ' + badgeClass + ' px-2 py-1">' + data + ' <i class="fas fa-edit ms-1"></i></span></a>';
        }
      },
      {
        data: 'deal_amount_formatted',
        className: 'fw-bold text-dark'
      },
      {
        data: 'jumlah_vt',
        className: 'text-center fw-bold'
      },
      {
        data: 'task_count',
        className: 'text-center',
        render: function(data, type, row) {
          return '<span class="badge bg-light text-dark border">' + row.task_completed_count + ' / ' + data + '</span>';
        }
      },
      {
        data: 'shipping_status',
        render: function(data, type, row) {
          let badge = '<span class="badge bg-secondary-subtle text-secondary border">NOT SENT</span>';
          if (data === 'SENT' || row.latest_shipment_status === 'SHIPPED') {
            badge = '<span class="badge bg-info-subtle text-info border">SHIPPED</span>';
          } else if (data === 'RECEIVED' || row.latest_shipment_status === 'DELIVERED') {
            badge = '<span class="badge bg-success-subtle text-success border">DELIVERED</span>';
          }
          const resi = row.latest_tracking_no ? '<div class="small text-muted fw-bold mt-1"><i class="fas fa-barcode me-1"></i>' + row.latest_tracking_no + '</div>' : '';
          return badge + resi;
        }
      },
      {
        data: null,
        orderable: false,
        render: function(data, type, row) {
          let cBadge = row.latest_contract_status
            ? '<span class="badge bg-light text-dark border" title="' + (row.latest_contract_no || 'Kontrak') + '"><i class="fas fa-file-contract text-primary me-1"></i>' + row.latest_contract_status + '</span>'
            : '<span class="text-muted small">-</span>';
          let iBadge = row.latest_invoice_status
            ? '<span class="badge bg-light text-dark border mt-1" title="' + (row.invoice_no || 'Invoice') + '"><i class="fas fa-file-invoice-dollar text-success me-1"></i>' + row.latest_invoice_status + '</span>'
            : '';
          return '<div>' + cBadge + '</div>' + (iBadge ? '<div>' + iBadge + '</div>' : '');
        }
      },
      {
        data: 'collaboration_score',
        className: 'text-center fw-bold'
      },
      {
        data: 'deal_date',
        className: 'text-muted small'
      },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function(data, type, row) {
          return '<div class="btn-group btn-group-sm">' +
                 '<a href="<?= site_url('campaign-kols/view/') ?>' + data + '" class="btn btn-outline-secondary" title="Detail"><i class="fas fa-eye"></i></a>' +
                 '<button type="button" class="btn btn-outline-primary" onclick="openWizard(' + data + ')" title="Wizard Konfigurasi"><i class="fas fa-sliders-h"></i></button>' +
                 '<button type="button" class="btn btn-outline-danger" onclick="deleteDealing(' + data + ')" title="Hapus"><i class="fas fa-trash-alt"></i></button>' +
                 '</div>';
        }
      }
    ],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Cari...",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
      infoEmpty: "Data tidak ditemukan",
      zeroRecords: "Tidak ada data yang cocok",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "&raquo;",
        previous: "&laquo;"
      }
    }
  });
}

function reloadTable() {
  table.ajax.reload(null, false);
  $('#checkAll').prop('checked', false);
  updateSelectedCount();
}

function applyFilter() {
  table.ajax.reload();
}

function resetFilter() {
  $('#filterSearch').val('');
  $('#filterCampaign').val('');
  $('#filterKol').val('');
  $('#filterStatus').val('');
  table.ajax.reload();
}

function updateSelectedCount() {
  const count = $('.row-checkbox:checked').length;
  if (count > 0) {
    $('#selectedCountBadge').text(count + ' Dipilih').fadeIn(150);
  } else {
    $('#selectedCountBadge').fadeOut(150);
  }
}

function modalAddDealing() {
  $('#formAssignKol')[0].reset();
  $('#modalAssignKol').modal('show');
}

function openQuickAddKolModal() {
  $('#formQuickAddKol')[0].reset();
  $('#modalQuickAddKol').modal('show');
}

function formatRupiahInput(el) {
  let val = el.value.replace(/[^0-9]/g, '');
  if (!val) {
    el.value = '';
    return;
  }
  el.value = parseInt(val, 10).toLocaleString('id-ID');
}

// -------------------------------------------------------------
// WIZARD MODAL LOGIC (CI3 PARITY: TASKS & SHIPPING)
// -------------------------------------------------------------
function openWizard(dealId) {
  $('#wizard_deal_id').val(dealId);
  $('#formWizardSubmit')[0].reset();
  $('#wizardTasksTbody').empty();
  $('#tbodyShipmentItems').empty();

  // Reset to Step 1
  var firstTab = new bootstrap.Tab(document.querySelector('#step1-tab'));
  firstTab.show();

  $('#wizardLoadingSpinner').show();
  $('#wizardTabContent').hide();
  $('#modalWizardDeal').modal('show');

  $.ajax({
    url: '<?= site_url('campaign-kols/tasks-json/') ?>' + dealId,
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      $('#wizardLoadingSpinner').hide();
      $('#wizardTabContent').show();

      if (!res.success) {
        Swal.fire('Error', res.message, 'error');
        $('#modalWizardDeal').modal('hide');
        return;
      }

      currentDealData = res;
      $('#wizardCampaignTitle').text(res.campaign_name || 'Campaign');
      $('#wizardKolName').text(res.kol_name || 'KOL');

      // Populate Step 1
      $('#wizard_status').val(res.campaign_kol.status || 'ASSIGNED');
      onWizardStatusChange(res.campaign_kol.status || 'ASSIGNED');

      $('#wizard_jumlah_vt').val(res.campaign_kol.jumlah_vt || 1);
      const dealAmt = parseFloat(res.campaign_kol.deal_amount) || 0;
      $('#wizard_deal_amount').val(dealAmt > 0 ? dealAmt.toLocaleString('id-ID') : '');

      const remBudget = parseFloat(res.campaign_remaining_budget) || 0;
      $('#wizard_remaining_budget_label').text('Rp ' + remBudget.toLocaleString('id-ID'));

      if (res.campaign_kol.rejection_reason) {
        $('#wizard_rejection_reason').val(res.campaign_kol.rejection_reason);
      }

      renderWizardTaskRows();

      // Populate Step 2 (Shipment)
      if (res.shipment) {
        $('#shipment_recipient_name').val(res.shipment.recipient_name || '');
        $('#shipment_recipient_phone').val(res.shipment.recipient_phone || '');
        $('#shipment_status').val(res.shipment.status || 'PENDING');
        $('#shipment_courier').val(res.shipment.courier || '');
        $('#shipment_tracking_no').val(res.shipment.tracking_no || '');
        const fee = parseFloat(res.shipment.shipping_fee) || 0;
        $('#shipment_shipping_fee').val(fee > 0 ? fee.toLocaleString('id-ID') : '');
        $('#shipment_shipping_address').val(res.shipment.shipping_address || '');
      } else if (res.kol) {
        $('#shipment_recipient_name').val(res.kol.name || '');
        $('#shipment_recipient_phone').val(res.kol.whatsapp || res.kol.phone || '');
        let addr = res.kol.address || '';
        if (res.kol.city) addr += (addr ? ', ' : '') + res.kol.city;
        if (res.kol.postal_code) addr += (addr ? ' ' : '') + res.kol.postal_code;
        $('#shipment_shipping_address').val(addr);
      }

      // Populate Shipment Items
      if (res.shipment_items && res.shipment_items.length > 0) {
        res.shipment_items.forEach(function(item) {
          addShipmentItemRow(item.product_id, item.qty);
        });
      } else {
        addShipmentItemRow();
      }
    },
    error: function() {
      $('#wizardLoadingSpinner').hide();
      Swal.fire('Error', 'Gagal memuat rincian dealing KOL.', 'error');
      $('#modalWizardDeal').modal('hide');
    }
  });
}

function onWizardStatusChange(status) {
  if (status === 'REJECTED') {
    $('#rejection_reason_container').fadeIn(150);
    $('#wizard_rejection_reason').prop('required', true);
  } else {
    $('#rejection_reason_container').fadeOut(150);
    $('#wizard_rejection_reason').prop('required', false);
  }

  if (status === 'NEGOTIATION') {
    $('#wizard_deal_amount').val('0').prop('readonly', true);
  } else {
    $('#wizard_deal_amount').prop('readonly', false);
  }
}

function renderWizardTaskRows() {
  const count = parseInt($('#wizard_jumlah_vt').val(), 10) || 1;
  const tbody = $('#wizardTasksTbody');
  tbody.empty();

  const existingTasks = (currentDealData && currentDealData.tasks) ? currentDealData.tasks : [];
  const platforms = (currentDealData && currentDealData.platforms && currentDealData.platforms.length > 0)
    ? currentDealData.platforms
    : availablePlatforms;

  const dealAmt = parseFloat(($('#wizard_deal_amount').val() || '').replace(/[^0-9]/g, '')) || 0;
  const ratePerVt = count > 0 ? Math.round(dealAmt / count) : 0;

  for (let i = 0; i < count; i++) {
    const t = existingTasks[i] || {};
    const taskBudget = (t.budget && parseFloat(t.budget) > 0) ? parseFloat(t.budget) : ratePerVt;
    const estDate = t.estimated_posting_date ? t.estimated_posting_date.substr(0, 10) : new Date().toISOString().substr(0, 10);

    let platformOptions = '';
    platforms.forEach(function(p) {
      const sel = (t.platform_id == p.id) ? 'selected' : '';
      platformOptions += `<option value="${p.id}" ${sel}>${p.name}</option>`;
    });

    let variantOptions = '<option value="">-- Varian --</option>';
    availableVariants.forEach(function(v) {
      const sel = (t.variant_id == v.id) ? 'selected' : '';
      variantOptions += `<option value="${v.id}" ${sel}>${v.name}</option>`;
    });

    const html = `
      <tr class="task-row" data-index="${i}">
        <td class="text-center fw-bold text-muted">
          #${i + 1}
          <input type="hidden" name="tasks[${i}][id]" value="${t.id || ''}">
          <input type="hidden" name="tasks[${i}][title]" value="Konten Video VT #${i + 1}">
        </td>
        <td>
          <select class="form-select form-select-sm" name="tasks[${i}][platform_id]">
            ${platformOptions}
          </select>
        </td>
        <td>
          <select class="form-select form-select-sm" name="tasks[${i}][content_type]">
            <option value="VT" ${t.content_type === 'VT' ? 'selected' : ''}>VT (TikTok)</option>
            <option value="REELS" ${t.content_type === 'REELS' ? 'selected' : ''}>Reels</option>
            <option value="STORY" ${t.content_type === 'STORY' ? 'selected' : ''}>Story</option>
            <option value="FEED" ${t.content_type === 'FEED' ? 'selected' : ''}>Feed Post</option>
            <option value="YOUTUBE_SHORTS" ${t.content_type === 'YOUTUBE_SHORTS' ? 'selected' : ''}>Shorts</option>
          </select>
        </td>
        <td>
          <select class="form-select form-select-sm" name="tasks[${i}][variant_id]">
            ${variantOptions}
          </select>
        </td>
        <td>
          <input type="text" class="form-control form-select-sm fw-bold task-budget-input" name="tasks[${i}][budget]" value="${taskBudget.toLocaleString('id-ID')}" oninput="formatRupiahInput(this); updateBudgetTracker();">
        </td>
        <td>
          <input type="date" class="form-control form-select-sm" name="tasks[${i}][estimated_posting_date]" value="${estDate}">
        </td>
        <td>
          <input type="text" class="form-control form-select-sm" name="tasks[${i}][notes]" value="${t.notes || ''}" placeholder="Catatan sudut pandang...">
        </td>
      </tr>
    `;
    tbody.append(html);
  }

  updateBudgetTracker();
}

function updateBudgetTracker() {
  const dealAmt = parseFloat(($('#wizard_deal_amount').val() || '').replace(/[^0-9]/g, '')) || 0;
  let totalTask = 0;
  $('.task-budget-input').each(function() {
    totalTask += parseFloat(($(this).val() || '').replace(/[^0-9]/g, '')) || 0;
  });

  $('#sumTaskBudgetLabel').text('Rp ' + totalTask.toLocaleString('id-ID'));

  const badge = $('#budgetSyncBadge');
  if (dealAmt > 0 && totalTask === dealAmt) {
    badge.removeClass('bg-secondary bg-danger').addClass('bg-success').text('Sinkron 100%');
  } else if (dealAmt === 0 && totalTask === 0) {
    badge.removeClass('bg-danger bg-success').addClass('bg-secondary').text('Nol');
  } else {
    badge.removeClass('bg-secondary bg-success').addClass('bg-danger').text('Selisih: Rp ' + Math.abs(dealAmt - totalTask).toLocaleString('id-ID'));
  }
}

function switchWizardTab() {
  const nextTab = new bootstrap.Tab(document.querySelector('#step2-tab'));
  nextTab.show();
}

function autoGenerateTrackingIfEmpty() {
  if (!$('#shipment_tracking_no').val()) {
    generateResiBtn();
  }
}

function generateResiBtn() {
  const courier = $('#shipment_courier').val() || 'JNE';
  $.ajax({
    url: '<?= site_url('campaign-kols/generate-tracking-no') ?>',
    type: 'GET',
    data: { courier: courier },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        $('#shipment_tracking_no').val(res.tracking_no);
      }
    }
  });
}

function addShipmentItemRow(selectedProductId, qtyVal) {
  const idx = $('#tbodyShipmentItems tr').length;
  let prodOptions = '<option value="">-- Pilih Produk Sampel --</option>';
  const prods = (currentDealData && currentDealData.products && currentDealData.products.length > 0)
    ? currentDealData.products
    : availableProducts;

  prods.forEach(function(p) {
    const sel = (selectedProductId && selectedProductId == p.id) ? 'selected' : '';
    prodOptions += `<option value="${p.id}" data-stock="${p.stock || p.stock_qty || 0}" ${sel}>${p.name} (SKU: ${p.sku})</option>`;
  });

  const row = `
    <tr class="shipment-item-row" data-index="${idx}">
      <td>
        <select class="form-select form-select-sm item-product-select" name="shipment_items[${idx}][product_id]" onchange="onProductItemChange(this)">
          ${prodOptions}
        </select>
      </td>
      <td class="text-center fw-bold text-muted item-stock-label">-</td>
      <td>
        <input type="number" class="form-control form-control-sm text-center fw-bold" name="shipment_items[${idx}][qty]" min="1" max="100" value="${qtyVal || 1}">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-link text-danger btn-sm p-0" onclick="$(this).closest('tr').remove()"><i class="fas fa-trash"></i></button>
      </td>
    </tr>
  `;
  $('#tbodyShipmentItems').append(row);

  // Trigger change to update stock label if selected
  if (selectedProductId) {
    const el = $('#tbodyShipmentItems tr:last').find('.item-product-select');
    onProductItemChange(el[0]);
  }
}

function onProductItemChange(selectEl) {
  const stock = $(selectEl).find(':selected').data('stock') ?? '-';
  $(selectEl).closest('tr').find('.item-stock-label').text(stock);
}

function deleteDealing(id) {
  Swal.fire({
    title: 'Hapus Dealing KOL #' + id + '?',
    text: 'Aksi ini akan menghapus alur tugas (tasks), briefs, drafts, kontrak, dan pengiriman produk terkait secara permanen.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= site_url('campaign-kols/delete/') ?>' + id,
        type: 'POST',
        data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Terhapus!', res.message, 'success');
            reloadTable();
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
        }
      });
    }
  });
}

function bulkDelete() {
  const ids = [];
  $('.row-checkbox:checked').each(function() {
    ids.push($(this).val());
  });

  if (ids.length === 0) {
    Swal.fire('Pilih Data', 'Pilih setidaknya satu baris dealing KOL.', 'info');
    return;
  }

  Swal.fire({
    title: 'Hapus ' + ids.length + ' Dealing KOL Terpilih?',
    text: 'Semua alur tugas dan data terkait akan dihapus secara kaskade.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus Semua!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= site_url('campaign-kols/bulk-delete') ?>',
        type: 'POST',
        data: {
          ids: ids,
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('Selesai', res.message, 'success');
            reloadTable();
          } else {
            Swal.fire('Gagal', res.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Gagal menghapus data massal.', 'error');
        }
      });
    }
  });
}
</script>
<?= $this->endSection() ?>
