<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1 class="page-title">Applicant Dashboard</h1>
        <p class="page-subtitle">Welcome, <?= esc(session()->get('name')) ?></p>
    </div>
    <?php if ($draft): ?>
        <a href="<?= base_url('applicant/application/step/' . (int) $draft['current_step']) ?>" class="btn btn-gold">
            <i class="bi bi-pencil-square me-1"></i>
            Continue Draft (Step <?= (int) $draft['current_step'] ?>)
        </a>
    <?php else: ?>
        <a href="<?= base_url('applicant/application/start') ?>" class="btn btn-mhc">
            <i class="bi bi-plus-lg me-1"></i> Start New Application
        </a>
    <?php endif; ?>
</div>

<?php
ob_start();
?>
<div class="col-md-3">
    <label class="form-label" for="statusFilter">Status</label>
    <select name="status" id="statusFilter" class="form-select">
        <option value="">All statuses</option>
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
    'placeholder'      => 'Application no. / name…',
    'action'           => base_url('applicant/dashboard'),
    'extraFilters'     => $extraFilters,
    'hasActiveFilters' => $hasActiveFilters ?? false,
]);
?>

<div class="card card-mhc">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-folder2-open me-1" aria-hidden="true"></i> My Applications</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($applications) && $total === 0 && $q === '' && $status === ''): ?>
            <div class="empty-state">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <p class="mb-2">No applications yet.</p>
                <p class="small mb-3">Begin your Application-cum-Consent Letter for Senior Advocate designation.</p>
                <a href="<?= base_url('applicant/application/start') ?>" class="btn btn-mhc btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Start New Application
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Application No.</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Step</th>
                        <th>Submitted</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="6" class="p-3 text-muted">No records match your filters.</td></tr>
                    <?php else: foreach ($applications as $a): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                            <td><?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?></td>
                            <td><?= sad_status_badge($a['status']) ?></td>
                            <td>
                                <span class="text-muted small"><?= (int) $a['current_step'] ?> / 7</span>
                            </td>
                            <td class="small text-muted"><?= esc($a['submitted_at'] ?? '—') ?></td>
                            <td class="text-md-end">
                                <div class="d-flex flex-wrap gap-1 justify-content-md-end">
                                    <?php if (in_array($a['status'], ['draft', 'returned'], true)): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('applicant/application/step/' . (int) $a['current_step']) ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('applicant/application/view/' . $a['id']) ?>">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if (! empty($a['application_no']) || $a['status'] !== 'draft'): ?>
                                        <a class="btn btn-sm btn-outline-danger" href="<?= base_url('applicant/application/pdf/' . $a['id']) ?>" target="_blank">
                                            <i class="bi bi-file-pdf"></i> PDF
                                        </a>
                                    <?php endif; ?>
                                </div>
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
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card card-mhc h-100">
            <div class="card-body">
                <h6 class="section-title mb-2" style="font-size:0.95rem;">Need help?</h6>
                <p class="small text-muted mb-2">
                    Review upload limits, paper-book requirements and citation rules before submitting.
                </p>
                <a href="<?= base_url('instructions') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-journal-text me-1"></i> Read Instructions
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="warning-box h-100 mb-0">
            <strong class="d-block mb-1">Reminder</strong>
            Errors cannot be rectified after submission and may result in rejection.
            Ensure all particulars match your enrolment certificate.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
