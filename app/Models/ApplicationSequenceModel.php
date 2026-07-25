<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationSequenceModel extends Model
{
    protected $table            = 'application_sequences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['year', 'last_number', 'updated_at'];
    protected $useTimestamps    = false;

    /**
     * Generate next application number: SAD/2026/0001
     */
    public function nextNumber(string $prefix = 'SAD', ?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $db   = db_connect();

        $db->transStart();

        $row = $db->table($this->table)
            ->where('year', $year)
            ->get()
            ->getRowArray();

        if (! $row) {
            $db->table($this->table)->insert([
                'year'        => $year,
                'last_number' => 1,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $next = 1;
        } else {
            $next = (int) $row['last_number'] + 1;
            $db->table($this->table)
                ->where('year', $year)
                ->update([
                    'last_number' => $next,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
        }

        $db->transComplete();

        return sprintf('%s/%d/%04d', $prefix, $year, $next);
    }
}
