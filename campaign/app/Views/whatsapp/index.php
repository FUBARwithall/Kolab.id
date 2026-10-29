<?= $this->extend('layout/master') ?>

<?= $this->section('content') ?>

<style>
/* WhatsApp Web Modern Styling */
.wa-container {
    height: calc(100vh - 180px);
    min-height: 600px;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.wa-sidebar {
    width: 360px;
    border-right: 1px solid #edf2f7;
    background: #ffffff;
    display: flex;
    flex-direction: column;
}

.wa-chat-area {
    background: #efeae2;
    background-image: radial-gradient(#d1d7db 1px, transparent 1px);
    background-size: 16px 16px;
    display: flex;
    flex-direction: column;
    position: relative;
}

.wa-context-area {
    width: 320px;
    border-left: 1px solid #edf2f7;
    background: #ffffff;
    overflow-y: auto;
}

.contact-item {
    transition: all 0.15s ease-in-out;
    border-bottom: 1px solid #f8fafc;
    cursor: pointer;
}
.contact-item:hover {
    background: #f1f5f9;
}
.contact-item.active {
    background: #e2e8f0;
}

/* Chat Bubbles */
.chat-messages {
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bubble {
    max-width: 75%;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13.5px;
    line-height: 1.45;
    position: relative;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    word-break: break-word;
    white-space: pre-wrap;
}

.bubble-out {
    align-self: flex-end;
    background: #d9fdd3;
    color: #111b21;
    border-top-right-radius: 2px;
}

.bubble-in {
    align-self: flex-start;
    background: #ffffff;
    color: #111b21;
    border-top-left-radius: 2px;
}

.bubble-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
    font-size: 10.5px;
    color: #667781;
}

.bubble-sender {
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 2px;
}
.bubble-out .bubble-sender { color: #008069; }
.bubble-in .bubble-sender { color: #0284c7; }
</style>

<div class="container-fluid px-0">
    <!-- Top Action Bar -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold mb-0 text-dark"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Communication Console</h4>
            <small class="text-muted">Kelola pesan langsung dengan Talent KOL, pengingat otomatis, dan broadcast notifikasi kampanye.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge <?= ($sessionStatus['online'] ?? false) ? 'bg-success' : 'bg-warning text-dark' ?> px-3 py-2 rounded-pill">
                <i class="fas fa-circle text-white me-1 fs-8"></i> <?= esc($sessionStatus['gateway'] ?? 'Gateway Standby') ?> (<?= esc($sessionStatus['status'] ?? 'CONNECTED') ?>)
            </span>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                <i class="fas fa-bullhorn me-1"></i> Broadcast Pesan
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="checkSessionStatus()">
                <i class="fas fa-sync-alt me-1"></i> Cek Gateway
            </button>
        </div>
    </div>

    <!-- Main Chat Workspace -->
    <div class="wa-container d-flex">
        <!-- 1. LEFT SIDEBAR: KOL CONTACT LIST -->
        <div class="wa-sidebar">
            <div class="p-3 border-bottom bg-light">
                <form action="<?= base_url('whatsapp') ?>" method="GET" class="position-relative">
                    <input type="text" name="q" value="<?= esc($searchQuery ?? '') ?>" class="form-control form-control-sm rounded-pill ps-4" placeholder="Cari nama KOL / nomor WA...">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-7"></i>
                </form>
            </div>

            <div class="overflow-y-auto flex-grow-1">
                <?php if (empty($conversations)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fab fa-whatsapp fa-3x text-muted mb-2"></i>
                        <p class="small mb-0">Belum ada kontak KOL yang terdaftar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $c): ?>
                        <?php 
                        $isActive = ($selectedKol && (int)$selectedKol['id'] === (int)$c['id']);
                        $initial = strtoupper(substr($c['name'], 0, 1));
                        ?>
                        <div class="contact-item p-3 d-flex align-items-center gap-3 <?= $isActive ? 'active' : '' ?>" onclick="location.href='<?= base_url('whatsapp?kol_id=' . $c['id']) ?>'">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 44px; height: 44px; font-size: 15px; flex-shrink: 0;">
                                <?= $initial ?>
                            </div>
                            <div class="overflow-hidden flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 13.5px;"><?= esc($c['name']) ?></h6>
                                    <small class="text-muted" style="font-size: 10.5px;">
                                        <?= !empty($c['last_message_time']) ? date('H:i', strtotime($c['last_message_time'])) : '' ?>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <small class="text-muted text-truncate" style="font-size: 12px; max-width: 180px;">
                                        <?= esc($c['last_message'] ?: ($c['whatsapp'] ?: 'Belum ada percakapan')) ?>
                                    </small>
                                    <?php if (!empty($c['tier_name'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1" style="font-size: 9.5px;"><?= esc($c['tier_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. CENTER: ACTIVE CHAT THREAD -->
        <div class="wa-chat-area flex-grow-1">
            <?php if ($selectedKol): ?>
                <!-- Chat Header -->
                <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 14px;">
                            <?= strtoupper(substr($selectedKol['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= esc($selectedKol['name']) ?></h6>
                            <small class="text-muted">
                                <i class="fab fa-whatsapp text-success me-1"></i><?= esc($selectedKol['whatsapp'] ?: '-') ?>
                                &bull; <?= esc($selectedKol['city'] ?: 'Indonesia') ?>
                            </small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $selectedKol['whatsapp']) ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3">
                            <i class="fas fa-external-link-alt me-1"></i> Buka di WhatsApp Web
                        </a>
                    </div>
                </div>

                <!-- Chat Messages Scroll Pane -->
                <div class="chat-messages flex-grow-1" id="chatMessages">
                    <?php if (empty($messages)): ?>
                        <div class="text-center my-auto p-4">
                            <div class="badge bg-white text-muted px-3 py-2 shadow-sm rounded-pill mb-2">
                                <i class="fas fa-lock me-1 text-warning"></i> Komunikasi terenkripsi & tercatat resmi di sistem Kolab.id
                            </div>
                            <p class="text-muted small">Belum ada riwayat pesan langsung dengan talent ini. Mulai kirimkan brief atau sapa talent!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $m): ?>
                            <?php 
                            $isOut = ($m['direction'] ?? 'OUTBOUND') === 'OUTBOUND';
                            ?>
                            <div class="bubble <?= $isOut ? 'bubble-out' : 'bubble-in' ?>">
                                <div class="bubble-sender"><?= esc($m['sender_name'] ?: ($isOut ? 'Staff Kolab.id' : $selectedKol['name'])) ?></div>
                                <div class="bubble-text"><?= esc($m['message_text']) ?></div>
                                <div class="bubble-meta">
                                    <span><?= date('H:i', strtotime($m['created_at'])) ?></span>
                                    <?php if ($isOut): ?>
                                        <i class="fas fa-check-double <?= $m['status'] === 'SENT' ? 'text-primary' : 'text-muted' ?>" style="font-size: 10px;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Input Footer Area -->
                <div class="p-3 bg-white border-top">
                    <!-- Quick Template Selector -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="text-muted text-xs fw-semibold"><i class="fas fa-magic text-warning me-1"></i> Template:</span>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle rounded-pill px-3 text-xs" type="button" data-bs-toggle="dropdown">
                                Pilih Template Pesan...
                            </button>
                            <ul class="dropdown-menu shadow border-0" style="font-size: 12px; max-width: 320px;">
                                <?php foreach ($templates as $tpl): ?>
                                    <li>
                                        <a class="dropdown-item py-2" href="javascript:;" onclick="insertTemplate(<?= htmlspecialchars(json_encode($tpl['body_text']), ENT_QUOTES, 'UTF-8') ?>)">
                                            <div class="fw-bold text-dark"><?= esc($tpl['title']) ?></div>
                                            <small class="text-muted text-truncate d-block"><?= esc($tpl['template_code']) ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <form id="sendMessageForm" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="kol_id" value="<?= esc($selectedKol['id']) ?>">
                        <textarea id="messageInput" name="message" class="form-control rounded-3" rows="2" placeholder="Ketik pesan WhatsApp untuk talent ini... (Enter untuk kirim)" required style="resize: none;"></textarea>
                        <button type="submit" class="btn btn-success px-4 py-3 rounded-3 shadow-sm h-100" id="btnSend">
                            <i class="fas fa-paper-plane fa-lg"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                    <i class="fab fa-whatsapp fa-4x text-success mb-3 opacity-50"></i>
                    <h5 class="fw-bold">Pilih Talent KOL untuk Memulai Percakapan</h5>
                    <p class="small text-muted">Pilih salah satu kontak di sisi kiri untuk melihat riwayat obrolan dan mengirimkan notifikasi.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. RIGHT SIDEBAR: CONTEXT (ACTIVE CAMPAIGN & TASKS) -->
        <?php if ($selectedKol && $kolContext): ?>
            <div class="wa-context-area p-3">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-primary me-1"></i> Konteks Kampanye & Tugas</h6>

                <!-- Deals -->
                <div class="card border-0 bg-light rounded-3 p-3 mb-3">
                    <div class="text-xs text-uppercase fw-bold text-muted mb-1">Kampanye Aktif</div>
                    <?php if (!empty($kolContext['deals'])): ?>
                        <?php foreach ($kolContext['deals'] as $deal): ?>
                            <div class="fw-bold text-dark" style="font-size: 13px;"><?= esc($deal['campaign_title']) ?></div>
                            <small class="text-muted">Fee: Rp <?= number_format((float)$deal['deal_amount'], 0, ',', '.') ?> (<?= esc($deal['total_vt']) ?> VT)</small>
                            <hr class="my-2 text-muted opacity-25">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <small class="text-muted">Belum ada penugasan kampanye aktif.</small>
                    <?php endif; ?>
                </div>

                <!-- Pending Tasks -->
                <div class="card border-0 bg-light rounded-3 p-3 mb-3">
                    <div class="text-xs text-uppercase fw-bold text-muted mb-2">Tugas Berjalan (<?= count($kolContext['tasks']) ?>)</div>
                    <?php if (!empty($kolContext['tasks'])): ?>
                        <?php foreach ($kolContext['tasks'] as $tsk): ?>
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="fw-semibold text-dark text-truncate" style="font-size: 12.5px;"><?= esc($tsk['title'] ?: ('VT #' . $tsk['task_number'])) ?></div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9.5px;"><?= esc($tsk['status']) ?></span>
                                    <small class="text-muted" style="font-size: 10px;"><?= esc($tsk['deadline'] ?: '-') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <small class="text-muted">Semua tugas telah selesai atau belum dimulai.</small>
                    <?php endif; ?>
                </div>

                <!-- Shipment Info -->
                <?php if (!empty($kolContext['shipment'])): ?>
                    <div class="card border-0 bg-light rounded-3 p-3">
                        <div class="text-xs text-uppercase fw-bold text-muted mb-1">Pengiriman Sampel</div>
                        <div class="fw-semibold text-dark" style="font-size: 12.5px;"><?= esc($kolContext['shipment']['courier_name']) ?>: <?= esc($kolContext['shipment']['tracking_number']) ?></div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle mt-1 w-fit"><?= esc($kolContext['shipment']['shipping_status']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-bullhorn me-2"></i>Broadcast Notifikasi WhatsApp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Target Kampanye (Opsional)</label>
                        <select id="bcCampaignId" class="form-select" onchange="loadBroadcastKols()">
                            <option value="">-- Semua KOL Terdaftar --</option>
                            <?php foreach ($allCampaigns as $ac): ?>
                                <option value="<?= $ac['id'] ?>"><?= esc($ac['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Gunakan Template Pesan</label>
                        <select id="bcTemplateId" class="form-select" onchange="applyBroadcastTemplate()">
                            <option value="">-- Pilih Template --</option>
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= $tpl['id'] ?>" data-text="<?= esc($tpl['body_text']) ?>"><?= esc($tpl['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Isi Pesan Siaran</label>
                    <textarea id="bcMessageText" class="form-control" rows="4" placeholder="Ketik pesan broadcast... Anda dapat menggunakan tag: {kol_name}, {campaign_name}, {vt_title}"></textarea>
                    <small class="text-muted">Tag <code>{kol_name}</code> akan otomatis digantikan dengan nama masing-masing talent.</small>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">Daftar Penerima (<span id="recipientCount">0</span>)</label>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 text-xs" onclick="toggleAllRecipients()">Pilih Semua / Batal</button>
                    </div>
                    <div class="border rounded p-2 overflow-y-auto" style="max-height: 180px;" id="recipientList">
                        <div class="text-center text-muted small p-3">Memuat daftar penerima...</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div id="bcProgressContainer" class="d-none">
                    <label class="form-label fw-semibold">Progress Pengiriman</label>
                    <div class="progress mb-2" style="height: 18px;">
                        <div id="bcProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                    </div>
                    <small id="bcStatusText" class="text-muted">Mengirim...</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnExecuteBroadcast" onclick="startBroadcast()">
                    <i class="fas fa-paper-plane me-1"></i> Mulai Broadcast
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto scroll chat to bottom
function scrollChatToBottom() {
    const el = document.getElementById('chatMessages');
    if (el) {
        el.scrollTop = el.scrollHeight;
    }
}
window.addEventListener('DOMContentLoaded', scrollChatToBottom);

// Template insertion
function insertTemplate(tplText) {
    const kolName = <?= json_encode($selectedKol['name'] ?? 'Kak') ?>;
    let text = tplText.replace(/{kol_name}/g, kolName);
    document.getElementById('messageInput').value = text;
    document.getElementById('messageInput').focus();
}

// Send message via AJAX
const form = document.getElementById('sendMessageForm');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        if (!text) return;

        const btn = document.getElementById('btnSend');
        btn.disabled = true;

        const formData = new FormData(form);
        fetch('<?= base_url('whatsapp/send') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res.success) {
                // Append bubble locally
                const container = document.getElementById('chatMessages');
                const bubble = document.createElement('div');
                bubble.className = 'bubble bubble-out';
                bubble.innerHTML = `
                    <div class="bubble-sender">${res.sender_name}</div>
                    <div class="bubble-text">${escapeHtml(text)}</div>
                    <div class="bubble-meta">
                        <span>${res.sent_at}</span>
                        <i class="fas fa-check text-primary" style="font-size: 10px;"></i>
                    </div>
                `;
                container.appendChild(bubble);
                input.value = '';
                scrollChatToBottom();
            } else {
                alert(res.message || 'Gagal mengirim pesan');
            }
        })
        .catch(err => {
            btn.disabled = false;
            console.error(err);
        });
    });

    // Enter to submit
    document.getElementById('messageInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Session status check
function checkSessionStatus() {
    fetch('<?= base_url('whatsapp/session-status') ?>')
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert('Gateway Status: ' + res.data.status + ' (' + res.data.gateway + ')');
        }
    });
}

// Broadcast logic
function loadBroadcastKols() {
    const campId = document.getElementById('bcCampaignId').value;
    const listEl = document.getElementById('recipientList');
    listEl.innerHTML = '<div class="text-center text-muted small p-3">Memuat penerima...</div>';

    fetch('<?= base_url('whatsapp/broadcast-kols') ?>?campaign_id=' + campId)
    .then(r => r.json())
    .then(res => {
        if (res.success && res.kols.length > 0) {
            let html = '';
            res.kols.forEach(k => {
                html += `
                    <div class="form-check py-1">
                        <input class="form-check-input bc-checkbox" type="checkbox" value="${k.id}" id="bc_kol_${k.id}" checked>
                        <label class="form-check-label text-dark" for="bc_kol_${k.id}" style="font-size: 13px;">
                            <b>${escapeHtml(k.name)}</b> (${k.whatsapp}) ${k.tier_name ? '<span class="badge bg-light text-dark border">' + k.tier_name + '</span>' : ''}
                        </label>
                    </div>
                `;
            });
            listEl.innerHTML = html;
            document.getElementById('recipientCount').innerText = res.kols.length;
        } else {
            listEl.innerHTML = '<div class="text-center text-muted small p-3">Tidak ada KOL ditemukan.</div>';
            document.getElementById('recipientCount').innerText = '0';
        }
    });
}

function applyBroadcastTemplate() {
    const sel = document.getElementById('bcTemplateId');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.text) {
        document.getElementById('bcMessageText').value = opt.dataset.text;
    }
}

function toggleAllRecipients() {
    const boxes = document.querySelectorAll('.bc-checkbox');
    const allChecked = Array.from(boxes).every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
}

async function startBroadcast() {
    const text = document.getElementById('bcMessageText').value.trim();
    const boxes = Array.from(document.querySelectorAll('.bc-checkbox:checked'));
    if (!text) {
        alert('Isi pesan broadcast wajib diisi!');
        return;
    }
    if (boxes.length === 0) {
        alert('Pilih minimal 1 KOL penerima!');
        return;
    }

    const tplId = document.getElementById('bcTemplateId').value;
    const campId = document.getElementById('bcCampaignId').value;
    const btn = document.getElementById('btnExecuteBroadcast');
    btn.disabled = true;

    const progCont = document.getElementById('bcProgressContainer');
    const progBar = document.getElementById('bcProgressBar');
    const statusText = document.getElementById('bcStatusText');
    progCont.classList.remove('d-none');

    let done = 0;
    const total = boxes.length;

    for (const box of boxes) {
        const kolId = box.value;
        statusText.innerText = `Mengirim ke penerima (${done + 1}/${total})...`;

        const fd = new FormData();
        fd.append('kol_id', kolId);
        fd.append('template_id', tplId);
        fd.append('custom_text', text);
        fd.append('campaign_id', campId);

        try {
            await fetch('<?= base_url('whatsapp/send-broadcast-item') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (e) {
            console.error(e);
        }

        done++;
        const pct = Math.round((done / total) * 100);
        progBar.style.width = pct + '%';
        progBar.innerText = pct + '%';
    }

    statusText.innerText = 'Broadcast selesai! Berhasil mengirim ' + done + ' pesan.';
    btn.disabled = false;
    setTimeout(() => {
        location.reload();
    }, 1500);
}

// Initial load for broadcast modal
document.getElementById('broadcastModal').addEventListener('show.bs.modal', function() {
    loadBroadcastKols();
});
</script>

<?= $this->endSection() ?>
