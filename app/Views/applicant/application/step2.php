<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 2 of 7 — Enrolment &amp; Practice (Sl. No. 6–8)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open_multipart('applicant/application/step/2') ?>
        <div class="section-title">6. Enrolment as Advocate</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">Date of Enrolment</label>
                <input type="date" name="enrolment_date" class="form-control" required
                       value="<?= esc(old('enrolment_date', $app['enrolment_date'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label required">Enrolment Number</label>
                <input type="text" name="enrolment_number" class="form-control" required
                       value="<?= esc(old('enrolment_number', $app['enrolment_number'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label required">Bar Council where registered</label>
                <input type="text" name="bar_council" class="form-control" required
                       value="<?= esc(old('bar_council', $app['bar_council'] ?? '')) ?>"
                       placeholder="e.g. Bar Council of Tamil Nadu & Puducherry">
            </div>
            <div class="col-md-3">
                <label class="form-label">Years of practice</label>
                <input type="number" name="practice_years" class="form-control" min="0" max="70"
                       value="<?= esc(old('practice_years', $app['practice_years'] ?? 0)) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Months</label>
                <input type="number" name="practice_months" class="form-control" min="0" max="11"
                       value="<?= esc(old('practice_months', $app['practice_months'] ?? 0)) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Net Professional Income per annum (₹ Lakhs)</label>
                <input type="number" step="0.01" name="net_income_lakhs" class="form-control"
                       value="<?= esc(old('net_income_lakhs', $app['net_income_lakhs'] ?? '')) ?>">
                <div class="form-text">Only earnings through practice as Advocate.</div>
            </div>
        </div>

        <div class="section-title mt-4">8. Bar Association Membership</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Member of any bar association attached to a specific court?</label>
                <select name="is_bar_association_member" class="form-select" data-toggle-detail="#barAssocDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= old('is_bar_association_member', sad_bool_label($app['is_bar_association_member'] ?? null) === 'Yes' ? '1' : '') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= old('is_bar_association_member', sad_bool_label($app['is_bar_association_member'] ?? null) === 'No' ? '0' : '') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-8" id="barAssocDetail">
                <label class="form-label">Name of Bar Association</label>
                <input type="text" name="bar_association_name" class="form-control"
                       value="<?= esc(old('bar_association_name', $app['bar_association_name'] ?? '')) ?>"
                       placeholder="e.g. Madras High Court Advocates Association">
            </div>
        </div>
        <p class="form-text mt-3">Enrolment Certificate PDF upload is available on Step 7 (Uploads).</p>
        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
