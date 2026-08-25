<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('spaces') ?>">Spaces</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?= base_url("spaces/{$project['space_id']}/projects") ?>">
                            <?= esc($project['space_name']) ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($project['name']) ?></li>
                </ol>
            </nav>
            <h1 class="page-title">
                <i class="bi bi-kanban me-2 text-primary"></i><?= esc($project['name']) ?>
            </h1>
            <p class="page-subtitle text-muted">
                <span class="badge badge-project-type badge-<?= $project['type'] ?> me-2">
                    <?= ucfirst($project['type']) ?>
                </span>
                <?= $stats['total'] ?> tiket total · <?= $stats['done'] ?> selesai
            </p>
        </div>
        <div class="page-actions d-flex gap-2">
            <a href="<?= base_url('sessions/create?project_id=' . $project['id']) ?>"
               class="btn btn-success">
                <i class="bi bi-play-circle-fill me-2"></i>Mulai Sesi
            </a>
            <button class="btn btn-primary" id="btnNewTicket">
                <i class="bi bi-plus-circle me-2"></i>Tambah Tiket
            </button>
        </div>
    </div>

    <!-- Eisenhower Priority Filter -->
    <div class="priority-filter mb-4">
        <div class="priority-filter-label">Filter Prioritas:</div>
        <div class="priority-filter-buttons">
            <a href="?<?= http_build_query(array_merge($_GET, ['priority' => ''])) ?>"
               class="priority-btn <?= !$active_priority ? 'active' : '' ?>">
                <i class="bi bi-grid me-1"></i>Semua
            </a>
            <?php foreach ($priorities as $key => $label): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['priority' => $key])) ?>"
               class="priority-btn priority-btn-<?= $priority_colors[$key] ?> <?= $active_priority === $key ? 'active' : '' ?>">
                <?= $label ?>
            </a>
            <?php endforeach ?>
        </div>
    </div>

    <!-- Status Stats Row -->
    <div class="status-stats mb-4">
        <?php foreach (['pending' => 'secondary', 'ongoing' => 'primary', 'done' => 'success', 'waiting-approval' => 'warning', 'cancelled' => 'danger'] as $s => $c): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['status' => $active_status === $s ? '' : $s])) ?>"
           class="status-stat status-stat-<?= $c ?> <?= $active_status === $s ? 'active' : '' ?>">
            <div class="status-stat-value"><?= $stats[$s] ?></div>
            <div class="status-stat-label"><?= ucfirst($s) ?></div>
        </a>
        <?php endforeach ?>
    </div>

    <!-- Ticket Cards -->
    <?php if (!empty($tickets)): ?>
    <div class="row g-3" id="ticketsGrid">
        <?php foreach ($tickets as $ticket): ?>
        <?php
            $pColor = $priority_colors[$ticket['priority']] ?? 'secondary';
            $sColor = $status_colors[$ticket['status']] ?? 'secondary';
        ?>
        <div class="col-12 col-md-6 col-xl-4" id="ticketCard<?= $ticket['id'] ?>">
            <div class="card card-ticket card-ticket-<?= $pColor ?>">
                <div class="card-body">
                    <!-- Priority Badge -->
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <span class="badge bg-<?= $pColor ?>-subtle text-<?= $pColor ?> priority-badge">
                            <?= $priorities[$ticket['priority']] ?? $ticket['priority'] ?>
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-icon btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php foreach (['pending','ongoing','done','waiting-approval','cancelled'] as $st): ?>
                                <li>
                                    <button class="dropdown-item btn-change-status <?= $ticket['status'] === $st ? 'active' : '' ?>"
                                            data-ticket-id="<?= $ticket['id'] ?>"
                                            data-status="<?= $st ?>">
                                        <i class="bi bi-circle<?= $ticket['status'] === $st ? '-fill' : '' ?> me-2 text-<?= $status_colors[$st] ?>"></i>
                                        <?= ucfirst($st) ?>
                                    </button>
                                </li>
                                <?php endforeach ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item btn-edit-ticket"
                                            data-id="<?= $ticket['id'] ?>"
                                            data-title="<?= esc($ticket['title']) ?>"
                                            data-description="<?= esc($ticket['description'] ?? '') ?>"
                                            data-priority="<?= $ticket['priority'] ?>"
                                            data-status="<?= $ticket['status'] ?>">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger btn-delete-ticket"
                                            data-id="<?= $ticket['id'] ?>"
                                            data-title="<?= esc($ticket['title']) ?>">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h6 class="ticket-title"><?= esc($ticket['title']) ?></h6>
                    <?php if ($ticket['description']): ?>
                    <p class="ticket-desc text-muted"><?= esc($ticket['description']) ?></p>
                    <?php endif ?>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <span class="badge bg-<?= $sColor ?>-subtle text-<?= $sColor ?> status-badge-card"
                              id="statusBadge<?= $ticket['id'] ?>">
                            <?= ucfirst($ticket['status']) ?>
                        </span>
                        <small class="text-muted">
                            <?= date('d M', strtotime($ticket['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <h4>Tidak Ada Tiket</h4>
        <p class="text-muted">
            <?= $active_priority || $active_status ? 'Tidak ada tiket dengan filter ini. Coba ubah filter.' : 'Tambahkan tiket pertama untuk proyek ini.' ?>
        </p>
        <?php if (!$active_priority && !$active_status): ?>
        <button class="btn btn-primary" id="btnNewTicketEmpty">
            <i class="bi bi-plus-circle me-2"></i>Tambah Tiket
        </button>
        <?php endif ?>
    </div>
    <?php endif ?>
</div>

<!-- Ticket Modal (Create/Edit) -->
<div class="modal fade" id="ticketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-ticket me-2"></i>
                    <span id="ticketModalTitle">Tambah Tiket</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="ticketForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="ticketId" name="ticket_id">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                    <div class="mb-3">
                        <label for="ticketTitle" class="form-label">Judul Tiket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ticketTitle" name="title"
                               placeholder="Deskripsi singkat tugas ini" required>
                        <div class="invalid-feedback" id="ticketTitleError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="ticketDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="ticketDescription" name="description"
                                  rows="4" placeholder="Detail lebih lanjut tentang tiket ini (opsional)"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ticketPriority" class="form-label">
                                Prioritas (Eisenhower) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="ticketPriority" name="priority" required>
                                <?php foreach ($priorities as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ticketStatus" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="ticketStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="done">Done</option>
                                <option value="waiting-approval">Waiting Approval</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Priority guide -->
                    <div class="eisenhower-guide mt-3">
                        <p class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Panduan Eisenhower:</p>
                        <div class="eisenhower-grid">
                            <div class="ei-cell ei-q1">Q1 Lakukan Sekarang</div>
                            <div class="ei-cell ei-q2">Q2 Jadwalkan</div>
                            <div class="ei-cell ei-q3">Q3 Delegasikan</div>
                            <div class="ei-cell ei-q4">Q4 Eliminasi</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="ticketSubmitBtn">
                        <span class="btn-text">Simpan</span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Ticket Modal -->
<div class="modal fade" id="deleteTicketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Hapus Tiket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin hapus tiket <strong id="deleteTicketTitle"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteTicket">
                    <span class="btn-text">Hapus</span>
                    <span class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE_URL    = '<?= base_url() ?>';
const PROJECT_ID  = <?= $project['id'] ?>;
let deleteTicketId = null;

function openCreateTicketModal() {
    document.getElementById('ticketModalTitle').textContent = 'Tambah Tiket Baru';
    document.getElementById('ticketId').value = '';
    document.getElementById('ticketForm').reset();
    clearFormErrors('ticketForm');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('ticketModal')).show();
}

document.getElementById('btnNewTicket')?.addEventListener('click', openCreateTicketModal);
document.getElementById('btnNewTicketEmpty')?.addEventListener('click', openCreateTicketModal);

document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.btn-edit-ticket');
    if (editBtn) {
        document.getElementById('ticketModalTitle').textContent = 'Edit Tiket';
        document.getElementById('ticketId').value = editBtn.dataset.id;
        document.getElementById('ticketTitle').value = editBtn.dataset.title;
        document.getElementById('ticketDescription').value = editBtn.dataset.description;
        document.getElementById('ticketPriority').value = editBtn.dataset.priority;
        document.getElementById('ticketStatus').value = editBtn.dataset.status;
        clearFormErrors('ticketForm');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('ticketModal')).show();
    }

    const delBtn = e.target.closest('.btn-delete-ticket');
    if (delBtn) {
        deleteTicketId = delBtn.dataset.id;
        document.getElementById('deleteTicketTitle').textContent = delBtn.dataset.title;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteTicketModal')).show();
    }

    const statusBtn = e.target.closest('.btn-change-status');
    if (statusBtn) {
        const ticketId = statusBtn.dataset.ticketId;
        const newStatus = statusBtn.dataset.status;
        ajaxRequest(`${BASE_URL}tickets/${ticketId}/status`, 'POST', { status: newStatus })
            .then(data => {
                if (data.success) {
                    // Update status badge in card
                    const badge = document.getElementById(`statusBadge${ticketId}`);
                    if (badge) {
                        badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        badge.className = `badge bg-${data.status_color}-subtle text-${data.status_color} status-badge-card`;
                    }
                    showToast('Status diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui status.', 'danger');
                }
            })
            .catch(() => showToast('Terjadi kesalahan.', 'danger'));
    }
});

