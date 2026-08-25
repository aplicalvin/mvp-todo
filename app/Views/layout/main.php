<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Personal Task Management System — kelola tugas, proyek, dan sesi fokus Anda.">
    <title><?= esc($title ?? 'Task Manager') ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-name"  content="<?= csrf_token() ?>">
</head>
<body class="app-body">

<!-- ===================== SIDEBAR ===================== -->
<nav id="sidebar" class="sidebar">
    <!-- Logo / Brand -->
    <div class="sidebar-brand">
        <a href="<?= base_url('/') ?>" class="brand-link">
            <div class="brand-icon">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <span class="brand-text">FocusFlow</span>
        </a>
        <button id="sidebarToggle" class="sidebar-toggle d-lg-none" type="button">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?= strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1)) ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?= esc(session()->get('user_name') ?? 'User') ?></div>
            <div class="user-email"><?= esc(session()->get('user_email') ?? '') ?></div>
        </div>
    </div>

    <!-- Nav Links -->
    <ul class="sidebar-nav">
        <li class="nav-section">Menu Utama</li>
        <li class="nav-item">
            <a class="nav-link <?= uri_string() === '' || uri_string() === 'dashboard' ? 'active' : '' ?>"
               href="<?= base_url('/') ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= str_starts_with(uri_string(), 'sessions') ? 'active' : '' ?>"
               href="<?= base_url('sessions/create') ?>">
                <i class="bi bi-play-circle-fill"></i>
                <span>Mulai Sesi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= uri_string() === 'sessions' ? 'active' : '' ?>"
               href="<?= base_url('sessions') ?>">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Sesi</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= uri_string() === 'spaces' ? 'active' : '' ?>"
               href="<?= base_url('spaces') ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Semua Space</span>
            </a>
        </li>

        <!-- Spaces Tree -->
        <li class="nav-section mt-2">
            Spaces & Proyek
            <button class="btn-add-space-mini" id="btnAddSpaceMini" title="Tambah Space">
                <i class="bi bi-plus"></i>
            </button>
        </li>

        <?php if (!empty($sidebar_tree)): ?>
            <?php foreach ($sidebar_tree as $space): ?>
            <li class="nav-item">
                <a class="nav-link nav-space-link" data-bs-toggle="collapse"
                   href="#spaceCollapse<?= $space['id'] ?>" role="button"
                   aria-expanded="false">
                    <i class="bi bi-folder2"></i>
                    <span><?= esc($space['name']) ?></span>
                    <i class="bi bi-chevron-down ms-auto collapse-icon"></i>
                </a>
                <div class="collapse" id="spaceCollapse<?= $space['id'] ?>">
                    <ul class="sidebar-subnav">
                        <?php if (!empty($space['projects'])): ?>
                            <?php foreach ($space['projects'] as $project): ?>
                            <li>
                                <a href="<?= base_url("projects/{$project['id']}/tickets") ?>"
                                   class="subnav-link <?= (isset($project) && strpos(uri_string(), "projects/{$project['id']}") !== false) ? 'active' : '' ?>">
                                    <i class="bi bi-kanban <?= $project['type'] === 'routine' ? 'text-success' : 'text-warning' ?>"></i>
                                    <span><?= esc($project['name']) ?></span>
                                    <span class="badge-type"><?= $project['type'] === 'routine' ? 'R' : 'S' ?></span>
                                </a>
                            </li>
                            <?php endforeach ?>
                        <?php else: ?>
                            <li class="subnav-empty">Belum ada proyek</li>
                        <?php endif ?>
                        <li>
                            <a href="<?= base_url("spaces/{$space['id']}/projects") ?>"
                               class="subnav-link subnav-manage">
                                <i class="bi bi-gear"></i>
                                <span>Kelola Proyek</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endforeach ?>
        <?php else: ?>
            <li class="nav-item">
                <div class="nav-empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada space. <a href="#" id="btnCreateFirstSpace">Buat sekarang!</a></p>
                </div>
            </li>
        <?php endif ?>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= base_url('logout') ?>" class="logout-link">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>

<!-- ===================== MAIN CONTENT ===================== -->
<div id="mainContent" class="main-content">

    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="navbar-left">
            <button id="sidebarOpen" class="btn-icon d-lg-none">
                <i class="bi bi-list"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-flex align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Home</a></li>
                    <?php if (isset($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                        <li class="breadcrumb-item <?= $crumb['active'] ? 'active' : '' ?>">
                            <?php if (!$crumb['active'] && isset($crumb['url'])): ?>
                                <a href="<?= $crumb['url'] ?>"><?= esc($crumb['label']) ?></a>
                            <?php else: ?>
                                <?= esc($crumb['label']) ?>
                            <?php endif ?>
                        </li>
                        <?php endforeach ?>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?= esc($title ?? '') ?></li>
                    <?php endif ?>
                </ol>
            </nav>
        </div>
        <div class="navbar-right">
            <a href="<?= base_url('sessions/create') ?>" class="btn btn-sm btn-primary btn-start-session">
                <i class="bi bi-play-fill"></i>
                <span class="d-none d-sm-inline">Mulai Sesi</span>
            </a>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="flash-container px-4 pt-3">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?= session()->getFlashdata('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= session()->getFlashdata('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>
    </div>

    <!-- Page Content -->
    <main class="page-content">
        <?= $this->renderSection('content') ?>
    </main>
</div>

<!-- ===================== GLOBAL MODALS ===================== -->

<!-- Space Modal (Create/Edit) -->
<div class="modal fade" id="spaceModal" tabindex="-1" aria-labelledby="spaceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="spaceModalLabel">
                    <i class="bi bi-folder-plus me-2"></i>
                    <span id="spaceModalTitleText">Buat Space Baru</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="spaceForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="spaceId" name="space_id">
                    <div class="mb-3">
                        <label for="spaceName" class="form-label">Nama Space <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="spaceName" name="name"
                               placeholder="e.g., Kantor, LabKom, Personal" required>
                        <div class="invalid-feedback" id="spaceNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="spaceDescription" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="spaceDescription" name="description"
                                  rows="3" placeholder="Opsional — deskripsi singkat tentang space ini"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="spaceSubmitBtn">
                        <span class="btn-text">Simpan</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Custom JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
