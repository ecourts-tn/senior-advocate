<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Instructions for Advocates</h1>
    <p class="page-subtitle">
        Follow these instructions carefully while applying for Designation of Senior Advocate
    </p>
</div>

<div class="card card-mhc">
    <div class="card-header">
        <i class="bi bi-journal-bookmark me-1"></i>
        Instructions to be followed while applying for Designation of Senior Advocate
    </div>
    <div class="card-body">
        <ol class="instruction-list lh-lg mb-0">
            <li>
                Advocates are requested to scrupulously follow these instructions while submitting
                the Application and the prescribed Formats (L-1 to L-4).
            </li>
            <li>
                At the time of submission of the Application online, the following should be kept ready:
                <ul>
                    <li>Data / Information that is to be furnished in formats L-1 to L-4;</li>
                    <li>Scanned signature of the applicant;</li>
                    <li>Recent scanned passport size colour photograph; and</li>
                    <li>Scanned Enrolment Certificate.</li>
                </ul>
            </li>
            <li>
                In addition to the Application submitted online, the following should be submitted
                in the Permanent Secretariat:
                <ul>
                    <li>One print-out of the Application along with its attachments in the shape of a Paper Book, duly tagged &amp; indexed.</li>
                    <li>One recent passport size colour photograph (name of the applicant should be written on its backside).</li>
                </ul>
            </li>
            <li>
                Data/Information relating to reported and unreported judgments in matters argued as
                lead arguing/assisting counsel, pro bono / amicus curiae, publication work
                (articles / books / teaching assignments / guest lectures) should be provided in the
                prescribed formats L-1 to L-4 which form part of Application.
            </li>
            <li>
                List of citations of reported and unreported judgments in matters argued as lead
                counsel should be furnished in chronological order. The citations should be of
                judgments made reportable by the Supreme Court / High Courts / Tribunals and are
                available in their official reports.
            </li>
            <li>
                Name of the applicant should tally with his/her name as mentioned in his/her
                enrolment certificate. <strong>Abbreviated name shall NOT be accepted.</strong>
            </li>
            <li>
                Request to accept application beyond the last date indicated in the notice
                <strong>shall NOT be entertained in any case.</strong>
            </li>
        </ol>
    </div>
</div>

<div class="warning-box mt-4">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Important acknowledgement:</strong>
    Yes, I have read the instructions carefully and understand that an error in this application
    cannot be subsequently rectified and may result in rejection of my application.
    (You will be required to confirm this at the time of final submission.)
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <?php if (current_user() && ! is_admin_role()): ?>
        <a href="<?= base_url('applicant/dashboard') ?>" class="btn btn-mhc">
            <i class="bi bi-arrow-right me-1"></i> Proceed to Dashboard
        </a>
    <?php elseif (! current_user()): ?>
        <a href="<?= base_url('register') ?>" class="btn btn-gold">
            <i class="bi bi-person-plus me-1"></i> Register as Advocate
        </a>
        <a href="<?= base_url('login') ?>" class="btn btn-mhc">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
        </a>
    <?php endif; ?>
    <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary">Back to Home</a>
</div>

<?= $this->endSection() ?>
