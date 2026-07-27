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

        // Extra filter keys for reviewer / approver / admin lists
        $ageMin           = $this->nullableIntGet('age_min');
        $ageMax           = $this->nullableIntGet('age_max');
        $experienceMin    = $this->nullableIntGet('experience_min');
        $natureOfPractice = trim((string) ($this->request->getGet('nature_of_practice') ?? ''));
        $fieldOfLaw       = trim((string) ($this->request->getGet('field_of_law') ?? ''));

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

        if ($ageMin !== null) {
            $model->where('applications.age_years >=', $ageMin);
        }
        if ($ageMax !== null) {
            $model->where('applications.age_years <=', $ageMax);
        }
        // Experience at the Bar (practice years)
        if ($experienceMin !== null) {
            $model->where('applications.practice_years >=', $experienceMin);
        }
        if ($natureOfPractice !== '') {
            $model->like('applications.nature_of_practice', $natureOfPractice, 'both', null, true);
        }
        if ($fieldOfLaw !== '') {
            $model->like('applications.field_of_law', $fieldOfLaw, 'both', null, true);
        }

        $model->orderBy('applications.submitted_at', 'DESC')
            ->orderBy('applications.id', 'DESC');

        $applications = $model->paginate($perPage, 'default', $filters['page']);
        $pager        = $model->pager;
        $pager->setPath('admin/applications');
        $pager->only([
            'q', 'per_page', 'status',
            'age_min', 'age_max', 'experience_min',
            'nature_of_practice', 'field_of_law',
        ]);

        $hasActiveFilters = $status !== ''
            || $ageMin !== null
            || $ageMax !== null
            || $experienceMin !== null
            || $natureOfPractice !== ''
            || $fieldOfLaw !== '';

        return view('admin/applications/index', [
            'title'             => 'Applications',
            'applications'      => $applications,
            'status'            => $status,
            'q'                 => $q,
            'perPage'           => $perPage,
            'page'              => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'             => (int) $pager->getTotal('default'),
            'allowedPerPage'    => $filters['allowedPerPage'],
            'pager'             => $pager,
            'statuses'          => ApplicationModel::STATUSES,
            'hasActiveFilters'  => $hasActiveFilters,
            'ageMin'            => $ageMin,
            'ageMax'            => $ageMax,
            'experienceMin'     => $experienceMin,
            'natureOfPractice'  => $natureOfPractice,
            'fieldOfLaw'        => $fieldOfLaw,
        ]);
    }

    /**
     * Integer GET param, or null when blank / invalid.
     */
    private function nullableIntGet(string $key): ?int
    {
        $raw = $this->request->getGet($key);
        if ($raw === null || $raw === '') {
            return null;
        }
        if (! is_numeric($raw)) {
            return null;
        }

        return (int) $raw;
    }

    public function show(int $id)
    {
        $app = model(ApplicationModel::class)->find($id);
        if (! $app) {
            return redirect()->to('/admin/applications')->with('error', 'Application not found.');
        }

        $user = model(UserModel::class)->find($app['user_id']);
        $role = (string) session()->get('role');

        return view('admin/applications/show', [
            'title'    => 'Application ' . ($app['application_no'] ?? '#' . $id),
            'app'      => model(ApplicationModel::class)->withDecoded($app),
            'user'     => $user,
            'history'  => model(ApplicationStatusHistoryModel::class)->forApplication($id),
            'l1'       => model(FormatL1Model::class)->forApplication($id),
            'l2'       => model(FormatL2Model::class)->forApplication($id),
            'l3pb'     => model(FormatL3ProBonoModel::class)->forApplication($id),
            'l3am'     => model(FormatL3AmicusModel::class)->forApplication($id),
            'l4'       => model(FormatL4Model::class)->forApplication($id),
            'statuses' => ApplicationModel::STATUSES,
            'actions'  => ApplicationModel::availableActions((string) $app['status'], $role),
            'role'     => $role,
        ]);
    }

    public function updateStatus(int $id)
    {
        $app = model(ApplicationModel::class)->find($id);
        if (! $app) {
            return redirect()->to('/admin/applications')->with('error', 'Application not found.');
        }

        $action  = (string) $this->request->getPost('action');
        $remarks = trim((string) $this->request->getPost('remarks'));
        $role    = (string) session()->get('role');
        $meta    = ApplicationModel::resolveAction($action);

        if ($meta === null) {
            return redirect()->back()->with('error', 'Invalid workflow action.');
        }

        $allowed = ApplicationModel::availableActions((string) $app['status'], $role);
        if (! isset($allowed[$action])) {
            $statusLabel = ApplicationModel::STATUSES[$app['status']] ?? $app['status'];
            $message     = 'You are not authorised to perform "' . $meta['label']
                . '" on an application in status "' . $statusLabel . '".';

            return redirect()->back()->with('error', $message);
        }

        if (! empty($meta['remarks_required']) && $remarks === '') {
            return redirect()->back()->withInput()->with(
                'error',
                'Remarks are required for: ' . $meta['label'] . '.'
            );
        }

        $toStatus = $meta['to'];
        $userId   = (int) session()->get('user_id');
        $from     = $app['status'];

        if ($from === $toStatus) {
            return redirect()->back()->with('error', 'Application is already in that status.');
        }

        model(ApplicationModel::class)->update($id, [
            'status'         => $toStatus,
            'reviewed_by'    => $userId,
            'reviewed_at'    => date('Y-m-d H:i:s'),
            'review_remarks' => $remarks,
        ]);

        $historyNote = $meta['label'] . ($remarks !== '' ? ': ' . $remarks : '');
        model(ApplicationStatusHistoryModel::class)->record($id, $from, $toStatus, $userId, $historyNote);
        model(AuditLogModel::class)->log('status_changed', $userId, $id, [
            'action' => $action,
            'from'   => $from,
            'to'     => $toStatus,
        ]);

        $fresh = model(ApplicationModel::class)->find($id) ?: $app;
        $owner = model(UserModel::class)->find((int) $fresh['user_id']);
        $notify = new NotificationService();

        if ($toStatus === ApplicationModel::STATUS_RETURNED) {
            $notify->applicationReturned($fresh, $owner ?: null, $remarks);
        } elseif (in_array($toStatus, [ApplicationModel::STATUS_APPROVED, ApplicationModel::STATUS_REJECTED], true)) {
            $notify->applicationStatus($fresh, $toStatus, $owner ?: null, $remarks);
        }

        $toLabel = ApplicationModel::STATUSES[$toStatus] ?? $toStatus;

        return redirect()->to('/admin/applications/' . $id)
            ->with('success', $meta['label'] . ' — status is now "' . $toLabel . '".');
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
