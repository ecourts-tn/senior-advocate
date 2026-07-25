<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">Email configuration</h1>
        <p class="page-subtitle">SMTP / outbound mail settings for portal notifications</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('admin/settings/sms') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-phone me-1"></i> SMS settings
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-mhc">
            <div class="card-header">Mail server</div>
            <div class="card-body">
                <?= form_open('admin/settings/email') ?>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" id="emailEnabled"
                        <?= ! empty($settings['enabled']) && $settings['enabled'] !== '0' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="emailEnabled">Enable email sending</label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="from_email">From email</label>
                        <input type="email" name="from_email" id="from_email" class="form-control" required
                               value="<?= esc(old('from_email', $settings['from_email'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="from_name">From name</label>
                        <input type="text" name="from_name" id="from_name" class="form-control" required
                               value="<?= esc(old('from_name', $settings['from_name'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required" for="protocol">Delivery mode</label>
                        <select name="protocol" id="protocol" class="form-select">
                            <option value="smtp" <?= old('protocol', $settings['protocol'] ?? '') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                            <option value="file" <?= old('protocol', $settings['protocol'] ?? '') === 'file' ? 'selected' : '' ?>>File only (writable/mail)</option>
                        </select>
                        <div class="form-text">Use “File only” for development without an SMTP server.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_host">SMTP host</label>
                        <input type="text" name="smtp_host" id="smtp_host" class="form-control"
                               value="<?= esc(old('smtp_host', $settings['smtp_host'] ?? '')) ?>"
                               placeholder="smtp.example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_user">SMTP username</label>
                        <input type="text" name="smtp_user" id="smtp_user" class="form-control"
                               value="<?= esc(old('smtp_user', $settings['smtp_user'] ?? '')) ?>"
                               autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="smtp_pass">SMTP password</label>
                        <input type="password" name="smtp_pass" id="smtp_pass" class="form-control"
                               value="" autocomplete="new-password"
                               placeholder="<?= ! empty($settings['smtp_pass']) ? '•••••••• (leave blank to keep)' : '' ?>">
                        <div class="form-text">Leave blank to keep the existing password.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="smtp_port">SMTP port</label>
                        <input type="number" name="smtp_port" id="smtp_port" class="form-control"
                               value="<?= esc(old('smtp_port', $settings['smtp_port'] ?? '587')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="smtp_crypto">Encryption</label>
                        <select name="smtp_crypto" id="smtp_crypto" class="form-select">
                            <?php $crypto = old('smtp_crypto', $settings['smtp_crypto'] ?? 'tls'); ?>
                            <option value="tls" <?= $crypto === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= $crypto === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= $crypto === '' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> Save email settings
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-mhc mb-3">
            <div class="card-header">Send test email</div>
            <div class="card-body">
                <?= form_open('admin/settings/email/test') ?>
                <div class="mb-3">
                    <label class="form-label" for="test_email">Recipient</label>
                    <input type="email" name="test_email" id="test_email" class="form-control" required
                           placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-send me-1"></i> Send test
                </button>
                <?= form_close() ?>
            </div>
        </div>
        <div class="card card-mhc mb-3">
            <div class="card-header">Sample values (local / demo)</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tbody>
                    <tr><th scope="row">Enabled</th><td><?= esc($samples['enabled'] ?? '1') ?></td></tr>
                    <tr><th scope="row">From email</th><td><code><?= esc($samples['from_email'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">From name</th><td><?= esc($samples['from_name'] ?? '') ?></td></tr>
                    <tr><th scope="row">Mode</th><td><code>file</code> → <code>writable/mail/</code></td></tr>
                    <tr><th scope="row">SMTP host</th><td><code><?= esc($samples['smtp_host'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">SMTP user</th><td><code><?= esc($samples['smtp_user'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">SMTP pass</th><td><code><?= esc($samples['smtp_pass'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">Port / crypto</th><td><code><?= esc($samples['smtp_port'] ?? '587') ?></code> / <code><?= esc($samples['smtp_crypto'] ?? 'tls') ?></code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-mhc mb-3">
            <div class="card-header">Sample values (production SMTP)</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tbody>
                    <tr><th scope="row">Mode</th><td><code>smtp</code></td></tr>
                    <tr><th scope="row">From email</th><td><code><?= esc($samplesProd['from_email'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">SMTP host</th><td><code><?= esc($samplesProd['smtp_host'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">SMTP user</th><td><code><?= esc($samplesProd['smtp_user'] ?? '') ?></code></td></tr>
                    <tr><th scope="row">Port / crypto</th><td><code>587</code> / <code>tls</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">Notes</strong>
            Settings are stored in the database and used by registration, application,
            approval/rejection and password emails. File mode writes under
            <code>writable/mail/</code>.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
