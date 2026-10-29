<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt text-warning me-2"></i>Finance &amp; Invoices</h3>
        <p class="text-muted small mb-0">Manajemen tagihan fee KOL, pemotongan pajak PPh 21 / PPh 23, verifikasi tim operasional, dan bukti pencairan dana transfer bank.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Invoices</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- KPI Finance Cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
              <i class="fas fa-file-invoice-dollar fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Liabilitas Fee</div>
              <h5 class="mb-0 fw-bold text-dark">Rp <?= number_format($metrics['total_liability'] ?? 0, 0, ',', '.') ?></h5>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-success-subtle text-success p-3 me-3">
              <i class="fas fa-check-double fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Total Sudah Dicairkan</div>
              <h5 class="mb-0 fw-bold text-success">Rp <?= number_format($metrics['total_paid'] ?? 0, 0, ',', '.') ?></h5>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
              <i class="far fa-clock fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Pending Verifikasi</div>
              <h5 class="mb-0 fw-bold text-warning"><?= $metrics['count_pending'] ?? 0 ?> Tagihan</h5>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3 d-flex align-items-center">
            <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
              <i class="fas fa-money-check-alt fa-lg"></i>
            </div>
            <div>
              <div class="text-muted small">Siap Dicairkan (Verified)</div>
              <h5 class="mb-0 fw-bold text-info"><?= $metrics['count_verified'] ?? 0 ?> Tagihan</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <div class="d-flex align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="filterCampaignInv" style="min-width: 220px;" onchange="reloadInvoiceTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterStatusInv" style="min-width: 170px;" onchange="reloadInvoiceTable()">
              <option value="">Semua Status Invoice</option>
              <option value="SUBMITTED">Submitted (Menunggu Verifikasi)</option>
              <option value="VERIFIED">Verified (Siap Cair)</option>
              <option value="PAID">Paid (Sudah Ditransfer)</option>
              <option value="REJECTED">Rejected</option>
            </select>
          </div>

          <div>
            <button type="button" class="btn btn-primary rounded-2 btn-sm px-3 shadow-sm" onclick="createInvoiceModal()">
              <i class="fas fa-plus me-1"></i> Terbitkan Invoice Baru
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableInvoices" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 180px;">No. Invoice</th>
                <th>Kampanye</th>
                <th>Talent KOL &amp; Rekening</th>
                <th style="width: 130px;">Gross Fee</th>
                <th style="width: 150px;">Potongan Pajak</th>
                <th style="width: 140px;">Net Dibayarkan</th>
                <th style="width: 130px;">Status</th>
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

