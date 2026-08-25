<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Spaces</h1>
            <p class="page-subtitle text-muted">Kelola semua ruang kerja Anda.</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" id="btnNewSpace">
                <i class="bi bi-folder-plus me-2"></i>Buat Space Baru
            </button>
        </div>
    </div>

    <!-- Space Cards Grid -->
    <?php if (!empty($spaces)): ?>
    <div class="row g-4" id="spacesGrid">
        <?php foreach ($spaces as $space): ?>
        <div class="col-12 col-sm-6 col-xl-4" id="spaceCard<?= $space['id'] ?>">
            <div class="card card-space">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="space-icon">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-icon btn-sm" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url("spaces/{$space['id']}/projects") ?>">
                                        <i class="bi bi-kanban me-2"></i>Lihat Proyek
                                    </a>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-edit-space"
                                            data-id="<?= $space['id'] ?>"
                                            data-name="<?= esc($space['name']) ?>"
                                            data-description="<?= esc($space['description'] ?? '') ?>">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger btn-delete-space"
                                            data-id="<?= $space['id'] ?>"
                                            data-name="<?= esc($space['name']) ?>">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <h5 class="card-title space-name"><?= esc($space['name']) ?></h5>
                    <p class="card-text text-muted small space-desc">
                        <?= esc($space['description'] ?? 'Tidak ada deskripsi.') ?>
                    </p>

                    <div class="space-meta">
                        <span class="meta-badge">
                            <i class="bi bi-kanban me-1"></i>
                            <?= $space['project_count'] ?> proyek
                        </span>
                        <span class="text-muted small">
                            <?= date('d M Y', strtotime($space['created_at'])) ?>
                        </span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url("spaces/{$space['id']}/projects") ?>"
                       class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-arrow-right me-1"></i>Buka Proyek
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-folder-x"></i>
        <h4>Belum Ada Space</h4>
        <p class="text-muted">Buat space pertama Anda untuk mulai mengorganisir proyek dan tugas.</p>
        <button class="btn btn-primary" id="btnNewSpaceEmpty">
            <i class="bi bi-folder-plus me-2"></i>Buat Space Pertama
        </button>
    </div>
    <?php endif ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSpaceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-trash me-2"></i>Hapus Space
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus space <strong id="deleteSpaceName"></strong>?</p>
                <p class="text-danger small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Semua proyek dan tiket di dalamnya juga akan terhapus!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteSpace">
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
let deleteSpaceId = null;

// --- Open Create Modal ---
function openCreateSpaceModal() {
    document.getElementById('spaceModalTitleText').textContent = 'Buat Space Baru';
    document.getElementById('spaceId').value = '';
    document.getElementById('spaceForm').reset();
    clearFormErrors('spaceForm');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('spaceModal')).show();
}

document.getElementById('btnNewSpace')?.addEventListener('click', openCreateSpaceModal);
document.getElementById('btnNewSpaceEmpty')?.addEventListener('click', openCreateSpaceModal);
document.getElementById('btnAddSpaceMini')?.addEventListener('click', openCreateSpaceModal);

// --- Open Edit Modal ---
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit-space');
    if (btn) {
        document.getElementById('spaceModalTitleText').textContent = 'Edit Space';
        document.getElementById('spaceId').value = btn.dataset.id;
        document.getElementById('spaceName').value = btn.dataset.name;
        document.getElementById('spaceDescription').value = btn.dataset.description;
        clearFormErrors('spaceForm');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('spaceModal')).show();
    }
});

// --- Open Delete Modal ---
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-space');
    if (btn) {
        deleteSpaceId = btn.dataset.id;
        document.getElementById('deleteSpaceName').textContent = btn.dataset.name;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteSpaceModal')).show();
    }
});

// --- Submit Space Form (Create/Edit) ---
document.getElementById('spaceForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id   = document.getElementById('spaceId').value;
    const btn  = document.getElementById('spaceSubmitBtn');
    const name = document.getElementById('spaceName').value.trim();
    const desc = document.getElementById('spaceDescription').value.trim();

    setLoading(btn, true);
    clearFormErrors('spaceForm');

    const url    = id ? `${BASE_URL}spaces/${id}` : `${BASE_URL}spaces`;
    const method = id ? 'PUT' : 'POST';

    ajaxRequest(url, method, { name, description: desc })
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('spaceModal')).hide();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                if (data.errors) showFormErrors(data.errors, { name: 'spaceNameError' });
                else showToast(data.message || 'Gagal menyimpan.', 'danger');
            }
        })
        .catch(() => showToast('Terjadi kesalahan.', 'danger'))
        .finally(() => setLoading(btn, false));
});

// --- Confirm Delete ---
document.getElementById('confirmDeleteSpace').addEventListener('click', function () {
    if (!deleteSpaceId) return;
    const btn = this;
    setLoading(btn, true);

    ajaxRequest(`${BASE_URL}spaces/${deleteSpaceId}`, 'DELETE')
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('deleteSpaceModal')).hide();
                const card = document.getElementById(`spaceCard${deleteSpaceId}`);
                card?.remove();
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
