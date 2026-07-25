<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="auth-wrap auth-wide">
    <div class="card card-mhc auth-card">
        <div class="card-header">
            <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
            <h2 class="auth-title">Advocate Registration</h2>
            <p class="auth-sub">Create an account to apply for Senior Advocate designation</p>
        </div>
        <div class="card-body">
            <div class="alert alert-warning py-2 small mb-3">
                <i class="bi bi-exclamation-circle me-1"></i>
                Register with the <strong>same full name</strong> as on your Enrolment Certificate. Abbreviated names will not be accepted.
            </div>

            <?= form_open('register') ?>
            <div class="mb-3">
                <label class="form-label required" for="name">Full Name (as per Enrolment Certificate)</label>
                <input type="text" name="name" id="name" class="form-control"
                       value="<?= old('name') ?>" required autocomplete="name"
                       placeholder="As printed on enrolment certificate">
            </div>
            <div class="mb-3">
                <label class="form-label required" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= old('email') ?>" required autocomplete="email"
                       placeholder="name@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label required" for="mobile">Mobile</label>
                <input type="text" name="mobile" id="mobile" class="form-control"
                       value="<?= old('mobile') ?>" required maxlength="15"
                       autocomplete="tel" placeholder="10-digit mobile number">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                           required minlength="8" autocomplete="new-password"
                           placeholder="Min. 8 characters">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required" for="password_confirm">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                           required autocomplete="new-password" placeholder="Re-enter password">
                </div>
            </div>
            <?= view('partials/captcha_field', ['fieldId' => 'registerCaptcha']) ?>
            <button type="submit" class="btn btn-mhc w-100 py-2">
                <i class="bi bi-check2-circle me-1"></i> Create Account
            </button>
            <?= form_close() ?>

            <p class="mt-3 mb-0 small text-center">
                Already registered? <a href="<?= base_url('login') ?>" class="fw-semibold">Login</a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
