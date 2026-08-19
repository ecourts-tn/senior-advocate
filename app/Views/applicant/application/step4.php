<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 4 of 7 — Pro Bono, Amicus Curiae &amp; Academic (Sl. No. 11–13)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<?php
$notificationDate = $notificationDate ?? ($ageAsOnDate ?? ssa_age_as_on_date($app ?? null));
$enrolmentDate    = $enrolmentDate ?? \App\Libraries\ApplicationDateRules::parseDate($app['enrolment_date'] ?? null);
$decidedOnMin     = $decidedOnMin ?? \App\Libraries\ApplicationDateRules::decidedOnMin($enrolmentDate);
$decidedOnMax     = $decidedOnMax ?? \App\Libraries\ApplicationDateRules::decidedOnMax($notificationDate);
$enrolLabel       = ! empty($enrolmentDate) ? date('d-m-Y', strtotime((string) $enrolmentDate)) : '';
$notifLabel       = ! empty($notificationDate) ? date('d-m-Y', strtotime((string) $notificationDate)) : '';

$applyOldL3 = static function (array $existing, array $map): array {
    $hasOld = false;
    $posted = [];
    foreach ($map as $postKey => $rowKey) {
        $posted[$rowKey] = old($postKey);
        if (is_array($posted[$rowKey])) {
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
        $row = [];
        foreach ($map as $rowKey) {
            $row[$rowKey] = $posted[$rowKey][$i] ?? '';
        }
        $rows[] = $row;
    }

    return $rows !== [] ? $rows : $existing;
};
$l3pb = $applyOldL3($l3pb ?? [], [
    'pb_court'           => 'court_tribunal',
    'pb_case_number'     => 'case_number',
    'pb_decided_on'      => 'decided_on',
    'pb_cause_title'     => 'cause_title',
    'pb_society_benefit' => 'society_benefit',
]);
$l3am = $applyOldL3($l3am ?? [], [
    'am_court'        => 'court_tribunal',
    'am_case_number'  => 'case_number',
    'am_cause_title'  => 'cause_title',
    'am_decided_on'   => 'decided_on',
    'am_reportable'   => 'reportable',
]);
$fgPosted = old('is_first_generation');
$fgValue  = $fgPosted !== null && $fgPosted !== false
    ? (string) $fgPosted
    : (ssa_bool_label($app['is_first_generation'] ?? null) === 'Yes' ? '1'
        : (ssa_bool_label($app['is_first_generation'] ?? null) === 'No' ? '0' : ''));
?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/4', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
            'novalidate'           => 'novalidate',
        ]) ?>

        <div class="section-title">11. Pro Bono / Amicus Curiae work <u>Format L-3(i) Format L-3(ii)</u></div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Total Pro Bono cases</label>
                <input type="number" min="0" name="pro_bono_total" class="form-control" value="<?= (int) old('pro_bono_total', $app['pro_bono_total'] ?? 0) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Total Amicus Curiae cases</label>
                <input type="number" min="0" name="amicus_total" class="form-control" value="<?= (int) old('amicus_total', $app['amicus_total'] ?? 0) ?>">
            </div>
        </div>

        <!-- Format L-3(i) Pro Bono -->
        <div class="entry-block mb-4">
            <div class="annexure-heading-right">
                <p>Format L-3 (i)</p>
                <p>(See Sl. No.11 of the application)</p>
            </div>
            <div class="annexure-heading-center">
                <p>LIST OF MATTERS IN WHICH APPEARED AS PRO-BONO</p>
            </div>
            <div class="entry-block-head justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#pbRows">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </div>
            <div id="pbRows" class="entry-list" data-rows>
                <?php
                $l3pb = $l3pb ?: [[]];
                // Hidden blank template for cloning
                ?>
                <div class="entry-card dynamic-row d-none" data-row-template>
                    <div class="entry-card-top">
                        <span class="entry-card-label">Pro Bono entry</span>
                        <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Court(s) / Tribunal(s)</label>
                            <input name="pb_court[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Citation / Case Number</label>
                            <input name="pb_case_number[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Decided on</label>
                            <input type="date" name="pb_decided_on[]" class="form-control form-control-sm" disabled
                                   <?php if ($decidedOnMin): ?>min="<?= esc($decidedOnMin) ?>"<?php endif; ?>
                                   <?php if ($decidedOnMax): ?>max="<?= esc($decidedOnMax) ?>"<?php endif; ?>
                                   title="Must be between the date of enrolment and the notification date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cause Title</label>
                            <textarea name="pb_cause_title[]" class="form-control form-control-sm" rows="1" disabled></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Describe Manner in which society was sought to be benefited by the litigation</label>
                            <textarea name="pb_society_benefit[]" class="form-control form-control-sm" rows="2" disabled></textarea>
                        </div>
                    </div>
                </div>
                <?php foreach ($l3pb as $row): ?>
                    <div class="entry-card dynamic-row">
                        <div class="entry-card-top">
                            <span class="entry-card-label">Pro Bono entry</span>
                            <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Court(s) / Tribunal(s)</label>
                                <input name="pb_court[]" class="form-control form-control-sm" value="<?= esc($row['court_tribunal'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Citation / Case Number</label>
                                <input name="pb_case_number[]" class="form-control form-control-sm" value="<?= esc($row['case_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Decided on</label>
                                <input type="date" name="pb_decided_on[]" class="form-control form-control-sm"
                                       value="<?= esc(isset($row['decided_on']) && $row['decided_on'] !== '' ? substr((string) $row['decided_on'], 0, 10) : '') ?>"
                                       <?php if ($decidedOnMin): ?>min="<?= esc($decidedOnMin) ?>"<?php endif; ?>
                                       <?php if ($decidedOnMax): ?>max="<?= esc($decidedOnMax) ?>"<?php endif; ?>
                                       title="Must be between the date of enrolment and the notification date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cause Title</label>
                                <textarea name="pb_cause_title[]" class="form-control form-control-sm" rows="2"><?= esc($row['cause_title'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Describe Manner in which society was sought to be benefited by the litigation</label>
                                <textarea name="pb_society_benefit[]" class="form-control form-control-sm" rows="2"><?= esc($row['society_benefit'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Format L-3(ii) Amicus Curiae -->
        <div class="entry-block mb-4">
            <div class="annexure-heading-right">
                <p>Format L-3 (ii)</p>
                <p>(See Sl. No.11 of the application)</p>
            </div>
            <div class="annexure-heading-center">
                <p>LIST OF MATTERS IN WHICH APPEARED AS AMICUS CURIAE</p>
            </div>
            <div class="entry-block-head justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#amRows">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </div>
            <div id="amRows" class="entry-list" data-rows>
                <div class="entry-card dynamic-row d-none" data-row-template>
                    <div class="entry-card-top">
                        <span class="entry-card-label">Amicus Curiae entry</span>
                        <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Court(s) / Tribunal(s)</label>
                            <input name="am_court[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Citation / Case Number</label>
                            <input name="am_case_number[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cause Title</label>
                            <textarea name="am_cause_title[]" class="form-control form-control-sm" rows="1" disabled></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Decided on</label>
                            <input type="date" name="am_decided_on[]" class="form-control form-control-sm" disabled
                                   <?php if ($decidedOnMin): ?>min="<?= esc($decidedOnMin) ?>"<?php endif; ?>
                                   <?php if ($decidedOnMax): ?>max="<?= esc($decidedOnMax) ?>"<?php endif; ?>
                                   title="Must be between the date of enrolment and the notification date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reportable / Unreportable?</label>
                            <select name="am_reportable[]" class="form-select form-select-sm" disabled>
                                <option value="">—</option>
                                <option value="Reportable">Reportable</option>
                                <option value="Unreportable">Unreportable</option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php $l3am = $l3am ?: [[]]; foreach ($l3am as $row): ?>
                    <div class="entry-card dynamic-row">
                        <div class="entry-card-top">
                            <span class="entry-card-label">Amicus Curiae entry</span>
                            <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Court(s) / Tribunal(s)</label>
                                <input name="am_court[]" class="form-control form-control-sm" value="<?= esc($row['court_tribunal'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Citation / Case Number</label>
                                <input name="am_case_number[]" class="form-control form-control-sm" value="<?= esc($row['case_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cause Title</label>
                                <textarea name="am_cause_title[]" class="form-control form-control-sm" rows="1"><?= esc($row['cause_title'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Decided on</label>
                                <input type="date" name="am_decided_on[]" class="form-control form-control-sm"
                                       value="<?= esc(isset($row['decided_on']) && $row['decided_on'] !== '' ? substr((string) $row['decided_on'], 0, 10) : '') ?>"
                                       <?php if ($decidedOnMin): ?>min="<?= esc($decidedOnMin) ?>"<?php endif; ?>
                                       <?php if ($decidedOnMax): ?>max="<?= esc($decidedOnMax) ?>"<?php endif; ?>
                                       title="Must be between the date of enrolment and the notification date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Reportable / Unreportable?</label>
                                <select name="am_reportable[]" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    <option value="Reportable" <?= ($row['reportable'] ?? '') === 'Reportable' ? 'selected' : '' ?>>Reportable</option>
                                    <option value="Unreportable" <?= ($row['reportable'] ?? '') === 'Unreportable' ? 'selected' : '' ?>>Unreportable</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="form-text">Decided on dates in Format L-3 entries must be between the date of enrolment<?php if ($enrolLabel !== ''): ?> (<?= esc($enrolLabel) ?>)<?php endif; ?> and the notification date<?php if ($notifLabel !== ''): ?> (<?= esc($notifLabel) ?>)<?php endif; ?>.</p>

        <div class="section-title">12. Whether the applicant is first-generation lawyer <span class="text-danger">*</span></div>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label required" for="is_first_generation">Select Yes or No</label>
                <select name="is_first_generation" id="is_first_generation" class="form-select" required>
                    <option value="">— Select —</option>
                    <option value="1" <?= $fgValue === '1' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $fgValue === '0' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
        </div>

        <div class="section-title">13. Academic Articles/Books published, experience of Teaching Assignments in the field of law, Guest Lectures delivered in law schools or professional institutions connected with law: Format L-4</div>
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <label class="form-label">No. of Academic Articles</label>
                <input type="number" min="0" name="academic_articles_count" class="form-control" value="<?= (int) ($app['academic_articles_count'] ?? 0) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">No. of Academic Books</label>
                <input type="number" min="0" name="academic_books_count" class="form-control" value="<?= (int) ($app['academic_books_count'] ?? 0) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">No. of Teaching Assignments</label>
                <input type="number" min="0" name="teaching_assignments_count" class="form-control" value="<?= (int) ($app['teaching_assignments_count'] ?? 0) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">No. of Guest Lectures</label>
                <input type="number" min="0" name="guest_lectures_count" class="form-control" value="<?= (int) ($app['guest_lectures_count'] ?? 0) ?>">
            </div>
        </div>

        <div class="entry-block">
            <div class="annexure-heading-right">
                <p>Format L-4</p>
                <p>(See Sl. No.13 of the application)</p>
            </div>
            <div class="annexure-heading-center">
                <p>Details of academic articles/books published, experience of teaching assignments in the field of law,<br>guest lectures delivered in law schools or professional institutions connected with law</p>
            </div>
            <div class="entry-block-head justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#l4Rows">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </div>
            <div id="l4Rows" class="entry-list" data-rows>
                <?php
                /**
                 * Render one Format L-4 entry card matching prescribed columns:
                 * Topic of published academic Articles | Books |
                 * Experience details (Teaching Assignment(s) | Guest Lectures) |
                 * Any other relevant details
                 */
                $renderL4Card = static function (array $row, bool $isTemplate): void {
                    $disabled = $isTemplate ? ' disabled' : '';
                    $classes  = 'entry-card dynamic-row' . ($isTemplate ? ' d-none' : '');
                    $attrs    = $isTemplate ? ' data-row-template hidden aria-hidden="true"' : '';
                    // Fallback: older rows stored only the combined "topic" column.
                    $articles = (string) ($row['articles'] ?? '');
                    $books    = (string) ($row['books'] ?? '');
                    if ($articles === '' && $books === '' && ! empty($row['topic'])) {
                        $articles = (string) $row['topic'];
                    }
                    ?>
                    <div class="<?= esc($classes, 'attr') ?>"<?= $attrs ?>>
                        <div class="entry-card-top">
                            <span class="entry-card-label">Format L-4 entry</span>
                            <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="border rounded p-2 bg-light-subtle">
                                    <div class="small fw-semibold text-muted mb-2">
                                        Topic of published academic Articles / Books
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Articles</label>
                                            <textarea name="l4_articles[]" class="form-control form-control-sm" rows="2"
                                                      placeholder="Title / topic of published academic article(s)"<?= $disabled ?>><?= esc($articles) ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Books</label>
                                            <textarea name="l4_books[]" class="form-control form-control-sm" rows="2"
                                                      placeholder="Title / topic of published academic book(s)"<?= $disabled ?>><?= esc($books) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-2 bg-light-subtle">
                                    <div class="small fw-semibold text-muted mb-2">
                                        Experience details in law schools or professional institutions (with names) connected with law
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Teaching Assignment(s)</label>
                                            <textarea name="l4_teaching[]" class="form-control form-control-sm" rows="2"
                                                      placeholder="Institution name, subject, period, etc."<?= $disabled ?>><?= esc($row['teaching_assignment'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Guest Lectures</label>
                                            <textarea name="l4_guest[]" class="form-control form-control-sm" rows="2"
                                                      placeholder="Institution name, topic, date, etc."<?= $disabled ?>><?= esc($row['guest_lectures'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Any other relevant details</label>
                                <textarea name="l4_other[]" class="form-control form-control-sm" rows="2"<?= $disabled ?>><?= esc($row['other_details'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php
                };

                $renderL4Card([], true);
                $l4 = $l4 ?: [[]];
                foreach ($l4 as $row) {
                    $renderL4Card($row, false);
                }
                ?>
            </div>
        </div>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
