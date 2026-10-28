<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-file-signature text-primary me-2"></i>Kontrak KOL &amp; Komisi</h3>
        <p class="text-muted small mb-0">Manajemen surat perjanjian kerjasama resmi, klausul kesepakatan SOW, fee, hak tayang konten, dan status penandatanganan.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Kontrak</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterCampaignKtr" style="min-width: 220px;" onchange="reloadContractTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <button type="button" class="btn btn-primary rounded-2 btn-sm px-3 shadow-sm" onclick="generateContractModal()">
              <i class="fas fa-plus me-1"></i> Generate Kontrak Baru
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableContracts" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 180px;">Nomor Kontrak</th>
                <th>Kampanye</th>
                <th>Talent KOL</th>
                <th style="width: 180px;">Nilai Kompensasi</th>
                <th style="width: 150px;">Status Penandatanganan</th>
                <th style="width: 110px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Generate Kontrak Baru -->
<div class="modal fade" id="modalGenContract" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formGenContract" onsubmit="submitGenContract(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-file-contract me-2"></i>Generate Surat Perjanjian Kontrak</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Penugasan KOL <span class="text-danger">*</span></label>
            <select class="form-select" id="ktrCampaignKolId" name="campaign_kol_id" required>
              <option value="">-- Pilih Penugasan KOL --</option>
              <?php foreach ($assignedKols as $ak): ?>
                <option value="<?= $ak['id'] ?>">
                  <?= esc($ak['kol_name']) ?> — <?= esc($ak['campaign_title']) ?> (Rp <?= number_format((float)$ak['deal_amount'], 0, ',', '.') ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Klausul pasal, hak &amp; kewajiban otomatis dirender dari database.</small>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSubmitGenKtr">Generate Dokumen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Baca Surat Kontrak -->
<div class="modal fade" id="modalViewContract" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewContractTitle"><i class="fas fa-file-signature text-primary me-2"></i>Naskah Perjanjian Kerjasama</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="card bg-light border p-3 font-monospace small" style="white-space: pre-wrap; line-height: 1.6;" id="contractClausesBody">
          Memuat klausul...
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
let dtContracts = null;

$(document).ready(function() {
    dtContracts = $('#tableContracts').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('contracts/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignKtr').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'contract_no' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'fee' },
            { data: 'status' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari nomor kontrak, KOL...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ kontrak",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadContractTable() {
    dtContracts.ajax.reload();
}

function generateContractModal() {
    $('#formGenContract')[0].reset();
    $('#modalGenContract').modal('show');
}

function submitGenContract(e) {
    e.preventDefault();
    const btn = $('#btnSubmitGenKtr');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    $.post('<?= base_url('contracts/generate') ?>', $('#formGenContract').serialize(), function(res) {
        btn.prop('disabled', false).text('Generate Dokumen');
        if (res.success) {
            $('#modalGenContract').modal('hide');
            dtContracts.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Generate Dokumen');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewContract(id) {
    $('#contractClausesBody').text('Memuat klausul perjanjian...');
    $('#modalViewContract').modal('show');

    $.get('<?= base_url('contracts/get-one/') ?>' + id, function(res) {
        if (res.success) {
            $('#contractClausesBody').text(res.data.clauses_rendered);
        }
    });
}
</script>
<?= $this->endSection() ?>
