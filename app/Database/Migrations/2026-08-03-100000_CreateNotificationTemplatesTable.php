<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Admin-managed email/SMS notification templates for portal events.
 */
class CreateNotificationTemplatesTable extends Migration
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
            'event_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'comment'    => 'registration, application_submitted, etc.',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'email_enabled' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'sms_enabled' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'email_subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'email_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sms_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('event_key');
        $this->forge->addKey('is_active');
        $this->forge->createTable('notification_templates', true);
    }

    public function down()
    {
        $this->forge->dropTable('notification_templates', true);
    }
}
