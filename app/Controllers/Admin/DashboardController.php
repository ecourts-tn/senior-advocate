<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LoginSecurityService;
use App\Models\ApplicationModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $apps = model(ApplicationModel::class);

        // Use countAllResults() with default reset (true) so WHERE clauses
        // do not stack across successive counts on the same model instance.
        // Previously countAllResults(false) caused impossible AND filters and
        // left "Recent submissions" empty even when applications existed.
        $stats = [
            'total'       => (int) $apps->where('status !=', ApplicationModel::STATUS_DRAFT)->countAllResults(),
            'submitted'   => (int) $apps->where('status', ApplicationModel::STATUS_SUBMITTED)->countAllResults(),
            'listed'      => (int) $apps->where('status', ApplicationModel::STATUS_LISTED)->countAllResults(),
            'waitlisted'  => (int) $apps->where('status', ApplicationModel::STATUS_WAITLISTED)->countAllResults(),
            'rejected'    => (int) $apps->where('status', ApplicationModel::STATUS_REJECTED)->countAllResults(),
            'applicants'  => (int) model(UserModel::class)->where('role', 'applicant')->countAllResults(),
        ];

        $recent = $apps
            ->where('status !=', ApplicationModel::STATUS_DRAFT)
            ->orderBy('submitted_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll(10);

        // Unauthorized access monitoring (failed / blocked logins)
        $loginSecurity   = new LoginSecurityService();
        $authSummary     = $loginSecurity->last24HourSummary();
        $authAttempts    = $loginSecurity->recentUnauthorizedAttempts(15);

        return view('admin/dashboard', [
            'title'        => 'Admin Dashboard',
            'stats'        => $stats,
            'recent'       => $recent,
            'authSummary'  => $authSummary,
            'authAttempts' => $authAttempts,
        ]);
    }
}
