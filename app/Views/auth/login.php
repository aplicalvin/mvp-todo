<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="auth-body">

<div class="auth-container">
    <!-- Left panel — branding -->
    <div class="auth-brand">
        <div class="auth-brand-content">
            <div class="auth-logo">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <h1 class="auth-brand-title">FocusFlow</h1>
            <p class="auth-brand-subtitle">Kelola tugas, proyek, dan sesi fokus dalam satu platform.</p>

            <div class="auth-features">
                <div class="auth-feature">
                    <i class="bi bi-kanban-fill"></i>
                    <span>Manajemen tiket Eisenhower</span>
                </div>
                <div class="auth-feature">
                    <i class="bi bi-stopwatch-fill"></i>
                    <span>Sesi fokus dengan Pomodoro</span>
                </div>
                <div class="auth-feature">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Rekap dan analitik sesi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right panel — form -->
    <div class="auth-form-panel">
        <div class="auth-form-wrapper">
            <div class="auth-form-header">
                <h2>Selamat Datang Kembali</h2>
                <p class="text-muted">Masuk ke akun Anda untuk melanjutkan.</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif ?>

            <?php if (!empty(session()->getFlashdata('errors'))): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <form action="<?= base_url('login') ?>" method="POST" class="auth-form" id="loginForm">
                <?= csrf_field() ?>

                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= old('email') ?>"
                               placeholder="nama@email.com" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Min. 6 karakter" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg btn-auth" id="loginBtn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Masuk
                    </button>
                </div>

                <p class="text-center text-muted mb-0">
                    Belum punya akun?
                    <a href="<?= base_url('register') ?>" class="auth-link">Daftar sekarang</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('togglePasswordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
</script>
</body>
</html>
