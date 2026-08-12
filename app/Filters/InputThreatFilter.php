<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Application-layer request inspection that closes known ModSecurity gaps:
 *
 * - PHP short echo tags (<?=) and other open tags often not blocked by CRS
 * - PostgreSQL-specific operators / casts (::type, || concatenation) used in SQLi
 * - Dangerous PostgreSQL functions and common injection scaffolds
 *
 * This does not replace parameterized queries — it is defence-in-depth when
 * WAF rules allow these tokens. Binary file uploads are not scanned here
 * (handled by UploadService re-encoding / PDF checks).
 */
class InputThreatFilter implements FilterInterface
{
    /**
     * High-confidence threat patterns (case-insensitive where noted).
     * Ordered roughly by cost (cheap checks first via early short patterns).
     *
     * @var list<array{id:string,pattern:string}>
     */
    private const PATTERNS = [
        // ── PHP / template injection (ModSecurity often misses <?=) ─────────
        ['id' => 'php_short_echo',     'pattern' => '/<\?=/'],
        ['id' => 'php_open_tag',       'pattern' => '/<\?(?!xml\b)(?:php\b|=|[\s\r\n])/i'],
        ['id' => 'asp_open_tag',       'pattern' => '/<%[=]?/'],
        ['id' => 'php_script_lang',    'pattern' => '/<script\b[^>]*\blanguage\s*=\s*[\'"]?php/i'],

        // ── PostgreSQL cast operator ::type (WAF often allows bare ::) ──────
        ['id' => 'pg_cast',            'pattern' => '/::\s*(int|integer|bigint|smallint|text|varchar|character|char|bool|boolean|numeric|decimal|float|real|double|bytea|json|jsonb|name|oid|regclass|regtype|date|timestamp|timestamptz|uuid|money|xml)\b/i'],

        // ── PostgreSQL concatenation || used as SQLi glue ───────────────────
        ['id' => 'pg_concat_quote',    'pattern' => '/([\'"`])\s*\|\||\|\|\s*([\'"`(])/'],
        ['id' => 'pg_concat_chr',      'pattern' => '/\|\|\s*(chr|char|ascii|encode|decode|cast|convert|substring|substr|pg_)\s*\(/i'],
        ['id' => 'pg_concat_select',   'pattern' => '/\|\|\s*\(\s*select\b/i'],

        // ── PostgreSQL / SQLi scaffolds ─────────────────────────────────────
        ['id' => 'pg_sleep',           'pattern' => '/\bpg_sleep\s*\(/i'],
        ['id' => 'pg_read_file',       'pattern' => '/\bpg_(read_file|ls_dir|stat_file|write_file)\s*\(/i'],
        ['id' => 'pg_large_object',    'pattern' => '/\b(lo_import|lo_export|lo_create|loid)\s*\(/i'],
        ['id' => 'pg_catalog',         'pattern' => '/\bpg_catalog\b|\binformation_schema\b/i'],
        ['id' => 'pg_copy',            'pattern' => '/\bcopy\s+\w+\s+from\b/i'],
        ['id' => 'union_select',       'pattern' => '/\bunion\b[\s\/\*]+\bselect\b/i'],
        ['id' => 'stacked_query',      'pattern' => '/;\s*(select|insert|update|delete|drop|alter|create|truncate|copy|grant|revoke)\b/i'],
        ['id' => 'sql_comment_tail',   'pattern' => '/(\'|"|`)\s*(--|#|\/\*)/'],
        ['id' => 'benchmark_sleep',    'pattern' => '/\b(sleep|benchmark|waitfor\s+delay)\s*\(/i'],
        ['id' => 'hex_blob',           'pattern' => '/0x[0-9a-f]{8,}/i'],
        ['id' => 'null_byte',          'pattern' => '/\x00|%00/i'],
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        $sources = [
            'get'  => $request->getGet() ?? [],
            'post' => $request->getPost() ?? [],
        ];

        // Avoid scanning raw multipart bodies (file binaries); only form fields above.
        $contentType = strtolower((string) $request->getHeaderLine('Content-Type'));
        if (! str_contains($contentType, 'multipart/form-data')) {
            $raw = $request->getRawInput();
            if (is_array($raw) && $raw !== []) {
                $sources['raw'] = $raw;
            } elseif (is_string($raw) && $raw !== '') {
                // Cap scan size for large JSON bodies
                $sources['raw_body'] = substr($raw, 0, 65536);
            }
        }

        // Cookie values are attacker-controlled too
        $cookies = $request->getCookie() ?? [];
        if ($cookies !== []) {
            $sources['cookie'] = $cookies;
        }

        foreach ($sources as $source => $values) {
            $hit = $this->scan($values, $source);
            if ($hit !== null) {
                return $this->block($request, $hit);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    /**
     * @param array<string|int, mixed>|string $value
     *
     * @return array{source:string,path:string,rule:string}|null
     */
    private function scan($value, string $source, string $path = ''): ?array
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $keyStr = is_string($key) ? $key : (string) $key;
                // Also inspect keys (rare but possible)
                if (is_string($key) && $key !== '') {
                    $keyHit = $this->matchString($key, $source, $path . '[key]');
                    if ($keyHit !== null) {
                        return $keyHit;
                    }
                }
                $childPath = $path === '' ? $keyStr : ($path . '.' . $keyStr);
                $hit       = $this->scan($item, $source, $childPath);
                if ($hit !== null) {
                    return $hit;
                }
            }

            return null;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        // Skip pure CSRF / captcha short tokens (no need, patterns won't match)
        return $this->matchString($value, $source, $path);
    }

    /**
     * @return array{source:string,path:string,rule:string}|null
     */
    private function matchString(string $value, string $source, string $path): ?array
    {
        // Normalize common evasion: fullwidth, null-stripped already handled, mixed case via /i
        $probe = $value;
        // Decode one level of URL encoding for scanners that double-encode
        if (str_contains($probe, '%')) {
            $decoded = rawurldecode($probe);
            if (is_string($decoded) && $decoded !== $probe) {
                $probe = $decoded;
            }
        }

        foreach (self::PATTERNS as $rule) {
            if (preg_match($rule['pattern'], $probe) === 1) {
                return [
                    'source' => $source,
                    'path'   => $path !== '' ? $path : '(value)',
                    'rule'   => $rule['id'],
                ];
            }
        }

        return null;
    }

    /**
     * @param array{source:string,path:string,rule:string} $hit
     */
    private function block(IncomingRequest $request, array $hit): ResponseInterface
    {
        $ip = $request->getIPAddress();
        log_message(
            'warning',
            sprintf(
                'InputThreatFilter blocked request rule=%s source=%s path=%s ip=%s uri=%s',
                $hit['rule'],
                $hit['source'],
                $hit['path'],
                $ip,
                (string) $request->getUri()
            )
        );

        // Best-effort audit trail (do not store full payload)
        try {
            model(\App\Models\AuditLogModel::class)->log(
                'input_threat_blocked',
                null,
                null,
                [
                    'rule'   => $hit['rule'],
                    'source' => $hit['source'],
                    'path'   => $hit['path'],
                    'uri'    => (string) $request->getUri(),
                    'method' => $request->getMethod(),
                ],
                'security',
                null
            );
        } catch (\Throwable $e) {
            // never fail closed on audit write
        }

        $response = service('response');
        $wantsJson = $request->isAJAX()
            || str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json');

        if ($wantsJson) {
            return $response
                ->setStatusCode(403)
                ->setJSON([
                    'error'   => true,
                    'message' => 'Request blocked by security policy.',
                    'code'    => 'INPUT_THREAT',
                ]);
        }

        // HTML form posts: friendly redirect when possible
        if (in_array(strtolower($request->getMethod()), ['post', 'put', 'patch'], true)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Your request was blocked by the security filter. Remove unusual characters or code fragments and try again.');
        }

        return $response
            ->setStatusCode(403)
            ->setBody(
                '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Forbidden</title></head>'
                . '<body style="font-family:system-ui,sans-serif;padding:2rem;">'
                . '<h1>403 Forbidden</h1>'
                . '<p>Your request was blocked by the application security filter.</p>'
                . '<p><a href="' . esc(base_url('login')) . '">Return to login</a></p>'
                . '</body></html>'
            )
            ->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
