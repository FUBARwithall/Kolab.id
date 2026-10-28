<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-shipping-fast text-info me-2"></i>Logistik &amp; Pengiriman Sampel</h3>
        <p class="text-muted small mb-0">Manajemen ekspedisi produk, nomor resi, otomatisasi pengurangan stok sampel, dan tracking penerimaan barang oleh KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Shipments</li>
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
            <select class="form-select form-select-sm rounded-2" id="filterCampaignShipment" style="min-width: 220px;" onchange="reloadShipmentTable()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="filterStatusShipment" style="min-width: 170px;" onchange="reloadShipmentTable()">
              <option value="">Semua Status Logistik</option>
              <option value="PENDING">Pending Packing</option>
              <option value="SHIPPED">Shipped (Dalam Pengiriman)</option>
              <option value="DELIVERED">Delivered (Terkirim)</option>
              <option value="RETURNED">Returned (Retur)</option>
            </select>
          </div>

          <div>
            <button type="button" class="btn btn-primary rounded-2 btn-sm px-3 shadow-sm" onclick="addShipmentModal()">
              <i class="fas fa-box-open me-1"></i> Kirim Sampel Baru
            </button>
          </div>

        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="tableShipments" style="width: 100%;">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 180px;">No. Resi &amp; Ekspedisi</th>
                <th>Kampanye</th>
                <th>Penerima (Talent KOL)</th>
                <th>Daftar Sampel Produk</th>
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

