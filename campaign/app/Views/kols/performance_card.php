<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
/* Print & PDF Export Styling */
@media print {
    .main-header, .main-sidebar, .content-header, .main-footer, .breadcrumb, .pmc-actions, .alert-info, .btn {
        display: none !important;
    }
    .content-wrapper, .wrapper, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
    }
    .performance-master-card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
    }
    .pmc-header {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .card {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }
}

/* Master Performance Card Styles */
.performance-master-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    overflow: hidden;
    margin-bottom: 24px;
}

.pmc-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    padding: 24px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.pmc-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.2);
    background: #334155;
    color: #ffffff;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pmc-title {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pmc-meta {
    font-size: 13px;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 6px;
}

.pmc-badge-tier {
    background: #f59e0b;
    color: #0f172a;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pmc-badge-cat {
    background: rgba(255, 255, 255, 0.12);
    color: #f1f5f9;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
}

.pmc-stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.pmc-stat-label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.pmc-stat-val {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

.pmc-channel-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.pmc-channel-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
</style>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark fs-4 fw-bold">
                <i class="fas fa-id-card-alt text-primary me-2"></i> KOL Performance Card
            </h1>
            <p class="text-muted text-xs mb-0">Executive talent scorecard, verifikasi metrik platform, dan estimasi jangkauan kampanye</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('kols') ?>">Direktori KOL</a></li>
                <li class="breadcrumb-item active">Performance Card</li>
            </ol>
        </div>
    </div>

    <?php
    $kolName = $kol['name'];
    $initials = strtoupper(substr($kolName, 0, 1));
    $parts = explode(' ', trim($kolName));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr($parts[1], 0, 1));
    }
    $tierName = $kol['tier']['name'] ?? 'Nano';
    ?>

    <!-- MASTER PERFORMANCE CARD -->
    <div class="performance-master-card">
        <!-- Header Navy -->
        <div class="pmc-header">
            <div class="d-flex align-items-center gap-3">
                <div class="pmc-avatar">
                    <?= esc($initials) ?>
                </div>
                <div>
                    <h2 class="pmc-title">
                        <?= esc($kolName) ?>
                        <span class="pmc-badge-tier">Tier <?= esc($tierName) ?></span>
                    </h2>
                    <div class="pmc-meta">
                        <span><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= esc($kol['city'] ?: 'Indonesia') ?></span>
                        <span>&bull;</span>
                        <span><i class="fab fa-whatsapp me-1 text-success"></i> <?= esc($kol['whatsapp'] ?: '-') ?></span>
                        <span>&bull;</span>
                        <?php if (!empty($kol['categories'])): ?>
                            <?php foreach ($kol['categories'] as $cat): ?>
                                <span class="pmc-badge-cat"><?= esc($cat['name']) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="d-flex gap-2 pmc-actions">
                <a href="<?= base_url('kols') ?>" class="btn btn-sm btn-outline-light rounded-pill px-3 text-xs">
                    <i class="fas fa-arrow-left me-1"></i> Direktori
                </a>
                <a href="<?= base_url('kols/view/' . $kol['id']) ?>" class="btn btn-sm btn-outline-light rounded-pill px-3 text-xs">
                    <i class="fas fa-eye me-1"></i> Detail Profil
                </a>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm text-xs" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Cetak / Ekspor PDF
                </button>
            </div>
        </div>

        <div class="p-4 bg-light-subtle">
            <!-- 4 Executive Summary Metrics -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="pmc-stat-box">
                        <span class="pmc-stat-label"><i class="fas fa-users text-primary me-1"></i> Total Reach Audiens</span>
                        <div class="pmc-stat-val text-dark">
                            <?= number_format($kol['total_followers']) ?>
                        </div>
                        <small class="text-muted text-xs mt-auto pt-2">
                            Kombinasi <?= count($kol['platforms']) ?> platform aktif
                        </small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="pmc-stat-box">
                        <span class="pmc-stat-label"><i class="fas fa-chart-line text-success me-1"></i> Engagement Rate (ER)</span>
                        <div class="pmc-stat-val text-success">
                            <?= number_format($kol['avg_er'] * 100, 2) ?>%
                        </div>
                        <small class="text-muted text-xs mt-auto pt-2">
                            <?php if ($kol['avg_er'] * 100 >= 5): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">High Performance</span>
                            <?php elseif ($kol['avg_er'] * 100 >= 2): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-xs">Healthy Engagement</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">Normal</span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="pmc-stat-box">
                        <span class="pmc-stat-label"><i class="fas fa-play-circle text-danger me-1"></i> Rata-rata Views</span>
                        <div class="pmc-stat-val text-dark">
                            <?= number_format($kol['avg_views']) ?>
                        </div>
                        <small class="text-muted text-xs mt-auto pt-2">
                            Per video konten postingan
                        </small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="pmc-stat-box">
                        <span class="pmc-stat-label" style="color: #0284c7;"><i class="fas fa-shopping-bag text-info me-1"></i> Total Est. GMV Impact</span>
                        <div class="pmc-stat-val" style="color: #0284c7;">
                            <span class="fs-6 fw-normal text-muted me-1">Rp</span><?= number_format($kol['total_gmv_estimate'], 0, ',', '.') ?>
                        </div>
                        <small class="text-muted text-xs mt-auto pt-2">
                            <span class="badge bg-dark text-white text-xs">TikTok Shop &amp; Affiliate</span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Platform Channels Breakdown Grid -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-share-alt text-primary me-2"></i> Akun Channel Media Sosial &amp; Metrik Live
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <?php if (empty($kol['platforms'])): ?>
                            <div class="col-12 text-center py-4 text-muted text-xs">Belum ada akun terhubung.</div>
                        <?php else: ?>
                            <?php foreach ($kol['platforms'] as $p): ?>
                                <div class="col-md-4">
                                    <div class="pmc-channel-card">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="<?= esc($p['platform_icon']) ?> fs-4 text-primary"></i>
                                                <div>
                                                    <div class="fw-bold text-dark text-sm"><?= esc($p['platform_name']) ?></div>
                                                    <div class="text-muted text-xs">@<?= esc($p['username']) ?></div>
                                                </div>
                                            </div>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle text-xs">
                                                Active
                                            </span>
                                        </div>
                                        <div class="row g-2 text-center py-2">
                                            <div class="col-4 border-end">
                                                <div class="text-muted text-xs">Followers</div>
                                                <div class="fw-bold text-dark fs-6"><?= number_format($p['followers_count']) ?></div>
                                            </div>
                                            <div class="col-4 border-end">
                                                <div class="text-muted text-xs">Avg Views</div>
                                                <div class="fw-bold text-dark fs-6"><?= number_format($p['avg_views']) ?></div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted text-xs">Avg ER</div>
                                                <div class="fw-bold text-success fs-6"><?= number_format($p['avg_er'] * 100, 2) ?>%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Historical Performance Trend Chart -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                        <i class="fas fa-chart-area text-success me-2"></i> Tren Pertumbuhan Metrik &amp; Views (6 Bulan Terakhir)
                    </h5>
                    <span class="badge bg-light text-muted border text-xs">
                        <i class="fas fa-sync-alt me-1"></i> Data Snapshot Historis
                    </span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 320px; width: 100%;">
                        <canvas id="chart-performance-trend"></canvas>
                    </div>
                </div>
            </div>

            <!-- Rate Card Matrix & Operational Quality Matrix -->
            <div class="row g-3">
                <!-- Commercial Rate Card Matrix -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                                <i class="fas fa-file-invoice-dollar text-success me-2"></i> Matriks Rate Card Komersial
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Format Konten</th>
                                            <th>Platform</th>
                                            <th class="text-end">Tarif Publikasi</th>
                                            <th class="text-center">Nego</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($kol['rate_cards'])): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted text-xs">Belum ada rate card terdaftar.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($kol['rate_cards'] as $rc): ?>
                                                <tr>
                                                    <td class="fw-semibold text-dark text-xs"><?= esc($rc['content_type']) ?></td>
                                                    <td>
                                                        <i class="<?= esc($rc['platform_icon']) ?> text-primary me-1"></i>
                                                        <span class="text-xs"><?= esc($rc['platform_name']) ?></span>
                                                    </td>
                                                    <td class="text-end fw-bold text-success text-sm">
                                                        Rp <?= number_format($rc['rate_price'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($rc['negotiable'])): ?>
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle text-xs">Ya</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-xs">Pas</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operational Quality Scorecard -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                                <i class="fas fa-award text-warning me-2"></i> Operational Deliverable Scorecard
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                                    <span class="fw-semibold text-dark">Ketepatan Waktu Draft (TAT On-Time)</span>
                                    <strong class="text-success">96%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 96%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                                    <span class="fw-semibold text-dark">Revisi Rate Konten (First-Pass Approval)</span>
                                    <strong class="text-primary">88%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 88%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                                    <span class="fw-semibold text-dark">Sentimen Positif Audiens (IndoBERT)</span>
                                    <strong class="text-info">92.4%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 92.4%"></div>
                                </div>
                            </div>
                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark text-sm">Rekomendasi Kampanye</div>
                                        <small class="text-muted text-xs">Berdasarkan historical campaign performance</small>
                                    </div>
                                    <span class="badge bg-success px-3 py-1.5 text-xs">
                                        <i class="fas fa-thumbs-up me-1"></i> Highly Recommended
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    var chartData = <?= json_encode($kol['chart']) ?>;
    var ctx = document.getElementById('chart-performance-trend');

    if (ctx && chartData && chartData.labels && chartData.labels.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Average Views per Video',
                        data: chartData.views,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yViews',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#2563eb',
                        pointRadius: 4
                    },
                    {
                        label: 'Engagement Rate (ER %)',
                        data: chartData.er,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.35,
                        yAxisID: 'yEr',
                        borderWidth: 2,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 12, family: 'Inter, system-ui, sans-serif' },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (context.datasetIndex === 0) {
                                    return label + ': ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                } else {
                                    return label + ': ' + context.parsed.y.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                },
                scales: {
                    yViews: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    },
                    yEr: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + '%';
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
