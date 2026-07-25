<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Libraries\PdfService;
use App\Libraries\UploadService;
use App\Models\ApplicationModel;
use App\Models\ApplicationSequenceModel;
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
    protected ApplicationModel $apps;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->apps = model(ApplicationModel::class);
        helper(['form', 'url', 'sad']);
    }

    public function start()
    {
        $userId = (int) session()->get('user_id');
        $draft  = $this->apps->findDraftForUser($userId);

        if ($draft) {
            return redirect()->to('/applicant/application/step/' . (int) $draft['current_step']);
        }

        // Only one active non-final application at a time
        $open = $this->apps->where('user_id', $userId)
            ->whereIn('status', ApplicationModel::inProcessStatuses())
            ->first();

        if ($open) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'You already have an application under process (' . $open['application_no'] . ').');
        }

        $contact = $this->registrationContact($userId);

        $id = $this->apps->insert([
            'user_id'      => $userId,
            'status'       => ApplicationModel::STATUS_DRAFT,
            'current_step' => 1,
            'email'        => $contact['email'],
            'mobile'       => $contact['mobile'],
            'full_name'    => session()->get('name'),
        ]);

        model(AuditLogModel::class)->log('application_created', $userId, (int) $id);
        model(ApplicationStatusHistoryModel::class)->record((int) $id, null, ApplicationModel::STATUS_DRAFT, $userId, 'Draft created');

        return redirect()->to('/applicant/application/step/1');
    }

    public function step(int $step = 1)
    {
        $step = max(1, min(ApplicationModel::TOTAL_STEPS, $step));
        $app  = $this->requireEditableDraft();

        $data = [
            'title' => 'Application – Step ' . $step,
            'app'   => $this->apps->withDecoded($app),
            'step'  => $step,
            'steps' => sad_step_labels(),
        ];

        if (in_array($step, [3, 4], true)) {
            $data['l1']   = model(FormatL1Model::class)->forApplication((int) $app['id']);
            $data['l2']   = model(FormatL2Model::class)->forApplication((int) $app['id']);
            $data['l3pb'] = model(FormatL3ProBonoModel::class)->forApplication((int) $app['id']);
            $data['l3am'] = model(FormatL3AmicusModel::class)->forApplication((int) $app['id']);
            $data['l4']   = model(FormatL4Model::class)->forApplication((int) $app['id']);
        }

        return view('applicant/application/step' . $step, $data);
    }

    public function saveStep(int $step)
    {
        $app    = $this->requireEditableDraft();
        $userId = (int) session()->get('user_id');
        $step   = max(1, min(ApplicationModel::TOTAL_STEPS, $step));
        $post   = $this->request->getPost();

        $data = $this->mapStepData($step, $post, $app);

        if ($step === 1 && ! empty($data['date_of_birth'])) {
            $data['age_years'] = $this->apps->calculateAgeAsOn($data['date_of_birth'], '2026-01-01');
        }

        $data = $this->apps->encodeListFields($data);
        $data['current_step'] = max((int) $app['current_step'], $step);

        // Handle boolean-like fields for PostgreSQL
        foreach ($data as $k => $v) {
            if (is_bool($v)) {
                $data[$k] = $v ? 't' : 'f';
            }
        }

        $this->apps->update($app['id'], $data);

        // Nested format tables
        if ($step === 3) {
            $this->saveFormatRows((int) $app['id'], $post);
        }
        if ($step === 4) {
            $this->saveFormatL3L4((int) $app['id'], $post);
        }

        // File uploads on step 7
        if ($step === 7) {
            $uploadResult = $this->handleUploads((int) $app['id']);
            if ($uploadResult !== true) {
                return redirect()->back()->withInput()->with('error', $uploadResult);
            }
        }

        model(AuditLogModel::class)->log('application_step_saved', $userId, (int) $app['id'], ['step' => $step]);

        $action = $this->request->getPost('action') ?? 'save';

        if ($action === 'submit' && $step === ApplicationModel::TOTAL_STEPS) {
            return $this->submitApplication((int) $app['id']);
        }

        if ($action === 'next' && $step < ApplicationModel::TOTAL_STEPS) {
            $this->apps->update($app['id'], ['current_step' => $step + 1]);

            return redirect()->to('/applicant/application/step/' . ($step + 1))
                ->with('success', 'Step ' . $step . ' saved.');
        }

        if ($action === 'prev' && $step > 1) {
            return redirect()->to('/applicant/application/step/' . ($step - 1));
        }

        return redirect()->to('/applicant/application/step/' . $step)
            ->with('success', 'Draft saved successfully.');
    }

    public function view(int $id)
    {
        $userId = (int) session()->get('user_id');
        $app    = $this->apps->findForUser($userId, $id);

        if (! $app) {
            return redirect()->to('/applicant/dashboard')->with('error', 'Application not found.');
        }

        return view('applicant/application/view', [
            'title'   => 'Application ' . ($app['application_no'] ?? '#' . $id),
            'app'     => $this->apps->withDecoded($app),
            'history' => model(ApplicationStatusHistoryModel::class)->forApplication($id),
            'l1'      => model(FormatL1Model::class)->forApplication($id),
            'l2'      => model(FormatL2Model::class)->forApplication($id),
            'l3pb'    => model(FormatL3ProBonoModel::class)->forApplication($id),
            'l3am'    => model(FormatL3AmicusModel::class)->forApplication($id),
            'l4'      => model(FormatL4Model::class)->forApplication($id),
        ]);
    }

    public function downloadPdf(int $id)
    {
        $userId = (int) session()->get('user_id');
        $app    = $this->apps->findForUser($userId, $id);

        if (! $app) {
            return redirect()->to('/applicant/dashboard')->with('error', 'Application not found.');
        }

        $pdf = new PdfService();
        if (empty($app['generated_pdf_path']) || ! is_file(WRITEPATH . 'uploads/' . $app['generated_pdf_path'])) {
            $path = $pdf->generateApplicationPdf($this->apps->withDecoded($app), [
                'l1'   => model(FormatL1Model::class)->forApplication($id),
                'l2'   => model(FormatL2Model::class)->forApplication($id),
                'l3pb' => model(FormatL3ProBonoModel::class)->forApplication($id),
                'l3am' => model(FormatL3AmicusModel::class)->forApplication($id),
                'l4'   => model(FormatL4Model::class)->forApplication($id),
            ]);
            $this->apps->update($id, ['generated_pdf_path' => $path]);
            $app['generated_pdf_path'] = $path;
        }

        $pdf->stream($app['generated_pdf_path'], ($app['application_no'] ?? 'application') . '.pdf');
    }

    protected function submitApplication(int $id)
    {
        $userId = (int) session()->get('user_id');
        $app    = $this->apps->find($id);

        $errors = $this->validateForSubmit($app);
        if ($errors) {
            return redirect()->to('/applicant/application/step/7')
                ->with('error', implode(' ', $errors));
        }

        $appNo = $app['application_no'];
        if (empty($appNo)) {
            $appNo = model(ApplicationSequenceModel::class)->nextNumber('SAD', 2026);
        }

        $from = $app['status'];
        $this->apps->update($id, [
            'application_no'       => $appNo,
            'status'               => ApplicationModel::STATUS_SUBMITTED,
            'submitted_at'         => date('Y-m-d H:i:s'),
            'declaration_accepted' => 't',
            'instructions_accepted'=> 't',
            'declaration_date'     => date('Y-m-d'),
        ]);

        $submitNote = $from === ApplicationModel::STATUS_RETURNED
            ? 'Application resubmitted after correction'
            : 'Application submitted';

        model(ApplicationStatusHistoryModel::class)->record(
            $id,
            $from,
            ApplicationModel::STATUS_SUBMITTED,
            $userId,
            $submitNote
        );
        model(AuditLogModel::class)->log('application_submitted', $userId, $id, [
            'application_no' => $appNo,
            'resubmit'       => $from === ApplicationModel::STATUS_RETURNED,
        ]);

        // Generate PDF snapshot
        $fresh = $this->apps->withDecoded($this->apps->find($id));
        try {
            $path = (new PdfService())->generateApplicationPdf($fresh, [
                'l1'   => model(FormatL1Model::class)->forApplication($id),
                'l2'   => model(FormatL2Model::class)->forApplication($id),
                'l3pb' => model(FormatL3ProBonoModel::class)->forApplication($id),
                'l3am' => model(FormatL3AmicusModel::class)->forApplication($id),
                'l4'   => model(FormatL4Model::class)->forApplication($id),
            ]);
            $this->apps->update($id, ['generated_pdf_path' => $path]);
        } catch (\Throwable $e) {
            log_message('error', 'PDF generation failed: ' . $e->getMessage());
        }

        // Email + SMS notification
        $user = model(UserModel::class)->find($userId);
        (new NotificationService())->applicationSubmitted($fresh, $user ?: null);

        $msg = $from === ApplicationModel::STATUS_RETURNED
            ? 'Application resubmitted successfully. Application No: ' . $appNo
            : 'Application submitted successfully. Application No: ' . $appNo;

        return redirect()->to('/applicant/application/view/' . $id)->with('success', $msg);
    }

    protected function validateForSubmit(array $app): array
    {
        $errors = [];
        $required = [
            'full_name'         => 'Name of the applicant',
            'date_of_birth'     => 'Date of birth',
            'address_office'    => 'Office address',
            'address_residence' => 'Residence address',
            'mobile'            => 'Mobile number',
            'email'             => 'Email',
            'qualifications'    => 'Educational / professional qualifications',
            'enrolment_date'    => 'Date of enrolment',
            'enrolment_number'  => 'Enrolment number',
            'bar_council'       => 'Bar Council',
            'photo_path'        => 'Passport photograph',
            'signature_path'    => 'Signature',
            'enrolment_cert_path' => 'Enrolment certificate',
        ];

        foreach ($required as $field => $label) {
            if (empty($app[$field])) {
                $errors[] = $label . ' is required.';
            }
        }

        if (empty($this->request->getPost('declaration_accepted')) && empty($app['declaration_accepted'])) {
            $errors[] = 'You must accept the declaration.';
        }
        if (empty($this->request->getPost('instructions_accepted')) && empty($app['instructions_accepted'])) {
            $errors[] = 'You must confirm that you have read the instructions.';
        }

        // Name must match enrolment (soft check — exact match encouraged)
        if (! empty($app['full_name']) && strlen(trim($app['full_name'])) < 3) {
            $errors[] = 'Full name must match the enrolment certificate (no abbreviations).';
        }

        return $errors;
    }

    protected function requireEditableDraft(): array
    {
        $userId = (int) session()->get('user_id');
        $draft  = $this->apps->findDraftForUser($userId);

        if (! $draft) {
            // Create a fresh draft if none exists
            $contact = $this->registrationContact($userId);
            $id = $this->apps->insert([
                'user_id'      => $userId,
                'status'       => ApplicationModel::STATUS_DRAFT,
                'current_step' => 1,
                'email'        => $contact['email'],
                'mobile'       => $contact['mobile'],
                'full_name'    => session()->get('name'),
            ]);
            $draft = $this->apps->find($id);
        } else {
            // Backfill registration contact if missing on older drafts
            $draft = $this->ensureRegistrationContact($draft);
        }

        return $draft;
    }

    /**
     * Email and mobile captured at advocate registration (source of truth).
     *
     * @return array{email: string, mobile: string}
     */
    protected function registrationContact(int $userId): array
    {
        $user = model(UserModel::class)->find($userId);

        return [
            'email'  => (string) ($user['email'] ?? session()->get('email') ?? ''),
            'mobile' => (string) ($user['mobile'] ?? session()->get('mobile') ?? ''),
        ];
    }

    /**
     * Ensure draft carries registration email/mobile (read-only on the form).
     */
    protected function ensureRegistrationContact(array $draft): array
    {
        $contact = $this->registrationContact((int) $draft['user_id']);
        $patch   = [];

        if (empty($draft['email']) && $contact['email'] !== '') {
            $patch['email'] = $contact['email'];
        }
        if (empty($draft['mobile']) && $contact['mobile'] !== '') {
            $patch['mobile'] = $contact['mobile'];
        }

        if ($patch !== []) {
            $this->apps->update($draft['id'], $patch);
            $draft = array_merge($draft, $patch);
        }

        return $draft;
    }

    protected function mapStepData(int $step, array $post, array $app): array
    {
        $bool = static function ($v) {
            if ($v === null || $v === '') {
                return null;
            }

            return in_array($v, ['1', 'true', 'yes', 'on', 1, true], true);
        };

        switch ($step) {
            case 1:
                // Email & mobile are fixed from registration; ignore client tampering.
                $contact = $this->registrationContact((int) session()->get('user_id'));

                return [
                    'title'             => $post['title'] ?? null,
                    'full_name'         => trim($post['full_name'] ?? ''),
                    'date_of_birth'     => $post['date_of_birth'] ?: null,
                    'address_office'    => trim($post['address_office'] ?? ''),
                    'address_residence' => trim($post['address_residence'] ?? ''),
                    'phone_landline'    => trim($post['phone_landline'] ?? ''),
                    'mobile'            => $contact['mobile'] !== '' ? $contact['mobile'] : (string) ($app['mobile'] ?? ''),
                    'email'             => $contact['email'] !== '' ? $contact['email'] : (string) ($app['email'] ?? ''),
                    'qualifications'    => trim($post['qualifications'] ?? ''),
                ];
            case 2:
                return [
                    'enrolment_date'            => $post['enrolment_date'] ?: null,
                    'enrolment_number'          => trim($post['enrolment_number'] ?? ''),
                    'bar_council'               => trim($post['bar_council'] ?? ''),
                    'practice_years'            => (int) ($post['practice_years'] ?? 0),
                    'practice_months'           => (int) ($post['practice_months'] ?? 0),
                    'net_income_lakhs'          => $post['net_income_lakhs'] !== '' ? $post['net_income_lakhs'] : null,
                    'is_bar_association_member' => $bool($post['is_bar_association_member'] ?? null),
                    'bar_association_name'      => trim($post['bar_association_name'] ?? ''),
                ];
            case 3:
                return [
                    'reported_sc'         => (int) ($post['reported_sc'] ?? 0),
                    'reported_hc'         => (int) ($post['reported_hc'] ?? 0),
                    'reported_district'   => (int) ($post['reported_district'] ?? 0),
                    'unreported_sc'       => (int) ($post['unreported_sc'] ?? 0),
                    'unreported_hc'       => (int) ($post['unreported_hc'] ?? 0),
                    'unreported_district' => (int) ($post['unreported_district'] ?? 0),
                ];
            case 4:
                return [
                    'pro_bono_total'             => (int) ($post['pro_bono_total'] ?? 0),
                    'amicus_total'               => (int) ($post['amicus_total'] ?? 0),
                    'is_first_generation'        => $bool($post['is_first_generation'] ?? null),
                    'academic_articles_count'    => (int) ($post['academic_articles_count'] ?? 0),
                    'academic_books_count'       => (int) ($post['academic_books_count'] ?? 0),
                    'teaching_assignments_count' => (int) ($post['teaching_assignments_count'] ?? 0),
                    'guest_lectures_count'       => (int) ($post['guest_lectures_count'] ?? 0),
                ];
            case 5:
                $courts = [];
                if (! empty($post['court_name']) && is_array($post['court_name'])) {
                    foreach ($post['court_name'] as $i => $name) {
                        $courts[] = [
                            'court' => $name,
                            'from'  => $post['court_from'][$i] ?? '',
                            'to'    => $post['court_to'][$i] ?? '',
                        ];
                    }
                }
                $tribunals = [];
                if (! empty($post['tribunal_name']) && is_array($post['tribunal_name'])) {
                    foreach ($post['tribunal_name'] as $i => $name) {
                        $tribunals[] = [
                            'tribunal' => $name,
                            'from'     => $post['tribunal_from'][$i] ?? '',
                            'to'       => $post['tribunal_to'][$i] ?? '',
                        ];
                    }
                }

                return [
                    'courts_practiced'    => $courts,
                    'tribunals_practiced' => $tribunals,
                    'nature_of_practice'  => trim($post['nature_of_practice'] ?? ''),
                    'field_of_law'        => trim($post['field_of_law'] ?? ''),
                ];
            case 6:
                return [
                    'applied_mhc_earlier'      => $bool($post['applied_mhc_earlier'] ?? null),
                    'applied_mhc_date'         => $post['applied_mhc_date'] ?: null,
                    'applied_mhc_status'       => trim($post['applied_mhc_status'] ?? ''),
                    'applied_other_court'      => $bool($post['applied_other_court'] ?? null),
                    'applied_other_details'    => trim($post['applied_other_details'] ?? ''),
                    'fir_lodged'               => $bool($post['fir_lodged'] ?? null),
                    'fir_details'              => trim($post['fir_details'] ?? ''),
                    'criminal_case_party'      => $bool($post['criminal_case_party'] ?? null),
                    'criminal_case_details'    => trim($post['criminal_case_details'] ?? ''),
                    'bar_council_proceedings'  => $bool($post['bar_council_proceedings'] ?? null),
                    'bar_council_details'      => trim($post['bar_council_details'] ?? ''),
                    'general_health'           => trim($post['general_health'] ?? ''),
                    'other_information'        => trim($post['other_information'] ?? ''),
                ];
            case 7:
                return [
                    'declaration_name'       => trim($post['declaration_name'] ?? ($app['full_name'] ?? '')),
                    'declaration_accepted'   => $bool($post['declaration_accepted'] ?? null) ?? false,
                    'instructions_accepted'  => $bool($post['instructions_accepted'] ?? null) ?? false,
                    'declaration_date'       => date('Y-m-d'),
                ];
        }

        return [];
    }

    protected function saveFormatRows(int $appId, array $post): void
    {
        $l1Rows = [];
        if (! empty($post['l1_case_number']) && is_array($post['l1_case_number'])) {
            foreach ($post['l1_case_number'] as $i => $cn) {
                $l1Rows[] = [
                    'court_level'        => $post['l1_court_level'][$i] ?? 'madras_hc',
                    'court_name'         => $post['l1_court_name'][$i] ?? '',
                    'case_number'        => $cn,
                    'citation'           => $post['l1_citation'][$i] ?? '',
                    'cause_title'        => $post['l1_cause_title'][$i] ?? '',
                    'decided_on'         => $post['l1_decided_on'][$i] ?: null,
                    'legal_formulation'  => $post['l1_legal_formulation'][$i] ?? '',
                ];
            }
        }
        model(FormatL1Model::class)->replaceForApplication($appId, $l1Rows);

        $l2Rows = [];
        if (! empty($post['l2_case_number']) && is_array($post['l2_case_number'])) {
            foreach ($post['l2_case_number'] as $i => $cn) {
                $l2Rows[] = [
                    'court_level'        => $post['l2_court_level'][$i] ?? 'madras_hc',
                    'court_name'         => $post['l2_court_name'][$i] ?? '',
                    'case_number'        => $cn,
                    'citation'           => $post['l2_citation'][$i] ?? '',
                    'cause_title'        => $post['l2_cause_title'][$i] ?? '',
                    'decided_on'         => $post['l2_decided_on'][$i] ?: null,
                    'legal_formulation'  => $post['l2_legal_formulation'][$i] ?? '',
                ];
            }
        }
        model(FormatL2Model::class)->replaceForApplication($appId, $l2Rows);
    }

    protected function saveFormatL3L4(int $appId, array $post): void
    {
        $pb = [];
        if (! empty($post['pb_case_number']) && is_array($post['pb_case_number'])) {
            foreach ($post['pb_case_number'] as $i => $cn) {
                $pb[] = [
                    'court_tribunal'  => $post['pb_court'][$i] ?? '',
                    'case_number'     => $cn,
                    'cause_title'     => $post['pb_cause_title'][$i] ?? '',
                    'decided_on'      => $post['pb_decided_on'][$i] ?: null,
                    'society_benefit' => $post['pb_society_benefit'][$i] ?? '',
                ];
            }
        }
        model(FormatL3ProBonoModel::class)->replaceForApplication($appId, $pb);

        $am = [];
        if (! empty($post['am_case_number']) && is_array($post['am_case_number'])) {
            foreach ($post['am_case_number'] as $i => $cn) {
                $am[] = [
                    'court_tribunal' => $post['am_court'][$i] ?? '',
                    'case_number'    => $cn,
                    'cause_title'    => $post['am_cause_title'][$i] ?? '',
                    'decided_on'     => $post['am_decided_on'][$i] ?: null,
                    'reportable'     => $post['am_reportable'][$i] ?? '',
                ];
            }
        }
        model(FormatL3AmicusModel::class)->replaceForApplication($appId, $am);

        $l4 = [];
        if (! empty($post['l4_topic']) && is_array($post['l4_topic'])) {
            foreach ($post['l4_topic'] as $i => $topic) {
                $l4[] = [
                    'topic'               => $topic,
                    'teaching_assignment' => $post['l4_teaching'][$i] ?? '',
                    'guest_lectures'      => $post['l4_guest'][$i] ?? '',
                    'other_details'       => $post['l4_other'][$i] ?? '',
                ];
            }
        }
        model(FormatL4Model::class)->replaceForApplication($appId, $l4);
    }

    /**
     * @return true|string
     */
    protected function handleUploads(int $appId)
    {
        $uploader = new UploadService();
        $map      = [
            'photo'          => 'photo_path',
            'signature'      => 'signature_path',
            'enrolment_cert' => 'enrolment_cert_path',
            'format_l1'      => 'format_l1_path',
            'format_l2'      => 'format_l2_path',
            'format_l3i'     => 'format_l3i_path',
            'format_l3ii'    => 'format_l3ii_path',
            'format_l4'      => 'format_l4_path',
        ];

        $updates = [];
        foreach ($map as $field => $column) {
            $file = $this->request->getFile($field);
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $result = $uploader->store($file, $field, $appId);
            if (! $result['ok']) {
                return $result['error'];
            }
            $updates[$column] = $result['path'];
        }

        if ($updates) {
            $this->apps->update($appId, $updates);
        }

        return true;
    }
}
