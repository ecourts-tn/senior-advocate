<?php

namespace App\Libraries;

use App\Models\AuditLogModel;
use App\Models\NotificationLogModel;

/**
 * Password-related emails (uses shared MailTransport + notification_logs audit).
 */
class PasswordMailer
{
    private MailTransport $mail;

    public function __construct(?MailTransport $mail = null)
    {
        $this->mail = $mail ?? new MailTransport();
    }

    /**
     * @return array{sent: bool, method: string, path?: string}
     */
    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl, ?int $userId = null): array
    {
        $subject = 'Password reset — Senior Advocate Designation Portal';
        $body    = view('emails/password_reset', [
            'name'     => $toName,
            'resetUrl' => $resetUrl,
            'expires'  => '1 hour',
        ]);

        $result = $this->mail->send($toEmail, $toName, $subject, $body);
        $this->logNotification(
            'password_reset',
            $toEmail,
            $toName,
            $subject,
            $body,
            $result,
            $userId
        );

        return $result;
    }

    /**
     * @return array{sent: bool, method: string, path?: string}
     */
    public function sendPasswordChanged(string $toEmail, string $toName, ?int $userId = null): array
    {
        $subject = 'Password changed — Senior Advocate Designation Portal';
        $body    = view('emails/password_changed', [
            'name' => $toName,
        ]);

        $result = $this->mail->send($toEmail, $toName, $subject, $body);
        $this->logNotification(
            'password_changed',
            $toEmail,
            $toName,
            $subject,
            $body,
            $result,
            $userId
        );

        return $result;
    }

    /**
     * @param array{sent: bool, method: string, path?: string} $emailResult
     */
    private function logNotification(
        string $event,
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        array $emailResult,
        ?int $userId
    ): void {
        try {
            $logId = model(NotificationLogModel::class)->record([
                'event'           => $event,
                'user_id'         => $userId,
                'recipient_name'  => $toName,
                'recipient_email' => $toEmail,
                'email_subject'   => $subject,
                'email_body'      => $body,
                'email_result'    => $emailResult,
                'sms_body'        => null,
                'sms_result'      => null,
            ]);

            model(AuditLogModel::class)->log(
                'notification_' . $event,
                $userId,
                null,
                [
                    'notification_log_id' => $logId,
                    'recipient_email'     => $toEmail,
                    'email_status'        => ! empty($emailResult['sent']) ? 'sent' : 'failed',
                    'email_method'        => $emailResult['method'] ?? null,
                ],
                'notification',
                $logId
            );
        } catch (\Throwable $e) {
            log_message('error', 'Password mail notification log failed: ' . $e->getMessage());
        }
    }
}
