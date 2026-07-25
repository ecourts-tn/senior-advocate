<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'application_id', 'action', 'entity_type', 'entity_id',
        'ip_address', 'user_agent', 'details', 'created_at',
    ];
    protected $useTimestamps = false;

    public function log(
        string $action,
        ?int $userId = null,
        ?int $applicationId = null,
        ?array $details = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): void {
        $request = service('request');

        $this->insert([
            'user_id'        => $userId,
            'application_id' => $applicationId,
            'action'         => $action,
            'entity_type'    => $entityType,
            'entity_id'      => $entityId,
            'ip_address'     => $request->getIPAddress(),
            'user_agent'     => substr((string) $request->getUserAgent(), 0, 500),
            'details'        => $details ? json_encode($details) : null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
