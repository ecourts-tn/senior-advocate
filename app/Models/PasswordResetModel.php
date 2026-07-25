<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email', 'token_hash', 'expires_at', 'used_at', 'created_at',
    ];
    protected $useTimestamps = false;

    /** Token validity in seconds (1 hour). */
    public const TTL = 3600;

    /**
     * Create a new reset token for email. Returns plain token for the reset URL.
     */
    public function createToken(string $email): string
    {
        $email = strtolower(trim($email));

        // Invalidate previous unused tokens for this email
        $this->where('email', $email)
            ->where('used_at', null)
            ->set(['used_at' => date('Y-m-d H:i:s')])
            ->update();

        $plain = bin2hex(random_bytes(32));

        $this->insert([
            'email'      => $email,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $plain;
    }

    /**
     * Find a still-valid reset row by plain token.
     */
    public function findValidByToken(string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);

        $row = $this->where('token_hash', $hash)
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
