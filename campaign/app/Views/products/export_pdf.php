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
    .badge-danger { background: #ffebe6; color: #de350b; }
    .badge-warning { background: #fffae6; color: #ff8b00; }
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
    Dicetak pada: <?= esc($generated) ?> &bull; Total Terdaftar: <?= count($items) ?> Produk &bull; Kolab.id Campaign Management
  </div>

  <?php
    $totalStock = 0;
    $totalValuation = 0;
    $activeCount = 0;
    foreach ($items as $p) {
      $stk = (int)($p['stock_qty'] ?? $p['stock'] ?? 0);
      $prc = (float)($p['unit_price'] ?? $p['price'] ?? 0);
      $totalStock += $stk;
      $totalValuation += ($stk * $prc);
      if ($p['is_active'] == 1) $activeCount++;
    }
  ?>

  <div class="summary-box">
    <div class="summary-item"><strong>Total Item:</strong> <?= count($items) ?> SKU</div>
    <div class="summary-item"><strong>Produk Aktif:</strong> <?= $activeCount ?> SKU</div>
    <div class="summary-item"><strong>Total Unit Sampel:</strong> <?= number_format($totalStock, 0, ',', '.') ?> Pcs</div>
    <div class="summary-item"><strong>Total Valuasi Stok:</strong> Rp <?= number_format($totalValuation, 0, ',', '.') ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 40px;" class="text-center">ID</th>
        <th style="width: 90px;">Kode SKU</th>
        <th>Nama Produk</th>
        <th style="width: 100px;">Kategori</th>
        <th style="width: 75px;" class="text-center">Stok Sampel</th>
        <th style="width: 105px;" class="text-end">Harga Satuan (Rp)</th>
        <th style="width: 110px;" class="text-end">Valuasi Stok (Rp)</th>
        <th style="width: 75px;" class="text-center">Status</th>
        <th style="width: 80px;" class="text-center">Tgl Daftar</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): ?>
          <?php
            $stock = (int)($row['stock_qty'] ?? $row['stock'] ?? 0);
            $price = (float)($row['unit_price'] ?? $row['price'] ?? 0);
            $subVal = $stock * $price;
          ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="text-center font-mono">#<?= $row['id'] ?></td>
            <td class="font-mono"><strong><?= esc($row['sku']) ?></strong></td>
            <td>
              <strong><?= esc($row['name']) ?></strong>
              <?php if (!empty($row['description'])): ?>
                <div style="font-size: 9.5px; color: #6b778c; margin-top: 2px;">
                  <?= esc(mb_strimwidth($row['description'], 0, 70, '...')) ?>
                </div>
              <?php endif; ?>
            </td>
            <td><?= esc($row['category'] ?? '-') ?></td>
            <td class="text-center font-mono">
              <?php if ($stock <= 0): ?>
                <span class="badge badge-danger">0 (Habis)</span>
              <?php elseif ($stock <= 10): ?>
                <span class="badge badge-warning"><?= $stock ?></span>
              <?php else: ?>
                <?= number_format($stock, 0, ',', '.') ?>
              <?php endif; ?>
            </td>
            <td class="text-end font-mono">Rp <?= number_format($price, 0, ',', '.') ?></td>
            <td class="text-end font-mono">Rp <?= number_format($subVal, 0, ',', '.') ?></td>
            <td class="text-center">
              <?php if ($row['is_active'] == 1): ?>
                <span class="badge badge-success">AKTIF</span>
              <?php else: ?>
                <span class="badge badge-danger">NONAKTIF</span>
              <?php endif; ?>
            </td>
            <td class="text-center"><?= !empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="10" class="text-center" style="padding: 20px; color: #6b778c;">
            Tidak ada data produk yang ditemukan.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr style="background: #f4f5f7; font-weight: bold;">
        <td colspan="5" class="text-end">TOTAL KESELURUHAN:</td>
        <td class="text-center font-mono"><?= number_format($totalStock, 0, ',', '.') ?></td>
        <td></td>
        <td class="text-end font-mono">Rp <?= number_format($totalValuation, 0, ',', '.') ?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
    <div style="text-align: center; width: 220px; font-size: 10.5px;">
      <div>Dicetak oleh,</div>
      <div style="margin-top: 50px; border-bottom: 1px solid #333; font-weight: bold;">Tim Inventaris &amp; Sampel</div>
      <div style="color: #6b778c; margin-top: 3px;">Kolab.id Platform</div>
    </div>
  </div>
</body>
</html>
