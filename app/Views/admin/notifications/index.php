<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Notification templates</h1>
        <p class="page-subtitle">Email and SMS message content for portal events (admin-controlled)</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('admin/settings/email') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear me-1"></i> Email transport
        </a>
        <a href="<?= base_url('admin/notifications/new') ?>" class="btn btn-mhc btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add template
        </a>
        <?php if (empty($templates)): ?>
            <span class="small text-muted align-self-center">No templates — defaults will load when the list is empty.</span>
        <?php endif; ?>
    </div>
</div>

<?= view('partials/table_toolbar', [
    'q'              => $q,
    'perPage'        => $perPage,
    'allowedPerPage' => $allowedPerPage,
    'placeholder'    => 'Name / event / subject…',
    'action'         => base_url('admin/notifications'),
]) ?>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Event</th>
                    <th>Email subject</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">SMS</th>
                    <th class="text-center">Active</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="7" class="p-3 text-muted">No notification templates found.</td>
                    </tr>
                <?php else: foreach ($templates as $t): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($t['name']) ?></div>
                            <?php if (! empty($t['description'])): ?>
                                <div class="small text-muted"><?= esc($t['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><code><?= esc($t['event_key']) ?></code></td>
                        <td class="small"><?= esc($t['email_subject']) ?></td>
                        <td class="text-center">
                            <?php if (! empty($t['email_enabled']) && $t['email_enabled'] !== '0' && $t['email_enabled'] !== 'f'): ?>
                                <span class="badge bg-success">On</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Off</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (! empty($t['sms_enabled']) && $t['sms_enabled'] !== '0' && $t['sms_enabled'] !== 'f'): ?>
                                <span class="badge bg-success">On</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Off</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (! empty($t['is_active']) && $t['is_active'] !== '0' && $t['is_active'] !== 'f'): ?>
                                <span class="badge bg-primary">Active</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= base_url('admin/notifications/' . (int) $t['id'] . '/edit') ?>"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?= form_open('admin/notifications/' . (int) $t['id'] . '/delete', [
                                'class'  => 'd-inline',
                                'onsubmit' => "return confirm('Delete this notification template?');",
                            ]) ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm">
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
