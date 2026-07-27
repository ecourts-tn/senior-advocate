<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-shield-lock"></i></div>
            <h2 class="auth-title">Sign in</h2>
            <p class="auth-sub">Advocate / Reviewer / Administrator login</p>
        </div>
        <div class="card-body">
            <?= form_open('login') ?>
            <div class="mb-3">
                <label class="form-label required" for="email">Email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= old('email') ?>" required autocomplete="username"
                           placeholder="name@example.com">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label required" for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" id="password" class="form-control"
                           required autocomplete="current-password" placeholder="Enter password">
                </div>
            </div>
            <?= view('partials/captcha_field', ['fieldId' => 'loginCaptcha']) ?>
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= base_url('forgot-password') ?>" class="small">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center text-muted">
                New advocate?
                <a href="<?= base_url('register') ?>" class="fw-semibold">Register here</a>
            </p>
        </div>
    </div>
    <p class="text-center small text-muted mt-3 mb-0">
        <a href="<?= base_url('instructions') ?>">Read instructions</a>
        before submitting an application.
    </p>
</div>

<?= $this->endSection() ?>
