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
        $list    = $this->applicationListFilters();
        $q       = $filters['q'];
        $perPage = $filters['perPage'];

        $model = $this->buildApplicationListQuery($list);
        $model->orderBy('applications.submitted_at', 'DESC')
            ->orderBy('applications.id', 'DESC');

        $applications = $model->paginate($perPage, 'default', $filters['page']);
        $pager        = $model->pager;
        $pager->setPath('admin/applications');
        $pager->only([
            'q', 'per_page', 'status',
            'age_min', 'age_max', 'experience_min',
            'nature_of_practice', 'field_of_law', 'first_generation',
        ]);

        return view('admin/applications/index', [
            'title'             => 'Applications',
            'applications'      => $applications,
            'status'            => $list['status'],
            'q'                 => $q,
            'perPage'           => $perPage,
            'page'              => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'             => (int) $pager->getTotal('default'),
            'allowedPerPage'    => $filters['allowedPerPage'],
            'pager'             => $pager,
            'statuses'          => ApplicationModel::STATUSES,
            'hasActiveFilters'  => $list['hasActiveFilters'],
            'ageMin'            => $list['ageMin'],
            'ageMax'            => $list['ageMax'],
            'experienceMin'     => $list['experienceMin'],
            'natureOfPractice'  => $list['natureOfPractice'],
            'fieldOfLaw'        => $list['fieldOfLaw'],
            'firstGeneration'   => $list['firstGeneration'],
            'exportQuery'       => $this->exportQueryString($list, $q),
        ]);
    }

    /**
     * Export filtered applications to Excel-compatible .xls (SpreadsheetML).
     * Defaults to non-draft applications; respects the same filters as the list.
     */
    public function exportExcel()
    {
        $filters = $this->listQueryParams();
        $list    = $this->applicationListFilters();
        $q       = $filters['q'];

        // Export focuses on submitted pipeline: if no status chosen, exclude drafts only
        // (same as list). Caller can pass status=submitted to limit further.
        $model = $this->buildApplicationListQuery($list);
        $model->orderBy('applications.submitted_at', 'DESC')
            ->orderBy('applications.id', 'DESC');

        $rows = $model->findAll(5000);

        model(AuditLogModel::class)->log(
            'applications_exported',
            (int) session()->get('user_id'),
            null,
            [
                'count'  => count($rows),
                'status' => $list['status'] !== '' ? $list['status'] : 'all_non_draft',
                'q'      => $q,
            ]
        );

        $headers = [
            'Application No.',
            'Status',
            'Cycle year',
            'Title',
            'Full name',
            'Date of birth',
            'Age (years)',
            'Age (months)',
            'Email',
            'Mobile',
            'Landline',
            'Office address',
            'Residential address',
            'Qualifications',
            'Enrolment number',
            'Enrolment date',
            'Bar Council',
            'Practice years',
            'Practice months',
            'Net income (₹ lakhs)',
            'Bar association member',
            'Bar association name',
            'Reported SC',
            'Reported HC',
            'Reported District/Tribunal',
            'Unreported SC',
            'Unreported HC',
            'Unreported District/Tribunal',
            'Pro bono total',
            'Amicus total',
            'First-generation lawyer',
            'Academic articles',
            'Academic books',
            'Teaching assignments',
            'Guest lectures',
            'Nature of practice',
            'Field of law',
            'Applied MHC earlier',
            'Applied MHC date',
            'Applied MHC status',
            'Applied other court',
            'Applied other date',
            'Applied other details',
            'FIR lodged',
            'FIR details',
            'Criminal case party',
            'Criminal case details',
            'Bar Council proceedings',
            'Bar Council details',
            'General health',
            'Other information',
            'Submitted at',
            'Reviewed at',
            'Review remarks',
            'Current step',
        ];

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Styles>'
            . '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#0F2340" ss:Pattern="Solid"/>'
            . '<Font ss:Color="#FFFFFF" ss:Bold="1"/></Style>'
            . '</Styles>' . "\n";
        $xml .= '<Worksheet ss:Name="Applications"><Table>' . "\n";

        $xml .= '<Row>';
        foreach ($headers as $h) {
            $xml .= '<Cell ss:StyleID="Header"><Data ss:Type="String">' . $this->xmlEscape($h) . '</Data></Cell>';
        }
        $xml .= '</Row>' . "\n";

        foreach ($rows as $a) {
            $statusLabel = ApplicationModel::STATUSES[$a['status'] ?? ''] ?? (string) ($a['status'] ?? '');
            $cells       = [
                $a['application_no'] ?? '',
                $statusLabel,
                $a['cycle_year'] ?? '',
                $a['title'] ?? '',
                $a['full_name'] ?? '',
                $this->exportDate($a['date_of_birth'] ?? null),
                $a['age_years'] ?? '',
                $a['age_months'] ?? '',
                $a['email'] ?? ($a['account_email'] ?? ''),
                $a['mobile'] ?? '',
                $a['phone_landline'] ?? '',
                $a['address_office'] ?? '',
                $a['address_residence'] ?? '',
                $a['qualifications'] ?? '',
                $a['enrolment_number'] ?? '',
                $this->exportDate($a['enrolment_date'] ?? null),
                $a['bar_council'] ?? '',
                $a['practice_years'] ?? '',
                $a['practice_months'] ?? '',
                $a['net_income_lakhs'] ?? '',
                $this->exportBool($a['is_bar_association_member'] ?? null),
                $a['bar_association_name'] ?? '',
                $a['reported_sc'] ?? '',
                $a['reported_hc'] ?? '',
                $a['reported_district'] ?? '',
                $a['unreported_sc'] ?? '',
                $a['unreported_hc'] ?? '',
                $a['unreported_district'] ?? '',
                $a['pro_bono_total'] ?? '',
                $a['amicus_total'] ?? '',
                $this->exportBool($a['is_first_generation'] ?? null),
                $a['academic_articles_count'] ?? '',
                $a['academic_books_count'] ?? '',
                $a['teaching_assignments_count'] ?? '',
                $a['guest_lectures_count'] ?? '',
                $a['nature_of_practice'] ?? '',
                $a['field_of_law'] ?? '',
                $this->exportBool($a['applied_mhc_earlier'] ?? null),
                $this->exportDate($a['applied_mhc_date'] ?? null),
                $a['applied_mhc_status'] ?? '',
                $this->exportBool($a['applied_other_court'] ?? null),
                $this->exportDate($a['applied_other_date'] ?? null),
                $a['applied_other_details'] ?? '',
                $this->exportBool($a['fir_lodged'] ?? null),
                $a['fir_details'] ?? '',
                $this->exportBool($a['criminal_case_party'] ?? null),
                $a['criminal_case_details'] ?? '',
                $this->exportBool($a['bar_council_proceedings'] ?? null),
                $a['bar_council_details'] ?? '',
                $a['general_health'] ?? '',
                $a['other_information'] ?? '',
                $a['submitted_at'] ?? '',
                $a['reviewed_at'] ?? '',
                $a['review_remarks'] ?? '',
                $a['current_step'] ?? '',
            ];

            $xml .= '<Row>';
            foreach ($cells as $cell) {
                $type = is_numeric($cell) && $cell !== '' && ! preg_match('/^0\d/', (string) $cell)
                    ? 'Number'
                    : 'String';
                // Keep long text / mixed IDs as strings
                if ($type === 'Number' && (str_contains((string) $cell, ' ') || strlen((string) $cell) > 12)) {
                    $type = 'String';
                }
                $xml .= '<Cell><Data ss:Type="' . $type . '">' . $this->xmlEscape((string) $cell) . '</Data></Cell>';
            }
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table></Worksheet></Workbook>';

        $filename = 'SAD_applications_' . date('Ymd_His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($xml);
    }

    /**
     * @return array{
     *   status: string,
     *   ageMin: ?int,
     *   ageMax: ?int,
     *   experienceMin: ?int,
     *   natureOfPractice: string,
     *   fieldOfLaw: string,
     *   firstGeneration: string,
     *   hasActiveFilters: bool
     * }
     */
    private function applicationListFilters(): array
    {
        $status           = (string) ($this->request->getGet('status') ?? '');
        $ageMin           = $this->nullableIntGet('age_min');
        $ageMax           = $this->nullableIntGet('age_max');
        $experienceMin    = $this->nullableIntGet('experience_min');
        $natureOfPractice = trim((string) ($this->request->getGet('nature_of_practice') ?? ''));
        $fieldOfLaw       = trim((string) ($this->request->getGet('field_of_law') ?? ''));
        $firstGeneration  = trim((string) ($this->request->getGet('first_generation') ?? ''));
        if (! in_array($firstGeneration, ['1', '0'], true)) {
            $firstGeneration = '';
        }
        if ($status !== '' && ! array_key_exists($status, ApplicationModel::STATUSES)) {
            $status = '';
        }

        $hasActiveFilters = $status !== ''
            || $ageMin !== null
            || $ageMax !== null
            || $experienceMin !== null
            || $natureOfPractice !== ''
            || $fieldOfLaw !== ''
            || $firstGeneration !== '';

        return [
            'status'           => $status,
            'ageMin'           => $ageMin,
            'ageMax'           => $ageMax,
            'experienceMin'    => $experienceMin,
            'natureOfPractice' => $natureOfPractice,
            'fieldOfLaw'       => $fieldOfLaw,
            'firstGeneration'  => $firstGeneration,
            'hasActiveFilters' => $hasActiveFilters,
        ];
    }

    /**
     * @param array{
     *   status: string,
     *   ageMin: ?int,
     *   ageMax: ?int,
     *   experienceMin: ?int,
     *   natureOfPractice: string,
     *   fieldOfLaw: string,
     *   firstGeneration: string
     * } $list
     */
    private function buildApplicationListQuery(array $list): ApplicationModel
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));

        $model = model(ApplicationModel::class);
        $model->select('applications.*, users.name as applicant_account_name, users.email as account_email')
            ->join('users', 'users.id = applications.user_id', 'left');

        if ($list['status'] !== '' && array_key_exists($list['status'], ApplicationModel::STATUSES)) {
            $model->where('applications.status', $list['status']);
        } else {
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

        if ($list['ageMin'] !== null) {
            $model->where('applications.age_years >=', $list['ageMin']);
        }
        if ($list['ageMax'] !== null) {
            $model->where('applications.age_years <=', $list['ageMax']);
        }
        if ($list['experienceMin'] !== null) {
            $model->where('applications.practice_years >=', $list['experienceMin']);
        }
        if ($list['natureOfPractice'] !== '') {
            $model->like('applications.nature_of_practice', $list['natureOfPractice'], 'both', null, true);
        }
        if ($list['fieldOfLaw'] !== '') {
            $model->like('applications.field_of_law', $list['fieldOfLaw'], 'both', null, true);
        }
        if ($list['firstGeneration'] === '1') {
            $model->groupStart()
                ->where('applications.is_first_generation', true)
                ->orWhere('applications.is_first_generation', 1)
                ->orWhere('applications.is_first_generation', 't')
                ->orWhere('applications.is_first_generation', '1')
                ->groupEnd();
        } elseif ($list['firstGeneration'] === '0') {
            $model->groupStart()
                ->where('applications.is_first_generation', false)
                ->orWhere('applications.is_first_generation', 0)
                ->orWhere('applications.is_first_generation', 'f')
                ->orWhere('applications.is_first_generation', '0')
                ->groupEnd();
        }

        return $model;
    }

    /**
     * @param array<string, mixed> $list
     */
    private function exportQueryString(array $list, string $q): string
    {
        $params = array_filter([
            'q'                  => $q !== '' ? $q : null,
            'status'             => $list['status'] !== '' ? $list['status'] : null,
            'age_min'            => $list['ageMin'],
            'age_max'            => $list['ageMax'],
            'experience_min'     => $list['experienceMin'],
            'nature_of_practice' => $list['natureOfPractice'] !== '' ? $list['natureOfPractice'] : null,
            'field_of_law'       => $list['fieldOfLaw'] !== '' ? $list['fieldOfLaw'] : null,
            'first_generation'   => $list['firstGeneration'] !== '' ? $list['firstGeneration'] : null,
        ], static fn ($v) => $v !== null && $v !== '');

        return $params === [] ? '' : ('?' . http_build_query($params));
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function exportBool(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if ($value === true || $value === 1 || $value === '1' || $value === 't' || $value === 'true') {
            return 'Yes';
        }
        if ($value === false || $value === 0 || $value === '0' || $value === 'f' || $value === 'false') {
            return 'No';
        }

        return (string) $value;
    }

    private function exportDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $s = (string) $value;

        return substr($s, 0, 10);
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
