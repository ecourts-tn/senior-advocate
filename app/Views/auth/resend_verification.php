<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-envelope-check"></i></div>
            <h2 class="auth-title">Verify your email</h2>
            <p class="auth-sub">Request a new verification link</p>
        </div>
        <div class="card-body">
            <p class="small text-muted mb-3">
                Enter the email address you used at registration. If the account exists and is not yet verified,
                we will send a new verification link.
            </p>
            <?= form_open('resend-verification', [
                'data-mail-loader'      => '1',
                'data-mail-loader-text' => 'Sending the verification email…',
            ]) ?>
            <div class="mb-3">
                <label class="form-label required" for="email">Email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= esc($email ?? old('email')) ?>" required autocomplete="username"
                           placeholder="name@example.com">
                </div>
            </div>
            <?= view('partials/captcha_field', ['fieldId' => 'resendVerifyCaptcha']) ?>
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-send me-1"></i> Send verification link
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center text-muted">
                <a href="<?= base_url('login') ?>">Back to login</a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
