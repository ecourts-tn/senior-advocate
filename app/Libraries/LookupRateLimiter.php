<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

/**
 * Rate-limit enrolment-number lookups that return advocate master data.
 * Uses audit_logs so attempts remain visible to administrators.
 */
class LookupRateLimiter
{
    /** Sliding window for counting attempts (seconds). */
    public const WINDOW_SECONDS = 900; // 15 minutes

    /** Max lookups per client IP within the window. */
    public const MAX_PER_IP = 10;

    /** Max lookups per browser session within the window. */
    public const MAX_PER_SESSION = 8;

    private const SESSION_KEY = 'ssa_lookup_attempts';

    private AuditLogModel $audit;

    public function __construct(?AuditLogModel $audit = null)
    {
        $this->audit = $audit ?? model(AuditLogModel::class);
    }

    /**
     * @return array{allowed: bool, message?: string, retry_after?: int}
     */
    public function check(?string $ip = null): array
    {
        $ip = $ip ?? $this->clientIp();

        if ($this->sessionCount() >= self::MAX_PER_SESSION) {
            return [
                'allowed'     => false,
                'message'     => 'Too many enrolment lookups from this session. Please wait 15 minutes and try again, or enter your details manually.',
                'retry_after' => self::WINDOW_SECONDS,
            ];
        }

        if ($ip !== '' && $this->ipCount($ip) >= self::MAX_PER_IP) {
            return [
                'allowed'     => false,
                'message'     => 'Too many enrolment lookups from this network. Please wait 15 minutes and try again, or enter your details manually.',
                'retry_after' => self::WINDOW_SECONDS,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Record a lookup attempt (success, not found, blocked, captcha failure, etc.).
     *
     * @param array<string, mixed> $details
     */
    public function record(string $outcome, string $enrolment = '', array $details = []): void
    {
        $this->bumpSessionCounter();

        $this->audit->log(
            'advocate_lookup',
            null,
            null,
            array_merge([
                'outcome'          => $outcome,
                'enrolment_number' => $enrolment !== '' ? $enrolment : null,
            ], $details),
            'auth',
            null
        );
    }

    private function sessionCount(): int
    {
        $this->pruneSession();
        $attempts = session()->get(self::SESSION_KEY);

        return is_array($attempts) ? count($attempts) : 0;
    }

    private function bumpSessionCounter(): void
    {
        $this->pruneSession();
        $attempts   = session()->get(self::SESSION_KEY);
        $attempts   = is_array($attempts) ? $attempts : [];
        $attempts[] = time();
        session()->set(self::SESSION_KEY, $attempts);
    }

    private function pruneSession(): void
    {
        $attempts = session()->get(self::SESSION_KEY);
        if (! is_array($attempts)) {
            session()->set(self::SESSION_KEY, []);

            return;
        }

        $cutoff   = time() - self::WINDOW_SECONDS;
        $attempts = array_values(array_filter(
            $attempts,
            static fn ($ts): bool => is_int($ts) && $ts >= $cutoff
        ));
        session()->set(self::SESSION_KEY, $attempts);
    }

    private function ipCount(string $ip): int
    {
        $since = date('Y-m-d H:i:s', time() - self::WINDOW_SECONDS);

        return (int) $this->audit->builder()
            ->where('action', 'advocate_lookup')
            ->where('created_at >=', $since)
            ->where('ip_address', $ip)
            ->countAllResults();
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
