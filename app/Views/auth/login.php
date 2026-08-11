<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$editWindow = $editWindow ?? \App\Models\ApplicationModel::editWindowInfo();
if (! empty($editWindow['open'])):
?>
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
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

<?php if (! empty($portalNotifications)): ?>
    <div class="alert alert-light border mb-4" role="region" aria-label="Official notifications">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
            <strong><i class="bi bi-megaphone me-1" aria-hidden="true"></i> Official notifications</strong>
            <a href="<?= base_url('notifications') ?>" class="small">View all</a>
        </div>
        <ul class="mb-0 ps-3">
            <?php foreach ($portalNotifications as $n): ?>
                <li class="mb-1">
                    <a href="<?= base_url('notifications/document/' . (int) $n['id']) ?>"
                       target="_blank" rel="noopener">
                        <?= esc($n['notification_number'] ?? 'Notification') ?>
                    </a>
                    <?php if (! empty($n['notification_date'])): ?>
                        <span class="text-muted small">
                            (<?= esc(date('d-m-Y', strtotime($n['notification_date']))) ?>)
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="auth-wrap">
    <div class="card card-mhc auth-card">
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
    <p class="text-center small text-muted mt-3 mb-0">
        <a href="<?= base_url('rules') ?>">Rules, 2026</a>
        ·
        <a href="<?= base_url('instructions') ?>">Instructions</a>
        before submitting an application.
    </p>
</div>

<?= $this->endSection() ?>
