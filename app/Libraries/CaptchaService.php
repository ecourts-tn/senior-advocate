<?php

namespace App\Libraries;

/**
 * Session-backed image CAPTCHA (no third-party keys required).
 */
class CaptchaService
{
    public const SESSION_KEY = 'sad_captcha_hash';
    public const SESSION_EXP = 'sad_captcha_exp';

    /** Captcha lifetime in seconds. */
    private int $ttl = 600;

    /** Characters that avoid common confusions (0/O, 1/I/l). */
    private string $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private int $length = 5;

    /**
     * Create a new captcha answer and store a hash in session.
     */
    public function regenerate(): string
    {
        $code = '';
        $max  = strlen($this->alphabet) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->alphabet[random_int(0, $max)];
        }

        $session = session();
        $session->set(self::SESSION_KEY, password_hash(strtoupper($code), PASSWORD_DEFAULT));
        $session->set(self::SESSION_EXP, time() + $this->ttl);

        return $code;
    }

    /**
     * Validate user input against the session captcha (one-time use).
     */
    public function verify(?string $input): bool
    {
        $session = session();
        $hash    = $session->get(self::SESSION_KEY);
        $exp     = (int) $session->get(self::SESSION_EXP);

        // Always clear after a verification attempt
        $session->remove(self::SESSION_KEY);
        $session->remove(self::SESSION_EXP);

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
            $size = 5; // built-in font scale via imagestring uses 1–5
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
    public function ensure(): string
    {
        $session = session();
        $hash    = $session->get(self::SESSION_KEY);
        $exp     = (int) $session->get(self::SESSION_EXP);

        if ($hash === null || $exp < time()) {
            return $this->regenerate();
        }

        // Cannot recover plain text from hash — always regenerate for display
        return $this->regenerate();
    }
}
