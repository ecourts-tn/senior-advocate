<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerificationToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'is_active',
            ],
            'email_verification_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'email_verified_at',
                'comment'    => 'SHA-256 hash of the email verification token',
            ],
            'email_verification_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'email_verification_token',
            ],
        ]);

        // Existing accounts (including admin staff) remain able to log in.
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')
            ->where('email_verified_at', null)
            ->update(['email_verified_at' => $now]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'email_verified_at',
            'email_verification_token',
            'email_verification_sent_at',
        ]);
    }
}
