<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Split form_lookup_options into individual master tables.
 */
class CreateIndividualMasterTables extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'master_qualifications',
        'master_courts',
        'master_tribunals',
        'master_nature_of_practice',
        'master_fields_of_law',
    ];

    /** @var array<string, string> category => table */
    private array $categoryMap = [
        'qualification'      => 'master_qualifications',
        'court'              => 'master_courts',
        'tribunal'           => 'master_tribunals',
        'nature_of_practice' => 'master_nature_of_practice',
        'field_of_law'       => 'master_fields_of_law',
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            $this->createMasterTable($table);
        }

        // Migrate existing form_lookup_options rows when present
        if ($this->db->tableExists('form_lookup_options')) {
            $rows = $this->db->table('form_lookup_options')->get()->getResultArray();
            foreach ($rows as $row) {
                $cat = (string) ($row['category'] ?? '');
                if (! isset($this->categoryMap[$cat])) {
                    continue;
                }
                $table = $this->categoryMap[$cat];
                $exists = $this->db->table($table)
                    ->where('label', $row['label'])
                    ->countAllResults();
                if ($exists > 0) {
                    continue;
                }
                $this->db->table($table)->insert([
                    'label'      => $row['label'],
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_active'  => $this->toBool($row['is_active'] ?? true),
                    'updated_by' => $row['updated_by'] ?? null,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }

            $this->forge->dropTable('form_lookup_options', true);
        }
    }

    public function down()
    {
        // Recreate form_lookup_options and fold data back
        if (! $this->db->tableExists('form_lookup_options')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'category' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'label' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'is_active' => [
                    'type'    => 'BOOLEAN',
                    'default' => true,
                ],
                'updated_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
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
            $this->forge->addUniqueKey(['category', 'label']);
            $this->forge->createTable('form_lookup_options', true);
        }

        foreach ($this->categoryMap as $category => $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            $rows = $this->db->table($table)->get()->getResultArray();
            foreach ($rows as $row) {
                $this->db->table('form_lookup_options')->insert([
                    'category'   => $category,
                    'label'      => $row['label'],
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_active'  => $this->toBool($row['is_active'] ?? true),
                    'updated_by' => $row['updated_by'] ?? null,
                    'created_at' => $row['created_at'] ?? null,
                    'updated_at' => $row['updated_at'] ?? null,
                ]);
            }
            $this->forge->dropTable($table, true);
        }
    }

    private function createMasterTable(string $table): void
    {
        if ($this->db->tableExists($table)) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey('label');
        $this->forge->addKey('sort_order');
        $this->forge->addKey('is_active');
        $this->forge->createTable($table, true);
    }

    private function toBool(mixed $value): bool
    {
        return $value === true
            || $value === 1
            || $value === '1'
            || $value === 't'
            || $value === 'true';
    }
}
