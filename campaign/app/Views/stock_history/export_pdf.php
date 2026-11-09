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
      margin: 20px;
      line-height: 1.4;
    }
    .header-box {
      border-bottom: 2px solid #0052cc;
      padding-bottom: 12px;
      margin-bottom: 16px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    h2 {
      margin: 0 0 4px 0;
      font-size: 17px;
      color: #172b4d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .subtitle {
      color: #6b778c;
      font-size: 10.5px;
    }
    .summary-grid {
      display: flex;
      gap: 12px;
      margin-bottom: 16px;
    }
    .summary-card {
      flex: 1;
      border: 1px solid #dfe1e6;
      border-radius: 4px;
      padding: 8px 12px;
      background: #fafbfc;
      text-align: center;
    }
    .summary-card-title {
      font-size: 9.5px;
      text-transform: uppercase;
      color: #6b778c;
      font-weight: bold;
      margin-bottom: 3px;
    }
    .summary-card-val {
      font-size: 14px;
      font-weight: bold;
      font-family: monospace;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #dfe1e6;
      padding: 6px 8px;
      text-align: left;
      vertical-align: middle;
      font-size: 10px;
    }
    th {
      background: #f4f5f7;
      font-weight: 600;
      text-transform: uppercase;
      color: #5e6c84;
      font-size: 9.5px;
    }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    .badge {
      display: inline-block;
      padding: 2px 5px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 9px;
    }
    .badge-in { background: #e3fcef; color: #006644; }
    .badge-out { background: #ffebe6; color: #de350b; }
    .badge-adj { background: #deebff; color: #0747a6; }
    .signature-container {
      margin-top: 35px;
      display: flex;
      justify-content: space-between;
    }
    .sig-col {
      text-align: center;
      width: 200px;
      font-size: 10px;
    }
    .sig-space {
      margin-top: 50px;
      border-bottom: 1px solid #333;
      font-weight: bold;
    }
    @media print {
      body { margin: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>

  <div class="no-print" style="margin-bottom: 20px;">
    <button onclick="window.print()" style="padding: 8px 18px; background: #0052cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px;">
      Cetak / Simpan PDF
    </button>
  </div>

  <div class="header-box">
    <div>
      <h2><?= esc($title) ?></h2>
      <div class="subtitle">
        Kolab.id Platform &bull; Buku Besar Mutasi Inventaris Sampel Produk &amp; Logistik Influencer
      </div>
    </div>
    <div style="text-align: right; font-size: 10px; color: #6b778c;">
      <div>Dicetak: <strong><?= esc($generated) ?></strong></div>
      <div>Total Data: <strong><?= count($items) ?> Mutasi</strong></div>
    </div>
  </div>

  <?php if (!empty($summary)): ?>
    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-card-title">Total Mutasi</div>
        <div class="summary-card-val text-dark"><?= number_format($summary['total_mutations'] ?? 0, 0, ',', '.') ?></div>
      </div>
      <div class="summary-card">
        <div class="summary-card-title">Total Masuk (IN)</div>
        <div class="summary-card-val" style="color: #006644;">+<?= number_format($summary['total_in'] ?? 0, 0, ',', '.') ?></div>
      </div>
      <div class="summary-card">
        <div class="summary-card-title">Total Keluar (OUT)</div>
        <div class="summary-card-val" style="color: #de350b;">-<?= number_format($summary['total_out'] ?? 0, 0, ',', '.') ?></div>
      </div>
      <div class="summary-card">
        <div class="summary-card-title">Total Koreksi (ADJ)</div>
        <div class="summary-card-val" style="color: #0747a6;"><?= number_format($summary['total_adjustment'] ?? 0, 0, ',', '.') ?></div>
      </div>
    </div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th style="width: 30px;" class="text-center">#</th>
        <th style="width: 100px;">Waktu Mutasi</th>
        <th style="width: 90px;">Kode SKU</th>
        <th>Nama Produk &amp; Varian</th>
        <th style="width: 65px;" class="text-center">Tipe</th>
        <th style="width: 60px;" class="text-end">Jumlah</th>
        <th style="width: 65px;" class="text-end">Stok Awal</th>
        <th style="width: 65px;" class="text-end">Stok Akhir</th>
        <th>Referensi &amp; Alasan</th>
        <th style="width: 80px;">Operator</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($items)): ?>
        <?php foreach ($items as $idx => $row): 
          $type = strtoupper($row['type'] ?: ($row['change_type'] ?? ''));
          $badgeClass = match ($type) {
            'IN'         => 'badge-in',
            'OUT'        => 'badge-out',
            'ADJUSTMENT' => 'badge-adj',
            default      => '',
          };
          $sign = match ($type) {
            'IN'    => '+',
            'OUT'   => '-',
            default => '',
          };
        ?>
          <tr>
            <td class="text-center font-mono"><?= $idx + 1 ?></td>
            <td class="font-mono"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td class="font-mono"><strong><?= esc($row['product_sku'] ?? '-') ?></strong></td>
            <td>
              <strong><?= esc($row['product_name']) ?></strong>
              <?php if (!empty($row['variant_name'])): ?>
                <span style="color: #0052cc;">(<?= esc($row['variant_name']) ?>)</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <span class="badge <?= $badgeClass ?>"><?= $type ?></span>
            </td>
            <td class="text-end font-mono">
              <strong><?= $sign ?><?= number_format((int)$row['qty'], 0, ',', '.') ?></strong>
            </td>
            <td class="text-end font-mono text-muted"><?= number_format((int)$row['stock_before'], 0, ',', '.') ?></td>
            <td class="text-end font-mono"><strong><?= number_format((int)$row['balance_after'], 0, ',', '.') ?></strong></td>
            <td>
              <?php if (!empty($row['reference_type'])): ?>
                [<strong><?= esc($row['reference_type']) ?></strong>]
              <?php endif; ?>
              <?= esc($row['notes'] ?: '-') ?>
            </td>
            <td><?= esc($row['user_name'] ?? 'Admin') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="10" class="text-center" style="padding: 20px; color: #6b778c;">
            Tidak ada riwayat mutasi stok pada periode filter yang dipilih.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="signature-container">
    <div class="sig-col">
      <div>Petugas Gudang / Inventaris:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Staff Warehouse</div>
    </div>
    <div class="sig-col">
      <div>Auditor / Logistik:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Staff Quality &amp; Logistik</div>
    </div>
    <div class="sig-col">
      <div>Disetujui Oleh:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Supervisor Operasional</div>
    </div>
  </div>

</body>
</html>
