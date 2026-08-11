<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Staff Dashboard</h1>
        <p class="page-subtitle">Senior Advocate Designation — classification overview</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
            <a href="<?= base_url('admin/notifications') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-megaphone me-1"></i> Notifications
            </a>
        <?php endif; ?>
        <a href="<?= base_url('admin/applications/status') ?>" class="btn btn-mhc">
            <i class="bi bi-ui-checks me-1"></i> Update status
        </a>
        <a href="<?= base_url('admin/applications') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-folder2-open me-1"></i> All Applications
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Submitted', $stats['submitted'] ?? 0, 'bi-send'],
        ['Select Listed', $stats['listed'] ?? 0, 'bi-check-circle'],
        ['Wait Listed', $stats['waitlisted'] ?? 0, 'bi-hourglass-split'],
        ['Rejected', $stats['rejected'] ?? 0, 'bi-x-circle'],
        ['Total (non-draft)', $stats['total'] ?? 0, 'bi-collection'],
        ['Applicants', $stats['applicants'] ?? 0, 'bi-people'],
    ];
    foreach ($cards as [$label, $val, $icon]):
    ?>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card card-mhc stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="stat-label"><?= esc($label) ?></div>
                        <i class="bi <?= $icon ?> text-muted"></i>
                    </div>
                    <div class="stat-value mt-1"><?= (int) $val ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card card-mhc h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1"></i> Recent submissions</span>
                <a href="<?= base_url('admin/applications') ?>" class="btn btn-sm btn-outline-secondary">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Application No.</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recent)): ?>
                            <tr>
                                <td colspan="5" class="text-muted p-4 text-center">No submitted applications yet.</td>
                            </tr>
                        <?php else: foreach ($recent as $a): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($a['application_no'] ?? '—') ?></td>
                                <td><?= esc(trim(($a['title'] ?? '') . ' ' . ($a['full_name'] ?? ''))) ?></td>
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
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <?php
        $authSummary  = $authSummary ?? ['failed' => 0, 'blocked' => 0, 'window_hours' => 24];
        $authAttempts = $authAttempts ?? [];
        $failed24     = (int) ($authSummary['failed'] ?? 0);
        $blocked24    = (int) ($authSummary['blocked'] ?? 0);
        ?>
        <div class="card card-mhc h-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-shield-exclamation me-1"></i> Unauthorized access attempts</span>
                <?php if ((current_user()['role'] ?? '') === 'admin'): ?>
                    <a href="<?= base_url('admin/audit?action=login_failed') ?>" class="btn btn-sm btn-outline-secondary">
                        Full audit
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <div>
                        <div class="small text-muted">Failed logins (24h)</div>
                        <div class="fs-4 fw-semibold <?= $failed24 > 0 ? 'text-danger' : '' ?>"><?= $failed24 ?></div>
                    </div>
                    <div>
                        <div class="small text-muted">Temporarily blocked (24h)</div>
                        <div class="fs-4 fw-semibold <?= $blocked24 > 0 ? 'text-warning' : '' ?>"><?= $blocked24 ?></div>
                    </div>
                </div>
                <p class="small text-muted mb-2">
                    Valid credentials grant access to the SSA dashboard and application processing.
                    Monitor failed attempts below. Accounts are temporarily locked after repeated failures.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                        <tr>
                            <th>When</th>
                            <th>Email</th>
                            <th>Reason</th>
                            <th>IP</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($authAttempts)): ?>
                            <tr>
                                <td colspan="4" class="text-muted small p-3 text-center">No recent unauthorized attempts.</td>
                            </tr>
                        <?php else: foreach ($authAttempts as $row):
                            $details = [];
                            if (! empty($row['details'])) {
                                $decoded = json_decode((string) $row['details'], true);
                                if (is_array($decoded)) {
                                    $details = $decoded;
                                }
                            }
                            $reason = (string) ($details['reason'] ?? ($row['action'] ?? '—'));
                            $em     = (string) ($details['email'] ?? '—');
                            ?>
                            <tr>
                                <td class="small text-nowrap"><?= esc($row['created_at'] ?? '—') ?></td>
                                <td class="small"><?= esc($em) ?></td>
                                <td class="small"><code><?= esc($reason) ?></code></td>
                                <td class="small text-muted"><?= esc($row['ip_address'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
