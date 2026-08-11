<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 6 of 7 — Prior Applications &amp; Declarations (Sl. No. 18–24)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/6', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
        ]) ?>

        <?php
        $yn = static function ($field, $app) {
            $v = ssa_bool_label($app[$field] ?? null);
            return $v === 'Yes' ? '1' : ($v === 'No' ? '0' : '');
        };
        ?>

        <div class="section-title">18. Whether the applicant has applied earlier to the Madras High Court for designation; If so, date of the application & current status thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="applied_mhc_earlier" class="form-select" data-toggle-detail="#mhcDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $yn('applied_mhc_earlier', $app) === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('applied_mhc_earlier', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="mhcDetail">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Date of application</label>
                        <input type="date" name="applied_mhc_date" class="form-control" value="<?= esc($app['applied_mhc_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Details</label>
                        <input type="text" name="applied_mhc_status" class="form-control" value="<?= esc($app['applied_mhc_status'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">19. Whether the applicant has applied earlier to the Supreme Court, or any other High Court; if so, date of the application and details thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="applied_other_court" class="form-select" data-toggle-detail="#otherCourtDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $yn('applied_other_court', $app) === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('applied_other_court', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="otherCourtDetail">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Date of application</label>
                        <input type="date" name="applied_other_date" class="form-control" value="<?= esc($app['applied_other_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Details thereof</label>
                        <input type="text" name="applied_other_details" class="form-control" value="<?= esc($app['applied_other_details'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">20. Whether any FIR has ever been lodged against the applicant; if so, details thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 pt-4 mt-4">
                <select name="fir_lodged" class="form-select" data-toggle-detail="#firDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $yn('fir_lodged', $app) === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('fir_lodged', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="firDetail">
                <label class="form-label">Details thereof</label>
                <textarea name="fir_details" class="form-control" rows="2"><?= esc($app['fir_details'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="section-title">21. Whether the applicant is a party to any criminal case; if so, details thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="criminal_case_party" class="form-select" data-toggle-detail="#crimDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $yn('criminal_case_party', $app) === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('criminal_case_party', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="crimDetail">
                <label class="form-label">Details thereof</label>
                <textarea name="criminal_case_details" class="form-control" rows="2"><?= esc($app['criminal_case_details'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="section-title">22. Whether any proceedings were initiated or are pending against the applicant before Bar Council of India or State Bar Council; if so, details thereof</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="bar_council_proceedings" class="form-select" data-toggle-detail="#bcDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $yn('bar_council_proceedings', $app) === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('bar_council_proceedings', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="bcDetail">
                <label class="form-label">Details thereof</label>
                <textarea name="bar_council_details" class="form-control" rows="2"><?= esc($app['bar_council_details'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="section-title">23. General State of Health</div>
        <textarea name="general_health" class="form-control mb-3" rows="2"><?= esc($app['general_health'] ?? '') ?></textarea>

        <div class="section-title">24. Any other information</div>
        <textarea name="other_information" class="form-control" rows="3"><?= esc($app['other_information'] ?? '') ?></textarea>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
