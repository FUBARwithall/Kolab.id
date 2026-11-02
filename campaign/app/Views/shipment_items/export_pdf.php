<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 11px;
      color: #333;
      margin: 15px;
    }
    h2 {
      margin: 0 0 5px 0;
      font-size: 16px;
      color: #172b4d;
    }
    .subtitle {
      color: #6b778c;
      font-size: 10.5px;
      margin-bottom: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #dfe1e6;
      padding: 6px 8px;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background: #f4f5f7;
      font-weight: 600;
      font-size: 10px;
      text-transform: uppercase;
      color: #5e6c84;
    }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 9px;
    }
    .badge-success { background: #e3fcef; color: #006644; }
    .badge-info { background: #deebff; color: #0747a6; }
    .badge-warning { background: #fffae6; color: #ff8b00; }
    .badge-danger { background: #ffebe6; color: #de350b; }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 10px;
    }
    .summary-box {
      display: flex;
      gap: 15px;
      margin-bottom: 12px;
    }
    .summary-item {
      background: #f8f9fa;
      border: 1px solid #e2e8f0;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 11px;
    }
    @media print {
      body { margin: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom: 15px;">
    <button onclick="window.print()" style="padding: 7px 15px; background: #0052cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      Cetak / Simpan PDF
    </button>
  </div>

  <h2><?= esc($title) ?></h2>
  <div class="subtitle">
    Dicetak pada: <?= esc($generated) ?> &bull; Total Baris Item: <?= count($items) ?> &bull; Kolab.id Inventory &amp; Logistics Management
  </div>

  <?php
    $totalQty = 0;
    $totalValuation = 0;
    foreach ($items as $it) {
      $q = (int)$it['qty'];
      $p = (float)($it['product_price'] ?? 0);
      $totalQty += $q;
      $totalValuation += ($q * $p);
    }
  ?>

  <div class="summary-box">
    <div class="summary-item"><strong>Total Alokasi Baris:</strong> <?= count($items) ?> Item</div>
    <div class="summary-item"><strong>Total Unit Sampel:</strong> <?= number_format($totalQty, 0, ',', '.') ?> Pcs</div>
    <div class="summary-item"><strong>Total Estimasi Valuasi:</strong> Rp <?= number_format($totalValuation, 0, ',', '.') ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 35px;" class="text-center">ID</th>
        <th style="width: 100px;">No. Resi</th>
        <th style="width: 140px;">Penerima (KOL)</th>
        <th style="width: 90px;">Kode SKU</th>
        <th>Nama Produk &amp; Varian</th>
        <th style="width: 80px;">Kategori</th>
        <th style="width: 60px;" class="text-center">Qty</th>
        <th style="width: 90px;" class="text-end">Harga Satuan</th>
        <th style="width: 100px;" class="text-end">Valuasi Item</th>
        <th style="width: 75px;" class="text-center">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $price = (float)($row['product_price'] ?? 0);
            $qty = (int)$row['qty'];
            $sub = $qty * $price;
            $statusVal = strtoupper($row['shipping_status'] ?: ($row['status'] ?: 'PENDING'));
            $badgeCls = match ($statusVal) {
              'DELIVERED' => 'badge-success',
              'SHIPPED'   => 'badge-info',
              'RETURNED'  => 'badge-danger',
              default     => 'badge-warning',
            };
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center font-mono">#<?= $row['id'] ?></td>
            <td class="font-mono"><?= esc($row['tracking_number'] ?: ($row['tracking_no'] ?: 'Draft #' . $row['shipment_id'])) ?></td>
            <td><strong><?= esc($row['recipient_name'] ?: ($row['kol_name'] ?? '-')) ?></strong></td>
            <td class="font-mono"><?= esc($row['product_sku'] ?? '-') ?></td>
            <td>
              <strong><?= esc($row['product_name']) ?></strong>
              <?php if (!empty($row['variant_name'])): ?>
                <span style="color: #0052cc;">(<?= esc($row['variant_name']) ?>)</span>
              <?php endif; ?>
            </td>
            <td><?= esc($row['product_category'] ?? '-') ?></td>
            <td class="text-center font-mono fw-bold"><?= $qty ?></td>
            <td class="text-end font-mono">Rp <?= number_format($price, 0, ',', '.') ?></td>
            <td class="text-end font-mono">Rp <?= number_format($sub, 0, ',', '.') ?></td>
            <td class="text-center"><span class="badge <?= $badgeCls ?>"><?= $statusVal ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="11" class="text-center" style="padding: 20px; color: #6b778c;">
            Tidak ada data item sampel pengiriman yang ditemukan.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr style="background: #f4f5f7; font-weight: bold;">
        <td colspan="7" class="text-end">TOTAL KESELURUHAN:</td>
        <td class="text-center font-mono"><?= number_format($totalQty, 0, ',', '.') ?> Pcs</td>
        <td></td>
        <td class="text-end font-mono">Rp <?= number_format($totalValuation, 0, ',', '.') ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
    <div style="text-align: center; width: 220px; font-size: 10.5px;">
      <div>Dicetak oleh,</div>
      <div style="margin-top: 50px; border-bottom: 1px solid #333; font-weight: bold;">Staff Pengawas Logistik</div>
      <div style="color: #6b778c; margin-top: 3px;">Kolab.id Platform</div>
    </div>
  </div>
</body>
</html>
