<?php

namespace App\Libraries;

use App\Models\ApplicationModel;
use App\Models\AuditLogModel;
use App\Models\NotificationLogModel;
use Config\Site;

/**
 * Email + SMS notifications for key portal events.
 * Every attempt is stored in notification_logs and summarised in audit_logs.
 */
class NotificationService
{
    private MailTransport $mail;
    private SmsService $sms;
    private Site $site;

    public function __construct(?MailTransport $mail = null, ?SmsService $sms = null)
    {
        $this->mail = $mail ?? new MailTransport();
        $this->sms  = $sms ?? new SmsService();
        $this->site = config(Site::class);
    }

    /**
     * After successful advocate registration.
     *
     * @param array{id?:int,name:string,email:string,mobile?:string|null} $user
     */
    public function registration(array $user): void
    {
        $name   = $user['name'] ?? 'Advocate';
        $email  = $user['email'] ?? '';
        $mobile = $user['mobile'] ?? '';
        $login  = base_url('login');

        $subject = 'Registration successful — ' . $this->site->portalName;
        $body    = view('emails/notify_registration', [
            'name'     => $name,
            'email'    => $email,
            'loginUrl' => $login,
            'site'     => $this->site,
        ]);

        $sms = "MHC SAD Portal: Registration successful for {$name}. Login at {$login} to start your application.";

        $this->dispatch(
            'registration',
            $email,
            $name,
            $mobile,
            $subject,
            $body,
            $sms,
            (int) ($user['id'] ?? 0),
            0
        );
    }

    /**
     * After application is submitted.
     *
     * @param array<string,mixed> $app
     */
    public function applicationSubmitted(array $app, ?array $user = null): void
    {
        [$name, $email, $mobile] = $this->recipientFromApp($app, $user);
        $appNo = $app['application_no'] ?? ('#' . ($app['id'] ?? ''));
        $url   = base_url('applicant/application/view/' . (int) ($app['id'] ?? 0));

        $subject = 'Application submitted — ' . $appNo;
        $body    = view('emails/notify_application_submitted', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'submittedAt'   => $app['submitted_at'] ?? date('Y-m-d H:i:s'),
            'viewUrl'       => $url,
            'site'          => $this->site,
        ]);

        $sms = "MHC SAD Portal: Application {$appNo} submitted successfully. Keep your Application No. for reference.";

