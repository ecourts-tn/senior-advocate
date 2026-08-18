<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$courtTypeLabels = \App\Models\ApplicationModel::PRACTICE_COURT_LABELS;
$tribunalOptions = $lookupOptions['tribunal'] ?? [];
$natureOptions   = $lookupOptions['nature_of_practice'] ?? [];
$fieldOptions    = $lookupOptions['field_of_law'] ?? [];

$periodDate = static function (array $row, string $key): string {
    $alt = $key === 'from_date' ? 'from' : 'to';
    $raw = trim((string) ($row[$key] ?? $row[$alt] ?? ''));
    if ($raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $raw)) {
        return substr($raw, 0, 10);
    }

    return '';
};
$notificationDate = $notificationDate ?? ($ageAsOnDate ?? ssa_age_as_on_date($app ?? null));
$enrolmentDate    = $enrolmentDate ?? \App\Libraries\ApplicationDateRules::parseDate($app['enrolment_date'] ?? null);
$practiceMin      = $enrolmentDate ?: null;
$practiceMax      = $notificationDate ?: null;
$enrolLabel       = $enrolmentDate ? date('d-m-Y', strtotime($enrolmentDate)) : '';
$notifLabel       = $notificationDate ? date('d-m-Y', strtotime((string) $notificationDate)) : '';
$practiceRangeHelp = 'Leave "<em>To (date)</em>" blank if still practicing there. '
    . 'From and To dates must fall between the date of enrolment'
    . ($enrolLabel !== '' ? ' (' . $enrolLabel . ')' : '')
    . ' and the notification date'
    . ($notifLabel !== '' ? ' (' . $notifLabel . ')' : '')
    . '';

$rangeMinAttr = $practiceMin ? ' data-range-min="' . esc($practiceMin, 'attr') . '"' : '';
$rangeMaxAttr = $practiceMax ? ' data-range-max="' . esc($practiceMax, 'attr') . '"' : '';

