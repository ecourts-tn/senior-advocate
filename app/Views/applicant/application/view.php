<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Application <?= esc($app['application_no'] ?? '#' . $app['id']) ?></h1>
        <div class="mt-1"><?= sad_status_badge($app['status']) ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
        <?php if (\App\Models\ApplicationModel::isEditableByApplicant($app)): ?>
            <a href="<?= base_url('applicant/application/step/' . (int) ($app['current_step'] ?: 1)) ?>" class="btn btn-mhc flex-grow-1 flex-md-grow-0">
                <?= in_array($app['status'] ?? '', ['draft', 'returned'], true) ? 'Continue Editing' : 'Edit application' ?>
            </a>
        <?php endif; ?>
        <a href="<?= base_url('applicant/application/pdf/' . $app['id']) ?>" class="btn btn-outline-danger flex-grow-1 flex-md-grow-0" target="_blank">Download PDF</a>
        <a href="<?= base_url('applicant/dashboard') ?>" class="btn btn-outline-secondary flex-grow-1 flex-md-grow-0">Back</a>
    </div>
</div>

<?php if ($app['status'] === 'returned'): ?>
    <div class="alert alert-warning">
        <strong>Returned for correction.</strong>
        Please update the application as directed and resubmit from Step 7.
        <?php if (! empty($app['review_remarks'])): ?>
            <hr class="my-2">
            <strong>Reviewer remarks:</strong> <?= nl2br(esc($app['review_remarks'])) ?>
        <?php endif; ?>
    </div>
<?php elseif (! empty($app['review_remarks']) && in_array($app['status'], ['approved', 'rejected'], true)): ?>
    <div class="alert alert-<?= $app['status'] === 'approved' ? 'success' : 'danger' ?>">
        <strong>Decision remarks:</strong> <?= nl2br(esc($app['review_remarks'])) ?>
    </div>
<?php elseif (! empty($app['review_remarks'])): ?>
    <div class="alert alert-light border">
        <strong>Registry remarks:</strong> <?= nl2br(esc($app['review_remarks'])) ?>
    </div>
<?php endif; ?>

<div class="card card-mhc mb-3">
    <div class="card-header">Application summary</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><strong>Name:</strong> <?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''))) ?></div>
            <div class="col-md-3"><strong>DOB:</strong> <?= esc($app['date_of_birth'] ?? '—') ?></div>
            <div class="col-md-3"><strong>Age (<?= esc(sad_age_as_on_label()) ?>):</strong>
                <?= esc($app['age_years'] ?? '—') ?> yrs
                <?= esc($app['age_months'] ?? '—') ?> mo
            </div>
            <div class="col-md-6"><strong>Mobile:</strong> <?= esc($app['mobile'] ?? '—') ?></div>
            <div class="col-md-6"><strong>Email:</strong> <?= esc($app['email'] ?? '—') ?></div>
            <div class="col-md-6"><strong>Enrolment No.:</strong> <?= esc($app['enrolment_number'] ?? '—') ?></div>
            <div class="col-md-6"><strong>Bar Council:</strong> <?= esc($app['bar_council'] ?? '—') ?></div>
            <div class="col-md-6"><strong>Office Address:</strong><br><?= nl2br(esc($app['address_office'] ?? '—')) ?></div>
            <div class="col-md-6"><strong>Residential Address:</strong><br><?= nl2br(esc($app['address_residence'] ?? '—')) ?></div>
            <div class="col-12"><strong>Qualifications:</strong><br><?= nl2br(esc($app['qualifications'] ?? '—')) ?></div>
            <div class="col-md-4"><strong>Practice:</strong> <?= (int) ($app['practice_years'] ?? 0) ?> yrs <?= (int) ($app['practice_months'] ?? 0) ?> mo</div>
            <div class="col-md-4"><strong>Income (₹ Lakh):</strong> <?= esc($app['net_income_lakhs'] ?? '—') ?></div>
            <div class="col-md-4"><strong>First-generation:</strong> <?= sad_bool_label($app['is_first_generation'] ?? null) ?></div>
            <div class="col-md-6"><strong>Nature of practice:</strong><br><?= nl2br(esc($app['nature_of_practice'] ?? '—')) ?></div>
            <div class="col-md-6"><strong>Field of law:</strong><br><?= nl2br(esc($app['field_of_law'] ?? '—')) ?></div>
        </div>
    </div>
</div>

<div class="card card-mhc mb-3">
    <div class="card-header">Photograph &amp; signature</div>
    <div class="card-body">
        <div class="review-identity justify-content-md-start">
            <div class="review-identity-item">
                <span class="review-label">Photograph</span>
                <?php if (! empty($app['photo_path'])): ?>
                    <a href="<?= base_url('files/application/' . $app['id'] . '/photo') ?>" target="_blank" rel="noopener">
                        <img src="<?= base_url('files/application/' . $app['id'] . '/photo') ?>"
                             class="photo-preview" width="120" height="150" alt="Passport photograph">
                    </a>
                <?php else: ?>
                    <div class="photo-preview d-flex align-items-center justify-content-center text-muted small">No photo</div>
                <?php endif; ?>
            </div>
            <div class="review-identity-item">
                <span class="review-label">Signature</span>
                <?php if (! empty($app['signature_path'])): ?>
                    <a href="<?= base_url('files/application/' . $app['id'] . '/signature') ?>" target="_blank" rel="noopener">
                        <img src="<?= base_url('files/application/' . $app['id'] . '/signature') ?>"
                             class="sig-preview" width="180" height="64" alt="Signature">
                    </a>
                <?php else: ?>
                    <div class="sig-preview d-flex align-items-center justify-content-center text-muted small">No signature</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-mhc mb-3">
    <div class="card-header">Other uploads</div>
    <div class="card-body">
        <div class="row g-3">
            <?php
            $files = [
                'enrolment_cert' => 'Enrolment Certificate',
                'format_l1' => 'Format L-1',
                'format_l2' => 'Format L-2',
                'format_l3i' => 'Format L-3(i)',
                'format_l3ii' => 'Format L-3(ii)',
                'format_l4' => 'Format L-4',
            ];
            foreach ($files as $key => $label):
                $col = $key === 'enrolment_cert' ? 'enrolment_cert_path' : $key . '_path';
                if ($key === 'format_l3i') {
                    $col = 'format_l3i_path';
                }
                if ($key === 'format_l3ii') {
                    $col = 'format_l3ii_path';
                }
            ?>
                <div class="col-md-4 col-lg-3">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc($label) ?></div>
                        <?php if (! empty($app[$col])): ?>
                            <a href="<?= base_url('files/application/' . $app['id'] . '/' . $key) ?>" target="_blank">View file</a>
                        <?php else: ?>
                            <span class="text-danger small">Not uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card card-mhc">
    <div class="card-header">Status history</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>When</th><th>From</th><th>To</th><th>Remarks</th></tr></thead>
            <tbody>
            <?php if (empty($history)): ?>
                <tr><td colspan="4" class="text-muted">No history yet.</td></tr>
            <?php else: foreach ($history as $h): ?>
                <tr>
                    <td><?= esc($h['created_at']) ?></td>
                    <td><?= esc($h['from_status'] ?? '—') ?></td>
                    <td><?= esc($h['to_status']) ?></td>
                    <td><?= esc($h['remarks'] ?? '') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
