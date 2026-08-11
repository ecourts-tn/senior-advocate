<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sl.19: split date of application and details into separate fields
 * (mirrors Sl.18 applied_mhc_date / applied_mhc_status).
 */
class AddAppliedOtherDateToApplications extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('applied_other_date', 'applications')) {
            $this->forge->addColumn('applications', [
                'applied_other_date' => [
                    'type'    => 'DATE',
                    'null'    => true,
                    'after'   => 'applied_other_court',
                    'comment' => 'Date of earlier application to SC / other High Court',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('applied_other_date', 'applications')) {
            $this->forge->dropColumn('applications', 'applied_other_date');
        }
    }
}
