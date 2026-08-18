<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccountLockToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'locked_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'last_login_at',
                'comment' => 'Set when the account is locked after repeated failed logins',
            ],
            'unlock_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'locked_at',
                'comment'    => 'SHA-256 hash of the account unlock token',
            ],
            'unlock_token_sent_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'unlock_token',
            ],
            'unlocked_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'after'   => 'unlock_token_sent_at',
                'comment' => 'Last successful unlock; failed-login window starts after this',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'locked_at',
            'unlock_token',
            'unlock_token_sent_at',
            'unlocked_at',
        ]);
    }
}
