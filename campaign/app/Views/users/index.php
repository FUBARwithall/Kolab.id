<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-users-cog text-primary me-2"></i>Manajemen Tim &amp; Pengguna
        </h3>
        <p class="text-muted small mb-0">Pengelolaan personil tim, otorisasi RBAC (Admin, Leader, Staff, KOL), dan status akun.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3 me-2" onclick="reloadUserTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 shadow-sm" onclick="modalNewUser()">
          <i class="fas fa-user-plus me-1"></i> Tambah Pengguna Baru
        </button>
      </div>
    </div>
  </div>
</div>

<div class="app-content pb-5">
  <div class="container-fluid">

    <!-- Stat Cards Overview -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-dark text-white p-3 me-3">
                <i class="fas fa-users fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Akun</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-danger-subtle text-danger p-3 me-3">
                <i class="fas fa-shield-alt fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Superadmin</span>
                <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['admins']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3">
                <i class="fas fa-user-tie fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Leader KOL</span>
                <h3 class="fw-bold mb-0 text-primary"><?= number_format($stats['leaders']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-info-subtle text-info p-3 me-3">
                <i class="fas fa-user-cog fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Staff Operasional</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['staff']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-purple-subtle text-purple p-3 me-3" style="background-color:#ede7f6;color:#673ab7;">
                <i class="fas fa-star fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Talent Influencer</span>
                <h3 class="fw-bold mb-0" style="color:#673ab7;"><?= number_format($stats['kols']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Peran (Role)</label>
            <select class="form-select form-select-sm" id="filterUserRole" onchange="reloadUserTable()">
              <option value="">-- Semua Peran --</option>
              <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>"><?= esc($r['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Status</label>
            <select class="form-select form-select-sm" id="filterUserStatus" onchange="reloadUserTable()">
              <option value="">-- Semua Status --</option>
              <option value="active">AKTIF</option>
              <option value="suspended">DIBEKUKAN</option>
              <option value="pending">PENDING</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableUsers" style="width: 100%;">
            <thead class="bg-light text-secondary border-bottom">
              <tr>
                <th style="width: 60px;">ID</th>
                <th>Pengguna</th>
                <th style="width: 160px;">Peran (Role)</th>
                <th style="width: 140px;">No. Telepon</th>
                <th style="width: 110px;" class="text-center">Status</th>
                <th style="width: 130px;">Terdaftar</th>
                <th style="width: 90px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Form Pengguna (Add / Edit) -->
<div class="modal fade" id="modalUserForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formUser" onsubmit="submitUserForm(event)">
        <input type="hidden" id="userId" name="id" value="">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="userFormTitle"><i class="fas fa-user-edit me-2"></i>Tambah Pengguna Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="userName" name="name" required placeholder="Contoh: Budi Pratama">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="userEmail" name="email" required placeholder="user@kolab.id">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Nomor WhatsApp / HP</label>
            <input type="text" class="form-control" id="userPhone" name="phone" placeholder="08123456789">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Peran (Role) <span class="text-danger">*</span></label>
              <select class="form-select" id="userRole" name="role_id" required>
                <option value="">-- Pilih Peran --</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['id'] ?>"><?= esc($r['label']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status Akun</label>
              <select class="form-select" id="userStatus" name="status">
                <option value="active">AKTIF</option>
                <option value="suspended">DIBEKUKAN</option>
                <option value="pending">PENDING</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold" id="labelPassword">Password</label>
            <input type="password" class="form-control" id="userPassword" name="password" placeholder="Minimal 6 karakter">
            <small class="text-muted" id="helpPassword">Kosongkan jika tidak ingin mengganti password akun.</small>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSubmitUser">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtUsers = null;

$(document).ready(function() {
    dtUsers = $('#tableUsers').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('users/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.role_id = $('#filterUserRole').val();
                d.status  = $('#filterUserStatus').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center' },
            { data: 'user' },
            { data: 'role' },
            { data: 'phone' },
            { data: 'status', className: 'text-center' },
            { data: 'created_at' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari nama, email, role...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pengguna",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadUserTable() {
    dtUsers.ajax.reload();
}

function modalNewUser() {
    $('#formUser')[0].reset();
    $('#userId').val('');
    $('#userFormTitle').html('<i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru');
    $('#userPassword').prop('required', true);
    $('#helpPassword').text('Wajib diisi untuk pengguna baru (default: password123 jika dikosongkan).');
    $('#modalUserForm').modal('show');
}

function editUser(id) {
    $('#formUser')[0].reset();
    $('#userId').val(id);
    $('#userFormTitle').html('<i class="fas fa-user-edit me-2"></i>Edit Data Pengguna #' + id);
    $('#userPassword').prop('required', false);
    $('#helpPassword').text('Kosongkan jika tidak ingin mengganti password akun.');
    $('#modalUserForm').modal('show');

    $.get('<?= base_url('users/get-one/') ?>' + id, function(res) {
        if (res.success && res.data) {
            const u = res.data;
            $('#userName').val(u.name);
            $('#userEmail').val(u.email);
            $('#userPhone').val(u.phone || '');
            $('#userRole').val(u.role_id || '');
            $('#userStatus').val(u.status || 'active');
        }
    }).fail(function() {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat detail pengguna.' });
    });
}

function submitUserForm(e) {
    e.preventDefault();
    const btn = $('#btnSubmitUser');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('users/save') ?>', $('#formUser').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan Data');
        if (res.success) {
            $('#modalUserForm').modal('hide');
            dtUsers.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan Data');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function toggleUserStatus(id, currentStatus) {
    const actionText = (currentStatus === 'active') ? 'membekukan akun' : 'mengaktifkan kembali akun';
    Swal.fire({
        title: 'Konfirmasi Status',
        text: 'Apakah Anda yakin ingin ' + actionText + ' ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('users/toggle-status/') ?>' + id, {}, function(res) {
                if (res.success) {
                    dtUsers.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
                }
            }).fail(function(xhr) {
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan';
                Swal.fire({ icon: 'error', title: 'Gagal', text: err });
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
