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
            $posted = old($field);
            if ($posted !== null && $posted !== false && $posted !== '') {
                return (string) $posted;
            }
            $v = ssa_bool_label($app[$field] ?? null);
            return $v === 'Yes' ? '1' : ($v === 'No' ? '0' : '');
        };
        $mhcYes   = $yn('applied_mhc_earlier', $app) === '1';
        $otherYes = $yn('applied_other_court', $app) === '1';
        $firYes   = $yn('fir_lodged', $app) === '1';
        $crimYes  = $yn('criminal_case_party', $app) === '1';
        $bcYes    = $yn('bar_council_proceedings', $app) === '1';
        $notificationDate = $notificationDate ?? ($ageAsOnDate ?? ssa_age_as_on_date($app ?? null));
        $enrolmentDate    = $enrolmentDate ?? \App\Libraries\ApplicationDateRules::parseDate($app['enrolment_date'] ?? null);
        $dateMin          = $decidedOnMin ?? \App\Libraries\ApplicationDateRules::decidedOnMin($enrolmentDate);
        $dateMax          = $decidedOnMax ?? \App\Libraries\ApplicationDateRules::decidedOnMax($notificationDate);
        $enrolLabel       = $enrolmentDate ? date('d-m-Y', strtotime((string) $enrolmentDate)) : '';
        $notifLabel       = $notificationDate ? date('d-m-Y', strtotime((string) $notificationDate)) : '';
        $priorDateHelp    = 'Must be between the date of enrolment'
            . ($enrolLabel !== '' ? ' (' . $enrolLabel . ')' : '')
            . ' and the notification date'
            . ($notifLabel !== '' ? ' (' . $notifLabel . ')' : '')
            . '.';
        $isoDate = static function (string $field, array $app): string {
            $raw = old($field, isset($app[$field]) ? (string) $app[$field] : '');
            if ($raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
                return substr($raw, 0, 10);
            }

            return is_string($raw) ? $raw : '';
        };
        $mhcDate   = $isoDate('applied_mhc_date', $app);
        $otherDate = $isoDate('applied_other_date', $app);
        ?>

        <div class="section-title">18. Whether the applicant has applied earlier to the Madras High Court for designation; If so, date of the application & current status thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="applied_mhc_earlier" class="form-select" data-toggle-detail="#mhcDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $mhcYes ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('applied_mhc_earlier', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="mhcDetail">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label<?= $mhcYes ? ' required' : '' ?>">Date of application</label>
                        <input type="date" name="applied_mhc_date" class="form-control"
                               value="<?= esc($mhcDate) ?>"
                               <?= $dateMin ? 'min="' . esc($dateMin) . '"' : '' ?>
                               <?= $dateMax ? 'max="' . esc($dateMax) . '"' : '' ?>
                               title="Must be between the date of enrolment and the notification date"
                               <?= $mhcYes ? 'required' : '' ?>>
                        <div class="form-text"><?= esc($priorDateHelp) ?></div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label<?= $mhcYes ? ' required' : '' ?>">Details</label>
                        <input type="text" name="applied_mhc_status" class="form-control"
                               value="<?= esc(old('applied_mhc_status', $app['applied_mhc_status'] ?? '')) ?>"
                               <?= $mhcYes ? 'required' : '' ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">19. Whether the applicant has applied earlier to the Supreme Court, or any other High Court; if so, date of the application and details thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="applied_other_court" class="form-select" data-toggle-detail="#otherCourtDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $otherYes ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('applied_other_court', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="otherCourtDetail">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label<?= $otherYes ? ' required' : '' ?>">Date of application</label>
                        <input type="date" name="applied_other_date" class="form-control"
                               value="<?= esc($otherDate) ?>"
                               <?= $dateMin ? 'min="' . esc($dateMin) . '"' : '' ?>
                               <?= $dateMax ? 'max="' . esc($dateMax) . '"' : '' ?>
                               title="Must be between the date of enrolment and the notification date"
                               <?= $otherYes ? 'required' : '' ?>>
                        <div class="form-text"><?= esc($priorDateHelp) ?></div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label<?= $otherYes ? ' required' : '' ?>">Details thereof</label>
                        <input type="text" name="applied_other_details" class="form-control"
                               value="<?= esc(old('applied_other_details', $app['applied_other_details'] ?? '')) ?>"
                               <?= $otherYes ? 'required' : '' ?>>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">20. Whether any FIR has ever been lodged against the applicant; if so, details thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 pt-4 mt-4">
                <select name="fir_lodged" class="form-select" data-toggle-detail="#firDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $firYes ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('fir_lodged', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="firDetail">
                <label class="form-label<?= $firYes ? ' required' : '' ?>">Details thereof</label>
                <textarea name="fir_details" class="form-control" rows="2" <?= $firYes ? 'required' : '' ?>><?= esc(old('fir_details', $app['fir_details'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="section-title">21. Whether the applicant is a party to any criminal case; if so, details thereof:</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="criminal_case_party" class="form-select" data-toggle-detail="#crimDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $crimYes ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('criminal_case_party', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="crimDetail">
                <label class="form-label<?= $crimYes ? ' required' : '' ?>">Details thereof</label>
                <textarea name="criminal_case_details" class="form-control" rows="2" <?= $crimYes ? 'required' : '' ?>><?= esc(old('criminal_case_details', $app['criminal_case_details'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="section-title">22. Whether any proceedings were initiated or are pending against the applicant before Bar Council of India or State Bar Council; if so, details thereof</div>
        <div class="row g-3 mb-3">
            <div class="col-md-3 mt-4 pt-4">
                <select name="bar_council_proceedings" class="form-select" data-toggle-detail="#bcDetail">
                    <option value="">— Select —</option>
                    <option value="1" <?= $bcYes ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $yn('bar_council_proceedings', $app) === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-9" id="bcDetail">
                <label class="form-label<?= $bcYes ? ' required' : '' ?>">Details thereof</label>
                <textarea name="bar_council_details" class="form-control" rows="2" <?= $bcYes ? 'required' : '' ?>><?= esc(old('bar_council_details', $app['bar_council_details'] ?? '')) ?></textarea>
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
