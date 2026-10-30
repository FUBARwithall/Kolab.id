<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Breadcrumb & Header -->
    <div class="row mb-3">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark fs-4 fw-bold">
                <i class="fas fa-trophy text-warning me-2"></i> Leaderboard &amp; Performa KOL
            </h1>
            <p class="text-muted text-xs mb-0">Pemeringkatan kinerja KOL berdasarkan total tayangan (views), efisiensi CPV, dan ulasan kampanye</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end text-xs">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item active">Leaderboard</li>
            </ol>
        </div>
    </div>

    <!-- Top KOL Banner & Stats Grid -->
    <div class="row g-3 mb-4">
        <!-- Top 1 KOL Spotlight Card -->
        <?php if ($top_kol): ?>
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-3 bg-gradient text-white h-100 p-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-warning text-dark fw-bold text-xs px-2.5 py-1">
                            <i class="fas fa-crown text-dark me-1"></i> RANK #1 TALENT
                        </span>
                        <span class="badge bg-light bg-opacity-10 text-light text-xs">Top Performer</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-3 shadow" style="width: 58px; height: 58px;">
                            <?= strtoupper(substr($top_kol['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="fw-bold text-white mb-0 fs-5"><?= esc($top_kol['name']) ?></h4>
                            <div class="text-white-50 text-xs">
                                <i class="fas fa-map-marker-alt me-1"></i> <?= esc($top_kol['city']) ?> &bull; Tier <?= esc($top_kol['tier_name']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 text-center mt-auto pt-2 border-top border-secondary">
                        <div class="col-4 border-end border-secondary">
                            <span class="text-white-50 text-xs d-block">Total Views</span>
                            <strong class="text-warning fs-6"><?= number_format($top_kol['total_views']) ?></strong>
                        </div>
                        <div class="col-4 border-end border-secondary">
                            <span class="text-white-50 text-xs d-block">Completed VT</span>
                            <strong class="text-white fs-6"><?= (int)$top_kol['completed_tasks'] ?> VT</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-white-50 text-xs d-block">Avg. ER</span>
                            <strong class="text-success fs-6"><?= number_format($top_kol['avg_er'] * 100, 2) ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tier Distribution Summary -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-4">
                <h5 class="fw-bold text-dark fs-6 mb-3">
                    <i class="fas fa-chart-pie text-primary me-2"></i> Distribusi Tier KOL Terdata
                </h5>
                <div class="row g-2 text-center mb-3">
                    <?php 
                    $tierColors = [
                        'Mega'  => 'bg-danger-subtle text-danger border-danger-subtle',
                        'Macro' => 'bg-warning-subtle text-warning border-warning-subtle',
                        'Mid'   => 'bg-primary-subtle text-primary border-primary-subtle',
                        'Micro' => 'bg-info-subtle text-info border-info-subtle',
                        'Nano'  => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                    ];
                    foreach ($tier_counts as $tier => $cnt): ?>
                        <div class="col">
                            <div class="p-2.5 rounded-3 border <?= $tierColors[$tier] ?? 'bg-light' ?>">
                                <div class="text-xs fw-semibold text-uppercase"><?= $tier ?></div>
                                <div class="fs-5 fw-bold"><?= $cnt ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top text-xs text-muted">
                    <span>Gunakan fitur komparasi untuk mengevaluasi efisiensi harga per tayangan (CPV).</span>
                    <button type="button" id="btn-trigger-compare" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" disabled>
                        <i class="fas fa-columns me-1"></i> Bandingkan Terpilih (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark fs-6">
                <i class="fas fa-list-ol text-primary me-2"></i> Peringkat Kinerja Kampanye KOL
            </h5>
            <small class="text-muted text-xs">Pilih 2 hingga 5 KOL untuk dibandingkan berdampingan</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="table-leaderboard">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 40px;">
                                <i class="far fa-check-square"></i>
                            </th>
                            <th style="width: 50px;">Rank</th>
                            <th>Talent &amp; Domisili</th>
                            <th>Tier</th>
                            <th>Completed VT</th>
                            <th>Total Tayangan (Views)</th>
                            <th>Avg. ER%</th>
                            <th>Efisiensi CPV</th>
                            <th>Rating Review</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaderboard)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted text-xs">Belum ada data metrik leaderboard.</td>
                            </tr>
                        <?php else: ?>
                            <?php $rank = 1; foreach ($leaderboard as $k): ?>
                                <tr>
                                    <td class="ps-3">
                                        <input type="checkbox" class="form-check-input kol-compare-cb" value="<?= $k['id'] ?>" style="cursor: pointer;">
                                    </td>
                                    <td>
                                        <?php if ($rank === 1): ?>
                                            <span class="badge bg-warning text-dark fw-bold rounded-circle p-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                                        <?php elseif ($rank === 2): ?>
                                            <span class="badge bg-secondary text-white fw-bold rounded-circle p-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                                        <?php elseif ($rank === 3): ?>
                                            <span class="badge bg-amber text-dark fw-bold rounded-circle p-2" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; background: #cd7f32; color: #fff;">3</span>
                                        <?php else: ?>
                                            <span class="fw-bold text-muted ps-1">#<?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-xs"><?= esc($k['name']) ?></div>
                                        <small class="text-muted text-xs"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($k['city'] ?: 'Indonesia') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-xs"><?= esc($k['tier_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-xs">
                                            <?= (int)$k['completed_tasks'] ?> VT
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark text-xs">
                                        <?= number_format($k['total_views']) ?>
                                    </td>
                                    <td class="fw-bold text-success text-xs">
                                        <?= number_format($k['avg_er'] * 100, 2) ?>%
                                    </td>
                                    <td class="fw-semibold text-xs">
                                        <?= $k['cpv'] > 0 ? 'Rp ' . number_format($k['cpv'], 0, ',', '.') : '-' ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($k['avg_rating'])): ?>
                                            <span class="text-warning text-xs fw-bold"><i class="fas fa-star me-1"></i><?= $k['avg_rating'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="<?= base_url('kols/view/' . $k['id']) ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('kols/performance-card/' . $k['id']) ?>" class="btn btn-xs btn-dark rounded-pill px-2.5 py-1 text-xs" title="Performance Card">
                                                <i class="fas fa-id-card-alt text-warning"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php $rank++; endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Side-by-Side Comparison -->
<div class="modal fade" id="modal-compare-kol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark fs-6">
                    <i class="fas fa-columns text-primary me-2"></i> Komparasi Performa KOL (Side-by-Side)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="compare-modal-body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    <span class="ms-2 text-xs text-muted">Memuat komparasi metrik...</span>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script>
$(document).ready(function() {
    function updateCompareButton() {
        var checked = $('.kol-compare-cb:checked').length;
        $('#selected-count').text(checked);
        if (checked >= 2 && checked <= 5) {
            $('#btn-trigger-compare').prop('disabled', false);
        } else {
            $('#btn-trigger-compare').prop('disabled', true);
        }
    }

    $('.kol-compare-cb').on('change', function() {
        var checked = $('.kol-compare-cb:checked').length;
        if (checked > 5) {
            $(this).prop('checked', false);
            alert('Maksimal 5 KOL dapat dibandingkan sekaligus.');
        }
        updateCompareButton();
    });

    $('#btn-trigger-compare').on('click', function() {
        var selectedIds = [];
        $('.kol-compare-cb:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length < 2) {
            alert('Pilih minimal 2 KOL untuk dibandingkan.');
            return;
        }

        $('#modal-compare-kol').modal('show');
        $('#compare-modal-body').html('<div class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div><span class="ms-2 text-xs text-muted">Memuat komparasi metrik...</span></div>');

        $.ajax({
            url: '<?= base_url('dashboard/compare-kol') ?>',
            type: 'POST',
            data: { kol_ids: selectedIds },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#compare-modal-body').html(resp.html);
                } else {
                    $('#compare-modal-body').html('<div class="alert alert-danger text-xs mb-0">' + (resp.message || 'Terjadi kesalahan') + '</div>');
                }
            },
            error: function() {
                $('#compare-modal-body').html('<div class="alert alert-danger text-xs mb-0">Gagal memuat data komparasi server.</div>');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
