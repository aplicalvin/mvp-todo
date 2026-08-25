<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('sessions') ?>">Riwayat Sesi</a></li>
            <li class="breadcrumb-item active">Detail Sesi #<?= $session['id'] ?></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Detail Sesi</h1>
            <p class="page-subtitle text-muted">
                <?= esc($session['project_name']) ?> &bull;
                <?= esc($session['space_name']) ?>
            </p>
        </div>
        <div class="page-actions">
            <a href="<?= base_url("sessions/create?project_id=" . $session['project_id']) ?>"
               class="btn btn-primary">
                <i class="bi bi-play-circle-fill me-2"></i>Sesi Baru di Proyek Ini
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Session Metadata -->
        <div class="col-12 col-lg-4">
            <div class="card card-app h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Sesi</h5>
                </div>
                <div class="card-body">
                    <dl class="session-detail-list">
                        <dt>Proyek</dt>
                        <dd>
                            <a href="<?= base_url("projects/{$session['project_id']}/tickets") ?>">
                                <?= esc($session['project_name']) ?>
                            </a>
                        </dd>

                        <dt>Space</dt>
                        <dd><?= esc($session['space_name']) ?></dd>

                        <dt>Tipe Proyek</dt>
                        <dd>
                            <span class="badge badge-project-type badge-<?= $session['project_type'] ?>">
                                <?= ucfirst($session['project_type']) ?>
                            </span>
                        </dd>

                        <dt>Mulai</dt>
                        <dd>
                            <?= date('d M Y, H:i', strtotime($session['start_time'])) ?>
                        </dd>

                        <dt>Selesai</dt>
                        <dd>
                            <?= $session['end_time']
                                ? date('d M Y, H:i', strtotime($session['end_time']))
                                : '<span class="text-muted">—</span>' ?>
                        </dd>

                        <dt>Durasi</dt>
                        <dd>
                            <?php if ($session['duration_minutes']): ?>
                            <span class="badge bg-primary-subtle text-primary fs-6">
                                <?= $session['duration_minutes'] ?> menit
                            </span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif ?>
                        </dd>

                        <dt>Status</dt>
                        <dd>
                            <?php
                                $sc = ['active'=>'warning','completed'=>'success','cancelled'=>'danger'][$session['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $sc ?> fs-6">
                                <?= ucfirst($session['status']) ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Ticket Completion List -->
        <div class="col-12 col-lg-8">
            <div class="card card-app">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2 text-success"></i>Tiket dalam Sesi
                    </h5>
                    <?php
                        $doneCount = count(array_filter($sessionTickets, fn($t) => $t['status'] === 'completed'));
                    ?>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success-subtle text-success">
                            <?= $doneCount ?> selesai
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary">
                            <?= count($sessionTickets) ?> total
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($sessionTickets)): ?>
                    <div class="table-responsive">
                        <table class="table table-app mb-0">
                            <thead>
                                <tr>
                                    <th>Tiket</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Waktu Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessionTickets as $st): ?>
                                <?php
                                    $pColor = $priorityColors[$st['priority']] ?? 'secondary';
                                    $isDone = ($st['status'] === 'completed');
                                ?>
                                <tr class="<?= $isDone ? 'table-success-subtle' : '' ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($isDone): ?>
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <?php else: ?>
                                            <i class="bi bi-circle text-muted"></i>
                                            <?php endif ?>
                                            <div>
                                                <div class="fw-semibold <?= $isDone ? 'text-decoration-line-through text-muted' : '' ?>">
                                                    <?= esc($st['title']) ?>
                                                </div>
                                                <?php if ($st['description']): ?>
                                                <div class="text-muted small">
                                                    <?= esc(substr($st['description'], 0, 80)) ?><?= strlen($st['description']) > 80 ? '…' : '' ?>
                                                </div>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $pColor ?>-subtle text-<?= $pColor ?> small">
                                            <?= $st['priority'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $isDone ? 'success' : 'secondary' ?>-subtle text-<?= $isDone ? 'success' : 'secondary' ?>">
                                            <?= $isDone ? 'Selesai' : 'Pending' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isDone && $st['completed_at']): ?>
                                        <span class="text-success small">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= date('H:i:s', strtotime($st['completed_at'])) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif ?>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-inbox"></i>
                        <p class="text-muted">Tidak ada tiket dalam sesi ini.</p>
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
