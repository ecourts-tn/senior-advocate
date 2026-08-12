<?php

namespace App\Libraries;

/**
 * Session-backed image CAPTCHA (no third-party keys required).
 *
 * Supports independent scopes so multiple captchas can coexist on one page
 * (e.g. registration search vs. account submit).
 */
class CaptchaService
{
    public const SESSION_KEY = 'ssa_captcha_hash';
    public const SESSION_EXP = 'ssa_captcha_exp';

    /** Default scope used by login and other single-captcha forms. */
    public const SCOPE_DEFAULT = 'default';

    /** Registration enrolment lookup. */
    public const SCOPE_LOOKUP = 'lookup';

    /** Registration account creation. */
    public const SCOPE_REGISTER = 'register';

    /** Captcha lifetime in seconds. */
    private int $ttl = 600;

    /** Characters that avoid common confusions (0/O, 1/I/l). */
    private string $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private int $length = 5;

    /**
     * Normalise a scope name for session keys.
     */
    public static function normaliseScope(?string $scope): string
    {
        $scope = strtolower(trim((string) $scope));
        if ($scope === '' || $scope === 'default' || $scope === 'main') {
            return self::SCOPE_DEFAULT;
        }
        // Allow only simple identifiers (prevents session-key pollution)
        if (! preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $scope)) {
            return self::SCOPE_DEFAULT;
        }

        return $scope;
    }

    private function hashKey(string $scope): string
    {
        $scope = self::normaliseScope($scope);

        return $scope === self::SCOPE_DEFAULT
            ? self::SESSION_KEY
            : self::SESSION_KEY . '_' . $scope;
    }

    private function expKey(string $scope): string
    {
        $scope = self::normaliseScope($scope);

        return $scope === self::SCOPE_DEFAULT
            ? self::SESSION_EXP
            : self::SESSION_EXP . '_' . $scope;
    }

    /**
     * Create a new captcha answer and store a hash in session for the given scope.
     */
    public function regenerate(?string $scope = null): string
    {
        $scope = self::normaliseScope($scope);
        $code  = '';
        $max   = strlen($this->alphabet) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->alphabet[random_int(0, $max)];
        }

        $session = session();
        $session->set($this->hashKey($scope), password_hash(strtoupper($code), PASSWORD_DEFAULT));
        $session->set($this->expKey($scope), time() + $this->ttl);

        return $code;
    }

    /**
     * Validate user input against the session captcha for a scope (one-time use).
     * Only that scope is cleared — other scopes remain valid.
     */
    public function verify(?string $input, ?string $scope = null): bool
    {
        $scope   = self::normaliseScope($scope);
        $session = session();
        $hashKey = $this->hashKey($scope);
        $expKey  = $this->expKey($scope);
        $hash    = $session->get($hashKey);
        $exp     = (int) $session->get($expKey);

        // Always clear after a verification attempt (one-time for this scope)
        $session->remove($hashKey);
        $session->remove($expKey);

        if ($hash === null || $exp < time()) {
            return false;
        }

        $input = strtoupper(trim((string) $input));
        if ($input === '' || strlen($input) < 4) {
            return false;
        }

        return password_verify($input, $hash);
    }

    /**
     * Render captcha PNG binary for the given plain code.
     */
    public function renderImage(string $code): string
    {
        $width  = 180;
        $height = 56;
        $img    = imagecreatetruecolor($width, $height);

        $bg    = imagecolorallocate($img, 247, 244, 239); // cream
        $ink   = imagecolorallocate($img, 15, 35, 64);    // navy
        $noise = imagecolorallocate($img, 169, 121, 44);  // brass
        $line  = imagecolorallocate($img, 180, 170, 150);

        imagefilledrectangle($img, 0, 0, $width, $height, $bg);

        // Noise lines
        for ($i = 0; $i < 6; $i++) {
            imageline(
                $img,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $line
            );
        }

        // Noise dots
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($img, random_int(0, $width - 1), random_int(0, $height - 1), $noise);
        }

        $len   = strlen($code);
        $slotW = (int) (($width - 20) / max(1, $len));

        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];
            $x    = 12 + ($i * $slotW) + random_int(0, 4);
            $y    = random_int(14, 22);
            // Use larger built-in font (5)
            imagestring($img, 5, $x, $y, $char, $ink);
            // Slight second pass for bolder look
            imagestring($img, 5, $x + 1, $y, $char, $ink);
        }

        // Border
        imagerectangle($img, 0, 0, $width - 1, $height - 1, $ink);

        ob_start();
        imagepng($img);
        $binary = ob_get_clean() ?: '';
        imagedestroy($img);

        return $binary;
    }

    /**
     * Ensure a captcha exists (regenerate if missing/expired). Returns plain code for image only.
     */
    public function ensure(?string $scope = null): string
    {
        $scope   = self::normaliseScope($scope);
        $session = session();
        $hash    = $session->get($this->hashKey($scope));
        $exp     = (int) $session->get($this->expKey($scope));

        if ($hash === null || $exp < time()) {
            return $this->regenerate($scope);
        }

        // Cannot recover plain text from hash — always regenerate for display
        return $this->regenerate($scope);
    }
}
