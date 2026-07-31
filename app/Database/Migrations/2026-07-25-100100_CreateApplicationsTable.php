<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Main application table for Senior Advocate Designation
 * Fields map to APPLICATION-CUM-CONSENT LETTER proforma (Sl. No. 1–24).
 */
class CreateApplicationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'application_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'comment'    => 'e.g. MHC/DSA/2026/0001',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'draft',
                'comment'    => 'draft|submitted|under_review|approved|rejected|returned',
            ],
            'current_step' => [
                'type'       => 'SMALLINT',
                'constraint' => 2,
                'default'    => 1,
            ],

            // Sl.1 Name
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'Dr./Mr./Ms./Mrs.',
            ],
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // Sl.2 DOB & Age
            'date_of_birth' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'age_years' => [
                'type'       => 'SMALLINT',
                'constraint' => 3,
                'null'       => true,
            ],
            'age_months' => [
                'type'       => 'SMALLINT',
                'constraint' => 2,
                'null'       => true,
                'comment'    => 'Age months component as on 01.01.2026',
            ],

            // Sl.3 Address
            'address_office' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'address_residence' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.4 Contact
            'phone_landline' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'mobile' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // Sl.5 Qualifications
            'qualifications' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.6 Enrolment
            'enrolment_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'enrolment_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bar_council' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'practice_years' => [
                'type'       => 'SMALLINT',
                'constraint' => 3,
                'null'       => true,
            ],
            'practice_months' => [
                'type'       => 'SMALLINT',
                'constraint' => 2,
                'null'       => true,
            ],

            // Sl.7 Income
            'net_income_lakhs' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],

            // Sl.8 Bar association
            'is_bar_association_member' => [
                'type'    => 'BOOLEAN',
                'null'    => true,
            ],
            'bar_association_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            // Sl.9 Reported judgments counts
            'reported_sc' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'reported_hc' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'reported_district' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Sl.10 Unreported judgments counts
            'unreported_sc' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'unreported_hc' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'unreported_district' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Sl.11 Pro Bono / Amicus
            'pro_bono_total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'amicus_total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Sl.12 First generation
            'is_first_generation' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],

            // Sl.13 Academic counts
            'academic_articles_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'academic_books_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'teaching_assignments_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'guest_lectures_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            // Sl.14 Courts practiced — JSON array of
            // { court: string, from_date: Y-m-d|null, to_date: Y-m-d|null }
            'courts_practiced' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.15 Tribunals — JSON array of
            // { tribunal: string, from_date: Y-m-d|null, to_date: Y-m-d|null }
            'tribunals_practiced' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.16 Nature of practice
            'nature_of_practice' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.17 Field of law
            'field_of_law' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.18 Earlier MHC application
            'applied_mhc_earlier' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'applied_mhc_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'applied_mhc_status' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.19 Other courts application
            'applied_other_court' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'applied_other_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.20 FIR
            'fir_lodged' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'fir_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.21 Criminal case
            'criminal_case_party' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'criminal_case_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.22 Bar Council proceedings
            'bar_council_proceedings' => [
                'type' => 'BOOLEAN',
                'null' => true,
            ],
            'bar_council_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.23 Health
            'general_health' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Sl.24 Other info
            'other_information' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Declaration
            'declaration_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'declaration_accepted' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'instructions_accepted' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'declaration_date' => [
                'type' => 'DATE',
                'null' => true,
            ],

            // File paths (stored under writable/uploads)
            'photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'signature_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'enrolment_cert_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'format_l1_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'format_l2_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'format_l3i_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'format_l3ii_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'format_l4_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'generated_pdf_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],

            // Review workflow
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'review_remarks' => [
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('application_no');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('applications', true);
    }

    public function down()
    {
        $this->forge->dropTable('applications', true);
    }
}
