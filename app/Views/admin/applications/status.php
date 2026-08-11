<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Update application status</h1>
        <p class="page-subtitle">
            Select applications and set <strong>Select Listed</strong>, <strong>Wait Listed</strong>, or <strong>Rejected</strong>.
            No email/SMS is sent on status change.
        </p>
    </div>
    <a href="<?= base_url('admin/applications') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to applications
    </a>
</div>

<?php
$filterStatuses      = $filterStatuses ?? \App\Models\ApplicationModel::ADMIN_PIPELINE_STATUSES;
$assignableStatuses  = $assignableStatuses ?? \App\Models\ApplicationModel::ADMIN_ASSIGNABLE_STATUSES;
$notificationOptions = $notificationOptions ?? [];
$notificationId      = $notificationId ?? null;
ob_start();
?>
<div class="col-md-4 col-lg-3">
    <label class="form-label" for="notificationFilter">Notification</label>
    <select name="notification_id" id="notificationFilter" class="form-select">
        <option value="">All notifications</option>
        <?php foreach ($notificationOptions as $nid => $nLabel): ?>
            <option value="<?= (int) $nid ?>" <?= (int) ($notificationId ?? 0) === (int) $nid ? 'selected' : '' ?>>
                <?= esc($nLabel) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-4 col-lg-3">
    <label class="form-label" for="statusFilter">Filter by status</label>
    <select name="status" id="statusFilter" class="form-select">
        <option value="">All statuses</option>
        <?php foreach ($filterStatuses as $k => $lab): ?>
            <option value="<?= esc($k) ?>" <?= ($status ?? '') === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-6 col-md-2 col-lg-2">
    <label class="form-label" for="practiceYearsMin">Practice min</label>
    <input type="number" name="practice_years_min" id="practiceYearsMin" class="form-control" min="0" max="70"
           value="<?= esc(isset($practiceYearsMin) && $practiceYearsMin !== null ? (string) $practiceYearsMin : '') ?>"
           placeholder="Yrs">
</div>
<div class="col-6 col-md-2 col-lg-2">
    <label class="form-label" for="practiceYearsMax">Practice max</label>
    <input type="number" name="practice_years_max" id="practiceYearsMax" class="form-control" min="0" max="70"
           value="<?= esc(isset($practiceYearsMax) && $practiceYearsMax !== null ? (string) $practiceYearsMax : '') ?>"
           placeholder="Yrs">
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="ageMin">Age min</label>
    <input type="number" name="age_min" id="ageMin" class="form-control" min="0" max="120"
           value="<?= esc(isset($ageMin) && $ageMin !== null ? (string) $ageMin : '') ?>"
           placeholder="Yrs">
</div>
<div class="col-6 col-md-2 col-lg-1">
    <label class="form-label" for="ageMax">Age max</label>
    <input type="number" name="age_max" id="ageMax" class="form-control" min="0" max="120"
           value="<?= esc(isset($ageMax) && $ageMax !== null ? (string) $ageMax : '') ?>"
           placeholder="Yrs">
</div>
<?php
$extraFilters = ob_get_clean();

echo view('partials/table_toolbar', [
    'q'                => $q,
    'perPage'          => $perPage,
    'allowedPerPage'   => $allowedPerPage,
    'placeholder'      => 'App no. / name / enrolment / email',
    'action'           => base_url('admin/applications/status'),
    'extraFilters'     => $extraFilters,
    'hasActiveFilters' => $hasActiveFilters ?? false,
]);
?>

