<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Portal for Designation of Senior Advocates — High Court of Madras. GIGW 3.0 aligned online application portal.">
    <meta name="keywords" content="Madras High Court, Senior Advocate, Designation, SSA Portal, GIGW">
    <meta name="theme-color" content="#0f2340">
    <title><?= esc($title ?? 'SSA Portal') ?> | Madras High Court</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/app.css') ?: time() ?>" rel="stylesheet">
    <link rel="icon" href="<?= base_url('assets/img/logo.svg') ?>" type="image/svg+xml">
</head>
<?php
$u = current_user();
$site = $site ?? config(\Config\Site::class);
?>
<body>
<!-- GIGW / WCAG: skip link -->
<a class="skip-link" href="#main-content">Skip to main content</a>

<!-- Accessibility toolbar (GIGW / RPwD Act §42) -->
<div class="a11y-bar no-print" role="region" aria-label="Accessibility options">
    <div class="container a11y-bar-inner">
        <div class="a11y-left">
            <span class="a11y-label"><i class="bi bi-universal-access" aria-hidden="true"></i> Accessibility</span>
            <div class="a11y-controls" role="group" aria-label="Text size">
                <button type="button" class="a11y-btn" id="fontDec" title="Decrease text size" aria-label="Decrease text size">A−</button>
                <button type="button" class="a11y-btn" id="fontReset" title="Default text size" aria-label="Default text size">A</button>
                <button type="button" class="a11y-btn" id="fontInc" title="Increase text size" aria-label="Increase text size">A+</button>
            </div>
            <button type="button" class="a11y-btn" id="contrastToggle" title="Toggle high contrast" aria-pressed="false" aria-label="Toggle high contrast mode">
                <i class="bi bi-circle-half" aria-hidden="true"></i> Contrast
            </button>
            <a class="a11y-link" href="<?= base_url('policy/accessibility') ?>">Accessibility Statement</a>
        </div>
        <div class="a11y-right">
            <a class="a11y-link" href="<?= base_url('policy/help') ?>">Screen Reader Access / Help</a>
        </div>
    </div>
</div>

<?php if ($u): ?>
<!-- Utility strip (signed-in user only) -->
<div class="util-strip no-print">
    <div class="container util-inner">
        <div></div>
        <div class="util-right">
            <span class="util-email text-truncate" style="max-width:16rem;display:inline-block;vertical-align:bottom;"><?= esc($u['email']) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Official masthead -->
<header class="masthead no-print">
    <div class="container masthead-inner">
        <img src="<?= base_url('assets/img/logo.svg') ?>" alt="Emblem of the High Court of Madras" class="masthead-seal" width="72" height="72">
        <div class="masthead-text">
            <!-- <div class="eyebrow"><?= esc($site->organisation) ?></div> -->
            <p class="masthead-title"><?= esc($site->portalName) ?></p>
            <p>Online Application Submission Portal</p>
        </div>
    </div>
</header>

