<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$editWindow = $editWindow ?? \App\Models\ApplicationModel::editWindowInfo();
?>

<?php if (! empty($editWindow['open'])): ?>
    <div class="alert alert-info alert-dismissible fade show mb-4 landing-alert" role="alert">
        <div class="d-flex gap-2">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1" aria-hidden="true"></i>
            <div>
                <strong class="d-block mb-1">Application edit window is open</strong>
                <?= esc($editWindow['message'] ?: 'The Permanent Secretariat has opened a limited window to correct and resubmit applications.') ?>
                <?php if (! empty($editWindow['from']) || ! empty($editWindow['to'])): ?>
                    <div class="small mt-2">
                        <?php if (! empty($editWindow['from'])): ?>
                            <span class="me-3"><strong>Opens:</strong>
                                <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($editWindow['from'])) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (! empty($editWindow['to'])): ?>
                            <span><strong>Closes:</strong>
                                <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($editWindow['to'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="small mt-2">
                    Sign in with your advocate account to edit and resubmit your application.
                </div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="hero-banner hero-banner-login">
    <div class="row g-4 align-items-center">
        <div class="col-lg-7">
            <div class="eyebrow">High Court of Madras · Official Portal</div>
            <h1>Designation of Senior Advocates</h1>
            <p class="subtitle">
                Sign in to your account to submit/view application. <br/>
                <a href="<?= base_url('rules') ?>" class="text-white text-decoration-underline fw-semibold">
                    Rules for Designation of Senior Advocates, 2026
                </a>.
            </p>
            <!-- <div class="hero-meta">
                <span class="hero-chip"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Proforma Sl.&nbsp;No.&nbsp;1–24</span>
                <span class="hero-chip"><i class="bi bi-files" aria-hidden="true"></i> Formats L-1 to L-4</span>
                <span class="hero-chip"><i class="bi bi-hash" aria-hidden="true"></i> MHC/SSA/2026/####</span>
                <span class="hero-chip"><i class="bi bi-shield-lock" aria-hidden="true"></i> Secure · Audited</span>
            </div> -->
            <div class="hero-cta">
                <a href="<?= base_url('register') ?>" class="btn btn-gold btn-lg">
                    <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Register as Advocate
                </a>
                <a href="<?= base_url('instructions') ?>" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-journal-bookmark me-1" aria-hidden="true"></i>Read Instructions
                </a>
            </div>

            <?php if (! empty($portalNotifications)): ?>
                <div class="hero-side-card landing-notice" role="region" aria-label="Latest official notifications">
                    <div class="hero-side-label">
                        <i class="bi bi-megaphone me-1" aria-hidden="true"></i> Latest official notifications
                    </div>
                    <ul class="landing-notice-list">
                        <?php foreach ($portalNotifications as $n): ?>
                            <li>
                                <a href="<?= base_url('notifications/document/' . (int) $n['id']) ?>"
                                   target="_blank" rel="noopener">
                                    <?= esc($n['notification_number'] ?? 'Notification') ?>
                                </a>
                                <?php if (! empty($n['notification_date'])): ?>
                                    <span class="notif-date">
                                        <?= esc(date('d-m-Y', strtotime($n['notification_date']))) ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= base_url('notifications') ?>" class="landing-notice-link">View all notifications</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <div class="card card-mhc auth-card login-card">
                <div class="card-header">
                    <div class="auth-icon"><i class="bi bi-shield-lock"></i></div>
                    <h2 class="auth-title">Sign in</h2>
                    <p class="auth-sub">Advocate / Administrator login</p>
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
                        <div class="input-group password-toggle-group">
                            <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control"
                                   required autocomplete="current-password" placeholder="Enter password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn"
                                    data-password-toggle="1"
                                    aria-label="Show password" aria-pressed="false" title="Show password">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                                <span class="visually-hidden">Show password</span>
                            </button>
                        </div>
                    </div>
                    <?= view('partials/captcha_field', ['fieldId' => 'loginCaptcha']) ?>
                    <div class="d-flex justify-content-between mb-3">
                        <a href="<?= base_url('resend-verification') ?>" class="small">Resend verification email</a>
                        <a href="<?= base_url('forgot-password') ?>" class="small">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-mhc w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>
                    <?= form_close() ?>

                    <?php if (session()->getFlashdata('unverified_email')): ?>
                        <div class="alert alert-warning mt-3 mb-0 small" role="alert">
                            Email not verified?
                            <a href="<?= base_url('resend-verification') ?>" class="fw-semibold">Resend verification link</a>
                        </div>
                    <?php endif; ?>

                    <p class="mt-3 mb-0 small text-center text-muted">
                        New advocate?
                        <a href="<?= base_url('register') ?>" class="fw-semibold">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <div class="row g-3 g-md-4 landing-strip">
    <div class="col-md-6 col-lg-4">
        <div class="card card-mhc h-100">
            <div class="card-header"><i class="bi bi-people me-1" aria-hidden="true"></i> Who should apply</div>
            <div class="card-body">
                <ul class="home-checklist mb-0">
                    <li>Advocates enrolled with a Bar Council and practising before the High Court of Madras / courts in Tamil Nadu as notified.</li>
                    <li>Applicants who satisfy the standing, integrity and ability criteria under the Approved Rules, 2026.</li>
                    <li>Applicants ready to furnish complete particulars for Sl.&nbsp;No.&nbsp;1–24 and Formats L-1 to L-4 with supporting documents.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card card-mhc h-100">
            <div class="card-header"><i class="bi bi-list-check me-1" aria-hidden="true"></i> Before you begin</div>
            <div class="card-body">
                <ul class="home-checklist mb-0">
                    <li>Register with your full name exactly as on the enrolment certificate — abbreviated names are not accepted.</li>
                    <li>Keep data for Formats L-1 to L-4 ready and scan photo, signature &amp; enrolment certificate to the prescribed limits.</li>
                    <li>Plan time to file the paper book at the Permanent Secretariat after online submission.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card card-mhc home-help-card h-100">
            <div class="card-body">
                <div class="feature-icon"><i class="bi bi-question-circle" aria-hidden="true"></i></div>
                <h3 class="h6">Need help?</h3>
                <p class="small text-muted mb-3">Read the full instructions or contact the Permanent Secretariat before you begin.</p>
                <a href="<?= base_url('policy/help') ?>" class="small fw-semibold">Help &amp; contact</a>
                <span class="text-muted small" aria-hidden="true"> · </span>
                <a href="<?= base_url('instructions') ?>" class="small fw-semibold">Instructions</a>
            </div>
        </div>
    </div>
</div> -->

<!-- <p class="text-center small text-muted mt-4 mb-0">
    <a href="<?= base_url('rules') ?>">Rules, 2026</a>
    ·
    <a href="<?= base_url('instructions') ?>">Instructions</a>
    before submitting an application.
</p> -->

<?= $this->endSection() ?>
