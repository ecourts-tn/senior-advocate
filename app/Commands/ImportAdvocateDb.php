<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Import advocate_db from a MySQL phpMyAdmin dump into PostgreSQL.
 *
 * Usage:
 *   php spark advocate:import /path/to/advocate_db.sql
 *   php spark advocate:import /path/to/advocate_db.sql --truncate
 */
class ImportAdvocateDb extends BaseCommand
{
    protected $group       = 'SAD';
    protected $name        = 'advocate:import';
    protected $description = 'Import advocate_db master data from MySQL SQL dump';
    protected $usage       = 'advocate:import <sql-file> [--truncate]';
    protected $arguments   = [
        'sql-file' => 'Path to advocate_db.sql dump',
    ];
    protected $options = [
        '--truncate' => 'Truncate advocate_db before import',
    ];

    public function run(array $params)
    {
        $file = $params[0] ?? null;
        if (! $file || ! is_readable($file)) {
            CLI::error('Provide a readable SQL dump path. Example: php spark advocate:import /path/to/advocate_db.sql');

            return EXIT_ERROR;
        }

        $db = Database::connect();
        if (! $db->tableExists('advocate_db')) {
            CLI::error('Table advocate_db does not exist. Run: php spark migrate');

            return EXIT_ERROR;
        }

        if (array_key_exists('truncate', CLI::getOptions()) || CLI::getOption('truncate') !== null) {
            CLI::write('Truncating advocate_db…', 'yellow');
            $db->query('TRUNCATE TABLE advocate_db');
        }

        CLI::write('Parsing and importing from: ' . $file, 'green');

        $handle = fopen($file, 'rb');
        if (! $handle) {
            CLI::error('Cannot open file.');

            return EXIT_ERROR;
        }

        $buffer    = '';
        $inInsert  = false;
        $batch     = [];
        $inserted  = 0;
        $skipped   = 0;
        $batchSize = 200;

        while (($line = fgets($handle)) !== false) {
            if (! $inInsert) {
                if (stripos($line, 'INSERT INTO') !== false && stripos($line, 'advocate_db') !== false) {
                    $inInsert = true;
                    if (preg_match('/VALUES\s*(.*)$/is', $line, $m)) {
                        $buffer = $m[1];
                    } else {
                        $buffer = '';
                    }
                }
                continue;
            }

            $buffer .= $line;

            if (str_contains($line, ';') && preg_match('/;\s*$/', rtrim($line))) {
                $rows = $this->parseValuesBlock($buffer);
                foreach ($rows as $row) {
                    if ($row === null) {
                        $skipped++;
                        continue;
                    }
                    $batch[] = $row;
                    if (count($batch) >= $batchSize) {
                        $inserted += $this->flushBatch($db, $batch);
                        $batch = [];
                        if ($inserted % 2000 === 0) {
                            CLI::write("  … {$inserted} rows", 'white');
                        }
                    }
                }
                $buffer   = '';
                $inInsert = false;
            }
        }

        if ($batch !== []) {
            $inserted += $this->flushBatch($db, $batch);
        }

        fclose($handle);
        CLI::newLine();
        CLI::write("Done. Upserted: {$inserted}. Skipped: {$skipped}.", 'green');

        $total = $db->table('advocate_db')->countAll();
        CLI::write("Table now has {$total} rows.", 'green');

        return EXIT_SUCCESS;
    }

    /**
     * @return list<array<string, mixed>|null>
     */
    protected function parseValuesBlock(string $block): array
    {
        $block = trim($block);
        $block = rtrim($block, "; \t\n\r");

        $rows = [];
        $len  = strlen($block);
        $i    = 0;

        while ($i < $len) {
            while ($i < $len && (ctype_space($block[$i]) || $block[$i] === ',')) {
                $i++;
            }
            if ($i >= $len || $block[$i] !== '(') {
                if ($i < $len) {
                    $i++;
                }
                continue;
            }
            $i++;
            $fields   = [];
            $current  = '';
            $inString = false;

            while ($i < $len) {
                $ch = $block[$i];

                if ($inString) {
                    if ($ch === '\\' && $i + 1 < $len) {
                        $next = $block[$i + 1];
                        if ($next === 'n') {
                            $current .= "\n";
                        } elseif ($next === 'r') {
                            $current .= "\r";
                        } elseif ($next === 't') {
                            $current .= "\t";
                        } else {
                            $current .= $next;
                        }
                        $i += 2;
                        continue;
                    }
                    if ($ch === "'") {
                        if ($i + 1 < $len && $block[$i + 1] === "'") {
                            $current .= "'";
                            $i += 2;
                            continue;
                        }
                        $inString = false;
                        $i++;
                        continue;
                    }
                    $current .= $ch;
                    $i++;
                    continue;
                }

                if ($ch === "'") {
                    $inString = true;
                    $i++;
                    continue;
                }
                if ($ch === ',') {
                    $fields[] = $this->castField($current);
                    $current  = '';
                    $i++;
                    continue;
                }
                if ($ch === ')') {
                    $fields[] = $this->castField($current);
                    $i++;
                    break;
                }
                $current .= $ch;
                $i++;
            }

            if (count($fields) < 16) {
                $rows[] = null;
                continue;
            }

            $enrol = trim((string) ($fields[0] ?? ''));
            if ($enrol === '' || strtoupper($enrol) === 'NULL') {
                $rows[] = null;
                continue;
            }

            $mobile = $fields[16] ?? null;
            if ($mobile === null || $mobile === '' || $mobile === '0' || $mobile === 0 || $mobile === '0.0') {
                $mobile = null;
            } else {
                $mobile = (string) $mobile;
                if (str_contains($mobile, '.')) {
                    $mobile = explode('.', $mobile, 2)[0];
                }
            }

            $rows[] = [
                'advenrol'      => $enrol,
                'advname'       => $this->nullIfEmpty($fields[1] ?? null) ?? '',
                'advaddr'       => $this->nullIfEmpty($fields[2] ?? null),
                'adv_gend'      => $this->nullIfEmpty($fields[3] ?? null),
                'adv_fat_hus'   => $this->nullIfEmpty($fields[4] ?? null),
                'adv_id'        => $this->nullIfEmpty($fields[5] ?? null),
                'dob'           => $this->cleanDate($fields[8] ?? null),
                'dobsen'        => $this->cleanDate($fields[9] ?? null),
                'doen'          => $this->cleanDate($fields[10] ?? null),
                'doensen'       => $this->cleanDate($fields[11] ?? null),
                'mastcur'       => $this->nullIfEmpty($fields[12] ?? null),
                'mastex'        => $this->nullIfEmpty($fields[13] ?? null),
                'bar'           => $this->nullIfEmpty($fields[14] ?? null),
                'remarks'       => $this->nullIfEmpty($fields[15] ?? null),
                'mobileno'      => $mobile,
                'create_modify' => $this->cleanTimestamp($fields[17] ?? null),
            ];
        }

        return $rows;
    }