<?= form_open('admin/applications/bulk-status', ['id' => 'bulkStatusForm']) ?>
<div class="card card-mhc mb-3">
    <div class="card-header">Set status for selected applications</div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="bulkStatus">New status <span class="text-danger">*</span></label>
                <select name="status" id="bulkStatus" class="form-select" required>
                    <option value="">— Choose status —</option>
                    <?php foreach ($assignableStatuses as $k => $lab): ?>
                        <option value="<?= esc($k) ?>"><?= esc($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 col-lg-5">
                <label class="form-label" for="bulkRemarks">Remarks (optional)</label>
                <input type="text" name="remarks" id="bulkRemarks" class="form-control"
                       maxlength="500" placeholder="Optional note recorded in status history">
            </div>
            <div class="col-md-3 col-lg-4 d-flex flex-wrap gap-2 align-items-center">
                <button type="submit" class="btn btn-mhc" id="bulkStatusSubmit" disabled>
                    <i class="bi bi-check2-square me-1"></i> Update selected
                </button>
                <span class="small text-muted" id="bulkSelectedCount">0 selected</span>
            </div>
        </div>
    </div>
</div>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="statusApplicationsTable">
                <thead>
                <tr>
                    <th class="text-center" style="width:2.5rem">
                        <input type="checkbox" class="form-check-input" id="selectAllApps"
                               title="Select all on this page" aria-label="Select all on this page">
                    </th>
                    <th>Application No.</th>
                    <th>Applicant</th>
                    <th>Enrolment</th>
                    <th>Age</th>
                    <th>Practice</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="9" class="p-3 text-muted">No records found. Adjust filters or search.</td></tr>
                <?php else: foreach ($applications as $a): ?>
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input app-row-check"
                                   name="ids[]" value="<?= (int) $a['id'] ?>"
                                   aria-label="Select application <?= esc($a['application_no'] ?? $a['id']) ?>">
                        </td>
                        <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                        <td>
                            <?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?>
                            <div class="small text-muted"><?= esc($a['email'] ?? $a['account_email'] ?? '') ?></div>
                        </td>
                        <td><?= esc($a['enrolment_number'] ?? '—') ?></td>
                        <td class="small">
                            <?php if (isset($a['age_years']) && $a['age_years'] !== null && $a['age_years'] !== ''): ?>
                                <?= (int) $a['age_years'] ?>y <?= (int) ($a['age_months'] ?? 0) ?>m <?= (int) ($a['age_days'] ?? 0) ?>d
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?= (int) ($a['practice_years'] ?? 0) ?>y
                            <?= (int) ($a['practice_months'] ?? 0) ?>m
                        </td>
                        <td><?= ssa_status_badge($a['status']) ?></td>
                        <td class="small text-muted"><?= esc($a['submitted_at'] ?? '—') ?></td>
                        <td class="text-end">
                            <a href="<?= base_url('admin/applications/' . $a['id']) ?>"
                               class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                View
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
<?= form_close() ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var selectAll = document.getElementById('selectAllApps');
  var checks = document.querySelectorAll('.app-row-check');
  var countEl = document.getElementById('bulkSelectedCount');
  var submitBtn = document.getElementById('bulkStatusSubmit');
  var form = document.getElementById('bulkStatusForm');
  var statusSel = document.getElementById('bulkStatus');

  function syncBulkUi() {
    var n = 0;
    checks.forEach(function (c) { if (c.checked) n++; });
    if (countEl) countEl.textContent = n + ' selected';
    if (submitBtn) submitBtn.disabled = n < 1 || !statusSel || !statusSel.value;
    if (selectAll && checks.length) {
      selectAll.checked = n === checks.length && n > 0;
      selectAll.indeterminate = n > 0 && n < checks.length;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', function () {
      checks.forEach(function (c) { c.checked = selectAll.checked; });
      syncBulkUi();
    });
  }
  checks.forEach(function (c) {
    c.addEventListener('change', syncBulkUi);
  });
  if (statusSel) {
    statusSel.addEventListener('change', syncBulkUi);
  }
  if (form) {
    form.addEventListener('submit', function (e) {
      var n = 0;
      checks.forEach(function (c) { if (c.checked) n++; });
      if (n < 1 || !statusSel.value) {
        e.preventDefault();
        return;
      }
      var label = statusSel.options[statusSel.selectedIndex].text;
      if (!confirm('Update ' + n + ' application(s) to "' + label + '"?')) {
        e.preventDefault();
      }
    });
  }
  syncBulkUi();
});
</script>

<?= $this->endSection() ?>