<!-- Modal Terbitkan Invoice Baru dari Penugasan KOL -->
<div class="modal fade" id="modalCreateInvoice" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formCreateInvoice" onsubmit="submitCreateInvoice(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Terbitkan Invoice Fee KOL</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Penugasan KOL &amp; Kampanye <span class="text-danger">*</span></label>
            <select class="form-select" id="invCampaignKolId" name="campaign_kol_id" required>
              <option value="">-- Pilih Penugasan KOL --</option>
              <?php foreach ($assignedKols as $ak): ?>
                <option value="<?= $ak['id'] ?>">
                  <?= esc($ak['kol_name']) ?> — <?= esc($ak['campaign_title']) ?> (Deal: Rp <?= number_format((float)$ak['deal_amount'], 0, ',', '.') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Jenis Pemotongan Pajak</label>
            <select class="form-select" id="invTaxType" name="tax_type">
              <option value="PPh21">PPh 21 (2.5% ber-NPWP / 3% non-NPWP)</option>
              <option value="PPh23">PPh 23 (2.0% Jasa Agensi / Manajemen)</option>
              <option value="NONE">Tanpa Pajak (0%)</option>
            </select>
            <small class="text-muted">Pajak otomatis dipotong dari subtotal untuk menghitung nilai bersih pencairan.</small>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSubmitCreateInv">Terbitkan Invoice</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Detail Invoice -->
<div class="modal fade" id="modalViewInvoice" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="viewInvoiceTitle"><i class="fas fa-file-invoice-dollar text-warning me-2"></i>Detail Invoice Tagihan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewInvoiceBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Proses Pembayaran / Pencairan -->
<div class="modal fade" id="modalPayInvoice" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formPayInvoice" onsubmit="submitPayment(event)">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Proses Pembayaran Invoice</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="payInvoiceId">
          
          <div class="alert alert-light border p-3 mb-3">
            <div class="small text-muted">Nomor Tagihan:</div>
            <div class="fw-bold font-monospace text-primary fs-5" id="payInvNumber">-</div>
            <div class="small text-muted mt-2">Nominal Bersih yang Harus Ditransfer:</div>
            <div class="fw-bold text-success fs-4" id="payGrandTotal">-</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Metode Pembayaran</label>
            <select class="form-select" id="payMethod" name="payment_method">
              <option value="BANK_TRANSFER">Transfer Bank (BCA / Mandiri / BNI / BRI)</option>
              <option value="E_WALLET">E-Wallet (GoPay / OVO / Dana)</option>
              <option value="CASH">Tunai / Internal Cash</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nomor Referensi Bank / TRX ID</label>
            <input type="text" class="form-control font-monospace" id="payBankRef" name="bank_reference" placeholder="Contoh: TRX-98234123">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success rounded-2 px-4" id="btnSubmitPayment">Konfirmasi Bayar &amp; Lunasi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtInvoices = null;

$(document).ready(function() {
    dtInvoices = $('#tableInvoices').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('invoices/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignInv').val();
                d.status = $('#filterStatusInv').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'inv_number' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'subtotal' },
            { data: 'tax' },
            { data: 'grand_total' },
            { data: 'status' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari invoice, KOL, rekening...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ invoice",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadInvoiceTable() {
    dtInvoices.ajax.reload();
}

function createInvoiceModal() {
    $('#formCreateInvoice')[0].reset();
    $('#modalCreateInvoice').modal('show');
}

function submitCreateInvoice(e) {
    e.preventDefault();
    const ckId = $('#invCampaignKolId').val();
    const btn = $('#btnSubmitCreateInv');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    $.post('<?= base_url('invoices/create-from-deal/') ?>' + ckId, $('#formCreateInvoice').serialize(), function(res) {
        btn.prop('disabled', false).text('Terbitkan Invoice');
        if (res.success) {
            $('#modalCreateInvoice').modal('hide');
            dtInvoices.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Terbitkan Invoice');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewInvoice(id) {
    $('#modalViewInvoice').modal('show');
    $('#viewInvoiceBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.get('<?= base_url('invoices/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const inv = res.data;
            let paymentsHtml = '';
            if (inv.payments && inv.payments.length > 0) {
                paymentsHtml = inv.payments.map(p => `
                    <div class="border rounded-2 p-2 mb-2 bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-success">Rp ${Number(p.amount_paid).toLocaleString('id-ID')}</strong>
                            <div class="small text-muted font-monospace">${p.payment_method} &bull; Ref: ${p.bank_reference || '-'}</div>
                        </div>
                        <div class="text-end small text-muted">
                            <div>${p.paid_at}</div>
                            <small>Oleh: ${p.payer_name || 'Finance'}</small>
                        </div>
                    </div>
                `).join('');
            } else {
                paymentsHtml = '<div class="text-muted small fst-italic">Belum ada pembayaran dicatat</div>';
            }

            $('#viewInvoiceBody').html(`
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fs-5 fw-bold font-monospace text-primary">${inv.invoice_number}</span>
                    <span class="badge ${inv.status === 'PAID' ? 'bg-success' : (inv.status === 'VERIFIED' ? 'bg-info' : 'bg-warning')}">${inv.status}</span>
                </div>
                <div class="card bg-light border-0 p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="text-muted small">Penerima (Talent KOL)</label>
                            <div class="fw-bold">${inv.kol_name}</div>
                            <div class="small text-muted">NIK: ${inv.kol_nik || '-'} &bull; NPWP: ${inv.kol_npwp || '-'}</div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small">Tujuan Rekening Transfer</label>
                            <div class="fw-bold text-dark">${inv.bank_name || '-'} ${inv.bank_account || '-'}</div>
                            <div class="small text-muted">a/n ${inv.bank_holder || '-'}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td>Gross Fee Kesepakatan SOW (${inv.total_vt || 1} VT)</td>
                                <td class="text-end fw-bold">Rp ${Number(inv.subtotal).toLocaleString('id-ID')}</td>
                            </tr>
                            <tr>
                                <td>Potongan Pajak (${inv.tax_type} - ${(inv.tax_rate * 100)}%)</td>
                                <td class="text-end text-danger">- Rp ${Number(inv.tax_amount).toLocaleString('id-ID')}</td>
                            </tr>
                            <tr class="table-light">
                                <td class="fw-bold fs-6">Total Bersih yang Ditransfer (Net Payout)</td>
                                <td class="text-end fw-bold text-success fs-5">Rp ${Number(inv.grand_total).toLocaleString('id-ID')}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mb-2"><i class="fas fa-history text-secondary me-2"></i>Riwayat Pembayaran &amp; Pencairan</h6>
                ${paymentsHtml}
            `);
        }
    });
}

function verifyInvoice(id) {
    Swal.fire({
        title: 'Verifikasi Invoice?',
        text: 'Invoice akan diverifikasi dan diteruskan ke tim finance untuk pencairan dana.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('invoices/verify/') ?>' + id, function(res) {
                if (res.success) {
                    dtInvoices.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Terverifikasi', text: res.message, timer: 1500, showConfirmButton: false });
                }
            });
        }
    });
}

function payInvoiceModal(id) {
    $.get('<?= base_url('invoices/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const inv = res.data;
            $('#payInvoiceId').val(inv.id);
            $('#payInvNumber').text(inv.invoice_number);
            $('#payGrandTotal').text('Rp ' + Number(inv.grand_total).toLocaleString('id-ID'));
            $('#payBankRef').val('');
            $('#modalPayInvoice').modal('show');
        }
    });
}

function submitPayment(e) {
    e.preventDefault();
    const id = $('#payInvoiceId').val();
    const btn = $('#btnSubmitPayment');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    $.post('<?= base_url('invoices/save-payment/') ?>' + id, $('#formPayInvoice').serialize(), function(res) {
        btn.prop('disabled', false).text('Konfirmasi Bayar & Lunasi');
        if (res.success) {
            $('#modalPayInvoice').modal('hide');
            dtInvoices.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Lunas', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Konfirmasi Bayar & Lunasi');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
