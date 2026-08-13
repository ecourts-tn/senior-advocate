<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-unlock"></i></div>
            <h2 class="auth-title">Unlock your account</h2>
            <p class="auth-sub">Request a one-time unlock link by email</p>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Your account was locked after several unsuccessful sign-in attempts.
                This protects the portal from unauthorised access.
            </p>
            <p class="small text-muted mb-3">
                Enter the registered email address and complete the security check.
                We will send a one-time unlock link to that address. The link is valid for
                <strong>1 hour</strong> and can be used only once. Check your inbox and spam folder.
                No email is sent until you click the button below.
            </p>
            <?= form_open('request-unlock') ?>
            <div class="mb-3">
                <label class="form-label required" for="email">Registered email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= esc($email ?? old('email')) ?>" required autocomplete="username"
                           placeholder="name@example.com">
                </div>
            </div>
            <?= view('partials/captcha_field', ['fieldId' => 'unlockAccountCaptcha']) ?>
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-send me-1"></i> Send unlock link
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center text-muted">
                <a href="<?= base_url('login') ?>">Back to login</a>
                ·
                <a href="<?= base_url('forgot-password') ?>">Forgot password?</a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
