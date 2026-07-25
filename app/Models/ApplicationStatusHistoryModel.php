<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationStatusHistoryModel extends Model
{
    protected $table            = 'application_status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'application_id', 'from_status', 'to_status', 'remarks', 'changed_by', 'created_at',
    ];
    protected $useTimestamps = false;

    public function record(int $applicationId, ?string $from, string $to, ?int $userId, ?string $remarks = null): void
    {
        $this->insert([
            'application_id' => $applicationId,
            'from_status'    => $from,
            'to_status'      => $to,
            'remarks'        => $remarks,
            'changed_by'     => $userId,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function forApplication(int $applicationId): array
    {
        return $this->where('application_id', $applicationId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}
