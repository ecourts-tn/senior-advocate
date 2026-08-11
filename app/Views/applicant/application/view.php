<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $ageAsOnLabel = ssa_age_as_on_label($app ?? null); ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Application <?= esc($app['application_no'] ?? '#' . $app['id']) ?></h1>
        <div class="mt-1"><?= ssa_status_badge($app['status']) ?></div>
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
<?php elseif (! empty($app['review_remarks']) && in_array($app['status'], ['listed', 'waitlisted', 'rejected', 'approved'], true)): ?>
    <?php
    $remarkClass = match ($app['status']) {
        'listed', 'approved' => 'success',
        'waitlisted'         => 'warning',
        'rejected'           => 'danger',
        default              => 'light border',
    };
    ?>
    <div class="alert alert-<?= $remarkClass ?>">
        <strong>Registry remarks:</strong> <?= nl2br(esc($app['review_remarks'])) ?>
    </div>
<?php elseif (! empty($app['review_remarks'])): ?>
    <div class="alert alert-light border">
        <strong>Registry remarks:</strong> <?= nl2br(esc($app['review_remarks'])) ?>
    </div>
<?php endif; ?>

<div class="card card-mhc mb-3">
    <div class="card-header">1–6. Personal Particulars</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <strong>Name of the Applicant-Advocate</strong><br>
                <?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''))) ?>
            </div>
            <div class="col-md-3">
                <strong>Date of Birth</strong><br>
                <?= esc($app['date_of_birth'] ?? '—') ?>
            </div>
            <div class="col-md-3">
                <strong>Age as on <?= esc($ageAsOnLabel) ?></strong><br>
                <?= esc($app['age_years'] ?? '—') ?> Years
                <?= esc($app['age_months'] ?? '—') ?> Months
                <?= esc($app['age_days'] ?? '—') ?> Days
            </div>
            <div class="col-md-6">
                <strong>Address in Full — Office</strong><br>
                <?= nl2br(esc($app['address_office'] ?? '—')) ?>
            </div>
            <div class="col-md-6">
                <strong>Address in Full — Residence</strong><br>
                <?= nl2br(esc($app['address_residence'] ?? '—')) ?>
            </div>
            <div class="col-md-4">
                <strong>Landline</strong><br>
                <?= esc($app['phone_landline'] ?? '—') ?>
            </div>
            <div class="col-md-4">
                <strong>Mobile</strong><br>
                <?= esc($app['mobile'] ?? '—') ?>
            </div>
            <div class="col-md-4">
                <strong>Email</strong><br>
                <?= esc($app['email'] ?? '—') ?>
            </div>
            <div class="col-12">
                <strong>Educational / Professional Qualifications</strong><br>
                <?= nl2br(esc($app['qualifications'] ?? '—')) ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-mhc mb-3">
    <div class="card-header">7. Enrolment Details</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <strong>Date, Month and Year of Enrolment as an Advocate</strong><br>
                <?= esc($app['enrolment_date'] ?? '—') ?>
            </div>
            <div class="col-md-4">
                <strong>Enrolment Number</strong><br>
                <?= esc($app['enrolment_number'] ?? '—') ?>
            </div>
            <div class="col-md-4">
                <strong>Bar Council where registered (Copy of Enrolment Certificate to be attached)</strong><br>
                <?= esc($app['bar_council'] ?? '—') ?>
            </div>
            <div class="col-md-6">
                <strong>Number of years of practice from the date of enrolment (as on <?= esc($ageAsOnLabel) ?>)</strong><br>
                <?= (int) ($app['practice_years'] ?? 0) ?> Years
                <?= (int) ($app['practice_months'] ?? 0) ?> Months
            </div>
            <div class="col-md-6">
                <strong>Net Professional Income per annum (in Lakhs of Rs) [Only earnings through practice as Advocate]</strong><br>
                <?= esc($app['net_income_lakhs'] ?? '—') ?>
            </div>
            <div class="col-md-6">
                <strong>Whether the applicant is a member of any bar association attached to a specific court</strong><br>
                <?= ssa_bool_label($app['is_bar_association_member'] ?? null) ?>
            </div>
            <div class="col-md-6">
                <strong>Name of Bar Association</strong><br>
                <?= esc($app['bar_association_name'] ?? '—') ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-mhc mb-3">
    <div class="card-header">9–13. Judgments / Pro Bono / Academic</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <strong>9. Number of Reported Judgments … Format L-1</strong><br>
                Supreme Court: <?= (int) ($app['reported_sc'] ?? 0) ?><br>
                High Court: <?= (int) ($app['reported_hc'] ?? 0) ?><br>
                District Court / Labour Court and Tribunals: <?= (int) ($app['reported_district'] ?? 0) ?>
            </div>
            <div class="col-md-6">
                <strong>10. Number of Unreported Judgments … Format L-2</strong><br>
                Supreme Court: <?= (int) ($app['unreported_sc'] ?? 0) ?><br>
                High Court: <?= (int) ($app['unreported_hc'] ?? 0) ?><br>
                District / Labour Court and Tribunals: <?= (int) ($app['unreported_district'] ?? 0) ?>
            </div>
            <div class="col-md-6">
                <strong>11. Pro Bono / Amicus Curiae work Format L-3(i), Format L-3(ii)</strong><br>
                Total Pro Bono cases: <?= (int) ($app['pro_bono_total'] ?? 0) ?><br>
                Total Amicus Curiae cases: <?= (int) ($app['amicus_total'] ?? 0) ?>
            </div>
            <div class="col-md-6">
                <strong>12. Whether the applicant is first-generation lawyer</strong><br>
                <?= ssa_bool_label($app['is_first_generation'] ?? null) ?>
            </div>
            <div class="col-12">
                <strong>13. Academic Articles/Books published, experience of Teaching Assignments in the field of law, Guest Lectures delivered in law schools or professional institutions connected with law: Format L-4</strong><br>
                No. of Academic Articles: <?= (int) ($app['academic_articles_count'] ?? 0) ?> ·
                No. of Academic Books: <?= (int) ($app['academic_books_count'] ?? 0) ?> ·
                No. of Teaching Assignments: <?= (int) ($app['teaching_assignments_count'] ?? 0) ?> ·
                No. of Guest Lectures: <?= (int) ($app['guest_lectures_count'] ?? 0) ?>
            </div>
            <div class="col-md-6">
                <strong>16. Nature of practice (e.g. Civil, Criminal, Constitutional, Taxation, Labour, Company, Service, etc.)</strong><br>
                <?= nl2br(esc($app['nature_of_practice'] ?? '—')) ?>
            </div>
            <div class="col-md-6">
                <strong>17. Field of Law — domain expertise … in which the applicant has specialization/expertise</strong><br>
                <?= nl2br(esc($app['field_of_law'] ?? '—')) ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-mhc mb-3">
    <div class="card-header">18–24. Prior applications &amp; declarations</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <strong>18. Whether the applicant has applied earlier to the Madras High Court for designation; If so, date of the application &amp; current status thereof</strong><br>
                <?= ssa_bool_label($app['applied_mhc_earlier'] ?? null) ?>
                <?php if (! empty($app['applied_mhc_date']) || ! empty($app['applied_mhc_status'])): ?>
                    <br><?= esc($app['applied_mhc_date'] ?? '') ?> <?= esc($app['applied_mhc_status'] ?? '') ?>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>19. Whether the applicant has applied earlier to the Supreme Court, or any other High Court; if so, date of the application and details thereof</strong><br>
                <?= ssa_bool_label($app['applied_other_court'] ?? null) ?>
                <?php if (! empty($app['applied_other_date']) || ! empty($app['applied_other_details'])): ?>
                    <br><?= esc($app['applied_other_date'] ?? '') ?> <?= esc($app['applied_other_details'] ?? '') ?>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>20. Whether any FIR has ever been lodged against the applicant; if so, details thereof</strong><br>
                <?= ssa_bool_label($app['fir_lodged'] ?? null) ?>
                <?php if (! empty($app['fir_details'])): ?><br><?= nl2br(esc($app['fir_details'])) ?><?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>21. Whether the applicant is a party to any criminal case; if so, details thereof</strong><br>
                <?= ssa_bool_label($app['criminal_case_party'] ?? null) ?>
                <?php if (! empty($app['criminal_case_details'])): ?><br><?= nl2br(esc($app['criminal_case_details'])) ?><?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>22. Whether any proceedings were initiated or are pending against the applicant before Bar Council of India or State Bar Council; if so, details thereof</strong><br>
                <?= ssa_bool_label($app['bar_council_proceedings'] ?? null) ?>
                <?php if (! empty($app['bar_council_details'])): ?><br><?= nl2br(esc($app['bar_council_details'])) ?><?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>23. General State of Health</strong><br>
                <?= esc($app['general_health'] ?? '—') ?>
            </div>
            <div class="col-12">
                <strong>24. Any other information</strong><br>
                <?= nl2br(esc($app['other_information'] ?? '—')) ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-mhc mb-3">
    <div class="card-header">Uploads</div>
    <div class="card-body">
        <div class="review-identity justify-content-md-start mb-3">
            <div class="review-identity-item">
                <span class="review-label">Recent Passport Size Colour Photograph</span>
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
        <div class="row g-3">
            <?php
            $files = [
                'enrolment_cert' => 'Enrolment Certificate',
                'age_proof'      => 'Age proof',
                'education_qual' => 'Educational qualifications document',
                'format_l1'      => 'Format L-1 (Reported Judgments)',
                'format_l2'      => 'Format L-2 (Unreported Judgments)',
                'format_l3i'     => 'Format L-3(i) Pro Bono',
                'format_l3ii'    => 'Format L-3(ii) Amicus Curiae',
                'format_l4'      => 'Format L-4 Academic',
            ];
            foreach ($files as $key => $label):
                $col = $key . '_path';
            ?>
                <div class="col-md-4 col-lg-3">
                    <div class="border rounded p-2 h-100">
                        <div class="small text-muted"><?= esc($label) ?></div>
                        <?php if (! empty($app[$col])): ?>
                            <a href="<?= base_url('files/application/' . $app['id'] . '/' . $key) ?>" target="_blank">View file</a>
                        <?php else: ?>
                            <span class="text-<?= $key === 'education_qual' ? 'muted' : 'danger' ?> small">
                                <?= $key === 'education_qual' ? 'Not uploaded (optional)' : 'Not uploaded' ?>
                            </span>
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
