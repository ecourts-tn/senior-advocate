<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

/**
 * CSRF filter with friendly handling when PHP drops the body
 * (post_max_size exceeded) — common on multi-file upload steps.
 */
class CsrfFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        $method = strtolower($request->getMethod());
        if (! in_array($method, ['post', 'put', 'delete', 'patch'], true)) {
            return null;
        }

        // When the request body exceeds post_max_size, PHP empties $_POST/$_FILES.
        // CSRF then fails with a cryptic 403 — surface a clear upload-size message instead.
        if ($this->bodyLikelyExceededPostMax($request) && ! $request->isAJAX()) {
            return redirect()->back()->with(
                'error',
                'The form data or uploaded files are too large for the server limit'
                . ' (post_max_size=' . ini_get('post_max_size')
                . ', upload_max_filesize=' . ini_get('upload_max_filesize') . ').'
                . ' Please use smaller files (photo/signature ≤ 200 KB; each PDF under 5 MB)'
                . ' and try again. If this persists, ask the administrator to raise PHP upload limits.'
            );
        }

        $security = service('security');

        try {
            $security->verify($request);
        } catch (SecurityException $e) {
            if ($security->shouldRedirect() && ! $request->isAJAX()) {
                return redirect()->back()->with(
                    'error',
                    'Your session security token expired or was missing. Please try submitting the form again.'
                );
            }

            throw $e;
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    protected function bodyLikelyExceededPostMax(IncomingRequest $request): bool
    {
        $contentLength = (int) ($request->getServer('CONTENT_LENGTH') ?? 0);
        if ($contentLength <= 0) {
            return false;
        }

        $postMax = $this->iniToBytes((string) ini_get('post_max_size'));
        if ($postMax <= 0) {
            return false;
        }

        // PHP discards the body when CONTENT_LENGTH > post_max_size → empty POST.
        if ($contentLength <= $postMax) {
            return false;
        }

        $post = $request->getPost();

        return $post === [] || $post === null;
    }

    protected function iniToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return 0;
        }

        if (! preg_match('/^(\d+(?:\.\d+)?)\s*([KMG])?$/i', $value, $m)) {
            return (int) $value;
        }

        $number = (float) $m[1];
        $unit   = strtoupper($m[2] ?? '');

        return (int) match ($unit) {
            'G'     => $number * 1024 * 1024 * 1024,
            'M'     => $number * 1024 * 1024,
            'K'     => $number * 1024,
            default => $number,
        };
    }
}
