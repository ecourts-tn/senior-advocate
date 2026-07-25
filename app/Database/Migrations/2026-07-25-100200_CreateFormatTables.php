<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Format L-1 to L-4 line items (structured data in addition to PDF uploads).
 */
class CreateFormatTables extends Migration
{
    public function up()
    {
        // Format L-1: Reported judgments as arguing counsel
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'court_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'madras_hc|supreme_other_hc|district_tribunal',
            ],
            's_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'court_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'case_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'citation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cause_title' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'decided_on' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'legal_formulation' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('format_l1_entries', true);

        // Format L-2: Unreported judgments (same structure)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'court_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            's_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'court_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'case_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'citation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cause_title' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'decided_on' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'legal_formulation' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('format_l2_entries', true);

        // Format L-3(i): Pro Bono work
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            's_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'court_tribunal' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'case_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cause_title' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'decided_on' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'society_benefit' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Manner in which society was sought to be benefited',
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
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('format_l3_pro_bono', true);

        // Format L-3(ii): Amicus Curiae
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            's_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'court_tribunal' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'case_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'cause_title' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'decided_on' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'reportable' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Reportable|Unreportable',
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
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('format_l3_amicus', true);

        // Format L-4: Academic articles/books/teaching/guest lectures
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            's_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'topic' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'teaching_assignment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'guest_lectures' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'other_details' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('format_l4_entries', true);
    }

    public function down()
    {
        $this->forge->dropTable('format_l4_entries', true);
        $this->forge->dropTable('format_l3_amicus', true);
        $this->forge->dropTable('format_l3_pro_bono', true);
        $this->forge->dropTable('format_l2_entries', true);
        $this->forge->dropTable('format_l1_entries', true);
    }
}
