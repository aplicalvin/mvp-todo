<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Daftar') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="auth-body">

<div class="auth-container">
    <!-- Left panel -->
    <div class="auth-brand">
        <div class="auth-brand-content">
            <div class="auth-logo">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <h1 class="auth-brand-title">FocusFlow</h1>
            <p class="auth-brand-subtitle">Mulai perjalanan produktivitas Anda hari ini.</p>

            <div class="auth-features">
                <div class="auth-feature">
                    <i class="bi bi-check2-circle"></i>
                    <span>Gratis selamanya</span>
                </div>
                <div class="auth-feature">
                    <i class="bi bi-shield-check"></i>
                    <span>Data aman dan terenkripsi</span>
                </div>
                <div class="auth-feature">
                    <i class="bi bi-lightning-charge"></i>
                    <span>Mulai dalam 30 detik</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right panel — form -->
    <div class="auth-form-panel">
        <div class="auth-form-wrapper">
            <div class="auth-form-header">
                <h2>Buat Akun Baru</h2>
                <p class="text-muted">Isi form di bawah untuk mendaftar.</p>
            </div>

            <?php if (!empty(session()->getFlashdata('errors'))): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif ?>

            <form action="<?= base_url('register') ?>" method="POST" class="auth-form" id="registerForm">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= old('name') ?>"
                               placeholder="Nama kamu" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= old('email') ?>"
                               placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="mb-3">
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

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password_confirm"
                               name="password_confirm" placeholder="Ulangi password" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg btn-auth">
                        <i class="bi bi-person-plus me-2"></i>
                        Daftar Sekarang
                    </button>
                </div>

                <p class="text-center text-muted mb-0">
                    Sudah punya akun?
                    <a href="<?= base_url('login') ?>" class="auth-link">Masuk di sini</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
