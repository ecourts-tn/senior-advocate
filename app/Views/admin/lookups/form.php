<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$o = $option ?? [];
$isEdit = ! empty($isEdit);
$formAction = $isEdit
    ? 'admin/lookups/' . (int) $o['id']
    : 'admin/lookups';
$defaultCategory = old('category', $o['category'] ?? ($category ?? ''));
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title"><?= esc($title) ?></h1>
        <p class="page-subtitle">Options appear on the applicant form; “Others” is always appended automatically.</p>
    </div>
    <a href="<?= base_url('admin/lookups' . ($defaultCategory ? '?category=' . rawurlencode($defaultCategory) : '')) ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to list
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-mhc">
            <div class="card-header"><?= $isEdit ? 'Edit option' : 'New option' ?></div>
            <div class="card-body">
                <?= form_open($formAction) ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="category">Category</label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="">Select…</option>
                            <?php foreach ($categories as $key => $lab): ?>
                                <option value="<?= esc($key) ?>" <?= $defaultCategory === $key ? 'selected' : '' ?>>
                                    <?= esc($lab) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sort_order">Sort order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control"
                               value="<?= esc(old('sort_order', (string) ($o['sort_order'] ?? '0'))) ?>">
                        <div class="form-text">Lower numbers appear first.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label required" for="label">Label</label>
                        <input type="text" name="label" id="label" class="form-control" required maxlength="255"
                               value="<?= esc(old('label', $o['label'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="isActive"
                                <?= old('is_active', ! isset($o['is_active']) || \App\Models\FormLookupOptionModel::isTruthy($o['is_active'] ?? true) ? '1' : '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">Active (shown on form)</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Save changes' : 'Create option' ?>
                    </button>
                    <a href="<?= base_url('admin/lookups') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">Notes</strong>
            Do not create a label named “Others” — the form always offers that choice and captures free text when selected.
            Inactive options are hidden from new applications but existing saved values remain on records.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
