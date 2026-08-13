<?php

namespace App\Libraries;

use App\Models\AuditLogModel;
use App\Models\NotificationLogModel;
use App\Models\NotificationTemplateModel;
use Config\Site;

/**
 * Password-related emails (uses shared MailTransport + notification_logs audit).
 * Subjects/bodies prefer admin-managed notification_templates when present.
 */
class PasswordMailer
{
    private MailTransport $mail;
    private NotificationTemplateModel $templates;
    private Site $site;

    public function __construct(?MailTransport $mail = null)
    {
        $this->mail      = $mail ?? new MailTransport();
        $this->templates = model(NotificationTemplateModel::class);
        $this->site      = config(Site::class);
    }

    /**
     * @return array{sent: bool, method: string, path?: string}
     */
    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl, ?int $userId = null): array
    {
        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'      => $toName,
            'email'     => $toEmail,
            'reset_url' => $resetUrl,
            'expires'   => '1 hour',
        ]);

        $fallbackSubject = 'Password reset — Senior Advocate Designation Portal';
        $fallbackBody    = view('emails/password_reset', [
            'name'     => $toName,
            'resetUrl' => $resetUrl,
            'expires'  => '1 hour',
            'site'     => $this->site,
        ]);

        [$subject, $body, $skip] = $this->resolveTemplate(
            'password_reset',
            $vars,
            $fallbackSubject,
            $fallbackBody
        );

        if ($skip) {
            $result = ['sent' => false, 'method' => 'disabled'];
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
    public function sendAccountUnlock(string $toEmail, string $toName, string $unlockUrl, ?int $userId = null): array
    {
        try {
            $this->templates->ensureDefaults();
        } catch (\Throwable $e) {
            // Fallback view still used if templates table is unavailable.
        }

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'       => $toName,
            'email'      => $toEmail,
            'unlock_url' => $unlockUrl,
            'expires'    => '1 hour',
        ]);

        $fallbackSubject = 'Unlock your account — Senior Advocate Designation Portal';
        $fallbackBody    = view('emails/notify_account_unlock', [
            'name'      => $toName,
            'email'     => $toEmail,
            'unlockUrl' => $unlockUrl,
            'expires'   => '1 hour',
            'site'      => $this->site,
        ]);

        [$subject, $body, $skip] = $this->resolveTemplate(
            'account_unlock',
            $vars,
            $fallbackSubject,
            $fallbackBody
        );

        if ($skip) {
            $result = ['sent' => false, 'method' => 'disabled'];
            $this->logNotification(
                'account_unlock',
                $toEmail,
                $toName,
                $subject,
                $body,
                $result,
                $userId
            );

            return $result;
        }

        $result = $this->mail->send($toEmail, $toName, $subject, $body);
        $this->logNotification(
            'account_unlock',
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
        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'  => $toName,
            'email' => $toEmail,
        ]);

        $fallbackSubject = 'Password changed — Senior Advocate Designation Portal';
        $fallbackBody    = view('emails/password_changed', [
            'name' => $toName,
            'site' => $this->site,
        ]);

        [$subject, $body, $skip] = $this->resolveTemplate(
            'password_changed',
            $vars,
            $fallbackSubject,
            $fallbackBody
        );

        if ($skip) {
            $result = ['sent' => false, 'method' => 'disabled'];
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
     * @param array<string, mixed> $vars
     *
     * @return array{0:string,1:string,2:bool} subject, bodyHtml, skip
     */
    private function resolveTemplate(
        string $event,
        array $vars,
        string $fallbackSubject,
        string $fallbackBody
    ): array {
        try {
            $resolved = $this->templates->resolveForSend($event);
            if ($resolved === null) {
                return [$fallbackSubject, $fallbackBody, false];
            }

            if (! empty($resolved['_inactive']) || empty($resolved['email_enabled'])) {
                $plainVars = $this->templates->plainVars($vars);
                $subject   = $this->templates->render($resolved['email_subject'] ?: $fallbackSubject, $plainVars);

                return [$subject, '', true];
            }

            $htmlVars  = $this->templates->escapeVars($vars);
            $plainVars = $this->templates->plainVars($vars);
            $subject   = $this->templates->render($resolved['email_subject'], $plainVars);
            $inner     = $this->templates->render($resolved['email_body'], $htmlVars);
            $body      = $this->templates->wrapEmailHtml($inner, $subject);

            return [$subject, $body, false];
        } catch (\Throwable $e) {
            log_message('error', "Password mail template resolve failed [{$event}]: " . $e->getMessage());

            return [$fallbackSubject, $fallbackBody, false];
        }
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
                'email_body'      => $body !== '' ? $body : null,
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
                    'email_status'        => ! empty($emailResult['sent']) ? 'sent' : (
                        ($emailResult['method'] ?? '') === 'disabled' ? 'skipped' : 'failed'
                    ),
                    'email_method' => $emailResult['method'] ?? null,
                ],
                'notification',
                $logId
            );
        } catch (\Throwable $e) {
            log_message('error', 'Password mail notification log failed: ' . $e->getMessage());
        }
    }
}
