<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 1 of 7 — Personal Details (Sl. No. 1–6)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/1', [
            'id'                   => 'stepForm',
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
            'novalidate'           => 'novalidate',
        ]) ?>
        <div class="section-title">Personal Particulars</div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label required">1. Name of the Applicant-Advocate: (Dr./Mr./Ms./Mrs.)</label>      
                </div>
                <div class="col-md-2">
                    <select name="title" class="form-select" required>
                        <?php foreach (['Dr.', 'Mr.', 'Ms.', 'Mrs.'] as $t): ?>
                            <option value="<?= $t ?>" <?= old('title', $app['title'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-10">
                    <input type="text" name="full_name" class="form-control" required
                        value="<?= esc(old('full_name', $app['full_name'] ?? '')) ?>">
                    <div class="form-text form-help-text">Name of the applicant should tally with his/her name as mentioned in
                    his/her enrolment certificate. Abbreviated name shall not be accepted.</div>
                </div>
            <?php
            $ageAsOnDate  = $ageAsOnDate ?? ssa_age_as_on_date($app ?? null);
            $ageAsOnLabel = $ageAsOnLabel ?? ssa_age_as_on_label($app ?? null);
            $dobMin       = \App\Libraries\ApplicationDateRules::DATE_OF_BIRTH_MIN;
            $dobValue     = old('date_of_birth', $app['date_of_birth'] ?? '');
            if (is_string($dobValue) && $dobValue !== '') {
                $dobValue = substr($dobValue, 0, 10);
            }
            $ageYears  = $app['age_years'] ?? null;
            $ageMonths = $app['age_months'] ?? null;
            $ageDays   = $app['age_days'] ?? null;
            // Always recompute from DOB so fields show the correct values immediately.
            if ($dobValue !== '') {
                try {
                    $birth = new \DateTime($dobValue);
                    $ref   = new \DateTime($ageAsOnDate);
                    if ($birth <= $ref) {
                        $diff      = $birth->diff($ref);
                        $ageYears  = (int) $diff->y;
                        $ageMonths = (int) $diff->m;
                        $ageDays   = (int) $diff->d;
                    }
                } catch (\Exception $e) {
                    // keep existing values
                }
            }
            ?>
            <div class="col-md-3">
                <label class="form-label required" for="date_of_birth">2. Date of Birth</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" required
                       value="<?= esc($dobValue) ?>"
                       min="<?= esc($dobMin) ?>"
                       max="<?= esc($ageAsOnDate) ?>"
                       data-age-as-on="<?= esc($ageAsOnDate) ?>"
                       data-age-years-target="age_years_display"
                       data-age-months-target="age_months_display"
                       data-age-days-target="age_days_display">
            </div>
            <div class="col-md-9">
                <label class="form-label" id="ageAsOnLabel">
                    3. Age (as on <?= esc($ageAsOnLabel) ?>)
                </label>
                <div class="row g-2" role="group" aria-labelledby="ageAsOnLabel">
                    <div class="col-4">
                        <div class="input-group">
                            <input type="text" id="age_years_display" class="form-control bg-light text-center"
                                   value="<?= $ageYears !== null && $ageYears !== '' ? esc((string) (int) $ageYears) : '' ?>"
                                   placeholder="—" readonly tabindex="-1" autocomplete="off"
                                   aria-label="Years">
                            <span class="input-group-text">Years</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <input type="text" id="age_months_display" class="form-control bg-light text-center"
                                   value="<?= $ageMonths !== null && $ageMonths !== '' ? esc((string) (int) $ageMonths) : '' ?>"
                                   placeholder="—" readonly tabindex="-1" autocomplete="off"
                                   aria-label="Months">
                            <span class="input-group-text">Months</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group">
                            <input type="text" id="age_days_display" class="form-control bg-light text-center"
                                   value="<?= $ageDays !== null && $ageDays !== '' ? esc((string) (int) $ageDays) : '' ?>"
                                   placeholder="—" readonly tabindex="-1" autocomplete="off"
                                   aria-label="Days">
                            <span class="input-group-text">Days</span>
                        </div>
                    </div>
                </div>
                <div class="form-text">
                    Auto-calculated from date of birth as on the notification date (read-only).
                </div>
            </div>
            <div class="col-md-12">
                <p class="border-bottom pb-0 mb-0 form-label">4. Address in Full:</p>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Office</label>
                <textarea name="address_office" class="form-control" rows="3" required><?= esc(old('address_office', $app['address_office'] ?? '')) ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Residence</label>
                <textarea name="address_residence" class="form-control" rows="3" required><?= esc(old('address_residence', $app['address_residence'] ?? '')) ?></textarea>
            </div>
            <div class="col-md-12">
                <p class="border-bottom pb-0 mb-0 form-label">5. Contact Details</p>
            </div>
            <div class="col-md-4">
                <label class="form-label">Landline</label>
                <input type="text" name="phone_landline" class="form-control"
                       inputmode="tel" autocomplete="tel"
                       maxlength="20"
                       pattern="[0-9+\-()\/., ]*"
                       data-landline="1"
                       title="Numbers and special characters only (no letters)"
                       value="<?= esc(old('phone_landline', $app['phone_landline'] ?? '')) ?>">
                <div class="form-text">Numbers and special characters only — letters are not allowed.</div>
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
                <label class="form-label required">6. Educational / Professional Qualifications</label>
                <?php
                $qualLabels = $lookupOptions['qualification'] ?? [];
                $qualMulti  = $app['_multi']['qualification'] ?? null;
                if ($qualMulti === null) {
                    $qualMulti = \App\Models\MasterRegistry::parseMultiStored(
                        (string) ($app['qualifications'] ?? ''),
                        $qualLabels
                    );
                }
                $qualSelected = old('qualifications');
                if (is_array($qualSelected)) {
                    $qualMulti['selected'] = $qualSelected;
                    $qualMulti['other']    = (string) old('qualifications_other', '');
                }
                echo view('partials/multi_select_others', [
                    'name'             => 'qualifications',
                    'options'          => $qualLabels,
                    'selected'         => $qualMulti['selected'] ?? [],
                    'other'            => $qualMulti['other'] ?? '',
                    'required'         => true,
                    'requiredMessage'  => 'Sl. No. 6 (Educational / Professional Qualifications) is required.',
                    'help'             => 'Select all that apply. Choose Others to enter a qualification not listed.',
                ]);
                ?>
            </div>
        </div>
        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
