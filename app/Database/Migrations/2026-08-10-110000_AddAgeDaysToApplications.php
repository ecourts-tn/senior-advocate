<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Age as on 01.01 of cycle year is stored as years + months + days.
 */
class AddAgeDaysToApplications extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('age_days', 'applications')) {
            $this->forge->addColumn('applications', [
                'age_days' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 2,
                    'null'       => true,
                    'after'      => 'age_months',
                    'comment'    => 'Age days remainder component as on 01.01 of cycle year',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('age_days', 'applications')) {
            $this->forge->dropColumn('applications', 'age_days');
        }
    }
}
