<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

/**
 * Monitor and throttle authentication attempts.
 *
 * Failed logins are written to audit_logs so administrators can review
 * unauthorized access attempts against SSA dashboard credentials.
 */
class LoginSecurityService
{
    /** Look-back window for failed-attempt counting (seconds). */
    public const WINDOW_SECONDS = 900; // 15 minutes

    /** Max failed attempts per email within the window before temporary lockout. */
    public const MAX_PER_EMAIL = 5;

    /** Max failed attempts per IP within the window before temporary lockout. */
    public const MAX_PER_IP = 20;

    private AuditLogModel $audit;

    public function __construct(?AuditLogModel $audit = null)
    {
        $this->audit = $audit ?? model(AuditLogModel::class);
    }

    /**
     * Whether login is currently blocked for this email/IP.
     * Returns a user-facing message when blocked, or null when allowed.
     */
    public function lockoutMessage(string $email, ?string $ip = null): ?string
    {
        $email = strtolower(trim($email));
        $ip    = $ip ?? $this->clientIp();

        if ($email !== '' && $this->countRecentFailuresByEmail($email) >= self::MAX_PER_EMAIL) {
            return 'Too many failed sign-in attempts for this account. Please try again after 15 minutes, or use Forgot password / Resend verification.';
        }

        if ($ip !== '' && $this->countRecentFailuresByIp($ip) >= self::MAX_PER_IP) {
            return 'Too many failed sign-in attempts from this network. Please try again after 15 minutes.';
        }

        return null;
    }

    /**
     * Record a failed authentication attempt (never stores the password).
     *
     * @param string $reason invalid_credentials|inactive|unverified|rate_limited|captcha
     */
    public function recordFailure(
        string $email,
        string $reason,
        ?int $userId = null,
        array $extra = []
    ): void {
        $email = strtolower(trim($email));

        $this->audit->log(
            $reason === 'rate_limited' ? 'login_blocked' : 'login_failed',
            $userId,
            null,
            array_merge([
                'email'  => $email,
                'reason' => $reason,
            ], $extra),
            'auth',
            $userId
        );

        log_message(
            'warning',
            sprintf(
                'Login failure [%s] email=%s ip=%s user_id=%d',
                $reason,
                $email !== '' ? $email : '(empty)',
                $this->clientIp(),
                (int) ($userId ?? 0)
            )
        );
    }

    /**
     * Record a successful login (IP/UA already captured by AuditLogModel).
     */
    public function recordSuccess(int $userId, string $email, string $role): void
    {
        $this->audit->log('login', $userId, null, [
            'email' => strtolower(trim($email)),
            'role'  => $role,
        ], 'auth', $userId);
    }

    public function countRecentFailuresByEmail(string $email): int
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return 0;
        }

        // details JSON includes "email":"<address>"
        $needle = '"email":"' . $email . '"';

        return (int) $this->audit->builder()
            ->whereIn('action', ['login_failed', 'login_blocked'])
            ->where('created_at >=', $this->windowStart())
            ->like('details', $needle)
            ->countAllResults();
    }

    public function countRecentFailuresByIp(string $ip): int
    {
        $ip = trim($ip);
        if ($ip === '') {
            return 0;
        }

        return (int) $this->audit->builder()
            ->whereIn('action', ['login_failed', 'login_blocked'])
            ->where('created_at >=', $this->windowStart())
            ->where('ip_address', $ip)
            ->countAllResults();
    }

    /**
     * Recent unauthorized access attempts for admin monitoring.
     *
     * @return list<array<string, mixed>>
     */
    public function recentUnauthorizedAttempts(int $limit = 25): array
    {
        $limit = max(1, min(100, $limit));

        return $this->audit->builder()
            ->select('id, user_id, action, ip_address, user_agent, details, created_at')
            ->whereIn('action', ['login_failed', 'login_blocked'])
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Counts of failed/blocked logins in the last 24 hours (admin dashboard).
     *
     * @return array{failed:int,blocked:int,window_hours:int}
     */
    public function last24HourSummary(): array
    {
        $since = date('Y-m-d H:i:s', time() - 86400);

        $failed = (int) $this->audit->builder()
            ->where('action', 'login_failed')
            ->where('created_at >=', $since)
            ->countAllResults();

        $blocked = (int) $this->audit->builder()
            ->where('action', 'login_blocked')
            ->where('created_at >=', $since)
            ->countAllResults();

        return [
            'failed'       => $failed,
            'blocked'      => $blocked,
            'window_hours' => 24,
        ];
    }

    private function windowStart(): string
    {
        return date('Y-m-d H:i:s', time() - self::WINDOW_SECONDS);
    }

    private function clientIp(): string
    {
        try {
            return (string) service('request')->getIPAddress();
        } catch (\Throwable $e) {
            return '';
        }
    }
}
