<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-contract text-primary me-2"></i>Kontrak &amp; MoU Saya</h3>
        <p class="text-muted small mb-0">Daftar surat perjanjian kerjasama, status tanda tangan digital, dan penerbitan invoice honorarium.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Kontrak Saya</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <?php if (!empty($unlinked_kol)): ?>
      <div class="alert alert-warning border-0 shadow-sm rounded-3 p-4 mb-4" role="alert">
        <div class="d-flex align-items-center">
          <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
          <div>
            <h5 class="alert-heading fw-bold mb-1">Akun Belum Terhubung ke Profil KOL</h5>
            <p class="mb-0 small">Akun Anda saat ini belum terhubung ke profil talent KOL. Silakan hubungi tim administrator/manager agar akun Anda ditautkan untuk melihat dokumen kontrak kerjasama.</p>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
          <i class="fas fa-signature text-success me-2"></i>Daftar Kontrak &amp; MoU (<?= count($contracts) ?>)
        </h5>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th>No. Kontrak</th>
                <th>Kampanye &amp; Brand</th>
                <th>Nilai Kompensasi</th>
                <th>Status MoU</th>
                <th>Tagihan / Invoice</th>
                <th>Tanggal Terbit</th>
                <th style="width: 200px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($contracts)): ?>
                <?php $no = 1; foreach ($contracts as $c): ?>
                  <?php
                    $isSigned = !empty($c['signed_at']) || in_array(strtoupper($c['status']), ['ACTIVE', 'SIGNED']);
                    $ctrNo = $c['display_contract_no'] ?: ($c['contract_no'] ?: ($c['contract_number'] ?? 'CTR-' . $c['id']));
                  ?>
                  <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="font-monospace fw-bold text-dark"><?= esc($ctrNo) ?></td>
                    <td>
                      <div class="fw-semibold text-primary"><?= esc($c['campaign_title'] ?: ($c['campaign_name'] ?? '-')) ?></div>
                      <?php if (!empty($c['brand_name'])): ?>
                        <small class="text-muted d-block">Brand: <?= esc($c['brand_name']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong class="text-success">Rp <?= number_format((float)($c['deal_amount'] ?? 0), 0, ',', '.') ?></strong>
                      <div class="small text-muted"><?= esc((string)($c['total_vt'] ?? 1)) ?> Video</div>
                    </td>
                    <td>
                      <?php if ($isSigned): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                          <i class="fas fa-check-circle me-1"></i> AKTIF (TTD Sah)
                        </span>
                      <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                          <i class="far fa-clock me-1"></i> MENUNGGU TTD KOL
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($c['invoice_id'])): ?>
                        <?php
                          $invSt = strtoupper($c['invoice_status'] ?? 'PENDING');
                          $invBadge = match ($invSt) {
                              'PAID'      => 'bg-success',
                              'PARTIAL'   => 'bg-warning text-dark',
                              'APPROVED'  => 'bg-primary',
                              'SUBMITTED' => 'bg-info text-dark',
                              default     => 'bg-secondary'
                          };
                        ?>
                        <span class="badge <?= $invBadge ?> px-2 py-1"><?= $invSt ?></span>
                        <div class="font-monospace small text-muted mt-1"><?= esc($c['invoice_number'] ?? '') ?></div>
                        <a href="<?= base_url('invoices/print-pdf/' . $c['invoice_id']) ?>" target="_blank" class="btn btn-outline-secondary btn-xs py-0 px-1 mt-1 small">
                          <i class="fas fa-print me-1"></i> Cetak Invoice
                        </a>
                      <?php elseif ($isSigned): ?>
                        <button type="button" class="btn btn-success btn-sm rounded-2 py-1 px-2 shadow-sm" onclick="kolIssueInvoice(<?= $c['id'] ?>, '<?= esc($ctrNo) ?>')">
                          <i class="fas fa-paper-plane me-1"></i> Terbitkan Invoice
                        </button>
                      <?php else: ?>
                        <span class="small text-muted">Menunggu TTD MoU</span>
                      <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                      <?= !empty($c['created_at']) ? date('d M Y', strtotime($c['created_at'])) : '-' ?>
                    </td>
                    <td class="text-center">
                      <div class="d-flex gap-1 justify-content-center">
                        <a href="<?= base_url('contracts/print/' . $c['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Lihat Dokumen Kontrak">
                          <i class="fas fa-eye me-1"></i> Naskah
                        </a>
                        <?php if (!$isSigned): ?>
                          <button type="button" class="btn btn-primary btn-sm rounded-2 py-1 px-2 shadow-sm" onclick="openKolSignModal(<?= $c['id'] ?>)" title="Tandatangani Surat Perjanjian">
                            <i class="fas fa-pen-nib me-1"></i> TTD MoU
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">
                    <i class="fas fa-file-contract fa-3x mb-3 text-black-50 d-block"></i>
                    Belum ada dokumen kontrak kerjasama yang ditugaskan ke akun Anda.
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

