<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 3 of 7 — Judgments Format L-1 &amp; L-2 (Sl. No. 9–10)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<?php
$courtLevels = [
    'madras_hc'         => 'Madras High Court',
    'supreme_other_hc'  => 'Supreme Court / Other High Courts',
    'district_tribunal' => 'District Courts / Labour Courts / Tribunals',
];
$notificationDate = $notificationDate ?? ($ageAsOnDate ?? ssa_age_as_on_date($app ?? null));
$enrolmentDate    = $enrolmentDate ?? \App\Libraries\ApplicationDateRules::parseDate($app['enrolment_date'] ?? null);
$decidedOnMin     = $decidedOnMin ?? \App\Libraries\ApplicationDateRules::decidedOnMin($enrolmentDate);
$decidedOnMax     = $decidedOnMax ?? \App\Libraries\ApplicationDateRules::decidedOnMax($notificationDate);
$enrolLabel       = ! empty($enrolmentDate) ? date('d-m-Y', strtotime((string) $enrolmentDate)) : '';
$notifLabel       = ! empty($notificationDate) ? date('d-m-Y', strtotime((string) $notificationDate)) : '';

$applyOldJudgmentRows = static function (array $existing, string $prefix): array {
    $keys = ['court_level', 'court_name', 'decided_on', 'case_number', 'cause_title', 'legal_formulation'];
    $hasOld = false;
    $posted = [];
    foreach ($keys as $key) {
        $posted[$key] = old($prefix . '_' . $key);
        if (is_array($posted[$key])) {
            $hasOld = true;
        }
    }
    if (! $hasOld) {
        return $existing;
    }
    $count = 0;
    foreach ($posted as $vals) {
        if (is_array($vals)) {
            $count = max($count, count($vals));
        }
    }
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'court_level'        => $posted['court_level'][$i] ?? 'madras_hc',
            'court_name'         => $posted['court_name'][$i] ?? '',
            'decided_on'         => $posted['decided_on'][$i] ?? '',
            'case_number'        => $posted['case_number'][$i] ?? '',
            'cause_title'        => $posted['cause_title'][$i] ?? '',
            'legal_formulation'  => $posted['legal_formulation'][$i] ?? '',
        ];
    }

    return $rows !== [] ? $rows : $existing;
};
$l1 = $applyOldJudgmentRows($l1 ?? [], 'l1');
$l2 = $applyOldJudgmentRows($l2 ?? [], 'l2');

/**
 * Render one L-1 / L-2 judgment entry card.
 *
 * @param string               $prefix  Field name prefix: l1 | l2
 * @param array<string,mixed>  $row
 * @param array<string,string> $courtLevels
 * @param bool                 $isTemplate
 * @param string               $label
 */
