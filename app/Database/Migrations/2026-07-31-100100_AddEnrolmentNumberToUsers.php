<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnrolmentNumberToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'enrolment_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'after'      => 'mobile',
                'comment'    => 'Bar Council enrolment number',
            ],
        ]);

        // Unique when present (partial uniqueness handled at app level for soft-deleted rows)
        $this->db->query('CREATE UNIQUE INDEX IF NOT EXISTS users_enrolment_number_unique ON users (enrolment_number) WHERE enrolment_number IS NOT NULL AND deleted_at IS NULL');
    }

    public function down()
    {
        $this->db->query('DROP INDEX IF EXISTS users_enrolment_number_unique');
        $this->forge->dropColumn('users', 'enrolment_number');
    }
}