<!-- Modal E-Sign TTD MoU KOL -->
<div class="modal fade" id="modalKolSign" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-3">
      <div class="modal-header bg-light border-bottom py-3">
        <h5 class="modal-title fw-bold text-dark">
          <i class="fas fa-file-signature text-primary me-2"></i>Review &amp; Tanda Tangan Surat Perjanjian Kerjasama (MoU)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4 bg-light">
        <input type="hidden" id="signContractId" value="">

        <div class="row g-4">
          <!-- Left: Contract Naskah Preview -->
          <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
              <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="small fw-bold text-muted text-uppercase">Naskah Dokumen Perjanjian</span>
                <span class="badge bg-primary-subtle text-primary" id="previewContractNo">CTR-...</span>
              </div>
              <div class="card-body p-4 bg-white" style="max-height: 480px; overflow-y: auto; font-size: 13px; line-height: 1.6;" id="previewContractBody">
                <div class="text-center text-muted py-5">
                  <div class="spinner-border text-primary mb-2" role="status"></div>
                  <div>Memuat naskah kontrak...</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Canvas Signature Pad -->
          <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
              <div class="card-header bg-white py-2 px-3 border-bottom">
                <span class="small fw-bold text-muted text-uppercase">Goreskan Tanda Tangan Digital (E-Sign)</span>
              </div>
              <div class="card-body p-4">
                <p class="small text-muted mb-2">Tandatangani pada area kanvas di bawah menggunakan jari (touchscreen) atau mouse kursor:</p>

                <div class="border rounded-2 bg-white text-center p-2 mb-3 position-relative" style="box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
                  <canvas id="signatureCanvas" width="400" height="180" style="touch-action: none; cursor: crosshair; max-width: 100%; border: 1px dashed #cbd5e1; border-radius: 4px; background: #fafafa;"></canvas>
                  <div class="small text-muted mt-1" style="font-size: 11px;">Area Tanda Tangan Digital</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                  <button type="button" class="btn btn-outline-secondary btn-sm rounded-2" onclick="clearSignatureCanvas()">
                    <i class="fas fa-eraser me-1"></i> Bersihkan Kanvas
                  </button>
                  <span class="badge bg-light text-muted border">Sah Sesuai UU ITE</span>
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="checkAgreement" required>
                  <label class="form-check-label small text-muted" for="checkAgreement">
                    Saya telah membaca, memahami, dan menyetujui seluruh klausul &amp; S&amp;K surat perjanjian kerjasama ini.
                  </label>
                </div>

                <div class="d-grid gap-2">
                  <button type="button" class="btn btn-success py-2 rounded-2 shadow-sm fw-bold" id="btnSubmitSign" onclick="submitKolSignature()">
                    <i class="fas fa-check-circle me-1"></i> Konfirmasi &amp; Tandatangani MoU
                  </button>
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
let canvas, ctx, isDrawing = false;

function initCanvas() {
    canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;
    ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function start(e) {
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function move(e) {
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stop() {
        isDrawing = false;
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', stop);

    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    window.addEventListener('touchend', stop);
}

function clearSignatureCanvas() {
    if (ctx && canvas) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
}

function openKolSignModal(contractId) {
    $('#signContractId').val(contractId);
    $('#checkAgreement').prop('checked', false);
    clearSignatureCanvas();

    const modal = new bootstrap.Modal(document.getElementById('modalKolSign'));
    modal.show();

    setTimeout(initCanvas, 300);

    // Load preview HTML
    $.get('<?= base_url('contracts/get_contract_html_ajax/') ?>' + contractId, function(res) {
        if (res.success) {
            $('#previewContractNo').text(res.contract_no || 'CTR-' + contractId);
            let bodyHtml = res.html || '';
            if (res.terms_and_conditions) {
                bodyHtml += '<hr><h6><b>Syarat & Ketentuan (S&K):</b></h6><div style="white-space: pre-wrap;">' + res.terms_and_conditions + '</div>';
            }
            $('#previewContractBody').html(bodyHtml);
        }
    });
}

function submitKolSignature() {
    if (!$('#checkAgreement').is(':checked')) {
        alert('Harap centang persetujuan syarat & ketentuan terlebih dahulu.');
        return;
    }

    const contractId = $('#signContractId').val();
    const dataUrl = canvas.toDataURL('image/png');

    $('#btnSubmitSign').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan Tanda Tangan...');

    $.post('<?= base_url('contracts/kol_sign_action') ?>', {
        id: contractId,
        signature: dataUrl,
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
    }, function(res) {
        if (res.success) {
            alert(res.message);
            window.location.reload();
        } else {
            alert(res.message || 'Gagal menyimpan tanda tangan.');
            $('#btnSubmitSign').prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Konfirmasi & Tandatangani MoU');
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON?.message || 'Terjadi kesalahan sistem.');
        $('#btnSubmitSign').prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Konfirmasi & Tandatangani MoU');
    });
}

function kolIssueInvoice(contractId, contractNo) {
    if (!confirm('Apakah Anda ingin menerbitkan invoice resmi untuk kontrak ' + contractNo + '?')) {
        return;
    }

    $.post('<?= base_url('contracts/kol_issue_invoice_ajax') ?>', {
        contract_id: contractId,
        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
    }, function(res) {
        if (res.success) {
            alert(res.message);
            window.location.reload();
        } else {
            alert(res.message || 'Gagal menerbitkan invoice.');
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON?.message || 'Terjadi kesalahan sistem.');
    });
}
</script>
<?= $this->endSection() ?>