document.getElementById('ticketForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id  = document.getElementById('ticketId').value;
    const btn = document.getElementById('ticketSubmitBtn');
    setLoading(btn, true);
    clearFormErrors('ticketForm');

    const payload = {
        project_id:  PROJECT_ID,
        title:       document.getElementById('ticketTitle').value.trim(),
        description: document.getElementById('ticketDescription').value.trim(),
        priority:    document.getElementById('ticketPriority').value,
        status:      document.getElementById('ticketStatus').value,
    };

    const url    = id ? `${BASE_URL}tickets/${id}` : `${BASE_URL}tickets`;
    const method = id ? 'PUT' : 'POST';

    ajaxRequest(url, method, payload)
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('ticketModal')).hide();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                if (data.errors) showFormErrors(data.errors, { title: 'ticketTitleError' });
                else showToast(data.message || 'Gagal menyimpan.', 'danger');
            }
        })
        .catch(() => showToast('Terjadi kesalahan.', 'danger'))
        .finally(() => setLoading(btn, false));
});

document.getElementById('confirmDeleteTicket').addEventListener('click', function () {
    if (!deleteTicketId) return;
    const btn = this;
    setLoading(btn, true);

    ajaxRequest(`${BASE_URL}tickets/${deleteTicketId}`, 'DELETE')
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('deleteTicketModal')).hide();
                document.getElementById(`ticketCard${deleteTicketId}`)?.remove();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Gagal menghapus.', 'danger');
            }
        })
        .catch(() => showToast('Terjadi kesalahan.', 'danger'))
        .finally(() => setLoading(btn, false));
});
</script>
<?= $this->endSection() ?>
