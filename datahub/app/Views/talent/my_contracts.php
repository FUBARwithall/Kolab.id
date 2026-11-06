<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
/* ===== Base Card Styling ===== */
.section-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(23,43,77,0.08);
    border: none;
    overflow: hidden;
}
.section-card .card-header-custom {
    padding: 18px 24px;
    background: #fafbfc;
    border-bottom: 1.5px solid #edf2f7;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
}
.section-subtitle {
    font-size: 13px;
    color: #64748b;
}
.count-badge {
    background: #e0f2fe;
    color: #0284c7;
    font-weight: 700;
    font-size: 12.5px;
    border-radius: 20px;
    padding: 4px 14px;
}

/* ===== Table Styling ===== */
.contract-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}
.contract-table thead th {
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1.5px solid #e2e8f0;
    padding: 14px 18px;
    white-space: nowrap;
}
.contract-table tbody td {
    padding: 15px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #334155;
}
.contract-table tbody tr:last-child td {
    border-bottom: none;
}
.contract-table tbody tr:hover {
    background: #fafbfc;
}

/* ===== Status Badges ===== */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 6px;
    white-space: nowrap;
}
.status-pill-warning {
    background: #fef3c7;
    color: #b45309;
}
.status-pill-success {
    background: #dcfce7;
    color: #15803d;
}
.status-pill-secondary {
    background: #f1f5f9;
    color: #64748b;
}

/* ===== Action Buttons ===== */
.btn-action-sign {
    background: #0284c7;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 15px;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease-in-out;
}
.btn-action-sign:hover {
    background: #0369a1;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}
.btn-action-view {
    background: #10b981;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 15px;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease-in-out;
}
.btn-action-view:hover {
    background: #059669;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
}

/* ===== Empty State ===== */
.empty-contracts {
    text-align: center;
    padding: 50px 20px;
    color: #64748b;
}
.empty-contracts i {
    font-size: 44px;
    color: #cbd5e1;
    margin-bottom: 12px;
}

/* ===== Modal Tanda Tangan & Preview ===== */
#modal_kol_sign_contract .modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}
#modal_kol_sign_contract .modal-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 22px;
}
#modal_kol_sign_contract .modal-title {
    font-weight: 700;
    color: #1e293b;
    font-size: 16px;
}
#modal_kol_sign_contract .modal-body {
    background: #f1f5f9;
    padding: 20px;
}

/* Document Sheet Preview */
.contract-doc-container {
    background: #334155;
    padding: 20px 10px;
    border-radius: 8px;
    max-height: 590px;
    overflow-y: auto;
    display: flex;
    justify-content: center;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.3);
}
.contract-doc-sheet {
    background: #ffffff;
    width: 100%;
    max-width: 450px;
    min-height: 620px;
    padding: 30px 24px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.35);
    border-radius: 3px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-size: 12px;
    line-height: 1.6;
    color: #1e293b;
    box-sizing: border-box;
}

/* Right Panel */
.kol-sign-panel {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}
.kol-sign-tabs {
    display: flex;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 6px;
    margin-bottom: 18px;
    gap: 4px;
}
.kol-sign-tab-btn {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 5px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}
.kol-sign-tab-btn.active {
    background: #ffffff;
    color: #0284c7;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.kol-sign-tab-pane {
    display: none;
}
.kol-sign-tab-pane.active {
    display: block;
}

/* Signature Pad Canvas */
#kol-modal-signature-pad {
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    cursor: crosshair;
    background: #fff;
    width: 100%;
    max-width: 320px;
    height: 150px;
    touch-action: none;
    display: block;
    margin: 0 auto;
}
</style>

