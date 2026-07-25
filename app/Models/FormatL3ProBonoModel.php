<?php

namespace App\Models;

use CodeIgniter\Model;

class FormatL3ProBonoModel extends Model
{
    protected $table            = 'format_l3_pro_bono';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'application_id', 's_no', 'court_tribunal', 'case_number',
        'cause_title', 'decided_on', 'society_benefit',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function replaceForApplication(int $applicationId, array $rows): void
    {
        $this->where('application_id', $applicationId)->delete();
        $s = 1;
        foreach ($rows as $row) {
            if (empty(array_filter($row))) {
                continue;
            }
            $row['application_id'] = $applicationId;
            $row['s_no']           = $s++;
            $this->insert($row);
        }
    }

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)->orderBy('s_no', 'ASC')->findAll();
    }
}
