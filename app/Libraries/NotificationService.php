<?php

namespace App\Libraries;

use App\Models\ApplicationModel;
use App\Models\AuditLogModel;
use App\Models\NotificationLogModel;
use App\Models\NotificationTemplateModel;
use Config\Site;

/**
 * Email + SMS notifications for key portal events.
 * Content comes from admin-managed notification_templates (with view fallbacks).
 * Every attempt is stored in notification_logs and summarised in audit_logs.
 */
class NotificationService
{
    private MailTransport $mail;
    private SmsService $sms;
    private Site $site;
    private NotificationTemplateModel $templates;

    public function __construct(?MailTransport $mail = null, ?SmsService $sms = null)
    {
        $this->mail      = $mail ?? new MailTransport();
        $this->sms       = $sms ?? new SmsService();
        $this->site      = config(Site::class);
        $this->templates = model(NotificationTemplateModel::class);
    }

    /**
     * After successful advocate registration (legacy welcome; prefer emailVerification).
     *
     * @param array{id?:int,name:string,email:string,mobile?:string|null} $user
     */
    public function registration(array $user): void
    {
        $name   = $user['name'] ?? 'Advocate';
        $email  = $user['email'] ?? '';
        $mobile = $user['mobile'] ?? '';
        $login  = base_url('login');

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'      => $name,
            'email'     => $email,
            'login_url' => $login,
        ]);

        $fallbackSubject = 'Registration successful — ' . $this->site->portalName;
        $fallbackBody    = view('emails/notify_registration', [
            'name'      => $name,
            'email'     => $email,
            'loginUrl'  => $login,
            'verifyUrl' => '',
            'site'      => $this->site,
        ]);
        $fallbackSms = "MHC SSA Portal: Registration successful for {$name}. Login at {$login} to start your application.";

        $this->sendEvent(
            'registration',
            $vars,
            $email,
            $name,
            $mobile,
            $fallbackSubject,
            $fallbackBody,
            $fallbackSms,
            (int) ($user['id'] ?? 0),
            0
        );
    }

    /**
     * Send email verification link (registration + resend). User cannot log in until verified.
     *
     * @param array{id?:int,name:string,email:string,mobile?:string|null} $user
     */
    public function emailVerification(array $user, string $verifyUrl): void
    {
        try {
            $this->templates->ensureDefaults();
        } catch (\Throwable $e) {
            // Fallback view still used if templates table is unavailable.
        }

        $name   = $user['name'] ?? 'Advocate';
        $email  = $user['email'] ?? '';
        $mobile = $user['mobile'] ?? '';
        $login  = base_url('login');

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'       => $name,
            'email'      => $email,
            'login_url'  => $login,
            'verify_url' => $verifyUrl,
            'expires'    => '48 hours',
        ]);

        $fallbackSubject = 'Verify your email — ' . $this->site->portalName;
        $fallbackBody    = view('emails/notify_email_verification', [
            'name'      => $name,
            'email'     => $email,
            'verifyUrl' => $verifyUrl,
            'loginUrl'  => $login,
            'expires'   => '48 hours',
            'site'      => $this->site,
        ]);
        $fallbackSms = "MHC SSA Portal: Verify your email to activate your account. Open the link sent to your registered email.";

        $this->sendEvent(
            'email_verification',
            $vars,
            $email,
            $name,
            $mobile,
            $fallbackSubject,
            $fallbackBody,
            $fallbackSms,
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
        $submittedAt = $app['submitted_at'] ?? date('Y-m-d H:i:s');

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'           => $name,
            'application_no' => $appNo,
            'view_url'       => $url,
            'submitted_at'   => $submittedAt,
        ]);

        $fallbackSubject = 'Application submitted — ' . $appNo;
        $fallbackBody    = view('emails/notify_application_submitted', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'submittedAt'   => $submittedAt,
            'viewUrl'       => $url,
            'site'          => $this->site,
        ]);
        $fallbackSms = "MHC SSA Portal: Application {$appNo} submitted successfully. Keep your Application No. for reference.";

        $this->sendEvent(
            'application_submitted',
            $vars,
            $email,
            $name,
            $mobile,
            $fallbackSubject,
            $fallbackBody,
            $fallbackSms,
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

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'                  => $name,
            'application_no'        => $appNo,
            'remarks'               => $remarks,
            'remarks_sms'           => NotificationTemplateModel::remarksSms($remarks, 80),
            'decision_label'        => 'Accepted',
            'decision_label_lower'  => 'accepted',
        ]);

        $fallbackSubject = 'Application accepted — ' . $appNo;
        $fallbackBody    = view('emails/notify_application_decision', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'decision'      => 'approved',
            'decisionLabel' => 'Accepted',
            'remarks'       => $remarks,
            'site'          => $this->site,
        ]);
        $fallbackSms = "MHC SSA Portal: Application {$appNo} has been ACCEPTED."
            . ($remarks !== '' ? ' Remarks: ' . $this->truncate($remarks, 80) : '');

        $this->sendEvent(
            'application_approved',
            $vars,
            $email,
            $name,
            $mobile,
            $fallbackSubject,
            $fallbackBody,
            $fallbackSms,
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

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'                 => $name,
            'application_no'       => $appNo,
            'remarks'              => $remarks,
            'remarks_sms'          => NotificationTemplateModel::remarksSms($remarks, 80),
            'decision_label'       => 'Rejected',
            'decision_label_lower' => 'rejected',
        ]);

        $fallbackSubject = 'Application rejected — ' . $appNo;
        $fallbackBody    = view('emails/notify_application_decision', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'decision'      => 'rejected',
            'decisionLabel' => 'Rejected',
            'remarks'       => $remarks,
            'site'          => $this->site,
        ]);
        $fallbackSms = "MHC SSA Portal: Application {$appNo} has been REJECTED."
            . ($remarks !== '' ? ' Remarks: ' . $this->truncate($remarks, 80) : '');

        $this->sendEvent(
            'application_rejected',
            $vars,
            $email,
            $name,
            $mobile,
            $fallbackSubject,
            $fallbackBody,
            $fallbackSms,
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

        $vars = array_merge($this->templates->baseVars($this->site), [
            'name'           => $name,
            'application_no' => $appNo,
            'remarks'        => $remarks,
            'remarks_sms'    => NotificationTemplateModel::remarksSms($remarks, 60),
            'view_url'       => $url,
        ]);

        $fallbackSubject = 'Application returned for correction — ' . $appNo;
        $fallbackBody    = view('emails/notify_application_returned', [
            'name'          => $name,
            'applicationNo' => $appNo,
            'remarks'       => $remarks,
            'viewUrl'       => $url,
            'site'          => $this->site,
        ]);
        $fallbackSms = "MHC SSA Portal: Application {$appNo} returned for correction. Login to update and resubmit."
            . ($remarks !== '' ? ' Remarks: ' . $this->truncate($remarks, 60) : '');

        $this->sendEvent(
            'application_returned',
            $vars,
            $email,
            $name,
            $mobile,
            $fallbackSubject,
            $fallbackBody,
            $fallbackSms,
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
     * @param array<string,mixed> $vars
     */
    private function sendEvent(
        string $event,
        array $vars,
        string $email,
        string $name,
        string $mobile,
        string $fallbackSubject,
        string $fallbackBodyHtml,
        string $fallbackSms,
        int $userId = 0,
        int $applicationId = 0
    ): void {
        $subject  = $fallbackSubject;
        $bodyHtml = $fallbackBodyHtml;
        $smsText  = $fallbackSms;
        $sendEmail = $email !== '';
        $sendSms   = $mobile !== '';

        try {
            $resolved = $this->templates->resolveForSend($event);
            if ($resolved !== null) {
                if (! empty($resolved['_inactive'])) {
                    // Admin disabled this event entirely — log as skipped
                    $this->dispatch(
                        $event,
                        '',
                        $name,
                        '',
                        $subject,
                        '',
                        '',
                        $userId,
                        $applicationId
                    );

                    return;
                }

                $htmlVars  = $this->templates->escapeVars($vars);
                $plainVars = $this->templates->plainVars($vars);

                if (! empty($resolved['email_enabled']) && $email !== '') {
                    $subject  = $this->templates->render($resolved['email_subject'], $plainVars);
                    $inner    = $this->templates->render($resolved['email_body'], $htmlVars);
                    $bodyHtml = $this->templates->wrapEmailHtml($inner, $subject);
                    $sendEmail = true;
                } else {
                    $sendEmail = false;
                    $subject   = $this->templates->render($resolved['email_subject'] ?: $fallbackSubject, $plainVars);
                    $bodyHtml  = '';
                }

                if (! empty($resolved['sms_enabled']) && $mobile !== '' && ($resolved['sms_body'] ?? '') !== '') {
                    $smsText = $this->templates->render($resolved['sms_body'], $plainVars);
                    $sendSms = true;
                } else {
                    $sendSms = false;
                    $smsText = '';
                }
            }
        } catch (\Throwable $e) {
            log_message('error', "Notification template resolve failed [{$event}]: " . $e->getMessage());
            // Keep fallbacks
        }

        $this->dispatch(
            $event,
            $sendEmail ? $email : '',
            $name,
            $sendSms ? $mobile : '',
            $subject,
            $bodyHtml,
            $smsText,
            $userId,
            $applicationId
        );
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
            if ($email !== '' && $bodyHtml !== '') {
                $emailResult = $this->mail->send($email, $name, $subject, $bodyHtml);
            }
        } catch (\Throwable $e) {
            log_message('error', "Notification email failed [{$event}]: " . $e->getMessage());
            $emailResult = ['sent' => false, 'method' => 'error', 'error' => $e->getMessage()];
        }

        try {
            if ($mobile !== '' && $smsText !== '') {
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
                'email_body'       => $bodyHtml !== '' ? $bodyHtml : null,
                'email_result'     => $emailResult,
                'sms_body'         => $smsText !== '' ? $smsText : null,
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
                        : ($email === '' || $bodyHtml === '' ? 'skipped' : 'failed'),
                    'sms_status' => $smsResult
                        ? (! empty($smsResult['sent']) ? 'sent' : 'failed')
                        : ($mobile === '' || $smsText === '' ? 'skipped' : 'failed'),
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
