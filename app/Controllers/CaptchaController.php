<?php

namespace App\Controllers;

use App\Libraries\CaptchaService;

class CaptchaController extends BaseController
{
    /**
     * Serve a fresh captcha image (PNG when GD is available, otherwise SVG).
     * Always regenerates so each request gets a new challenge.
     *
     * Optional query: ?scope=lookup|register|default
     * Independent scopes allow multiple captchas on one page.
     */
    public function image()
    {
        $scope   = CaptchaService::normaliseScope($this->request->getGet('scope'));
        $captcha = new CaptchaService();
        $code    = $captcha->regenerate($scope);
        $image   = $captcha->render($code);

        return $this->response
            ->setHeader('Content-Type', $image['mime'])
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($image['body']);
    }
}