        $this->dispatch(
            'application_submitted',
            $email,
            $name,
            $mobile,
            $subject,
            $body,
            $sms,
            (int) ($user['id'] ?? $app['user_id'] ?? 0),
            (int) ($app['id'] ?? 0)
        );
    }

    /**
     * When application is approved.
     *
     * @param array<string,mixed> $app
     */
    public function applicationApproved(array $app, ?array $user = null, string $remarks = ''): void
    {
        [$name, $email, $mobile] = $this->recipientFromApp($app, $user);
        $appNo = $app['application_no'] ?? ('#' . ($app['id'] ?? ''));

        $subject = 'Application accepted — ' . $appNo;
        $body    = view('emails/notify_application_decision', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'decision'      => 'approved',
            'decisionLabel' => 'Accepted',
            'remarks'       => $remarks,
            'site'          => $this->site,
        ]);

        $sms = "MHC SAD Portal: Application {$appNo} has been ACCEPTED."
            . ($remarks !== '' ? ' Remarks: ' . $this->truncate($remarks, 80) : '');

        $this->dispatch(
            'application_approved',
            $email,
            $name,
            $mobile,
            $subject,
            $body,
            $sms,
            (int) ($user['id'] ?? $app['user_id'] ?? 0),
            (int) ($app['id'] ?? 0)
        );
    }

    /**
     * When application is rejected.
     *
     * @param array<string,mixed> $app
     */
    public function applicationRejected(array $app, ?array $user = null, string $remarks = ''): void
    {
        [$name, $email, $mobile] = $this->recipientFromApp($app, $user);
        $appNo = $app['application_no'] ?? ('#' . ($app['id'] ?? ''));

        $subject = 'Application rejected — ' . $appNo;
        $body    = view('emails/notify_application_decision', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'decision'      => 'rejected',
            'decisionLabel' => 'Rejected',
            'remarks'       => $remarks,
            'site'          => $this->site,
        ]);

        $sms = "MHC SAD Portal: Application {$appNo} has been REJECTED."
            . ($remarks !== '' ? ' Remarks: ' . $this->truncate($remarks, 80) : '');

        $this->dispatch(
            'application_rejected',
            $email,
            $name,
            $mobile,
            $subject,
            $body,
            $sms,
            (int) ($user['id'] ?? $app['user_id'] ?? 0),
            (int) ($app['id'] ?? 0)
        );
    }

    /**
     * When application is returned for correction by the reviewer.
     *
     * @param array<string,mixed> $app
     */
    public function applicationReturned(array $app, ?array $user = null, string $remarks = ''): void
    {
        [$name, $email, $mobile] = $this->recipientFromApp($app, $user);
        $appNo = $app['application_no'] ?? ('#' . ($app['id'] ?? ''));
        $url   = base_url('applicant/application/view/' . (int) ($app['id'] ?? 0));

        $subject = 'Application returned for correction — ' . $appNo;
        $body    = view('emails/notify_application_returned', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'remarks'       => $remarks,
            'viewUrl'       => $url,
            'site'          => $this->site,
        ]);

        $sms = "MHC SAD Portal: Application {$appNo} returned for correction. Login to update and resubmit."
            . ($remarks !== '' ? ' Remarks: ' . $this->truncate($remarks, 60) : '');

        $this->dispatch(
            'application_returned',
            $email,
            $name,
            $mobile,
            $subject,
            $body,
            $sms,
            (int) ($user['id'] ?? $app['user_id'] ?? 0),
            (int) ($app['id'] ?? 0)
        );
    }

    /**
     * Notify on approved/rejected based on status string.
     *
     * @param array<string,mixed> $app
     */
    public function applicationStatus(array $app, string $status, ?array $user = null, string $remarks = ''): void
    {
        if ($status === ApplicationModel::STATUS_APPROVED) {
            $this->applicationApproved($app, $user, $remarks);
        } elseif ($status === ApplicationModel::STATUS_REJECTED) {
            $this->applicationRejected($app, $user, $remarks);
        } elseif ($status === ApplicationModel::STATUS_RETURNED) {
            $this->applicationReturned($app, $user, $remarks);
        }
    }

    /**
     * @param array<string,mixed>      $app
     * @param array<string,mixed>|null $user
     *
     * @return array{0:string,1:string,2:string}
     */
    private function recipientFromApp(array $app, ?array $user): array
    {
        $name   = trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''));
        $name   = $name !== '' ? $name : ($user['name'] ?? 'Advocate');
        $email  = $app['email'] ?? ($user['email'] ?? '');
        $mobile = $app['mobile'] ?? ($user['mobile'] ?? '');

        return [$name, (string) $email, (string) $mobile];
    }

    private function truncate(string $text, int $len): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if (mb_strlen($text) <= $len) {
            return $text;
        }

        return mb_substr($text, 0, $len - 1) . '…';
    }

    private function dispatch(
        string $event,
        string $email,
        string $name,
        string $mobile,
        string $subject,
        string $bodyHtml,
        string $smsText,
        int $userId = 0,
        int $applicationId = 0
    ): void {
        $emailResult = null;
        $smsResult   = null;

        try {
            if ($email !== '') {
                $emailResult = $this->mail->send($email, $name, $subject, $bodyHtml);
            }
        } catch (\Throwable $e) {
            log_message('error', "Notification email failed [{$event}]: " . $e->getMessage());
            $emailResult = ['sent' => false, 'method' => 'error', 'error' => $e->getMessage()];
        }

        try {
            if ($mobile !== '') {
                $smsResult = $this->sms->send($mobile, $smsText);
            }
        } catch (\Throwable $e) {
            log_message('error', "Notification SMS failed [{$event}]: " . $e->getMessage());
            $smsResult = ['sent' => false, 'method' => 'error', 'error' => $e->getMessage()];
        }

        $notificationLogId = null;

        try {
            $notificationLogId = model(NotificationLogModel::class)->record([
                'event'            => $event,
                'user_id'          => $userId > 0 ? $userId : null,
                'application_id'   => $applicationId > 0 ? $applicationId : null,
                'recipient_name'   => $name,
                'recipient_email'  => $email !== '' ? $email : null,
                'recipient_mobile' => $mobile !== '' ? $mobile : null,
                'email_subject'    => $subject,
                'email_body'       => $bodyHtml,
                'email_result'     => $emailResult,
                'sms_body'         => $smsText,
                'sms_result'       => $smsResult,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Notification DB log failed: ' . $e->getMessage());
        }

        // Cross-reference in general audit trail
        try {
            model(AuditLogModel::class)->log(
                'notification_' . $event,
                $userId > 0 ? $userId : null,
                $applicationId > 0 ? $applicationId : null,
                [
                    'notification_log_id' => $notificationLogId,
                    'recipient_email'     => $email,
                    'recipient_mobile'    => $mobile,
                    'email_status'        => $emailResult
                        ? (! empty($emailResult['sent']) ? 'sent' : 'failed')
                        : ($email === '' ? 'skipped' : 'failed'),
                    'sms_status' => $smsResult
                        ? (! empty($smsResult['sent']) ? 'sent' : 'failed')
                        : ($mobile === '' ? 'skipped' : 'failed'),
                    'email_method' => $emailResult['method'] ?? null,
                    'sms_method'   => $smsResult['method'] ?? null,
                ],
                'notification',
                $notificationLogId
            );
        } catch (\Throwable $e) {
            log_message('error', 'Notification audit log failed: ' . $e->getMessage());
        }
    }
}
