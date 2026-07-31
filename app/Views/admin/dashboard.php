<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Staff Dashboard</h1>
        <p class="page-subtitle">Senior Advocate Designation — application decision overview</p>
    </div>
    <a href="<?= base_url('admin/applications') ?>" class="btn btn-mhc">
        <i class="bi bi-folder2-open me-1"></i> All Applications
    </a>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Submitted (awaiting decision)', $stats['submitted'], 'bi-send'],
        ['Accepted', $stats['approved'], 'bi-check-circle'],
        ['Rejected', $stats['rejected'], 'bi-x-circle'],
        ['Total (non-draft)', $stats['total'], 'bi-collection'],
        ['Applicants', $stats['applicants'], 'bi-people'],
    ];
    foreach ($cards as [$label, $val, $icon]):
    ?>
        <div class="col-6 col-md-3">
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

<div class="card card-mhc">
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
                        <td><?= sad_status_badge($a['status']) ?></td>
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

<?= $this->endSection() ?>
