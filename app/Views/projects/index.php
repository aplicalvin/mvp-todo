<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= base_url('spaces') ?>">Spaces</a></li>
                    <li class="breadcrumb-item active"><?= esc($space['name']) ?></li>
                </ol>
            </nav>
            <h1 class="page-title">
                <i class="bi bi-folder2-open me-2 text-primary"></i><?= esc($space['name']) ?>
            </h1>
            <p class="page-subtitle text-muted"><?= esc($space['description'] ?? 'Kelola proyek dalam space ini.') ?></p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" id="btnNewProject">
                <i class="bi bi-plus-circle me-2"></i>Tambah Proyek
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <?php
            $totalTickets = 0;
            $doneTickets  = 0;
            foreach ($projects as $p) {
                $totalTickets += $p['ticket_stats']['total'] ?? 0;
                $doneTickets  += $p['ticket_stats']['done']  ?? 0;
            }
        ?>
        <div class="col-6 col-md-3">
            <div class="mini-stat">
                <div class="mini-stat-value"><?= count($projects) ?></div>
                <div class="mini-stat-label">Total Proyek</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="mini-stat">
                <div class="mini-stat-value"><?= $totalTickets ?></div>
                <div class="mini-stat-label">Total Tiket</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="mini-stat text-success">
                <div class="mini-stat-value"><?= $doneTickets ?></div>
                <div class="mini-stat-label">Selesai</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="mini-stat text-warning">
                <div class="mini-stat-value"><?= $totalTickets - $doneTickets ?></div>
                <div class="mini-stat-label">Sisa Tugas</div>
            </div>
        </div>
    </div>

    <!-- Project Cards -->
    <?php if (!empty($projects)): ?>
    <div class="row g-4" id="projectsGrid">
        <?php foreach ($projects as $proj): ?>
        <?php $stats = $proj['ticket_stats']; ?>
        <div class="col-12 col-md-6 col-xl-4" id="projectCard<?= $proj['id'] ?>">
            <div class="card card-project">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="badge badge-project-type badge-<?= $proj['type'] ?>">
                                <i class="bi bi-<?= $proj['type'] === 'routine' ? 'arrow-repeat' : 'calendar-event' ?> me-1"></i>
                                <?= ucfirst($proj['type']) ?>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-icon btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url("projects/{$proj['id']}/tickets") ?>">
                                        <i class="bi bi-kanban me-2"></i>Lihat Tiket
                                    </a>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-edit-project"
                                            data-id="<?= $proj['id'] ?>"
                                            data-name="<?= esc($proj['name']) ?>"
                                            data-description="<?= esc($proj['description'] ?? '') ?>"
                                            data-type="<?= $proj['type'] ?>">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger btn-delete-project"
                                            data-id="<?= $proj['id'] ?>"
                                            data-name="<?= esc($proj['name']) ?>">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h5 class="project-name"><?= esc($proj['name']) ?></h5>
                    <p class="text-muted small mb-3"><?= esc($proj['description'] ?? 'Tidak ada deskripsi.') ?></p>

                    <!-- Ticket Stats -->
                    <div class="ticket-stats-row mb-3">
                        <div class="ticket-stat">
                            <span class="ts-value text-secondary"><?= $stats['pending'] ?></span>
                            <span class="ts-label">Pending</span>
                        </div>
                        <div class="ticket-stat">
                            <span class="ts-value text-primary"><?= $stats['ongoing'] ?></span>
                            <span class="ts-label">Ongoing</span>
                        </div>
                        <div class="ticket-stat">
                            <span class="ts-value text-success"><?= $stats['done'] ?></span>
                            <span class="ts-label">Done</span>
                        </div>
                        <div class="ticket-stat">
                            <span class="ts-value text-danger"><?= $stats['cancelled'] ?></span>
                            <span class="ts-label">Cancelled</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <?php
                        $pct = $stats['total'] > 0 ? round(($stats['done'] / $stats['total']) * 100) : 0;
                    ?>
                    <div class="mb-1 d-flex justify-content-between small">
                        <span class="text-muted">Progress</span>
                        <span class="fw-semibold"><?= $pct ?>%</span>
                    </div>
                    <div class="progress progress-app mb-0" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <a href="<?= base_url("projects/{$proj['id']}/tickets") ?>"
                       class="btn btn-sm btn-outline-primary flex-grow-1">
                        <i class="bi bi-kanban me-1"></i>Tiket
                    </a>
                    <a href="<?= base_url('sessions/create?project_id=' . $proj['id']) ?>"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-play-fill"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-kanban"></i>
        <h4>Belum Ada Proyek</h4>
        <p class="text-muted">Buat proyek pertama di space ini.</p>
        <button class="btn btn-primary" id="btnNewProjectEmpty">
            <i class="bi bi-plus-circle me-2"></i>Tambah Proyek
        </button>
    </div>
    <?php endif ?>
