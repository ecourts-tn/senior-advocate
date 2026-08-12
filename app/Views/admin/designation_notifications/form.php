<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$n = $notification ?? [];
$isEdit = ! empty($isEdit);
$formAction = $isEdit
    ? 'admin/notifications/' . (int) $n['id']
    : 'admin/notifications';

$isActiveDefault = $isEdit
    ? (! empty($n['is_active']) && $n['is_active'] !== 'f' && $n['is_active'] !== '0' && $n['is_active'] !== false)
    : true;
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title"><?= esc($title) ?></h1>
        <p class="page-subtitle">
            <?= $isEdit
                ? 'Update notification details and application / edit windows'
                : 'Create an official notification that opens an application cycle' ?>
        </p>
    </div>
    <a href="<?= base_url('admin/notifications') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to list
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-mhc">
            <div class="card-header"><?= $isEdit ? 'Edit notification' : 'New notification' ?></div>
            <div class="card-body">
                <?= form_open_multipart($formAction) ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="notification_number">Notification number</label>
                        <input type="text" name="notification_number" id="notification_number" class="form-control"
                               required maxlength="100"
                               value="<?= esc(old('notification_number', $n['notification_number'] ?? '')) ?>"
                               placeholder="e.g. ROC.No.123/2026">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="notification_date">Notification date</label>
                        <input type="date" name="notification_date" id="notification_date" class="form-control" required
                               value="<?= esc(old('notification_date', $n['notification_date'] ?? '')) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="title">Title / label (optional)</label>
                        <input type="text" name="title" id="title" class="form-control" maxlength="255"
                               value="<?= esc(old('title', $n['title'] ?? '')) ?>"
                               placeholder="Short label for admin lists">
                    </div>

                    <div class="col-12">
                        <hr class="my-1">
                        <h2 class="h6 text-uppercase text-muted mb-0">Notification document</h2>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="document">Upload notification (PDF)</label>
                        <input type="file" name="document" id="document" class="form-control"
                               accept=".pdf,application/pdf">
                        <div class="form-text">
                            Official notification PDF, max 10 MB. When uploaded, the document is shown on the public portal.
                            <?php if ($isEdit): ?>
                                Leave empty to keep the existing file.
                            <?php endif; ?>
                        </div>
                        <?php if ($isEdit && ! empty($n['document_path'])): ?>
                            <div class="mt-2 d-flex flex-wrap align-items-center gap-3">
                                <a href="<?= base_url('notifications/document/' . (int) $n['id']) ?>"
                                   class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> View current PDF
                                </a>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="remove_document" value="1"
                                           id="removeDocument">
                                    <label class="form-check-label" for="removeDocument">
                                        Remove current document
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <hr class="my-1">
                        <h2 class="h6 text-uppercase text-muted mb-0">Application period</h2>
                        <p class="small text-muted mb-0">Date and time when applicants may start and last submit applications.</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="application_start_date">Application start date &amp; time</label>
                        <input type="datetime-local" name="application_start_date" id="application_start_date"
                               class="form-control" required
                               value="<?= esc(old(
                                   'application_start_date',
                                   \App\Models\DesignationNotificationModel::toDatetimeLocal($n['application_start_date'] ?? '')
                               )) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="application_end_date">Application end date &amp; time</label>
                        <input type="datetime-local" name="application_end_date" id="application_end_date"
                               class="form-control" required
                               value="<?= esc(old(
                                   'application_end_date',
                                   \App\Models\DesignationNotificationModel::toDatetimeLocal($n['application_end_date'] ?? '')
                               )) ?>">
                        <div class="form-text">Applications close at this exact date and time.</div>
                    </div>

                    <div class="col-12">
                        <hr class="my-1">
                        <h2 class="h6 text-uppercase text-muted mb-0">Edit window</h2>
                        <p class="small text-muted mb-0">
                            Period when applicants may correct and resubmit after submission. Leave blank if not applicable.
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="edit_window_start_date">Edit window start date &amp; time</label>
                        <input type="datetime-local" name="edit_window_start_date" id="edit_window_start_date"
                               class="form-control"
                               value="<?= esc(old(
                                   'edit_window_start_date',
                                   \App\Models\DesignationNotificationModel::toDatetimeLocal($n['edit_window_start_date'] ?? '')
                               )) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="edit_window_end_date">Edit window end date &amp; time</label>
                        <input type="datetime-local" name="edit_window_end_date" id="edit_window_end_date"
                               class="form-control"
                               value="<?= esc(old(
                                   'edit_window_end_date',
                                   \App\Models\DesignationNotificationModel::toDatetimeLocal($n['edit_window_end_date'] ?? '')
                               )) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="remarks">Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3"
                                  maxlength="2000"><?= esc(old('remarks', $n['remarks'] ?? '')) ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" value="1" id="isActive"
                                <?= old('is_active', $isActiveDefault ? '1' : '') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">
                                Active (current notification cycle)
                            </label>
                        </div>
                        <div class="form-text">
                            Activating this notification deactivates others. Application period and edit window on this record control when applicants can apply or correct submissions.
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Save changes' : 'Create notification' ?>
                    </button>
                    <a href="<?= base_url('admin/notifications') ?>" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">How this works</strong>
            <ul class="mb-0 small ps-3">
                <li>Each official notification opens one application cycle.</li>
                <li>Application start/end dates control when applicants may submit.</li>
                <li>Edit window dates control post-submission corrections.</li>
                <li>Applications are listed notification-wise in admin.</li>
                <li>When a notification PDF is uploaded, it appears on the public portal for advocates to download/view.</li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
