<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Models\AuditLogModel;

class AuditController extends BaseController
{
    use ListQuery;

    public function index()
    {
        $filters = $this->listQueryParams();
        $q       = $filters['q'];
        $perPage = $filters['perPage'];
        $action  = trim((string) ($this->request->getGet('action') ?? ''));

        $model = model(AuditLogModel::class);
        $model->select([
            'audit_logs.*',
            'users.name AS user_name',
            'users.email AS user_email',
            'applications.application_no AS application_no',
            'applications.full_name AS applicant_name',
        ])
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->join('applications', 'applications.id = audit_logs.application_id', 'left');

        if ($q !== '') {
            $model->groupStart()
                ->like('audit_logs.action', $q, 'both', null, true)
                ->orLike('audit_logs.ip_address', $q, 'both', null, true)
                ->orLike('audit_logs.details', $q, 'both', null, true)
                ->orLike('audit_logs.entity_type', $q, 'both', null, true)
                ->orLike('users.name', $q, 'both', null, true)
                ->orLike('users.email', $q, 'both', null, true)
                ->orLike('applications.application_no', $q, 'both', null, true)
                ->orLike('applications.full_name', $q, 'both', null, true);

            if (ctype_digit($q)) {
                $model->orWhere('audit_logs.user_id', (int) $q)
                    ->orWhere('audit_logs.application_id', (int) $q)
                    ->orWhere('audit_logs.id', (int) $q);
            }

            $model->groupEnd();
        }

        if ($action !== '') {
            $model->where('audit_logs.action', $action);
        }

        $model->orderBy('audit_logs.id', 'DESC');

        $logs  = $model->paginate($perPage, 'default', $filters['page']);
        $pager = $model->pager;
        $pager->setPath('admin/audit');
        $pager->only(['q', 'per_page', 'action']);

        // Distinct actions for filter dropdown
        $actionRows = model(AuditLogModel::class)->builder()
            ->select('action')
            ->distinct()
            ->orderBy('action', 'ASC')
            ->get()
            ->getResultArray();
        $actions = array_values(array_filter(array_column($actionRows, 'action')));

        return view('admin/audit/index', [
            'title'            => 'Audit Logs',
            'logs'             => $logs,
            'q'                => $q,
            'action'           => $action,
            'actions'          => $actions,
            'perPage'          => $perPage,
            'page'             => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'            => (int) $pager->getTotal('default'),
            'allowedPerPage'   => $filters['allowedPerPage'],
            'pager'            => $pager,
            'hasActiveFilters' => $action !== '',
        ]);
    }
}