    protected function castField(string $raw)
    {
        $raw = trim($raw);
        if ($raw === '' || strtoupper($raw) === 'NULL') {
            return null;
        }
        if (preg_match('/^X\'/i', $raw)) {
            return null;
        }

        return $raw;
    }

    protected function nullIfEmpty($v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    protected function cleanDate($v): ?string
    {
        if ($v === null || $v === '' || $v === '0000-00-00') {
            return null;
        }
        $s = substr((string) $v, 0, 10);
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m) || str_starts_with($s, '0000')) {
            return null;
        }
        $y = (int) $m[1];
        $mo = (int) $m[2];
        $d = (int) $m[3];
        if ($y < 1900 || $y > 2100 || $mo < 1 || $mo > 12 || $d < 1 || $d > 31) {
            return null;
        }
        if (! checkdate($mo, $d, $y)) {
            return null;
        }

        return $s;
    }

    protected function cleanTimestamp($v): ?string
    {
        if ($v === null || $v === '' || str_starts_with((string) $v, '0000-00-00')) {
            return null;
        }
        $s = (string) $v;
        if (! preg_match('/^\d{4}-\d{2}-\d{2}/', $s)) {
            return null;
        }

        return substr($s, 0, 19);
    }

    /**
     * @param list<array<string, mixed>> $batch
     */
    protected function flushBatch($db, array $batch): int
    {
        if ($batch === []) {
            return 0;
        }

        $cols = [
            'advenrol', 'advname', 'advaddr', 'adv_gend', 'adv_fat_hus', 'adv_id',
            'dob', 'dobsen', 'doen', 'doensen', 'mastcur', 'mastex', 'bar',
            'remarks', 'mobileno', 'create_modify',
        ];

        $placeholders = [];
        $binds        = [];
        foreach ($batch as $row) {
            $ph = [];
            foreach ($cols as $c) {
                $ph[]    = '?';
                $binds[] = $row[$c] ?? null;
            }
            $placeholders[] = '(' . implode(',', $ph) . ')';
        }

        $colList = implode(', ', $cols);
        $updates = [];
        foreach ($cols as $c) {
            if ($c === 'advenrol') {
                continue;
            }
            $updates[] = "{$c} = EXCLUDED.{$c}";
        }

        $sql = 'INSERT INTO advocate_db (' . $colList . ') VALUES '
            . implode(', ', $placeholders)
            . ' ON CONFLICT (advenrol) DO UPDATE SET ' . implode(', ', $updates);

        try {
            $db->query($sql, $binds);

            return count($batch);
        } catch (\Throwable $e) {
            // Fallback row-by-row
            $count = 0;
            foreach ($batch as $row) {
                try {
                    $singleBinds = [];
                    $ph          = [];
                    foreach ($cols as $c) {
                        $ph[]        = '?';
                        $singleBinds[] = $row[$c] ?? null;
                    }
                    $sql1 = 'INSERT INTO advocate_db (' . $colList . ') VALUES (' . implode(',', $ph) . ')'
                        . ' ON CONFLICT (advenrol) DO UPDATE SET ' . implode(', ', $updates);
                    $db->query($sql1, $singleBinds);
                    $count++;
                } catch (\Throwable $inner) {
                    CLI::write('Skip ' . ($row['advenrol'] ?? '?') . ': ' . $inner->getMessage(), 'red');
                }
            }

            return $count;
        }
    }
}
