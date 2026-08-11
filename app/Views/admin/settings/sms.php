<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h1 class="page-title">SMS configuration</h1>
        <p class="page-subtitle">Outbound SMS gateway for portal notifications</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('admin/settings/email') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-envelope me-1"></i> Email settings
        </a>
        <a href="<?= base_url('admin/notification-templates') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-envelope-paper me-1"></i> Email / SMS templates
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-mhc">
            <div class="card-header">SMS gateway</div>
            <div class="card-body">
                <?= form_open('admin/settings/sms') ?>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="enabled" value="1" id="smsEnabled"
                        <?= ! empty($settings['enabled']) && $settings['enabled'] !== '0' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="smsEnabled">Enable SMS sending</label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="provider">Provider mode</label>
                        <select name="provider" id="provider" class="form-select">
                            <option value="log" <?= old('provider', $settings['provider'] ?? '') === 'log' ? 'selected' : '' ?>>
                                Log only (writable/sms)
                            </option>
                            <option value="http" <?= old('provider', $settings['provider'] ?? '') === 'http' ? 'selected' : '' ?>>
                                HTTP API gateway
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sender_id">Sender ID</label>
                        <input type="text" name="sender_id" id="sender_id" class="form-control" maxlength="20"
                               value="<?= esc(old('sender_id', $settings['sender_id'] ?? 'MHCSSA')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="api_url">API URL</label>
                        <input type="url" name="api_url" id="api_url" class="form-control"
                               value="<?= esc(old('api_url', $settings['api_url'] ?? '')) ?>"
                               placeholder="https://gateway.example.com/send">
                        <div class="form-text">POST JSON: <code>to</code>, <code>message</code>, <code>sender</code>, <code>api_key</code></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="api_key">API key</label>
                        <input type="password" name="api_key" id="api_key" class="form-control"
                               value="" autocomplete="new-password"
                               placeholder="<?= ! empty($settings['api_key']) ? '•••••••• (leave blank to keep)' : '' ?>">
                        <div class="form-text">Leave blank to keep the existing API key.</div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-mhc">
                        <i class="bi bi-save me-1"></i> Save SMS settings
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-mhc mb-3">
            <div class="card-header">Send test SMS</div>
            <div class="card-body">
                <?= form_open('admin/settings/sms/test') ?>
                <div class="mb-3">
                    <label class="form-label" for="test_mobile">Mobile number</label>
                    <input type="text" name="test_mobile" id="test_mobile" class="form-control" required
                           maxlength="15" placeholder="10-digit mobile">
                </div>
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-chat-dots me-1"></i> Send test
                </button>
                <?= form_close() ?>
            </div>
        </div>
        <div class="warning-box mb-0">
            <strong class="d-block mb-1">Notes</strong>
            With provider “Log only”, messages are written under
            <code>writable/sms/</code>. Use HTTP mode for production gateways.
        </div>
    </div>
</div>

<?= $this->endSection() ?>
