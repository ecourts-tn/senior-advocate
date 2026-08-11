<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Form dropdown options</h1>
        <p class="page-subtitle">Manage lists used on the application form (qualifications, courts, tribunals, etc.). “Others” is always available to applicants.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= form_open('admin/lookups/seed-defaults', ['class' => 'd-inline']) ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"
                onclick="return confirm('Add any missing default options? Existing rows are not changed.');">
            <i class="bi bi-arrow-repeat me-1"></i> Ensure defaults
        </button>
        <?= form_close() ?>
        <a href="<?= base_url('admin/lookups/new' . ($category !== '' ? '?category=' . rawurlencode($category) : '')) ?>"
           class="btn btn-mhc btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add option
        </a>
    </div>
</div>

<ul class="nav nav-pills flex-wrap gap-1 mb-3">
    <li class="nav-item">
        <a class="nav-link <?= $category === '' ? 'active' : '' ?>" href="<?= base_url('admin/lookups') ?>">All</a>
    </li>
    <?php foreach ($categories as $key => $lab): ?>
        <li class="nav-item">
            <a class="nav-link <?= $category === $key ? 'active' : '' ?>"
               href="<?= base_url('admin/lookups?category=' . rawurlencode($key)) ?>">
                <?= esc($lab) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php
ob_start();
?>
<div class="col-md-3">
    <label class="form-label" for="categoryFilter">Category</label>
    <select name="category" id="categoryFilter" class="form-select">
        <option value="">All categories</option>
        <?php foreach ($categories as $key => $lab): ?>
            <option value="<?= esc($key) ?>" <?= $category === $key ? 'selected' : '' ?>><?= esc($lab) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php
$extraFilters = ob_get_clean();

echo view('partials/table_toolbar', [
    'q'                => $q,
    'perPage'          => $perPage,
    'allowedPerPage'   => $allowedPerPage,
    'placeholder'      => 'Label / category…',
    'action'           => base_url('admin/lookups'),
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
                    <th>Category</th>
                    <th>Label</th>
                    <th class="text-center">Sort</th>
                    <th class="text-center">Active</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($options)): ?>
                    <tr><td colspan="5" class="p-3 text-muted">No options found.</td></tr>
                <?php else: foreach ($options as $opt): ?>
                    <tr>
                        <td>
                            <span class="fw-semibold"><?= esc($categories[$opt['category']] ?? $opt['category']) ?></span>
                            <div class="small text-muted"><code><?= esc($opt['category']) ?></code></div>
                        </td>
                        <td><?= esc($opt['label']) ?></td>
                        <td class="text-center"><?= (int) $opt['sort_order'] ?></td>
                        <td class="text-center">
                            <?php if (\App\Models\FormLookupOptionModel::isTruthy($opt['is_active'] ?? false)): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= base_url('admin/lookups/' . (int) $opt['id'] . '/edit') ?>"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?= form_open('admin/lookups/' . (int) $opt['id'] . '/delete', [
                                'class'    => 'd-inline',
                                'onsubmit' => "return confirm('Delete this option?');",
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
