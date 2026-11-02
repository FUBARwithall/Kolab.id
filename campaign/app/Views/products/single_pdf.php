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
    }
    .subtitle {
      color: #6b778c;
      font-size: 11px;
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
    .spec-table td.label-col {
      width: 180px;
      background: #fafbfc;
      font-weight: 600;
      color: #42526e;
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
    .badge-success { background: #e3fcef; color: #006644; }
    .badge-danger { background: #ffebe6; color: #de350b; }
    .badge-info { background: #deebff; color: #0747a6; }
    .font-mono {
      font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    .section-title {
      font-size: 13px;
      font-weight: bold;
      color: #172b4d;
      margin: 15px 0 5px 0;
      border-bottom: 1px solid #ebecf0;
      padding-bottom: 5px;
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
      Cetak / Simpan Lembar PDF
    </button>
  </div>

  <div class="header-box">
    <div>
      <h2>Lembar Spesifikasi &amp; Inventaris Produk</h2>
      <div class="subtitle">
        Kolab.id Platform &bull; Manajemen Sampel &amp; Katalog Kolaborasi KOL
      </div>
    </div>
    <div style="text-align: right; font-size: 10.5px; color: #6b778c;">
      <div>No. Ref: <strong class="font-mono">PRD-<?= str_pad((string)$product['id'], 5, '0', STR_PAD_LEFT) ?></strong></div>
      <div>Dicetak: <?= esc($generated) ?></div>
    </div>
  </div>

  <?php
    $stock = (int)($product['stock_qty'] ?? $product['stock'] ?? 0);
    $price = (float)($product['unit_price'] ?? $product['price'] ?? 0);
    $subValuation = $stock * $price;
  ?>

  <div class="section-title">Informasi &amp; Spesifikasi Utama</div>
  <table class="spec-table">
    <tbody>
      <tr>
        <td class="label-col">ID Sistem</td>
        <td class="font-mono">#<?= $product['id'] ?></td>
      </tr>
      <tr>
        <td class="label-col">Kode SKU</td>
        <td class="font-mono"><strong><?= esc($product['sku']) ?></strong></td>
      </tr>
      <tr>
        <td class="label-col">Nama Produk</td>
        <td><strong style="font-size: 13px; color: #172b4d;"><?= esc($product['name']) ?></strong></td>
      </tr>
      <tr>
        <td class="label-col">Kategori Produk</td>
        <td><?= esc($product['category'] ?? '-') ?></td>
      </tr>
      <tr>
        <td class="label-col">Status Ketersediaan</td>
        <td>
          <?php if ($product['is_active'] == 1): ?>
            <span class="badge badge-success">AKTIF (Tersedia untuk Alokasi Kampanye)</span>
          <?php else: ?>
            <span class="badge badge-danger">NONAKTIF (Diarsipkan)</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td class="label-col">Stok Fisik Sampel Saat Ini</td>
        <td class="font-mono">
          <strong style="font-size: 13px;"><?= number_format($stock, 0, ',', '.') ?> Unit / Pcs</strong>
          <?php if ($stock <= 0): ?>
            <span class="badge badge-danger" style="margin-left: 8px;">Stok Habis</span>
          <?php elseif ($stock <= 10): ?>
            <span class="badge badge-info" style="margin-left: 8px;">Stok Menipis</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td class="label-col">Harga Satuan Estimasi</td>
        <td class="font-mono">Rp <?= number_format($price, 0, ',', '.') ?></td>
      </tr>
      <tr>
        <td class="label-col">Total Estimasi Valuasi Stok</td>
        <td class="font-mono"><strong>Rp <?= number_format($subValuation, 0, ',', '.') ?></strong></td>
      </tr>
      <tr>
        <td class="label-col">Deskripsi &amp; Spesifikasi</td>
        <td><?= nl2br(esc($product['description'] ?? '-')) ?></td>
      </tr>
      <tr>
        <td class="label-col">Tanggal Pendaftaran</td>
        <td><?= !empty($product['created_at']) ? date('d F Y, H:i:s', strtotime($product['created_at'])) : '-' ?></td>
      </tr>
      <tr>
        <td class="label-col">Terakhir Diperbarui</td>
        <td><?= !empty($product['updated_at']) ? date('d F Y, H:i:s', strtotime($product['updated_at'])) : '-' ?></td>
      </tr>
    </tbody>
  </table>

  <!-- Varian Produk -->
  <div class="section-title">Daftar Varian Produk</div>
  <table>
    <thead>
      <tr>
        <th style="width: 30px;" class="text-center">#</th>
        <th style="width: 120px;">Kode SKU Varian</th>
        <th>Nama Varian</th>
        <th style="width: 100px;" class="text-center">Stok Unit</th>
        <th style="width: 140px;" class="text-end">Tambahan Harga</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($product['variants'])): ?>
        <?php foreach ($product['variants'] as $vIdx => $v): ?>
          <tr>
            <td class="text-center"><?= $vIdx + 1 ?></td>
            <td class="font-mono"><strong><?= esc($v['sku']) ?></strong></td>
            <td><?= esc($v['name']) ?></td>
            <td class="text-center font-mono"><?= number_format((int)$v['stock_qty'], 0, ',', '.') ?></td>
            <td class="text-end font-mono">
              <?= ((float)$v['additional_price'] > 0) ? '+Rp ' . number_format((float)$v['additional_price'], 0, ',', '.') : 'Normal' ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center" style="padding: 12px; color: #6b778c;">
            Tidak ada sub-varian khusus (Single Master Item).
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Riwayat Mutasi Stok -->
  <div class="section-title">Riwayat Mutasi Stok Terakhir (Maks. 10 Transaksi)</div>
  <table>
    <thead>
      <tr>
        <th style="width: 110px;">Tanggal / Waktu</th>
        <th style="width: 70px;" class="text-center">Jenis</th>
        <th style="width: 70px;" class="text-center">Qty</th>
        <th style="width: 90px;" class="text-center">Saldo Akhir</th>
        <th>Keterangan / Referensi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($product['stock_history'])): ?>
        <?php foreach ($product['stock_history'] as $sh): ?>
          <?php
            $shType = strtoupper($sh['type'] ?? '');
            $typeBadge = ($shType === 'IN')
              ? '<span class="badge badge-success">MASUK</span>'
              : (($shType === 'OUT') ? '<span class="badge badge-danger">KELUAR</span>' : '<span class="badge badge-info">ADJUSTMENT</span>');
          ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($sh['created_at'])) ?></td>
            <td class="text-center"><?= $typeBadge ?></td>
            <td class="text-center font-mono"><strong><?= (int)$sh['qty'] ?></strong></td>
            <td class="text-center font-mono"><?= (int)$sh['balance_after'] ?></td>
            <td style="color: #555;"><?= esc($sh['notes'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center" style="padding: 12px; color: #6b778c;">
            Belum ada catatan mutasi riwayat stok.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Signature Block -->
  <div style="margin-top: 40px; display: flex; justify-content: space-between;">
    <div style="text-align: center; width: 220px; font-size: 10.5px;">
      <div>Petugas Pengelola Sampel:</div>
      <div style="margin-top: 55px; border-bottom: 1px solid #333; font-weight: bold;">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Staff Inventaris / Gudang</div>
    </div>
    <div style="text-align: center; width: 220px; font-size: 10.5px;">
      <div>Disetujui Oleh:</div>
      <div style="margin-top: 55px; border-bottom: 1px solid #333; font-weight: bold;">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Campaign Manager / Lead</div>
    </div>
  </div>

</body>
</html>
