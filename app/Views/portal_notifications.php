<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle mb-0">
            Official notifications of the High Court of Madras relating to designation of Senior Advocates
        </p>
    </div>
    <a href="<?= base_url('rules') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-file-earmark-pdf me-1" aria-hidden="true"></i> Rules, 2026
    </a>
</div>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Notification No.</th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Application period</th>
                    <th class="text-end">Document</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-muted text-center">
                            No notification documents have been published yet.
                        </td>
                    </tr>
                <?php else: foreach ($notifications as $n): ?>
                    <?php
                    $nid = (int) $n['id'];
                    $appFrom = \App\Models\DesignationNotificationModel::formatDateTime($n['application_start_date'] ?? null);
                    $appTo   = \App\Models\DesignationNotificationModel::formatDateTime($n['application_end_date'] ?? null);
                    ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($n['notification_number'] ?? '') ?></td>
                        <td class="small">
                            <?= ! empty($n['notification_date'])
                                ? esc(date('d-m-Y', strtotime($n['notification_date'])))
                                : '—' ?>
                        </td>
                        <td class="small"><?= esc($n['title'] ?? '—') ?></td>
                        <td class="small"><?= esc($appFrom) ?> → <?= esc($appTo) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= base_url('notifications/document/' . $nid) ?>"
                               class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener">
                                <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
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
