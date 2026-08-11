<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'SERIAL',
                'auto_increment' => true,
            ],
            'cycle_year' => [
                'type'       => 'INTEGER',
                'constraint' => 4,
                'null'       => false,
            ],
            'application_start_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'application_last_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'null'    => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('cycle_year');

        $this->forge->createTable('application_settings', true);

        // PostgreSQL timestamp defaults
        $this->db->query("
            ALTER TABLE application_settings
            ALTER COLUMN created_at
            SET DEFAULT CURRENT_TIMESTAMP
        ");

        $this->db->query("
            ALTER TABLE application_settings
            ALTER COLUMN updated_at
            SET DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down()
    {
        $this->forge->dropTable('application_settings', true);
    }
}