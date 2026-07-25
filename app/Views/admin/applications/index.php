<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Applications</h1>
    <p class="page-subtitle">Search and review Senior Advocate designation applications</p>
</div>

<?php
ob_start();
?>
<div class="col-md-3">
    <label class="form-label" for="statusFilter">Status</label>
    <select name="status" id="statusFilter" class="form-select">
        <option value="">All (except drafts)</option>
        <?php foreach ($statuses as $k => $lab): ?>
            <option value="<?= esc($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
        <?php endforeach; ?>
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
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($applications)): ?>
                    <tr><td colspan="7" class="p-3 text-muted">No records found.</td></tr>
                <?php else: foreach ($applications as $a): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                        <td>
                            <?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?>
                            <div class="small text-muted"><?= esc($a['email'] ?? $a['account_email'] ?? '') ?></div>
                        </td>
                        <td><?= esc($a['enrolment_number'] ?? '—') ?></td>
                        <td><?= esc($a['mobile'] ?? '—') ?></td>
                        <td><?= sad_status_badge($a['status']) ?></td>
                        <td class="small text-muted"><?= esc($a['submitted_at'] ?? '—') ?></td>
                        <td class="text-end">
                            <a href="<?= base_url('admin/applications/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary">Review</a>
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
