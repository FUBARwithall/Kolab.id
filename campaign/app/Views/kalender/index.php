<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="far fa-calendar-alt text-primary me-2"></i>Kalender Timeline Produksi</h3>
        <p class="text-muted small mb-0">Visualisasi jadwal produksi konten, deadline video, dan tanggal penayangan live seluruh kampanye KOL.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Kalender</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          
          <!-- Filters -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <select class="form-select form-select-sm rounded-2" id="calCampaign" style="min-width: 200px;" onchange="reloadCalendar()">
              <option value="">Semua Kampanye</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="calKol" style="min-width: 170px;" onchange="reloadCalendar()">
              <option value="">Semua Talent KOL</option>
              <?php foreach ($allKols as $k): ?>
                <option value="<?= $k['id'] ?>"><?= esc($k['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm rounded-2" id="calStatus" style="min-width: 150px;" onchange="reloadCalendar()">
              <option value="">Semua Status</option>
              <option value="POSTED">Tayang Live</option>
              <option value="DRAFT_SUBMITTED">Review Draft</option>
              <option value="BRIEF_APPROVED">Brief Approved</option>
              <option value="DRAFT">Draft / SOW</option>
            </select>
          </div>

          <!-- Color Legend -->
          <div class="d-flex flex-wrap align-items-center gap-2 small">
            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Live/Posted</span>
            <span class="badge bg-warning text-dark"><i class="fas fa-video me-1"></i>Review Draft</span>
            <span class="badge bg-info text-dark"><i class="fas fa-file-alt me-1"></i>Brief Approved</span>
            <span class="badge bg-secondary"><i class="fas fa-file me-1"></i>Draft SOW</span>
          </div>

        </div>
      </div>

      <div class="card-body p-4">
        <div id="calendar"></div>
      </div>
    </div>

  </div>
</div>

<!-- Modal Detail Event Kalender -->
<div class="modal fade" id="modalCalEvent" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title fw-bold" id="calModalTitle"><i class="far fa-calendar-check text-primary me-2"></i>Detail Jadwal Tugas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="calModalBody">
        <!-- Event detail filled dynamically -->
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<!-- Include FullCalendar v6 Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
let calendar = null;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        locale: 'id',
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            list: 'Daftar'
        },
        events: function(info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                campaign_id: document.getElementById('calCampaign').value,
                kol_id: document.getElementById('calKol').value,
                status: document.getElementById('calStatus').value,
            });

            fetch('<?= base_url('kalender/get-events') ?>?' + params.toString())
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            const props = info.event.extendedProps;
            const taskId = props.taskId;

            let liveLink = '';
            if (props.liveUrl) {
                liveLink = `<div class="mt-2"><a href="${props.liveUrl}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3"><i class="fas fa-play me-1"></i>Buka Video Live</a></div>`;
            }

            document.getElementById('calModalBody').html = '';
            $('#calModalBody').html(`
                <h5 class="fw-bold text-dark mb-1">${info.event.title}</h5>
                <div class="text-muted small mb-3">Kampanye: <strong>${props.campaignTitle}</strong> &bull; KOL: <strong>${props.kolName}</strong></div>
                <div class="mb-3">
                    <label class="text-muted small">Target Tanggal Posting</label>
                    <div class="fw-bold fs-6 text-primary"><i class="far fa-calendar-alt me-1"></i>${info.event.startStr}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Status Produksi</label>
                    <div><span class="badge bg-primary">${props.status}</span></div>
                </div>
                ${liveLink}
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= base_url('tasks') ?>?campaign_id=${props.campaignId || ''}" class="btn btn-outline-primary btn-sm rounded-2"><i class="fas fa-tasks me-1"></i>Buka di Tasks Engine</a>
                </div>
            `);
            $('#modalCalEvent').modal('show');
        }
    });

    calendar.render();
});

function reloadCalendar() {
    if (calendar) {
        calendar.refetchEvents();
    }
}
</script>
<?= $this->endSection() ?>
