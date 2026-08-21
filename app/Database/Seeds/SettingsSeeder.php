<?php

namespace App\Database\Seeds;

use App\Models\SystemSettingModel;
use CodeIgniter\Database\Seeder;

/**
 * Seeds Email / SMS configuration. Email uses the MHC SMTP server.
 * Failed sends are not written to writable/mail or writable/sms.
 */
class SettingsSeeder extends Seeder
{
    public function run()
    {
        $model = model(SystemSettingModel::class);

        $samples = [
            'email' => [
                'enabled'     => '1',
                'from_email'  => 'sradvsec.mhc@tn.gov.in',
                'from_name'   => 'High Court of Madras — SSA Portal',
                'protocol'    => 'smtp',
                'smtp_host'   => 'mail2.tn.gov.in',
                'smtp_user'   => 'sradvsec.mhc',
                'smtp_pass'   => 'MufasaSimba@*2026',
                'smtp_port'   => '465',
                'smtp_crypto' => 'ssl',
            ],
            'sms' => [
                'enabled'   => '0',
                'provider'  => 'log',
                'api_url'   => '',
                'api_key'   => 'your-api-key',
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
                    // Keep existing SMS API keys; always apply the MHC SMTP password.
                    if ($group !== 'email' && ! empty($secrets[$key]) && $row['setting_value'] !== null && $row['setting_value'] !== '') {
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