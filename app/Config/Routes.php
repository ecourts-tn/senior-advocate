<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public — home temporarily disabled; login is the landing page
$routes->get('/', 'AuthController::login', ['filter' => 'guest']);
// $routes->get('home', 'Home::index'); // temporarily disabled
$routes->get('instructions', 'Home::instructions');

// GIGW policy & information pages
$routes->get('policy/(:segment)', 'Home::policy/$1');

// Auth (guest only)
$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::attemptRegister');
    $routes->get('register/lookup', 'AuthController::lookupAdvocate');
    $routes->post('register/lookup', 'AuthController::lookupAdvocate');
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
    $routes->post('application/start', 'Applicant\ApplicationController::acceptInstructions');
    $routes->get('application/step/(:num)', 'Applicant\ApplicationController::step/$1');
    $routes->post('application/step/(:num)', 'Applicant\ApplicationController::saveStep/$1');
    $routes->get('application/view/(:num)', 'Applicant\ApplicationController::view/$1');
    $routes->get('application/pdf/(:num)', 'Applicant\ApplicationController::downloadPdf/$1');
});

// Secure file access (any authenticated user with rights checked in controller)
$routes->get('files/application/(:num)/(:segment)', 'FileController::application/$1/$2', ['filter' => 'auth']);

// Admin staff area (reviewer / multi-step approver workflow temporarily disabled;
// status decisions are admin-only — see ApplicationModel::ACTIONS)
$routes->group('admin', ['filter' => 'auth:admin,reviewer,approver'], static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('applications', 'Admin\ApplicationController::index');
    $routes->get('applications/export', 'Admin\ApplicationController::exportExcel');
    $routes->get('applications/(:num)', 'Admin\ApplicationController::show/$1');
    // Accept / reject — admin only while reviewer/approver path is disabled
    $routes->post('applications/(:num)/status', 'Admin\ApplicationController::updateStatus/$1', ['filter' => 'auth:admin']);
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
        $routes->get('application', 'Admin\SettingsController::application');
        $routes->post('application', 'Admin\SettingsController::saveApplication');
    });

    // Notification templates CRUD (admin only)
    $routes->group('notifications', ['filter' => 'auth:admin'], static function ($routes) {
        $routes->get('/', 'Admin\NotificationTemplateController::index');
        $routes->get('new', 'Admin\NotificationTemplateController::create');
        $routes->post('/', 'Admin\NotificationTemplateController::store');
        $routes->get('(:num)/edit', 'Admin\NotificationTemplateController::edit/$1');
        $routes->post('(:num)', 'Admin\NotificationTemplateController::update/$1');
        $routes->post('(:num)/delete', 'Admin\NotificationTemplateController::delete/$1');
    });

    // Master management — separate CRUD per type (admin only)
    $routes->group('masters', ['filter' => 'auth:admin'], static function ($routes) {
        $routes->get('/', 'Admin\MasterController::hub');
        $routes->post('seed-defaults', 'Admin\MasterController::seedDefaults');
        $routes->get('(:segment)', 'Admin\MasterController::index/$1');
        $routes->get('(:segment)/new', 'Admin\MasterController::create/$1');
        $routes->post('(:segment)', 'Admin\MasterController::store/$1');
        $routes->post('(:segment)/seed-defaults', 'Admin\MasterController::seedDefaults/$1');
        $routes->get('(:segment)/(:num)/edit', 'Admin\MasterController::edit/$1/$2');
        $routes->post('(:segment)/(:num)', 'Admin\MasterController::update/$1/$2');
        $routes->post('(:segment)/(:num)/delete', 'Admin\MasterController::delete/$1/$2');
    });

    // Legacy lookups URL → masters hub
    $routes->get('lookups', static function () {
        return redirect()->to('/admin/masters');
    }, ['filter' => 'auth:admin']);
    $routes->get('lookups/(:any)', static function () {
        return redirect()->to('/admin/masters');
    }, ['filter' => 'auth:admin']);
});