<div class="row mb-4">
  <div class="col-12">
    <div class="section-card">

      <!-- Header -->
      <div class="card-header-custom">
        <div>
          <div class="section-title">
            <i class="fas fa-file-contract text-primary me-2"></i> Perjanjian Kerja Sama
          </div>
          <div class="section-subtitle">Daftar Perjanjian Kerja Sama (KOL Contract)</div>
        </div>
        <span class="count-badge"><?= count($contracts) ?> Kontrak</span>
      </div>

      <!-- Table Body -->
      <div class="p-0">
        <div class="table-responsive">
          <table class="contract-table">
            <thead>
              <tr>
                <th style="width: 50px; text-align: center;">No</th>
                <th>Nomor Kontrak</th>
                <th>Campaign</th>
                <th>Brand</th>
                <th>Nilai Kerja Sama (Fee)</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center; width: 220px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($contracts)): ?>
                <?php $no = 1; foreach ($contracts as $c): ?>
                  <?php
                    $isSigned  = !empty($c['signed_at']);
                    $rawStatus = strtoupper((string)($c['contract_status'] ?? ''));
                    if ($isSigned) {
                        $rawStatus = 'SIGNED';
                    } elseif (empty($rawStatus) || $rawStatus === 'SENT') {
                        $rawStatus = 'WAITING_KOL_SIGNATURE';
                    }
                  ?>
                  <tr>
                    <td style="text-align: center; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                    <td style="font-weight: 600; color: #0284c7;">
                      <?= esc($c['contract_number'] ?? 'CTR-' . $c['id']) ?>
                    </td>
                    <td style="font-weight: 600; color: #1e293b;">
                      <?= esc($c['campaign_title'] ?? 'Campaign') ?>
                    </td>
                    <td style="color: #475569;">
                      Kolab.id
                    </td>
                    <td style="font-weight: 700; color: #16a34a;">
                      Rp <?= number_format((float)($c['deal_amount'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td style="text-align: center;">
                      <?php if ($rawStatus === 'SIGNED' || $rawStatus === 'ACTIVE' || $rawStatus === 'COMPLETED'): ?>
                        <span class="status-pill status-pill-success">
                          <i class="fas fa-check-circle"></i> Selesai Ditandatangani
                        </span>
                      <?php elseif ($rawStatus === 'WAITING_KOL_SIGNATURE' || $rawStatus === 'PENDING'): ?>
                        <span class="status-pill status-pill-warning">
                          <i class="fas fa-clock"></i> Menunggu Tanda Tangan Anda
                        </span>
                      <?php else: ?>
                        <span class="status-pill status-pill-secondary">
                          Draft Admin
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                      <?php if ($rawStatus === 'WAITING_KOL_SIGNATURE' || $rawStatus === 'PENDING'): ?>
                        <button type="button" class="btn-action-sign btn-open-kol-sign" data-id="<?= (int)$c['id'] ?>">
                          <i class="fas fa-pencil-alt"></i> Tanda Tangani Kontrak
                        </button>
                      <?php else: ?>
                        <button type="button" class="btn-action-view btn-open-kol-sign" data-id="<?= (int)$c['id'] ?>">
                          <i class="fas fa-eye"></i> Lihat Dokumen
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7">
                    <div class="empty-contracts">
                      <i class="fas fa-file-signature d-block"></i>
                      Belum ada kontrak kerja sama yang diterbitkan untuk akun Anda.
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ===== MODAL TANDA TANGAN & PREVIEW (Style Sistem Lama) ===== -->
<div class="modal fade" id="modal_kol_sign_contract" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="width: 95%; max-width: 1050px;">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title mb-0">
          <i class="fas fa-file-signature text-primary me-2"></i> Tanda Tangan Elektronik Kontrak Kerja Sama
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body (2-Column Split: Kiri Preview Dokumen A4, Kanan Form TTD) -->
      <div class="modal-body">
        <input type="hidden" id="kol_sign_contract_id" value="">

        <div class="row g-3">
          <!-- Kiri: A4 Portrait Contract Document Preview -->
          <div class="col-lg-7">
            <div class="contract-doc-container">
              <div id="modal_kol_contract_preview" class="contract-doc-sheet">
                <div style="text-align: center; color: #64748b; padding: 150px 20px;">
                  <i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>
                  Memuat pratinjau kontrak...
                </div>
              </div>
            </div>
          </div>

          <!-- Kanan: Signature Form & Status for KOL -->
          <div class="col-lg-5">

            <!-- Panel Form Tanda Tangan -->
            <div id="kol_sign_action_panel" class="kol-sign-panel">
              <label style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: block;">
                <i class="fas fa-hand-pointer text-primary me-1"></i> Pilih Cara Tanda Tangan:
              </label>

              <!-- Nav Tabs -->
              <div class="kol-sign-tabs" id="kol_sign_option_tabs">
                <button type="button" class="kol-sign-tab-btn active" data-tab="draw">
                  <i class="fas fa-paint-brush me-1"></i> 1. Draw E-Sign
                </button>
                <button type="button" class="kol-sign-tab-btn" data-tab="upload">
                  <i class="fas fa-cloud-upload-alt me-1"></i> 2. Upload File
                </button>
              </div>

              <!-- Tab Content: Draw Signature -->
              <div id="tab_kol_draw_sign" class="kol-sign-tab-pane active">
                <p style="font-size: 12px; color: #64748b; margin-bottom: 8px;">
                  Goreskan tanda tangan Anda pada kanvas berikut:
                </p>
                <div style="text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 10px;">
                  <canvas id="kol-modal-signature-pad" width="320" height="150"></canvas>
                  <div style="margin-top: 8px; text-align: right;">
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" id="kol-modal-clear-signature" style="font-size: 11px; border-radius: 4px;">
                      <i class="fas fa-eraser me-1"></i> Hapus / Clear
                    </button>
                  </div>
                </div>

                <button type="button" class="btn btn-primary w-100 fw-bold py-2 mt-3" id="btn_kol_submit_draw_sign" style="border-radius: 6px; font-size: 13.5px; background-color: #0284c7; border-color: #0284c7;">
                  <i class="fas fa-check-circle me-1"></i> Setujui &amp; Tanda Tangani Kontrak
                </button>
              </div>

              <!-- Tab Content: Upload File -->
              <div id="tab_kol_upload_sign" class="kol-sign-tab-pane">
                <p style="font-size: 12px; color: #64748b; margin-bottom: 8px;">
                  Upload scan tanda tangan Anda (JPG/PNG) atau dokumen kontrak bertanda tangan:
                </p>
                <form id="form_kol_sign_contract_upload" enctype="multipart/form-data">
                  <?= csrf_field() ?>
                  <div class="mb-3">
                    <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                      Pilih Gambar Tanda Tangan:
                    </label>
                    <input type="file" name="contract_file" id="kol_sign_contract_file" class="form-control form-control-sm" accept="image/*" required>
                  </div>
                  <button type="submit" class="btn btn-success w-100 fw-bold py-2" id="btn_kol_submit_upload_sign" style="border-radius: 6px; font-size: 13.5px; background-color: #059669; border-color: #059669;">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Upload &amp; Tanda Tangani Kontrak
                  </button>
                </form>
              </div>
            </div>

            <!-- Notice jika sudah ditandatangani -->
            <div id="kol_signed_notice" style="display: none; padding: 22px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; text-align: center;">
              <i class="fas fa-check-circle text-success mb-2" style="font-size: 40px;"></i>
              <h5 style="font-weight: 700; color: #065f46; margin: 0 0 5px 0; font-size: 16px;">Kontrak Telah Ditandatangani</h5>
              <p style="font-size: 12.5px; color: #047857; margin-bottom: 0;">Dokumen ini telah resmi berlaku dan mengikat kedua belah pihak.</p>
            </div>

            <!-- Legal Disclaimer Badge -->
            <div style="margin-top: 15px; padding: 12px 15px; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 6px; font-size: 11.5px; color: #0369a1; line-height: 1.5;">
              <i class="fas fa-shield-alt me-1"></i> Dengan menandatangani secara elektronik, Anda menyatakan menyetujui seluruh butir perjanjian kerja sama yang tercantum pada dokumen.
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
(function() {
    'use strict';

    var kolCanvas, kolCtx, isKolDrawing = false, kolSignPos = { x: 0, y: 0 };
    var kolCurrentName = '<?= esc(session('name') ?? 'KOL / Creator') ?>';
    var kolAdminSignHtml = '';
    var rawContractContent = '';

    function initKolSignaturePad() {
        kolCanvas = document.getElementById('kol-modal-signature-pad');
        if (!kolCanvas) return;
        kolCtx = kolCanvas.getContext('2d');

        function setPos(e) {
            var rect = kolCanvas.getBoundingClientRect();
            var clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            var clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            var scaleX = kolCanvas.width / rect.width;
            var scaleY = kolCanvas.height / rect.height;
            kolSignPos.x = (clientX - rect.left) * scaleX;
            kolSignPos.y = (clientY - rect.top) * scaleY;
        }

        function startDraw(e) {
            setPos(e);
            isKolDrawing = true;
        }

        function drawing(e) {
            if (!isKolDrawing) return;
            e.preventDefault();
            kolCtx.beginPath();
            kolCtx.lineWidth = 2.5;
            kolCtx.lineCap = 'round';
            kolCtx.lineJoin = 'round';
            kolCtx.strokeStyle = '#0f172a';
            kolCtx.moveTo(kolSignPos.x, kolSignPos.y);
            setPos(e);
            kolCtx.lineTo(kolSignPos.x, kolSignPos.y);
            kolCtx.stroke();
        }

        function stopDraw() {
            if (isKolDrawing) {
                isKolDrawing = false;
                updateKolLivePreview(kolCanvas.toDataURL('image/png'));
            }
        }

        kolCanvas.addEventListener('mousedown', startDraw);
        kolCanvas.addEventListener('mousemove', drawing);
        kolCanvas.addEventListener('mouseup', stopDraw);
        kolCanvas.addEventListener('mouseleave', stopDraw);

        kolCanvas.addEventListener('touchstart', startDraw, { passive: true });
        kolCanvas.addEventListener('touchmove', drawing, { passive: false });
        kolCanvas.addEventListener('touchend', stopDraw);
    }

    function updateKolLivePreview(dataUrl) {
        var existingImg = document.getElementById('preview-kol-sign-img');
        if (existingImg) {
            existingImg.src = dataUrl;
            existingImg.style.display = 'block';
            return;
        }

        var signBlock = '<div id="kol-preview-sign-wrapper" style="margin-top: 24px; border-top: 1px dashed #94a3b8; padding-top: 14px;">' +
            '<table style="width: 100%; border: none;">' +
                '<tr>' +
                    '<td style="width: 50%; text-align: left; vertical-align: top;">' +
                        '<div style="font-size: 10px; color: #64748b; font-weight: bold; margin-bottom: 4px;">Pihak Kedua (KOL / Talent):</div>' +
                        '<div style="min-height: 48px; margin-bottom: 6px;">' +
                            '<img id="preview-kol-sign-img" src="' + dataUrl + '" style="max-height: 48px; max-width: 140px; object-fit: contain;">' +
                        '</div>' +
                        '<div style="font-size: 10.5px; font-weight: bold; color: #0f172a; border-top: 1px solid #cbd5e1; display: inline-block; padding-top: 3px; min-width: 130px;">' + kolCurrentName + '</div>' +
                        '<div style="font-size: 9px; color: #64748b;">KOL / Creator</div>' +
                    '</td>' +
                    '<td style="width: 50%; text-align: right; vertical-align: top;">' +
                        '<div style="font-size: 10px; color: #64748b; font-weight: bold; margin-bottom: 4px;">Pihak Pertama (Brand / Agency):</div>' +
                        '<div style="min-height: 48px; margin-bottom: 6px;">' +
                            (kolAdminSignHtml || '<span style="color: #94a3b8; font-size: 10px; font-style: italic;">[Kolab.id Team]</span>') +
                        '</div>' +
                        '<div style="font-size: 10.5px; font-weight: bold; color: #0f172a; border-top: 1px solid #cbd5e1; display: inline-block; padding-top: 3px; min-width: 130px;">Kolab.id Authorized</div>' +
                        '<div style="font-size: 9px; color: #64748b;">Brand / Agency Partner</div>' +
                    '</td>' +
                '</tr>' +
            '</table>' +
        '</div>';

        var previewContainer = document.getElementById('modal_kol_contract_preview');
        if (previewContainer) {
            previewContainer.insertAdjacentHTML('beforeend', signBlock);
        }
    }

    function clearKolSignaturePad() {
        if (kolCtx && kolCanvas) {
            kolCtx.clearRect(0, 0, kolCanvas.width, kolCanvas.height);
            var wrapper = document.getElementById('kol-preview-sign-wrapper');
            if (wrapper) wrapper.remove();
        }
    }

    function isKolCanvasEmpty() {
        if (!kolCanvas) return true;
        var blank = document.createElement('canvas');
        blank.width = kolCanvas.width;
        blank.height = kolCanvas.height;
        return kolCanvas.toDataURL() === blank.toDataURL();
    }

    // Tab switcher
    document.querySelectorAll('.kol-sign-tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.kol-sign-tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.kol-sign-tab-pane').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var tabId = 'tab_kol_' + this.dataset.tab + '_sign';
            var pane = document.getElementById(tabId);
            if (pane) pane.classList.add('active');
        });
    });

    // Button open modal
    document.querySelectorAll('.btn-open-kol-sign').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var id = this.dataset.id;
            document.getElementById('kol_sign_contract_id').value = id;
            var fileInput = document.getElementById('kol_sign_contract_file');
            if (fileInput) fileInput.value = '';

            var previewEl = document.getElementById('modal_kol_contract_preview');
            previewEl.innerHTML = '<div style="text-align: center; color: #64748b; padding: 150px 20px;"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>Memuat pratinjau kontrak...</div>';

            var modalEl = document.getElementById('modal_kol_sign_contract');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();

            setTimeout(function() {
                initKolSignaturePad();
                clearKolSignaturePad();
            }, 300);

            // Fetch detail & html via AJAX
            fetch('<?= base_url('my-contracts/preview/') ?>' + id)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success && res.html) {
                        if (res.kol_name) kolCurrentName = res.kol_name;
                        rawContractContent = res.html;
                        previewEl.innerHTML = res.html;

                        if (res.status === 'SIGNED') {
                            document.getElementById('kol_sign_action_panel').style.display = 'none';
                            document.getElementById('kol_signed_notice').style.display = 'block';

                            // Render stored signature in preview
                            if (res.signature_image) {
                                updateKolLivePreview(res.signature_image);
                            }
                        } else {
                            document.getElementById('kol_sign_action_panel').style.display = 'block';
                            document.getElementById('kol_signed_notice').style.display = 'none';
                        }
                    } else {
                        previewEl.innerHTML = '<div style="color: #e11d48; text-align: center; padding: 50px 20px;">Gagal memuat dokumen kontrak.</div>';
                    }
                })
                .catch(function() {
                    previewEl.innerHTML = '<div style="color: #e11d48; text-align: center; padding: 50px 20px;">Terjadi kesalahan jaringan saat memuat kontrak.</div>';
                });
        });
    });

    // Clear signature button
    document.getElementById('kol-modal-clear-signature')?.addEventListener('click', function(e) {
        e.preventDefault();
        clearKolSignaturePad();
    });

    // File change preview
    document.getElementById('kol_sign_contract_file')?.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file && file.type.match('image.*')) {
            var reader = new FileReader();
            reader.onload = function(evt) {
                updateKolLivePreview(evt.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Submit Draw Signature
    document.getElementById('btn_kol_submit_draw_sign')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (isKolCanvasEmpty()) {
            alert('Silakan goreskan tanda tangan Anda terlebih dahulu pada kanvas.');
            return;
        }

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan Tanda Tangan...';

        var body = new URLSearchParams();
        body.append('id', document.getElementById('kol_sign_contract_id').value);
        body.append('signature', kolCanvas.toDataURL('image/png'));
        var csrf = document.querySelector('input[name="csrf_token"]');
        if (csrf) body.append('csrf_token', csrf.value);

        fetch('<?= base_url('my-contracts/sign') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                alert('Selamat! Kontrak berhasil ditandatangani dan diaktifkan.');
                window.location.reload();
            } else {
                alert('Gagal: ' + res.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Setujui & Tanda Tangani Kontrak';
            }
        })
        .catch(function() {
            alert('Terjadi kesalahan jaringan.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Setujui & Tanda Tangani Kontrak';
        });
    });

    // Submit Upload Signature
    document.getElementById('form_kol_sign_contract_upload')?.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('btn_kol_submit_upload_sign');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengunggah...';

        var formData = new FormData(this);
        formData.set('id', document.getElementById('kol_sign_contract_id').value);

        fetch('<?= base_url('my-contracts/sign') ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                alert('Selamat! Dokumen tanda tangan berhasil disimpan.');
                window.location.reload();
            } else {
                alert(res.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i> Upload & Tanda Tangani Kontrak';
            }
        })
        .catch(function() {
            alert('Terjadi kesalahan jaringan.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cloud-upload-alt me-1"></i> Upload & Tanda Tangani Kontrak';
        });
    });

})();
</script>

<?= $this->endSection() ?>
