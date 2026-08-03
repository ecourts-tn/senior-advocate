<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MailTransport;
use App\Libraries\SmsService;
use App\Models\AuditLogModel;
use App\Models\SystemSettingModel;

class SettingsController extends BaseController
{
    public function email()
    {
        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();

        return view('admin/settings/email', [
            'title'    => 'Email configuration',
            'settings' => $settings->getGroup('email'),
        ]);
    }

    public function saveEmail()
    {
        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();

        $rules = [
            'from_email'  => 'required|valid_email|max_length[255]',
            'from_name'   => 'required|max_length[255]',
            'protocol'    => 'required|in_list[smtp,file]',
            'smtp_host'   => 'permit_empty|max_length[255]',
            'smtp_user'   => 'permit_empty|max_length[255]',
            'smtp_pass'   => 'permit_empty|max_length[255]',
            'smtp_port'   => 'permit_empty|integer',
            'smtp_crypto' => 'permit_empty|in_list[tls,ssl,]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $enabled = $this->request->getPost('enabled') ? '1' : '0';
        $values  = [
            'enabled'     => $enabled,
            'from_email'  => trim((string) $this->request->getPost('from_email')),
            'from_name'   => trim((string) $this->request->getPost('from_name')),
            'protocol'    => (string) $this->request->getPost('protocol'),
            'smtp_host'   => trim((string) $this->request->getPost('smtp_host')),
            'smtp_user'   => trim((string) $this->request->getPost('smtp_user')),
            'smtp_pass'   => (string) $this->request->getPost('smtp_pass'),
            'smtp_port'   => (string) $this->request->getPost('smtp_port'),
            'smtp_crypto' => (string) $this->request->getPost('smtp_crypto'),
        ];

        $userId = (int) session()->get('user_id');
        $settings->setGroup('email', $values, $userId);

        model(AuditLogModel::class)->log('settings_email_updated', $userId, null, [
            'from_email' => $values['from_email'],
            'protocol'   => $values['protocol'],
            'smtp_host'  => $values['smtp_host'],
            'enabled'    => $values['enabled'],
        ]);

        return redirect()->to('/admin/settings/email')->with('success', 'Email configuration saved.');
    }

    public function sms()
    {
        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();

        return view('admin/settings/sms', [
            'title'    => 'SMS configuration',
            'settings' => $settings->getGroup('sms'),
        ]);
    }

    public function saveSms()
    {
        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();

        $rules = [
            'provider'  => 'required|in_list[log,http]',
            'api_url'   => 'permit_empty|max_length[500]',
            'api_key'   => 'permit_empty|max_length[500]',
            'sender_id' => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $values = [
            'enabled'   => $this->request->getPost('enabled') ? '1' : '0',
            'provider'  => (string) $this->request->getPost('provider'),
            'api_url'   => trim((string) $this->request->getPost('api_url')),
            'api_key'   => (string) $this->request->getPost('api_key'),
            'sender_id' => trim((string) $this->request->getPost('sender_id')),
        ];

        $userId = (int) session()->get('user_id');
        $settings->setGroup('sms', $values, $userId);

        model(AuditLogModel::class)->log('settings_sms_updated', $userId, null, [
            'enabled'  => $values['enabled'],
            'provider' => $values['provider'],
            'api_url'  => $values['api_url'],
            'sender_id'=> $values['sender_id'],
        ]);

        return redirect()->to('/admin/settings/sms')->with('success', 'SMS configuration saved.');
    }

    public function testEmail()
    {
        $to = trim((string) $this->request->getPost('test_email'));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Enter a valid test email address.');
        }

        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();
        $cfg = $settings->getGroup('email');

        $result = (new MailTransport())->send(
            $to,
            'Test recipient',
            'SAD Portal — test email',
            '<p>This is a test email from the Senior Advocate Designation Portal.</p>'
            . '<p>Protocol: ' . esc($cfg['protocol'] ?? '') . '<br>SMTP host: ' . esc($cfg['smtp_host'] ?? '') . '</p>'
            . '<p>Sent at ' . date('Y-m-d H:i:s') . '</p>'
        );

        model(AuditLogModel::class)->log('settings_email_test', (int) session()->get('user_id'), null, [
            'to'     => $to,
            'result' => $result,
        ]);

        if (! empty($result['sent'])) {
            $msg = 'Test email sent via ' . ($result['method'] ?? 'unknown') . '.';
            if (! empty($result['path'])) {
                $msg .= ' Saved to ' . $result['path'];
            }

            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', 'Test email failed.');
    }

    public function testSms()
    {
        $mobile = trim((string) $this->request->getPost('test_mobile'));
        if ($mobile === '') {
            return redirect()->back()->with('error', 'Enter a mobile number for the test SMS.');
        }

        $result = (new SmsService())->send(
            $mobile,
            'MHC SAD Portal test SMS at ' . date('Y-m-d H:i:s')
        );

        model(AuditLogModel::class)->log('settings_sms_test', (int) session()->get('user_id'), null, [
            'mobile' => $mobile,
            'result' => $result,
        ]);

        if (! empty($result['sent'])) {
            $msg = 'Test SMS processed via ' . ($result['method'] ?? 'unknown') . '.';
            if (! empty($result['path'])) {
                $msg .= ' Saved to ' . $result['path'];
            }
            if (! empty($result['response'])) {
                $msg .= ' Response: ' . $result['response'];
            }

            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', 'Test SMS failed: ' . ($result['response'] ?? 'unknown error'));
    }

    public function application()
    {
        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();

        return view('admin/settings/application', [
            'title'    => 'Application cycle & edit window',
            'settings' => $settings->getGroup('application'),
        ]);
    }

    public function saveApplication()
    {
        $settings = model(SystemSettingModel::class);
        $settings->ensureDefaults();

        $rules = [
            'cycle_year'          => 'required|integer|greater_than[1999]|less_than[2101]',
            'edit_window_from'    => 'permit_empty',
            'edit_window_to'      => 'permit_empty',
            'edit_window_message' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $from = trim((string) $this->request->getPost('edit_window_from'));
        $to   = trim((string) $this->request->getPost('edit_window_to'));

        // Normalise datetime-local values (YYYY-MM-DDTHH:MM) to SQL datetime
        $norm = static function (string $v): string {
            $v = trim($v);
            if ($v === '') {
                return '';
            }
            $v = str_replace('T', ' ', $v);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
                $v .= ':00';
            }

            return $v;
        };
        $from = $norm($from);
        $to   = $norm($to);

        if ($from !== '' && $to !== '' && strtotime($to) !== false && strtotime($from) !== false
            && strtotime($to) < strtotime($from)) {
            return redirect()->back()->withInput()
                ->with('error', 'Edit window end must be after the start.');
        }

        $values = [
            'cycle_year'          => (string) (int) $this->request->getPost('cycle_year'),
            'one_per_year'        => $this->request->getPost('one_per_year') ? '1' : '0',
            'edit_window_enabled' => $this->request->getPost('edit_window_enabled') ? '1' : '0',
            'edit_window_from'    => $from,
            'edit_window_to'      => $to,
            'edit_window_message' => trim((string) $this->request->getPost('edit_window_message')),
        ];

        $userId = (int) session()->get('user_id');
        $settings->setGroup('application', $values, $userId);

        model(AuditLogModel::class)->log('settings_application_updated', $userId, null, [
            'cycle_year'          => $values['cycle_year'],
            'one_per_year'        => $values['one_per_year'],
            'edit_window_enabled' => $values['edit_window_enabled'],
            'edit_window_from'    => $values['edit_window_from'],
            'edit_window_to'      => $values['edit_window_to'],
        ]);

        return redirect()->to('/admin/settings/application')
            ->with('success', 'Application cycle and edit window settings saved.');
    }
}
