<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$courtOptions    = $lookupOptions['court'] ?? [];
$tribunalOptions = $lookupOptions['tribunal'] ?? [];
$natureOptions   = $lookupOptions['nature_of_practice'] ?? [];
$fieldOptions    = $lookupOptions['field_of_law'] ?? [];

$periodDate = static function (array $row, string $key): string {
    $alt = $key === 'from_date' ? 'from' : 'to';
    $raw = trim((string) ($row[$key] ?? $row[$alt] ?? ''));
    if ($raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        return $raw;
    }

    return '';
};
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

        <div class="section-title">14. Courts where the applicant is practicing / has practiced <span class="small text-muted">(Court-wise period may be indicated)</span></div>
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#courtRows">+ Add court</button>
        </div>
        <div id="courtRows" data-rows>
            <?php
            $courts = ! empty($app['courts_practiced']) ? $app['courts_practiced'] : [
                ['court' => '', 'from_date' => '', 'to_date' => ''],
            ];
            // Editable rows first (never disabled)
            foreach ($courts as $i => $c):
                $parsed = \App\Models\MasterRegistry::parseSingleStored($c['court'] ?? '', $courtOptions);
            ?>
                <div class="row g-2 mb-2 dynamic-row align-items-end">
                    <div class="col-md-5">
                        <?= view('partials/select_others', [
                            'name'        => 'court_name[]',
                            'otherName'   => 'court_other[]',
                            'options'     => $courtOptions,
                            'value'       => $parsed['value'],
                            'other'       => $parsed['other'],
                            'placeholder' => 'Select court…',
                            'showLabel'   => $i === 0,
                            'label'       => 'Court',
                            'disabled'    => false,
                        ], ['saveData' => false]) ?>
                    </div>
                    <div class="col-md-3">
                        <?php if ($i === 0): ?><label class="form-label">From (date)</label><?php endif; ?>
                        <input type="date" name="court_from[]" class="form-control"
                               value="<?= esc($periodDate($c, 'from_date')) ?>"
                               title="Practice from date">
                    </div>
                    <div class="col-md-3">
                        <?php if ($i === 0): ?><label class="form-label">To (date)</label><?php endif; ?>
                        <input type="date" name="court_to[]" class="form-control"
                               value="<?= esc($periodDate($c, 'to_date')) ?>"
                               title="Practice to date (leave blank if ongoing)">
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
                        'name'        => 'court_name[]',
                        'otherName'   => 'court_other[]',
                        'options'     => $courtOptions,
                        'value'       => '',
                        'other'       => '',
                        'placeholder' => 'Select court…',
                        'showLabel'   => true,
                        'label'       => 'Court',
                        'disabled'    => true,
                    ], ['saveData' => false]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From (date)</label>
                    <input type="date" name="court_from[]" class="form-control" disabled title="Practice from date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To (date)</label>
                    <input type="date" name="court_to[]" class="form-control" disabled title="Practice to date (leave blank if ongoing)">
                </div>
                <div class="col-md-1">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove">&times;</button>
                </div>
            </div>
        </div>
        <p class="form-text">Leave <em>To</em> blank if still practicing there. Choose <strong>Others</strong> to enter a court not listed.</p>

        <div class="section-title mt-4">15. Tribunals, where the applicant has specialized practice: <span class="small text-muted">(Applicable to those before practising Tribunals)</span></div>
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
                               title="Practice from date">
                    </div>
                    <div class="col-md-3">
                        <?php if ($i === 0): ?><label class="form-label">To (date)</label><?php endif; ?>
                        <input type="date" name="tribunal_to[]" class="form-control"
                               value="<?= esc($periodDate($t, 'to_date')) ?>"
                               title="Practice to date (leave blank if ongoing)">
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
                    <input type="date" name="tribunal_from[]" class="form-control" disabled title="Practice from date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To (date)</label>
                    <input type="date" name="tribunal_to[]" class="form-control" disabled title="Practice to date (leave blank if ongoing)">
                </div>
                <div class="col-md-1">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger w-100" data-remove-row aria-label="Remove">&times;</button>
                </div>
            </div>
        </div>
        <p class="form-text">Leave <em>To</em> blank if still practicing there. Choose <strong>Others</strong> to enter a tribunal not listed.</p>

        <div class="section-title mt-4">16. Nature of practice (e.g. Civil, Criminal, Constitutional, Taxation, Labour, Company, Service, etc.)</div>
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
            'required' => false,
            'help'     => 'Select all that apply (stored as related master records).',
        ]);
        ?>

        <div class="section-title mt-4">17. Field of Law — domain expertise (such as Constitutional Law, Inter-State Water Disputes, Criminal Law, Family Law, Human Rights, Public Interest Litigation, International Law, law relating to women) in which the applicant has specialization/expertise</div>
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
            'required' => false,
            'help'     => 'Select all that apply (stored as related master records).',
        ]);
        ?>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
