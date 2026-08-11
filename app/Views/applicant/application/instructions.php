<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- <div class="page-header">
    <h1 class="page-title">Instructions for Advocates</h1>
    <p class="page-subtitle">
        Please read carefully before starting a new application for Designation of Senior Advocate
    </p>
</div> -->

<div class="alert alert-light border mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="bi bi-book me-1" aria-hidden="true"></i>
            Please read the
            <a href="<?= base_url('rules') ?>" class="fw-semibold" target="_blank" rel="noopener">
                Rules for Designation of Senior Advocates, 2026
            </a>
            before proceeding.
        </div>
        <a href="<?= base_url('rules/download') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1" aria-hidden="true"></i> Download Rules PDF
        </a>
    </div>
</div>

<?php if (! empty($notificationNumber) || ! empty($applicationStartDate)): ?>
    <div class="alert alert-primary border mb-3">
        <div class="small">
            <?php if (! empty($notificationNumber)): ?>
                <div>
                    <strong>Notification:</strong> <?= esc($notificationNumber) ?>
                    <?php if (! empty($notificationDate)): ?>
                        dated <?= esc(date('d-m-Y', strtotime($notificationDate))) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (! empty($applicationStartDate) && ! empty($applicationLastDate)): ?>
                <div>
                    <strong>Application period:</strong>
                    <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($applicationStartDate)) ?>
                    to
                    <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($applicationLastDate)) ?>
                </div>
            <?php endif; ?>
            <?php if (! empty($cycleYear)): ?>
                <div><strong>Cycle year:</strong> <?= (int) $cycleYear ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card card-mhc">
    <div class="card-header">
        <i class="bi bi-journal-bookmark me-1"></i>
        INSTRUCTIONS TO BE FOLLOWED BY THE ADVOCATES WHILE APPLYING FOR DESIGNATION OF SENIOR ADVOCATE
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
<!-- </div> -->

<!-- <div class="warning-box mt-4">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Important acknowledgement:</strong>
    Yes, I have read the instructions carefully and understand that an error in this application
    cannot be subsequently rectified and may result in rejection of my application.
</div> -->

    <?= form_open('applicant/application/start', ['class' => 'mt-4']) ?>
        <!-- <div class="card card-mhc">
            <div class="card-body"> -->
                <div class="p-3">

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" name="instructions_accepted"
                            id="instructions_accepted" required>
                        <label class="form-check-label" for="instructions_accepted">
                            Yes, I have read the instructions carefully and understand that an error in this application
                            cannot be subsequently rectified and may result in rejection of my application.
                        </label>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-mhc" id="btnAcceptInstructions">
                            <i class="bi bi-check2-circle me-1"></i> Accept &amp; Start Application
                        </button>
                        <a href="<?= base_url('applicant/dashboard') ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            <!-- </div>
        </div> -->
    <?= form_close() ?>
</div>

<?= $this->endSection() ?>
