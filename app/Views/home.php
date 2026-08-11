<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<section class="hero-banner home-hero">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <div class="eyebrow">High Court of Madras · Official Portal</div>
            <h1>Designation of Senior Advocates</h1>
            <p class="subtitle">
                Apply online for designation as Senior Advocate under the
                <a href="<?= base_url('rules') ?>" class="text-white text-decoration-underline fw-semibold">
                    Rules for Designation of Senior Advocates, 2026
                </a>.
                Complete the Application-cum-Consent Letter, attach Formats&nbsp;L-1 to&nbsp;L-4, and track review status from one secure account.
            </p>
            <div class="hero-meta">
                <span class="hero-chip"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Proforma Sl.&nbsp;No.&nbsp;1–24</span>
                <span class="hero-chip"><i class="bi bi-files" aria-hidden="true"></i> Formats L-1 to L-4</span>
                <span class="hero-chip"><i class="bi bi-hash" aria-hidden="true"></i> MHC/SSA/2026/####</span>
                <span class="hero-chip"><i class="bi bi-shield-lock" aria-hidden="true"></i> Secure · Audited</span>
            </div>
            <div class="hero-cta">
                <?php if (current_user()): ?>
                    <?php if (is_admin_role()): ?>
                        <a href="<?= base_url('admin') ?>" class="btn btn-gold btn-lg">
                            <i class="bi bi-speedometer2 me-1" aria-hidden="true"></i>Go to Admin Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('applicant/dashboard') ?>" class="btn btn-gold btn-lg">
                            <i class="bi bi-person-badge me-1" aria-hidden="true"></i>My Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('instructions') ?>" class="btn btn-outline-light btn-lg">Read Instructions</a>
                <?php else: ?>
                    <a href="<?= base_url('register') ?>" class="btn btn-gold btn-lg">
                        <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Register as Advocate
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Login
                    </a>
                    <a href="<?= base_url('instructions') ?>" class="btn btn-outline-light btn-lg">Read Instructions</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="hero-side-card hero-process-card">
                <div class="hero-side-label">Application journey</div>
                <ol class="hero-process-steps mb-0">
                    <li>
                        <span class="step-num">1</span>
                        <div>
                            <strong>Register</strong>
                            <span>Create an advocate account with the name as on enrolment certificate.</span>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">2</span>
                        <div>
                            <strong>Fill steps 1–7</strong>
                            <span>Personal details, practice, judgments (L-1/L-2), pro bono &amp; academic work, declarations.</span>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">3</span>
                        <div>
                            <strong>Upload documents</strong>
                            <span>Photo, signature, enrolment certificate and Format PDFs (within size limits).</span>
                        </div>
                    </li>
                    <li>
                        <span class="step-num">4</span>
                        <div>
                            <strong>Submit &amp; print</strong>
                            <span>Receive application number, download PDF, and file the paper book at the Permanent Secretariat.</span>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="section-heading d-flex flex-wrap justify-content-between align-items-end gap-2">
        <div>
            <h2 class="h5 mb-1">Why use this portal</h2>
            <p class="text-muted small mb-0">Purpose-built for the official Application-cum-Consent Letter workflow</p>
        </div>
    </div>
    <div class="row g-3 g-md-4">
        <div class="col-md-6 col-xl-3">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i></div>
                    <h3 class="h6 card-title">Guided multi-step form</h3>
                    <p class="card-text text-muted mb-0 small">
                        Capture every field of the official proforma (Sl.&nbsp;No.&nbsp;1–24). Save drafts at any stage and resume later.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-journal-richtext" aria-hidden="true"></i></div>
                    <h3 class="h6 card-title">Formats L-1 to L-4</h3>
                    <p class="card-text text-muted mb-0 small">
                        Enter reported and unreported judgments, pro bono, amicus curiae and academic work with PDF attachments.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></div>
                    <h3 class="h6 card-title">Transparent review</h3>
                    <p class="card-text text-muted mb-0 small">
                        Track status from submission through review and decision. Returned applications can be corrected and resubmitted.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-mhc feature-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
                    <h3 class="h6 card-title">Secure &amp; audited</h3>
                    <p class="card-text text-muted mb-0 small">
                        Strict file validation, unique application numbers (MHC/SSA/2026/####), role-based access and full audit logs.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="section-heading">
        <h2 class="h5 mb-1">Who should apply</h2>
        <p class="text-muted small mb-0">Eligibility is governed by the Approved Rules, 2026 — verify the official notification before applying</p>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-mhc h-100">
                <div class="card-body">
                    <ul class="home-checklist home-checklist-lg mb-0">
                        <li>Advocates enrolled with a Bar Council and practising before the High Court of Madras / courts in Tamil Nadu as notified.</li>
                        <li>Applicants who satisfy the standing, integrity and ability criteria prescribed in the Approved Rules for Designation of Senior Advocates, 2026.</li>
                        <li>Those ready to furnish complete particulars for Sl.&nbsp;No.&nbsp;1–24 and Formats L-1 to L-4 with supporting documents.</li>
                        <li>Applicants who will also submit the prescribed paper book to the Permanent Secretariat after online submission.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-mhc home-notice-card h-100">
                <div class="card-header">
                    <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Important
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        An error in a submitted application <strong>cannot be subsequently rectified</strong> and may result in rejection.
                    </p>
                    <p class="small mb-3">
                        Late applications beyond the last date indicated in the notice <strong>shall not be entertained</strong>.
                    </p>
                    <a href="<?= base_url('instructions') ?>" class="btn btn-mhc btn-sm w-100">
                        Read full instructions <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="section-heading">
        <h2 class="h5 mb-1">Document specifications</h2>
        <p class="text-muted small mb-0">Prepare scans to these limits before you begin — incorrect files will be rejected at upload</p>
    </div>
    <div class="row g-3 g-md-4">
        <div class="col-md-6 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-header">
                    <i class="bi bi-image me-1" aria-hidden="true"></i> Identity images
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

        <div class="col-md-6 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-header">
                    <i class="bi bi-file-earmark-check me-1" aria-hidden="true"></i> Enrolment certificate
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

        <div class="col-md-12 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-header">
                    <i class="bi bi-list-check me-1" aria-hidden="true"></i> Before you begin
                </div>
                <div class="card-body">
                    <ul class="home-checklist mb-3">
                        <li>Keep data for Formats L-1 to L-4 ready</li>
                        <li>Scan passport photo, signature &amp; enrolment certificate</li>
                        <li>Use full name exactly as on enrolment certificate</li>
                        <li>Plan time to file the paper book at the Permanent Secretariat</li>
                    </ul>
                    <?php if (! current_user()): ?>
                        <a href="<?= base_url('register') ?>" class="btn btn-gold btn-sm">
                            Start registration <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('instructions') ?>" class="btn btn-mhc btn-sm">
                            View full instructions <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-md-4 mt-0">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-mhc format-card h-100">
                <div class="card-header py-2">
                    <span class="format-badge">L-1</span> Reported judgments
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>Reported judgments as lead / assisting counsel</td>
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
            <div class="card card-mhc format-card h-100">
                <div class="card-header py-2">
                    <span class="format-badge">L-2</span> Unreported judgments
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>Unreported judgments in chronological order</td>
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
            <div class="card card-mhc format-card h-100">
                <div class="card-header py-2">
                    <span class="format-badge">L-3</span> Pro Bono · Amicus
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>L-3(i) Pro Bono · L-3(ii) Amicus Curiae</td>
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
            <div class="card card-mhc format-card h-100">
                <div class="card-header py-2">
                    <span class="format-badge">L-4</span> Academic work
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 spec-table">
                            <tbody>
                            <tr>
                                <th scope="row">Content</th>
                                <td>Articles, books, teaching &amp; guest lectures</td>
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

<section class="home-section">
    <div class="section-heading">
        <h2 class="h5 mb-1">Need help?</h2>
        <p class="text-muted small mb-0">Use official channels only for application-related queries</p>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card card-mhc home-help-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-book" aria-hidden="true"></i></div>
                    <h3 class="h6">Instructions</h3>
                    <p class="small text-muted mb-3">Mandatory reading before registration and final submission.</p>
                    <a href="<?= base_url('instructions') ?>" class="stretched-link small fw-semibold">Open instructions</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-mhc home-help-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-question-circle" aria-hidden="true"></i></div>
                    <h3 class="h6">Help &amp; contact</h3>
                    <p class="small text-muted mb-3">Permanent Secretariat contact details and support guidance.</p>
                    <a href="<?= base_url('policy/help') ?>" class="stretched-link small fw-semibold">View help page</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-mhc home-help-card h-100">
                <div class="card-body">
                    <div class="feature-icon"><i class="bi bi-bank" aria-hidden="true"></i></div>
                    <h3 class="h6">High Court website</h3>
                    <p class="small text-muted mb-3">Official notices and updates of the High Court of Madras.</p>
                    <a href="<?= esc(config(\Config\Site::class)->website) ?>" class="stretched-link small fw-semibold" target="_blank" rel="noopener noreferrer">hcmadras.tn.gov.in</a>
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
