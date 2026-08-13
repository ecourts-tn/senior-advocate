<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Site;

/**
 * Admin-managed email/SMS notification templates (subjects + bodies + channel toggles).
 */
class NotificationTemplateModel extends Model
{
    protected $table            = 'notification_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_key',
        'name',
        'description',
        'email_enabled',
        'sms_enabled',
        'email_subject',
        'email_body',
        'sms_body',
        'is_active',
        'updated_by',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** @var array<string, string> */
    public const EVENTS = [
        'registration'           => 'Advocate registration',
        'email_verification'     => 'Email verification link',
        'application_submitted'  => 'Application submitted',
        'application_accepted'   => 'Application accepted',
        'application_rejected'   => 'Application rejected',
        'application_returned'   => 'Application returned for correction',
        'password_reset'         => 'Password reset link',
        'password_changed'       => 'Password changed notice',
        'account_unlock'         => 'Account unlock link',
    ];

    /**
     * Default templates matching current portal copy.
     *
     * @return array<string, array{
     *   name: string,
     *   description: string,
     *   email_enabled: bool,
     *   sms_enabled: bool,
     *   email_subject: string,
     *   email_body: string,
     *   sms_body: string|null
     * }>
     */
    public static function defaults(): array
    {
        return [
            'registration' => [
                'name'          => self::EVENTS['registration'],
                'description'   => 'Optional welcome after registration (verification email is primary).',
                'email_enabled' => true,
                'sms_enabled'   => true,
                'email_subject' => 'Registration successful — {{portal_name}}',
                'email_body'    => <<<'HTML'
<h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Registration successful</h1>
<p>Dear {{name}},</p>
<p>
    Your account has been created on the
    <strong>{{portal_name}}</strong> portal of the {{organisation}}.
</p>
<p>Registered email: <strong>{{email}}</strong></p>
<p>
    Please verify your email using the verification link sent to you, then sign in to begin the application.
</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{login_url}}"
       style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
        Sign in to portal
    </a>
</p>
<p style="font-size:0.9rem;color:#6b6558;">
    Please read the Instructions carefully before submitting. Errors cannot be rectified after submission.
</p>
HTML,
                'sms_body' => 'MHC SSA Portal: Registration successful for {{name}}. Verify your email, then login at {{login_url}}.',
            ],
            'email_verification' => [
                'name'          => self::EVENTS['email_verification'],
                'description'   => 'Sent on registration and when a user requests a new verification link. Required before login.',
                'email_enabled' => true,
                'sms_enabled'   => true,
                'email_subject' => 'Verify your email — {{portal_name}}',
                'email_body'    => <<<'HTML'
<h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Verify your email address</h1>
<p>Dear {{name}},</p>
<p>
    Your account has been created on the
    <strong>{{portal_name}}</strong> portal of the {{organisation}}.
</p>
<p>Registered email: <strong>{{email}}</strong></p>
<p>
    Please verify your email address to activate your account. You cannot sign in until verification is complete.
    This link is valid for <strong>{{expires}}</strong>.
</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{verify_url}}"
       style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
        Verify email address
    </a>
</p>
<p style="font-size:0.9rem;color:#6b6558;">
    If the button does not work, copy and paste this URL into your browser:<br>
    <a href="{{verify_url}}" style="color:#1a3558;word-break:break-all;">{{verify_url}}</a>
</p>
HTML,
                'sms_body' => 'MHC SSA Portal: Verify your email to activate your account. Open the verification link sent to your registered email.',
            ],
            'application_submitted' => [
                'name'          => self::EVENTS['application_submitted'],
                'description'   => 'Sent when an applicant submits an application.',
                'email_enabled' => true,
                'sms_enabled'   => true,
                'email_subject' => 'Application submitted — {{application_no}}',
                'email_body'    => <<<'HTML'
<h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Application submitted</h1>
<p>Dear {{name}},</p>
<p>
    Your Application-cum-Consent Letter for designation as Senior Advocate has been
    <strong>submitted successfully</strong>.
</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:0.95rem;">
    <tr>
        <td style="padding:8px;border:1px solid #d9d2c5;background:#faf8f3;font-weight:600;width:40%;">Application No.</td>
        <td style="padding:8px;border:1px solid #d9d2c5;">{{application_no}}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #d9d2c5;background:#faf8f3;font-weight:600;">Submitted at</td>
        <td style="padding:8px;border:1px solid #d9d2c5;">{{submitted_at}}</td>
    </tr>
</table>
<p>
    Please keep this Application Number for future reference. Also submit the prescribed paper book
    to the Permanent Secretariat as per the Instructions.
</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{view_url}}"
       style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
        View application
    </a>
</p>
HTML,
                'sms_body' => 'MHC SSA Portal: Application {{application_no}} submitted successfully. Keep your Application No. for reference.',
            ],
            'application_accepted' => [
                'name'          => self::EVENTS['application_accepted'],
                'description'   => 'Sent when an application is accepted by admin.',
                'email_enabled' => true,
                'sms_enabled'   => true,
                'email_subject' => 'Application accepted — {{application_no}}',
                'email_body'    => <<<'HTML'
<div style="background:#1b6b3a;color:#fff;padding:10px 14px;border-radius:4px;font-weight:600;margin-bottom:16px;">
    Application {{decision_label}}
</div>
<p>Dear {{name}},</p>
<p>
    Your application <strong>{{application_no}}</strong>
    for designation as Senior Advocate has been
    <strong>{{decision_label_lower}}</strong>
    by the High Court of Madras.
</p>
<p style="margin:16px 0 8px;font-weight:600;">Remarks</p>
<div style="background:#faf8f3;border:1px solid #d9d2c5;border-radius:4px;padding:12px;white-space:pre-wrap;">{{remarks}}</div>
HTML,
                'sms_body' => 'MHC SSA Portal: Application {{application_no}} has been ACCEPTED.{{remarks_sms}}',
            ],
            'application_rejected' => [
                'name'          => self::EVENTS['application_rejected'],
                'description'   => 'Sent when an application is rejected by admin.',
                'email_enabled' => true,
                'sms_enabled'   => true,
                'email_subject' => 'Application rejected — {{application_no}}',
                'email_body'    => <<<'HTML'
<div style="background:#9b1c1c;color:#fff;padding:10px 14px;border-radius:4px;font-weight:600;margin-bottom:16px;">
    Application {{decision_label}}
</div>
<p>Dear {{name}},</p>
<p>
    Your application <strong>{{application_no}}</strong>
    for designation as Senior Advocate has been
    <strong>{{decision_label_lower}}</strong>
    by the High Court of Madras.
</p>
<p style="margin:16px 0 8px;font-weight:600;">Remarks</p>
<div style="background:#faf8f3;border:1px solid #d9d2c5;border-radius:4px;padding:12px;white-space:pre-wrap;">{{remarks}}</div>
HTML,
                'sms_body' => 'MHC SSA Portal: Application {{application_no}} has been REJECTED.{{remarks_sms}}',
            ],
            'application_returned' => [
                'name'          => self::EVENTS['application_returned'],
                'description'   => 'Sent when an application is returned for correction.',
                'email_enabled' => true,
                'sms_enabled'   => true,
                'email_subject' => 'Application returned for correction — {{application_no}}',
                'email_body'    => <<<'HTML'
<div style="background:#b45309;color:#fff;padding:10px 14px;border-radius:4px;font-weight:600;margin-bottom:16px;">
    Application returned for correction
</div>
<p>Dear {{name}},</p>
<p>
    Your application <strong>{{application_no}}</strong>
    for designation as Senior Advocate has been
    <strong>returned for correction</strong>
    by the reviewing officer of the High Court of Madras.
</p>
<p style="margin:16px 0 8px;font-weight:600;">Reviewer remarks</p>
<div style="background:#faf8f3;border:1px solid #d9d2c5;border-radius:4px;padding:12px;white-space:pre-wrap;">{{remarks}}</div>
<p style="margin-top:16px;">
    Please log in, correct the indicated particulars, and <strong>resubmit</strong> the application.
</p>
<p>
    <a href="{{view_url}}" style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:10px 16px;border-radius:4px;">
        Open application
    </a>
</p>
HTML,
                'sms_body' => 'MHC SSA Portal: Application {{application_no}} returned for correction. Login to update and resubmit.{{remarks_sms}}',
            ],
            'password_reset' => [
                'name'          => self::EVENTS['password_reset'],
                'description'   => 'Sent when a user requests a password reset link.',
                'email_enabled' => true,
                'sms_enabled'   => false,
                'email_subject' => 'Password reset — Senior Advocate Designation Portal',
                'email_body'    => <<<'HTML'
<h1 style="font-size:1.25rem;color:#0f2340;margin:0 0 12px;">Password reset request</h1>
<p>Dear {{name}},</p>
<p>
    We received a request to reset the password for your account on the
    <strong>{{portal_name}}</strong> ({{organisation}}).
</p>
<p>
    Click the button below to set a new password. This link is valid for
    <strong>{{expires}}</strong> and can be used only once.
</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{reset_url}}"
       style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
        Reset password
    </a>
