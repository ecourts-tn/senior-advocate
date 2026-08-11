<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= base_url('admin/masters') ?>">Master management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($meta['label']) ?></li>
            </ol>
        </nav>
        <h1 class="page-title">
            <i class="bi <?= esc($meta['icon']) ?> me-1" aria-hidden="true"></i><?= esc($meta['label']) ?>
        </h1>
        <p class="page-subtitle mb-0"><?= esc($meta['description']) ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('admin/masters') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-grid me-1"></i> All masters
        </a>
        <?= form_open('admin/masters/' . $category . '/seed-defaults', ['class' => 'd-inline']) ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"
                onclick="return confirm('Add missing default values for this list?');">
            <i class="bi bi-arrow-repeat me-1"></i> Defaults
        </button>
        <?= form_close() ?>
        <a href="<?= base_url('admin/masters/' . $category . '/new') ?>" class="btn btn-mhc btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add value
        </a>
    </div>
</div>

<ul class="nav nav-pills flex-wrap gap-1 mb-3">
    <?php foreach ($masters as $key => $m): ?>
        <li class="nav-item">
            <a class="nav-link <?= $category === $key ? 'active' : '' ?>"
               href="<?= base_url('admin/masters/' . $key) ?>">
                <i class="bi <?= esc($m['icon']) ?> me-1" aria-hidden="true"></i><?= esc($m['label']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?= view('partials/table_toolbar', [
    'q'              => $q,
    'perPage'        => $perPage,
    'allowedPerPage' => $allowedPerPage,
    'placeholder'    => 'Search label…',
    'action'         => base_url('admin/masters/' . $category),
]) ?>

<div class="card card-mhc">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Label</th>
                    <th class="text-center">Sort</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($options)): ?>
                    <tr><td colspan="4" class="p-3 text-muted">No values yet. Add one or load defaults.</td></tr>
                <?php else: foreach ($options as $opt): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($opt['label']) ?></td>
                        <td class="text-center"><?= (int) $opt['sort_order'] ?></td>
                        <td class="text-center">
                            <?php if (\App\Models\MasterRegistry::isTruthy($opt['is_active'] ?? false)): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= base_url('admin/masters/' . $category . '/' . (int) $opt['id'] . '/edit') ?>"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?= form_open('admin/masters/' . $category . '/' . (int) $opt['id'] . '/delete', [
                                'class'    => 'd-inline',
                                'onsubmit' => "return confirm('Delete this master value?');",
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
