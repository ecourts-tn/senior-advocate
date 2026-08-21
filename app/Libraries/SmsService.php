<?php

namespace App\Libraries;

use App\Models\SystemSettingModel;

/**
 * SMS sender using admin DB settings (HTTP gateway).
 * Does not write fallback files under writable/sms.
 */
class SmsService
{
    /**
     * @return array{sent: bool, method: string, response?: string}
     */
    public function send(string $mobile, string $message): array
    {
        $mobile = $this->normalizeMobile($mobile);
        $cfg    = $this->config();

        $enabled = ! empty($cfg['enabled']) && $cfg['enabled'] !== '0' && $cfg['enabled'] !== false;

        if (! $enabled || $mobile === '') {
            return ['sent' => false, 'method' => 'disabled'];
        }

        $provider = strtolower((string) ($cfg['provider'] ?? 'log'));

        if ($provider === 'http') {
            $result = $this->sendHttp($mobile, $message, $cfg);
            if (! $result['sent']) {
                log_message('error', 'SMS HTTP failed: ' . ($result['response'] ?? 'unknown'));
            }

            return $result;
        }

        return ['sent' => false, 'method' => 'log'];
    }

    /**
     * @return array<string, string|null>
     */
    private function config(): array
    {
        try {
            $model = model(SystemSettingModel::class);
            $model->ensureDefaults();

            return $model->getGroup('sms');
        } catch (\Throwable $e) {
            return [
                'enabled'   => filter_var(env('sms.enabled', true), FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
                'provider'  => (string) env('sms.provider', 'log'),
                'api_url'   => (string) env('sms.apiUrl', ''),
                'api_key'   => (string) env('sms.apiKey', ''),
                'sender_id' => (string) env('sms.senderId', 'MHCSSA'),
            ];
        }
    }

    private function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';
        if (strlen($digits) > 12) {
            $digits = substr($digits, -12);
        }

        return $digits;
    }

    /**
     * @param array<string, string|null> $cfg
     *
     * @return array{sent: bool, method: string, response?: string}
     */
    private function sendHttp(string $mobile, string $message, array $cfg): array
    {
        $url      = (string) ($cfg['api_url'] ?? '');
        $apiKey   = (string) ($cfg['api_key'] ?? '');
        $senderId = (string) ($cfg['sender_id'] ?? 'MHCSSA');

        if ($url === '') {
            return ['sent' => false, 'method' => 'http', 'response' => 'SMS API URL not set'];
        }

        $payload = [
            'to'      => $mobile,
            'message' => $message,
            'sender'  => $senderId,
            'api_key' => $apiKey,
        ];

        try {
            $client   = \Config\Services::curlrequest(['timeout' => 15, 'http_errors' => false]);
            $headers  = [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];
            if ($apiKey !== '') {
                $headers['Authorization'] = 'Bearer ' . $apiKey;
            }

            $response = $client->post($url, [
                'headers' => $headers,
                'json'    => $payload,
            ]);
            $code = $response->getStatusCode();
            $body = (string) $response->getBody();
            $ok   = $code >= 200 && $code < 300;

            return [
                'sent'     => $ok,
                'method'   => 'http',
                'response' => "HTTP {$code}: {$body}",
            ];
        } catch (\Throwable $e) {
            return ['sent' => false, 'method' => 'http', 'response' => $e->getMessage()];
        }
    }

}