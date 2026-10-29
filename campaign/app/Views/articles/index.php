<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-newspaper text-info me-2"></i>Artikel Landing CMS</h3>
        <p class="text-muted small mb-0">Manajemen konten artikel publik dan blog yang disalurkan otomatis via REST API ke landing page KOLAB.ID.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Artikel CMS</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
              <i class="fas fa-rss me-1"></i> Public Feed Endpoint: <code>/campaign/articles/feed</code>
            </span>
          </div>
          <div>
            <button type="button" class="btn btn-primary rounded-2 btn-sm px-3 shadow-sm" onclick="addArticleModal()">
              <i class="fas fa-plus me-1"></i> Tulis Artikel Baru
            </button>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableArticles" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Judul &amp; URL Slug</th>
                <th style="width: 170px;">Kategori</th>
                <th style="width: 130px;">Status</th>
                <th style="width: 160px;">Tanggal Terbit</th>
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

<!-- Modal Form Tulis / Edit Artikel -->
<div class="modal fade" id="modalArticleForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formArticle" onsubmit="saveArticle(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="articleModalTitle"><i class="fas fa-edit me-2"></i>Tulis Artikel CMS</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="articleId" name="id">

          <div class="mb-3">
            <label class="form-label fw-semibold">Judul Artikel <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="artTitle" name="title" required placeholder="Judul artikel yang menarik...">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Kategori</label>
              <input type="text" class="form-control" id="artCategory" name="category" placeholder="Contoh: Insight, Strategi, Riset">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status Publikasi</label>
              <select class="form-select" id="artStatus" name="status">
                <option value="PUBLISHED">PUBLISHED (Tayang di Landing Page)</option>
                <option value="DRAFT">DRAFT (Simpan Sementara)</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Ringkasan / Excerpt Singkat</label>
            <textarea class="form-control" id="artExcerpt" name="excerpt" rows="2" placeholder="Ringkasan 1-2 kalimat untuk meta description..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Isi Lengkap Konten Artikel <span class="text-danger">*</span></label>
            <textarea class="form-control font-monospace" id="artContent" name="content" rows="8" required placeholder="Tuliskan naskah artikel lengkap (HTML atau Markdown)..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveArticle">Simpan &amp; Publikasikan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtArticles = null;

$(document).ready(function() {
    dtArticles = $('#tableArticles').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('articles/get-datatable') ?>',
            type: 'POST'
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'title' },
            { data: 'category' },
            { data: 'status' },
            { data: 'published' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari artikel...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ artikel",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function addArticleModal() {
    $('#formArticle')[0].reset();
    $('#articleId').val('');
    $('#articleModalTitle').html('<i class="fas fa-plus me-2"></i>Tulis Artikel Baru');
    $('#modalArticleForm').modal('show');
}

function editArticle(id) {
    $.get('<?= base_url('articles/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const a = res.data;
            $('#articleId').val(a.id);
            $('#artTitle').val(a.title);
            $('#artCategory').val(a.category);
            $('#artStatus').val(a.status);
            $('#artExcerpt').val(a.excerpt || '');
            $('#artContent').val(a.content);
            $('#articleModalTitle').html('<i class="fas fa-edit me-2"></i>Edit Artikel');
            $('#modalArticleForm').modal('show');
        }
    });
}

function saveArticle(e) {
    e.preventDefault();
    const btn = $('#btnSaveArticle');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('articles/save') ?>', $('#formArticle').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan & Publikasikan');
        if (res.success) {
            $('#modalArticleForm').modal('hide');
            dtArticles.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan & Publikasikan');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function deleteArticle(id) {
    Swal.fire({
        title: 'Hapus Artikel?',
        text: 'Artikel akan dihapus dan tidak lagi muncul di feed blog landing page.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('articles/delete/') ?>' + id, function(res) {
                if (res.success) {
                    dtArticles.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
                }
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
