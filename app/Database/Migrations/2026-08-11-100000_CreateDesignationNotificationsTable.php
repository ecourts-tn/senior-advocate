<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Official High Court designation notifications that open application cycles.
 * Frequency may be quarterly (3 months), half-yearly (6 months), or yearly.
 */
class CreateDesignationNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'SERIAL',
                'auto_increment' => true,
            ],
            'notification_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Official notification number (e.g. ROC.No.123/2026)',
            ],
            'notification_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'frequency' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'yearly',
                'comment'    => '3_months|6_months|yearly',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Optional short label for admin lists',
            ],
            'application_start_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'application_end_date' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'edit_window_start_date' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'edit_window_end_date' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'null'    => false,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('notification_number');
        $this->forge->addKey('notification_date');
        $this->forge->addKey('is_active');
        $this->forge->addKey(['application_start_date', 'application_end_date']);

        $this->forge->createTable('designation_notifications', true);

        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN created_at SET DEFAULT CURRENT_TIMESTAMP
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN updated_at SET DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down()
    {
        $this->forge->dropTable('designation_notifications', true);
    }
}
