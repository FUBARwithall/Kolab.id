<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?></title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 11.5px;
      color: #333;
      margin: 20px;
      line-height: 1.5;
    }
    .header-box {
      border-bottom: 2px solid #0052cc;
      padding-bottom: 12px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    h2 {
      margin: 0 0 4px 0;
      font-size: 18px;
      color: #172b4d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .subtitle {
      color: #6b778c;
      font-size: 11px;
    }
    .two-cols {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }
    .info-box {
      flex: 1;
      border: 1px solid #dfe1e6;
      border-radius: 4px;
      padding: 10px 14px;
      background: #fafbfc;
    }
    .info-box-title {
      font-weight: bold;
      font-size: 11px;
      color: #0052cc;
      text-transform: uppercase;
      margin-bottom: 6px;
      border-bottom: 1px solid #ebecf0;
      padding-bottom: 4px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #dfe1e6;
      padding: 7px 10px;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background: #f4f5f7;
      font-weight: 600;
      font-size: 10.5px;
      text-transform: uppercase;
      color: #5e6c84;
    }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 3px;
      font-weight: bold;
      font-size: 10px;
    }
    .badge-in { background: #e3fcef; color: #006644; }
    .badge-out { background: #ffebe6; color: #de350b; }
    .badge-adj { background: #deebff; color: #0747a6; }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    .signature-container {
      margin-top: 40px;
      display: flex;
      justify-content: space-between;
    }
    .sig-col {
      text-align: center;
      width: 190px;
      font-size: 10.5px;
    }
    .sig-space {
      margin-top: 55px;
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
    <button onclick="window.print()" style="padding: 8px 18px; background: #0052cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      Cetak / Simpan Bukti Mutasi (PDF)
    </button>
  </div>

  <div class="header-box">
    <div>
      <h2>Bukti Transaksi Pergerakan &amp; Mutasi Stok</h2>
      <div class="subtitle">
        Kolab.id Platform &bull; Dokumentasi Resmi Mutasi Inventaris Gudang Sampel
      </div>
    </div>
    <div style="text-align: right; font-size: 10.5px; color: #6b778c;">
      <div>No. Bukti: <strong class="font-mono">VCH-STK-<?= str_pad((string)$item['id'], 6, '0', STR_PAD_LEFT) ?></strong></div>
      <div>Dicetak: <?= esc($generated) ?></div>
    </div>
  </div>

  <?php
    $type = strtoupper($item['type'] ?: ($item['change_type'] ?? ''));
    $typeName = match ($type) {
      'IN'         => 'BARANG MASUK (RESTOCK / RETUR)',
      'OUT'        => 'BARANG KELUAR (ALOKASI SAMPEL)',
      'ADJUSTMENT' => 'PENYESUAIAN STOK (KOREKSI OPNAME)',
      default      => $type,
    };
    $sign = match ($type) {
      'IN'    => '+',
      'OUT'   => '-',
      default => '',
    };
    $price = (float)($item['product_price'] ?? 0);
    $valuation = $price * (int)$item['qty'];
  ?>

  <div class="two-cols">
    <div class="info-box">
      <div class="info-box-title">Identitas Produk Terkait</div>
      <div>Nama Produk: <strong><?= esc($item['product_name']) ?></strong></div>
      <div>Kode SKU: <span class="font-mono"><strong><?= esc($item['product_sku'] ?? '-') ?></strong></span></div>
      <div>Varian: <strong><?= esc($item['variant_name'] ?: 'Standar / Default') ?></strong></div>
      <div>Kategori: <?= esc($item['product_category'] ?? 'Umum') ?></div>
      <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #dfe1e6;">
        <div>Estimasi Harga Satuan: <span class="font-mono">Rp <?= number_format($price, 0, ',', '.') ?></span></div>
        <div>Total Valuasi Mutasi: <strong class="font-mono" style="color: #0052cc;">Rp <?= number_format($valuation, 0, ',', '.') ?></strong></div>
      </div>
    </div>

    <div class="info-box">
      <div class="info-box-title">Informasi Dokumen &amp; Otorisasi</div>
      <div>Klasifikasi: <strong><?= esc($typeName) ?></strong></div>
      <div>Waktu Mutasi: <span class="font-mono"><?= date('d F Y, H:i:s', strtotime($item['created_at'])) ?> WIB</span></div>
      <div>Referensi: <strong><?= esc($item['reference_type'] ?: 'MANUAL') ?> <?= $item['reference_id'] ? '#' . $item['reference_id'] : '' ?></strong></div>
      <?php if (!empty($item['shipment_tracking'])): ?>
        <div>No. Resi Pengiriman: <strong class="font-mono"><?= esc($item['shipment_tracking']) ?></strong> (<?= esc($item['shipment_courier'] ?? 'Kurir') ?>)</div>
      <?php endif; ?>
      <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #dfe1e6;">
        <div>Operator Pencatat: <strong><?= esc($item['user_name'] ?? 'Admin') ?></strong></div>
        <div style="font-size: 10.5px; color: #42526e;">Email: <?= esc($item['user_email'] ?? '-') ?></div>
      </div>
    </div>
  </div>

  <div style="font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #172b4d;">
    Kalkulasi Saldo Fisik Gudang:
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 150px;">Komponen Saldo</th>
        <th style="width: 120px;" class="text-center">Kuantitas (Unit)</th>
        <th>Status / Keterangan Mutasi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Saldo Stok Awal</strong></td>
        <td class="text-center font-mono" style="font-size: 13px;">
          <?= number_format((int)$item['stock_before'], 0, ',', '.') ?>
        </td>
        <td class="text-muted">Jumlah fisik di gudang sebelum transaksi mutasi dicatat.</td>
      </tr>
      <tr>
        <td><strong>Kuantitas Transaksi</strong></td>
        <td class="text-center font-mono" style="font-size: 14px; font-weight: bold; color: <?= $type === 'IN' ? '#006644' : ($type === 'OUT' ? '#de350b' : '#0747a6') ?>;">
          <?= $sign ?><?= number_format((int)$item['qty'], 0, ',', '.') ?>
        </td>
        <td>
          <strong><?= esc($typeName) ?></strong> &bull; Pergerakan fisik unit barang.
        </td>
      </tr>
      <tr style="background: #fafbfc; font-weight: bold;">
        <td><strong>Saldo Stok Akhir</strong></td>
        <td class="text-center font-mono" style="font-size: 14px; color: #0052cc;">
          <?= number_format((int)$item['balance_after'], 0, ',', '.') ?>
        </td>
        <td>Saldo fisik terkini yang tercatat secara sah di master inventaris.</td>
      </tr>
    </tbody>
  </table>

  <div style="background: #f4f5f7; border: 1px solid #dfe1e6; padding: 10px 14px; border-radius: 4px; margin-bottom: 25px; font-size: 11px;">
    <strong>Catatan / Alasan Mutasi:</strong><br>
    <?= nl2br(esc($item['notes'] ?: 'Tidak ada memo tertulis.')) ?>
  </div>

  <div class="signature-container">
    <div class="sig-col">
      <div>Dibuat Oleh:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Staff Inventaris / Gudang</div>
    </div>
    <div class="sig-col">
      <div>Diperiksa Oleh:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Quality Control &amp; Logistik</div>
    </div>
    <div class="sig-col">
      <div>Disetujui Oleh:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Supervisor / Kepala Gudang</div>
    </div>
  </div>

</body>
</html>
