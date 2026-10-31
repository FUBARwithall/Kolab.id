<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>
<style>
  /* Modern SaaS Calendar & Event Card Styling */
  .cal-filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    overflow: hidden;
  }
  .cal-filter-toggle-header {
    cursor: pointer;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
    background: #ffffff;
  }
  .cal-filter-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 6px;
    display: block;
  }
  .cal-filter-card .form-control, .cal-filter-card .form-select {
    border-radius: 6px;
    border-color: #cbd5e1;
    font-size: 13px;
    box-shadow: none;
    height: 38px;
  }
  .cal-legend-bar {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
  }
  .cal-legend-group {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
  }
  .cal-legend-label {
    font-weight: 600;
    color: #475569;
    font-size: 11.5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-right: 4px;
  }
  .cal-legend-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    border: 1px solid transparent;
  }
  .cal-legend-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
  }

  /* Main Calendar Card */
  .cal-main-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    margin-bottom: 30px;
  }

  /* FullCalendar Toolbar Customization */
  .fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
  }
  .fc .fc-button-primary {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #334155 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    padding: 6px 14px !important;
    border-radius: 6px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
  }
  .fc .fc-button-primary:hover {
    background-color: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
  }
  .fc .fc-button-primary:not(:disabled).fc-button-active,
  .fc .fc-button-primary:not(:disabled):active {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
  }

  /* Event Custom Rendering */
  .fc-task-card {
    padding: 4px 6px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #1e293b;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    cursor: pointer;
    overflow: hidden;
    margin-bottom: 2px;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
  }
  .fc-task-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
  }
  .fc-task-card-posted {
    border-left: 4px solid #16a34a !important;
    background: #f0fdf4 !important;
  }
  .fc-task-card-overdue {
    border-left: 4px solid #dc2626 !important;
    background: #fef2f2 !important;
  }
  .fc-task-card-today {
    border-left: 4px solid #f59e0b !important;
    background: #fffbeb !important;
  }
  .fc-task-card-urgent {
    border-left: 4px solid #ea580c !important;
    background: #fff7ed !important;
  }
  .fc-task-card-pending {
    border-left: 4px solid #0284c7 !important;
    background: #f0f9ff !important;
  }

  .deadline-pill {
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
    display: inline-block;
  }

  .fc-event-campaign {
    border-radius: 4px !important;
    padding: 2px 6px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
  }
</style>

