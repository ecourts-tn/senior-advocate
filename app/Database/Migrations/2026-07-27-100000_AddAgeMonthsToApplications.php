<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Age as on 01.01.2026 is stored as years + months (not years alone).
 */
class AddAgeMonthsToApplications extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('age_months', 'applications')) {
            $this->forge->addColumn('applications', [
                'age_months' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 2,
                    'null'       => true,
                    'after'      => 'age_years',
                    'comment'    => 'Age months component as on 01.01.2026',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('age_months', 'applications')) {
            $this->forge->dropColumn('applications', 'age_months');
        }
    }
}
