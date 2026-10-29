<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.finance-stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #edf2f7;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    height: 100%;
}
.finance-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.stat-value {
    font-size: 22px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.2;
}
</style>

<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-coins text-warning me-2"></i>Finance Hub &mdash; Manajemen Keuangan Terpadu</h4>
            <p class="text-muted small mb-0">Workspace terpadu mengelola seluruh siklus finansial KOL: Alokasi Deal, Surat Kontrak, Invoice Resmi, Potongan Pajak PPh 21/23, hingga Realisasi Pencairan Dana.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-receipt me-1"></i> Data Invoice Mandiri
            </a>
            <a href="<?= base_url('contracts') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-file-signature me-1"></i> Data Kontrak
            </a>
        </div>
    </div>

    <!-- 5 KPI Stat Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Deal Fee -->
        <div class="col-sm-6 col-xl">
            <div class="finance-stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-xs fw-bold text-uppercase text-muted">Komitmen Deal</span>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <div class="stat-value">Rp <?= number_format((float)$metrics['total_deal_amount'], 0, ',', '.') ?></div>
                <small class="text-muted"><?= number_format($metrics['total_deals']) ?> penugasan KOL</small>
            </div>
        </div>

        <!-- 2. Total Invoiced -->
        <div class="col-sm-6 col-xl">
            <div class="finance-stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-xs fw-bold text-uppercase text-muted">Total Invoice</span>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="stat-value text-info">Rp <?= number_format((float)$metrics['total_invoiced'], 0, ',', '.') ?></div>
                <small class="text-muted"><?= number_format($metrics['total_invoices']) ?> invoice diterbitkan</small>
            </div>
        </div>

        <!-- 3. Total PPh Tax -->
        <div class="col-sm-6 col-xl">
            <div class="finance-stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-xs fw-bold text-uppercase text-muted">Potongan PPh (21/23)</span>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-percent"></i>
                    </div>
                </div>
                <div class="stat-value text-secondary">Rp <?= number_format((float)$metrics['total_tax'], 0, ',', '.') ?></div>
                <small class="text-muted">PPh 21 (2.5%/3%) & PPh 23 (2%)</small>
            </div>
        </div>

        <!-- 4. Realisasi Pembayaran -->
        <div class="col-sm-6 col-xl">
            <div class="finance-stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-xs fw-bold text-uppercase text-muted">Realisasi Cair</span>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value text-success">Rp <?= number_format((float)$metrics['total_paid'], 0, ',', '.') ?></div>
                <small class="text-muted"><?= number_format($metrics['total_transactions']) ?> transaksi transfer</small>
            </div>
        </div>

        <!-- 5. Sisa Outstanding -->
        <div class="col-sm-6 col-xl">
            <div class="finance-stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-xs fw-bold text-uppercase text-muted">Sisa Outstanding</span>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-value text-warning">Rp <?= number_format((float)$metrics['outstanding'], 0, ',', '.') ?></div>
                <small class="text-muted">Belum ditransfer / proses</small>
            </div>
        </div>
    </div>

    <!-- Filter & Workspace Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Filter Kampanye</label>
                    <select id="filterCampaign" class="form-select form-select-sm">
                        <option value="">-- Semua Kampanye --</option>
                        <?php foreach ($allCampaigns as $ac): ?>
                            <option value="<?= $ac['id'] ?>" <?= ($campaignId && $campaignId === (int)$ac['id']) ? 'selected' : '' ?>>
                                <?= esc($ac['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Status Pembayaran</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="NO_INVOICE">Belum Ada Invoice</option>
                        <option value="SUBMITTED">Submitted (Menunggu Verifikasi)</option>
                        <option value="VERIFIED">Siap Cair (Verified)</option>
                        <option value="PAID">Terbayar Lunas (Paid)</option>
                        <option value="REJECTED">Ditolak (Rejected)</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-end align-self-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" onclick="reloadTable()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh Data
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="financeTable" class="table table-hover align-middle mb-0" style="width: 100%;">
                    <thead class="table-light text-uppercase text-xs fw-bold text-muted">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th style="min-width: 180px;">Kampanye & SOW</th>
                            <th style="min-width: 220px;">Talent KOL & Rekening</th>
                            <th style="min-width: 150px;">Kontrak Kerjasama</th>
                            <th style="min-width: 180px;">Invoice & Pajak PPh</th>
                            <th style="min-width: 140px;">Nominal Deal</th>
                            <th style="min-width: 150px;">Realisasi Pembayaran</th>
                            <th style="min-width: 120px;" class="text-center">Status</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject Invoice -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Tolak / Minta Revisi Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="rejectInvoiceId" name="invoice_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Penolakan / Catatan Revisi <span class="text-danger">*</span></label>
                        <textarea id="rejectReason" name="reason" class="form-control" rows="3" placeholder="Contoh: Nomor rekening tidak cocok, lampiran invoice kurang lengkap, tarif pajak salah." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Tolak Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload Bupot -->
<div class="modal fade" id="bupotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Upload Bukti Potong Pajak (Bupot)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bupotForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" id="bupotInvoiceId" name="invoice_id">
                    <div id="currentBupotContainer" class="mb-3 d-none">
                        <label class="form-label fw-semibold">File Bupot Saat Ini:</label>
                        <div id="currentBupotLink"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File Bukti Potong (PDF / Foto) <span class="text-danger">*</span></label>
                        <input type="file" name="bupot_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg" required>
                        <small class="text-muted">Format: PDF, PNG, JPG (Maks. 5 MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold">Unggah Bupot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bayar Invoice -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Catat Pencairan Dana / Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="payForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="payInvoiceId" name="invoice_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nominal Ditransfer (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="any" id="payAmount" name="amount_paid" class="form-control fw-bold fs-5 text-success" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select">
                                <option value="BANK_TRANSFER">Bank Transfer</option>
                                <option value="BCA_VIRTUAL_ACCOUNT">BCA VA</option>
                                <option value="MANDIRI_VA">Mandiri VA</option>
                                <option value="MANUAL_CASH">Tunai / Kas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Referensi Bank / Trx</label>
                            <input type="text" name="bank_reference" class="form-control" placeholder="Contoh: TRX-98234823">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link / Catatan Bukti Transfer</label>
                        <input type="text" name="transfer_receipt_file" class="form-control" placeholder="URL bukti transfer atau catatan transaksi...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Konfirmasi Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let financeTable;

$(document).ready(function() {
    financeTable = $('#financeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('finance/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaign').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center fw-bold text-muted' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'contract' },
            { data: 'invoice' },
            { data: 'deal_amount' },
            { data: 'payment' },
            { data: 'status', className: 'text-center' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm me-2"></div> Memuat data keuangan...',
            search: "_INPUT_",
            searchPlaceholder: "Cari KOL / Invoice / Rekening...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            zeroRecords: "Data keuangan tidak ditemukan",
            emptyTable: "Belum ada transaksi keuangan tercatat"
        }
    });

    $('#filterCampaign, #filterStatus').on('change', function() {
        financeTable.ajax.reload();
    });
});

function reloadTable() {
    financeTable.ajax.reload(null, false);
}

// Create Invoice
function createInvoice(ckId) {
    if (!confirm('Terbitkan invoice resmi untuk penugasan KOL ini?')) return;
    
    $.post('<?= base_url('invoices/create-from-deal') ?>/' + ckId, function(res) {
        if (res.success) {
            alert(res.message);
            reloadTable();
        } else {
            alert(res.message || 'Gagal menerbitkan invoice');
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON?.message || 'Terjadi kesalahan sistem');
    });
}

// Verify Invoice
function verifyInvoice(invId) {
    if (!confirm('Verifikasi dan setujui invoice ini untuk siap dicairkan?')) return;
    
    $.post('<?= base_url('invoices/verify') ?>/' + invId, function(res) {
        if (res.success) {
            alert(res.message);
            reloadTable();
        } else {
            alert(res.message || 'Gagal memverifikasi invoice');
        }
    });
}

// Reject Invoice Modal
function rejectInvoiceModal(invId) {
    $('#rejectInvoiceId').val(invId);
    $('#rejectReason').val('');
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

$('#rejectForm').on('submit', function(e) {
    e.preventDefault();
    const invId = $('#rejectInvoiceId').val();
    const reason = $('#rejectReason').val();

    $.post('<?= base_url('finance/reject-invoice') ?>/' + invId, { reason: reason }, function(res) {
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        if (res.success) {
            alert(res.message);
            reloadTable();
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON?.message || 'Gagal menolak invoice');
    });
});

// Upload Bupot Modal
function uploadBupotModal(invId, currentFile) {
    $('#bupotInvoiceId').val(invId);
    if (currentFile) {
        $('#currentBupotContainer').removeClass('d-none');
        $('#currentBupotLink').html(`<a href="<?= base_url() ?>/${currentFile}" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-file-pdf me-1"></i> Lihat Bupot Tersimpan</a>`);
    } else {
        $('#currentBupotContainer').addClass('d-none');
    }
    new bootstrap.Modal(document.getElementById('bupotModal')).show();
}

$('#bupotForm').on('submit', function(e) {
    e.preventDefault();
    const invId = $('#bupotInvoiceId').val();
    const fd = new FormData(this);

    $.ajax({
        url: '<?= base_url('finance/upload-bupot') ?>/' + invId,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            bootstrap.Modal.getInstance(document.getElementById('bupotModal')).hide();
            if (res.success) {
                alert(res.message);
                reloadTable();
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Gagal mengunggah bupot');
        }
    });
});

// Pay Modal
function payInvoiceModal(invId, amount) {
    $('#payInvoiceId').val(invId);
    $('#payAmount').val(amount);
    new bootstrap.Modal(document.getElementById('payModal')).show();
}

$('#payForm').on('submit', function(e) {
    e.preventDefault();
    const invId = $('#payInvoiceId').val();
    const fd = new FormData(this);

    $.ajax({
        url: '<?= base_url('invoices/save-payment') ?>/' + invId,
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            bootstrap.Modal.getInstance(document.getElementById('payModal')).hide();
            if (res.success) {
                alert(res.message);
                reloadTable();
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Gagal memproses pembayaran');
        }
    });
});
</script>
<?= $this->endSection() ?>
