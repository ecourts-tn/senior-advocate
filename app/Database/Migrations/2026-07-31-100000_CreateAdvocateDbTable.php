<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Advocate master data (from Bar Council / VMS advocate_db dump).
 * Used to prefill registration when enrolment number is found.
 */
class CreateAdvocateDbTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'advenrol' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'comment'    => 'Enrolment number (primary key)',
            ],
            'advname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'advaddr' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'adv_gend' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'null'       => true,
            ],
            'adv_fat_hus' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'adv_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'dob' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'dobsen' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'doen' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Date of enrolment',
            ],
            'doensen' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'mastcur' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'mastex' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'bar' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Bar association / place of practice',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'mobileno' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'create_modify' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('advenrol', true);
        $this->forge->addKey('advname');
        $this->forge->addKey('mobileno');
        $this->forge->createTable('advocate_t', true);
    }

    public function down()
    {
        $this->forge->dropTable('advocate_t', true);
    }
}