<!-- Modal Form Pengiriman Sampel -->
<div class="modal fade" id="modalShipmentForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form id="formShipment" onsubmit="saveShipment(event)">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="shipmentFormTitle"><i class="fas fa-shipping-fast me-2"></i>Form Pengiriman Sampel KOL</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="shipmentId" name="id">
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Target KOL &amp; Kampanye <span class="text-danger">*</span></label>
            <select class="form-select" id="shipmentCampaignKolId" name="campaign_kol_id" required onchange="onKolSelected()">
              <option value="">-- Pilih KOL Terdaftar --</option>
              <?php foreach ($assignedKols as $ak): ?>
                <option value="<?= $ak['id'] ?>" data-address="<?= esc($ak['address'] ?? '') ?>" data-city="<?= esc($ak['city'] ?? '') ?>" data-postal="<?= esc($ak['postal_code'] ?? '') ?>">
                  <?= esc($ak['kol_name']) ?> — <?= esc($ak['campaign_title']) ?> (<?= esc($ak['city'] ?: 'Kota belum ada') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="card bg-light border-0 p-3 mb-3" id="recipientAddressBox" style="display: none;">
            <div class="small fw-semibold text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i>Alamat Pengiriman Penerima:</div>
            <div class="small text-secondary" id="recipientAddressText">-</div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Jasa Ekspedisi / Kurir <span class="text-danger">*</span></label>
              <select class="form-select" id="shipmentCourier" name="courier_name" required>
                <option value="JNE">JNE Express</option>
                <option value="J&T">J&amp;T Express</option>
                <option value="SiCepat">SiCepat Ekspres</option>
                <option value="Anteraja">Anteraja</option>
                <option value="GoSend">GoSend / GrabExpress (Instant)</option>
                <option value="Lainnya">Kurir Internal / Lainnya</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nomor Resi / Tracking <span class="text-danger">*</span></label>
              <input type="text" class="form-control font-monospace" id="shipmentTracking" name="tracking_number" required placeholder="Contoh: JP1234567890">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status Pengiriman</label>
              <select class="form-select" id="shipmentStatus" name="shipping_status">
                <option value="PENDING">PENDING (Persiapan Packing)</option>
                <option value="SHIPPED" selected>SHIPPED (Sudah Diserahkan ke Ekspedisi)</option>
                <option value="DELIVERED">DELIVERED (Sudah Diterima KOL)</option>
              </select>
              <small class="text-muted">Jika status SHIPPED, stok gudang otomatis berkurang (Mutasi OUT).</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Catatan Pengiriman</label>
              <input type="text" class="form-control" id="shipmentNotes" name="notes" placeholder="Contoh: Titip di satpam jika tidak ada orang">
            </div>
          </div>

          <hr>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-boxes text-success me-1"></i>Daftar Sampel Barang</h6>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill" onclick="addItemRow()">
              <i class="fas fa-plus me-1"></i> Tambah Item
            </button>
          </div>

          <div id="itemsContainer">
            <!-- Row items added dynamically -->
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary rounded-2 px-4" id="btnSaveShipment">Simpan Pengiriman</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Detail Pengiriman -->
<div class="modal fade" id="modalViewShipment" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-truck text-info me-2"></i>Detail Pengiriman Sampel</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="viewShipmentBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Status Logistik -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-truck-loading me-2"></i>Update Status Pengiriman</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="upShipmentId">
        <div class="mb-3">
          <label class="form-label fw-semibold">Status Terkini</label>
          <select class="form-select" id="upStatus">
            <option value="PENDING">PENDING (Persiapan)</option>
            <option value="SHIPPED">SHIPPED (Dalam Perjalanan)</option>
            <option value="DELIVERED">DELIVERED (Sudah Diterima)</option>
            <option value="RETURNED">RETURNED (Paket Gagal / Retur)</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Catatan Ekspedisi</label>
          <input type="text" class="form-control" id="upNotes" placeholder="Contoh: Diterima oleh yang bersangkutan">
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary rounded-2 px-4" onclick="saveUpdatedStatus()">Perbarui Status</button>
      </div>
    </div>
  </div>
</div>

<script>
let dtShipments = null;
const availableProducts = <?= json_encode($allProducts) ?>;
let itemRowIdx = 0;

$(document).ready(function() {
    dtShipments = $('#tableShipments').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('shipments/get-datatable') ?>',
            type: 'POST',
            data: function(d) {
                d.campaign_id = $('#filterCampaignShipment').val();
                d.status = $('#filterStatusShipment').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center text-muted small' },
            { data: 'resi' },
            { data: 'campaign' },
            { data: 'kol' },
            { data: 'items' },
            { data: 'status' },
            { data: 'action', orderable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari nomor resi, kurir, KOL...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ paket",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        }
    });
});

function reloadShipmentTable() {
    dtShipments.ajax.reload();
}

function onKolSelected() {
    const opt = $('#shipmentCampaignKolId option:selected');
    const addr = opt.data('address');
    const city = opt.data('city');
    const postal = opt.data('postal');

    if (addr || city) {
        $('#recipientAddressBox').show();
        $('#recipientAddressText').html(`<strong>${city || '-'}</strong> (Kode Pos: ${postal || '-'})<br>${addr || '-'}`);
    } else {
        $('#recipientAddressBox').hide();
    }
}

function addItemRow(prodId = '', variantId = '', qty = 1) {
    itemRowIdx++;
    let prodOptions = '<option value="">-- Pilih Produk Sampel --</option>';
    availableProducts.forEach(p => {
        prodOptions += `<option value="${p.id}" ${p.id == prodId ? 'selected' : ''}>${p.name} (Stok: ${p.stock})</option>`;
    });

    const html = `
        <div class="row g-2 align-items-center mb-2 item-row" id="itemRow_${itemRowIdx}">
            <div class="col-6">
                <select class="form-select form-select-sm" name="items[${itemRowIdx}][product_id]" required onchange="onProductChange(${itemRowIdx})">
                    ${prodOptions}
                </select>
            </div>
            <div class="col-4">
                <select class="form-select form-select-sm" name="items[${itemRowIdx}][variant_id]" id="variantSelect_${itemRowIdx}">
                    <option value="">Varian Standar</option>
                </select>
            </div>
            <div class="col-1">
                <input type="number" class="form-control form-control-sm text-center" name="items[${itemRowIdx}][qty]" value="${qty}" min="1" required>
            </div>
            <div class="col-1 text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-2" onclick="$('#itemRow_${itemRowIdx}').remove()"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
    `;
    $('#itemsContainer').append(html);
    if (prodId) {
        onProductChange(itemRowIdx, variantId);
    }
}

function onProductChange(idx, selectedVariantId = '') {
    const prodId = $(`#itemRow_${idx} select[name*="product_id"]`).val();
    const variantSelect = $(`#variantSelect_${idx}`);
    variantSelect.empty().append('<option value="">Varian Standar</option>');

    const prod = availableProducts.find(p => p.id == prodId);
    if (prod && prod.variants && prod.variants.length > 0) {
        prod.variants.forEach(v => {
            variantSelect.append(`<option value="${v.id}" ${v.id == selectedVariantId ? 'selected' : ''}>${v.name || v.variant_name}</option>`);
        });
    }
}

function addShipmentModal() {
    $('#formShipment')[0].reset();
    $('#shipmentId').val('');
    $('#itemsContainer').empty();
    $('#recipientAddressBox').hide();
    $('#shipmentFormTitle').html('<i class="fas fa-shipping-fast me-2"></i>Form Pengiriman Sampel Baru');
    addItemRow();
    $('#modalShipmentForm').modal('show');
}

function editShipment(id) {
    $.get('<?= base_url('shipments/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const s = res.data;
            $('#shipmentId').val(s.id);
            $('#shipmentCampaignKolId').val(s.campaign_kol_id);
            onKolSelected();
            $('#shipmentCourier').val(s.courier_name);
            $('#shipmentTracking').val(s.tracking_number);
            $('#shipmentStatus').val(s.shipping_status);
            $('#shipmentNotes').val(s.notes || '');

            $('#itemsContainer').empty();
            if (s.items && s.items.length > 0) {
                s.items.forEach(it => {
                    addItemRow(it.product_id, it.variant_id, it.qty);
                });
            } else {
                addItemRow();
            }

            $('#shipmentFormTitle').html('<i class="fas fa-edit me-2"></i>Edit Pengiriman Sampel');
            $('#modalShipmentForm').modal('show');
        }
    });
}