</div>

<!-- Project Modal (Create/Edit) -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-kanban me-2"></i>
                    <span id="projectModalTitle">Tambah Proyek</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="projectForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="projectId" name="project_id">
                    <input type="hidden" name="space_id" value="<?= $space['id'] ?>">

                    <div class="mb-3">
                        <label for="projectName" class="form-label">Nama Proyek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="projectName" name="name"
                               placeholder="e.g., Absensi Aslab, Project A" required>
                        <div class="invalid-feedback" id="projectNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="projectDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="projectDescription" name="description"
                                  rows="3" placeholder="Opsional"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Proyek <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check flex-grow-1 project-type-option" id="typeRoutineOption">
                                <input class="form-check-input" type="radio" name="type" id="typeRoutine"
                                       value="routine" checked>
                                <label class="form-check-label" for="typeRoutine">
                                    <i class="bi bi-arrow-repeat me-1 text-success"></i>
                                    <strong>Routine</strong>
                                    <p class="mb-0 small text-muted">Berulang secara reguler</p>
                                </label>
                            </div>
                            <div class="form-check flex-grow-1 project-type-option" id="typeSeasonalOption">
                                <input class="form-check-input" type="radio" name="type" id="typeSeasonal"
                                       value="seasonal">
                                <label class="form-check-label" for="typeSeasonal">
                                    <i class="bi bi-calendar-event me-1 text-warning"></i>
                                    <strong>Seasonal</strong>
                                    <p class="mb-0 small text-muted">Satu kali / musiman</p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="projectSubmitBtn">
                        <span class="btn-text">Simpan</span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Project Modal -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Hapus Proyek</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin hapus proyek <strong id="deleteProjectName"></strong>?</p>
                <p class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Semua tiket akan terhapus!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteProject">
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
const BASE_URL = '<?= base_url() ?>';
const SPACE_ID = <?= $space['id'] ?>;
let deleteProjectId = null;

function openCreateProjectModal() {
    document.getElementById('projectModalTitle').textContent = 'Tambah Proyek';
    document.getElementById('projectId').value = '';
    document.getElementById('projectForm').reset();
    document.querySelector('input[name="type"][value="routine"]').checked = true;
    clearFormErrors('projectForm');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('projectModal')).show();
}

document.getElementById('btnNewProject')?.addEventListener('click', openCreateProjectModal);
document.getElementById('btnNewProjectEmpty')?.addEventListener('click', openCreateProjectModal);

document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.btn-edit-project');
    if (editBtn) {
        document.getElementById('projectModalTitle').textContent = 'Edit Proyek';
        document.getElementById('projectId').value = editBtn.dataset.id;
        document.getElementById('projectName').value = editBtn.dataset.name;
        document.getElementById('projectDescription').value = editBtn.dataset.description;
        document.querySelector(`input[name="type"][value="${editBtn.dataset.type}"]`).checked = true;
        clearFormErrors('projectForm');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('projectModal')).show();
    }

    const delBtn = e.target.closest('.btn-delete-project');
    if (delBtn) {
        deleteProjectId = delBtn.dataset.id;
        document.getElementById('deleteProjectName').textContent = delBtn.dataset.name;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteProjectModal')).show();
    }
});

document.getElementById('projectForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id   = document.getElementById('projectId').value;
    const btn  = document.getElementById('projectSubmitBtn');
    setLoading(btn, true);
    clearFormErrors('projectForm');

    const payload = {
        space_id:    SPACE_ID,
        name:        document.getElementById('projectName').value.trim(),
        description: document.getElementById('projectDescription').value.trim(),
        type:        document.querySelector('input[name="type"]:checked')?.value ?? 'routine',
    };

    const url    = id ? `${BASE_URL}projects/${id}` : `${BASE_URL}projects`;
    const method = id ? 'PUT' : 'POST';

    ajaxRequest(url, method, payload)
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('projectModal')).hide();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                if (data.errors) showFormErrors(data.errors, { name: 'projectNameError' });
                else showToast(data.message || 'Gagal menyimpan.', 'danger');
            }
        })
        .catch(() => showToast('Terjadi kesalahan.', 'danger'))
        .finally(() => setLoading(btn, false));
});

document.getElementById('confirmDeleteProject').addEventListener('click', function () {
    if (!deleteProjectId) return;
    const btn = this;
    setLoading(btn, true);

    ajaxRequest(`${BASE_URL}projects/${deleteProjectId}`, 'DELETE')
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('deleteProjectModal')).hide();
                document.getElementById(`projectCard${deleteProjectId}`)?.remove();
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
