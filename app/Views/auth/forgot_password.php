<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-envelope-exclamation"></i></div>
            <h2 class="auth-title">Forgot password</h2>
            <p class="auth-sub">Enter your registered email to receive a reset link</p>
        </div>
        <div class="card-body">
            <p class="small text-muted mb-3">
                We will send a secure link to reset your password. The link expires in <strong>1 hour</strong>.
            </p>
            <?= form_open('forgot-password', [
                'data-mail-loader'      => '1',
                'data-mail-loader-text' => 'Sending the password reset email…',
            ]) ?>
            <div class="mb-3">
                <label class="form-label required" for="email">Registered email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= old('email') ?>" required autocomplete="username"
                           placeholder="name@example.com">
                </div>
            </div>
            <?= view('partials/captcha_field', ['fieldId' => 'forgotCaptcha']) ?>
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-send me-1"></i> Send reset link
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center">
                <a href="<?= base_url('login') ?>">Back to login</a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
