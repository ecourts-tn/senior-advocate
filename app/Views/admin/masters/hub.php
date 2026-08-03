<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Master management</h1>
        <p class="page-subtitle">
            Maintain dropdown values used on the application form. Each master type has its own database table.
            Applicants always see an additional <strong>Others</strong> choice.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= form_open('admin/masters/seed-defaults', ['class' => 'd-inline']) ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"
                onclick="return confirm('Add any missing default master values? Existing rows are not changed.');">
            <i class="bi bi-arrow-repeat me-1"></i> Ensure all defaults
        </button>
        <?= form_close() ?>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($cards as $key => $card): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card card-mhc h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:2.75rem;height:2.75rem;">
                            <i class="bi <?= esc($card['icon']) ?> text-primary fs-5" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-1"><?= esc($card['label']) ?></h2>
                            <p class="small text-muted mb-0"><?= esc($card['description']) ?></p>
                            <?php if (! empty($card['table'])): ?>
                                <p class="small mb-0 mt-1"><code><?= esc($card['table']) ?></code></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex gap-3 small mb-3 mt-auto">
                        <span><strong><?= (int) $card['total'] ?></strong> total</span>
                        <span class="text-success"><strong><?= (int) $card['active'] ?></strong> active</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('admin/masters/' . $key) ?>" class="btn btn-mhc btn-sm">
                            <i class="bi bi-list-ul me-1"></i> Manage
                        </a>
                        <a href="<?= base_url('admin/masters/' . $key . '/new') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
