<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationLogModel extends Model
{
    protected $table            = 'notification_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event',
        'user_id',
        'application_id',
        'recipient_name',
        'recipient_email',
        'recipient_mobile',
        'email_subject',
        'email_body',
        'email_status',
        'email_method',
        'email_meta',
        'sms_body',
        'sms_status',
        'sms_method',
        'sms_meta',
        'overall_status',
        'ip_address',
        'created_at',
    ];
    protected $useTimestamps = false;

    /**
     * Persist a notification attempt for audit.
     *
     * @param array{
     *   event: string,
     *   user_id?: int|null,
     *   application_id?: int|null,
     *   recipient_name?: string,
     *   recipient_email?: string,
     *   recipient_mobile?: string,
     *   email_subject?: string|null,
     *   email_body?: string|null,
     *   email_result?: array|null,
     *   sms_body?: string|null,
     *   sms_result?: array|null,
     * } $data
     */
    public function record(array $data): int
    {
        $emailResult = $data['email_result'] ?? null;
        $smsResult   = $data['sms_result'] ?? null;

        $emailStatus = $this->channelStatus(
            (string) ($data['recipient_email'] ?? ''),
            $emailResult
        );
        $smsStatus = $this->channelStatus(
            (string) ($data['recipient_mobile'] ?? ''),
            $smsResult
        );

        $overall = $this->overallStatus($emailStatus, $smsStatus);

        $ip = null;
        try {
            $ip = service('request')->getIPAddress();
        } catch (\Throwable $e) {
            $ip = null;
        }

        // Cap stored HTML body size for DB practicality
        $emailBody = $data['email_body'] ?? null;
        if (is_string($emailBody) && strlen($emailBody) > 65000) {
            $emailBody = substr($emailBody, 0, 65000) . "\n<!-- truncated -->";
        }

        $id = $this->insert([
            'event'             => $data['event'],
            'user_id'           => $data['user_id'] ?? null,
            'application_id'    => $data['application_id'] ?? null,
            'recipient_name'    => $data['recipient_name'] ?? null,
            'recipient_email'   => $data['recipient_email'] ?? null,
            'recipient_mobile'  => $data['recipient_mobile'] ?? null,
            'email_subject'     => $data['email_subject'] ?? null,
            'email_body'        => $emailBody,
            'email_status'      => $emailStatus,
            'email_method'      => is_array($emailResult) ? ($emailResult['method'] ?? null) : null,
            'email_meta'        => $emailResult ? json_encode($emailResult) : null,
            'sms_body'          => $data['sms_body'] ?? null,
            'sms_status'        => $smsStatus,
            'sms_method'        => is_array($smsResult) ? ($smsResult['method'] ?? null) : null,
            'sms_meta'          => $smsResult ? json_encode($smsResult) : null,
            'overall_status'    => $overall,
            'ip_address'        => $ip,
            'created_at'        => date('Y-m-d H:i:s'),
        ], true);

        return (int) $id;
    }

    private function channelStatus(string $recipient, ?array $result): string
    {
        if ($recipient === '') {
            return 'skipped';
        }
        if ($result === null) {
            return 'failed';
        }

        return ! empty($result['sent']) ? 'sent' : 'failed';
    }

    private function overallStatus(string $emailStatus, string $smsStatus): string
    {
        $statuses = array_filter([$emailStatus, $smsStatus], static fn ($s) => $s !== 'skipped');

        if ($statuses === []) {
            return 'skipped';
        }

        $sent   = count(array_filter($statuses, static fn ($s) => $s === 'sent'));
        $failed = count(array_filter($statuses, static fn ($s) => $s === 'failed'));

        if ($sent > 0 && $failed === 0) {
            return 'success';
        }
        if ($sent > 0 && $failed > 0) {
            return 'partial';
        }

        return 'failed';
    }
}
