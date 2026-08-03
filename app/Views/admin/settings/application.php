<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$toLocal = static function (?string $v): string {
    $v = trim((string) $v);
    if ($v === '') {
        return '';
    }
    // datetime-local wants YYYY-MM-DDTHH:MM
    $v = str_replace(' ', 'T', $v);
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $v, $m)) {
        return substr($v, 0, 16);
    }

    return $v;
};
$windowOpen = \App\Models\ApplicationModel::isEditWindowOpen($settings);
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Application cycle &amp; edit window</h1>
        <p class="page-subtitle">One application per year, and admin-controlled correction period after submission</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('admin/settings/email') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-envelope me-1"></i> Email settings
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-mhc">
            <div class="card-header">Cycle rules</div>
            <div class="card-body">
                <?= form_open('admin/settings/application') ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required" for="cycle_year">Designation cycle year</label>
                        <input type="number" name="cycle_year" id="cycle_year" class="form-control" required
                               min="2000" max="2100"
                               value="<?= esc(old('cycle_year', $settings['cycle_year'] ?? date('Y'))) ?>">
                        <div class="form-text">Used on new applications and application numbers.</div>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="one_per_year" value="1" id="onePerYear"
                                <?= ! empty($settings['one_per_year']) && $settings['one_per_year'] !== '0' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="onePerYear">
                                Limit to <strong>one application per applicant per cycle year</strong>
                            </label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h2 class="h6 text-uppercase text-muted mb-3">Post-submission edit window</h2>
                <p class="small text-muted">
                    When enabled (and within the date range), applicants may edit and resubmit applications that are
                    <em>Submitted</em>, <em>Under review</em>, or <em>Pending approval</em> for the current cycle.
                    Drafts and “Returned for correction” remain editable as usual. Accepted/Rejected applications stay locked.
                </p>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="edit_window_enabled" value="1" id="editWindowEnabled"
                        <?= ! empty($settings['edit_window_enabled']) && $settings['edit_window_enabled'] !== '0' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="editWindowEnabled">Enable edit window</label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="edit_window_from">Window opens</label>
                        <input type="datetime-local" name="edit_window_from" id="edit_window_from" class="form-control"
                               value="<?= esc(old('edit_window_from', $toLocal($settings['edit_window_from'] ?? ''))) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="edit_window_to">Window closes</label>
                        <input type="datetime-local" name="edit_window_to" id="edit_window_to" class="form-control"
                               value="<?= esc(old('edit_window_to', $toLocal($settings['edit_window_to'] ?? ''))) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="edit_window_message">Message shown to applicants</label>
                        <textarea name="edit_window_message" id="edit_window_message" class="form-control" rows="3"
                                  maxlength="1000"><?= esc(old('edit_window_message', $settings['edit_window_message'] ?? '')) ?></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> Save settings
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-mhc mb-3">
            <div class="card-header">Current status</div>
            <div class="card-body">
                <?php if ($windowOpen): ?>
                    <span class="badge bg-success mb-2">Edit window is OPEN</span>
                <?php else: ?>
                    <span class="badge bg-secondary mb-2">Edit window is CLOSED</span>
                <?php endif; ?>
                <ul class="small mb-0 ps-3">
                    <li>Cycle year: <strong><?= esc($settings['cycle_year'] ?? '—') ?></strong></li>
                    <li>One per year: <strong><?= ! empty($settings['one_per_year']) && $settings['one_per_year'] !== '0' ? 'Yes' : 'No' ?></strong></li>
                    <li>From: <?= esc($settings['edit_window_from'] ?: '—') ?></li>
                    <li>To: <?= esc($settings['edit_window_to'] ?: '—') ?></li>
                </ul>
            </div>
        </div>
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">How it works</strong>
            Applicants cannot start a second application in the same cycle year after they have created or submitted one.
            To allow corrections after submission, enable the edit window for a defined period.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
