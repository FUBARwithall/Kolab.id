<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 30px 40px;
            font-size: 13px;
            line-height: 1.8;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0 0 6px 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .meta {
            font-size: 11px;
            color: #666;
        }
        .contract-body {
            text-align: justify;
        }
        .contract-body p {
            margin-bottom: 14px;
        }
        .no-print {
            margin-bottom: 25px;
            text-align: right;
        }
        .btn-print {
            background: #007bff;
            color: #fff;
            padding: 8px 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak Dokumen Kontrak / PDF</button>
    </div>

    <div class="header">
        <h1><?= esc($tpl['title']) ?></h1>
        <div class="meta">
            ID Template: #<?= esc($tpl['id']) ?> | Dibuat: <?= !empty($tpl['created_at']) ? date('d F Y', strtotime($tpl['created_at'])) : '-' ?> | Dokumen Resmi Kolab.id
        </div>
    </div>

    <div class="contract-body">
        <?= $sampleRendered ?>
    </div>
</body>
</html>
