<?php

namespace App\Database\Seeds;

use App\Models\SystemSettingModel;
use CodeIgniter\Database\Seeder;

/**
 * Seeds sample Email / SMS configuration for local development and demos.
 * Safe defaults use file/log delivery so no real SMTP/SMS gateway is required.
 */
class SettingsSeeder extends Seeder
{
    public function run()
    {
        $model = model(SystemSettingModel::class);

        // Sample profile A — local / offline (recommended for this environment)
        $samples = [
            'email' => [
                'enabled'     => '1',
                'from_email'  => 'ibms.mhc@gmail.com',
                'from_name'   => 'Madras High Court — DSA Portal',
                'protocol'    => 'file', // writes under writable/mail/
                'smtp_host'   => 'smtp.gmail.com',
                'smtp_user'   => 'ibms.mhc@gmail.com',
                'smtp_pass'   => 'bpzcrxxhrzbtktdd',
                'smtp_port'   => '587',
                'smtp_crypto' => 'tls',
            ],
            'sms' => [
                'enabled'   => '1',
                'provider'  => 'log', // writes under writable/sms/
                'api_url'   => 'https://api.textlocal.in/send/',
                'api_key'   => 'your-textlocal-api-key',
                'sender_id' => 'MHCSSA',
            ],
        ];

        foreach ($samples as $group => $keys) {
            $secrets = [];
            foreach (SystemSettingModel::defaults()[$group] ?? [] as $k => $meta) {
                if (! empty($meta['secret'])) {
                    $secrets[$k] = true;
                }
            }

            foreach ($keys as $key => $value) {
                $row = $model->where('group_name', $group)
                    ->where('setting_key', $key)
                    ->first();

                $payload = [
                    'group_name'    => $group,
                    'setting_key'   => $key,
                    'setting_value' => $value,
                    'is_secret'     => ! empty($secrets[$key]),
                ];

                if ($row) {
                    // Do not overwrite existing non-empty secrets with sample placeholders
                    if (! empty($secrets[$key]) && $row['setting_value'] !== null && $row['setting_value'] !== '') {
                        continue;
                    }
                    $model->update($row['id'], $payload);
                } else {
                    $model->insert($payload);
                }
            }
        }

        SystemSettingModel::clearCache();
    }
}
