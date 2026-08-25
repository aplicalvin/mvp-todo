<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="page-header mb-4">
        <div>
            <h1 class="page-title"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Sesi</h1>
            <p class="page-subtitle text-muted">Semua sesi fokus yang telah kamu lakukan.</p>
        </div>
        <div class="page-actions">
            <a href="<?= base_url('sessions/create') ?>" class="btn btn-primary">
                <i class="bi bi-play-circle-fill me-2"></i>Sesi Baru
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <?php
        $totalSessions  = count($sessions);
        $totalMinutes   = array_sum(array_column($sessions, 'duration_minutes'));
        $totalDone      = array_sum(array_column($sessions, 'tickets_done'));
        $completedSessions = count(array_filter($sessions, fn($s) => $s['status'] === 'completed'));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="mini-stat">
                <div class="mini-stat-value"><?= $totalSessions ?></div>
                <div class="mini-stat-label">Total Sesi</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="mini-stat text-success">
                <div class="mini-stat-value"><?= $completedSessions ?></div>
                <div class="mini-stat-label">Selesai</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="mini-stat text-primary">
                <div class="mini-stat-value">
                    <?= floor($totalMinutes / 60) ?>j <?= $totalMinutes % 60 ?>m
                </div>
                <div class="mini-stat-label">Total Waktu Fokus</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="mini-stat text-warning">
                <div class="mini-stat-value"><?= $totalDone ?></div>
                <div class="mini-stat-label">Tiket Diselesaikan</div>
            </div>
        </div>
    </div>

    <!-- Sessions Table -->
    <?php if (!empty($sessions)): ?>
    <div class="card card-app">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-app mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Space / Proyek</th>
                            <th>Tipe</th>
                            <th>Durasi</th>
                            <th>Tiket Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $sess): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= date('d M Y', strtotime($sess['start_time'])) ?></div>
                                <div class="text-muted small">
                                    <?= date('H:i', strtotime($sess['start_time'])) ?>
                                    <?php if ($sess['end_time']): ?>
                                    — <?= date('H:i', strtotime($sess['end_time'])) ?>
                                    <?php endif ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($sess['project_name']) ?></div>
                                <div class="text-muted small"><?= esc($sess['space_name']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-project-type badge-<?= $sess['project_type'] ?>">
                                    <?= ucfirst($sess['project_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($sess['duration_minutes']): ?>
                                <span class="badge bg-primary-subtle text-primary">
                                    <?= $sess['duration_minutes'] ?> menit
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif ?>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= $sess['tickets_done'] ?> tiket
                                </span>
                            </td>
                            <td>
                                <?php
                                    $sc = ['active' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'][$sess['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $sc ?>-subtle text-<?= $sc ?>">
                                    <?= ucfirst($sess['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($sess['status'] === 'active'): ?>
                                <a href="<?= base_url("sessions/{$sess['id']}/active") ?>"
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-play-fill"></i> Lanjutkan
                                </a>
                                <?php else: ?>
                                <a href="<?= base_url("sessions/{$sess['id']}") ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                                <?php endif ?>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-hourglass"></i>
        <h4>Belum Ada Sesi</h4>
        <p class="text-muted">Mulai sesi fokus pertamamu untuk melihat riwayat di sini.</p>
        <a href="<?= base_url('sessions/create') ?>" class="btn btn-primary">
            <i class="bi bi-play-circle-fill me-2"></i>Mulai Sesi Sekarang
        </a>
    </div>
    <?php endif ?>
</div>

<?= $this->endSection() ?>
