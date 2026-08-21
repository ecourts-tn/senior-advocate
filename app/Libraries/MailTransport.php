<?php

namespace App\Libraries;

use App\Models\SystemSettingModel;

/**
 * Shared email sender. Reads admin DB settings (falls back to env defaults).
 */
class MailTransport
{
    /**
     * @return array{sent: bool, method: string, path?: string, error?: string}
     */
    public function send(string $toEmail, string $toName, string $subject, string $bodyHtml): array
    {
        $cfg = $this->config();

        if (empty($cfg['enabled']) || $cfg['enabled'] === '0' || $cfg['enabled'] === false) {
            return $this->writeFile($toEmail, $toName, $subject, $bodyHtml, 'disabled');
        }

        $fromEmail = (string) ($cfg['from_email'] ?? 'sradvsec.mhc@tn.gov.in');
        $fromName  = (string) ($cfg['from_name'] ?? 'Madras High Court - SSA Portal');
        $protocol  = strtolower((string) ($cfg['protocol'] ?? 'file'));
        $smtpHost  = trim((string) ($cfg['smtp_host'] ?? ''));

        if ($protocol === 'smtp' && $smtpHost !== '') {
            try {
                $email = \Config\Services::email();
                $email->initialize([
                    'protocol'   => 'smtp',
                    'SMTPHost'   => $smtpHost,
                    'SMTPUser'   => (string) ($cfg['smtp_user'] ?? 'sradvsec.mhc'),
                    'SMTPPass'   => (string) ($cfg['smtp_pass'] ?? 'MufasaSimba@*2026'),
                    'SMTPPort'   => (int) ($cfg['smtp_port'] ?? 465),
                    'SMTPCrypto' => (string) ($cfg['smtp_crypto'] ?? 'ssl'),
                    'mailType'   => 'html',
                    'charset'    => 'UTF-8',
                    'wordWrap'   => true,
                ]);
                $email->setFrom($fromEmail, $fromName);
                $email->setTo($toEmail);
                $email->setSubject($subject);
                $email->setMessage($bodyHtml);
                if ($email->send(false)) {
                    return ['sent' => true, 'method' => 'smtp'];
                }
                $debug = $email->printDebugger(['headers']);
                log_message('error', 'Email SMTP failed: ' . $debug);

                return $this->writeFile($toEmail, $toName, $subject, $bodyHtml, 'smtp_fallback', $debug);
            } catch (\Throwable $e) {
                log_message('error', 'Email SMTP exception: ' . $e->getMessage());

                return $this->writeFile($toEmail, $toName, $subject, $bodyHtml, 'smtp_error', $e->getMessage());
            }
        }

        return $this->writeFile($toEmail, $toName, $subject, $bodyHtml, 'file');
    }

    /**
     * @return array<string, string|null>
     */
    private function config(): array
    {
        try {
            $model = model(SystemSettingModel::class);
            $model->ensureDefaults();

            return $model->getGroup('email');
        } catch (\Throwable $e) {
            return [
                'enabled'    => '1',
                'from_email' => (string) env('email.fromEmail', 'noreply@hcmadras.tn.gov.in'),
                'from_name'  => (string) env('email.fromName', 'Madras High Court SSA Portal'),
                'protocol'   => (string) env('email.SMTPHost', '') !== '' ? 'smtp' : 'file',
                'smtp_host'  => (string) env('email.SMTPHost', ''),
                'smtp_user'  => (string) env('email.SMTPUser', ''),
                'smtp_pass'  => (string) env('email.SMTPPass', ''),
                'smtp_port'  => (string) env('email.SMTPPort', '587'),
                'smtp_crypto'=> (string) env('email.SMTPCrypto', 'tls'),
            ];
        }
    }

    /**
     * @return array{sent: bool, method: string, path: string, error?: string}
     */
    private function writeFile(
        string $toEmail,
        string $toName,
        string $subject,
        string $bodyHtml,
        string $method,
        ?string $error = null
    ): array {
        $dir = WRITEPATH . 'mail';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $toEmail) . '.html';
        $path     = $dir . DIRECTORY_SEPARATOR . $filename;
        $content  = "To: {$toName} <{$toEmail}>\nSubject: {$subject}\nDate: " . date('c')
            . "\nMethod: {$method}\n"
            . ($error ? "Error: {$error}\n" : '')
            . "\n" . $bodyHtml;
        file_put_contents($path, $content);
        log_message('info', "Email saved to {$path} ({$method})");

        $out = ['sent' => true, 'method' => $method, 'path' => $path];
        if ($error) {
            $out['error'] = $error;
        }

        return $out;
    }
}