function saveShipment(e) {
    e.preventDefault();
    const btn = $('#btnSaveShipment');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

    $.post('<?= base_url('shipments/save') ?>', $('#formShipment').serialize(), function(res) {
        btn.prop('disabled', false).text('Simpan Pengiriman');
        if (res.success) {
            $('#modalShipmentForm').modal('hide');
            dtShipments.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        btn.prop('disabled', false).text('Simpan Pengiriman');
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}

function viewShipment(id) {
    $('#modalViewShipment').modal('show');
    $('#viewShipmentBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.get('<?= base_url('shipments/get-one/') ?>' + id, function(res) {
        if (res.success) {
            const s = res.data;
            let itemsHtml = '';
            if (s.items && s.items.length > 0) {
                itemsHtml = s.items.map(it => `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${it.product_name}</strong>
                            ${it.variant_name ? `<span class="text-muted small">(${it.variant_name})</span>` : ''}
                        </div>
                        <span class="badge bg-primary rounded-pill">${it.qty} pcs</span>
                    </li>
                `).join('');
            } else {
                itemsHtml = '<li class="list-group-item text-muted fst-italic">Tidak ada item tercatat</li>';
            }

            $('#viewShipmentBody').html(`
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-dark">${s.courier_name}</span>
                    <span class="badge ${s.shipping_status === 'DELIVERED' ? 'bg-success' : (s.shipping_status === 'SHIPPED' ? 'bg-info' : 'bg-warning')}">${s.shipping_status}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Nomor Resi / AWB</label>
                    <div class="fs-5 fw-bold font-monospace text-primary">${s.tracking_number}</div>
                </div>
                <div class="card bg-light border-0 p-3 mb-3">
                    <label class="text-muted small mb-1">Penerima &amp; Alamat Tujuan</label>
                    <div class="fw-bold text-dark">${s.kol_name} (${s.kol_whatsapp || '-'})</div>
                    <div class="small text-secondary mt-1">${s.kol_address || '-'}<br>Kota ${s.kol_city || '-'}, Pos: ${s.kol_postal_code || '-'}</div>
                </div>
                <h6 class="fw-bold mb-2">Item Sampel yang Dikirim:</h6>
                <ul class="list-group mb-3">
                    ${itemsHtml}
                </ul>
                <div class="small text-muted">
                    <div>Waktu Kirim: <strong>${s.shipped_at || 'Belum di-update'}</strong></div>
                    <div>Waktu Diterima: <strong>${s.delivered_at || 'Belum diterima'}</strong></div>
                    ${s.notes ? `<div>Catatan: <em>${s.notes}</em></div>` : ''}
                </div>
            `);
        }
    });
}

function updateStatusModal(id) {
    $('#upShipmentId').val(id);
    $('#upNotes').val('');
    $('#modalUpdateStatus').modal('show');
}

function saveUpdatedStatus() {
    const id = $('#upShipmentId').val();
    const status = $('#upStatus').val();
    const notes = $('#upNotes').val();

    $.post('<?= base_url('shipments/update-status/') ?>' + id, { status: status, notes: notes }, function(res) {
        if (res.success) {
            $('#modalUpdateStatus').modal('hide');
            dtShipments.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
        Swal.fire({ icon: 'error', title: 'Gagal', text: err });
    });
}
</script>
<?= $this->endSection() ?>
