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

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    public function findByEnrolment(string $enrolment): ?array
    {
        $enrolment = trim($enrolment);
        if ($enrolment === '') {
            return null;
        }

        return $this->where('enrolment_number', $enrolment)->first();
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
}
