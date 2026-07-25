<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 3 of 7 — Judgments Format L-1 &amp; L-2 (Sl. No. 9–10)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/3') ?>

        <div class="section-title">9. Number of Reported Judgments (Format L-1)</div>
        <p class="small text-muted">Excluding orders that do not lay down any principle of law. Counts of judgments actually argued.</p>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Supreme Court</label>
                <input type="number" min="0" name="reported_sc" class="form-control" value="<?= (int) old('reported_sc', $app['reported_sc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">High Court</label>
                <input type="number" min="0" name="reported_hc" class="form-control" value="<?= (int) old('reported_hc', $app['reported_hc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">District / Labour Court / Tribunals</label>
                <input type="number" min="0" name="reported_district" class="form-control" value="<?= (int) old('reported_district', $app['reported_district'] ?? 0) ?>">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Format L-1 entries (as arguing counsel)</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#l1Rows">+ Add row</button>
        </div>
        <div class="table-responsive mb-4">
            <table class="table table-bordered dynamic-table" id="l1Rows" data-rows>
                <thead>
                <tr>
                    <th>Court level</th>
                    <th>Court</th>
                    <th>Case No. / Citation</th>
                    <th>Cause Title &amp; Subject</th>
                    <th>Decided on</th>
                    <th>Legal formulation</th>
                    <th class="col-actions"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $l1 = $l1 ?: [['court_level' => 'madras_hc']];
                foreach ($l1 as $i => $row):
                ?>
                    <tr class="dynamic-row" <?= $i === 0 ? 'data-row-template' : '' ?>>
                        <td>
                            <select name="l1_court_level[]" class="form-select form-select-sm">
                                <?php foreach (['madras_hc' => 'Madras HC', 'supreme_other_hc' => 'SC / Other HC', 'district_tribunal' => 'District / Tribunal'] as $k => $lab): ?>
                                    <option value="<?= $k ?>" <?= ($row['court_level'] ?? '') === $k ? 'selected' : '' ?>><?= $lab ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input name="l1_court_name[]" class="form-control form-control-sm" value="<?= esc($row['court_name'] ?? '') ?>"></td>
                        <td>
                            <input name="l1_case_number[]" class="form-control form-control-sm mb-1" placeholder="Case No." value="<?= esc($row['case_number'] ?? '') ?>">
                            <input name="l1_citation[]" class="form-control form-control-sm" placeholder="Citation" value="<?= esc($row['citation'] ?? '') ?>">
                        </td>
                        <td><textarea name="l1_cause_title[]" class="form-control form-control-sm" rows="2"><?= esc($row['cause_title'] ?? '') ?></textarea></td>
                        <td><input type="date" name="l1_decided_on[]" class="form-control form-control-sm" value="<?= esc($row['decided_on'] ?? '') ?>"></td>
                        <td><textarea name="l1_legal_formulation[]" class="form-control form-control-sm" rows="2"><?= esc($row['legal_formulation'] ?? '') ?></textarea></td>
                        <td class="col-actions"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remove"><i class="bi bi-x-lg"></i></button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section-title">10. Number of Unreported Judgments (Format L-2)</div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Supreme Court</label>
                <input type="number" min="0" name="unreported_sc" class="form-control" value="<?= (int) old('unreported_sc', $app['unreported_sc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">High Court</label>
                <input type="number" min="0" name="unreported_hc" class="form-control" value="<?= (int) old('unreported_hc', $app['unreported_hc'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">District / Labour Court / Tribunals</label>
                <input type="number" min="0" name="unreported_district" class="form-control" value="<?= (int) old('unreported_district', $app['unreported_district'] ?? 0) ?>">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Format L-2 entries</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#l2Rows">+ Add row</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered dynamic-table" id="l2Rows" data-rows>
                <thead>
                <tr>
                    <th>Court level</th>
                    <th>Court</th>
                    <th>Case No. / Citation</th>
                    <th>Cause Title &amp; Subject</th>
                    <th>Decided on</th>
                    <th>Legal formulation</th>
                    <th class="col-actions"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $l2 = $l2 ?: [['court_level' => 'madras_hc']];
                foreach ($l2 as $i => $row):
                ?>
                    <tr class="dynamic-row" <?= $i === 0 ? 'data-row-template' : '' ?>>
                        <td>
                            <select name="l2_court_level[]" class="form-select form-select-sm">
                                <?php foreach (['madras_hc' => 'Madras HC', 'supreme_other_hc' => 'SC / Other HC', 'district_tribunal' => 'District / Tribunal'] as $k => $lab): ?>
                                    <option value="<?= $k ?>" <?= ($row['court_level'] ?? '') === $k ? 'selected' : '' ?>><?= $lab ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input name="l2_court_name[]" class="form-control form-control-sm" value="<?= esc($row['court_name'] ?? '') ?>"></td>
                        <td>
                            <input name="l2_case_number[]" class="form-control form-control-sm mb-1" placeholder="Case No." value="<?= esc($row['case_number'] ?? '') ?>">
                            <input name="l2_citation[]" class="form-control form-control-sm" placeholder="Citation" value="<?= esc($row['citation'] ?? '') ?>">
                        </td>
                        <td><textarea name="l2_cause_title[]" class="form-control form-control-sm" rows="2"><?= esc($row['cause_title'] ?? '') ?></textarea></td>
                        <td><input type="date" name="l2_decided_on[]" class="form-control form-control-sm" value="<?= esc($row['decided_on'] ?? '') ?>"></td>
                        <td><textarea name="l2_legal_formulation[]" class="form-control form-control-sm" rows="2"><?= esc($row['legal_formulation'] ?? '') ?></textarea></td>
                        <td class="col-actions"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row aria-label="Remove"><i class="bi bi-x-lg"></i></button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="form-text">PDF uploads for Format L-1 and L-2 are on Step 7 (each less than 5 MB).</p>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
