<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="hero-banner home-hero">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="eyebrow">Official Online Portal</div>
            <h1>Designation of Senior Advocates</h1>
            <p class="subtitle mb-0">
                Submit the Application-cum-Consent Letter online under the
                <strong>Approved Rules for Designation of Senior Advocates by the High Court of Madras, 2026</strong>.
            </p>
            <div class="hero-meta">
                <span class="hero-chip"><i class="bi bi-file-earmark-text"></i> Proforma Sl. No. 1–24</span>
                <span class="hero-chip"><i class="bi bi-files"></i> Formats L-1 to L-4</span>
                <span class="hero-chip"><i class="bi bi-hash"></i> SAD/2026/####</span>
            </div>
            <div class="hero-cta">
                <?php if (current_user()): ?>
                    <?php if (is_admin_role()): ?>
                        <a href="<?= base_url('admin') ?>" class="btn btn-gold btn-lg">Go to Admin Dashboard</a>
                    <?php else: ?>
                        <a href="<?= base_url('applicant/dashboard') ?>" class="btn btn-gold btn-lg">My Dashboard</a>
                    <?php endif; ?>
                    <a href="<?= base_url('instructions') ?>" class="btn btn-outline-light btn-lg">Read Instructions</a>
                <?php else: ?>
                    <a href="<?= base_url('register') ?>" class="btn btn-gold btn-lg">
                        <i class="bi bi-person-plus me-1"></i>Register as Advocate
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                    <a href="<?= base_url('instructions') ?>" class="btn btn-outline-light btn-lg">Read Instructions</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4 d-none d-lg-block">
            <div class="hero-side-card">
                <div class="hero-side-label">How it works</div>
                <ol class="hero-side-steps mb-0">
                    <li>Register with enrolment name</li>
                    <li>Complete steps 1–7 online</li>
                    <li>Upload photo, signature &amp; PDFs</li>
                    <li>Submit &amp; print paper book</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="section-heading">
        <h2 class="h5 mb-1">Portal features</h2>
        <p class="text-muted small mb-0">Built for the official Application-cum-Consent Letter workflow</p>
    </div>
    <div class="row g-3 g-md-4">
        <div class="col-md-4">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-person-vcard"></i></div>
                    <h3 class="h6 card-title">Multi-step Application</h3>
                    <p class="card-text text-muted mb-0 small">
                        Capture all fields of the official proforma (Sl. No. 1–24), save drafts at any stage, and submit when complete.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-file-earmark-pdf"></i></div>
                    <h3 class="h6 card-title">Formats L-1 to L-4</h3>
                    <p class="card-text text-muted mb-0 small">
                        Enter reported / unreported judgments, pro bono, amicus curiae and academic work with PDF uploads (max 5&nbsp;MB each).
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h3 class="h6 card-title">Secure &amp; Audited</h3>
                    <p class="card-text text-muted mb-0 small">
                        Strict file validation, application numbering (SAD/2026/####), review workflow and full audit logs.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="section-heading">
        <h2 class="h5 mb-1">Upload specifications</h2>
        <p class="text-muted small mb-0">As prescribed in the official proforma — prepare these before you start</p>
    </div>
    <div class="row g-3 g-md-4">
        <!-- Identity images -->
        <div class="col-md-6 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-header">
                    <i class="bi bi-image me-1"></i> Identity images
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <thead>
                            <tr>
                                <th>Document</th>
                                <th>Format</th>
                                <th>Size</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Passport size colour photograph</td>
                                <td>JPG / JPEG</td>
                                <td>20 – 200 KB</td>
                            </tr>
                            <tr>
                                <td>Signature</td>
                                <td>JPG / JPEG</td>
                                <td>20 – 200 KB</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrolment certificate -->
        <div class="col-md-6 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-header">
                    <i class="bi bi-file-earmark-check me-1"></i> Enrolment certificate
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <thead>
                            <tr>
                                <th>Document</th>
                                <th>Format</th>
                                <th>Size</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Enrolment Certificate</td>
                                <td>PDF</td>
                                <td>Less than 5 MB</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Before you begin -->
        <div class="col-md-12 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-header">
                    <i class="bi bi-list-check me-1"></i> Before you begin
                </div>
                <div class="card-body">
                    <ul class="home-checklist mb-3">
                        <li>Keep data for Formats L-1 to L-4 ready</li>
                        <li>Scan passport photo, signature &amp; enrolment certificate</li>
                        <li>Use full name exactly as on enrolment certificate</li>
                        <li>Also submit a paper book to the Permanent Secretariat</li>
                    </ul>
                    <a href="<?= base_url('instructions') ?>" class="btn btn-mhc btn-sm">
                        View full instructions <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formats L-1 to L-4 as separate compact tables -->
    <div class="row g-3 g-md-4 mt-0">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-mhc h-100">
                <div class="card-header py-2">
                    <i class="bi bi-journal-text me-1"></i> Format L-1
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>Reported judgments</td>
                            </tr>
                            <tr>
                                <th scope="row">Format</th>
                                <td>PDF</td>
                            </tr>
                            <tr>
                                <th scope="row">Size</th>
                                <td>Less than 5 MB</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-mhc h-100">
                <div class="card-header py-2">
                    <i class="bi bi-journal me-1"></i> Format L-2
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>Unreported judgments</td>
                            </tr>
                            <tr>
                                <th scope="row">Format</th>
                                <td>PDF</td>
                            </tr>
                            <tr>
                                <th scope="row">Size</th>
                                <td>Less than 5 MB</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-mhc h-100">
                <div class="card-header py-2">
                    <i class="bi bi-people me-1"></i> Format L-3
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>L-3(i) Pro Bono · L-3(ii) Amicus</td>
                            </tr>
                            <tr>
                                <th scope="row">Format</th>
                                <td>PDF (each)</td>
                            </tr>
                            <tr>
                                <th scope="row">Size</th>
                                <td>Less than 5 MB each</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-mhc h-100">
                <div class="card-header py-2">
                    <i class="bi bi-mortarboard me-1"></i> Format L-4
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>Academic / teaching / lectures</td>
                            </tr>
                            <tr>
                                <th scope="row">Format</th>
                                <td>PDF</td>
                            </tr>
                            <tr>
                                <th scope="row">Size</th>
                                <td>Less than 5 MB</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="warning-box mt-2">
    <strong><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Important:</strong>
    An error in this application cannot be subsequently rectified and may result in rejection of the application.
    Late applications beyond the last date indicated in the notice shall not be entertained.
</div>

<p class="small text-muted mt-3 mb-0">
    <strong>Last updated:</strong>
    <time datetime="<?= esc(date('Y-m-d', strtotime(config(\Config\Site::class)->lastUpdated))) ?>">
        <?= esc(config(\Config\Site::class)->lastUpdated) ?>
    </time>
    ·
    <a href="<?= base_url('policy/accessibility') ?>">Accessibility</a>
    ·
    <a href="<?= base_url('policy/privacy') ?>">Privacy</a>
    ·
    <a href="<?= base_url('policy/disclaimer') ?>">Disclaimer</a>
</p>

<?= $this->endSection() ?>