$renderJudgmentCard = static function (
    string $prefix,
    array $row,
    array $courtLevels,
    bool $isTemplate,
    string $label
) use ($decidedOnMin, $decidedOnMax): void {
    $disabled = $isTemplate ? ' disabled' : '';
    $classes  = 'entry-card dynamic-row' . ($isTemplate ? ' d-none' : '');
    $attrs    = $isTemplate ? ' data-row-template hidden aria-hidden="true"' : '';
    $level    = (string) ($row['court_level'] ?? 'madras_hc');
    $courtName = (string) ($row['court_name'] ?? '');
    if ($level === 'madras_hc') {
        $courtName = $courtLevels['madras_hc'] ?? 'Madras High Court';
    }
    $courtNameLocked = $level === 'madras_hc';
    $decided  = $row['decided_on'] ?? '';
    if (is_string($decided) && $decided !== '') {
        $decided = substr($decided, 0, 10);
    }
    ?>
    <div class="<?= esc($classes, 'attr') ?>"<?= $attrs ?>>
        <div class="entry-card-top">
            <span class="entry-card-label"><?= esc($label) ?></span>
            <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove entry">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="row g-2 g-md-3">
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label">Court</label>
                <select name="<?= esc($prefix) ?>_court_level[]" class="form-select form-select-sm"
                        data-judgment-court-level<?= $disabled ?>>
                    <?php foreach ($courtLevels as $k => $lab): ?>
                        <option value="<?= esc($k) ?>" <?= $level === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label">Court Name</label>
                <input name="<?= esc($prefix) ?>_court_name[]" class="form-control form-control-sm<?= $courtNameLocked ? ' bg-light' : '' ?>"
                       data-judgment-court-name
                       value="<?= esc($courtName) ?>"
                       <?php if ($courtNameLocked): ?>readonly tabindex="-1"<?php endif; ?>
                       <?= $courtNameLocked ? 'title="Copied from Court: Madras High Court"' : 'placeholder="Enter court name"' ?>
                       <?= $disabled ?>>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label">Decided on</label>
                <input type="date" name="<?= esc($prefix) ?>_decided_on[]" class="form-control form-control-sm"
                       value="<?= esc($decided) ?>"
                       <?php if ($decidedOnMin): ?>min="<?= esc($decidedOnMin) ?>"<?php endif; ?>
                       <?php if ($decidedOnMax): ?>max="<?= esc($decidedOnMax) ?>"<?php endif; ?>
                       title="Must be between the date of enrolment and the notification date"<?= $disabled ?>>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Case Number / Citation <?php if ($prefix === 'l2') echo '(If any)' ?></label>
                <input name="<?= esc($prefix) ?>_case_number[]" class="form-control form-control-sm"
                       value="<?= esc($row['case_number'] ?? '') ?>"
                       <?= $disabled ?>>
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">Cause Title and Subject Matter</label>
                <textarea name="<?= esc($prefix) ?>_cause_title[]" class="form-control form-control-sm" rows="2"
                          <?= $disabled ?>><?= esc($row['cause_title'] ?? '') ?></textarea>
            </div>
            <div class="col-12 col-sm-6">
                <label class="form-label">Legal formulation advanced by the applicant</label>
                <textarea name="<?= esc($prefix) ?>_legal_formulation[]" class="form-control form-control-sm" rows="2"
                          <?= $disabled ?>><?= esc($row['legal_formulation'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
    <?php
};
?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/3', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
            'novalidate'           => 'novalidate',
        ]) ?>

        <div class="section-title">9. Number of Reported Judgments (excluding orders that do not lay down any principle of law): <u>Format L-1</u></div>
        <div class="row g-3 mb-3">
            <span class="form-help-text mb-0">No. of Reported Judgments that the Advocate actually argued</span>
            <div class="col-md-4">
                <label class="form-label">Supreme Court</label>
                <input type="number" min="0" name="reported_sc" class="form-control" value="<?= (int) old('reported_sc', $app['reported_sc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">High Court</label>
                <input type="number" min="0" name="reported_hc" class="form-control" value="<?= (int) old('reported_hc', $app['reported_hc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">District Court / Labour Court and Tribunals</label>
                <input type="number" min="0" name="reported_district" class="form-control" value="<?= (int) old('reported_district', $app['reported_district'] ?? 0) ?>">
            </div>
        </div>

        <div class="entry-block mb-4">
            <div class="annexure-heading-right">
                <p>Format L-1</p>
                <p>(See Sl. No.9 of the application)</p>
            </div>
            <div class="annexure-heading-center">
                <p>AS ARGUING COUNSEL</p>
                <p>LIST OF REPORTED JUDGMENTS (EXCLUDING ORDERS NOT LAYING DOWN ANY PRINCIPLE OF LAW)</p>
            </div>
            <div class="entry-block-head justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#l1Rows">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add entry
                </button>
            </div>
            <div id="l1Rows" class="entry-list" data-rows>
                <?php
                // Hidden clone template — never submitted (disabled fields)
                $renderJudgmentCard('l1', ['court_level' => 'madras_hc'], $courtLevels, true, 'L-1 entry');
                $l1 = $l1 ?: [['court_level' => 'madras_hc']];
                $l1Index = 0;
                foreach ($l1 as $row):
                    $l1Index++;
                    $renderJudgmentCard('l1', $row, $courtLevels, false, 'Format L-1 entry #' . $l1Index);
                endforeach;
                ?>
            </div>
        </div>

        <div class="section-title">10. Number of Unreported Judgments (excluding orders that do not lay down any principle of law): <u>Format L-2</u></div>
        <div class="row g-3 mb-3">
            <span class="form-help-text mb-0">No. of Unreported Judgments that the Advocate actually argued</span>
            <div class="col-md-4">
                <label class="form-label">Supreme Court</label>
                <input type="number" min="0" name="unreported_sc" class="form-control" value="<?= (int) old('unreported_sc', $app['unreported_sc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">High Court</label>
                <input type="number" min="0" name="unreported_hc" class="form-control" value="<?= (int) old('unreported_hc', $app['unreported_hc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">District / Labour Court and Tribunals</label>
                <input type="number" min="0" name="unreported_district" class="form-control" value="<?= (int) old('unreported_district', $app['unreported_district'] ?? 0) ?>">
            </div>
        </div>

        <div class="entry-block mb-3">
            <div class="annexure-heading-right">
                <p>Format L-2</p>
                <p>(See Sl. No.10 of the application)</p>
            </div>
            <div class="annexure-heading-center">
                <p>AS ARGUING COUNSEL</p>
                <p>LIST OF UNREPORTED JUDGMENTS (EXCLUDING ORDERS NOT LAYING DOWN ANY PRINCIPLE OF LAW)</p>
            </div>
            <div class="entry-block-head justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#l2Rows">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add entry
                </button>
            </div>
            <div id="l2Rows" class="entry-list" data-rows>
                <?php
                $renderJudgmentCard('l2', ['court_level' => 'madras_hc'], $courtLevels, true, 'L-2 entry');
                $l2 = $l2 ?: [['court_level' => 'madras_hc']];
                $l2Index = 0;
                foreach ($l2 as $row):
                    $l2Index++;
                    $renderJudgmentCard('l2', $row, $courtLevels, false, 'Format L-2 entry #' . $l2Index);
                endforeach;
                ?>
            </div>
        </div>

        <p class="form-text mb-0">PDF uploads for Format L-1 and L-2 are on Step 7 (each less than 5 MB). Use <strong>Add entry</strong> for additional judgments. Removing entries always keeps one blank card so you can add again. <strong>Decided on</strong> must be between the date of enrolment<?php if ($enrolLabel !== ''): ?> (<?= esc($enrolLabel) ?>)<?php endif; ?> and the notification date<?php if ($notifLabel !== ''): ?> (<?= esc($notifLabel) ?>)<?php endif; ?>.</p>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
