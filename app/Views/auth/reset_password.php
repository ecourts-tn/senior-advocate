<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-key"></i></div>
            <h2 class="auth-title">Reset password</h2>
            <p class="auth-sub">Set a new password for <?= esc($email) ?></p>
        </div>
        <div class="card-body">
            <?= form_open('reset-password/' . $token) ?>
            <div class="mb-3">
                <label class="form-label required" for="password">New password</label>
                <input type="password" name="password" id="password" class="form-control"
                       required minlength="8" autocomplete="new-password"
                       placeholder="Min. 8 characters">
            </div>
            <div class="mb-3">
                <label class="form-label required" for="password_confirm">Confirm new password</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                       required minlength="8" autocomplete="new-password"
                       placeholder="Re-enter new password">
            </div>
            <?= view('partials/captcha_field', ['fieldId' => 'resetCaptcha']) ?>
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-check2-circle me-1"></i> Update password
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center">
                <a href="<?= base_url('login') ?>">Back to login</a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
