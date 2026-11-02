<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
.finance-stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 18px 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    height: 100%;
}
.finance-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}
.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.25;
}
.timeline-item {
    position: relative;
    padding-left: 28px;
    margin-bottom: 18px;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 4px;
    bottom: -18px;
    width: 2px;
    background: #e2e8f0;
}
.timeline-item:last-child::before {
    display: none;
}
.timeline-dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #3b82f6;
    border: 3px solid #fff;
    box-shadow: 0 0 0 1px #cbd5e1;
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
            <a href="<?= base_url('finance/export') ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-file-excel me-1"></i> Ekspor CSV
            </a>
            <a href="<?= base_url('finance/export-pdf') ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-file-pdf me-1"></i> Cetak Rekap
            </a>
            <a href="<?= base_url('invoices') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-receipt me-1"></i> Data Invoice
            </a>
            <a href="<?= base_url('contracts') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
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
                    <span class="text-xs fw-bold text-uppercase text-muted">Potongan Pajak</span>
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
                <small class="text-muted">Belum ditransfer / lunas</small>
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
                                <?= esc($ac['title'] ?: ($ac['name'] ?? 'Kampanye #' . $ac['id'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Status Pembayaran</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="NO_INVOICE">Belum Ada Invoice</option>
                        <option value="SUBMITTED">Submitted (Menunggu Persetujuan)</option>
                        <option value="APPROVED">Approved / Siap Cair</option>
                        <option value="PARTIAL">Dibayar Sebagian (Partial)</option>
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
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="min-width: 180px;">Kampanye & SOW</th>
                            <th style="min-width: 230px;">Talent KOL & Rekening</th>
                            <th style="min-width: 150px;">Kontrak Kerjasama</th>
                            <th style="min-width: 190px;">Invoice & Pajak PPh</th>
                            <th style="min-width: 130px;">Nominal Deal</th>
                            <th style="min-width: 160px;">Realisasi Pembayaran</th>
                            <th style="min-width: 120px;" class="text-center">Status</th>
                            <th style="width: 140px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REVIEW & PERSETUJUAN TAGIHAN (WITHHOLDING TAX VERIFICATION) -->
<div class="modal fade" id="modalReviewInvoice" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check text-info me-2"></i>Verifikasi &amp; Setujui Tagihan Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formReviewInvoice">
                <div class="modal-body p-4">
                    <input type="hidden" name="invoice_id" id="review_invoice_id">
                    
                    <div class="alert alert-info border-0 shadow-sm rounded-3 p-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3 text-info"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Verifikasi Pajak Penghasilan (PPh 21 / PPh 23)</h6>
                                <p class="mb-0 small">Pastikan pemotongan pajak sesuai kepemilikan NPWP talent: PPh 21 tarif 2.5% (Ber-NPWP) atau 3% (Non-NPWP). Nominal Net Transfer akan otomatis dihitung.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nilai Dasar Pengenaan Pajak (DPP / Subtotal)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="text" class="form-control fw-bold bg-white" id="review_subtotal_display" readonly>
                            </div>
                            <input type="hidden" id="review_subtotal_raw">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Pemotongan Pajak</label>
                            <select class="form-select" name="tax_type" id="review_tax_type">
                                <option value="PPh21">PPh 21 (KOL / Perorangan)</option>
                                <option value="PPh23">PPh 23 (Badan Usaha / Agency)</option>
                                <option value="NONE">Tanpa Potongan Pajak (Net 100%)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tarif Pajak Efektif (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="tax_rate_percent" id="review_tax_rate_percent" value="2.50" required>
                                <span class="input-group-text bg-light">%</span>
                            </div>
                            <div class="form-text" id="review_tax_helper">Tarif standar PPh 21 ber-NPWP: 2.5%, non-NPWP: 3.0%.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nominal Potongan Pajak</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-danger">Rp</span>
                                <input type="text" class="form-control fw-bold text-danger bg-white" id="review_tax_amount_display" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-success text-uppercase">Nominal Bersih yang Ditransfer (Net):</span>
                                    <h4 class="mb-0 fw-bold text-success" id="review_grand_total_display">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-check-circle me-1"></i> Setujui Invoice (APPROVED)</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TOLAK INVOICE -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Tolak / Minta Revisi Tagihan Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="rejectInvoiceId" name="invoice_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Penolakan / Catatan Revisi <span class="text-danger">*</span></label>
                        <textarea id="rejectReason" name="rejection_reason" class="form-control" rows="4" placeholder="Contoh: Nomor rekening bank tidak cocok dengan profil, lampiran file invoice tidak terbaca, atau tarif pajak salah." required></textarea>
                    </div>
                    <div class="form-text">Status invoice akan menjadi REJECTED dan alasan ini akan tampil di portal talent KOL.</div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Konfirmasi Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD BUPOT -->
<div class="modal fade" id="bupotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Upload Bukti Potong Pajak (Bupot Resmi)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bupotForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" id="bupotInvoiceId" name="invoice_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Bukti Potong (No. Bupot) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bupot_no" id="bupotNoInput" placeholder="Contoh: BP-2026-PPh21-0091" required>
                    </div>

                    <div id="currentBupotContainer" class="mb-3 d-none">
                        <label class="form-label fw-semibold d-block">Berkas Bukti Potong Tersimpan:</label>
                        <div id="currentBupotLink"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Berkas Bukti Potong (PDF / Foto) <span class="text-danger">*</span></label>
                        <input type="file" name="bupot_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg" required>
                        <small class="text-muted">Maksimal 5 MB. Format: PDF, PNG, JPG.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold"><i class="fas fa-upload me-1"></i> Unggah Bukti Potong</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CATAT PEMBAYARAN -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Catat Pencairan Dana / Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="payForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" id="payInvoiceId" name="invoice_id">
                    
                    <!-- Bank Target Info Box -->
                    <div class="p-3 bg-light border rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-xs fw-bold text-uppercase text-muted">Rekening Tujuan Transfer:</span>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" onclick="copyBankAccount()"><i class="far fa-copy me-1"></i>Salin No. Rek</button>
                        </div>
                        <div class="fw-bold text-dark fs-6" id="payTargetBankName">-</div>
                        <div class="font-monospace fw-bold text-primary fs-5" id="payTargetBankAccount">-</div>
                        <div class="small text-muted" id="payTargetBankHolder">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nominal Ditransfer (Rp) <span class="text-danger">*</span></label>
                        <input type="text" id="payAmount" name="amount" class="form-control fw-bold fs-5 text-success" required>
                        <small class="text-muted" id="payBalanceHelper">Sisa tagihan yang harus dibayar: Rp 0</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Transfer <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="payment_date" id="payPaymentDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select">
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="BCA VA">BCA Virtual Account</option>
                                <option value="Mandiri VA">Mandiri Virtual Account</option>
                                <option value="Kas / Tunai">Kas / Tunai</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. Referensi Bank / Trx</label>
                        <input type="text" name="bank_reference" class="form-control" placeholder="Contoh: TRX-20261102-9842">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unggah Bukti Transfer (PDF / Gambar)</label>
                        <input type="file" name="payment_proof_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Pembayaran</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pelunasan 100% biaya endorsement video."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fas fa-check me-1"></i> Konfirmasi Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL AUDIT TRAIL / RIWAYAT INVOICE -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-history text-info me-2"></i>Riwayat Audit &amp; Perubahan Status Tagihan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 500px; overflow-y: auto;">
                <div id="historyLoading" class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat riwayat audit...
                </div>
                <div id="historyList" class="d-none"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
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
            { data: 'no', className: 'text-center fw-bold text-muted' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'contract' },
            { data: 'invoice' },
            { data: 'deal_amount' },
            { data: 'payment' },
            { data: 'status', className: 'text-center' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm me-2"></div> Memuat data keuangan...',
            search: "_INPUT_",
            searchPlaceholder: "Cari KOL / Invoice / Rekening...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            zeroRecords: "Data transaksi keuangan tidak ditemukan",
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

// Create Invoice Initial
function createInvoice(ckId) {
    if (!confirm('Terbitkan invoice resmi untuk penugasan KOL ini?')) return;
    
    $.post('<?= base_url('contracts/generate_initial_invoice_ajax') ?>', { campaign_kol_id: ckId }, function(res) {
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

// Open Review & Approve Invoice Modal
function openReviewInvoiceModal(invId, subtotal, npwp) {
    $('#review_invoice_id').val(invId);
    $('#review_subtotal_raw').val(subtotal);
    $('#review_subtotal_display').val(Number(subtotal).toLocaleString('id-ID'));
    
    // Auto-select tax rate based on NPWP presence
    if (npwp && npwp.trim() !== '') {
        $('#review_tax_type').val('PPh21');
        $('#review_tax_rate_percent').val('2.50');
        $('#review_tax_helper').text('NPWP ditemukan (' + npwp + '). Tarif standar PPh 21: 2.5%.');
    } else {
        $('#review_tax_type').val('PPh21');
        $('#review_tax_rate_percent').val('3.00');
        $('#review_tax_helper').text('Tanpa NPWP. Tarif PPh 21 non-NPWP: 3.0%.');
    }

    calculateReviewNet();
    new bootstrap.Modal(document.getElementById('modalReviewInvoice')).show();
}

function calculateReviewNet() {
    const subtotal = parseFloat($('#review_subtotal_raw').val()) || 0;
    const rate     = parseFloat($('#review_tax_rate_percent').val()) || 0;
    const taxAmt   = Math.round(subtotal * (rate / 100));
    const grandNet = Math.max(0, subtotal - taxAmt);

    $('#review_tax_amount_display').val(Number(taxAmt).toLocaleString('id-ID'));
    $('#review_grand_total_display').text('Rp ' + Number(grandNet).toLocaleString('id-ID'));
}

$('#review_tax_type').on('change', function() {
    const val = $(this).val();
    if (val === 'PPh21') {
        $('#review_tax_rate_percent').val('2.50');
    } else if (val === 'PPh23') {
        $('#review_tax_rate_percent').val('2.00');
    } else {
        $('#review_tax_rate_percent').val('0.00');
    }
    calculateReviewNet();
});

$('#review_tax_rate_percent').on('input', calculateReviewNet);

$('#formReviewInvoice').on('submit', function(e) {
    e.preventDefault();
    $.post('<?= base_url('finance/approve-invoice') ?>', $(this).serialize(), function(res) {
        bootstrap.Modal.getInstance(document.getElementById('modalReviewInvoice')).hide();
        if (res.success) {
            alert(res.message);
            reloadTable();
        } else {
            alert(res.message || 'Gagal memverifikasi tagihan.');
        }
    }).fail(function(xhr) {
        alert(xhr.responseJSON?.message || 'Terjadi kesalahan sistem');
    });
});

// Reject Invoice Modal
function rejectInvoiceModal(invId) {
    $('#rejectInvoiceId').val(invId);
    $('#rejectReason').val('');
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

$('#rejectForm').on('submit', function(e) {
    e.preventDefault();
    $.post('<?= base_url('finance/reject-invoice') ?>', $(this).serialize(), function(res) {
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
function uploadBupotModal(invId, bupotNo, currentFile) {
    $('#bupotInvoiceId').val(invId);
    $('#bupotNoInput').val(bupotNo || '');
    if (currentFile && currentFile.trim() !== '') {
        $('#currentBupotContainer').removeClass('d-none');
        $('#currentBupotLink').html(`<a href="<?= base_url() ?>/${currentFile}" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-file-pdf me-1"></i> Buka Bukti Potong (${bupotNo || 'Bupot'})</a>`);
    } else {
        $('#currentBupotContainer').addClass('d-none');
    }
    new bootstrap.Modal(document.getElementById('bupotModal')).show();
}

$('#bupotForm').on('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);

    $.ajax({
        url: '<?= base_url('finance/upload-bupot') ?>',
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
function payInvoiceModal(invId, amount, kolName, bankName, bankAccount, bankHolder, invoiceNo) {
    $('#payInvoiceId').val(invId);
    $('#payTargetBankName').text(bankName || '-');
    $('#payTargetBankAccount').text(bankAccount || '-');
    $('#payTargetBankHolder').text((bankHolder || kolName) + ' &bull; Invoice: ' + invoiceNo);
    
    $('#payAmount').val(amount > 0 ? Number(amount).toLocaleString('id-ID') : '');
    $('#payBalanceHelper').text('Sisa tagihan yang harus dibayar: Rp ' + Number(amount).toLocaleString('id-ID'));
    
    // Set current datetime
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('#payPaymentDate').val(now.toISOString().slice(0, 16));

    new bootstrap.Modal(document.getElementById('payModal')).show();
}

function copyBankAccount() {
    const acc = $('#payTargetBankAccount').text();
    if (acc && acc !== '-') {
        navigator.clipboard.writeText(acc).then(function() {
            alert('Nomor rekening disalin: ' + acc);
        });
    }
}

$('#payForm').on('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);

    $.ajax({
        url: '<?= base_url('finance/save-payment') ?>',
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

// View Audit History
function viewInvoiceHistory(invId) {
    $('#historyLoading').removeClass('d-none');
    $('#historyList').addClass('d-none').html('');
    new bootstrap.Modal(document.getElementById('historyModal')).show();

    $.get('<?= base_url('finance/get_invoice_history_ajax') ?>/' + invId, function(res) {
        $('#historyLoading').addClass('d-none');
        if (res.success && res.histories && res.histories.length > 0) {
            let html = '';
            res.histories.forEach(function(item) {
                html += `
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="fw-bold text-dark fs-6">${item.action || item.to_status}</div>
                        <div class="small text-muted mb-1"><i class="far fa-user me-1"></i>Oleh: <b>${item.actor_name || 'System / Admin'}</b> &bull; <i class="far fa-clock me-1"></i>${item.created_at}</div>
                        <div class="p-2 bg-light rounded text-secondary small">${item.notes || '-'}</div>
                    </div>
                `;
            });
            $('#historyList').html(html).removeClass('d-none');
        } else {
            $('#historyList').html('<div class="text-center py-4 text-muted">Belum ada catatan riwayat audit untuk invoice ini.</div>').removeClass('d-none');
        }
    }).fail(function() {
        $('#historyLoading').addClass('d-none');
        $('#historyList').html('<div class="text-danger text-center py-4">Gagal memuat riwayat audit.</div>').removeClass('d-none');
    });
}
</script>
<?= $this->endSection() ?>
