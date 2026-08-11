<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Applications</h1>
        <p class="page-subtitle">Search, filter, and review Senior Advocate designation applications</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
            <a href="<?= base_url('admin/applications/status') ?>" class="btn btn-mhc btn-sm">
                <i class="bi bi-ui-checks me-1"></i> Update status
            </a>
        <?php endif; ?>
        <a href="<?= base_url('admin/applications/export' . ($exportQuery ?? '')) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
    </div>
</div>

<?php
$filterStatuses = $filterStatuses ?? \App\Models\ApplicationModel::ADMIN_PIPELINE_STATUSES;
$notificationOptions = $notificationOptions ?? [];
$notificationId = $notificationId ?? null;
ob_start();
?>
<div class="col-md-4 col-lg-3">
    <label class="form-label" for="notificationFilter">Notification</label>
    <select name="notification_id" id="notificationFilter" class="form-select"
            title="Filter applications by designation notification">
        <option value="">All notifications</option>
        <?php foreach ($notificationOptions as $nid => $nLabel): ?>
            <option value="<?= (int) $nid ?>" <?= (int) ($notificationId ?? 0) === (int) $nid ? 'selected' : '' ?>>
                <?= esc($nLabel) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="statusFilter">Status</label>
    <select name="status" id="statusFilter" class="form-select">
        <option value="">All statuses</option>
        <?php foreach ($filterStatuses as $k => $lab): ?>
            <option value="<?= esc($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="ageMin">Age min</label>
    <input type="number" name="age_min" id="ageMin" class="form-control" min="0" max="120"
           value="<?= esc($ageMin !== null ? (string) $ageMin : '') ?>"
           placeholder="Yrs" title="Minimum age (years as on <?= esc(ssa_age_as_on_label()) ?>)">
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="ageMax">Age max</label>
    <input type="number" name="age_max" id="ageMax" class="form-control" min="0" max="120"
           value="<?= esc($ageMax !== null ? (string) $ageMax : '') ?>"
           placeholder="Yrs" title="Maximum age (years as on <?= esc(ssa_age_as_on_label()) ?>)">
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="practiceYearsMin">Practice min</label>
    <input type="number" name="practice_years_min" id="practiceYearsMin" class="form-control" min="0" max="70"
           value="<?= esc(isset($practiceYearsMin) && $practiceYearsMin !== null ? (string) $practiceYearsMin : '') ?>"
           placeholder="Yrs" title="Minimum years of practice (practice_years)">
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="practiceYearsMax">Practice max</label>
    <input type="number" name="practice_years_max" id="practiceYearsMax" class="form-control" min="0" max="70"
           value="<?= esc(isset($practiceYearsMax) && $practiceYearsMax !== null ? (string) $practiceYearsMax : '') ?>"
           placeholder="Yrs" title="Maximum years of practice (practice_years)">
</div>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="natureOfPractice">Nature of practice</label>
    <select name="nature_of_practice" id="natureOfPractice" class="form-select"
            title="Filter by nature of practice (from masters)">
        <option value="">All</option>
        <?php foreach ($natureOptions ?? [] as $opt): ?>
            <option value="<?= esc($opt) ?>" <?= ($natureOfPractice ?? '') === $opt ? 'selected' : '' ?>>
                <?= esc($opt) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="fieldOfLaw">Field of law</label>
    <select name="field_of_law" id="fieldOfLaw" class="form-select"
            title="Filter by field of law (from masters)">
        <option value="">All</option>
        <?php foreach ($fieldOptions ?? [] as $opt): ?>
            <option value="<?= esc($opt) ?>" <?= ($fieldOfLaw ?? '') === $opt ? 'selected' : '' ?>>
                <?= esc($opt) ?>
            </option>
        <?php endforeach; ?>
    </select>
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
            <table class="table table-hover mb-0" id="applicationsTable">
                <thead>
                <tr>
                    <th>Application No.</th>
                    <th>Notification</th>
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
                    <tr><td colspan="11" class="p-3 text-muted">No records found.</td></tr>
                <?php else: foreach ($applications as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                        <td class="small">
                            <?php if (! empty($a['notification_number'])): ?>
                                <a href="<?= base_url('admin/applications?notification_id=' . (int) ($a['notification_id'] ?? 0)) ?>"
                                   class="text-decoration-none">
                                    <?= esc($a['notification_number']) ?>
                                </a>
                                <?php if (! empty($a['notification_date'])): ?>
                                    <div class="text-muted"><?= esc(date('d-m-Y', strtotime($a['notification_date']))) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?>
                            <div class="small text-muted"><?= esc($a['email'] ?? $a['account_email'] ?? '') ?></div>
                        </td>
                        <td><?= esc($a['enrolment_number'] ?? '—') ?></td>
                        <td class="small">
                            <?php if (isset($a['age_years']) && $a['age_years'] !== null && $a['age_years'] !== ''): ?>
                                <?= (int) $a['age_years'] ?>y
                                <?= (int) ($a['age_months'] ?? 0) ?>m
                                <?= (int) ($a['age_days'] ?? 0) ?>d
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
                        <td class="small"><?= ssa_bool_label($a['is_first_generation'] ?? null) ?></td>
                        <td><?= ssa_status_badge($a['status']) ?></td>
                        <td class="small text-muted"><?= esc($a['submitted_at'] ?? '—') ?></td>
                        <td class="text-end">
                            <a href="<?= base_url('admin/applications/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary">
                                Open
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
