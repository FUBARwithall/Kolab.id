<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-bullhorn text-warning me-2"></i> Peluang Kampanye Terbuka
          </h5>
          <small class="text-muted">Pilih dan ajukan kolaborasi untuk kampanye brand yang sedang aktif</small>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
          <?= count($campaigns) ?> Kampanye Tersedia
        </span>
      </div>
      <div class="card-body p-4">
        <?php if (empty($campaigns)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-bullhorn fa-3x mb-3 text-secondary opacity-50"></i>
            <h6>Belum Ada Kampanye Terbuka</h6>
            <p class="small">Saat ini belum ada kampanye baru yang membuka penerimaan kreator.</p>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($campaigns as $camp): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 border border-light shadow-sm rounded-3">
                  <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div>
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-success-subtle text-success fw-bold text-uppercase" style="font-size: 10px;">
                          <?= esc($camp['status'] ?? 'ACTIVE') ?>
                        </span>
                        <small class="text-muted" style="font-size: 11px;">
                          <i class="fas fa-users me-1 text-primary"></i> <?= (int) ($camp['total_applicants'] ?? 0) ?> Pelamar
                        </small>
                      </div>
                      <h6 class="fw-bold text-dark mb-1"><?= esc($camp['title']) ?></h6>
                      <p class="text-muted small mb-2" style="font-size: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= esc($camp['description'] ?? 'Kolaborasi kampanye brand bersama KOL & Creator pilihan.') ?>
                      </p>
                    </div>
                    <div class="pt-2 border-top mt-2">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="text-muted" style="font-size: 11px;">Alokasi Budget</small>
                        <span class="fw-bold text-success" style="font-size: 13px;">Rp <?= number_format((float) ($camp['budget_total'] ?? 0), 0, ',', '.') ?></span>
                      </div>
                      <div class="d-flex align-items-center justify-content-between mb-3">
                        <small class="text-muted" style="font-size: 11px;">Periode</small>
                        <small class="text-dark fw-semibold" style="font-size: 11px;"><?= esc($camp['start_date'] ?? '-') ?> s/d <?= esc($camp['end_date'] ?? '-') ?></small>
                      </div>
                      <button type="button" class="btn btn-warning btn-sm w-100 fw-bold rounded-pill text-dark shadow-sm" onclick="openApplyModal(<?= $camp['id'] ?>, '<?= esc(addslashes($camp['title'])) ?>', <?= (float)($camp['budget_total'] ?? 0) ?>)">
                        <i class="fas fa-paper-plane me-1"></i> Lamar Kampanye
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Lamar Kampanye -->
<div class="modal fade" id="modalApplyCampaign" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold text-dark mb-0">
          <i class="fas fa-paper-plane text-warning me-2"></i> Ajukan Lamaran Kolaborasi
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formApplyCampaign">
        <input type="hidden" name="campaign_id" id="apply_campaign_id">
        <div class="modal-body p-4">
          <div class="alert alert-light border mb-3">
            <small class="text-muted d-block mb-1">Kampanye yang dipilih:</small>
            <div class="fw-bold text-dark fs-6" id="apply_campaign_title">-</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Tarif Penawaran (IDR) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted">Rp</span>
              <input type="number" class="form-control" name="proposed_rate" id="apply_proposed_rate" required placeholder="Contoh: 3000000" min="100000" step="50000">
            </div>
            <small class="text-muted text-xs">Masukkan penawaran harga Anda untuk keseluruhan paket VT/konten pada kampanye ini.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold text-dark text-xs text-uppercase">Ide Konten / Pitching Notes</label>
            <textarea class="form-control" name="pitch_notes" id="apply_pitch_notes" rows="3" placeholder="Ceritakan konsep video singkat, angle produk, atau insight unik yang ingin Anda bawakan..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light border-top py-2.5">
          <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold text-dark" id="btnSubmitApply">
            <i class="fas fa-paper-plane me-1"></i> Kirim Lamaran
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openApplyModal(id, title, budget) {
  $('#apply_campaign_id').val(id);
  $('#apply_campaign_title').text(title);
  $('#apply_proposed_rate').val('');
  $('#apply_pitch_notes').val('');
  new bootstrap.Modal(document.getElementById('modalApplyCampaign')).show();
}

$(document).ready(function() {
  $('#formApplyCampaign').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#btnSubmitApply');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Mengirim...');

    $.ajax({
      url: '<?= base_url('apply-campaign') ?>',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Kirim Lamaran');
        if (res.success) {
          bootstrap.Modal.getInstance(document.getElementById('modalApplyCampaign')).hide();
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: res.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '<?= base_url('my-applications') ?>';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: res.message || 'Terjadi kesalahan saat mengajukan lamaran.'
          });
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Kirim Lamaran');
        Swal.fire({
          icon: 'error',
          title: 'Error Jaringan',
          text: 'Gagal terhubung ke server (' + xhr.status + ').'
        });
      }
    });
  });
});
</script>

<?= $this->endSection() ?>
