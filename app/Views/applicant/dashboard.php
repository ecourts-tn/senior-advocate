<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1 class="page-title">Applicant Dashboard</h1>
        <p class="page-subtitle">Welcome, <?= esc(session()->get('name')) ?></p>
    </div>
    <?php if (! empty($draft)): ?>
        <a href="<?= base_url('applicant/application/step/' . (int) $draft['current_step']) ?>" class="btn btn-gold">
            <i class="bi bi-pencil-square me-1"></i>
            Continue Draft (Step <?= (int) $draft['current_step'] ?>)
        </a>
    <?php elseif (! empty($editable) && \App\Models\ApplicationModel::isEditableByApplicant($editable)): ?>
        <a href="<?= base_url('applicant/application/step/' . (int) ($editable['current_step'] ?: 1)) ?>" class="btn btn-gold">
            <i class="bi bi-pencil-square me-1"></i>
            Edit application (edit window open)
        </a>
    <?php elseif (! empty($canStart)): ?>
        <a href="<?= base_url('applicant/application/start') ?>" class="btn btn-mhc">
            <i class="bi bi-plus-lg me-1"></i> Start New Application
        </a>
    <?php endif; ?>
</div>

<?php
$periodInfo = $periodInfo ?? \App\Models\DesignationNotificationModel::applicationPeriodInfo();
if (! empty($periodInfo['open'])):
?>
    <div class="alert alert-success" role="status">
        <i class="bi bi-calendar-check me-1" aria-hidden="true"></i>
        <strong>Application period open.</strong>
        <?= esc($periodInfo['message'] ?? '') ?>
        <?php if (! empty($periodInfo['notification_number'])): ?>
            <span class="d-block small mt-1">
                Notification: <?= esc($periodInfo['notification_number']) ?>
                · <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($periodInfo['application_start_date'] ?? null)) ?>
                to
                <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($periodInfo['application_end_date'] ?? null)) ?>
            </span>
        <?php endif; ?>
    </div>
<?php elseif (empty($draft) && (empty($editable) || ! \App\Models\ApplicationModel::isEditableByApplicant($editable ?? []))): ?>
    <div class="alert alert-warning" role="status">
        <i class="bi bi-calendar-x me-1" aria-hidden="true"></i>
        <strong>Cannot start a new application.</strong>
        <?= esc($periodInfo['message'] ?? 'The application submission period is not open.') ?>
        <?php if (! empty($periodInfo['application_start_date']) || ! empty($periodInfo['application_end_date'])): ?>
            <span class="d-block small mt-1">
                Period:
                <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($periodInfo['application_start_date'] ?? null)) ?>
                –
                <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($periodInfo['application_end_date'] ?? null)) ?>
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (! empty($editWindow['open'])): ?>
    <div class="alert alert-info" role="status">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        <strong>Edit window open.</strong>
        <?= esc($editWindow['message'] ?: 'You may correct and resubmit your application during this period.') ?>
        <?php if (! empty($editWindow['to'])): ?>
            <span class="d-block small mt-1">
                Closes: <?= esc(\App\Models\DesignationNotificationModel::formatDateTime($editWindow['to'])) ?>
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (! empty($portalNotifications)): ?>
    <div class="card card-mhc mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-megaphone me-1" aria-hidden="true"></i> Official notifications</span>
            <a href="<?= base_url('notifications') ?>" class="btn btn-sm btn-outline-secondary">View all</a>
        </div>
        <ul class="list-group list-group-flush">
            <?php foreach ($portalNotifications as $n): ?>
                <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="fw-semibold"><?= esc($n['notification_number'] ?? '') ?></span>
                        <?php if (! empty($n['notification_date'])): ?>
                            <span class="text-muted small ms-1">
                                (<?= esc(date('d-m-Y', strtotime($n['notification_date']))) ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= base_url('notifications/document/' . (int) $n['id']) ?>"
                       class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

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
                <?php if (! empty($canStart)): ?>
                    <a href="<?= base_url('applicant/application/start') ?>" class="btn btn-mhc btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Start New Application
                    </a>
                <?php endif; ?>
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
                        <?php $rowEditable = \App\Models\ApplicationModel::isEditableByApplicant($a); ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                            <td><?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?></td>
                            <td><?= ssa_status_badge($a['status']) ?></td>
                            <td>
                                <span class="text-muted small"><?= (int) $a['current_step'] ?> / 7</span>
                            </td>
                            <td class="small text-muted"><?= esc($a['submitted_at'] ?? '—') ?></td>
                            <td class="text-md-end">
                                <div class="d-flex flex-wrap gap-1 justify-content-md-end">
                                    <?php if ($rowEditable): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= base_url('applicant/application/step/' . (int) ($a['current_step'] ?: 1)) ?>">
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
<?= $this->endSection() ?>
