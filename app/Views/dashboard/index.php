<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle text-muted">Selamat datang kembali, <?= esc(session()->get('user_name')) ?>! 👋</p>
        </div>
        <div class="page-actions">
            <a href="<?= base_url('sessions/create') ?>" class="btn btn-primary">
                <i class="bi bi-play-circle-fill me-2"></i>Mulai Sesi Fokus
            </a>
        </div>
    </div>

    <!-- Active Session Alert -->
    <?php if ($active_session): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4 active-session-alert" role="alert">
        <div class="pulse-dot me-3"></div>
        <div class="flex-grow-1">
            <strong>Sesi aktif terdeteksi!</strong>
            Kamu memiliki sesi yang sedang berjalan.
        </div>
        <a href="<?= base_url("sessions/{$active_session['id']}/active") ?>"
           class="btn btn-warning btn-sm ms-3">
            <i class="bi bi-arrow-right-circle me-1"></i>Lanjutkan
        </a>
    </div>
    <?php endif ?>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-list-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $pending_tickets ?></div>
                    <div class="stat-label">Tiket Aktif</div>
                    <div class="stat-sub">Pending & Ongoing</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="bi bi-stopwatch"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $sessions_today ?></div>
                    <div class="stat-label">Sesi Hari Ini</div>
                    <div class="stat-sub"><?= date('d M Y') ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="bi bi-grid-3x3-gap"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $spaces_count ?></div>
                    <div class="stat-label">Spaces</div>
                    <div class="stat-sub">Total space aktif</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-info">
                <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-info">
                    <?php
                        $totalMins = array_sum(array_column($recent_sessions, 'duration_minutes'));
                        $hours = floor($totalMins / 60);
                        $mins  = $totalMins % 60;
                    ?>
                    <div class="stat-value"><?= $hours ?>j <?= $mins ?>m</div>
                    <div class="stat-label">Total Fokus</div>
                    <div class="stat-sub">5 sesi terakhir</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Sessions -->
        <div class="col-12 col-lg-8">
            <div class="card card-app h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Sesi Terbaru
                    </h5>
                    <a href="<?= base_url('sessions') ?>" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recent_sessions)): ?>
                    <div class="table-responsive">
                        <table class="table table-app mb-0">
                            <thead>
                                <tr>
                                    <th>Proyek</th>
                                    <th>Tanggal</th>
                                    <th>Durasi</th>
                                    <th>Tiket Selesai</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sessions as $sess): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($sess['project_name']) ?></div>
                                        <div class="text-muted small"><?= esc($sess['space_name']) ?></div>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d M Y', strtotime($sess['start_time'])) ?>
                                        <br><?= date('H:i', strtotime($sess['start_time'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($sess['duration_minutes']): ?>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <?= $sess['duration_minutes'] ?>m
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">
                                            <?= $sess['tickets_done'] ?> tiket
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = [
                                                'active'    => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                            ][$sess['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>">
                                            <?= ucfirst($sess['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($sess['status'] === 'active'): ?>
                                            <a href="<?= base_url("sessions/{$sess['id']}/active") ?>"
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-play-fill"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url("sessions/{$sess['id']}") ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif ?>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state py-5">
                        <i class="bi bi-stopwatch"></i>
                        <p>Belum ada sesi. <br>Mulai sesi fokus pertamamu!</p>
                        <a href="<?= base_url('sessions/create') ?>" class="btn btn-primary">
                            <i class="bi bi-play-circle me-2"></i>Mulai Sekarang
                        </a>
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <!-- Quick Start -->
        <div class="col-12 col-lg-4">
            <div class="card card-app h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2 text-warning"></i>Quick Start
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($sidebar_tree)): ?>
                        <p class="text-muted small mb-3">Pilih proyek dan langsung mulai sesi:</p>
                        <div class="quick-start-list">
                            <?php foreach (array_slice($sidebar_tree, 0, 3) as $space): ?>
                                <?php foreach (array_slice($space['projects'] ?? [], 0, 2) as $project): ?>
                                <a href="<?= base_url('sessions/create?project_id=' . $project['id']) ?>"
                                   class="quick-start-item">
                                    <div class="quick-start-icon">
                                        <i class="bi bi-kanban"></i>
                                    </div>
                                    <div class="quick-start-info">
                                        <div class="quick-start-name"><?= esc($project['name']) ?></div>
                                        <div class="quick-start-space"><?= esc($space['name']) ?></div>
                                    </div>
                                    <i class="bi bi-play-circle-fill text-primary"></i>
                                </a>
                                <?php endforeach ?>
                            <?php endforeach ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state py-4">
                            <i class="bi bi-folder-plus"></i>
                            <p class="small">Buat Space dan Proyek terlebih dahulu.</p>
                            <button class="btn btn-sm btn-primary" id="btnCreateSpaceQuick">
                                <i class="bi bi-plus me-1"></i>Buat Space
                            </button>
                        </div>
                    <?php endif ?>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('sessions/create') ?>" class="btn btn-primary w-100">
                        <i class="bi bi-play-circle-fill me-2"></i>Buat Sesi Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Trigger space modal from dashboard empty state
    document.getElementById('btnCreateSpaceQuick')?.addEventListener('click', () => {
        document.getElementById('spaceModalTitleText').textContent = 'Buat Space Baru';
        document.getElementById('spaceId').value = '';
        document.getElementById('spaceForm').reset();
        new bootstrap.Modal(document.getElementById('spaceModal')).show();
    });

    document.getElementById('btnCreateFirstSpace')?.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('spaceModalTitleText').textContent = 'Buat Space Baru';
        document.getElementById('spaceId').value = '';
        document.getElementById('spaceForm').reset();
        new bootstrap.Modal(document.getElementById('spaceModal')).show();
    });
</script>
<?= $this->endSection() ?>
