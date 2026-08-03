<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Applications</h1>
        <p class="page-subtitle">Search and review Senior Advocate designation applications</p>
    </div>
    <a href="<?= base_url('admin/applications/export' . ($exportQuery ?? '')) ?>"
       class="btn btn-outline-success btn-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
    </a>
</div>

<?php
ob_start();
?>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="statusFilter">Status</label>
    <select name="status" id="statusFilter" class="form-select">
        <option value="">All (except drafts)</option>
        <?php foreach ($statuses as $k => $lab): ?>
            <option value="<?= esc($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="ageMin">Age min</label>
    <input type="number" name="age_min" id="ageMin" class="form-control" min="0" max="120"
           value="<?= esc($ageMin !== null ? (string) $ageMin : '') ?>"
           placeholder="Yrs" title="Minimum age (years as on <?= esc(sad_age_as_on_label()) ?>)">
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="ageMax">Age max</label>
    <input type="number" name="age_max" id="ageMax" class="form-control" min="0" max="120"
           value="<?= esc($ageMax !== null ? (string) $ageMax : '') ?>"
           placeholder="Yrs" title="Maximum age (years as on <?= esc(sad_age_as_on_label()) ?>)">
</div>
<div class="col-6 col-md-2 col-lg-2">
    <label class="form-label" for="experienceMin">Experience (min yrs)</label>
    <input type="number" name="experience_min" id="experienceMin" class="form-control" min="0" max="70"
           value="<?= esc($experienceMin !== null ? (string) $experienceMin : '') ?>"
           placeholder="Practice yrs" title="Minimum years of practice at the Bar">
</div>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="natureOfPractice">Nature of practice</label>
    <input type="text" name="nature_of_practice" id="natureOfPractice" class="form-control"
           value="<?= esc($natureOfPractice ?? '') ?>"
           placeholder="e.g. Civil, Criminal" autocomplete="off">
</div>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="fieldOfLaw">Field of law</label>
    <input type="text" name="field_of_law" id="fieldOfLaw" class="form-control"
           value="<?= esc($fieldOfLaw ?? '') ?>"
           placeholder="e.g. Constitutional" autocomplete="off">
</div>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="firstGeneration">First-generation lawyer</label>
    <select name="first_generation" id="firstGeneration" class="form-select"
            title="Filter by first-generation lawyer (Sl. No. 12)">
        <option value="">All</option>
        <option value="1" <?= ($firstGeneration ?? '') === '1' ? 'selected' : '' ?>>Yes</option>
        <option value="0" <?= ($firstGeneration ?? '') === '0' ? 'selected' : '' ?>>No</option>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();

echo view('partials/table_toolbar', [
    'q'                => $q,
    'perPage'          => $perPage,
    'allowedPerPage'   => $allowedPerPage,
    'placeholder'      => 'App no. / name / enrolment / email',
    'action'           => base_url('admin/applications'),
    'extraFilters'     => $extraFilters,
    'hasActiveFilters' => $hasActiveFilters ?? false,
]);
?>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>Application No.</th>
                    <th>Applicant</th>
                    <th>Enrolment</th>
                    <th>Age</th>
                    <th>Experience</th>
                    <th>Nature / Field</th>
                    <th>1st-gen</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="10" class="p-3 text-muted">No records found.</td></tr>
                <?php else: foreach ($applications as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                        <td>
                            <?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?>
                            <div class="small text-muted"><?= esc($a['email'] ?? $a['account_email'] ?? '') ?></div>
                        </td>
                        <td><?= esc($a['enrolment_number'] ?? '—') ?></td>
                        <td class="small">
                            <?php if (isset($a['age_years']) && $a['age_years'] !== null && $a['age_years'] !== ''): ?>
                                <?= (int) $a['age_years'] ?>y
                                <?= (int) ($a['age_months'] ?? 0) ?>m
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?= (int) ($a['practice_years'] ?? 0) ?>y
                            <?= (int) ($a['practice_months'] ?? 0) ?>m
                        </td>
                        <td class="small">
                            <?php
                            $nature = trim((string) ($a['nature_of_practice'] ?? ''));
                            $field  = trim((string) ($a['field_of_law'] ?? ''));
                            if ($nature === '' && $field === '') {
                                echo '—';
                            } else {
                                if ($nature !== '') {
                                    echo '<div class="text-truncate" style="max-width:12rem" title="' . esc($nature) . '">'
                                        . esc($nature) . '</div>';
                                }
                                if ($field !== '') {
                                    echo '<div class="text-muted text-truncate" style="max-width:12rem" title="' . esc($field) . '">'
                                        . esc($field) . '</div>';
                                }
                            }
                            ?>
                        </td>
                        <td class="small"><?= sad_bool_label($a['is_first_generation'] ?? null) ?></td>
                        <td><?= sad_status_badge($a['status']) ?></td>
                        <td class="small text-muted"><?= esc($a['submitted_at'] ?? '—') ?></td>
                        <td class="text-end">
                            <a href="<?= base_url('admin/applications/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <?php if (in_array($a['status'] ?? '', ['submitted', 'under_review', 'pending_approval'], true)): ?>
                                    Decide
                                <?php else: ?>
                                    Open
                                <?php endif; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?= view('partials/table_footer', [
            'pager'   => $pager,
            'total'   => $total,
            'perPage' => $perPage,
            'page'    => $page,
        ]) ?>
    </div>
</div>

<?= $this->endSection() ?>
