<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: #222;
            margin: 40px auto;
            max-width: 800px;
            font-size: 15px;
            line-height: 1.8;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 26px;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }
        .meta {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        .excerpt {
            font-style: italic;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 12px 16px;
            margin: 20px 0;
            color: #555;
        }
        .content {
            margin-top: 20px;
        }
        .content img {
            max-width: 100%;
            height: auto;
        }
        .no-print {
            text-align: right;
            margin-bottom: 20px;
        }
        .btn-print {
            font-family: Arial, sans-serif;
            background: #007bff;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 20px; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak Dokumen / Simpan PDF</button>
    </div>

    <div class="header">
        <div class="meta">
            Kolab.id &bull; Kategori: <?= esc($article->category ?: 'Umum') ?> &bull; Status: <?= esc($article->status) ?>
        </div>
        <h1><?= esc($article->title) ?></h1>
        <div class="meta">
            Penulis: <?= esc($article->author_name ?: 'Admin') ?> &bull; 
            Terbit: <?= !empty($article->published_at) ? date('d F Y H:i', strtotime($article->published_at)) : 'Draft' ?> &bull;
            Views: <?= (int) $article->views ?>
        </div>
    </div>

    <?php if (!empty($article->excerpt)): ?>
        <div class="excerpt">
            <?= esc($article->excerpt) ?>
        </div>
    <?php endif; ?>

    <div class="content">
        <?= $article->content ?>
    </div>

    <?php if (!empty($article->tags)): ?>
        <div style="margin-top: 40px; padding-top: 15px; border-top: 1px solid #ddd; font-family: Arial, sans-serif; font-size: 12px; color: #666;">
            Tagar: <?= esc($article->tags) ?>
        </div>
    <?php endif; ?>
</body>
</html>