<!-- Primary navigation -->
<nav class="navbar navbar-expand-lg navbar-mhc no-print" aria-label="Primary">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url($u ? (is_admin_role() ? 'admin' : 'applicant/dashboard') : 'login') ?>">
            <i class="bi bi-building-fill-check" aria-hidden="true"></i>
            <span>SSA Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto align-items-lg-center">
                <?php if ($u): ?>
                    <?php if (is_admin_role()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('admin') ?>"><i class="bi bi-speedometer2 me-1" aria-hidden="true"></i>Admin</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navApplications" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-folder2-open me-1" aria-hidden="true"></i>Applications
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navApplications">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('admin/applications') ?>">
                                        <i class="bi bi-list-ul me-1"></i> Application list
                                    </a>
                                </li>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/applications/status') ?>">
                                            <i class="bi bi-ui-checks me-1"></i> Update status
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php if ($u['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('admin/audit') ?>"><i class="bi bi-shield-check me-1" aria-hidden="true"></i>Audit</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('admin/notifications') ?>"><i class="bi bi-megaphone me-1" aria-hidden="true"></i>Notifications</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navMasters" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-database me-1" aria-hidden="true"></i>Masters
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navMasters">
                                    <li><a class="dropdown-item" href="<?= base_url('admin/masters') ?>"><i class="bi bi-grid me-1"></i> All masters</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/masters/qualification') ?>">Educational qualifications</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/masters/court') ?>">Courts</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/masters/tribunal') ?>">Tribunals</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/masters/nature_of_practice') ?>">Nature of practice</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/masters/field_of_law') ?>">Field of law</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navSettings" role="button"
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-gear me-1" aria-hidden="true"></i>Settings
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navSettings">
                                    <li><a class="dropdown-item" href="<?= base_url('admin/notification-templates') ?>">Email / SMS templates</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/settings/email') ?>">Email transport</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/settings/sms') ?>">SMS gateway</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('applicant/dashboard') ?>"><i class="bi bi-grid me-1" aria-hidden="true"></i>Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('notifications') ?>"><i class="bi bi-megaphone me-1" aria-hidden="true"></i>Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('rules') ?>"><i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Rules</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('instructions') ?>"><i class="bi bi-journal-bookmark me-1" aria-hidden="true"></i>Instructions</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <?php if ($u): ?>
                    <li class="nav-item">
                        <span class="nav-link nav-user">
                            <i class="bi bi-person-circle me-1" aria-hidden="true"></i><?= esc($u['name']) ?>
                            <span class="opacity-75 small">(<?= esc(ucfirst($u['role'])) ?>)</span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('change-password') ?>"><i class="bi bi-key me-1" aria-hidden="true"></i>Change password</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('notifications') ?>"><i class="bi bi-megaphone me-1" aria-hidden="true"></i>Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('rules') ?>"><i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i>Rules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('login') ?>"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-nav-cta" href="<?= base_url('register') ?>">Register as Advocate</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main id="main-content" class="container site-main" tabindex="-1">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show alert-inline" role="alert">
            <i class="bi bi-check-circle me-1" aria-hidden="true"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show alert-inline" role="alert">
            <i class="bi bi-info-circle me-1" aria-hidden="true"></i><?= esc(session()->getFlashdata('info')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($errors = session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-x-circle me-1" aria-hidden="true"></i>Please correct the following:</div>
            <ul class="mb-0">
                <?php foreach ((array) $errors as $err): ?>
                    <li><?= esc(is_array($err) ? implode(', ', $err) : $err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<!-- GIGW-compliant footer -->
<footer class="footer-mhc no-print" role="contentinfo">
    <div class="container">
        <div class="row g-4 footer-top">
            <div class="col-md-4">
                <div class="footer-brand"><?= esc($site->organisation) ?></div>
                <p class="footer-meta mb-2"><?= esc($site->portalName) ?></p>
                <p class="footer-meta mb-1"><?= esc($site->department) ?></p>
                <p class="footer-meta mb-0"><?= esc($site->address) ?></p>
            </div>
            <div class="col-6 col-md-2">
                <div class="footer-heading">Quick links</div>
                <ul class="footer-links">
                    <li><a href="<?= base_url('login') ?>">Login</a></li>
                    <li><a href="<?= base_url('register') ?>">Register</a></li>
                    <li><a href="<?= base_url('rules') ?>">Rules, 2026</a></li>
                    <li><a href="<?= base_url('instructions') ?>">Instructions</a></li>
                    <li><a href="<?= base_url('policy/help') ?>">Help &amp; Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <div class="footer-heading">Website policies</div>
                <ul class="footer-links">
                    <li><a href="<?= base_url('policy/privacy') ?>">Privacy Policy</a></li>
                    <li><a href="<?= base_url('policy/terms') ?>">Terms &amp; Conditions</a></li>
                    <li><a href="<?= base_url('policy/copyright') ?>">Copyright Policy</a></li>
                    <li><a href="<?= base_url('policy/hyperlinking') ?>">Hyperlinking Policy</a></li>
                    <!-- <li><a href="<?= base_url('policy/security') ?>">Security Policy</a></li> -->
                    <li><a href="<?= base_url('policy/disclaimer') ?>">Disclaimer</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <!-- <div class="footer-heading">GIGW &amp; more</div>
                <ul class="footer-links">
                    <li><a href="<?= base_url('policy/accessibility') ?>">Accessibility Statement</a></li>
                    <li><a href="<?= base_url('policy/content-archival') ?>">Content Archival Policy</a></li>
                    <li><a href="<?= base_url('policy/content-review') ?>">Content Review Policy</a></li>
                    <li><a href="<?= base_url('policy/contingency') ?>">Contingency Management Plan</a></li>
                    <li><a href="<?= base_url('policy/website-monitoring') ?>">Website Monitoring Plan</a></li>
                </ul> -->
                <div class="footer-contact mt-3">
                    <div class="footer-heading">Contact</div>
                    <p class="footer-meta mb-1">
                        <a href="mailto:<?= esc($site->email) ?>"><?= esc($site->email) ?></a>
                    </p>
                    <p class="footer-meta mb-0"><?= esc($site->phone) ?></p>
                </div>
            </div>
        </div>

        <div class="footer-divider"></div>

        <div class="row g-2 footer-bottom align-items-md-center">
            <div class="col-md-7">
                <p class="footer-meta mb-1">
                    <strong>Content owned by:</strong> <?= esc($site->contentOwnedBy) ?>
                </p>
                <p class="footer-meta mb-1">
                    <strong>Maintained by:</strong> <?= esc($site->maintainedBy) ?>
                </p>
                <p class="footer-meta mb-1">
                    Email : cpc-tn(at)indianjudiciary(dot)gov(dot)in
                </p>
                <p class="footer-meta mb-1">
                    Designed & Developed by Madras High Court.
                </p>
                <!-- <p class="footer-meta mb-0">
                    Designed for compliance with
                    <abbr title="Guidelines for Indian Government Websites">GIGW</abbr> 3.0
                    and WCAG 2.1 Level AA (target).
                </p> -->
            </div>
            <div class="col-md-5 text-md-end">
                <p class="footer-meta mb-1">
                    <strong>Last updated:</strong>
                    <time datetime="<?= esc(date('Y-m-d', strtotime($site->lastUpdated))) ?>"><?= esc($site->lastUpdated) ?></time>
                </p>
                <p class="footer-meta mb-0">
                    &copy; <?= date('Y') ?> <?= esc($site->organisation) ?>. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>?v=<?= @filemtime(FCPATH . 'assets/js/app.js') ?: time() ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
