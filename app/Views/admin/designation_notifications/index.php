<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">
            Official notifications that open application cycles and edit windows.
            Uploaded PDFs are published on the portal.
        </p>
    </div>
    <a href="<?= base_url('admin/notifications/new') ?>" class="btn btn-mhc btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add notification
    </a>
</div>

<?php
ob_start();
?>
<div class="col-md-3 col-lg-2">
    <label class="form-label" for="activeFilter">Status</label>
    <select name="active" id="activeFilter" class="form-select">
        <option value="">All</option>
        <option value="1" <?= ($activeOnly ?? '') === '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= ($activeOnly ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();

echo view('partials/table_toolbar', [
    'q'                => $q,
    'perPage'          => $perPage,
    'allowedPerPage'   => $allowedPerPage,
    'placeholder'      => 'Number / title / remarks…',
    'action'           => base_url('admin/notifications'),
    'extraFilters'     => $extraFilters,
    'hasActiveFilters' => $hasActiveFilters ?? false,
]);
?>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Notification No.</th>
                    <th>Date</th>
                    <th>Application period</th>
                    <th>Edit window</th>
                    <th class="text-center">Document</th>
                    <th class="text-center">Apps</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="8" class="p-3 text-muted">
                            No notifications yet. Create one to open an application cycle.
                        </td>
                    </tr>
                <?php else: foreach ($notifications as $n): ?>
                    <?php
                    $nid   = (int) $n['id'];
                    $count = (int) ($appCounts[$nid] ?? 0);
                    $appFrom = \App\Models\DesignationNotificationModel::formatDateTime($n['application_start_date'] ?? null);
                    $appTo   = \App\Models\DesignationNotificationModel::formatDateTime($n['application_end_date'] ?? null);
                    $editFrom = \App\Models\DesignationNotificationModel::formatDateTime($n['edit_window_start_date'] ?? null);
                    $editTo   = \App\Models\DesignationNotificationModel::formatDateTime($n['edit_window_end_date'] ?? null);
                    $isActive = ! empty($n['is_active']) && $n['is_active'] !== 'f' && $n['is_active'] !== '0' && $n['is_active'] !== false;
                    $hasDoc = ! empty($n['document_path']);
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($n['notification_number'] ?? '') ?></div>
                            <?php if (! empty($n['title'])): ?>
                                <div class="small text-muted"><?= esc($n['title']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?= ! empty($n['notification_date'])
                                ? esc(date('d-m-Y', strtotime($n['notification_date'])))
                                : '—' ?>
                        </td>
                        <td class="small">
                            <?= esc($appFrom) ?> → <?= esc($appTo) ?>
                        </td>
                        <td class="small">
                            <?= esc($editFrom) ?> → <?= esc($editTo) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($hasDoc): ?>
                                <a href="<?= base_url('notifications/document/' . $nid) ?>"
                                   class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener"
                                   title="View notification PDF (published on portal)">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/applications?notification_id=' . $nid) ?>"
                               class="badge <?= $count > 0 ? 'bg-primary' : 'bg-secondary' ?> text-decoration-none"
                               title="View applications for this notification">
                                <?= $count ?>
                            </a>
                        </td>
                        <td class="text-center">
                            <?php if ($isActive): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= base_url('admin/applications?notification_id=' . $nid) ?>"
                               class="btn btn-outline-secondary btn-sm" title="Applications">
                                <i class="bi bi-folder2-open"></i>
                            </a>
                            <a href="<?= base_url('admin/notifications/' . $nid . '/edit') ?>"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?= form_open('admin/notifications/' . $nid . '/delete', [
                                'class'    => 'd-inline',
                                'onsubmit' => "return confirm('Delete this notification?');",
                            ]) ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm" <?= $count > 0 ? 'disabled title="Linked applications exist"' : '' ?>>
                                <i class="bi bi-trash"></i>
                            </button>
                            <?= form_close() ?>
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