$postedCourtTypes = old('court_type');
if (is_array($postedCourtTypes)) {
    $postedFrom   = (array) (old('court_from') ?? []);
    $postedTo     = (array) (old('court_to') ?? []);
    $postedNames  = (array) (old('court_name') ?? []);
    $app['courts_practiced'] = [];
    foreach ($postedCourtTypes as $i => $type) {
        $type = (string) $type;
        $app['courts_practiced'][] = [
            'court_type' => $type,
            'court'      => $type === \App\Models\ApplicationModel::PRACTICE_COURT_SUPREME
                ? \App\Models\ApplicationModel::PRACTICE_COURT_LABELS[$type]
                : (string) ($postedNames[$i] ?? ''),
            'from_date'  => $postedFrom[$i] ?? '',
            'to_date'    => $postedTo[$i] ?? '',
        ];
    }
}
$postedTribNames = old('tribunal_name');
if (is_array($postedTribNames)) {
    $postedTFrom  = (array) (old('tribunal_from') ?? []);
    $postedTTo    = (array) (old('tribunal_to') ?? []);
    $postedTOther = (array) (old('tribunal_other') ?? []);
    $app['tribunals_practiced'] = [];
    foreach ($postedTribNames as $i => $name) {
        $resolved = \App\Models\MasterRegistry::resolveSingle((string) $name, $postedTOther[$i] ?? null);
        $app['tribunals_practiced'][] = [
            'tribunal'  => $resolved !== '' ? $resolved : (string) $name,
            'from_date' => $postedTFrom[$i] ?? '',
            'to_date'   => $postedTTo[$i] ?? '',
        ];
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 5 of 7 — Practice Domain (Sl. No. 14–17)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/5', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
        ]) ?>

        <div class="section-title">14. Courts where the applicant is practicing / has practiced <span class="text-danger">*</span> <span class="small text-muted">(Court-wise period may be indicated)</span></div>
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#courtRows">+ Add court</button>
        </div>
        <div id="courtRows" data-rows>
            <?php
            $courts = ! empty($app['courts_practiced']) ? $app['courts_practiced'] : [
                ['court' => '', 'court_type' => '', 'from_date' => '', 'to_date' => ''],
            ];
            $hcDistrictVal = \App\Models\ApplicationModel::PRACTICE_COURT_HC_DISTRICT;

            $renderPracticeCourtFields = static function (
                array $c,
                bool $showLabel,
                bool $required,
                bool $disabled
            ) use ($courtTypeLabels, $hcDistrictVal, $periodDate, $practiceMin, $practiceMax, $rangeMinAttr, $rangeMaxAttr): void {
                $type     = \App\Models\ApplicationModel::practiceCourtTypeFromRow($c);
                $detail   = \App\Models\ApplicationModel::practiceCourtDetailFromRow($c);
                $showName = $type === $hcDistrictVal;
                $fromVal  = $periodDate($c, 'from_date');
                $toMin    = $fromVal !== '' ? $fromVal : $practiceMin;
                $dis      = $disabled ? ' disabled' : '';
                ?>
                <div class="col-md-5">
                    <?php if ($showLabel): ?>
                        <label class="form-label<?= $required ? ' required' : '' ?>">Court</label>
                    <?php endif; ?>
                    <select name="court_type[]" class="form-select" data-practice-court-type
                            <?= $required ? ' required' : '' ?><?= $dis ?>>
                        <option value="">Select court…</option>
                        <?php foreach ($courtTypeLabels as $key => $lab): ?>
                            <option value="<?= esc($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= esc($lab) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="mt-1" data-practice-court-name <?= $showName ? '' : 'hidden' ?>>
                        <input type="text" name="court_name[]" class="form-control"
                               value="<?= esc($detail) ?>"
                               placeholder="Enter court name"
                               maxlength="255"
                               <?= $disabled ? 'disabled' : '' ?>>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php if ($showLabel): ?><label class="form-label">From (date)</label><?php endif; ?>
                    <input type="date" name="court_from[]" class="form-control"
                           value="<?= esc($periodDate($c, 'from_date')) ?>"
                           data-period="from"
                           <?= $practiceMin ? 'min="' . esc($practiceMin) . '"' : '' ?>
                           <?= $practiceMax ? 'max="' . esc($practiceMax) . '"' : '' ?>
                           <?= $rangeMinAttr ?><?= $rangeMaxAttr ?>
                           title="Must be between enrolment date and notification date"<?= $dis ?>>
                </div>
                <div class="col-md-3">
                    <?php if ($showLabel): ?><label class="form-label">To (date)</label><?php endif; ?>
                    <input type="date" name="court_to[]" class="form-control"
                           value="<?= esc($periodDate($c, 'to_date')) ?>"
                           data-period="to"
                           <?= $toMin ? 'min="' . esc($toMin) . '"' : '' ?>
                           <?= $practiceMax ? 'max="' . esc($practiceMax) . '"' : '' ?>
                           <?= $rangeMinAttr ?><?= $rangeMaxAttr ?>
                           title="Must be on or after From date and on or before the notification date (leave blank if ongoing)"<?= $dis ?>>
                </div>
                <div class="col-md-1">
                    <?php if ($showLabel): ?><label class="form-label d-none d-md-block">&nbsp;</label><?php endif; ?>
                    <button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove">&times;</button>
                </div>
                <?php
            };

            foreach ($courts as $i => $c):
            ?>
                <div class="row g-2 mb-2 dynamic-row align-items-end">
                    <?php $renderPracticeCourtFields($c, $i === 0, $i === 0, false); ?>
                </div>
            <?php endforeach; ?>
            <!-- Hidden clone template last (disabled fields; not submitted) -->
            <div class="row g-2 mb-2 dynamic-row align-items-end d-none" data-row-template hidden aria-hidden="true">
                <?php $renderPracticeCourtFields(['court' => '', 'court_type' => '', 'from_date' => '', 'to_date' => ''], true, false, true); ?>
            </div>
        </div>
        <p class="form-text form-help-text"><?= $practiceRangeHelp ?> If <strong>High Court(s)/District/Trial Court(s)</strong> is selected, enter the court name.</p>

        <div class="section-title mt-4">15. Tribunals, where the applicant has specialized practice: <span class="small text-muted">(Applicable to those practising before Tribunals)</span></div>
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#tribRows">+ Add tribunal</button>
        </div>
        <div id="tribRows" data-rows>
            <?php
            $tribunals = ! empty($app['tribunals_practiced']) ? $app['tribunals_practiced'] : [
                ['tribunal' => '', 'from_date' => '', 'to_date' => ''],
            ];
            // Editable rows first (never disabled)
            foreach ($tribunals as $i => $t):
                $parsedT = \App\Models\MasterRegistry::parseSingleStored($t['tribunal'] ?? '', $tribunalOptions);
                $fromValT = $periodDate($t, 'from_date');
                $toMinT   = $fromValT !== '' ? $fromValT : $practiceMin;
            ?>
                <div class="row g-2 mb-2 dynamic-row align-items-end">
                    <div class="col-md-5">
                        <?= view('partials/select_others', [
                            'name'        => 'tribunal_name[]',
                            'otherName'   => 'tribunal_other[]',
                            'options'     => $tribunalOptions,
                            'value'       => $parsedT['value'],
                            'other'       => $parsedT['other'],
                            'placeholder' => 'Select tribunal…',
                            'showLabel'   => $i === 0,
                            'label'       => 'Tribunal(s)',
                            'disabled'    => false,
                        ], ['saveData' => false]) ?>
                    </div>
                    <div class="col-md-3">
                        <?php if ($i === 0): ?><label class="form-label">From (date)</label><?php endif; ?>
                        <input type="date" name="tribunal_from[]" class="form-control"
                               value="<?= esc($periodDate($t, 'from_date')) ?>"
                               data-period="from"
                               <?= $practiceMin ? 'min="' . esc($practiceMin) . '"' : '' ?>
                               <?= $practiceMax ? 'max="' . esc($practiceMax) . '"' : '' ?>
                               <?= $rangeMinAttr ?><?= $rangeMaxAttr ?>
                               title="Must be between enrolment date and notification date">
                    </div>
                    <div class="col-md-3">
                        <?php if ($i === 0): ?><label class="form-label">To (date)</label><?php endif; ?>
                        <input type="date" name="tribunal_to[]" class="form-control"
                               value="<?= esc($periodDate($t, 'to_date')) ?>"
                               data-period="to"
                               <?= $toMinT ? 'min="' . esc($toMinT) . '"' : '' ?>
                               <?= $practiceMax ? 'max="' . esc($practiceMax) . '"' : '' ?>
                               <?= $rangeMinAttr ?><?= $rangeMaxAttr ?>
                               title="Must be on or after From date and on or before the notification date (leave blank if ongoing)">
                    </div>
                    <div class="col-md-1">
                        <?php if ($i === 0): ?><label class="form-label d-none d-md-block">&nbsp;</label><?php endif; ?>
                        <button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove">&times;</button>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- Hidden clone template last (disabled fields; not submitted) -->
            <div class="row g-2 mb-2 dynamic-row align-items-end d-none" data-row-template hidden aria-hidden="true">
                <div class="col-md-5">
                    <?= view('partials/select_others', [
                        'name'        => 'tribunal_name[]',
                        'otherName'   => 'tribunal_other[]',
                        'options'     => $tribunalOptions,
                        'value'       => '',
                        'other'       => '',
                        'placeholder' => 'Select tribunal…',
                        'showLabel'   => true,
                        'label'       => 'Tribunal',
                        'disabled'    => true,
                    ], ['saveData' => false]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From (date)</label>
                    <input type="date" name="tribunal_from[]" class="form-control" disabled
                           data-period="from"
                           <?= $practiceMin ? 'min="' . esc($practiceMin) . '"' : '' ?>
                           <?= $practiceMax ? 'max="' . esc($practiceMax) . '"' : '' ?>
                           <?= $rangeMinAttr ?><?= $rangeMaxAttr ?>
                           title="Must be between enrolment date and notification date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To (date)</label>
                    <input type="date" name="tribunal_to[]" class="form-control" disabled
                           data-period="to"
                           <?= $practiceMin ? 'min="' . esc($practiceMin) . '"' : '' ?>
                           <?= $practiceMax ? 'max="' . esc($practiceMax) . '"' : '' ?>
                           <?= $rangeMinAttr ?><?= $rangeMaxAttr ?>
                           title="Must be on or after From date and on or before the notification date (leave blank if ongoing)">
                </div>
                <div class="col-md-1">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove">&times;</button>
                </div>
            </div>
        </div>
        <p class="form-text form-help-text"><?= $practiceRangeHelp ?> Choose <strong>Others</strong> to enter a tribunal not listed.</p>

        <div class="section-title mt-4">16. Nature of practice (e.g. Civil, Criminal, Constitutional, Taxation, Labour, Company, Service, etc.) <span class="text-danger">*</span></div>
        <?php
        $natureMulti = $app['_multi']['nature_of_practice'] ?? null;
        if ($natureMulti === null) {
            $natureMulti = \App\Models\MasterRegistry::parseMultiStored($app['nature_of_practice'] ?? '', $natureOptions);
        }
        $natureSel = old('nature_of_practice');
        if (is_array($natureSel)) {
            $natureMulti['selected'] = $natureSel;
            $natureMulti['other']    = (string) old('nature_of_practice_other', '');
        }
        echo view('partials/multi_select_others', [
            'name'     => 'nature_of_practice',
            'options'  => $natureOptions,
            'selected' => $natureMulti['selected'] ?? [],
            'other'    => $natureMulti['other'] ?? '',
            'required' => true,
            'help'     => 'Select at least one option (stored as related master records).',
        ]);
        ?>

        <div class="section-title mt-4">17. Field of Law — domain expertise (such as Constitutional Law, Inter-State Water Disputes, Criminal Law, Arbitration Law, Corporate Law, Family Law, Human Rights, Public Interest Litigation, International Law, law relating to women) in which the applicant has specialization/expertise <span class="text-danger">*</span></div>
        <?php
        $fieldMulti = $app['_multi']['field_of_law'] ?? null;
        if ($fieldMulti === null) {
            $fieldMulti = \App\Models\MasterRegistry::parseMultiStored($app['field_of_law'] ?? '', $fieldOptions);
        }
        $fieldSel = old('field_of_law');
        if (is_array($fieldSel)) {
            $fieldMulti['selected'] = $fieldSel;
            $fieldMulti['other']    = (string) old('field_of_law_other', '');
        }
        echo view('partials/multi_select_others', [
            'name'     => 'field_of_law',
            'options'  => $fieldOptions,
            'selected' => $fieldMulti['selected'] ?? [],
            'other'    => $fieldMulti['other'] ?? '',
            'required' => true,
            'help'     => 'Select at least one option (stored as related master records).',
        ]);
        ?>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
