<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="fas fa-receipt text-warning me-2"></i> Tagihan &amp; Riwayat Pembayaran Saya
          </h5>
          <small class="text-muted">Pantau status pencairan fee kampanye, pemotongan pajak (PPh21/23), dan bukti transfer</small>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
          <?= count($invoices) ?> Tagihan
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">No. Invoice</th>
                <th>Kampanye</th>
                <th>Subtotal Fee</th>
                <th>Potongan Pajak</th>
                <th>Total Diterima (Net)</th>
                <th>Status Pembayaran</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($invoices)): ?>
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <i class="fas fa-file-invoice-dollar fa-3x mb-2 text-secondary opacity-50"></i>
                    <div>Belum ada tagihan yang diterbitkan. Tagihan dibuat otomatis setelah draft disetujui.</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                  <tr>
                    <td class="ps-3">
                      <div class="fw-bold text-dark"><?= esc($inv['invoice_number'] ?? 'INV-' . $inv['id']) ?></div>
                    </td>
                    <td>
                      <small class="fw-semibold text-primary"><?= esc($inv['campaign_title'] ?? '-') ?></small>
                    </td>
                    <td>
                      Rp <?= number_format((float) ($inv['subtotal'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td>
                      <small class="text-danger">
                        <?= esc($inv['tax_type'] ?? 'PPh21') ?> (Rp <?= number_format((float) ($inv['tax_amount'] ?? 0), 0, ',', '.') ?>)
                      </small>
                    </td>
                    <td class="fw-bold text-success fs-6">
                      Rp <?= number_format((float) ($inv['grand_total'] ?? 0), 0, ',', '.') ?>
                    </td>
                    <td>
                      <?php 
                        $badgeClass = 'bg-warning text-dark';
                        if ($inv['status'] === 'PAID') $badgeClass = 'bg-success text-white';
                        if ($inv['status'] === 'VERIFIED') $badgeClass = 'bg-info text-white';
                        if ($inv['status'] === 'REJECTED') $badgeClass = 'bg-danger text-white';
                      ?>
                      <span class="badge <?= $badgeClass ?> px-2 py-1 rounded-pill" style="font-size: 10px;">
                        <?= esc($inv['status']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
