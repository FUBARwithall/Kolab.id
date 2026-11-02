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
    .badge-success { background: #e3fcef; color: #006644; }
    .badge-info { background: #deebff; color: #0747a6; }
    .badge-warning { background: #fffae6; color: #ff8b00; }
    .badge-danger { background: #ffebe6; color: #de350b; }
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
      Cetak / Simpan Lembar Sampel (PDF)
    </button>
  </div>

  <div class="header-box">
    <div>
      <h2>Lembar Spesifikasi Item Sampel Pengiriman</h2>
      <div class="subtitle">
        Kolab.id Platform &bull; Dokumentasi &amp; Penyerahan Sampel Produk KOL
      </div>
    </div>
    <div style="text-align: right; font-size: 10.5px; color: #6b778c;">
      <div>No. Dokumen: <strong class="font-mono">ITEM-SMP-<?= str_pad((string)$item['id'], 6, '0', STR_PAD_LEFT) ?></strong></div>
      <div>Dicetak: <?= esc($generated) ?></div>
    </div>
  </div>

  <?php
    $price = (float)($item['product_price'] ?? 0);
    $extra = (float)($item['variant_additional_price'] ?? 0);
    $unitPrice = $price + $extra;
    $qty = (int)($item['qty'] ?? 1);
    $subtotal = $unitPrice * $qty;

    $courier = $item['courier_name'] ?: ($item['courier'] ?: '-');
    $trackingNo = $item['tracking_number'] ?: ($item['tracking_no'] ?: 'Belum Ada Resi');
    $recipient = $item['recipient_name'] ?: ($item['kol_name'] ?? '-');
    $phone = $item['recipient_phone'] ?: ($item['kol_whatsapp'] ?? '-');
    $address = $item['shipping_address'] ?: '-';
    $status = strtoupper($item['shipping_status'] ?: ($item['status'] ?: 'PENDING'));
  ?>

  <div class="two-cols">
    <div class="info-box">
      <div class="info-box-title">Spesifikasi Sampel Produk</div>
      <div>Nama Produk: <strong><?= esc($item['product_name']) ?></strong></div>
      <div>Kode SKU: <span class="font-mono"><strong><?= esc($item['product_sku'] ?? '-') ?></strong></span></div>
      <div>Varian: <strong><?= esc($item['variant_name'] ?: 'Standar / Default') ?></strong></div>
      <div>Kategori: <?= esc($item['product_category'] ?? '-') ?></div>
      <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #dfe1e6;">
        <div>Kuantitas: <span class="font-mono" style="font-size: 13px;"><strong><?= $qty ?> Unit</strong></span></div>
        <div>Estimasi Valuasi Satuan: <span class="font-mono">Rp <?= number_format($unitPrice, 0, ',', '.') ?></span></div>
        <div>Total Valuasi Sampel: <strong class="font-mono" style="color: #0052cc;">Rp <?= number_format($subtotal, 0, ',', '.') ?></strong></div>
      </div>
    </div>

    <div class="info-box">
      <div class="info-box-title">Alokasi Paket &amp; Penerima KOL</div>
      <div>ID Pengiriman: <strong class="font-mono">#<?= $item['shipment_id'] ?></strong></div>
      <div>No. Resi / AWB: <strong class="font-mono"><?= esc($trackingNo) ?></strong></div>
      <div>Ekspedisi: <strong><?= esc($courier) ?></strong></div>
      <div>Status Kirim: <strong><?= esc($status) ?></strong></div>
      <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed #dfe1e6;">
        <div>Talent KOL / Penerima: <strong><?= esc($recipient) ?></strong></div>
        <div>Kontak: <span class="font-mono"><?= esc($phone) ?></span></div>
        <div>Kampanye: <strong><?= esc($item['campaign_title'] ?? '-') ?></strong></div>
        <div style="margin-top: 3px; font-size: 10.5px; color: #42526e;">
          Alamat Tujuan: <?= nl2br(esc($address)) ?>
        </div>
      </div>
    </div>
  </div>

  <div style="font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #172b4d;">
    Pemeriksaan Fisik &amp; Syarat Review Produk:
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 35px;" class="text-center">#</th>
        <th>Item Pemeriksaan QC</th>
        <th style="width: 130px;" class="text-center">Kondisi Standar</th>
        <th style="width: 140px;" class="text-center">Verifikasi Tim QC</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-center">1</td>
        <td><strong>Kondisi Kemasan / Packaging Luar</strong> (Segel utuh, tidak penyok/bocor)</td>
        <td class="text-center">Baik / Utuh</td>
        <td class="text-center" style="color: #006644; font-size: 10px;">[ &radic; ] Memenuhi Syarat</td>
      </tr>
      <tr>
        <td class="text-center">2</td>
        <td><strong>Kesesuaian Spesifikasi &amp; Varian</strong> (<?= esc($item['variant_name'] ?: 'Standar') ?>)</td>
        <td class="text-center">Sesuai Pesanan</td>
        <td class="text-center" style="color: #006644; font-size: 10px;">[ &radic; ] Memenuhi Syarat</td>
      </tr>
      <tr>
        <td class="text-center">3</td>
        <td><strong>Jumlah Unit Sampel Dialokasikan</strong> (<?= $qty ?> Unit)</td>
        <td class="text-center">Lengkap (<?= $qty ?> Pcs)</td>
        <td class="text-center" style="color: #006644; font-size: 10px;">[ &radic; ] Sesuai</td>
      </tr>
      <tr>
        <td class="text-center">4</td>
        <td><strong>Lampiran Brief / Panduan Review Influencer</strong></td>
        <td class="text-center">Terlampir dalam Box</td>
        <td class="text-center" style="color: #006644; font-size: 10px;">[ &radic; ] Sesuai</td>
      </tr>
    </tbody>
  </table>

  <?php if (!empty($item['product_description'])): ?>
    <div style="background: #f4f5f7; border: 1px solid #dfe1e6; padding: 8px 12px; border-radius: 4px; margin-bottom: 20px; font-size: 11px;">
      <strong>Deskripsi / Catatan Produk:</strong> <?= nl2br(esc($item['product_description'])) ?>
    </div>
  <?php endif; ?>

  <div class="signature-container">
    <div class="sig-col">
      <div>Gudang / Logistik:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Penyedia Stok Produk</div>
    </div>
    <div class="sig-col">
      <div>Verifikasi QC Produk:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Staff Quality Control</div>
    </div>
    <div class="sig-col">
      <div>Penerima Sampel:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Talent KOL / Tim Talent</div>
    </div>
  </div>

</body>
</html>
