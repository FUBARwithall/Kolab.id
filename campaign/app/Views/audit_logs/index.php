<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark">
          <i class="fas fa-history text-secondary me-2"></i>Audit Trail &amp; Log Aktivitas
        </h3>
        <p class="text-muted small mb-0">Rekaman jejak operasional, persetujuan brief &amp; draft, verifikasi invoice, dan perubahan data sistem.</p>
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <div class="btn-group me-2">
          <button type="button" class="btn btn-outline-success btn-sm rounded-2 px-3" onclick="exportAuditCsv()" title="Export CSV Data Audit Logs">
            <i class="fas fa-file-excel me-1"></i> Excel / CSV
          </button>
          <button type="button" class="btn btn-outline-danger btn-sm rounded-2 px-3" onclick="exportAuditPdf()" title="Print Laporan PDF">
            <i class="fas fa-file-pdf me-1"></i> Print PDF
          </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" onclick="reloadAuditTable()">
          <i class="fas fa-sync-alt me-1"></i> Refresh Log
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
                <i class="fas fa-clipboard-list fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Total Aktivitas</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h3>
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
                <i class="fas fa-calendar-day fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Hari Ini</span>
                <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['today']) ?></h3>
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
                <i class="fas fa-check-double fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Approval &amp; Verifikasi</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['approvals']) ?></h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3">
                <i class="fas fa-users-cog fs-4"></i>
              </div>
              <div>
                <span class="text-muted small d-block">Operator Terlibat</span>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['unique_users']) ?></h3>
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
            <label class="form-label small text-muted mb-1">Aksi Masal (Bulk Action)</label>
            <div class="input-group input-group-sm">
              <select class="form-select" id="bulkAuditAction">
                <option value="">-- Tindakan Masal --</option>
                <option value="delete">Hapus Log Terpilih</option>
              </select>
              <button type="button" class="btn btn-outline-secondary" onclick="applyBulkAudit()">Terapkan</button>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Jenis Aksi</label>
            <select class="form-select form-select-sm" id="filterAuditAction" onchange="reloadAuditTable()">
              <option value="">-- Semua Aksi --</option>
              <?php foreach ($actions as $act): ?>
                <option value="<?= esc($act) ?>"><?= esc($act) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Entitas Data</label>
            <select class="form-select form-select-sm" id="filterAuditEntity" onchange="reloadAuditTable()">
              <option value="">-- Semua Entitas --</option>
              <?php foreach ($entityTypes as $ent): ?>
                <option value="<?= esc($ent) ?>"><?= strtoupper(esc($ent)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Filter Operator</label>
            <select class="form-select form-select-sm" id="filterAuditUser" onchange="reloadAuditTable()">
              <option value="">-- Semua Operator --</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableAuditLogs" style="width: 100%;">
            <thead class="bg-light text-secondary border-bottom">
              <tr>
                <th style="width: 35px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="checkAllAudit" title="Pilih Semua">
                </th>
                <th style="width: 55px;" class="text-center">ID</th>
                <th style="width: 140px;">Waktu Kejadian</th>
                <th>Operator &amp; Role</th>
                <th style="width: 110px;" class="text-center">Aksi</th>
                <th style="width: 150px;">Entitas Target</th>
                <th>IP Address &amp; User Agent</th>
                <th style="width: 135px;" class="text-center">Tindakan</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Audit Diff Viewer -->
<div class="modal fade" id="modalAuditDiff" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="auditDiffTitle"><i class="fas fa-columns text-primary me-2"></i>Audit Trail Diff Viewer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Log Metadata Header -->
        <div class="card bg-light border-0 p-3 mb-4 rounded-3">
          <div class="row g-2">
            <div class="col-md-3">
              <span class="text-muted small d-block">Operator:</span>
              <strong class="text-dark" id="diffUser">-</strong>
            </div>
            <div class="col-md-3">
              <span class="text-muted small d-block">Aksi &amp; Entitas:</span>
              <strong class="text-dark" id="diffActionEntity">-</strong>
            </div>
            <div class="col-md-3">
              <span class="text-muted small d-block">Waktu:</span>
              <span class="text-dark font-monospace" id="diffTime">-</span>
            </div>
            <div class="col-md-3">
              <span class="text-muted small d-block">IP Address:</span>
              <span class="text-dark font-monospace" id="diffIp">-</span>
            </div>
          </div>
        </div>

        <!-- Side-by-Side Diff Panels -->
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border border-danger-subtle h-100">
              <div class="card-header bg-danger-subtle text-danger fw-semibold py-2">
                <i class="fas fa-minus-circle me-1"></i> Nilai Sebelumnya (Old Values)
              </div>
              <div class="card-body p-0">
                <pre class="p-3 mb-0 font-monospace small bg-white" style="max-height: 380px; overflow-y: auto;" id="diffOldPre">Tidak ada data sebelumnya.</pre>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border border-success-subtle h-100">
              <div class="card-header bg-success-subtle text-success fw-semibold py-2">
                <i class="fas fa-plus-circle me-1"></i> Nilai Baru (New Values)
              </div>
              <div class="card-body p-0">
                <pre class="p-3 mb-0 font-monospace small bg-white" style="max-height: 380px; overflow-y: auto;" id="diffNewPre">Tidak ada perubahan baru.</pre>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
let dtAudit = null;

$(document).ready(function() {
    dtAudit = $('#tableAuditLogs').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('audit-logs/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.action      = $('#filterAuditAction').val();
                d.entity_type = $('#filterAuditEntity').val();
                d.user_id     = $('#filterAuditUser').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, className: 'text-center' },
            { data: 'id', className: 'text-center' },
            { data: 'created_at' },
            { data: 'user' },
            { data: 'action_type', className: 'text-center' },
            { data: 'entity' },
            { data: 'client' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[1, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari aksi, entitas, IP, operator...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ log",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });

    $('#checkAllAudit').on('change', function() {
        $('.check-audit-item').prop('checked', this.checked);
    });

    $('#tableAuditLogs tbody').on('change', '.check-audit-item', function() {
        if (!this.checked) {
            $('#checkAllAudit').prop('checked', false);
        } else if ($('.check-audit-item:checked').length === $('.check-audit-item').length) {
            $('#checkAllAudit').prop('checked', true);
        }
    });
});

function reloadAuditTable() {
    $('#checkAllAudit').prop('checked', false);
    dtAudit.ajax.reload();
}

function getFilterParams() {
    const act = $('#filterAuditAction').val();
    const ent = $('#filterAuditEntity').val();
    const usr = $('#filterAuditUser').val();
    const params = new URLSearchParams();
    if (act) params.append('action', act);
    if (ent) params.append('entity_type', ent);
    if (usr) params.append('user_id', usr);
    const qs = params.toString();
    return qs ? '?' + qs : '';
}

function exportAuditCsv() {
    window.location.href = '<?= base_url('audit-logs/export') ?>' + getFilterParams();
}

function exportAuditPdf() {
    window.open('<?= base_url('audit-logs/export-pdf') ?>' + getFilterParams(), '_blank');
}

function viewAuditDiff(id) {
    $('#diffOldPre').text('Memuat...');
    $('#diffNewPre').text('Memuat...');
    $('#modalAuditDiff').modal('show');

    $.get('<?= base_url('audit-logs/get-one/') ?>/' + id, function(res) {
        if (res.success && res.data) {
            const d = res.data;
            $('#diffUser').text(d.user_name || 'System / Auto');
            $('#diffActionEntity').text(d.action + ' ' + (d.entity_type || '').toUpperCase() + ' #' + d.entity_id);
            $('#diffTime').text(d.created_at + ' WIB');
            $('#diffIp').text(d.ip_address || '-');

            // Format old
            if (d.old_values) {
                try {
                    const parsedOld = JSON.parse(d.old_values);
                    $('#diffOldPre').text(JSON.stringify(parsedOld, null, 2));
                } catch(e) {
                    $('#diffOldPre').text(d.old_values);
                }
            } else {
                $('#diffOldPre').text('— (Null / Data Baru Dibuat) —');
            }

            // Format new
            if (d.new_values) {
                try {
                    const parsedNew = JSON.parse(d.new_values);
                    $('#diffNewPre').text(JSON.stringify(parsedNew, null, 2));
                } catch(e) {
                    $('#diffNewPre').text(d.new_values);
                }
            } else {
                $('#diffNewPre').text('— (Null / Data Dihapus) —');
            }
        }
    }).fail(function() {
        $('#diffOldPre').text('Gagal memuat detail data.');
        $('#diffNewPre').text('Gagal memuat detail data.');
    });
}

function deleteAuditLog(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus catatan audit log #' + id + '?')) return;

    $.ajax({
        url: '<?= base_url('audit-logs/delete') ?>/' + id,
        type: 'POST',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            if (res.success) {
                alert(res.message);
                reloadAuditTable();
            } else {
                alert(res.message || 'Gagal menghapus log');
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat menghapus log audit.');
        }
    });
}

function applyBulkAudit() {
    const action = $('#bulkAuditAction').val();
    if (!action) {
        alert('Silakan pilih aksi masal terlebih dahulu.');
        return;
    }

    const selectedIds = [];
    $('.check-audit-item:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('Pilih setidaknya satu baris log untuk diproses.');
        return;
    }

    if (action === 'delete') {
        if (!confirm('Apakah Anda yakin ingin menghapus ' + selectedIds.length + ' catatan audit log yang dipilih?')) return;

        $.ajax({
            url: '<?= base_url('audit-logs/bulk-delete') ?>',
            type: 'POST',
            data: { ids: selectedIds },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    reloadAuditTable();
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
