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
    .badge-primary { background: #e6fcff; color: #008da6; }
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
    Dicetak pada: <?= esc($generated) ?> &bull; Total Pengiriman: <?= count($items) ?> Paket &bull; Kolab.id Logistics Management
  </div>

  <?php
    $totalFee = 0;
    $totalUnits = 0;
    $shippedCount = 0;
    $deliveredCount = 0;
    foreach ($items as $s) {
      $totalFee += (float)($s['shipping_fee'] ?? 0);
      $st = strtoupper($s['shipping_status'] ?: ($s['status'] ?: ''));
      if ($st === 'SHIPPED') $shippedCount++;
      if ($st === 'DELIVERED') $deliveredCount++;
      if (!empty($s['items'])) {
        foreach ($s['items'] as $it) {
          $totalUnits += (int)$it['qty'];
        }
      }
    }
  ?>

  <div class="summary-box">
    <div class="summary-item"><strong>Total Paket:</strong> <?= count($items) ?> Resi</div>
    <div class="summary-item"><strong>Dalam Pengiriman (Shipped):</strong> <?= $shippedCount ?> Paket</div>
    <div class="summary-item"><strong>Terkirim (Delivered):</strong> <?= $deliveredCount ?> Paket</div>
    <div class="summary-item"><strong>Total Unit Sampel:</strong> <?= number_format($totalUnits, 0, ',', '.') ?> Pcs</div>
    <div class="summary-item"><strong>Total Ongkos Kirim:</strong> Rp <?= number_format($totalFee, 0, ',', '.') ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 35px;" class="text-center">ID</th>
        <th style="width: 110px;">No. Resi &amp; Kurir</th>
        <th style="width: 140px;">Kampanye</th>
        <th style="width: 130px;">Penerima (Talent KOL)</th>
        <th style="width: 90px;">Kota Tujuan</th>
        <th>Daftar Sampel Barang</th>
        <th style="width: 75px;" class="text-end">Ongkir</th>
        <th style="width: 80px;" class="text-center">Status</th>
        <th style="width: 80px;" class="text-center">Tgl Kirim</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $statusVal = strtoupper($row['shipping_status'] ?: ($row['status'] ?: 'PENDING'));
            $badgeCls = match ($statusVal) {
              'DELIVERED' => 'badge-success',
              'SHIPPED'   => 'badge-info',
              'PROCESSING'=> 'badge-primary',
              'RETURNED', 'RETURN_PENDING' => 'badge-danger',
              default     => 'badge-warning',
            };
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center font-mono">#<?= $row['id'] ?></td>
            <td>
              <strong class="font-mono"><?= esc($row['tracking_number'] ?: ($row['tracking_no'] ?: 'Draft')) ?></strong>
              <div style="font-size: 9.5px; color: #6b778c;"><?= esc($row['courier_name'] ?: ($row['courier'] ?: '-')) ?></div>
            </td>
            <td><?= esc($row['campaign_title'] ?? '-') ?></td>
            <td>
              <strong><?= esc($row['recipient_name'] ?: ($row['kol_name'] ?? '-')) ?></strong>
              <?php if (!empty($row['recipient_phone'] ?: ($row['kol_whatsapp'] ?? ''))): ?>
                <div style="font-size: 9.5px; color: #6b778c;"><?= esc($row['recipient_phone'] ?: ($row['kol_whatsapp'] ?? '')) ?></div>
              <?php endif; ?>
            </td>
            <td><?= esc($row['kol_city'] ?: '-') ?></td>
            <td>
              <?php if (!empty($row['items'])): ?>
                <?php foreach ($row['items'] as $it): ?>
                  <div style="font-size: 10px; margin-bottom: 2px;">
                    &bull; <?= esc($it['product_name']) ?> <?= $it['variant_name'] ? '(' . esc($it['variant_name']) . ')' : '' ?> &times; <strong><?= $it['qty'] ?></strong>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <span style="color: #6b778c;">-</span>
              <?php endif; ?>
            </td>
            <td class="text-end font-mono">
              Rp <?= number_format((float)($row['shipping_fee'] ?? 0), 0, ',', '.') ?>
            </td>
            <td class="text-center">
              <span class="badge <?= $badgeCls ?>"><?= $statusVal ?></span>
            </td>
            <td class="text-center">
              <?= !empty($row['shipped_at']) ? date('d/m/Y', strtotime($row['shipped_at'])) : '-' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="10" class="text-center" style="padding: 20px; color: #6b778c;">
            Tidak ada data pengiriman sampel yang ditemukan.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr style="background: #f4f5f7; font-weight: bold;">
        <td colspan="6" class="text-end">TOTAL KESELURUHAN ONGKIR:</td>
        <td class="text-center font-mono"><?= number_format($totalUnits, 0, ',', '.') ?> unit sampel</td>
        <td class="text-end font-mono">Rp <?= number_format($totalFee, 0, ',', '.') ?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
    <div style="text-align: center; width: 220px; font-size: 10.5px;">
      <div>Dicetak oleh,</div>
      <div style="margin-top: 50px; border-bottom: 1px solid #333; font-weight: bold;">Tim Logistik &amp; Gudang</div>
      <div style="color: #6b778c; margin-top: 3px;">Kolab.id Platform</div>
    </div>
  </div>
</body>
</html>
