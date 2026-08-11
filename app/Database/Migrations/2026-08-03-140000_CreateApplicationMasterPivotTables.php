<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * One-to-many (application → master) pivot tables for multi-select masters.
 * Supports both master FK and free-text "Others" rows.
 */
class CreateApplicationMasterPivotTables extends Migration
{
    /** @var array<string, array{table: string, master_table: string, fk: string, source_col: string}> */
    private array $pivots = [
        'qualification' => [
            'table'        => 'application_qualifications',
            'master_table' => 'master_qualifications',
            'fk'           => 'qualification_id',
            'source_col'   => 'qualifications',
        ],
        'nature_of_practice' => [
            'table'        => 'application_nature_of_practice',
            'master_table' => 'master_nature_of_practice',
            'fk'           => 'nature_id',
            'source_col'   => 'nature_of_practice',
        ],
        'field_of_law' => [
            'table'        => 'application_fields_of_law',
            'master_table' => 'master_fields_of_law',
            'fk'           => 'field_id',
            'source_col'   => 'field_of_law',
        ],
    ];

    public function up()
    {
        foreach ($this->pivots as $meta) {
            $this->createPivot($meta['table'], $meta['fk']);
        }

        // Backfill from denormalised TEXT columns when present
        if ($this->db->tableExists('applications')) {
            foreach ($this->pivots as $meta) {
                $this->backfill($meta);
            }
        }
    }

    public function down()
    {
        foreach ($this->pivots as $meta) {
            $this->forge->dropTable($meta['table'], true);
        }
    }

    private function createPivot(string $table, string $fkCol): void
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
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            $fkCol => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL when value is free-text Others',
            ],
            'other_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('application_id');
        $this->forge->addKey($fkCol);
        $this->forge->createTable($table, true);
    }

    /**
     * @param array{table: string, master_table: string, fk: string, source_col: string} $meta
     */
    private function backfill(array $meta): void
    {
        if (! $this->db->tableExists($meta['master_table'])) {
            return;
        }

        $apps = $this->db->table('applications')
            ->select('id, ' . $meta['source_col'])
            ->where($meta['source_col'] . ' IS NOT NULL')
            ->where($meta['source_col'] . ' !=', '')
            ->get()
            ->getResultArray();

        $masters = $this->db->table($meta['master_table'])->get()->getResultArray();
        $byLabel = [];
        foreach ($masters as $m) {
            $byLabel[mb_strtolower(trim((string) $m['label']))] = (int) $m['id'];
        }

        foreach ($apps as $app) {
            $existing = $this->db->table($meta['table'])
                ->where('application_id', $app['id'])
                ->countAllResults();
            if ($existing > 0) {
                continue;
            }

            $parts = preg_split('/\s*,\s*|\r\n|\n|\r/', (string) $app[$meta['source_col']]) ?: [];
            $order = 0;
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                $key = mb_strtolower($part);
                $row = [
                    'application_id' => (int) $app['id'],
                    $meta['fk']      => $byLabel[$key] ?? null,
                    'other_text'     => isset($byLabel[$key]) ? null : $part,
                    'sort_order'     => $order,
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
                $this->db->table($meta['table'])->insert($row);
                $order += 10;
            }
        }
    }
}
