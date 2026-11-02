<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title ?? 'Laporan Rekapitulasi Finance Hub') ?></title>
  <style>
    @page {
      size: A4 landscape;
      margin: 12mm 15mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 10px;
      color: #1e293b;
      margin: 0;
      padding: 0;
    }
    .header {
      border-bottom: 2px solid #0f172a;
      padding-bottom: 8px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .title {
      font-size: 16px;
      font-weight: bold;
      color: #0f172a;
      margin: 0 0 4px 0;
    }
    .subtitle {
      font-size: 9px;
      color: #64748b;
      margin: 0;
    }
    .meta {
      text-align: right;
      font-size: 8.5px;
      color: #64748b;
    }
    .kpi-row {
      display: flex;
      gap: 10px;
      margin-bottom: 14px;
    }
    .kpi-box {
      flex: 1;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      padding: 6px 10px;
    }
    .kpi-label {
      font-size: 8px;
      text-transform: uppercase;
      font-weight: bold;
      color: #64748b;
    }
    .kpi-value {
      font-size: 12px;
      font-weight: bold;
      color: #0f172a;
      margin-top: 2px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    th {
      background-color: #f1f5f9;
      color: #334155;
      font-weight: bold;
      font-size: 8.5px;
      text-transform: uppercase;
      border: 1px solid #cbd5e1;
      padding: 5px 6px;
      text-align: left;
    }
    td {
      border: 1px solid #e2e8f0;
      padding: 5px 6px;
      font-size: 8.5px;
      vertical-align: middle;
    }
    tr:nth-child(even) {
      background-color: #f8fafc;
    }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .font-mono { font-family: monospace; }
    .badge {
      display: inline-block;
      padding: 2px 5px;
      border-radius: 3px;
      font-size: 7.5px;
      font-weight: bold;
    }
    .badge-paid { background: #dcfce7; color: #15803d; }
    .badge-approved { background: #dbeafe; color: #1d4ed8; }
    .badge-submitted { background: #fef9c3; color: #a16207; }
    .badge-rejected { background: #fee2e2; color: #b91c1c; }
    .badge-no-inv { background: #f1f5f9; color: #64748b; }
    .footer {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 8.5px;
      color: #64748b;
    }
    .sig-box {
      text-align: center;
      width: 150px;
    }
    .sig-space {
      height: 40px;
    }
  </style>
</head>
<body>

  <div class="header">
    <div>
      <div class="title">KOLAB.ID &mdash; REKAPITULASI KEUANGAN &amp; PENCAIRAN DANA</div>
      <div class="subtitle">Laporan eksekutif integrasi SOW Kampanye, Surat Kontrak, Invoice Resmi, Potongan Pajak PPh, dan Realisasi Pembayaran.</div>
    </div>
    <div class="meta">
      Dicetak: <strong><?= esc($date ?? date('d F Y, H:i') . ' WIB') ?></strong><br>
      Total Penugasan: <strong><?= count($deals ?? []) ?></strong>
    </div>
  </div>

  <?php if (!empty($metrics)): ?>
  <div class="kpi-row">
    <div class="kpi-box">
      <div class="kpi-label">Komitmen Deal</div>
      <div class="kpi-value">Rp <?= number_format((float)($metrics['total_deal_amount'] ?? 0), 0, ',', '.') ?></div>
    </div>
    <div class="kpi-box">
      <div class="kpi-label">Total Invoiced</div>
      <div class="kpi-value" style="color: #0284c7;">Rp <?= number_format((float)($metrics['total_invoiced'] ?? 0), 0, ',', '.') ?></div>
    </div>
    <div class="kpi-box">
      <div class="kpi-label">Potongan Pajak PPh</div>
      <div class="kpi-value" style="color: #64748b;">Rp <?= number_format((float)($metrics['total_tax'] ?? 0), 0, ',', '.') ?></div>
    </div>
    <div class="kpi-box">
      <div class="kpi-label">Realisasi Cair</div>
      <div class="kpi-value" style="color: #16a34a;">Rp <?= number_format((float)($metrics['total_paid'] ?? 0), 0, ',', '.') ?></div>
    </div>
    <div class="kpi-box">
      <div class="kpi-label">Sisa Outstanding</div>
      <div class="kpi-value" style="color: #d97706;">Rp <?= number_format((float)($metrics['outstanding'] ?? 0), 0, ',', '.') ?></div>
    </div>
  </div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th style="width: 25px;" class="text-center">#</th>
        <th style="width: 140px;">Kampanye &amp; Brand</th>
        <th style="width: 130px;">Talent KOL</th>
        <th style="width: 120px;">Rekening Bank Tujuan</th>
        <th style="width: 85px;">No. Kontrak</th>
        <th style="width: 90px;">No. Invoice</th>
        <th style="width: 75px;" class="text-end">Nilai Deal</th>
        <th style="width: 75px;" class="text-end">Pajak PPh</th>
        <th style="width: 80px;" class="text-end">Net Tagihan</th>
        <th style="width: 80px;" class="text-end">Terbayar</th>
        <th style="width: 75px;" class="text-center">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($deals)): ?>
        <?php $no = 1; foreach ($deals as $d): ?>
          <?php
            $st = strtoupper($d['invoice_status'] ?? '');
            $badgeCls = match ($st) {
              'PAID'      => 'badge-paid',
              'APPROVED', 'VERIFIED' => 'badge-approved',
              'SUBMITTED' => 'badge-submitted',
              'REJECTED'  => 'badge-rejected',
              default     => 'badge-no-inv',
            };
            $dealAmt = (float)($d['deal_amount'] ?? 0);
            $taxAmt  = (float)($d['tax_amount'] ?? 0);
            $netAmt  = (float)($d['grand_total'] ?? 0);
            $paidAmt = (float)($d['total_paid'] ?? 0);
          ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td>
              <div class="fw-bold"><?= esc($d['campaign_name'] ?: 'Kampanye') ?></div>
              <small style="color: #64748b;"><?= esc($d['brand_name'] ?: '-') ?></small>
            </td>
            <td>
              <div class="fw-bold"><?= esc($d['kol_name']) ?></div>
              <small style="color: #64748b;">NPWP: <?= esc($d['kol_npwp_number'] ?: 'Non-NPWP') ?></small>
            </td>
            <td>
              <div><?= esc($d['kol_bank_name'] ?: '-') ?></div>
              <div class="font-mono"><?= esc($d['kol_bank_account_number'] ?: '-') ?></div>
              <small style="color: #64748b;"><?= esc($d['kol_bank_account_name'] ?: '-') ?></small>
            </td>
            <td class="font-mono"><?= esc($d['contract_no'] ?: '-') ?></td>
            <td class="font-mono"><?= esc($d['invoice_no'] ?: '-') ?></td>
            <td class="text-end">Rp <?= number_format($dealAmt, 0, ',', '.') ?></td>
            <td class="text-end" style="color: #e11d48;">Rp <?= number_format($taxAmt, 0, ',', '.') ?></td>
            <td class="text-end fw-bold">Rp <?= number_format($netAmt, 0, ',', '.') ?></td>
            <td class="text-end" style="color: #16a34a; font-weight: bold;">Rp <?= number_format($paidAmt, 0, ',', '.') ?></td>
            <td class="text-center">
              <span class="badge <?= $badgeCls ?>"><?= $st ?: 'NO_INVOICE' ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="11" class="text-center" style="padding: 15px; color: #94a3b8;">Belum ada data keuangan yang tercatat.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">
    <div>
      Dicetak secara otomatis dari <strong>Kolab.id Enterprise Management System</strong>
    </div>
    <div class="sig-box">
      <div>Disetujui oleh,</div>
      <div class="sig-space"></div>
      <div style="border-bottom: 1px solid #334155; font-weight: bold;">Finance &amp; Tax Controller</div>
    </div>
  </div>

</body>
</html>
