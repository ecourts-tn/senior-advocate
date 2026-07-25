<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Libraries\NotificationService;
use App\Libraries\PdfService;
use App\Libraries\UploadService;
use App\Models\ApplicationModel;
use App\Models\ApplicationStatusHistoryModel;
use App\Models\AuditLogModel;
use App\Models\FormatL1Model;
use App\Models\FormatL2Model;
use App\Models\FormatL3AmicusModel;
use App\Models\FormatL3ProBonoModel;
use App\Models\FormatL4Model;
use App\Models\UserModel;

class ApplicationController extends BaseController
{
    use ListQuery;

    public function index()
    {
        $filters = $this->listQueryParams();
        $status  = (string) ($this->request->getGet('status') ?? '');
        $q       = $filters['q'];
        $perPage = $filters['perPage'];

        $model = model(ApplicationModel::class);
        $model->select('applications.*, users.name as applicant_account_name, users.email as account_email')
            ->join('users', 'users.id = applications.user_id', 'left');

        if ($status !== '' && array_key_exists($status, ApplicationModel::STATUSES)) {
            $model->where('applications.status', $status);
        } else {
            $status = '';
            $model->where('applications.status !=', ApplicationModel::STATUS_DRAFT);
        }

        if ($q !== '') {
            $model->groupStart()
                ->like('applications.application_no', $q, 'both', null, true)
                ->orLike('applications.full_name', $q, 'both', null, true)
                ->orLike('applications.enrolment_number', $q, 'both', null, true)
                ->orLike('applications.email', $q, 'both', null, true)
                ->orLike('users.email', $q, 'both', null, true)
                ->orLike('users.name', $q, 'both', null, true)
                ->groupEnd();
        }

        $model->orderBy('applications.submitted_at', 'DESC')
            ->orderBy('applications.id', 'DESC');

        $applications = $model->paginate($perPage, 'default', $filters['page']);
        $pager        = $model->pager;
        $pager->setPath('admin/applications');
        $pager->only(['q', 'per_page', 'status']);

        return view('admin/applications/index', [
            'title'            => 'Applications',
            'applications'     => $applications,
            'status'           => $status,
            'q'                => $q,
            'perPage'          => $perPage,
            'page'             => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'            => (int) $pager->getTotal('default'),
            'allowedPerPage'   => $filters['allowedPerPage'],
            'pager'            => $pager,
            'statuses'         => ApplicationModel::STATUSES,
            'hasActiveFilters' => $status !== '',
        ]);
    }

    public function show(int $id)
    {
        $app = model(ApplicationModel::class)->find($id);
        if (! $app) {
            return redirect()->to('/admin/applications')->with('error', 'Application not found.');
        }

        $user = model(UserModel::class)->find($app['user_id']);

        return view('admin/applications/show', [
            'title'   => 'Application ' . ($app['application_no'] ?? '#' . $id),
            'app'     => model(ApplicationModel::class)->withDecoded($app),
            'user'    => $user,
            'history' => model(ApplicationStatusHistoryModel::class)->forApplication($id),
            'l1'      => model(FormatL1Model::class)->forApplication($id),
            'l2'      => model(FormatL2Model::class)->forApplication($id),
            'l3pb'    => model(FormatL3ProBonoModel::class)->forApplication($id),
            'l3am'    => model(FormatL3AmicusModel::class)->forApplication($id),
            'l4'      => model(FormatL4Model::class)->forApplication($id),
            'statuses'=> ApplicationModel::STATUSES,
        ]);
    }

    public function updateStatus(int $id)
    {
        $app = model(ApplicationModel::class)->find($id);
        if (! $app) {
            return redirect()->to('/admin/applications')->with('error', 'Application not found.');
        }

        $toStatus = (string) $this->request->getPost('status');
        $remarks  = trim((string) $this->request->getPost('remarks'));
        $allowed  = array_keys(ApplicationModel::STATUSES);

        if (! in_array($toStatus, $allowed, true) || $toStatus === ApplicationModel::STATUS_DRAFT) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $userId = (int) session()->get('user_id');
        $from   = $app['status'];

        model(ApplicationModel::class)->update($id, [
            'status'         => $toStatus,
            'reviewed_by'    => $userId,
            'reviewed_at'    => date('Y-m-d H:i:s'),
            'review_remarks' => $remarks,
        ]);

        model(ApplicationStatusHistoryModel::class)->record($id, $from, $toStatus, $userId, $remarks);
        model(AuditLogModel::class)->log('status_changed', $userId, $id, [
            'from' => $from,
            'to'   => $toStatus,
        ]);

        // Email + SMS on approve / reject
        if (in_array($toStatus, [ApplicationModel::STATUS_APPROVED, ApplicationModel::STATUS_REJECTED], true)
            && $from !== $toStatus
        ) {
            $fresh = model(ApplicationModel::class)->find($id) ?: $app;
            $owner = model(UserModel::class)->find((int) $fresh['user_id']);
            (new NotificationService())->applicationStatus($fresh, $toStatus, $owner ?: null, $remarks);
        }

        return redirect()->to('/admin/applications/' . $id)
            ->with('success', 'Status updated to ' . (ApplicationModel::STATUSES[$toStatus] ?? $toStatus));
    }

    public function downloadPdf(int $id)
    {
        $app = model(ApplicationModel::class)->find($id);
        if (! $app) {
            return redirect()->to('/admin/applications')->with('error', 'Application not found.');
        }

        $pdf = new PdfService();
        if (empty($app['generated_pdf_path']) || ! is_file(WRITEPATH . 'uploads/' . $app['generated_pdf_path'])) {
            $path = $pdf->generateApplicationPdf(model(ApplicationModel::class)->withDecoded($app), [
                'l1'   => model(FormatL1Model::class)->forApplication($id),
                'l2'   => model(FormatL2Model::class)->forApplication($id),
                'l3pb' => model(FormatL3ProBonoModel::class)->forApplication($id),
                'l3am' => model(FormatL3AmicusModel::class)->forApplication($id),
                'l4'   => model(FormatL4Model::class)->forApplication($id),
            ]);
            model(ApplicationModel::class)->update($id, ['generated_pdf_path' => $path]);
            $app['generated_pdf_path'] = $path;
        }

        model(AuditLogModel::class)->log('pdf_downloaded', (int) session()->get('user_id'), $id);
        $pdf->stream($app['generated_pdf_path'], ($app['application_no'] ?? 'application') . '.pdf');
    }

    public function file(int $id, string $type)
    {
        $app = model(ApplicationModel::class)->find($id);
        if (! $app) {
            return redirect()->to('/admin/applications')->with('error', 'Not found.');
        }

        $map = [
            'photo'          => 'photo_path',
            'signature'      => 'signature_path',
            'enrolment_cert' => 'enrolment_cert_path',
            'format_l1'      => 'format_l1_path',
            'format_l2'      => 'format_l2_path',
            'format_l3i'     => 'format_l3i_path',
            'format_l3ii'    => 'format_l3ii_path',
            'format_l4'      => 'format_l4_path',
        ];

        if (! isset($map[$type]) || empty($app[$map[$type]])) {
            return redirect()->back()->with('error', 'File not available.');
        }

        $uploader = new UploadService();
        $abs      = $uploader->absolutePath($app[$map[$type]]);
        if (! is_file($abs)) {
            return redirect()->back()->with('error', 'File missing on server.');
        }

        return $this->response->download($abs, null)->setFileName(basename($abs));
    }
}
