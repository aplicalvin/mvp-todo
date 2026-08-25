<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="page-header mb-4">
        <div>
            <h1 class="page-title"><i class="bi bi-play-circle-fill me-2 text-success"></i>Mulai Sesi Fokus</h1>
            <p class="page-subtitle text-muted">Pilih proyek dan mulai sesi kerja Anda.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card card-app">
                <div class="card-body p-4">

                    <?php if (empty($spaces)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-folder-x"></i>
                        <h5>Belum Ada Space & Proyek</h5>
                        <p class="text-muted small">Buat space dan proyek terlebih dahulu sebelum memulai sesi.</p>
                        <a href="<?= base_url('spaces') ?>" class="btn btn-primary">
                            <i class="bi bi-folder-plus me-2"></i>Buat Space
                        </a>
                    </div>
                    <?php else: ?>

                    <form action="<?= base_url('sessions') ?>" method="POST" id="startSessionForm">
                        <?= csrf_field() ?>

                        <!-- Step 1: Select Space -->
                        <div class="session-step mb-4">
                            <div class="session-step-number">1</div>
                            <div class="session-step-content">
                                <label for="space_id" class="form-label fw-semibold">Pilih Space</label>
                                <select class="form-select form-select-lg" id="space_id" name="space_id" required>
                                    <option value="">— Pilih Space —</option>
                                    <?php foreach ($spaces as $space): ?>
                                    <option value="<?= $space['id'] ?>"
                                        <?= (isset($_GET['space_id']) && $_GET['space_id'] == $space['id']) ? 'selected' : '' ?>>
                                        <?= esc($space['name']) ?>
                                    </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>

                        <!-- Step 2: Select Project (cascading) -->
                        <div class="session-step mb-4">
                            <div class="session-step-number">2</div>
                            <div class="session-step-content">
                                <label for="project_id" class="form-label fw-semibold">Pilih Proyek</label>
                                <select class="form-select form-select-lg" id="project_id" name="project_id"
                                        required disabled>
                                    <option value="">— Pilih proyek dahulu —</option>
                                </select>
                                <div class="spinner-border spinner-border-sm text-primary mt-2 d-none"
                                     id="projectLoadingSpinner" role="status"></div>
                            </div>
                        </div>

                        <!-- Session Duration Hint -->
                        <div class="pomodoro-hint mb-4">
                            <div class="pomodoro-hint-icon">
                                <i class="bi bi-stopwatch-fill"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-semibold">Timer Pomodoro: 25 menit</p>
                                <p class="mb-0 text-muted small">
                                    Sesi akan otomatis memuat semua tiket aktif dari proyek yang dipilih.
                                    Anda bisa menandai tiket selesai selama sesi berlangsung.
                                </p>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" id="startBtn" disabled>
                                <i class="bi bi-play-circle-fill me-2"></i>Mulai Sesi Fokus
                            </button>
                        </div>
                    </form>

                    <?php endif ?>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card card-app mt-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Tips Sesi Fokus</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                            <span class="small">Fokus pada satu proyek per sesi untuk hasil maksimal.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                            <span class="small">Centang tiket saat selesai — waktu penyelesaian otomatis tercatat.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-0">
                            <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                            <span class="small">Gunakan tombol "+5 Menit" jika butuh waktu ekstra.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const BASE_URL = '<?= base_url() ?>';

const spaceSelect   = document.getElementById('space_id');
const projectSelect = document.getElementById('project_id');
const startBtn      = document.getElementById('startBtn');
const spinner       = document.getElementById('projectLoadingSpinner');

// Pre-select from query string
const preProjectId = '<?= isset($_GET['project_id']) ? (int) $_GET['project_id'] : '' ?>';

spaceSelect?.addEventListener('change', function () {
    const spaceId = this.value;
    projectSelect.innerHTML = '<option value="">Memuat...</option>';
    projectSelect.disabled = true;
    startBtn.disabled = true;

    if (!spaceId) {
        projectSelect.innerHTML = '<option value="">— Pilih proyek dahulu —</option>';
        return;
    }

    spinner?.classList.remove('d-none');

    fetch(`${BASE_URL}api/spaces/${spaceId}/projects`)
        .then(r => r.json())
        .then(data => {
            spinner?.classList.add('d-none');
            projectSelect.innerHTML = '<option value="">— Pilih Proyek —</option>';

            if (data.success && data.data.length > 0) {
                data.data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.name} (${p.type})`;
                    if (preProjectId && preProjectId == p.id) opt.selected = true;
                    projectSelect.appendChild(opt);
                });
                projectSelect.disabled = false;
                if (preProjectId) startBtn.disabled = false;
            } else {
                projectSelect.innerHTML = '<option value="">Tidak ada proyek di space ini</option>';
            }
        })
        .catch(() => {
            spinner?.classList.add('d-none');
            projectSelect.innerHTML = '<option value="">Gagal memuat proyek</option>';
        });
});

projectSelect?.addEventListener('change', function () {
    startBtn.disabled = !this.value;
});

// Auto-trigger if space pre-selected
if (spaceSelect?.value) {
    spaceSelect.dispatchEvent(new Event('change'));
}
</script>
<?= $this->endSection() ?>
