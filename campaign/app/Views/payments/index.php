<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-money-bill-wave text-success me-2"></i>Payments &amp; Disbursements</h3>
        <p class="text-muted small mb-0">Manajemen pencairan dana honorarium talent KOL, pencatatan bukti transfer bank, dan rekonsiliasi tagihan invoice.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>" class="text-decoration-none">Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">Payments</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-hand-holding-usd fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Realisasi Pencairan</div>
              <h5 class="mb-0 fw-bold text-success">Rp <?= number_format($metrics['total_disbursed'] ?? 0, 0, ',', '.') ?></h5>
              <small class="text-muted"><?= $metrics['total_count'] ?? 0 ?> Transaksi</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-calendar-day fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Pencairan Hari Ini</div>
              <h5 class="mb-0 fw-bold text-primary">Rp <?= number_format($metrics['today_disbursed'] ?? 0, 0, ',', '.') ?></h5>
              <small class="text-muted"><?= $metrics['today_count'] ?? 0 ?> Transaksi</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-university fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Via Transfer Bank</div>
              <h5 class="mb-0 fw-bold text-info">Rp <?= number_format($metrics['bank_transfer_total'] ?? 0, 0, ',', '.') ?></h5>
              <small class="text-muted">BCA, Mandiri, BRI, BNI</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="fas fa-file-invoice-dollar fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Manajemen Invoice</div>
              <h5 class="mb-0 fw-bold text-warning">Billing Hub</h5>
              <a href="<?= base_url('invoices') ?>" class="small text-decoration-none fw-semibold">Lihat Tagihan &rarr;</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <select class="form-select form-select-sm rounded-2" id="filterCampaignPay" style="min-width: 200px;" onchange="reloadPaymentTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach (($allCampaigns ?? []) as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title'] ?: ($c['name'] ?? 'Kampanye')) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterMethodPay" style="min-width: 170px;" onchange="reloadPaymentTable()">
              <option value="">Semua Metode</option>
              <option value="BANK_TRANSFER">Bank Transfer</option>
              <option value="Bank Transfer (BCA)">Bank Transfer (BCA)</option>
              <option value="Bank Transfer (Mandiri)">Bank Transfer (Mandiri)</option>
              <option value="E_WALLET">E-Wallet</option>
              <option value="CASH">Tunai</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('payments/export') ?>" class="btn btn-outline-success btn-sm rounded-2" title="Export Excel / CSV">
              <i class="fas fa-file-excel me-1"></i> Export XLS
            </a>
            <a href="<?= base_url('payments/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-2" title="Cetak Rekap PDF">
              <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
            <a href="<?= base_url('payments/add') ?>" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm">
              <i class="fas fa-plus me-1"></i> Catat Pembayaran
            </a>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tablePayments" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 45px;" class="text-center">#</th>
                <th style="width: 140px;">Tanggal Transfer</th>
                <th>No. Invoice &amp; Kampanye</th>
                <th>Penerima (Talent KOL)</th>
                <th style="width: 140px;">Nominal Dicairkan</th>
                <th style="width: 130px;">Metode</th>
                <th style="width: 150px;">Referensi Bank</th>
                <th style="width: 80px;" class="text-center">Bukti</th>
                <th style="width: 130px;" class="text-center">Aksi</th>
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
let dtPayments;

$(document).ready(function() {
    dtPayments = $('#tablePayments').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?= base_url('payments/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id    = $('#filterCampaignPay').val();
                d.payment_method = $('#filterMethodPay').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id', className: 'text-center' },
            { data: 'date' },
            { data: 'invoice' },
            { data: 'kol' },
            { data: 'amount', className: 'text-end' },
            { data: 'method' },
            { data: 'reference' },
            { data: 'receipt', className: 'text-center' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari ref, KOL, invoice...",
            lengthMenu: "Tampil _MENU_ data",
            zeroRecords: "Tidak ada data transaksi pembayaran",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ transaksi",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ data)",
            paginate: { first: "&laquo;", last: "&raquo;", next: "&rsaquo;", previous: "&lsaquo;" }
        }
    });
});

function reloadPaymentTable() {
    dtPayments.ajax.reload();
}

function deletePayment(id) {
    Swal.fire({
        title: 'Hapus Catatan Pembayaran?',
        text: 'Data transaksi dan bukti transfer akan dihapus. Status tagihan invoice akan otomatis dikalkulasi ulang.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('payments/delete/') ?>' + id, function(res) {
                if (res.success) {
                    dtPayments.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            }).fail(function() {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan sistem' });
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
