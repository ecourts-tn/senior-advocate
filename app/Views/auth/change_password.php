<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-shield-lock"></i></div>
            <h2 class="auth-title">Change password</h2>
            <p class="auth-sub">Update the password for your account</p>
        </div>
        <div class="card-body">
            <?= form_open('change-password') ?>
            <div class="mb-3">
                <label class="form-label required" for="current_password">Current password</label>
                <input type="password" name="current_password" id="current_password" class="form-control"
                       required autocomplete="current-password" placeholder="Your current password">
            </div>
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
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-check2-circle me-1"></i> Save new password
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center text-muted">
                Choose a strong password you do not use on other sites.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
