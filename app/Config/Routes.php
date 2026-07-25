<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public
$routes->get('/', 'Home::index');
$routes->get('instructions', 'Home::instructions');

// GIGW policy & information pages
$routes->get('policy/(:segment)', 'Home::policy/$1');

// Auth (guest only)
$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::attemptRegister');
    $routes->get('forgot-password', 'PasswordController::forgot');
    $routes->post('forgot-password', 'PasswordController::sendResetLink');
    $routes->get('reset-password/(:segment)', 'PasswordController::reset/$1');
    $routes->post('reset-password/(:segment)', 'PasswordController::processReset/$1');
});
// CAPTCHA image (session-backed; available without auth)
$routes->get('captcha/image', 'CaptchaController::image');
$routes->get('logout', 'AuthController::logout');

// Change password (any authenticated role)
$routes->get('change-password', 'PasswordController::change', ['filter' => 'auth']);
$routes->post('change-password', 'PasswordController::processChange', ['filter' => 'auth']);

// Applicant area
$routes->group('applicant', ['filter' => 'auth:applicant'], static function ($routes) {
    $routes->get('dashboard', 'Applicant\DashboardController::index');
    $routes->get('application/start', 'Applicant\ApplicationController::start');
    $routes->get('application/step/(:num)', 'Applicant\ApplicationController::step/$1');
    $routes->post('application/step/(:num)', 'Applicant\ApplicationController::saveStep/$1');
    $routes->get('application/view/(:num)', 'Applicant\ApplicationController::view/$1');
    $routes->get('application/pdf/(:num)', 'Applicant\ApplicationController::downloadPdf/$1');
});

// Secure file access (any authenticated user with rights checked in controller)
$routes->get('files/application/(:num)/(:segment)', 'FileController::application/$1/$2', ['filter' => 'auth']);

// Admin / Reviewer
$routes->group('admin', ['filter' => 'auth:admin,reviewer'], static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('applications', 'Admin\ApplicationController::index');
    $routes->get('applications/(:num)', 'Admin\ApplicationController::show/$1');
    $routes->post('applications/(:num)/status', 'Admin\ApplicationController::updateStatus/$1');
    $routes->get('applications/(:num)/pdf', 'Admin\ApplicationController::downloadPdf/$1');
    $routes->get('applications/(:num)/file/(:segment)', 'Admin\ApplicationController::file/$1/$2');
    $routes->get('audit', 'Admin\AuditController::index', ['filter' => 'auth:admin']);

    // Email / SMS configuration (admin only)
    $routes->group('settings', ['filter' => 'auth:admin'], static function ($routes) {
        $routes->get('email', 'Admin\SettingsController::email');
        $routes->post('email', 'Admin\SettingsController::saveEmail');
        $routes->post('email/test', 'Admin\SettingsController::testEmail');
        $routes->get('sms', 'Admin\SettingsController::sms');
        $routes->post('sms', 'Admin\SettingsController::saveSms');
        $routes->post('sms/test', 'Admin\SettingsController::testSms');
    });
});
