<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Step 7: age proof (mandatory) and educational qualifications document (optional).
 */
class AddAgeProofAndEducationUploads extends Migration
{
    public function up()
    {
        $this->forge->addColumn('applications', [
            'age_proof_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Age proof PDF (e.g. birth certificate / SSLC)',
            ],
            'education_qual_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Educational qualifications supporting document PDF (optional)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('applications', 'age_proof_path');
        $this->forge->dropColumn('applications', 'education_qual_path');
    }
}
