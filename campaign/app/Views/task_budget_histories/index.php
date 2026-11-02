<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-file-invoice-dollar text-success me-2"></i>Task Budget Histories
        </h3>
        <p class="text-muted small mb-0">Audit trail penyesuaian tarif, negosiasi fee, dan kompensasi add-on per-konten task kolaborasi.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <div class="btn-group me-2">
          <button type="button" class="btn btn-outline-success btn-sm rounded-2 px-3" onclick="exportBudgetCsv()" title="Export CSV Data Riwayat Anggaran">
            <i class="fas fa-file-excel me-1"></i> Excel / CSV
          </button>
          <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3" onclick="exportBudgetPdf()" title="Print Laporan PDF">
            <i class="fas fa-file-pdf me-1"></i> Print PDF
          </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" onclick="reloadBudgetTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Stat Cards Overview -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-history fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Penyesuaian</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($metrics['total_adjustments']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
                <i class="fas fa-arrow-trend-up fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Kenaikan Tarif (<?= number_format($metrics['count_increase']) ?>x)</span>
                <h4 class="fw-bold mb-0 text-success">+Rp <?= number_format($metrics['total_increase_amount'], 0, ',', '.') ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3">
                <i class="fas fa-arrow-trend-down fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Diskon / Negosiasi (<?= number_format($metrics['count_decrease']) ?>x)</span>
                <h4 class="fw-bold mb-0 text-danger">-Rp <?= number_format($metrics['total_decrease_amount'], 0, ',', '.') ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-money-bill-wave fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Nilai Tarif Baru</span>
                <h4 class="fw-bold mb-0 text-info">Rp <?= number_format($metrics['total_new_rate'], 0, ',', '.') ?></h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body p-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Aksi Masal</label>
            <div class="input-group input-group-sm">
              <select class="form-select" id="bulkBudgetAction">
                <option value="">-- Tindakan Masal --</option>
                <option value="delete">Hapus Riwayat Terpilih</option>
              </select>
              <button type="button" class="btn btn-outline-secondary" onclick="applyBulkBudget()">Terapkan</button>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Kampanye</label>
            <select class="form-select form-select-sm" id="filterCampaign" onchange="reloadBudgetTable()">
              <option value="">-- Semua Kampanye --</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($filters['campaign_id'] == $c['id']) ? 'selected' : '' ?>><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Task</label>
            <select class="form-select form-select-sm" id="filterTask" onchange="reloadBudgetTable()">
              <option value="">-- Semua Task --</option>
              <?php foreach ($allTasks as $t): ?>
                <option value="<?= $t['id'] ?>" <?= ($filters['task_id'] == $t['id']) ? 'selected' : '' ?>><?= esc($t['title'] ?: ('Task #' . $t['task_number'])) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Tipe Penyesuaian</label>
            <select class="form-select form-select-sm" id="filterAdjustmentType" onchange="reloadBudgetTable()">
              <option value="">-- Semua Tipe --</option>
              <option value="increase">Kenaikan Tarif (+)</option>
              <option value="decrease">Penurunan Tarif (-)</option>
              <option value="same">Tarif Tetap (=)</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Operator Pengesah</label>
            <select class="form-select form-select-sm" id="filterAuthorized" onchange="reloadBudgetTable()">
              <option value="">-- Semua Operator --</option>
              <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($filters['authorized_by'] == $u['id']) ? 'selected' : '' ?>><?= esc($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- DataTables Container -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableBudgetHistories" style="width: 100%;">
            <thead class="bg-light text-secondary border-bottom">
              <tr>
                <th style="width: 35px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllBudget" title="Pilih Semua">
                </th>
                <th style="width: 55px;" class="text-center">ID</th>
                <th style="width: 160px;">Task Konten</th>
                <th style="width: 180px;">Kampanye &amp; KOL</th>
                <th style="width: 130px;">Tarif Semula</th>
                <th style="width: 160px;">Tarif Baru &amp; Selisih</th>
                <th>Diotorisasi Oleh</th>
                <th>Alasan Perubahan</th>
                <th style="width: 135px;">Waktu Perubahan</th>
                <th style="width: 115px;" class="text-center">Tindakan</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtBudget = null;

$(document).ready(function() {
    dtBudget = $('#tableBudgetHistories').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('task-budget-histories/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id     = $('#filterCampaign').val();
                d.task_id         = $('#filterTask').val();
                d.adjustment_type = $('#filterAdjustmentType').val();
                d.authorized_by   = $('#filterAuthorized').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'id', className: 'text-center' },
            { data: 'task' },
            { data: 'campaign' },
            { data: 'old_rate' },
            { data: 'new_rate' },
            { data: 'authorized' },
            { data: 'reason' },
            { data: 'created_at' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari task, kampanye, KOL, alasan...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ riwayat",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });

    $('#checkAllBudget').on('change', function() {
        $('.check-history-item').prop('checked', this.checked);
    });

    $('#tableBudgetHistories tbody').on('change', '.check-history-item', function() {
        if (!this.checked) {
            $('#checkAllBudget').prop('checked', false);
        } else if ($('.check-history-item:checked').length === $('.check-history-item').length) {
            $('#checkAllBudget').prop('checked', true);
        }
    });
});

function reloadBudgetTable() {
    $('#checkAllBudget').prop('checked', false);
    dtBudget.ajax.reload();
}

function getFilterParams() {
    const cid = $('#filterCampaign').val();
    const tid = $('#filterTask').val();
    const adj = $('#filterAdjustmentType').val();
    const auth = $('#filterAuthorized').val();
    const params = new URLSearchParams();
    if (cid) params.append('campaign_id', cid);
    if (tid) params.append('task_id', tid);
    if (adj) params.append('adjustment_type', adj);
    if (auth) params.append('authorized_by', auth);
    const qs = params.toString();
    return qs ? '?' + qs : '';
}

function exportBudgetCsv() {
    window.location.href = '<?= base_url('task-budget-histories/export') ?>' + getFilterParams();
}

function exportBudgetPdf() {
    window.open('<?= base_url('task-budget-histories/export-pdf') ?>' + getFilterParams(), '_blank');
}

function deleteHistory(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus catatan riwayat penyesuaian anggaran #' + id + '?')) return;

    $.ajax({
        url: '<?= base_url('task-budget-histories/delete') ?>/' + id,
        type: 'POST',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            if (res.success) {
                alert(res.message);
                reloadBudgetTable();
            } else {
                alert(res.message || 'Gagal menghapus riwayat');
            }
        },
        error: function() {
            alert('Terjadi kesalahan sistem saat menghapus data.');
        }
    });
}

function applyBulkBudget() {
    const action = $('#bulkBudgetAction').val();
    if (!action) {
        alert('Pilih aksi masal terlebih dahulu.');
        return;
    }

    const selectedIds = [];
    $('.check-history-item:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('Pilih setidaknya satu baris data untuk diproses.');
        return;
    }

    if (action === 'delete') {
        if (!confirm('Apakah Anda yakin ingin menghapus ' + selectedIds.length + ' data riwayat yang dipilih?')) return;

        $.ajax({
            url: '<?= base_url('task-budget-histories/bulk-delete') ?>',
            type: 'POST',
            data: { ids: selectedIds },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    reloadBudgetTable();
                } else {
                    alert(res.message || 'Gagal menghapus data masal');
                }
            },
            error: function() {
                alert('Terjadi kesalahan sistem saat memproses tindakan masal.');
            }
        });
    }
}
</script>
<?= $this->endSection() ?>
