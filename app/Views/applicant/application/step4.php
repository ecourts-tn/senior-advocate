<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 4 of 7 — Pro Bono, Amicus Curiae &amp; Academic (Sl. No. 11–13)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/4', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
        ]) ?>

        <div class="section-title">11. Pro Bono / Amicus Curiae work Format L-3(i), FonnatL-3(ii)</div>
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
            <div class="entry-block-head">
                <strong>Format L-3(i) — Pro Bono</strong>
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
                        <div class="col-md-6">
                            <label class="form-label">Court(s) / Tribunal(s)</label>
                            <input name="pb_court[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Citation / Case Number</label>
                            <input name="pb_case_number[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Cause Title</label>
                            <textarea name="pb_cause_title[]" class="form-control form-control-sm" rows="1" disabled></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Decided on</label>
                            <input type="date" name="pb_decided_on[]" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-12">
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
                            <div class="col-md-6">
                                <label class="form-label">Court(s) / Tribunal(s)</label>
                                <input name="pb_court[]" class="form-control form-control-sm" value="<?= esc($row['court_tribunal'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Citation / Case Number</label>
                                <input name="pb_case_number[]" class="form-control form-control-sm" value="<?= esc($row['case_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Cause Title</label>
                                <textarea name="pb_cause_title[]" class="form-control form-control-sm" rows="1"><?= esc($row['cause_title'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Decided on</label>
                                <input type="date" name="pb_decided_on[]" class="form-control form-control-sm" value="<?= esc($row['decided_on'] ?? '') ?>">
                            </div>
                            <div class="col-12">
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
            <div class="entry-block-head">
                <strong>Format L-3(ii) — Amicus Curiae</strong>
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
                            <input type="date" name="am_decided_on[]" class="form-control form-control-sm" disabled>
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
                                <input type="date" name="am_decided_on[]" class="form-control form-control-sm" value="<?= esc($row['decided_on'] ?? '') ?>">
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

        <div class="section-title">12. Whether the applicant is First-generation lawyer</div>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <select name="is_first_generation" class="form-select">
                    <option value="">— Select —</option>
                    <option value="1" <?= sad_bool_label($app['is_first_generation'] ?? null) === 'Yes' ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= sad_bool_label($app['is_first_generation'] ?? null) === 'No' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
        </div>

        <div class="section-title">13. Aqademic Articles/Books published, experience of Teaching Assignments in the field of law, Guest Lectures delivered in law schools or professional institutions connected with law: Format L-4</div>
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
            <div class="entry-block-head">
                <strong>Format L-4 details</strong>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#l4Rows">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </div>
            <div id="l4Rows" class="entry-list" data-rows>
                <div class="entry-card dynamic-row d-none" data-row-template>
                    <div class="entry-card-top">
                        <span class="entry-card-label">Academic entry</span>
                        <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Topic of published academic articles</label>
                            <textarea name="l4_topic[]" class="form-control form-control-sm" rows="2" disabled></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teaching Assignment(s)</label>
                            <textarea name="l4_teaching[]" class="form-control form-control-sm" rows="2" disabled></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guest Lectures</label>
                            <textarea name="l4_guest[]" class="form-control form-control-sm" rows="2" disabled></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Other relevant details</label>
                            <textarea name="l4_other[]" class="form-control form-control-sm" rows="2" disabled></textarea>
                        </div>
                    </div>
                </div>
                <?php $l4 = $l4 ?: [[]]; foreach ($l4 as $row): ?>
                    <div class="entry-card dynamic-row">
                        <div class="entry-card-top">
                            <span class="entry-card-label">Academic entry</span>
                            <button type="button" class="btn btn-sm btn-outline-danger entry-remove" data-remove-row aria-label="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Topic of published academic articles</label>
                                <textarea name="l4_topic[]" class="form-control form-control-sm" rows="2"><?= esc($row['topic'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teaching Assignment(s)</label>
                                <textarea name="l4_teaching[]" class="form-control form-control-sm" rows="2"><?= esc($row['teaching_assignment'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Guest Lectures</label>
                                <textarea name="l4_guest[]" class="form-control form-control-sm" rows="2"><?= esc($row['guest_lectures'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Other relevant details</label>
                                <textarea name="l4_other[]" class="form-control form-control-sm" rows="2"><?= esc($row['other_details'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