</p>
<p style="font-size:0.9rem;color:#6b6558;">
    If the button does not work, copy and paste this URL into your browser:<br>
    <a href="{{reset_url}}" style="color:#1a3558;word-break:break-all;">{{reset_url}}</a>
</p>
<p style="font-size:0.9rem;color:#6b6558;">
    If you did not request a password reset, you can ignore this message. Your password will remain unchanged.
</p>
HTML,
                'sms_body' => null,
            ],
            'password_changed' => [
                'name'          => self::EVENTS['password_changed'],
                'description'   => 'Sent after a password is changed successfully.',
                'email_enabled' => true,
                'sms_enabled'   => false,
                'email_subject' => 'Password changed — Senior Advocate Designation Portal',
                'email_body'    => <<<'HTML'
<h1 style="font-size:1.25rem;color:#0f2340;margin:0 0 12px;">Password changed</h1>
<p>Dear {{name}},</p>
<p>
    The password for your account on the
    <strong>{{portal_name}}</strong> was changed successfully.
</p>
<p>
    If you did not make this change, please contact the
    <strong>Registrar (Administration), Madras High Court</strong> immediately
    and use the “Forgot password” option if you still have access to your registered email.
</p>
HTML,
                'sms_body' => null,
            ],
            'account_unlock' => [
                'name'          => self::EVENTS['account_unlock'],
                'description'   => 'Sent when an account is locked after repeated failed sign-in attempts.',
                'email_enabled' => true,
                'sms_enabled'   => false,
                'email_subject' => 'Unlock your account — Senior Advocate Designation Portal',
                'email_body'    => <<<'HTML'
<h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Your account is locked</h1>
<p>Dear {{name}},</p>
<p>
    Your account on the <strong>{{portal_name}}</strong> was locked after several unsuccessful sign-in attempts.
</p>
<p>Registered email: <strong>{{email}}</strong></p>
<p>
    Click the button below to unlock your account. This link is valid for
    <strong>{{expires}}</strong> and can be used only once.
</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{unlock_url}}"
       style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
        Unlock account
    </a>
