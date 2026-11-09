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
      Cetak / Simpan Surat Jalan (PDF)
    </button>
  </div>

  <div class="header-box">
    <div>
      <h2>Surat Jalan Pengiriman Sampel Produk</h2>
      <div class="subtitle">
        Kolab.id Platform &bull; Manajemen Logistik Influencer &amp; Ekspedisi Sampel
      </div>
    </div>
    <div style="text-align: right; font-size: 10.5px; color: #6b778c;">
      <div>No. Surat Jalan: <strong class="font-mono">SJ-SMP-<?= str_pad((string)$shipment['id'], 5, '0', STR_PAD_LEFT) ?></strong></div>
      <div>Dicetak: <?= esc($generated) ?></div>
    </div>
  </div>

  <?php
    $status = strtoupper($shipment['shipping_status'] ?: ($shipment['status'] ?: 'PENDING'));
    $courier = $shipment['courier_name'] ?: ($shipment['courier'] ?: '-');
    $trackingNo = $shipment['tracking_number'] ?: ($shipment['tracking_no'] ?: 'Belum Ada Resi');
    $recipient = $shipment['recipient_name'] ?: ($shipment['kol_name'] ?? '-');
    $phone = $shipment['recipient_phone'] ?: ($shipment['kol_whatsapp'] ?? '-');
    $address = $shipment['shipping_address'] ?: ($shipment['kol_address'] ?: '-');
  ?>

  <div class="two-cols">
    <div class="info-box">
      <div class="info-box-title">Penerima Paket (Talent KOL)</div>
      <div><strong><?= esc($recipient) ?></strong></div>
      <div>No. HP / WA: <span class="font-mono"><?= esc($phone) ?></span></div>
      <div>Kota Tujuan: <?= esc($shipment['kol_city'] ?: '-') ?></div>
      <div style="margin-top: 4px; font-size: 11px; color: #42526e;">
        Alamat: <?= nl2br(esc($address)) ?>
      </div>
    </div>

    <div class="info-box">
      <div class="info-box-title">Informasi Ekspedisi &amp; Kampanye</div>
      <div>Jasa Kurir: <strong><?= esc($courier) ?></strong></div>
      <div>No. Resi / AWB: <strong class="font-mono text-dark" style="font-size: 13px;"><?= esc($trackingNo) ?></strong></div>
      <div>Kampanye: <strong><?= esc($shipment['campaign_title'] ?? '-') ?></strong></div>
      <div>Status Terkini: <strong><?= esc($status) ?></strong></div>
      <div>Biaya Ongkir: <span class="font-mono">Rp <?= number_format((float)($shipment['shipping_fee'] ?? 0), 0, ',', '.') ?></span></div>
    </div>
  </div>

  <div style="font-size: 12px; font-weight: bold; margin-bottom: 6px; color: #172b4d;">
    Daftar Barang Sampel yang Diserahkan:
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 30px;" class="text-center">#</th>
        <th style="width: 120px;">Kode SKU</th>
        <th>Nama Produk Sampel &amp; Varian</th>
        <th style="width: 90px;" class="text-center">Qty (Pcs)</th>
        <th style="width: 110px;" class="text-center">Kondisi Fisik</th>
        <th style="width: 120px;" class="text-center">Checklist QC</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($shipment['items'])): ?>
        <?php 
          $totalQty = 0;
          foreach ($shipment['items'] as $idx => $it): 
            $qty = (int)$it['qty'];
            $totalQty += $qty;
        ?>
          <tr>
            <td class="text-center"><?= $idx + 1 ?></td>
            <td class="font-mono"><strong><?= esc($it['product_sku'] ?? '-') ?></strong></td>
            <td>
              <strong><?= esc($it['product_name']) ?></strong>
              <?php if (!empty($it['variant_name'])): ?>
                <span style="color: #0052cc;">(<?= esc($it['variant_name']) ?>)</span>
              <?php endif; ?>
            </td>
            <td class="text-center font-mono"><strong><?= $qty ?></strong></td>
            <td class="text-center" style="font-size: 10px; color: #006644;">[ &radic; ] Baik / Segel</td>
            <td class="text-center" style="font-size: 10px;">[ &nbsp; ] Lolos QC</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" class="text-center" style="padding: 15px; color: #6b778c;">
            Tidak ada rincian item sampel spesifik tercatat.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr style="background: #f4f5f7; font-weight: bold;">
        <td colspan="3" class="text-end">TOTAL UNIT BARANG SAMPEL:</td>
        <td class="text-center font-mono"><?= $totalQty ?? 0 ?> Pcs</td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
  </table>

  <?php if (!empty($shipment['notes'])): ?>
    <div style="background: #fff8e6; border: 1px solid #ffe399; padding: 8px 12px; border-radius: 4px; margin-bottom: 20px; font-size: 11px;">
      <strong>Catatan Khusus Pengiriman:</strong> <?= nl2br(esc($shipment['notes'])) ?>
    </div>
  <?php endif; ?>

  <div class="signature-container">
    <div class="sig-col">
      <div>Disiapkan Oleh:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Staff Logistik / Gudang</div>
    </div>
    <div class="sig-col">
      <div>Diserahkan ke Kurir:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Driver / Ekspedisi <?= esc($courier) ?></div>
    </div>
    <div class="sig-col">
      <div>Diterima Oleh:</div>
      <div class="sig-space">( .................................... )</div>
      <div style="color: #6b778c; margin-top: 3px;">Penerima / Talent KOL</div>
    </div>
  </div>

</body>
</html>
