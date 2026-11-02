<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-edit text-primary me-2"></i>Edit Template Kontrak #<?= esc($tpl['id']) ?></h4>
            <p class="text-muted small mb-0">Perbarui naskah perjanjian, klausul syarat dan ketentuan, serta placeholder.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('contract-templates/view/' . $tpl['id']) ?>" class="btn btn-outline-info btn-sm rounded-pill px-3">
                <i class="fas fa-eye me-1"></i> Lihat Detail &amp; Preview
            </a>
            <a href="<?= base_url('contract-templates') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Batal &amp; Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Form Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-contract me-2 text-primary"></i>Formulir Edit Template</h6>
                    <span class="badge bg-light text-muted border">ID: #<?= esc($tpl['id']) ?></span>
                </div>
                <div class="card-body p-4">
                    <div id="alertMessage" class="alert d-none mb-3"></div>

                    <form id="form_contract_templates" method="POST" action="<?= base_url('contract-templates/edit-save/' . $tpl['id']) ?>">
                        <input type="hidden" name="id" value="<?= esc($tpl['id']) ?>">

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Judul Template Kontrak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" placeholder="Judul template..." required value="<?= esc(old('title', $tpl['title'])) ?>">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="content" class="form-label fw-bold mb-0">Isi Naskah / Klausul Perjanjian (HTML/Text) <span class="text-danger">*</span></label>
                                <small class="text-muted">Klik tag di samping untuk menyisipkan variabel</small>
                            </div>
                            <textarea class="form-control font-monospace" name="content" id="content" rows="16" placeholder="Naskah template..." required style="line-height: 1.6;"><?= esc(old('content', $tpl['body_template'] ?: $tpl['content'])) ?></textarea>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($tpl['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="is_active">Aktifkan template ini agar dapat langsung digunakan dalam modul kontrak</label>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn_save rounded-pill px-4" data-stype="stay" id="btn_save">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                            <button type="button" class="btn btn-info text-white btn_save rounded-pill px-4" data-stype="back" id="btn_save_back">
                                <i class="fas fa-check-circle me-1"></i> Simpan &amp; Ke Daftar
                            </button>
                            <a href="<?= base_url('contract-templates') ?>" class="btn btn-outline-secondary rounded-pill px-4" id="btn_cancel">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cheatsheet Helper Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4 sticky-top" style="top: 85px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-magic me-2 text-warning"></i>Kamus Tag Placeholder</h6>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-3">Klik tombol tag di bawah untuk menyisipkan placeholder langsung ke posisi kursor naskah:</p>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Nomor Kontrak:</span>
                                <div class="text-muted text-xs">Nomor urut resmi dokumen</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{nomor_kontrak}')">+{nomor_kontrak}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Tanggal Kontrak:</span>
                                <div class="text-muted text-xs">Tanggal efektif perjanjian</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{tanggal}')">+{tanggal}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Nama Brand:</span>
                                <div class="text-muted text-xs">Pihak Pertama / Brand</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{brand_name}')">+{brand_name}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Judul Kampanye:</span>
                                <div class="text-muted text-xs">Nama project promosi</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{campaign_title}')">+{campaign_title}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Nama Talent KOL:</span>
                                <div class="text-muted text-xs">Pihak Kedua (Influencer)</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{kol_name}')">+{kol_name}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">NIK KTP Talent:</span>
                                <div class="text-muted text-xs">Nomor KTP untuk pajak</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{kol_nik}')">+{kol_nik}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Alamat Domisili:</span>
                                <div class="text-muted text-xs">Alamat resmi KOL</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{kol_address}')">+{kol_address}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">WhatsApp KOL:</span>
                                <div class="text-muted text-xs">Nomor kontak resmi</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{kol_whatsapp}')">+{kol_whatsapp}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Nilai Kompensasi (Fee):</span>
                                <div class="text-muted text-xs">Nominal pembayaran</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{deal_amount}')">+{deal_amount}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Total Deliverable / VT:</span>
                                <div class="text-muted text-xs">Jumlah video / post</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{total_vt}')">+{total_vt}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">Nama Bank KOL:</span>
                                <div class="text-muted text-xs">Bank penerima transfer</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{kol_bank_name}')">+{kol_bank_name}</button>
                        </div>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">No. Rekening KOL:</span>
                                <div class="text-muted text-xs">Nomor rekening transfer</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace" onclick="insertTag('{kol_bank_account}')">+{kol_bank_account}</button>
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
function insertTag(tag) {
    const textarea = document.getElementById('content');
    const startPos = textarea.selectionStart;
    const endPos = textarea.selectionEnd;
    const currentVal = textarea.value;

    textarea.value = currentVal.substring(0, startPos) + tag + currentVal.substring(endPos, currentVal.length);
    textarea.focus();
    textarea.selectionStart = startPos + tag.length;
    textarea.selectionEnd = startPos + tag.length;
}

$(document).ready(function() {
    $('.btn_save').click(function(e) {
        e.preventDefault();
        const saveType = $(this).attr('data-stype');
        const form = $('#form_contract_templates');
        const alertBox = $('#alertMessage');

        const postData = form.serializeArray();
        postData.push({ name: 'save_type', value: saveType });

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: postData,
        })
        .done(function(res) {
            if (res.success) {
                if (saveType === 'back') {
                    window.location.href = res.redirect || '<?= base_url('contract-templates') ?>';
                    return;
                }
                alertBox.removeClass('d-none alert-danger alert-warning').addClass('alert-success').html(res.message).fadeIn();
            } else {
                alertBox.removeClass('d-none alert-success').addClass('alert-danger').html(res.message || 'Gagal menyimpan perubahan').fadeIn();
            }
        })
        .fail(function() {
            alertBox.removeClass('d-none alert-success').addClass('alert-danger').html('Terjadi kesalahan jaringan atau server.').fadeIn();
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
            $('html, body').animate({ scrollTop: 0 }, 500);
        });
    });
});
</script>
<?= $this->endSection() ?>
