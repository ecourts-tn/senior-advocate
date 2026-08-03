<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$t = $template ?? [];
$isEdit = ! empty($isEdit);
$formAction = $isEdit
    ? 'admin/notifications/' . (int) $t['id']
    : 'admin/notifications';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title"><?= esc($title) ?></h1>
        <p class="page-subtitle">
            <?= $isEdit
                ? 'Update subject, body and channel toggles for this event'
                : 'Create a template for a portal notification event' ?>
        </p>
    </div>
    <a href="<?= base_url('admin/notifications') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to list
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-mhc">
            <div class="card-header"><?= $isEdit ? 'Edit template' : 'New template' ?></div>
            <div class="card-body">
                <?= form_open($formAction) ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="event_key">Event</label>
                        <?php if ($isEdit): ?>
                            <input type="text" class="form-control" id="event_key" value="<?= esc($t['event_key'] ?? '') ?>" disabled>
                            <input type="hidden" name="event_key" value="<?= esc($t['event_key'] ?? '') ?>">
                            <div class="form-text">Event key cannot be changed after creation.</div>
                        <?php else: ?>
                            <select name="event_key" id="event_key" class="form-select" required>
                                <option value="">Select event…</option>
                                <?php foreach ($available as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" <?= old('event_key') === $key ? 'selected' : '' ?>>
                                        <?= esc($label) ?> (<?= esc($key) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="name">Display name</label>
                        <input type="text" name="name" id="name" class="form-control" required maxlength="150"
                               value="<?= esc(old('name', $t['name'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Description</label>
                        <input type="text" name="description" id="description" class="form-control" maxlength="500"
                               value="<?= esc(old('description', $t['description'] ?? '')) ?>"
                               placeholder="When is this notification sent?">
                    </div>

                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="isActive"
                                    <?= old('is_active', ! isset($t['is_active']) || (! empty($t['is_active']) && $t['is_active'] !== '0' && $t['is_active'] !== 'f') ? '1' : '') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Template active</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="email_enabled" value="1" id="emailEnabled"
                                    <?= old('email_enabled', ! isset($t['email_enabled']) || (! empty($t['email_enabled']) && $t['email_enabled'] !== '0' && $t['email_enabled'] !== 'f') ? '1' : '') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="emailEnabled">Send email</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="sms_enabled" value="1" id="smsEnabled"
                                    <?= old('sms_enabled', (isset($t['sms_enabled']) && ! empty($t['sms_enabled']) && $t['sms_enabled'] !== '0' && $t['sms_enabled'] !== 'f') ? '1' : '') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="smsEnabled">Send SMS</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label required" for="email_subject">Email subject</label>
                        <input type="text" name="email_subject" id="email_subject" class="form-control" required maxlength="500"
                               value="<?= esc(old('email_subject', $t['email_subject'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="email_body">Email body (HTML)</label>
                        <textarea name="email_body" id="email_body" class="form-control font-monospace" rows="14"
                                  spellcheck="false"><?= esc(old('email_body', $t['email_body'] ?? '')) ?></textarea>
                        <div class="form-text">
                            Inner HTML is wrapped in the portal email shell automatically.
                            Paste a full HTML document (starting with <code>&lt;!DOCTYPE</code> or <code>&lt;html</code>) to skip the wrapper.
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="sms_body">SMS body</label>
                        <textarea name="sms_body" id="sms_body" class="form-control" rows="3" maxlength="500"><?= esc(old('sms_body', $t['sms_body'] ?? '')) ?></textarea>
                        <div class="form-text">Plain text. Keep under ~160 characters when possible for single-segment SMS.</div>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Save changes' : 'Create template' ?>
                    </button>
                    <a href="<?= base_url('admin/notifications') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-mhc mb-3">
            <div class="card-header">Placeholders</div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    Use double curly braces. Values are escaped for HTML emails.
                </p>
                <ul class="list-unstyled small mb-0">
                    <?php foreach ($placeholders as $ph): ?>
                        <li class="mb-1"><code><?= esc($ph) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">Notes</strong>
            Inactive templates skip both email and SMS for that event.
            Channel toggles control email vs SMS independently when the template is active.
            Transport (SMTP) is configured under Email settings.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