</p>
<p style="font-size:0.9rem;color:#6b6558;">
    If the button does not work, copy and paste this URL into your browser:<br>
    <a href="{{unlock_url}}" style="color:#1a3558;word-break:break-all;">{{unlock_url}}</a>
</p>
<p style="font-size:0.9rem;color:#6b6558;">
    If you did not try to sign in, you can ignore this message or use Forgot password after unlocking.
</p>
HTML,
                'sms_body' => null,
            ],
        ];
    }

    /**
     * Ensure default rows exist for all known event keys.
     */
    public function ensureDefaults(): void
    {
        foreach (self::defaults() as $eventKey => $meta) {
            $exists = $this->where('event_key', $eventKey)->first();
            if ($exists) {
                continue;
            }
            $this->insert([
                'event_key'     => $eventKey,
                'name'          => $meta['name'],
                'description'   => $meta['description'],
                'email_enabled' => (bool) $meta['email_enabled'],
                'sms_enabled'   => (bool) $meta['sms_enabled'],
                'email_subject' => $meta['email_subject'],
                'email_body'    => $meta['email_body'],
                'sms_body'      => $meta['sms_body'],
                'is_active'     => true,
            ]);
        }
    }

    public function findByEvent(string $eventKey): ?array
    {
        $row = $this->where('event_key', $eventKey)->first();

        return $row ?: null;
    }

    /**
     * Site + common placeholder values.
     *
     * @return array<string, string>
     */
    public function baseVars(?Site $site = null): array
    {
        $site ??= config(Site::class);

        return [
            'portal_name'     => $site->portalName,
            'organisation'    => $site->organisation,
            'contact_email'   => $site->email,
            'contact_phone'   => $site->phone,
            'contact_address' => $site->address,
        ];
    }

    /**
     * Replace {{key}} placeholders. Values should already be escaped for HTML contexts.
     *
     * @param array<string, string|int|float|null> $vars
     */
    public function render(string $template, array $vars): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            static function (array $m) use ($vars): string {
                $key = $m[1];
                if (! array_key_exists($key, $vars) || $vars[$key] === null) {
                    return '';
                }

                return (string) $vars[$key];
            },
            $template
        );
    }

    /**
     * Escape scalar vars for safe HTML injection (admin template is trusted; vars are not).
     *
     * @param array<string, mixed> $vars
     *
     * @return array<string, string>
     */
    public function escapeVars(array $vars): array
    {
        $out = [];
        foreach ($vars as $k => $v) {
            if ($v === null) {
                $out[$k] = '';
                continue;
            }
            // URLs used in href should still be escaped as attribute-safe text
            $out[$k] = esc((string) $v);
        }

        return $out;
    }

    /**
     * Escape for plain-text SMS (no HTML entities needed beyond strip).
     *
     * @param array<string, mixed> $vars
     *
     * @return array<string, string>
     */
    public function plainVars(array $vars): array
    {
        $out = [];
        foreach ($vars as $k => $v) {
            if ($v === null) {
                $out[$k] = '';
                continue;
            }
            $out[$k] = trim(strip_tags((string) $v));
        }

        return $out;
    }

    /**
     * Wrap inner HTML in the portal email shell, or return full documents as-is.
     */
    public function wrapEmailHtml(string $body, string $title = ''): string
    {
        $trimmed = ltrim($body);
        if (stripos($trimmed, '<!DOCTYPE') === 0 || stripos($trimmed, '<html') === 0) {
            return $body;
        }

        $site    = config(Site::class);
        $title   = $title !== '' ? $title : $site->portalName;
        $org     = esc($site->organisation);
        $email   = esc($site->email);
        $phone   = esc($site->phone);
        $addr    = esc($site->address);
        $titleE  = esc($title);
        $footer  = <<<HTML
<p style="margin-top:16px;font-size:0.9rem;color:#6b6558;line-height:1.55;">
    For further queries, please contact the
    <strong>Registrar (Administration), Madras High Court</strong>.
</p>
<p style="font-size:0.85rem;color:#6b6558;line-height:1.5;margin:8px 0 0;">
    Email: <a href="mailto:{$email}" style="color:#1a3558;">{$email}</a><br>
    Phone: {$phone}<br>
    {$addr}
</p>
<hr style="border:none;border-top:1px solid #d9d2c5;margin:20px 0;">
<p style="font-size:0.8rem;color:#6b6558;margin:0;">
    {$org} — Registrar (Administration)
</p>
HTML;

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$titleE}</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.55;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
{$body}
{$footer}
</div>
</body>
</html>
HTML;
    }

    /**
     * Resolve a template for sending. Returns null if inactive or missing.
     *
     * @return array{
     *   email_enabled: bool,
     *   sms_enabled: bool,
     *   email_subject: string,
     *   email_body: string,
     *   sms_body: string
     * }|null
     */
    public function resolveForSend(string $eventKey): ?array
    {
        $row = $this->findByEvent($eventKey);
        if ($row === null) {
            return null;
        }

        $active = $row['is_active'] === true
            || $row['is_active'] === 1
            || $row['is_active'] === '1'
            || $row['is_active'] === 't';

        if (! $active) {
            return [
                'email_enabled' => false,
                'sms_enabled'   => false,
                'email_subject' => (string) ($row['email_subject'] ?? ''),
                'email_body'    => (string) ($row['email_body'] ?? ''),
                'sms_body'      => (string) ($row['sms_body'] ?? ''),
                '_inactive'     => true,
            ];
        }

        return [
            'email_enabled' => $this->isTruthy($row['email_enabled'] ?? false),
            'sms_enabled'   => $this->isTruthy($row['sms_enabled'] ?? false),
            'email_subject' => (string) ($row['email_subject'] ?? ''),
            'email_body'    => (string) ($row['email_body'] ?? ''),
            'sms_body'      => (string) ($row['sms_body'] ?? ''),
            '_inactive'     => false,
        ];
    }

    private function isTruthy(mixed $value): bool
    {
        return $value === true
            || $value === 1
            || $value === '1'
            || $value === 't'
            || $value === 'true';
    }

    /**
     * Build remarks_sms suffix used in SMS templates.
     */
    public static function remarksSms(string $remarks, int $len = 80): string
    {
        $remarks = trim(preg_replace('/\s+/', ' ', $remarks) ?? $remarks);
        if ($remarks === '') {
            return '';
        }
        if (mb_strlen($remarks) > $len) {
            $remarks = mb_substr($remarks, 0, $len - 1) . '…';
        }

        return ' Remarks: ' . $remarks;
    }

    /**
     * Placeholders shown in the admin form help panel.
     *
     * @return list<string>
     */
    public static function placeholderHelp(): array
    {
        return [
            '{{name}}',
            '{{email}}',
            '{{portal_name}}',
            '{{organisation}}',
            '{{login_url}}',
            '{{application_no}}',
            '{{view_url}}',
            '{{submitted_at}}',
            '{{remarks}}',
            '{{remarks_sms}}',
            '{{decision_label}}',
            '{{decision_label_lower}}',
            '{{reset_url}}',
            '{{unlock_url}}',
            '{{expires}}',
            '{{contact_email}}',
            '{{contact_phone}}',
            '{{contact_address}}',
        ];
    }
}
