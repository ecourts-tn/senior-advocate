<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Libraries\PdfService;
use App\Libraries\UploadService;
use App\Models\AdvocateDbModel;
use App\Models\ApplicationModel;
use App\Models\ApplicationSequenceModel;
use App\Models\ApplicationStatusHistoryModel;
use App\Models\AuditLogModel;
use App\Models\DesignationNotificationModel;
use App\Models\FormatL1Model;
use App\Models\FormatL2Model;
use App\Models\FormatL3AmicusModel;
use App\Models\FormatL3ProBonoModel;
use App\Models\FormatL4Model;
use App\Models\ApplicationMasterLink;
use App\Models\MasterRegistry;
use App\Models\UserModel;

class ApplicationController extends BaseController
{
    protected ApplicationModel $apps;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->apps = model(ApplicationModel::class);
        helper(['form', 'url', 'ssa']);
        // Never cache application forms — browser Back must revalidate with the server
        $this->preventSensitiveCache();
    }

    /**
     * Stop browsers (and bfcache) from restoring editable form pages after submit.
     */
    protected function preventSensitiveCache(): void
    {
        $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }

    /**
     * Start a new application: show instructions first.
     * The form is only created after the advocate accepts instructions (POST).
     */
    // public function start()
    // {
    //     $userId = (int) session()->get('user_id');
    //     $draft  = $this->apps->findDraftForUser($userId);

    //     if ($draft) {
    //         return redirect()->to('/applicant/application/step/' . (int) $draft['current_step']);
    //     }

    //     if (! $this->apps->canStartNewApplication($userId)) {
    //         $existing = $this->apps->findForUserCycle($userId)
    //             ?? $this->apps->where('user_id', $userId)
    //                 ->whereIn('status', ApplicationModel::inProcessStatuses())
    //                 ->first();
    //         $year = ApplicationModel::currentCycleYear();
    //         $msg  = 'You may submit only one application for the ' . $year
    //             . ' designation cycle. '
    //             . ($existing
    //                 ? 'Existing application: ' . ($existing['application_no'] ?? ('#' . $existing['id'])) . ' (' . (ApplicationModel::STATUSES[$existing['status']] ?? $existing['status']) . ').'
    //                 : '');

    //         return redirect()->to('/applicant/dashboard')->with('error', trim($msg));
    //     }

    //     return view('applicant/application/instructions', [
    //         'title'      => 'Instructions — Start Application',
    //         'cycleYear'  => ApplicationModel::currentCycleYear(),
    //         'editWindow' => ApplicationModel::editWindowInfo(),
    //     ]);
    // }

    public function start()
    {
        $userId = (int) session()->get('user_id');

        $draft = $this->apps->findDraftForUser($userId);

        /*
         * If the applicant already has a draft,
         * allow them to continue the draft.
         */
        if ($draft) {
            return redirect()->to(
                '/applicant/application/step/' . (int) $draft['current_step']
            );
        }

        // Gate: application period must be open (date + time on active notification)
        $period = DesignationNotificationModel::applicationPeriodInfo();
        if (empty($period['open'])) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', $period['message'] ?? 'The application submission period is not open.');
        }

        $year = ApplicationModel::currentCycleYear();

        /*
         * Check whether the applicant has already
         * submitted an application for this cycle / notification.
         */
        if (! $this->apps->canStartNewApplication($userId)) {
            $existing = null;
            if (! empty($period['notification_id'])) {
                $existing = $this->apps->findForUserNotification($userId, (int) $period['notification_id']);
            }
            $existing ??= $this->apps->findForUserCycle($userId)
                ?? $this->apps->where('user_id', $userId)
                    ->whereIn(
                        'status',
                        ApplicationModel::inProcessStatuses()
                    )
                    ->first();

            $label = $period['notification_number'] !== ''
                ? 'notification ' . $period['notification_number']
                : $year . ' designation cycle';

            $msg = 'You may submit only one application for '
                . $label
                . '. '
                . (
                    $existing
                        ? 'Existing application: '
                            . (
                                $existing['application_no']
                                ?? ('#' . $existing['id'])
                            )
                            . ' ('
                            . (
                                ApplicationModel::STATUSES[
                                    $existing['status']
                                ]
                                ?? $existing['status']
                            )
                            . ').'
                        : ''
                );

            return redirect()
                ->to('/applicant/dashboard')
                ->with('error', trim($msg));
        }

        return view('applicant/application/instructions', [
            'title'      => 'Instructions — Start Application',
            'cycleYear'  => $year,
            'editWindow' => ApplicationModel::editWindowInfo(),

            'applicationStartDate' => $period['application_start_date'],
            'applicationLastDate'  => $period['application_end_date'],
            'notificationNumber'   => $period['notification_number'],
            'notificationDate'     => $period['notification_date'],
            'periodMessage'        => $period['message'] ?? '',
        ]);
    }

    /**
     * Accept instructions and create a new draft application.
     */
    public function acceptInstructions()
    {
        $userId = (int) session()->get('user_id');
        $draft  = $this->apps->findDraftForUser($userId);

        if ($draft) {
            return redirect()->to('/applicant/application/step/' . (int) $draft['current_step']);
        }

        // Re-check period at create time (window may have closed after instructions page)
        $period = DesignationNotificationModel::applicationPeriodInfo();
        if (empty($period['open'])) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', $period['message'] ?? 'The application submission period is not open.');
        }

        if (! $this->apps->canStartNewApplication($userId)) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'You may submit only one application for the current notification / cycle.');
        }

        $accepted = $this->request->getPost('instructions_accepted');
        if (! in_array($accepted, ['1', 'on', 'true', 'yes'], true)) {
            return redirect()->to('/applicant/application/start')
                ->with('error', 'You must read and accept the instructions before starting the application.');
        }

        $user    = model(UserModel::class)->find($userId);
        $contact = $this->registrationContact($userId);
        $year    = ApplicationModel::currentCycleYear();

        $id = $this->apps->insert([
            'user_id'               => $userId,
            'status'                => ApplicationModel::STATUS_DRAFT,
            'cycle_year'            => $year,
            'notification_id'       => $period['notification_id'] ?? null,
            'current_step'          => 1,
            'email'                 => $contact['email'],
            'mobile'                => $contact['mobile'],
            'full_name'             => session()->get('name'),
            'enrolment_number'      => $user['enrolment_number'] ?? null,
            'instructions_accepted' => 't',
        ]);

        model(AuditLogModel::class)->log('application_created', $userId, (int) $id, [
            'instructions_accepted' => true,
            'cycle_year'            => $year,
            'notification_id'       => $period['notification_id'] ?? null,
            'notification_number'   => $period['notification_number'] ?? '',
        ]);
        model(ApplicationStatusHistoryModel::class)->record(
            (int) $id,
            null,
            ApplicationModel::STATUS_DRAFT,
            $userId,
            'Draft created after accepting instructions'
        );

        return redirect()->to('/applicant/application/step/1')
            ->with('success', 'Instructions accepted. You may now fill the application form.');
    }

    public function step(int $step = 1)
    {
        $this->preventSensitiveCache();
        $step = max(1, min(ApplicationModel::TOTAL_STEPS, $step));
        $app  = $this->requireEditableApplication();
        if ($app === null) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'No editable application found. You can edit only drafts, returned applications, or during an admin-opened edit window.');
        }

        $app = $this->apps->withDecoded($app);

        $ageAsOn = ApplicationModel::ageAsOnDate($app);

        // Normalise DOB and always recompute age years/months/days for display (readonly fields).
        if ($step === 1 && ! empty($app['date_of_birth'])) {
            $app['date_of_birth'] = substr((string) $app['date_of_birth'], 0, 10);
            $ageParts             = $this->apps->calculateAgePartsAsOn($app['date_of_birth'], $ageAsOn);
            if ($ageParts !== null) {
                $app['age_years']  = $ageParts['years'];
                $app['age_months'] = $ageParts['months'];
                $app['age_days']   = $ageParts['days'];
            }
        }

        // Normalise enrolment date for the form (practice years/months are entered by applicant).
        if ($step === 2 && ! empty($app['enrolment_date'])) {
            $app['enrolment_date'] = substr((string) $app['enrolment_date'], 0, 10);
        }

        // Auto-populate enrolment (and related fields) from registration / advocate_db.
        $enrolmentFromAccount = null;
        if ($step === 2) {
            $user = model(UserModel::class)->find((int) session()->get('user_id'));
            $enrolmentFromAccount = trim((string) ($user['enrolment_number'] ?? session()->get('enrolment_number') ?? ''));
            if ($enrolmentFromAccount === '') {
                $enrolmentFromAccount = null;
            }

            if (empty($app['enrolment_number']) && $enrolmentFromAccount) {
                $app['enrolment_number'] = $enrolmentFromAccount;
            }

            // Prefill date / bar council from advocate master when still blank.
            // Practice duration is NOT auto-filled — applicant enters it manually.
            $lookupNo = trim((string) ($app['enrolment_number'] ?? $enrolmentFromAccount ?? ''));
            if ($lookupNo !== '' && (empty($app['enrolment_date']) || empty($app['bar_council']))) {
                $adv = model(AdvocateDbModel::class)->findByEnrolment($lookupNo);
                if ($adv) {
                    $prefill = model(AdvocateDbModel::class)->toRegistrationPrefill($adv);
                    if (empty($app['enrolment_date']) && ! empty($prefill['enrolment_date'])) {
                        $app['enrolment_date'] = $prefill['enrolment_date'];
                    }
                    if (empty($app['bar_council']) && ! empty($prefill['bar'])) {
                        $app['bar_council'] = $prefill['bar'];
                    }
                }
            }
        }

        $data = [
            'title'        => 'Application – Step ' . $step,
            'app'          => $app,
            'step'         => $step,
            'steps'        => ssa_step_labels(),
            'ageAsOnDate'  => $ageAsOn,
            'ageAsOnLabel' => ApplicationModel::ageAsOnLabel($app),
        ];

        if ($step === 2) {
            $data['enrolmentFromAccount'] = $enrolmentFromAccount;
        }

        if (in_array($step, [1, 5], true)) {
            $data['lookupOptions'] = $this->lookupOptionsForSteps();
        }

        $data['editWindow'] = ApplicationModel::editWindowInfo();
        $data['isEditWindowEdit'] = ! in_array($app['status'], [
            ApplicationModel::STATUS_DRAFT,
            ApplicationModel::STATUS_RETURNED,
        ], true);

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
        $this->preventSensitiveCache();
        $postedId = (int) ($this->request->getPost('application_id') ?? 0);
        $app      = $this->requireEditableApplication($postedId > 0 ? $postedId : null);
        if ($app === null) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'This application cannot be edited. It may already have been submitted.');
        }

        $userId = (int) session()->get('user_id');
        $step   = max(1, min(ApplicationModel::TOTAL_STEPS, $step));
        $post   = $this->request->getPost();

        // Re-load from DB immediately before write (guards browser-Back + stale forms)
        $fresh = $this->apps->find((int) $app['id']);
        if (
            ! $fresh
            || (int) ($fresh['user_id'] ?? 0) !== $userId
            || ! ApplicationModel::isEditableByApplicant($fresh)
        ) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'This application can no longer be modified after submission.');
        }
        $app = $fresh;

        $data = $this->mapStepData($step, $post, $app);

        $ageAsOn = ApplicationModel::ageAsOnDate($app);

        if ($step === 1 && ! empty($data['date_of_birth'])) {
            $ageParts = $this->apps->calculateAgePartsAsOn($data['date_of_birth'], $ageAsOn);
            if ($ageParts !== null) {
                $data['age_years']  = $ageParts['years'];
                $data['age_months'] = $ageParts['months'];
                $data['age_days']   = $ageParts['days'];
            } else {
                $data['age_years']  = null;
                $data['age_months'] = null;
                $data['age_days']   = null;
            }
        }

        // Practice years/months are entered manually by the applicant.
        if ($step === 2 && ! empty($data['enrolment_date'])) {
            $data['enrolment_date'] = substr((string) $data['enrolment_date'], 0, 10);
        }

        // Pass notification context so cumulative court experience uses the correct "as on" date
        if ($step === 5) {
            $data['notification_id'] = $app['notification_id'] ?? null;
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

        // One-to-many multi-select masters (also keep denormalised text columns in $data)
        if ($step === 1 || $step === 5) {
            $this->syncMultiMasters((int) $app['id'], $step, $post);
        }

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
            // Final gate before status change (stale Back-button form)
            $fresh = $this->apps->find((int) $app['id']);
            if (! $fresh || ! ApplicationModel::isEditableByApplicant($fresh)) {
                return redirect()->to('/applicant/dashboard')
                    ->with('error', 'This application has already been submitted and cannot be submitted again.');
            }

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

        $savedMsg = in_array($app['status'], [
            ApplicationModel::STATUS_DRAFT,
            ApplicationModel::STATUS_RETURNED,
        ], true)
            ? 'Draft saved successfully.'
            : 'Changes saved. Use Submit on the last step to resubmit the application.';

        return redirect()->to('/applicant/application/step/' . $step)
            ->with('success', $savedMsg);
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

        // Always generate at request time from the current template (no disk snapshot).
        (new PdfService())->streamApplicationPdf(
            $this->apps->withDecoded($app),
            [
                'l1'   => model(FormatL1Model::class)->forApplication($id),
                'l2'   => model(FormatL2Model::class)->forApplication($id),
                'l3pb' => model(FormatL3ProBonoModel::class)->forApplication($id),
                'l3am' => model(FormatL3AmicusModel::class)->forApplication($id),
                'l4'   => model(FormatL4Model::class)->forApplication($id),
            ],
            ($app['application_no'] ?? 'application') . '.pdf'
        );
    }

    protected function submitApplication(int $id)
    {
        $userId = (int) session()->get('user_id');
        $app    = $this->apps->find($id);

        if (
            ! $app
            || (int) ($app['user_id'] ?? 0) !== $userId
            || ! ApplicationModel::isEditableByApplicant($app)
        ) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'This application has already been submitted and cannot be modified.');
        }

        $errors = $this->validateForSubmit($app);
        if ($errors) {
            return redirect()->to('/applicant/application/step/7')
                ->with('error', implode(' ', $errors));
        }

        $cycleYear = (int) ($app['cycle_year'] ?? 0) ?: ApplicationModel::currentCycleYear();
        $appNo     = $app['application_no'];
        if (empty($appNo)) {
            $appNo = model(ApplicationSequenceModel::class)->nextNumber('MHC/SSA', $cycleYear);
        }

        $from = $app['status'];
        $isResubmit = in_array($from, [
            ApplicationModel::STATUS_RETURNED,
            ApplicationModel::STATUS_SUBMITTED,
            ApplicationModel::STATUS_UNDER_REVIEW,
            ApplicationModel::STATUS_PENDING_APPROVAL,
        ], true);

        // Atomic-ish guard: only draft/returned (or edit-window statuses) may transition to submitted
        $allowedFrom = array_values(array_unique(array_merge(
            [ApplicationModel::STATUS_DRAFT, ApplicationModel::STATUS_RETURNED],
            ApplicationModel::editWindowStatuses()
        )));
        $db = db_connect();
        $db->table('applications')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->whereIn('status', $allowedFrom)
            ->update([
                'application_no'        => $appNo,
                'status'                => ApplicationModel::STATUS_SUBMITTED,
                'cycle_year'            => $cycleYear,
                'submitted_at'          => date('Y-m-d H:i:s'),
                'declaration_accepted'  => 't',
                'instructions_accepted' => 't',
                'declaration_date'      => date('Y-m-d'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ]);

        if ($db->affectedRows() < 1) {
            return redirect()->to('/applicant/dashboard')
                ->with('error', 'This application has already been submitted and cannot be modified.');
        }

        $submitNote = match (true) {
            $from === ApplicationModel::STATUS_RETURNED => 'Application resubmitted after correction',
            $isResubmit && $from !== ApplicationModel::STATUS_DRAFT => 'Application updated during edit window and resubmitted',
            default => 'Application submitted',
        };

        model(ApplicationStatusHistoryModel::class)->record(
            $id,
            $from,
            ApplicationModel::STATUS_SUBMITTED,
            $userId,
            $submitNote
        );
        model(AuditLogModel::class)->log('application_submitted', $userId, $id, [
            'application_no' => $appNo,
            'resubmit'       => $isResubmit && $from !== ApplicationModel::STATUS_DRAFT,
            'from_status'    => $from,
            'cycle_year'     => $cycleYear,
        ]);

        // PDF is generated dynamically on download — no snapshot stored at submit.
        $fresh = $this->apps->withDecoded($this->apps->find($id));

        // Email + SMS notification
        $user = model(UserModel::class)->find($userId);
        (new NotificationService())->applicationSubmitted($fresh, $user ?: null);

        $msg = ($isResubmit && $from !== ApplicationModel::STATUS_DRAFT)
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
            'address_office'    => 'Office Address',
            'address_residence' => 'Residential Address',
            'mobile'            => 'Mobile number',
            'email'             => 'Email',
            'qualifications'    => 'Educational / professional qualifications',
            'enrolment_date'    => 'Date of enrolment',
            'enrolment_number'  => 'Enrolment number',
            'bar_council'       => 'Bar Council',
            'photo_path'          => 'Passport photograph',
            'signature_path'      => 'Signature',
            'enrolment_cert_path' => 'Enrolment certificate',
            'age_proof_path'      => 'Age proof document',
        ];

        foreach ($required as $field => $label) {
            if (empty($app[$field])) {
                $errors[] = $label . ' is required.';
            }
        }

        // Practice duration is entered manually (0 years / 0 months are valid once set)
        if ($app['practice_years'] === null || $app['practice_years'] === ''
            || $app['practice_months'] === null || $app['practice_months'] === '') {
            $errors[] = 'Years and months of practice are required.';
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

    /**
     * Application the current user may edit: draft / returned, or submitted during edit window.
     *
     * @param int|null $applicationId When posted from a form, prefer this id (ownership + editable check).
     */
    protected function requireEditableApplication(?int $applicationId = null): ?array
    {
        $userId = (int) session()->get('user_id');

        if ($applicationId !== null && $applicationId > 0) {
            $app = $this->apps->find($applicationId);
            if (
                ! $app
                || (int) ($app['user_id'] ?? 0) !== $userId
                || ! ApplicationModel::isEditableByApplicant($app)
            ) {
                return null;
            }

            return $this->ensureRegistrationContact($app);
        }

        $app = $this->apps->findEditableForUser($userId);

        if (! $app || ! ApplicationModel::isEditableByApplicant($app)) {
            return null;
        }

        // Backfill registration contact if missing on older drafts
        return $this->ensureRegistrationContact($app);
    }

    /** @deprecated Use requireEditableApplication() */
    protected function requireEditableDraft(): ?array
    {
        return $this->requireEditableApplication();
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

                // Display string is refreshed after pivot sync in saveStep
                return [
                    'title'             => $post['title'] ?? null,
                    'full_name'         => trim($post['full_name'] ?? ''),
                    'date_of_birth'     => $post['date_of_birth'] ?: null,
                    'address_office'    => trim($post['address_office'] ?? ''),
                    'address_residence' => trim($post['address_residence'] ?? ''),
                    'phone_landline'    => trim($post['phone_landline'] ?? ''),
                    'mobile'            => $contact['mobile'] !== '' ? $contact['mobile'] : (string) ($app['mobile'] ?? ''),
                    'email'             => $contact['email'] !== '' ? $contact['email'] : (string) ($app['email'] ?? ''),
                ];
            case 2:
                // Prefer registration enrolment number when present (locked on form).
                $user = model(UserModel::class)->find((int) session()->get('user_id'));
                $fromAccount = trim((string) ($user['enrolment_number'] ?? session()->get('enrolment_number') ?? ''));
                $postedEnrol = trim((string) ($post['enrolment_number'] ?? ''));
                $enrolmentNumber = $fromAccount !== '' ? $fromAccount : $postedEnrol;

                $practiceYears  = max(0, min(70, (int) ($post['practice_years'] ?? 0)));
                $practiceMonths = max(0, min(11, (int) ($post['practice_months'] ?? 0)));

                return [
                    'enrolment_date'            => $post['enrolment_date'] ?: null,
                    'enrolment_number'          => $enrolmentNumber,
                    'bar_council'               => trim($post['bar_council'] ?? ''),
                    'practice_years'            => $practiceYears,
                    'practice_months'           => $practiceMonths,
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
                $dateOrNull = static function ($value): ?string {
                    $value = trim((string) $value);
                    if ($value === '') {
                        return null;
                    }
                    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        return null;
                    }
                    $dt = \DateTime::createFromFormat('Y-m-d', $value);

                    return ($dt && $dt->format('Y-m-d') === $value) ? $value : null;
                };

                $courts = [];
                if (! empty($post['court_name']) && is_array($post['court_name'])) {
                    foreach ($post['court_name'] as $i => $name) {
                        $resolved = MasterRegistry::resolveSingle(
                            (string) $name,
                            isset($post['court_other'][$i]) ? (string) $post['court_other'][$i] : null
                        );
                        if ($resolved === '' && empty($post['court_from'][$i]) && empty($post['court_to'][$i])) {
                            continue;
                        }
                        $courts[] = [
                            'court'     => $resolved,
                            'from_date' => $dateOrNull($post['court_from'][$i] ?? null),
                            'to_date'   => $dateOrNull($post['court_to'][$i] ?? null),
                        ];
                    }
                }
                $tribunals = [];
                if (! empty($post['tribunal_name']) && is_array($post['tribunal_name'])) {
                    foreach ($post['tribunal_name'] as $i => $name) {
                        $resolved = MasterRegistry::resolveSingle(
                            (string) $name,
                            isset($post['tribunal_other'][$i]) ? (string) $post['tribunal_other'][$i] : null
                        );
                        if ($resolved === '' && empty($post['tribunal_from'][$i]) && empty($post['tribunal_to'][$i])) {
                            continue;
                        }
                        $tribunals[] = [
                            'tribunal'  => $resolved,
                            'from_date' => $dateOrNull($post['tribunal_from'][$i] ?? null),
                            'to_date'   => $dateOrNull($post['tribunal_to'][$i] ?? null),
                        ];
                    }
                }

                return [
                    'courts_practiced'    => $courts,
                    'tribunals_practiced' => $tribunals,
                    // nature_of_practice / field_of_law denormalised via pivot sync
                ];
            case 6:
                return [
                    'applied_mhc_earlier'      => $bool($post['applied_mhc_earlier'] ?? null),
                    'applied_mhc_date'         => $post['applied_mhc_date'] ?: null,
                    'applied_mhc_status'       => trim($post['applied_mhc_status'] ?? ''),
                    'applied_other_court'      => $bool($post['applied_other_court'] ?? null),
                    'applied_other_date'       => $post['applied_other_date'] ?: null,
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

    /**
     * Active dropdown labels for form steps that use admin-managed lookups.
     *
     * @return array<string, list<string>>
     */
    protected function lookupOptionsForSteps(): array
    {
        MasterRegistry::ensureAllDefaults();

        return MasterRegistry::allActiveLabels();
    }

    /**
     * Persist multi-select masters as one-to-many rows and refresh denormalised TEXT columns.
     *
     * @param array<string, mixed> $post
     */
    protected function syncMultiMasters(int $applicationId, int $step, array $post): void
    {
        $link = new ApplicationMasterLink();
        $patch = [];

        if ($step === 1) {
            $selected = $post['qualifications'] ?? [];
            if (! is_array($selected)) {
                $selected = $selected !== '' && $selected !== null ? [(string) $selected] : [];
            }
            $patch['qualifications'] = $link->syncMulti(
                $applicationId,
                'qualification',
                $selected,
                isset($post['qualifications_other']) ? (string) $post['qualifications_other'] : null
            );
        }

        if ($step === 5) {
            $nature = $post['nature_of_practice'] ?? [];
            if (! is_array($nature)) {
                $nature = $nature !== '' && $nature !== null ? [(string) $nature] : [];
            }
            $field = $post['field_of_law'] ?? [];
            if (! is_array($field)) {
                $field = $field !== '' && $field !== null ? [(string) $field] : [];
            }
            $patch['nature_of_practice'] = $link->syncMulti(
                $applicationId,
                'nature_of_practice',
                $nature,
                isset($post['nature_of_practice_other']) ? (string) $post['nature_of_practice_other'] : null
            );
            $patch['field_of_law'] = $link->syncMulti(
                $applicationId,
                'field_of_law',
                $field,
                isset($post['field_of_law_other']) ? (string) $post['field_of_law_other'] : null
            );
        }

        if ($patch !== []) {
            $this->apps->update($applicationId, $patch);
        }
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
                    'court_level'       => $post['l2_court_level'][$i] ?? 'madras_hc',
                    'court_name'        => $post['l2_court_name'][$i] ?? '',
                    'case_number'       => $cn,
                    'citation'          => null, // Citation not collected for unreported (L-2) judgments
                    'cause_title'       => $post['l2_cause_title'][$i] ?? '',
                    'decided_on'        => $post['l2_decided_on'][$i] ?: null,
                    'legal_formulation' => $post['l2_legal_formulation'][$i] ?? '',
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

        // Format L-4: Topic (Articles | Books) |
        // Experience (Teaching Assignment(s) | Guest Lectures) | Any other relevant details
        $l4 = [];
        $l4Articles  = is_array($post['l4_articles'] ?? null) ? $post['l4_articles'] : [];
        $l4Books     = is_array($post['l4_books'] ?? null) ? $post['l4_books'] : [];
        // Legacy combined field (older drafts / cached forms)
        $l4Topics    = is_array($post['l4_topic'] ?? null) ? $post['l4_topic'] : [];
        $l4Teaching  = is_array($post['l4_teaching'] ?? null) ? $post['l4_teaching'] : [];
        $l4Guest     = is_array($post['l4_guest'] ?? null) ? $post['l4_guest'] : [];
        $l4Other     = is_array($post['l4_other'] ?? null) ? $post['l4_other'] : [];
        $l4Count     = max(
            count($l4Articles),
            count($l4Books),
            count($l4Topics),
            count($l4Teaching),
            count($l4Guest),
            count($l4Other)
        );
        for ($i = 0; $i < $l4Count; $i++) {
            $articles = trim((string) ($l4Articles[$i] ?? ''));
            $books    = trim((string) ($l4Books[$i] ?? ''));
            $legacy   = trim((string) ($l4Topics[$i] ?? ''));
            if ($articles === '' && $books === '' && $legacy !== '') {
                $articles = $legacy;
            }
            $l4[] = [
                'articles'            => $articles,
                'books'               => $books,
                'teaching_assignment' => $l4Teaching[$i] ?? '',
                'guest_lectures'      => $l4Guest[$i] ?? '',
                'other_details'       => $l4Other[$i] ?? '',
            ];
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
            'photo'           => 'photo_path',
            'signature'       => 'signature_path',
            'enrolment_cert'  => 'enrolment_cert_path',
            'age_proof'       => 'age_proof_path',
            'education_qual'  => 'education_qual_path',
            'format_l1'       => 'format_l1_path',
            'format_l2'       => 'format_l2_path',
            'format_l3i'      => 'format_l3i_path',
            'format_l3ii'     => 'format_l3ii_path',
            'format_l4'       => 'format_l4_path',
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
