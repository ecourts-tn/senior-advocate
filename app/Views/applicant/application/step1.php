<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 1 of 7 — Personal Details (Sl. No. 1–5)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/1', ['id' => 'stepForm']) ?>
        <div class="section-title">1–5. Personal Particulars</div>
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label required">Title</label>
                <select name="title" class="form-select" required>
                    <?php foreach (['Dr.', 'Mr.', 'Ms.', 'Mrs.'] as $t): ?>
                        <option value="<?= $t ?>" <?= old('title', $app['title'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-10">
                <label class="form-label required">Name of the Applicant-Advocate</label>
                <input type="text" name="full_name" class="form-control" required
                       value="<?= esc(old('full_name', $app['full_name'] ?? '')) ?>">
                <div class="form-text">Must tally with enrolment certificate. No abbreviations.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label required">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" required
                       value="<?= esc(old('date_of_birth', $app['date_of_birth'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Age (as on 01.01.2026)</label>
                <input type="text" class="form-control" readonly
                       value="<?= esc($app['age_years'] ?? '—') ?> years (auto-calculated on save)">
            </div>
            <div class="col-md-6">
                <label class="form-label required">Address — Office</label>
                <textarea name="address_office" class="form-control" rows="3" required><?= esc(old('address_office', $app['address_office'] ?? '')) ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Address — Residence</label>
                <textarea name="address_residence" class="form-control" rows="3" required><?= esc(old('address_residence', $app['address_residence'] ?? '')) ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Landline</label>
                <input type="text" name="phone_landline" class="form-control"
                       value="<?= esc(old('phone_landline', $app['phone_landline'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label required">Mobile</label>
                <input type="text" name="mobile" class="form-control" required maxlength="15"
                       value="<?= esc(old('mobile', $app['mobile'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label required">Email</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= esc(old('email', $app['email'] ?? '')) ?>">
            </div>
            <div class="col-12">
                <label class="form-label required">Educational / Professional Qualifications</label>
                <textarea name="qualifications" class="form-control" rows="3" required><?= esc(old('qualifications', $app['qualifications'] ?? '')) ?></textarea>
            </div>
        </div>
        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
