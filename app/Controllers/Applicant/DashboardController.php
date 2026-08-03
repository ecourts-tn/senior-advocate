<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Models\ApplicationModel;

class DashboardController extends BaseController
{
    use ListQuery;

    public function index()
    {
        $userId  = (int) session()->get('user_id');
        $filters = $this->listQueryParams();
        $q       = $filters['q'];
        $perPage = $filters['perPage'];
        $status  = (string) ($this->request->getGet('status') ?? '');

        $model = model(ApplicationModel::class);
        $model->where('user_id', $userId);

        if ($status !== '' && array_key_exists($status, ApplicationModel::STATUSES)) {
            $model->where('status', $status);
        } else {
            $status = '';
        }

        if ($q !== '') {
            $model->groupStart()
                ->like('application_no', $q, 'both', null, true)
                ->orLike('full_name', $q, 'both', null, true)
                ->orLike('enrolment_number', $q, 'both', null, true)
                ->orLike('email', $q, 'both', null, true)
                ->groupEnd();
        }

        $model->orderBy('id', 'DESC');

        $applications = $model->paginate($perPage, 'default', $filters['page']);
        $pager        = $model->pager;
        $pager->setPath('applicant/dashboard');
        $pager->only(['q', 'per_page', 'status']);

        $appsModel   = model(ApplicationModel::class);
        $draft       = $appsModel->findDraftForUser($userId);
        $editable    = $appsModel->findEditableForUser($userId);
        $canStart    = $appsModel->canStartNewApplication($userId);
        $editWindow  = ApplicationModel::editWindowInfo();

        return view('applicant/dashboard', [
            'title'            => 'Applicant Dashboard',
            'applications'     => $applications,
            'draft'            => $draft,
            'editable'         => $editable,
            'canStart'         => $canStart,
            'editWindow'       => $editWindow,
            'q'                => $q,
            'status'           => $status,
            'statuses'         => ApplicationModel::STATUSES,
            'perPage'          => $perPage,
            'page'             => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'            => (int) $pager->getTotal('default'),
            'allowedPerPage'   => $filters['allowedPerPage'],
            'pager'            => $pager,
            'hasActiveFilters' => $status !== '',
        ]);
    }
}
