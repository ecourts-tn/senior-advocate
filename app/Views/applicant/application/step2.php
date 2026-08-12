<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 2 of 7 — Enrolment &amp; Practice (Sl. No. 7–8)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open_multipart('applicant/application/step/2', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
        ]) ?>
        <?php
        $ageAsOnDate  = $ageAsOnDate ?? ssa_age_as_on_date($app ?? null);
        $ageAsOnLabel = $ageAsOnLabel ?? ssa_age_as_on_label($app ?? null);
        $enrolDate    = old('enrolment_date', isset($app['enrolment_date']) ? substr((string) $app['enrolment_date'], 0, 10) : '');
        $practiceYears  = old('practice_years', $app['practice_years'] ?? '');
        $practiceMonths = old('practice_months', $app['practice_months'] ?? '');
        ?>
        <div class="section-title">7. Enrolment Details</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label required">(i) Date, Month and Year of Enrolment as an Advocate</label>
                <input type="date" name="enrolment_date" id="enrolment_date" class="form-control" required
                       max="<?= esc($ageAsOnDate) ?>"
                       value="<?= esc($enrolDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label required">(ii) Enrolment Number</label>
                <?php
                $enrolmentLocked = ! empty($enrolmentFromAccount);
                $enrolmentValue  = old(
                    'enrolment_number',
                    $app['enrolment_number'] ?? ($enrolmentFromAccount ?? '')
                );
                ?>
                <input type="text" name="enrolment_number" class="form-control <?= $enrolmentLocked ? 'bg-light' : '' ?>"
                       required
                       <?= $enrolmentLocked ? 'readonly tabindex="-1"' : '' ?>
                       value="<?= esc($enrolmentValue) ?>"
                       autocomplete="off">
                <?php if ($enrolmentLocked): ?>
                    <div class="form-text">From your registration (not editable).</div>
                <?php else: ?>
                    <div class="form-text">Enter the enrolment number as on your certificate.</div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label required">(iii) Bar Council where registered (Copy of Enrolment Certificate to be attached)</label>
                <input type="text" name="bar_council" class="form-control" required
                       value="<?= esc(old('bar_council', $app['bar_council'] ?? '')) ?>"
                       placeholder="e.g. Bar Council of Tamil Nadu & Puducherry">
            </div>
            <div class="col-md-6">
                <label class="form-label required" id="practiceAsOnLabel">
                    (iv) Number of years of practice from the date of enrolment (as on <?= esc($ageAsOnLabel) ?>)
                </label>
                <div class="row g-2" role="group" aria-labelledby="practiceAsOnLabel">
                    <div class="col-6">
                        <div class="input-group">
                            <input type="number" name="practice_years" id="practice_years"
                                   class="form-control text-center" min="0" max="70" required
                                   value="<?= esc($practiceYears !== null && $practiceYears !== '' ? (string) (int) $practiceYears : '') ?>"
                                   placeholder="0" aria-label="Years">
                            <span class="input-group-text">Years</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <input type="number" name="practice_months" id="practice_months"
                                   class="form-control text-center" min="0" max="11" required
                                   value="<?= esc($practiceMonths !== null && $practiceMonths !== '' ? (string) (int) $practiceMonths : '') ?>"
                                   placeholder="0" aria-label="Months">
                            <span class="input-group-text">Months</span>
                        </div>
                    </div>
                </div>
                <!-- <div class="form-text">
                    Enter the number of years and months of practice from the date of enrolment as on <?= esc($ageAsOnLabel) ?>.
                </div> -->
            </div>
            <div class="col-md-6">
                <label class="form-label">(v) Net Professional Income per annum (in Lakhs of Rs) [Only earnings through practice as Advocate]</label>
                <input type="number" step="0.01" name="net_income_lakhs" class="form-control"
                       value="<?= esc(old('net_income_lakhs', $app['net_income_lakhs'] ?? '')) ?>">
                <div class="form-text">Only earnings through practice as Advocate.</div>
            </div>
        </div>

        <div class="section-title mt-4">8. Whether the applicant is a member of any bar association attached to a specific court (eg. Madras High Court Advocates Association, Madurai High Court Advocates Association, or any district bar association)</div>
        <div class="row g-3">
            <div class="col-md-6 mt-4 pt-4">
                <select name="is_bar_association_member" class="form-select" data-toggle-detail="#barAssocDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= old('is_bar_association_member', ssa_bool_label($app['is_bar_association_member'] ?? null) === 'Yes' ? '1' : '') === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= old('is_bar_association_member', ssa_bool_label($app['is_bar_association_member'] ?? null) === 'No' ? '0' : '') === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-6" id="barAssocDetail">
                <label class="form-label">Name of Bar Association</label>
                <input type="text" name="bar_association_name" class="form-control"
                       value="<?= esc(old('bar_association_name', $app['bar_association_name'] ?? '')) ?>"
                       placeholder="e.g. Madras High Court Advocates Association">
            </div>
        </div>
        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
