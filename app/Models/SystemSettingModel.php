<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Key/value system settings (email, SMS, etc.) managed from admin.
 */
class SystemSettingModel extends Model
{
    protected $table            = 'system_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'group_name', 'setting_key', 'setting_value', 'is_secret',
        'updated_by', 'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** @var array<string, array<string, string|null>>|null */
    private static ?array $cache = null;

    /**
     * Default settings used when DB row is missing (also seeded).
     *
     * @return array<string, array<string, array{value: string, secret?: bool}>>
     */
    public static function defaults(): array
    {
        return [
            'email' => [
                'enabled'     => ['value' => '1'],
                'from_email'  => ['value' => (string) env('email.fromEmail', 'noreply@hcmadras.tn.gov.in')],
                'from_name'   => ['value' => (string) env('email.fromName', 'High Court of Madras — SAD Portal')],
                // Prefer file delivery until real SMTP is configured in admin
                'protocol'    => ['value' => (string) env('email.protocol', 'file')], // smtp|file
                'smtp_host'   => ['value' => (string) env('email.SMTPHost', 'smtp.gmail.com')],
                'smtp_user'   => ['value' => (string) env('email.SMTPUser', 'sad.portal@example.com')],
                'smtp_pass'   => ['value' => (string) env('email.SMTPPass', ''), 'secret' => true],
                'smtp_port'   => ['value' => (string) env('email.SMTPPort', '587')],
                'smtp_crypto' => ['value' => (string) env('email.SMTPCrypto', 'tls')], // tls|ssl|
            ],
            'sms' => [
                'enabled'   => ['value' => filter_var(env('sms.enabled', true), FILTER_VALIDATE_BOOLEAN) ? '1' : '0'],
                'provider'  => ['value' => (string) env('sms.provider', 'log')], // log|http
                'api_url'   => ['value' => (string) env('sms.apiUrl', 'https://api.textlocal.in/send/')],
                'api_key'   => ['value' => (string) env('sms.apiKey', ''), 'secret' => true],
                'sender_id' => ['value' => (string) env('sms.senderId', 'MHCSAD')],
            ],
        ];
    }

    /**
     * Ensure default rows exist for all known keys.
     */
    public function ensureDefaults(): void
    {
        foreach (self::defaults() as $group => $keys) {
            foreach ($keys as $key => $meta) {
                $exists = $this->where('group_name', $group)
                    ->where('setting_key', $key)
                    ->first();
                if ($exists) {
                    continue;
                }
                $this->insert([
                    'group_name'    => $group,
                    'setting_key'   => $key,
                    'setting_value' => $meta['value'],
                    'is_secret'     => ! empty($meta['secret']),
                ]);
            }
        }
        self::$cache = null;
    }

    public function get(string $group, string $key, ?string $default = null): ?string
    {
        $groupData = $this->getGroup($group);
        if (array_key_exists($key, $groupData)) {
            return $groupData[$key];
        }

        return $default;
    }

    /**
     * @return array<string, string|null>
     */
    public function getGroup(string $group): array
    {
        if (self::$cache === null) {
            $this->warmCache();
        }

        if (isset(self::$cache[$group])) {
            return self::$cache[$group];
        }

        // Fallback to defaults if table empty / unavailable
        $defaults = self::defaults()[$group] ?? [];
        $out      = [];
        foreach ($defaults as $k => $meta) {
            $out[$k] = $meta['value'];
        }

        return $out;
    }

    /**
     * @param array<string, string|null> $values
     */
    public function setGroup(string $group, array $values, ?int $updatedBy = null): void
    {
        $defaults = self::defaults()[$group] ?? [];

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, $defaults)) {
                continue;
            }

            // Keep existing secret if form sent blank password field
            if (! empty($defaults[$key]['secret']) && ($value === null || $value === '')) {
                continue;
            }

            $row = $this->where('group_name', $group)
                ->where('setting_key', $key)
                ->first();

            $payload = [
                'group_name'    => $group,
                'setting_key'   => $key,
                'setting_value' => $value,
                'is_secret'     => ! empty($defaults[$key]['secret']),
                'updated_by'    => $updatedBy,
            ];

            if ($row) {
                $this->update($row['id'], $payload);
            } else {
                $this->insert($payload);
            }
        }

        self::$cache = null;
    }

    public function bool(string $group, string $key, bool $default = false): bool
    {
        $val = $this->get($group, $key);
        if ($val === null || $val === '') {
            return $default;
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    public static function clearCache(): void
    {
        self::$cache = null;
    }

    private function warmCache(): void
    {
        self::$cache = [];
        try {
            $rows = $this->findAll();
            foreach ($rows as $row) {
                $g = $row['group_name'];
                $k = $row['setting_key'];
                self::$cache[$g][$k] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            self::$cache = [];
        }

        // Merge missing defaults
        foreach (self::defaults() as $group => $keys) {
            foreach ($keys as $key => $meta) {
                if (! isset(self::$cache[$group][$key])) {
                    self::$cache[$group][$key] = $meta['value'];
                }
            }
        }
    }
}
