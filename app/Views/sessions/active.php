<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Sesi Aktif') ?> — FocusFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
</head>
<body class="active-session-body">

<!-- Session Header Bar -->
<div class="session-header">
    <div class="session-header-left">
        <a href="<?= base_url('/') ?>" class="session-brand">
            <i class="bi bi-lightning-charge-fill"></i>
            <span>FocusFlow</span>
        </a>
        <div class="session-info">
            <span class="session-badge">
                <span class="pulse-dot-sm"></span>
                Sesi Aktif
            </span>
            <span class="session-project"><?= esc($session['project_name']) ?></span>
            <span class="text-muted small">&bull;</span>
            <span class="text-muted small"><?= esc($session['space_name']) ?></span>
        </div>
    </div>
    <div class="session-header-right">
        <span class="text-muted small" id="sessionStartTime">
            Dimulai: <?= date('H:i', strtotime($session['start_time'])) ?>
        </span>
        <button class="btn btn-danger btn-sm ms-3" id="btnEndSession">
            <i class="bi bi-stop-circle me-1"></i>Akhiri Sesi
        </button>
    </div>
</div>

<!-- Main Split Layout -->
<div class="session-layout">

    <!-- LEFT PANEL: Tickets -->
    <div class="session-tickets-panel">
        <div class="panel-header">
            <h5>
                <i class="bi bi-kanban me-2"></i>
                Tiket Proyek
            </h5>
            <div class="panel-meta">
                <span class="badge bg-success-subtle text-success" id="doneCountBadge">
                    <span id="doneCount">0</span> selesai
                </span>
                <span class="badge bg-secondary-subtle text-secondary" id="totalCountBadge">
                    <?= count($sessionTickets) ?> total
                </span>
            </div>
        </div>

        <div class="tickets-list" id="ticketsList">
            <?php if (empty($sessionTickets)): ?>
            <div class="empty-state-sm">
                <i class="bi bi-inbox"></i>
                <p>Tidak ada tiket aktif dalam proyek ini.</p>
            </div>
            <?php else: ?>
            <?php foreach ($sessionTickets as $st): ?>
            <?php
                $isDone   = ($st['status'] === 'completed');
                $pColor   = $priorityColors[$st['priority']] ?? 'secondary';
            ?>
            <div class="session-ticket-item <?= $isDone ? 'is-done' : '' ?>"
                 id="stItem<?= $st['ticket_id'] ?>">
                <div class="ticket-checkbox-wrap">
                    <input class="ticket-checkbox form-check-input"
                           type="checkbox"
                           id="tick<?= $st['ticket_id'] ?>"
                           data-ticket-id="<?= $st['ticket_id'] ?>"
                           data-session-id="<?= $session['id'] ?>"
                           <?= $isDone ? 'checked' : '' ?>>
                </div>
                <label class="ticket-item-label" for="tick<?= $st['ticket_id'] ?>">
                    <div class="ticket-item-title"><?= esc($st['title']) ?></div>
                    <?php if ($st['description']): ?>
                    <div class="ticket-item-desc"><?= esc($st['description']) ?></div>
                    <?php endif ?>
                    <div class="ticket-item-meta">
                        <span class="badge bg-<?= $pColor ?>-subtle text-<?= $pColor ?> small">
                            <?= $st['priority'] ?>
                        </span>
                        <?php if ($isDone && $st['completed_at']): ?>
                        <span class="text-success small">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Selesai <?= date('H:i', strtotime($st['completed_at'])) ?>
                        </span>
                        <?php endif ?>
                    </div>
                </label>
            </div>
            <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>

    <!-- RIGHT PANEL: Pomodoro Timer -->
    <div class="session-timer-panel">
        <div class="timer-card">
            <div class="timer-label" id="timerLabel">Sesi Fokus</div>

            <!-- Timer Display -->
            <div class="timer-display" id="timerDisplay">25:00</div>

            <!-- Timer Progress Ring -->
            <svg class="timer-ring" viewBox="0 0 200 200">
                <circle class="ring-track" cx="100" cy="100" r="90"/>
                <circle class="ring-progress" cx="100" cy="100" r="90"
                        id="timerRing"
                        stroke-dasharray="565.49"
                        stroke-dashoffset="0"/>
            </svg>

            <!-- Controls -->
            <div class="timer-controls">
                <button class="btn btn-timer-control btn-outline-secondary" id="btnReset" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button class="btn btn-timer-main btn-success" id="btnStartPause">
                    <i class="bi bi-play-fill" id="startPauseIcon"></i>
                    <span id="startPauseText">Mulai</span>
                </button>
                <button class="btn btn-timer-control btn-outline-secondary" id="btnExtend" title="+5 Menit">
                    <i class="bi bi-plus-circle"></i>
                    <span>+5m</span>
                </button>
            </div>

            <!-- Timer Preset Buttons -->
            <div class="timer-presets mt-3">
                <button class="btn btn-preset active" data-minutes="25">25m</button>
                <button class="btn btn-preset" data-minutes="15">15m</button>
                <button class="btn btn-preset" data-minutes="50">50m</button>
            </div>

            <!-- Session Stats -->
            <div class="timer-stats mt-4">
                <div class="timer-stat">
                    <div class="ts-val" id="elapsedTime">0m</div>
                    <div class="ts-lab">Waktu Berlalu</div>
                </div>
                <div class="timer-stat">
                    <div class="ts-val" id="doneCountTimer">0</div>
                    <div class="ts-lab">Tiket Selesai</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal (shown after session ends) -->
