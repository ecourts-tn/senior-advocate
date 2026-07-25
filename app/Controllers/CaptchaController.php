<?php

namespace App\Controllers;

use App\Libraries\CaptchaService;

class CaptchaController extends BaseController
{
    /**
     * Serve a fresh captcha image (PNG).
     * Always regenerates so each request gets a new challenge.
     */
    public function image()
    {
        $captcha = new CaptchaService();
        $code    = $captcha->regenerate();
        $png     = $captcha->renderImage($code);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($png);
    }
}
