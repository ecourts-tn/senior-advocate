<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$o = $option ?? [];
$isEdit = ! empty($isEdit);
$formAction = $isEdit
    ? 'admin/masters/' . $category . '/' . (int) $o['id']
    : 'admin/masters/' . $category;
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= base_url('admin/masters') ?>">Master management</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('admin/masters/' . $category) ?>"><?= esc($meta['label']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Edit' : 'Add' ?></li>
            </ol>
        </nav>
        <h1 class="page-title"><?= esc($title) ?></h1>
        <p class="page-subtitle mb-0"><?= esc($meta['description']) ?></p>
    </div>
    <a href="<?= base_url('admin/masters/' . $category) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to list
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-mhc">
            <div class="card-header"><?= $isEdit ? 'Edit value' : 'New value' ?></div>
            <div class="card-body">
                <?= form_open($formAction) ?>
                <input type="hidden" name="category" value="<?= esc($category) ?>">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Master type</label>
                        <input type="text" class="form-control bg-light" value="<?= esc($meta['label']) ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label required" for="label">Label</label>
                        <input type="text" name="label" id="label" class="form-control" required maxlength="255"
                               value="<?= esc(old('label', $o['label'] ?? '')) ?>"
                               placeholder="Display value on the form">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="sort_order">Sort order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control"
                               value="<?= esc(old('sort_order', (string) ($o['sort_order'] ?? '0'))) ?>">
                        <div class="form-text">Lower numbers appear first.</div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="isActive"
                                <?= old('is_active', ! isset($o['is_active']) || \App\Models\MasterRegistry::isTruthy($o['is_active'] ?? true) ? '1' : '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Active (shown on application form)</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Save changes' : 'Create value' ?>
                    </button>
                    <a href="<?= base_url('admin/masters/' . $category) ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">Notes</strong>
            Do not create a label named “Others” — it is always offered on the form with a free-text field.
            Inactive values are hidden from new applications; existing applications keep any previously saved text.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
