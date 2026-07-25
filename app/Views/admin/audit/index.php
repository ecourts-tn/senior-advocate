<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Audit Logs</h1>
    <p class="page-subtitle">Security and activity trail for the portal</p>
</div>

<?php
ob_start();
?>
<div class="col-md-3">
    <label class="form-label" for="actionFilter">Action</label>
    <select name="action" id="actionFilter" class="form-select">
        <option value="">All actions</option>
        <?php foreach ($actions as $act): ?>
            <option value="<?= esc($act) ?>" <?= $action === $act ? 'selected' : '' ?>><?= esc($act) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();

echo view('partials/table_toolbar', [
    'q'                => $q,
    'perPage'          => $perPage,
    'allowedPerPage'   => $allowedPerPage,
    'placeholder'      => 'User / app no. / action / IP…',
    'action'           => base_url('admin/audit'),
    'extraFilters'     => $extraFilters,
    'hasActiveFilters' => $hasActiveFilters ?? false,
]);
?>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>When</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Application</th>
                    <th>IP</th>
                    <th>Details</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="p-3 text-muted">No audit entries found.</td></tr>
                <?php else: foreach ($logs as $log): ?>
                    <tr>
                        <td><?= (int) $log['id'] ?></td>
                        <td class="small text-nowrap"><?= esc($log['created_at']) ?></td>
                        <td>
                            <?php if (! empty($log['user_name']) || ! empty($log['user_email'])): ?>
                                <div class="fw-semibold"><?= esc($log['user_name'] ?? '—') ?></div>
                                <?php if (! empty($log['user_email'])): ?>
                                    <div class="small text-muted"><?= esc($log['user_email']) ?></div>
                                <?php endif; ?>
                            <?php elseif (! empty($log['user_id'])): ?>
                                <span class="text-muted">User #<?= (int) $log['user_id'] ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><code><?= esc($log['action']) ?></code></td>
                        <td>
                            <?php if (! empty($log['application_id'])): ?>
                                <a href="<?= base_url('admin/applications/' . $log['application_id']) ?>" class="text-decoration-none">
                                    <?php if (! empty($log['application_no'])): ?>
                                        <span class="fw-semibold"><?= esc($log['application_no']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">#<?= (int) $log['application_id'] ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php if (! empty($log['applicant_name'])): ?>
                                    <div class="small text-muted"><?= esc($log['applicant_name']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= esc($log['ip_address'] ?? '') ?></td>
                        <td class="small"><code class="text-break"><?= esc($log['details'] ?? '') ?></code></td>
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
