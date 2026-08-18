<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public — home temporarily disabled; login is the landing page
$routes->get('/', 'AuthController::login', ['filter' => 'guest']);
// $routes->get('home', 'Home::index'); // temporarily disabled
$routes->get('instructions', 'Home::instructions');
$routes->get('rules', 'Home::rules');
$routes->get('rules/view', 'Home::rulesView');
$routes->get('rules/download', 'Home::rulesDownload');
// Official notification PDFs published on the portal (when uploaded by admin)
$routes->get('notifications', 'Home::notifications');
$routes->get('notifications/document/(:num)', 'Home::notificationDocument/$1');

// GIGW policy & information pages
$routes->get('policy/(:segment)', 'Home::policy/$1');

// Auth (guest only)
$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::attemptRegister');
    // Enrolment lookup: POST only (CAPTCHA + rate limit enforced in controller)
    $routes->post('register/lookup', 'AuthController::lookupAdvocate');
    $routes->get('register/lookup', static function () {
        return redirect()->to('/register')->with('error', 'Please use the Search button on the registration form.');
    });
    $routes->get('resend-verification', 'AuthController::resendVerification');
    $routes->post('resend-verification', 'AuthController::sendVerificationLink');
    $routes->get('request-unlock', 'AuthController::requestUnlock');
    $routes->post('request-unlock', 'AuthController::sendUnlockLink');
    $routes->get('forgot-password', 'PasswordController::forgot');
    $routes->post('forgot-password', 'PasswordController::sendResetLink');
    $routes->get('reset-password/(:segment)', 'PasswordController::reset/$1');
    $routes->post('reset-password/(:segment)', 'PasswordController::processReset/$1');
});
// Email verification / account-unlock links (public; not limited to guest so the link always works)
$routes->get('verify-email/(:segment)', 'AuthController::verifyEmail/$1');
$routes->get('unlock-account/(:segment)', 'AuthController::unlockAccount/$1');
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
    $routes->post('application/(:num)/upload/(:segment)/remove', 'Applicant\ApplicationController::removeUpload/$1/$2');
});

// Secure file access (any authenticated user with rights checked in controller)
$routes->get('files/application/(:num)/(:segment)', 'FileController::application/$1/$2', ['filter' => 'auth']);
$routes->get('files/designation-notification/(:num)', 'FileController::designationNotification/$1', ['filter' => 'auth']);

// Admin staff area — status classification (listed / waitlisted / rejected) is admin-only
$routes->group('admin', ['filter' => 'auth:admin,reviewer,approver'], static function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('applications', 'Admin\ApplicationController::index');
    $routes->get('applications/export', 'Admin\ApplicationController::exportExcel');
    // Dedicated classification / bulk status page (must be before :num)
    $routes->get('applications/status', 'Admin\ApplicationController::statusPage', ['filter' => 'auth:admin']);
    $routes->post('applications/bulk-status', 'Admin\ApplicationController::bulkUpdateStatus', ['filter' => 'auth:admin']);
    $routes->get('applications/(:num)', 'Admin\ApplicationController::show/$1');
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
        // Legacy cycle / edit-window settings → notifications (configured on each notification)
        $routes->get('application', static function () {
            return redirect()->to('/admin/notifications')
                ->with('info', 'Application period and edit window are configured on each notification.');
        });
        $routes->post('application', static function () {
            return redirect()->to('/admin/notifications');
        });
    });

    // Legacy application submission period → notifications
    $routes->get('application-settings', static function () {
        return redirect()->to('/admin/notifications')
            ->with('info', 'Application submission period is configured on each notification.');
    }, ['filter' => 'auth:admin']);
    $routes->post('application-settings/save', static function () {
        return redirect()->to('/admin/notifications');
    }, ['filter' => 'auth:admin']);

    // Official notifications (application cycles + edit windows) — admin only
    $routes->group('notifications', ['filter' => 'auth:admin'], static function ($routes) {
        $routes->get('/', 'Admin\DesignationNotificationController::index');
        $routes->get('new', 'Admin\DesignationNotificationController::create');
        $routes->post('/', 'Admin\DesignationNotificationController::store');
        $routes->get('(:num)/edit', 'Admin\DesignationNotificationController::edit/$1');
        $routes->post('(:num)', 'Admin\DesignationNotificationController::update/$1');
        $routes->post('(:num)/delete', 'Admin\DesignationNotificationController::delete/$1');
    });

    // Legacy designation-notifications URLs → /admin/notifications
    $routes->get('designation-notifications', static function () {
        return redirect()->to('/admin/notifications');
    }, ['filter' => 'auth:admin']);
    $routes->get('designation-notifications/(:any)', static function ($any) {
        return redirect()->to('/admin/notifications/' . $any);
    }, ['filter' => 'auth:admin']);

    // Email/SMS event templates (admin only)
    $routes->group('notification-templates', ['filter' => 'auth:admin'], static function ($routes) {
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
