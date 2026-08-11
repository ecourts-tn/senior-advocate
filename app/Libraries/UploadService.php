<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Strict upload validation matching High Court proforma rules:
 * - Photo/Signature: 20–200 KB, jpg/jpeg — re-encoded to strip polyglot/PHP payloads
 * - Enrolment cert & Formats L-1 to L-4: PDF, max 5 MB — magic-byte + script-scan checks
 *
 * Uploads are stored under writable/uploads (outside the public web root) and
 * served only through authenticated controllers.
 */
class UploadService
{
    public const RULES = [
        'photo' => [
            'ext'    => ['jpg', 'jpeg'],
            'mime'   => ['image/jpeg'],
            'min_kb' => 20,
            'max_kb' => 200,
            'label'  => 'Passport photograph',
            'kind'   => 'jpeg',
        ],
        'signature' => [
            'ext'    => ['jpg', 'jpeg'],
            'mime'   => ['image/jpeg'],
            'min_kb' => 20,
            'max_kb' => 200,
            'label'  => 'Signature',
            'kind'   => 'jpeg',
        ],
        'enrolment_cert' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Enrolment Certificate',
            'kind'   => 'pdf',
        ],
        'age_proof' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Age proof',
            'kind'   => 'pdf',
        ],
        'education_qual' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Educational qualifications document',
            'kind'   => 'pdf',
        ],
        'format_l1' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-1 (Reported Judgments)',
            'kind'   => 'pdf',
        ],
        'format_l2' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-2 (Unreported Judgments)',
            'kind'   => 'pdf',
        ],
        'format_l3i' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-3(i) (Pro Bono)',
            'kind'   => 'pdf',
        ],
        'format_l3ii' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-3(ii) (Amicus Curiae)',
            'kind'   => 'pdf',
        ],
        'format_l4' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-4 (Academic)',
            'kind'   => 'pdf',
        ],
        'notification_document' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 10240, // 10 MB
            'label'  => 'Notification document',
            'kind'   => 'pdf',
        ],
    ];

    /** Patterns that indicate embedded script payloads (e.g. polyglot images for ModSecurity bypass). */
    private const SCRIPT_PATTERN = '/<\?(?:php|=)|<%[\s=]|<script\b[^>]*\blanguage\s*=\s*[\'"]?php/i';

    protected string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'applications';
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0750, true);
        }
        $this->ensureUploadHardening();
    }

    /**
     * @return array{ok:bool,path?:string,error?:string}
     */
    public function store(UploadedFile $file, string $type, int $applicationId): array
    {
        if (! isset(self::RULES[$type])) {
            return ['ok' => false, 'error' => 'Unknown upload type.'];
        }

        $rules = self::RULES[$type];

        if (! $file->isValid() || $file->hasMoved()) {
            return ['ok' => false, 'error' => $rules['label'] . ': ' . ($file->getErrorString() ?: 'Invalid file.')];
        }

        $ext = strtolower((string) ($file->getClientExtension() ?: $file->guessExtension() ?: ''));
        // Never trust double extensions (photo.php.jpg) — only the final extension is used,
        // and we force a safe extension on store.
        if (! in_array($ext, $rules['ext'], true)) {
            return [
                'ok'    => false,
                'error' => $rules['label'] . ' must be ' . implode('/', $rules['ext']) . ' format.',
            ];
        }

        $clientName = (string) $file->getClientName();
        if ($this->clientNameLooksExecutable($clientName)) {
            return ['ok' => false, 'error' => $rules['label'] . ': unsafe file name rejected.'];
        }

        $sizeKb = (int) ceil($file->getSize() / 1024);
        if ($sizeKb < $rules['min_kb'] || $sizeKb > $rules['max_kb']) {
            if (($rules['kind'] ?? '') === 'jpeg') {
                return [
                    'ok'    => false,
                    'error' => $rules['label'] . ' size must be between ' . $rules['min_kb'] . ' KB and ' . $rules['max_kb'] . ' KB (got ' . $sizeKb . ' KB).',
                ];
            }

            return [
                'ok'    => false,
                'error' => $rules['label'] . ' must be less than ' . $rules['max_kb'] . ' KB / 5 MB (got ' . $sizeKb . ' KB).',
            ];
        }

        $tmp = $file->getTempName();
        if ($tmp === '' || ! is_file($tmp)) {
            return ['ok' => false, 'error' => $rules['label'] . ': temporary upload missing.'];
        }

        $realMime = $this->detectMime($tmp);
        if (! $this->mimeAllowed($realMime, $rules['mime'])) {
            return ['ok' => false, 'error' => $rules['label'] . ': invalid content type (' . ($realMime ?: 'unknown') . ').'];
        }

        $dir = $this->basePath . DIRECTORY_SEPARATOR . $applicationId;
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $this->writeDenyHtaccess($dir);

        $kind = $rules['kind'] ?? 'pdf';
        if ($kind === 'jpeg') {
            // Force .jpg — never preserve client extension variants
            $safeName = $type . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $dest     = $dir . DIRECTORY_SEPARATOR . $safeName;
            $result   = $this->sanitizeStoreJpeg($tmp, $dest, $rules);
            if (! $result['ok']) {
                return $result;
            }

            return ['ok' => true, 'path' => 'applications/' . $applicationId . '/' . $safeName];
        }

        $pdfCheck = $this->validatePdfContents($tmp, $rules['label']);
        if (! $pdfCheck['ok']) {
            return $pdfCheck;
        }

        $safeName = $type . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        if (! $file->move($dir, $safeName)) {
            return ['ok' => false, 'error' => 'Failed to store ' . $rules['label'] . '.'];
        }

        return ['ok' => true, 'path' => 'applications/' . $applicationId . '/' . $safeName];
    }

    /**
     * Store an official designation notification document (PDF).
     * Saved under writable/uploads/notifications/{id}/.
     *
     * @return array{ok:bool,path?:string,error?:string}
     */
    public function storeNotificationDocument(UploadedFile $file, int $notificationId): array
    {
        $type  = 'notification_document';
        $rules = self::RULES[$type];

        if (! $file->isValid() || $file->hasMoved()) {
            return ['ok' => false, 'error' => $rules['label'] . ': ' . ($file->getErrorString() ?: 'Invalid file.')];
        }

        $ext = strtolower((string) ($file->getClientExtension() ?: $file->guessExtension() ?: ''));
        if (! in_array($ext, $rules['ext'], true)) {
            return [
                'ok'    => false,
                'error' => $rules['label'] . ' must be PDF format.',
            ];
        }

        if ($this->clientNameLooksExecutable((string) $file->getClientName())) {
            return ['ok' => false, 'error' => $rules['label'] . ': unsafe file name rejected.'];
        }

        $sizeKb = (int) ceil($file->getSize() / 1024);
        if ($sizeKb < $rules['min_kb'] || $sizeKb > $rules['max_kb']) {
            return [
                'ok'    => false,
                'error' => $rules['label'] . ' must be at most ' . (int) ($rules['max_kb'] / 1024)
                    . ' MB (got ' . $sizeKb . ' KB).',
            ];
        }

        $tmp = $file->getTempName();
        $realMime = $this->detectMime($tmp);
        if (! $this->mimeAllowed($realMime, $rules['mime'])
            && ! in_array($realMime, ['application/x-pdf', 'application/acrobat'], true)
        ) {
            return ['ok' => false, 'error' => $rules['label'] . ': invalid content type (' . ($realMime ?: 'unknown') . ').'];
        }

        $pdfCheck = $this->validatePdfContents($tmp, $rules['label']);
        if (! $pdfCheck['ok']) {
            return $pdfCheck;
        }

        $base = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'notifications';
        if (! is_dir($base)) {
            mkdir($base, 0750, true);
        }
        $this->writeDenyHtaccess($base);

        $dir = $base . DIRECTORY_SEPARATOR . $notificationId;
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $this->writeDenyHtaccess($dir);

        $safeName = 'notification_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        if (! $file->move($dir, $safeName)) {
            return ['ok' => false, 'error' => 'Failed to store ' . $rules['label'] . '.'];
        }

        return [
            'ok'   => true,
            'path' => 'notifications/' . $notificationId . '/' . $safeName,
        ];
    }

    public function absolutePath(string $relative): string
    {
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        // Prevent path traversal outside writable/uploads
        $base = realpath(WRITEPATH . 'uploads');
        $abs  = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $relative;
        $real = realpath(dirname($abs));
        if ($base === false) {
            return $abs;
        }
        if ($real === false || ! str_starts_with($real, $base)) {
            return $base . DIRECTORY_SEPARATOR . 'invalid';
        }

        return $real . DIRECTORY_SEPARATOR . basename($abs);
    }

    public function deleteIfExists(?string $relative): void
    {
        if (empty($relative)) {
            return;
        }
        $abs = $this->absolutePath($relative);
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    /**
     * Safe Content-Type for authenticated file downloads by application field type.
     */
    public static function contentTypeForField(string $type): string
    {
        return match ($type) {
            'photo', 'signature' => 'image/jpeg',
            default              => 'application/pdf',
        };
    }

    /**
     * Re-encode JPEG via GD — strips EXIF, comments, and any appended PHP short tags
     * that ModSecurity may not flag inside image binaries.
     *
     * @param array<string, mixed> $rules
     *
     * @return array{ok:bool,error?:string}
     */
    protected function sanitizeStoreJpeg(string $tmpPath, string $destPath, array $rules): array
    {
        $label = (string) $rules['label'];

        // JPEG SOI marker
        $header = (string) @file_get_contents($tmpPath, false, null, 0, 3);
        if (strlen($header) < 3 || $header[0] !== "\xFF" || $header[1] !== "\xD8" || $header[2] !== "\xFF") {
            return ['ok' => false, 'error' => $label . ': file is not a valid JPEG image.'];
        }

        $info = @getimagesize($tmpPath);
        if ($info === false || (int) ($info[2] ?? 0) !== IMAGETYPE_JPEG) {
            return ['ok' => false, 'error' => $label . ': file is not a valid JPEG image.'];
        }

        // Width/height sanity (reject absurd dimensions used for decompression bombs)
        $w = (int) ($info[0] ?? 0);
        $h = (int) ($info[1] ?? 0);
        if ($w < 10 || $h < 10 || $w > 8000 || $h > 8000) {
            return ['ok' => false, 'error' => $label . ': image dimensions are not acceptable.'];
        }

        if (! function_exists('imagecreatefromjpeg') || ! function_exists('imagejpeg')) {
            return ['ok' => false, 'error' => $label . ': image processing is unavailable on the server.'];
        }

        $img = @imagecreatefromjpeg($tmpPath);
        if ($img === false) {
            return ['ok' => false, 'error' => $label . ': could not read JPEG (file may be corrupted or polyglot).'];
        }

        // Prefer truecolor without palette tricks
        imageinterlace($img, false);

        $maxBytes = (int) $rules['max_kb'] * 1024;
        $written  = false;
        $quality  = 90;

        // Write re-encoded image; reduce quality if over max size.
        for (; $quality >= 60; $quality -= 5) {
            if (! @imagejpeg($img, $destPath, $quality)) {
                imagedestroy($img);

                return ['ok' => false, 'error' => 'Failed to store ' . $label . '.'];
            }
            clearstatcache(true, $destPath);
            $size = (int) filesize($destPath);
            if ($size > 0 && $size <= $maxBytes) {
                $written = true;
                break;
            }
        }

        imagedestroy($img);

        if (! $written || ! is_file($destPath)) {
            @unlink($destPath);

            return ['ok' => false, 'error' => $label . ': could not produce a JPEG within the allowed size after sanitisation.'];
        }

        // Final content checks on stored file
        $outMime = $this->detectMime($destPath);
        if (! $this->mimeAllowed($outMime, ['image/jpeg'])) {
            @unlink($destPath);

            return ['ok' => false, 'error' => $label . ': sanitised output failed validation.'];
        }

        // Belt-and-braces: re-encoded JPEG should never contain PHP tags; reject if it does
        $sample = (string) @file_get_contents($destPath);
        if ($sample !== '' && preg_match(self::SCRIPT_PATTERN, $sample)) {
            @unlink($destPath);

            return ['ok' => false, 'error' => $label . ': rejected — embedded script content detected.'];
        }

        @chmod($destPath, 0640);

        return ['ok' => true];
    }

    /**
     * @return array{ok:bool,error?:string}
     */
    protected function validatePdfContents(string $tmpPath, string $label): array
    {
        $fh = @fopen($tmpPath, 'rb');
        if ($fh === false) {
            return ['ok' => false, 'error' => $label . ': could not read uploaded file.'];
        }

        $head = (string) fread($fh, 5);
        fclose($fh);

        if (! str_starts_with($head, '%PDF-')) {
            return ['ok' => false, 'error' => $label . ': file is not a valid PDF.'];
        }

        // Scan file for embedded PHP short/long tags (polyglot PDFs).
        // Stream in chunks to avoid loading multi‑MB PDFs fully into memory twice.
        $handle = @fopen($tmpPath, 'rb');
        if ($handle === false) {
            return ['ok' => false, 'error' => $label . ': could not read uploaded file.'];
        }

        $carry = '';
        while (! feof($handle)) {
            $chunk = (string) fread($handle, 1024 * 256);
            $hay   = $carry . $chunk;
            if (preg_match(self::SCRIPT_PATTERN, $hay)) {
                fclose($handle);

                return ['ok' => false, 'error' => $label . ': rejected — embedded script content detected.'];
            }
            // Keep overlap so patterns spanning chunk boundaries are still found
            $carry = substr($hay, -16);
        }
        fclose($handle);

        return ['ok' => true];
    }

    protected function detectMime(string $path): string
    {
        if (! is_file($path)) {
            return '';
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = (string) finfo_file($finfo, $path);
                finfo_close($finfo);
                if ($mime !== '') {
                    return strtolower($mime);
                }
            }
        }

        $mime = @mime_content_type($path);

        return is_string($mime) ? strtolower($mime) : '';
    }

    /**
     * @param list<string> $allowed
     */
    protected function mimeAllowed(string $mime, array $allowed): bool
    {
        $mime = strtolower(trim($mime));
        if ($mime === '') {
            return false;
        }

        if (in_array($mime, $allowed, true)) {
            return true;
        }

        // Common JPEG alias
        if ($mime === 'image/jpg' && in_array('image/jpeg', $allowed, true)) {
            return true;
        }

        return false;
    }

    protected function clientNameLooksExecutable(string $name): bool
    {
        $name = strtolower($name);
        if ($name === '') {
            return false;
        }

        // Reject names that embed script-like extensions anywhere
        return (bool) preg_match(
            '/\.(php\d*|phtml|phar|php\s|cgi|pl|asp|aspx|jsp|exe|sh|bash|htaccess)(\.|$)/i',
            $name
        );
    }

    protected function ensureUploadHardening(): void
    {
        $uploads = WRITEPATH . 'uploads';
        if (! is_dir($uploads)) {
            @mkdir($uploads, 0750, true);
        }
        $this->writeDenyHtaccess($uploads);
        $this->writeDenyHtaccess($this->basePath);

        // Empty index to discourage listing if server misconfigured
        foreach ([$uploads, $this->basePath] as $dir) {
            $index = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.html';
            if (! is_file($index)) {
                @file_put_contents($index, '');
            }
        }
    }

    protected function writeDenyHtaccess(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';
        if (is_file($path)) {
            return;
        }

        $rules = <<<'HTACCESS'
# Deny direct HTTP access and PHP execution in upload storage.
<IfModule authz_core_module>
    Require all denied
</IfModule>
<IfModule !authz_core_module>
    Deny from all
</IfModule>
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
<IfModule mod_php8.c>
    php_flag engine off
</IfModule>
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar
RemoveType .php .phtml .php3 .php4 .php5 .php7 .php8 .phar
HTACCESS;

        @file_put_contents($path, $rules);
    }
}