<div class="app-content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h3 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-alt text-primary me-2"></i>Kalender Jadwal Posting VT</h3>
        <p class="text-muted small mb-0">Visualisasi Timeline Campaign &amp; Jadwal Penayangan VT KOL secara terpadu.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('tasks?view=sow') ?>" class="text-decoration-none">Tasks SOW</a></li>
          <li class="breadcrumb-item active" aria-current="page">Kalender</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="app-content">
  <div class="container-fluid">

    <!-- 1. FILTER & QUICK LEGEND BAR -->
    <div class="cal-filter-card">
      <div class="cal-filter-toggle-header" data-bs-toggle="collapse" data-bs-target="#calFilterCollapse" aria-expanded="true">
        <div class="d-flex align-items-center gap-2">
          <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-2" style="width: 32px; height: 32px;">
            <i class="fas fa-filter"></i>
          </span>
          <div>
            <strong class="text-dark" style="font-size: 14px;">Filter &amp; Legenda Kalender</strong>
            <span class="badge bg-primary ms-2 d-none" id="calFilterActiveBadge">Filter Aktif</span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 text-muted small">
          <span class="cal-toggle-text fw-semibold">Tutup / Buka Filter</span>
          <i class="fas fa-chevron-down cal-toggle-icon"></i>
        </div>
      </div>

      <div class="collapse show border-top p-3" id="calFilterCollapse">
        <div class="row g-2 align-items-end">
          <div class="col-md-3 col-sm-6">
            <span class="cal-filter-title"><i class="fas fa-bullhorn me-1"></i> Filter Campaign</span>
            <select id="filter_campaign" class="form-select form-select-sm" onchange="reloadCalendar()">
              <option value="">-- Semua Campaign --</option>
              <?php foreach ($allCampaigns as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 col-sm-6">
            <span class="cal-filter-title"><i class="fas fa-user-circle me-1"></i> Filter Talent KOL</span>
            <select id="filter_kol" class="form-select form-select-sm" onchange="reloadCalendar()">
              <option value="">-- Semua KOL --</option>
              <?php foreach ($allKols as $k): ?>
                <option value="<?= $k['id'] ?>"><?= esc($k['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 col-sm-6">
            <span class="cal-filter-title"><i class="fas fa-share-alt me-1"></i> Filter Platform</span>
            <select id="filter_platform" class="form-select form-select-sm" onchange="reloadCalendar()">
              <option value="">-- Semua Platform --</option>
              <?php foreach ($platforms as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2 col-sm-4">
            <span class="cal-filter-title"><i class="fas fa-tasks me-1"></i> Status Task</span>
            <select id="filter_status" class="form-select form-select-sm" onchange="reloadCalendar()">
              <option value="">-- Semua Status --</option>
              <option value="NOT_POSTED">Belum Upload Video Live</option>
              <option value="POSTED_LIVE">Sudah Post Video Live</option>
            </select>
          </div>
          <div class="col-md-1 col-sm-2">
            <button type="button" id="btn_reset_filter" class="btn btn-outline-secondary btn-sm w-100" title="Reset Semua Filter" onclick="resetCalendarFilter()" style="height: 38px;">
              <i class="fas fa-undo"></i>
            </button>
          </div>
        </div>

        <!-- Legend Bar -->
        <div class="cal-legend-bar">
          <div class="cal-legend-group">
            <span class="cal-legend-label"><i class="fas fa-tag text-muted"></i> Status Task:</span>
            <span class="cal-legend-pill" style="background: #f0fdf4; color: #166534; border-color: #bbf7d0;">
              <span class="cal-legend-dot" style="background: #16a34a;"></span> Sudah Post Live
            </span>
            <span class="cal-legend-pill" style="background: #f0f9ff; color: #0369a1; border-color: #bae6fd;">
              <span class="cal-legend-dot" style="background: #0284c7;"></span> Belum Upload Live
            </span>
          </div>

          <div class="cal-legend-group">
            <span class="cal-legend-label"><i class="far fa-clock text-muted"></i> Deadline Urgency:</span>
            <span class="cal-legend-pill" style="background: #fef2f2; color: #991b1b; border-color: #fecaca;">Lewat Deadline</span>
            <span class="cal-legend-pill" style="background: #fffbeb; color: #92400e; border-color: #fde68a;">Hari Ini</span>
            <span class="cal-legend-pill" style="background: #fff7ed; color: #9a3412; border-color: #fed7aa;">H-1 s/d H-3</span>
          </div>

          <div class="cal-legend-group">
            <span class="cal-legend-label"><i class="fas fa-calendar text-muted"></i> Periode:</span>
            <span class="cal-legend-pill" style="background: #f5f3ff; color: #5b21b6; border-color: #ddd6fe;">
              <i class="fas fa-bullhorn me-1" style="font-size: 10px;"></i> Rentang Campaign
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. MAIN CALENDAR CARD -->
    <div class="cal-main-card">
      <div id="calendar"></div>
    </div>

  </div>
</div>

<!-- Modal Detail Jadwal (Ringkas, Bersih & Lengkap) -->
<div class="modal fade" id="modalEventDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
    <div class="modal-content border-0 shadow">
      
      <div class="modal-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h5 class="modal-title fw-bold mb-0 text-dark" id="modalEventTitle">Detail Jadwal VT</h5>
          <span id="modalStatusBadge" class="cal-legend-pill" style="font-size: 10px; padding: 2px 8px; font-weight: 700;">-</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        
        <!-- Info Ringkas: Campaign & Deadline -->
        <div class="bg-light border rounded-3 p-3 mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small fw-semibold text-uppercase"><i class="fas fa-bullhorn me-1"></i> Campaign</span>
            <div id="modalCampaign" class="fw-bold text-dark text-truncate" style="max-width: 65%; font-size: 13px;">-</div>
          </div>
          <div class="d-flex justify-content-between align-items-center border-top pt-2">
            <span class="text-muted small fw-semibold text-uppercase"><i class="far fa-calendar-alt me-1"></i> Deadline</span>
            <div id="modalPostingDate" class="fw-bold text-dark" style="font-size: 13px;">-</div>
          </div>
        </div>

        <!-- 3 Deliverable Status Chips Horizontal -->
        <div class="row g-2">
          <div class="col-4">
            <div id="modalBriefCard" class="bg-white border rounded-3 p-2 text-center h-100">
              <div class="text-muted small mb-1" style="font-size: 11px;"><i class="fas fa-file-alt"></i> Brief</div>
              <div id="modalBriefStatus" style="font-size: 12px; font-weight: 700;">-</div>
            </div>
          </div>
          <div class="col-4">
            <div id="modalDraftCard" class="bg-white border rounded-3 p-2 text-center h-100">
              <div class="text-muted small mb-1" style="font-size: 11px;"><i class="fas fa-video"></i> Draft</div>
              <div id="modalDraftStatus" style="font-size: 12px; font-weight: 700;">-</div>
            </div>
          </div>
          <div class="col-4">
            <div id="modalLiveCard" class="bg-white border rounded-3 p-2 text-center h-100">
              <div class="text-muted small mb-1" style="font-size: 11px;"><i class="fas fa-globe"></i> Live</div>
              <div id="modalLiveStatus" style="font-size: 12px; font-weight: 700;">-</div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-secondary btn-sm rounded-2" data-bs-dismiss="modal">Tutup</button>
        <a href="#" id="btnModalGoTask" class="btn btn-primary btn-sm rounded-2 fw-semibold px-3">
          Buka Detail Task <i class="fas fa-arrow-right ms-1"></i>
        </a>
      </div>

    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScript') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
let calendar = null;

function escapeHtml(str) {
    if (!str) return '';
    return $('<div>').text(str).html();
}

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
        dayMaxEvents: 4,
        events: function(info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                campaign_id: document.getElementById('filter_campaign').value,
                kol_id: document.getElementById('filter_kol').value,
                platform_id: document.getElementById('filter_platform').value,
                status: document.getElementById('filter_status').value,
            });

            fetch('<?= base_url('kalender/get-events') ?>?' + params.toString())
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventContent: function(arg) {
            const event = arg.event;
            const props = event.extendedProps;

            // 1. Campaign Multi-day Banner
            if (props.is_campaign_banner || props.isCampaignBanner) {
                const campName = props.campaign_name || props.campaignTitle || event.title;
                const html = `
                    <div class="fc-event-campaign" style="background-color: ${props.camp_bg || '#eef2ff'}; border: 1px solid ${props.camp_border || '#6366f1'}; color: ${props.camp_text || '#4338ca'};">
                        <i class="fas fa-bullhorn me-1" style="font-size: 10px;"></i>
                        <span style="font-size: 10.5px; font-weight: 700;">Periode:</span> ${escapeHtml(campName)}
                    </div>
                `;
                return { html: html };
            }

            // 2. Task / VT Event Card
            let cardClass = 'fc-task-card-pending';
            const statusUpper = (props.status_raw || props.status || '').toUpperCase();
            if (['POSTED', 'DONE', 'SUCCESS', 'COMPLETED'].includes(statusUpper)) {
                cardClass = 'fc-task-card-posted';
            } else if (props.deadline_type === 'overdue') {
                cardClass = 'fc-task-card-overdue';
            } else if (props.deadline_type === 'today') {
                cardClass = 'fc-task-card-today';
            } else if (props.deadline_type === 'urgent') {
                cardClass = 'fc-task-card-urgent';
            }

            let html = `<div class="fc-task-card ${cardClass}">`;
            
            // Header Row: VT badge & Urgency tag
            html += `<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">`;
            html += `  <span style="font-size: 9.5px; font-weight: 700; background: rgba(0,0,0,0.06); color: #334155; padding: 1px 4px; border-radius: 3px;">VT ${escapeHtml(props.task_number || props.taskNumber || '1')}</span>`;
            if (props.deadline_label) {
                html += `  <span class="deadline-pill" style="background:${props.deadline_badge_bg}; color:${props.deadline_badge_color};">${escapeHtml(props.deadline_label)}</span>`;
            }
            html += `</div>`;

            // KOL Name
            html += `<div style="font-weight: 700; font-size: 11px; line-height: 1.25; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px;">`;
            html += escapeHtml(props.kol_name || props.kolName || 'KOL');
            html += `</div>`;

            // Campaign Name
            if (props.campaign_name || props.campaignTitle) {
                html += `<div style="font-size: 9.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px;">`;
                html += escapeHtml(props.campaign_name || props.campaignTitle);
                html += `</div>`;
            }
            
            // Deliverable Indicators (Brief, Draft, Live)
            const hasBrief = Boolean(props.brief_file_url || props.briefFileUrl);
            const hasDraft = Boolean(props.draft_url || props.draftUrl);
            const hasLive  = Boolean(props.live_url || props.liveUrl);

            html += `<div style="font-size: 9.5px; display: flex; align-items: center; gap: 6px; color: #94a3b8; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 3px; margin-top: 2px;">`;
            html += `<span title="${hasBrief ? 'Brief / SOW Ready' : 'No Brief'}">${hasBrief ? '<i class="fas fa-file-alt text-success"></i>' : '<i class="fas fa-file-alt opacity-25"></i>'}</span>`;
            html += `<span title="${hasDraft ? 'Draft Video Ready' : 'No Draft'}">${hasDraft ? '<i class="fas fa-video text-success"></i>' : '<i class="fas fa-video opacity-25"></i>'}</span>`;
            html += `<span title="${hasLive ? 'Live Posted' : 'Not Live'}">${hasLive ? '<i class="fas fa-globe text-success"></i>' : '<i class="fas fa-globe opacity-25"></i>'}</span>`;
            html += `</div>`;

            html += `</div>`;
            return { html: html };
        },
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            if (props.is_campaign_banner || props.isCampaignBanner) {
                const cId = props.campaign_id || props.campaignId;
                if (cId) {
                    window.open('<?= base_url('campaigns/view/') ?>' + cId, '_blank');
                }
                return;
            }

            // Title
            $('#modalEventTitle').html('<i class="far fa-calendar-check text-primary me-2"></i>' + escapeHtml(props.kol_name || props.kolName || 'KOL') + ' &mdash; VT #' + (props.task_number || props.taskNumber || '1'));
            
            // Status badge
            const statusUpper = (props.status_raw || props.status || 'DRAFT').toUpperCase();
            let badgeBg = '#f1f5f9';
            let badgeColor = '#334155';
            if (['POSTED', 'DONE', 'SUCCESS', 'COMPLETED'].includes(statusUpper)) {
                badgeBg = '#dcfce7';
                badgeColor = '#15803d';
            } else if (['CANCELLED', 'REJECTED'].includes(statusUpper)) {
                badgeBg = '#fee2e2';
                badgeColor = '#b91c1c';
            } else {
                badgeBg = '#e0f2fe';
                badgeColor = '#0369a1';
            }

            $('#modalStatusBadge')
                .text(statusUpper)
                .css({'background-color': badgeBg, 'color': badgeColor, 'border-color': 'transparent'});

            // Campaign & Deadline info
            $('#modalCampaign').text(props.campaign_name || props.campaignTitle || '-');

            let deadlineHtml = '<i class="far fa-calendar me-1 text-muted"></i> ' + escapeHtml(event.startStr);
            if (props.deadline_label) {
                deadlineHtml += ` <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background:${props.deadline_badge_bg || '#f1f5f9'}; color:${props.deadline_badge_color || '#333'}; margin-left: 4px;">${escapeHtml(props.deadline_label)}</span>`;
            }
            $('#modalPostingDate').html(deadlineHtml);

            // 3 Deliverables (Brief, Draft, Live)
            const briefUrl = props.brief_file_url || props.briefFileUrl;
            if (briefUrl) {
                $('#modalBriefCard').css({'border-color': '#bbf7d0', 'background': '#f0fdf4'});
                $('#modalBriefStatus').html(`<a href="${briefUrl}" target="_blank" style="color: #16a34a; font-weight: 700; text-decoration: none;"><i class="fas fa-check-circle me-1"></i>Ada <i class="fas fa-external-link-alt" style="font-size: 9px;"></i></a>`);
            } else {
                $('#modalBriefCard').css({'border-color': '#e2e8f0', 'background': '#f8fafc'});
                $('#modalBriefStatus').html('<span style="color: #94a3b8; font-weight: 500;">Belum</span>');
            }

            const draftUrl = props.draft_url || props.draftUrl;
            if (draftUrl) {
                $('#modalDraftCard').css({'border-color': '#bbf7d0', 'background': '#f0fdf4'});
                $('#modalDraftStatus').html(`<a href="${draftUrl}" target="_blank" style="color: #16a34a; font-weight: 700; text-decoration: none;"><i class="fas fa-play-circle me-1"></i>Ada <i class="fas fa-external-link-alt" style="font-size: 9px;"></i></a>`);
            } else {
                $('#modalDraftCard').css({'border-color': '#e2e8f0', 'background': '#f8fafc'});
                $('#modalDraftStatus').html('<span style="color: #94a3b8; font-weight: 500;">Belum</span>');
            }

            const liveUrl = props.live_url || props.liveUrl;
            if (liveUrl) {
                $('#modalLiveCard').css({'border-color': '#bbf7d0', 'background': '#f0fdf4'});
                $('#modalLiveStatus').html(`<a href="${liveUrl}" target="_blank" style="color: #16a34a; font-weight: 700; text-decoration: none;"><i class="fas fa-globe me-1"></i>Link <i class="fas fa-external-link-alt" style="font-size: 9px;"></i></a>`);
            } else {
                $('#modalLiveCard').css({'border-color': '#e2e8f0', 'background': '#f8fafc'});
                $('#modalLiveStatus').html('<span style="color: #94a3b8; font-weight: 500;">Belum</span>');
            }

            // Button to Task
            const taskId = props.task_id || props.taskId;
            $('#btnModalGoTask').attr('href', '<?= base_url('tasks') ?>?id=' + taskId);

            $('#modalEventDetail').modal('show');
        }
    });

    calendar.render();
});

function reloadCalendar() {
    updateActiveFilterBadge();
    if (calendar) {
        calendar.refetchEvents();
    }
}

function updateActiveFilterBadge() {
    const hasFilter = $('#filter_campaign').val() || $('#filter_kol').val() || $('#filter_platform').val() || $('#filter_status').val();
    if (hasFilter) {
        $('#calFilterActiveBadge').removeClass('d-none');
    } else {
        $('#calFilterActiveBadge').addClass('d-none');
    }
}

function resetCalendarFilter() {
    $('#filter_campaign').val('');
    $('#filter_kol').val('');
    $('#filter_platform').val('');
    $('#filter_status').val('');
    reloadCalendar();
}
</script>
<?= $this->endSection() ?>
