<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'email',
        'mobile',
        'enrolment_number',
        'password_hash',
        'role',
        'is_active',
        'email_verified_at',
        'email_verification_token',
        'email_verification_sent_at',
        'last_login_at',
        'locked_at',
        'unlock_token',
        'unlock_token_sent_at',
        'unlocked_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[255]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
    ];

    /** Email verification link validity (48 hours). */
    public const EMAIL_VERIFY_TTL = 172800;

    /** Minimum seconds between verification email resends. */
    public const EMAIL_VERIFY_RESEND_COOLDOWN = 60;

    /** Account-unlock link validity (1 hour). */
    public const UNLOCK_TTL = 3600;

    /** Minimum seconds between unlock email resends. */
    public const UNLOCK_RESEND_COOLDOWN = 60;

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /**
     * Find by registered email or mobile (10-digit Indian number, with or without +91 / 0).
     */
    public function findByLogin(string $login): ?array
    {
        $login = trim($login);
        if ($login === '') {
            return null;
        }

        if (self::looksLikeEmail($login)) {
            return $this->findByEmail($login);
        }

        $byMobile = $this->findByMobile($login);
        if ($byMobile) {
            return $byMobile;
        }

        // Fallback: treat as email if it contains '@' but failed a strict parse.
        if (str_contains($login, '@')) {
            return $this->findByEmail($login);
        }

        return null;
    }

    public function findByMobile(string $mobile): ?array
    {
        $last10 = self::normaliseMobile($mobile);
        if (strlen($last10) < 10) {
            return null;
        }

        $sql = 'SELECT * FROM users'
            . ' WHERE deleted_at IS NULL'
            . " AND mobile IS NOT NULL AND BTRIM(mobile) <> ''"
            . " AND RIGHT(regexp_replace(mobile, '[^0-9]', '', 'g'), 10) = ?"
            . ' ORDER BY id ASC'
            . ' LIMIT 1';
        $row = $this->db->query($sql, [$last10])->getRowArray();

        return $row ?: null;
    }

    public static function looksLikeEmail(string $value): bool
    {
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Reduce a mobile string to the last 10 digits (strip +91 / leading 0).
     */
    public static function normaliseMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 13 && str_starts_with($digits, '091')) {
            $digits = substr($digits, 3);
        }
        if (strlen($digits) > 10) {
            $digits = substr($digits, -10);
        }

        return $digits;
    }

    public function findByEnrolment(string $enrolment): ?array
    {
        $enrolment = AdvocateDbModel::normaliseEnrolment($enrolment);
        if ($enrolment === '') {
            return null;
        }

        $exact = $this->where('enrolment_number', $enrolment)->first();
        if ($exact) {
            return $exact;
        }

        $key = AdvocateDbModel::parseNumberAndYear($enrolment);
        if ($key === null) {
            return null;
        }

        return $this->findByEnrolmentNumberAndYear($key['number'], $key['year']);
    }

    /**
     * Active user whose enrolment matches the given serial number and year.
     */
    public function findByEnrolmentNumberAndYear(string $number, string $year, ?int $exceptUserId = null): ?array
    {
        $number = ltrim($number, '0');
        if ($number === '') {
            $number = '0';
        }
        $year = trim($year);
        if ($number === '' || $year === '') {
            return null;
        }

        $builder = $this->builder();
        $builder->where('enrolment_number IS NOT NULL', null, false)
            ->where("enrolment_number <> ''", null, false);
        if ($exceptUserId !== null && $exceptUserId > 0) {
            $builder->where('id !=', $exceptUserId);
        }

        foreach ($builder->get()->getResultArray() as $row) {
            $parsed = AdvocateDbModel::parseNumberAndYear((string) ($row['enrolment_number'] ?? ''));
            if ($parsed !== null && $parsed['number'] === $number && $parsed['year'] === $year) {
                return $row;
            }
        }

        return null;
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isEmailVerified(array $user): bool
    {
        $verifiedAt = $user['email_verified_at'] ?? null;

        return $verifiedAt !== null && $verifiedAt !== '' && $verifiedAt !== '0000-00-00 00:00:00';
    }

    /**
     * Issue a new email verification token. Returns the plain token for the URL.
     */
    public function issueEmailVerificationToken(int $userId): string
    {
        $plain = bin2hex(random_bytes(32));

        $this->update($userId, [
            'email_verified_at'          => null,
            'email_verification_token'   => hash('sha256', $plain),
            'email_verification_sent_at' => date('Y-m-d H:i:s'),
        ]);

        return $plain;
    }

    /**
     * Whether a resend is allowed (cooldown after last send).
     */
    public function canResendEmailVerification(array $user): bool
    {
        $sentAt = $user['email_verification_sent_at'] ?? null;
        if ($sentAt === null || $sentAt === '') {
            return true;
        }

        return strtotime((string) $sentAt) <= (time() - self::EMAIL_VERIFY_RESEND_COOLDOWN);
    }

    /**
     * Verify token and mark the user verified. Returns the user row on success.
     *
     * @return array{ok:bool,user?:array,reason?:string}
     */
    public function consumeEmailVerificationToken(string $plainToken): array
    {
        $plainToken = trim($plainToken);
        if ($plainToken === '') {
            return ['ok' => false, 'reason' => 'invalid'];
        }

        $hash = hash('sha256', $plainToken);
        $user = $this->where('email_verification_token', $hash)->first();

        if (! $user) {
            return ['ok' => false, 'reason' => 'invalid'];
        }

        if ($this->isEmailVerified($user)) {
            return ['ok' => true, 'user' => $user, 'reason' => 'already'];
        }

        $sentAt = $user['email_verification_sent_at'] ?? null;
        if ($sentAt && strtotime((string) $sentAt) < (time() - self::EMAIL_VERIFY_TTL)) {
            return ['ok' => false, 'reason' => 'expired', 'user' => $user];
        }

        $this->update((int) $user['id'], [
            'email_verified_at'          => date('Y-m-d H:i:s'),
            'email_verification_token'   => null,
            'email_verification_sent_at' => null,
        ]);

        $user['email_verified_at']          = date('Y-m-d H:i:s');
        $user['email_verification_token']   = null;
        $user['email_verification_sent_at'] = null;

        return ['ok' => true, 'user' => $user];
    }

    /**
     * Whether this account is currently locked after failed sign-in attempts.
     */
    public function isAccountLocked(array $user): bool
    {
        $lockedAt = $user['locked_at'] ?? null;

        return $lockedAt !== null && $lockedAt !== '' && $lockedAt !== '0000-00-00 00:00:00';
    }

    /**
     * Mark the account locked (keeps the original lock time if already locked).
     */
    public function lockAccount(int $userId): void
    {
        $user = $this->find($userId);
        if (! $user) {
            return;
        }
        if ($this->isAccountLocked($user)) {
            return;
        }

        $this->update($userId, [
            'locked_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Clear lock state so the user may sign in again.
     */
    public function unlockAccount(int $userId): void
    {
        $this->update($userId, [
            'locked_at'            => null,
            'unlock_token'         => null,
            'unlock_token_sent_at' => null,
            'unlocked_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Issue a new unlock token and ensure the account is locked.
     * Returns the plain token for the email URL.
     */
    public function issueUnlockToken(int $userId): string
    {
        $plain = bin2hex(random_bytes(32));
        $user  = $this->find($userId);
        $now   = date('Y-m-d H:i:s');

        $this->update($userId, [
            'locked_at'            => ($user && $this->isAccountLocked($user))
                ? $user['locked_at']
                : $now,
            'unlock_token'         => hash('sha256', $plain),
            'unlock_token_sent_at' => $now,
        ]);

        return $plain;
    }

    /**
     * Whether another unlock email may be sent (cooldown, or previous token expired).
     */
    public function canSendUnlockEmail(array $user): bool
    {
        $sentAt = $user['unlock_token_sent_at'] ?? null;
        if ($sentAt === null || $sentAt === '') {
            return true;
        }

        $sentTs = strtotime((string) $sentAt);
        if ($sentTs === false) {
            return true;
        }

        if ($sentTs < (time() - self::UNLOCK_TTL)) {
            return true;
        }

        return $sentTs <= (time() - self::UNLOCK_RESEND_COOLDOWN);
    }

    /**
     * Consume an unlock-email token.
     *
     * @return array{ok:bool,user?:array,reason?:string}
     */
    public function consumeUnlockToken(string $plainToken): array
    {
        $plainToken = trim($plainToken);
        if ($plainToken === '') {
            return ['ok' => false, 'reason' => 'invalid'];
        }

        $hash = hash('sha256', $plainToken);
        $user = $this->where('unlock_token', $hash)->first();

        if (! $user) {
            return ['ok' => false, 'reason' => 'invalid'];
        }

        if (! $this->isAccountLocked($user)) {
            $this->update((int) $user['id'], [
                'unlock_token'         => null,
                'unlock_token_sent_at' => null,
            ]);

            return ['ok' => true, 'user' => $user, 'reason' => 'already'];
        }

        $sentAt = $user['unlock_token_sent_at'] ?? null;
        if ($sentAt && strtotime((string) $sentAt) < (time() - self::UNLOCK_TTL)) {
            return ['ok' => false, 'reason' => 'expired', 'user' => $user];
        }

        $this->unlockAccount((int) $user['id']);

        $user['locked_at']            = null;
        $user['unlock_token']         = null;
        $user['unlock_token_sent_at'] = null;
        $user['unlocked_at']          = date('Y-m-d H:i:s');

        return ['ok' => true, 'user' => $user];
    }
}
