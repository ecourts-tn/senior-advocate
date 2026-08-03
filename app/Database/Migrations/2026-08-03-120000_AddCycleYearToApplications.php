<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCycleYearToApplications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('applications', [
            'cycle_year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Designation cycle year (one application per applicant per year)',
            ],
        ]);

        $this->db->query('CREATE INDEX IF NOT EXISTS applications_cycle_year ON applications (cycle_year)');

        // Backfill from submitted_at / created_at
        $this->db->query('
            UPDATE applications
            SET cycle_year = EXTRACT(YEAR FROM COALESCE(submitted_at, created_at))::int
            WHERE cycle_year IS NULL
              AND COALESCE(submitted_at, created_at) IS NOT NULL
        ');
    }

    public function down()
    {
        $this->forge->dropColumn('applications', 'cycle_year');
    }
}
