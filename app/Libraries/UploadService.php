<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Strict upload validation matching High Court proforma rules:
 * - Photo/Signature: 20–200 KB, jpg/jpeg
 * - Enrolment cert & Formats L-1 to L-4: PDF, max 5 MB
 */
class UploadService
{
    public const RULES = [
        'photo' => [
            'ext'      => ['jpg', 'jpeg'],
            'mime'     => ['image/jpeg'],
            'min_kb'   => 20,
            'max_kb'   => 200,
            'label'    => 'Passport photograph',
        ],
        'signature' => [
            'ext'      => ['jpg', 'jpeg'],
            'mime'     => ['image/jpeg'],
            'min_kb'   => 20,
            'max_kb'   => 200,
            'label'    => 'Signature',
        ],
        'enrolment_cert' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Enrolment Certificate',
        ],
        'format_l1' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-1 (Reported Judgments)',
        ],
        'format_l2' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-2 (Unreported Judgments)',
        ],
        'format_l3i' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-3(i) (Pro Bono)',
        ],
        'format_l3ii' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-3(ii) (Amicus Curiae)',
        ],
        'format_l4' => [
            'ext'    => ['pdf'],
            'mime'   => ['application/pdf'],
            'min_kb' => 1,
            'max_kb' => 5120,
            'label'  => 'Format L-4 (Academic)',
        ],
    ];

    protected string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'applications';
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0750, true);
        }
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

        $ext = strtolower($file->getClientExtension() ?: $file->guessExtension() ?: '');
        if (! in_array($ext, $rules['ext'], true)) {
            return [
                'ok'    => false,
                'error' => $rules['label'] . ' must be ' . implode('/', $rules['ext']) . ' format.',
            ];
        }

        $sizeKb = (int) ceil($file->getSize() / 1024);
        if ($sizeKb < $rules['min_kb'] || $sizeKb > $rules['max_kb']) {
            if ($rules['min_kb'] >= 20 && $rules['max_kb'] <= 200) {
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

        $mime = $file->getMimeType();
        if (! empty($rules['mime']) && ! in_array($mime, $rules['mime'], true)) {
            // Allow slight MIME variance for JPEG
            if (! ($mime === 'image/jpg' && in_array('image/jpeg', $rules['mime'], true))) {
                return ['ok' => false, 'error' => $rules['label'] . ': invalid MIME type (' . $mime . ').'];
            }
        }

        $dir = $this->basePath . DIRECTORY_SEPARATOR . $applicationId;
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $safeName = $type . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (! $file->move($dir, $safeName)) {
            return ['ok' => false, 'error' => 'Failed to store ' . $rules['label'] . '.'];
        }

        // Relative path from writable/uploads
        $relative = 'applications/' . $applicationId . '/' . $safeName;

        return ['ok' => true, 'path' => $relative];
    }

    public function absolutePath(string $relative): string
    {
        return WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
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
}
