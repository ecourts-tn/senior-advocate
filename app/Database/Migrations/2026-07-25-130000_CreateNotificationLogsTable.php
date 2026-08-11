<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Structured log of email/SMS notifications for audit and troubleshooting.
 */
class CreateNotificationLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'comment'    => 'e.g. registration, application_submitted, application_approved',
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'recipient_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'recipient_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'recipient_mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'email_subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'email_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'email_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'comment'    => 'sent|failed|skipped',
            ],
            'email_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'comment'    => 'smtp|file|error',
            ],
            'email_meta' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: path, response, error, etc.',
            ],
            'sms_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sms_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'comment'    => 'sent|failed|skipped',
            ],
            'sms_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'sms_meta' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON: path, response, error, etc.',
            ],
            'overall_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'pending',
                'comment'    => 'success|partial|failed|skipped',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event');
        $this->forge->addKey('user_id');
        $this->forge->addKey('application_id');
        $this->forge->addKey('overall_status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('notification_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_logs', true);
    }
}