<div class="modal fade" id="summaryModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-trophy-fill text-warning me-2"></i>Sesi Selesai!
                </h5>
            </div>
            <div class="modal-body">
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="ss-icon text-primary"><i class="bi bi-stopwatch-fill"></i></div>
                        <div class="ss-val" id="summaryDuration">—</div>
                        <div class="ss-lab">Durasi</div>
                    </div>
                    <div class="summary-stat">
                        <div class="ss-icon text-success"><i class="bi bi-check-circle-fill"></i></div>
                        <div class="ss-val" id="summaryTickets">—</div>
                        <div class="ss-lab">Tiket Selesai</div>
                    </div>
                </div>
                <div id="summaryTicketList" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <a href="<?= base_url('sessions') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-clock-history me-1"></i>Riwayat
                </a>
                <a href="<?= base_url('sessions/create') ?>" class="btn btn-success">
                    <i class="bi bi-play-circle me-1"></i>Sesi Baru
                </a>
                <a id="summaryDetailLink" href="<?= base_url("sessions/{$session['id']}") ?>"
                   class="btn btn-primary">
                    <i class="bi bi-eye me-1"></i>Detail
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script src="<?= base_url('assets/js/timer.js') ?>"></script>
<script>
    // Initialize session data
    const SESSION_ID  = <?= $session['id'] ?>;
    const BASE_URL    = '<?= base_url() ?>';
    const SESSION_END_URL = `${BASE_URL}sessions/${SESSION_ID}/end`;
    const TICK_URL    = `${BASE_URL}sessions/tick`;

    // Count initial done tickets
    let doneCount = <?= count(array_filter($sessionTickets, fn($t) => $t['status'] === 'completed')) ?>;
    document.getElementById('doneCount').textContent = doneCount;
    document.getElementById('doneCountTimer').textContent = doneCount;

    // Wire up ticket checkboxes
    document.querySelectorAll('.ticket-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const ticketId  = this.dataset.ticketId;
            const sessionId = this.dataset.sessionId;
            const done      = this.checked ? '1' : '0';
            const item      = document.getElementById(`stItem${ticketId}`);

            ajaxRequest(TICK_URL, 'POST', { session_id: sessionId, ticket_id: ticketId, done })
                .then(data => {
                    if (data.success) {
                        item?.classList.toggle('is-done', data.done);
                        doneCount += data.done ? 1 : -1;
                        document.getElementById('doneCount').textContent = doneCount;
                        document.getElementById('doneCountTimer').textContent = doneCount;
                    } else {
                        // Revert checkbox on failure
                        this.checked = !this.checked;
                        showToast(data.message || 'Gagal memperbarui tiket.', 'danger');
                    }
                })
                .catch(() => {
                    this.checked = !this.checked;
                    showToast('Terjadi kesalahan.', 'danger');
                });
        });
    });

    // End session button
    document.getElementById('btnEndSession').addEventListener('click', function () {
        if (confirm('Yakin ingin mengakhiri sesi ini?')) {
            endSession();
        }
    });

    function endSession() {
        ajaxRequest(SESSION_END_URL, 'POST', {})
            .then(data => {
                if (data.success) {
                    // Stop timer
                    if (typeof timerStop === 'function') timerStop();

                    // Show summary modal
                    document.getElementById('summaryDuration').textContent = `${data.duration_minutes} menit`;
                    document.getElementById('summaryTickets').textContent = `${data.tickets_done} tiket`;

                    if (data.tickets.length > 0) {
                        const listHtml = data.tickets.map(t =>
                            `<div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="small">${escapeHtml(t.title)}</span>
                             </div>`
                        ).join('');
                        document.getElementById('summaryTicketList').innerHTML =
                            `<p class="small text-muted mb-2">Tiket diselesaikan:</p>${listHtml}`;
                    }

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('summaryModal')).show();
                } else {
                    showToast(data.message || 'Gagal mengakhiri sesi.', 'danger');
                }
            })
            .catch(() => showToast('Terjadi kesalahan.', 'danger'));
    }

    // Called by timer.js when countdown reaches zero
    window.onTimerEnd = function () {
        endSession();
    };

    function escapeHtml(text) {
        const el = document.createElement('span');
        el.textContent = text;
        return el.innerHTML;
    }
</script>
</body>
</html>
