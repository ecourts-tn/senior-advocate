<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 1 of 7 — Personal Details (Sl. No. 1–5)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/1', ['id' => 'stepForm']) ?>
        <div class="section-title">1–5. Personal Particulars</div>
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label required">Title</label>
                <select name="title" class="form-select" required>
                    <?php foreach (['Dr.', 'Mr.', 'Ms.', 'Mrs.'] as $t): ?>
                        <option value="<?= $t ?>" <?= old('title', $app['title'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-10">
                <label class="form-label required">Name of the Applicant-Advocate</label>
                <input type="text" name="full_name" class="form-control" required
                       value="<?= esc(old('full_name', $app['full_name'] ?? '')) ?>">
                <div class="form-text">Must tally with enrolment certificate. No abbreviations.</div>
            </div>
            <?php
            $dobValue = old('date_of_birth', $app['date_of_birth'] ?? '');
            if (is_string($dobValue) && $dobValue !== '') {
                $dobValue = substr($dobValue, 0, 10);
            }
            $ageYears  = $app['age_years'] ?? null;
            $ageMonths = $app['age_months'] ?? null;
            // Always recompute from DOB so fields show the correct values immediately.
            if ($dobValue !== '') {
                try {
                    $birth = new \DateTime($dobValue);
                    $ref   = new \DateTime('2026-01-01');
                    if ($birth <= $ref) {
                        $diff      = $birth->diff($ref);
                        $ageYears  = (int) $diff->y;
                        $ageMonths = (int) $diff->m;
                    }
                } catch (\Exception $e) {
                    // keep existing values
                }
            }
            ?>
            <div class="col-md-4">
                <label class="form-label required" for="date_of_birth">Date of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" required
                       value="<?= esc($dobValue) ?>"
                       max="2026-01-01"
                       data-age-as-on="2026-01-01"
                       data-age-years-target="age_years_display"
                       data-age-months-target="age_months_display">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="age_years_display">Age — Years (as on 01.01.2026)</label>
                <input type="text" id="age_years_display" class="form-control bg-light"
                       value="<?= $ageYears !== null && $ageYears !== '' ? esc((string) (int) $ageYears) : '' ?>"
                       placeholder="Auto-calculated" readonly tabindex="-1" autocomplete="off">
                <div class="form-text">Auto-calculated from date of birth (read-only).</div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="age_months_display">Age — Months (as on 01.01.2026)</label>
                <input type="text" id="age_months_display" class="form-control bg-light"
                       value="<?= $ageMonths !== null && $ageMonths !== '' ? esc((string) (int) $ageMonths) : '' ?>"
                       placeholder="Auto-calculated" readonly tabindex="-1" autocomplete="off">
                <div class="form-text">Auto-calculated remainder months (read-only).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Office Address</label>
                <textarea name="address_office" class="form-control" rows="3" required><?= esc(old('address_office', $app['address_office'] ?? '')) ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Residential Address</label>
                <textarea name="address_residence" class="form-control" rows="3" required><?= esc(old('address_residence', $app['address_residence'] ?? '')) ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Landline</label>
                <input type="text" name="phone_landline" class="form-control"
                       value="<?= esc(old('phone_landline', $app['phone_landline'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label required">Mobile</label>
                <input type="text" name="mobile" class="form-control" required maxlength="15" readonly
                       value="<?= esc(old('mobile', $app['mobile'] ?? session('mobile') ?? '')) ?>">
                <div class="form-text">From registration (not editable).</div>
            </div>
            <div class="col-md-4">
                <label class="form-label required">Email</label>
                <input type="email" name="email" class="form-control" required readonly
                       value="<?= esc(old('email', $app['email'] ?? session('email') ?? '')) ?>">
                <div class="form-text">From registration (not editable).</div>
            </div>
            <div class="col-12">
                <label class="form-label required">Educational / Professional Qualifications</label>
                <textarea name="qualifications" class="form-control" rows="3" required><?= esc(old('qualifications', $app['qualifications'] ?? '')) ?></textarea>
            </div>
        </div>
        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
