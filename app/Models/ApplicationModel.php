<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table            = 'applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id', 'application_no', 'status', 'cycle_year', 'notification_id', 'current_step',
        'title', 'full_name', 'date_of_birth', 'age_years', 'age_months', 'age_days',
        'address_office', 'address_residence',
        'phone_landline', 'mobile', 'email', 'qualifications',
        'enrolment_date', 'enrolment_number', 'bar_council',
        'practice_years', 'practice_months', 'net_income_lakhs',
        'is_bar_association_member', 'bar_association_name',
        'reported_sc', 'reported_hc', 'reported_district',
        'unreported_sc', 'unreported_hc', 'unreported_district',
        'pro_bono_total', 'amicus_total', 'is_first_generation',
        'academic_articles_count', 'academic_books_count',
        'teaching_assignments_count', 'guest_lectures_count',
        'courts_practiced', 'tribunals_practiced',
        'nature_of_practice', 'field_of_law',
        'applied_mhc_earlier', 'applied_mhc_date', 'applied_mhc_status',
        'applied_other_court', 'applied_other_date', 'applied_other_details',
        'fir_lodged', 'fir_details',
        'criminal_case_party', 'criminal_case_details',
        'bar_council_proceedings', 'bar_council_details',
        'general_health', 'other_information',
        'declaration_name', 'declaration_accepted', 'instructions_accepted',
        'declaration_date',
        'photo_path', 'signature_path', 'enrolment_cert_path',
        'age_proof_path', 'education_qual_path',
        'format_l1_path', 'format_l2_path', 'format_l3i_path',
        'format_l3ii_path', 'format_l4_path', 'generated_pdf_path',
        'submitted_at', 'reviewed_by', 'reviewed_at', 'review_remarks',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public const STATUS_DRAFT            = 'draft';
    public const STATUS_SUBMITTED        = 'submitted';
    public const STATUS_UNDER_REVIEW     = 'under_review';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED         = 'approved'; // legacy; prefer STATUS_LISTED
    public const STATUS_LISTED           = 'listed';
    public const STATUS_WAITLISTED       = 'waitlisted';
    public const STATUS_REJECTED         = 'rejected';
    public const STATUS_RETURNED         = 'returned';

    public const STATUSES = [
        self::STATUS_DRAFT            => 'Draft',
        self::STATUS_SUBMITTED        => 'Submitted',
        self::STATUS_UNDER_REVIEW     => 'Under Review',
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVED         => 'Accepted (legacy)',
        self::STATUS_LISTED           => 'Select Listed',
        self::STATUS_WAITLISTED       => 'Wait Listed',
        self::STATUS_REJECTED         => 'Rejected',
        self::STATUS_RETURNED         => 'Returned for Correction',
    ];

    /**
     * Operational statuses used in admin filters and status updates:
     * Submitted, Select Listed, Wait Listed, Rejected.
     * (Draft and other legacy values remain in STATUSES for display only.)
     *
     * @var array<string, string>
     */
    public const ADMIN_PIPELINE_STATUSES = [
        self::STATUS_SUBMITTED  => 'Submitted',
        self::STATUS_LISTED     => 'Select Listed',
        self::STATUS_WAITLISTED => 'Wait Listed',
        self::STATUS_REJECTED   => 'Rejected',
    ];

    /**
     * Statuses admins may assign (same set as pipeline filter).
     * No approve/reject workflow — classification only (no email/SMS on change).
     *
     * @var array<string, string>
     */
    public const ADMIN_ASSIGNABLE_STATUSES = self::ADMIN_PIPELINE_STATUSES;

    public const TOTAL_STEPS = 7;

    /**
     * Statuses that mean the application is still in the pipeline (not final).
     *
     * @return list<string>
     */
    public static function inProcessStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_PENDING_APPROVAL,
        ];
    }

    /**
     * Whether admin may assign this status (bulk or single).
     */
    public static function isAdminAssignableStatus(string $status): bool
    {
        return array_key_exists($status, self::ADMIN_ASSIGNABLE_STATUSES);
    }

    public function findDraftForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_RETURNED])
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Statuses that an applicant may edit during an admin-opened edit window.
     *
     * @return list<string>
     */
    public static function editWindowStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_PENDING_APPROVAL,
        ];
    }

    /**
     * Current designation cycle year from the active (or application-linked) notification date.
     */
    public static function currentCycleYear(?array $app = null): int
    {
        $asOn = self::ageAsOnDate($app);
        $year = (int) substr($asOn, 0, 4);
        if ($year >= 2000 && $year <= 2100) {
            return $year;
        }

        return (int) date('Y');
    }

    /**
     * Resolve the official notification row used for "as on" calculations.
     * Prefer the application's linked notification, then the currently active one.
     *
     * @return array<string, mixed>|null
     */
    public static function referenceNotification(?array $app = null): ?array
    {
        try {
            $model = model(DesignationNotificationModel::class);
            $nid   = (int) ($app['notification_id'] ?? 0);
            if ($nid > 0) {
                $row = $model->find($nid);
                if ($row) {
                    return $row;
                }
            }

            return $model->getActive();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Reference date (Y-m-d) for age and practice duration: notification date.
     * Fallback: 01 January of the calendar year.
     */
    public static function ageAsOnDate(?array $app = null): string
    {
        $notification = self::referenceNotification($app);
        if ($notification && ! empty($notification['notification_date'])) {
            $ts = strtotime((string) $notification['notification_date']);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        return sprintf('%04d-01-01', (int) date('Y'));
    }

    /**
     * Display label for the reference date, e.g. "15.08.2026" (from notification date).
     */
    public static function ageAsOnLabel(?array $app = null): string
    {
        $asOn = self::ageAsOnDate($app);
        $ts   = strtotime($asOn);

        return $ts !== false ? date('d.m.Y', $ts) : $asOn;
    }

    /**
     * Practice duration (years + months) from enrolment date as on the notification date.
     * Days remainder is folded into months only (practice is stored as years/months).
     *
     * @return array{years: int, months: int}|null
     */
    public function calculatePracticePartsAsOn(?string $enrolmentDate, ?string $asOn = null): ?array
    {
        $parts = $this->calculateAgePartsAsOn($enrolmentDate, $asOn);
        if ($parts === null) {
            return null;
        }

        return [
            'years'  => (int) $parts['years'],
            'months' => (int) $parts['months'],
        ];
    }

    /**
     * Whether admin has opened the post-submission edit window (now).
     * Driven only by the active notification's edit window dates.
     */
    public static function isEditWindowOpen(?array $settings = null): bool
    {
        try {
            $notification = model(DesignationNotificationModel::class)->getActive();
        } catch (\Throwable $e) {
            return false;
        }

        if (! $notification) {
            return false;
        }

        return DesignationNotificationModel::isEditWindowOpen($notification);
    }

    /**
     * @return array{open: bool, from: string, to: string, message: string, cycle_year: int, notification_id: ?int, notification_number: string, enabled: bool}
     */
    public static function editWindowInfo(): array
    {
        try {
            $notification = model(DesignationNotificationModel::class)->getActive();
        } catch (\Throwable $e) {
            $notification = null;
        }

        $info = DesignationNotificationModel::editWindowInfo($notification);
        $hasEditDates = $info['from'] !== '' || $info['to'] !== '';

        return [
            'open'                => $info['open'],
            'from'                => $info['from'],
            'to'                  => $info['to'],
            'message'             => $info['message'],
            'cycle_year'          => self::currentCycleYear(),
            'notification_id'     => $info['notification_id'],
            'notification_number' => $info['notification_number'],
            'enabled'             => $hasEditDates,
        ];
    }

    /**
     * Whether this application row is editable by the applicant right now.
     */
    public static function isEditableByApplicant(array $app): bool
    {
        $status = (string) ($app['status'] ?? '');
        if (in_array($status, [self::STATUS_DRAFT, self::STATUS_RETURNED], true)) {
            return true;
        }

        if (! in_array($status, self::editWindowStatuses(), true)) {
            return false;
        }

        // Prefer the notification this application belongs to
        $notificationId = (int) ($app['notification_id'] ?? 0);
        if ($notificationId > 0) {
            try {
                $notification = model(DesignationNotificationModel::class)->find($notificationId);
                if ($notification) {
                    return DesignationNotificationModel::isEditWindowOpen($notification);
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        if (! self::isEditWindowOpen()) {
            return false;
        }

        $cycle = (int) ($app['cycle_year'] ?? 0);
        if ($cycle > 0 && $cycle !== self::currentCycleYear()) {
            return false;
        }

        return true;
    }

    /**
     * Find the application the applicant may currently edit (draft/returned, or edit-window).
     */
    public function findEditableForUser(int $userId): ?array
    {
        $draft = $this->findDraftForUser($userId);
        if ($draft) {
            return $draft;
        }

        // Notification-scoped edit window first
        try {
            $active = model(DesignationNotificationModel::class)->getActive();
        } catch (\Throwable $e) {
            $active = null;
        }

        if ($active && DesignationNotificationModel::isEditWindowOpen($active)) {
            $byNotification = $this->where('user_id', $userId)
                ->where('notification_id', (int) $active['id'])
                ->whereIn('status', self::editWindowStatuses())
                ->orderBy('id', 'DESC')
                ->first();
            if ($byNotification) {
                return $byNotification;
            }
        }

        if (! self::isEditWindowOpen()) {
            return null;
        }

        $year = self::currentCycleYear();

        return $this->where('user_id', $userId)
            ->whereIn('status', self::editWindowStatuses())
            ->groupStart()
                ->where('cycle_year', $year)
                ->orWhere('cycle_year', null)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Existing application for this user under a designation notification.
     */
    public function findForUserNotification(int $userId, int $notificationId): ?array
    {
        if ($notificationId <= 0) {
            return null;
        }

        return $this->where('user_id', $userId)
            ->where('notification_id', $notificationId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Existing application for this user in the given cycle year (blocks a second start).
     */
    public function findForUserCycle(int $userId, ?int $year = null): ?array
    {
        $year = $year ?? self::currentCycleYear();

        // Prefer active notification scope when available
        try {
            $active = model(DesignationNotificationModel::class)->getActive();
            if ($active) {
                $byN = $this->findForUserNotification($userId, (int) $active['id']);
                if ($byN) {
                    return $byN;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $byCycle = $this->where('user_id', $userId)
            ->where('cycle_year', $year)
            ->orderBy('id', 'DESC')
            ->first();
        if ($byCycle) {
            return $byCycle;
        }

        // Legacy rows without cycle_year — match by application no. / submitted / created year
        $rows = $this->where('user_id', $userId)
            ->groupStart()
                ->where('cycle_year', null)
                ->orWhere('cycle_year', 0)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->findAll();

        foreach ($rows as $row) {
            if (self::resolveCycleYear($row) === $year) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Best-effort cycle year for a row.
     */
    public static function resolveCycleYear(array $app): int
    {
        if (! empty($app['cycle_year'])) {
            return (int) $app['cycle_year'];
        }
        if (! empty($app['application_no']) && preg_match('/(20\d{2})/', (string) $app['application_no'], $m)) {
            return (int) $m[1];
        }
        foreach (['submitted_at', 'created_at'] as $field) {
            if (! empty($app[$field]) && preg_match('/^(20\d{2})/', (string) $app[$field], $m)) {
                return (int) $m[1];
            }
        }

        return self::currentCycleYear();
    }

    /**
     * Whether the user may start a new application for the current cycle / notification.
     * Does not check the application period — use canStartNewApplicationNow() for the full gate.
     */
    public function canStartNewApplication(int $userId): bool
    {
        try {
            $onePerYear = model(SystemSettingModel::class)->bool('application', 'one_per_year', true);
        } catch (\Throwable $e) {
            $onePerYear = true;
        }

        if (! $onePerYear) {
            // Still block concurrent in-process applications
            $open = $this->where('user_id', $userId)
                ->whereIn('status', self::inProcessStatuses())
                ->first();

            return $open === null && $this->findDraftForUser($userId) === null;
        }

        // Prefer one application per currently open (or active) notification
        try {
            $period = DesignationNotificationModel::applicationPeriodInfo();
            $nid    = (int) ($period['notification_id'] ?? 0);
            if ($nid > 0) {
                return $this->findForUserNotification($userId, $nid) === null;
            }
        } catch (\Throwable $e) {
            // fall through to cycle-year rule
        }

        // One application per cycle year (any status, including draft)
        return $this->findForUserCycle($userId) === null;
    }

    /**
     * Full gate before starting a new application: period must be open and user eligible.
     */
    public function canStartNewApplicationNow(int $userId): bool
    {
        $period = DesignationNotificationModel::applicationPeriodInfo();
        if (empty($period['open'])) {
            return false;
        }

        return $this->canStartNewApplication($userId);
    }

    public function findForUser(int $userId, int $id): ?array
    {
        return $this->where('user_id', $userId)->find($id);
    }

    public function listForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Decode JSON list fields for views/forms.
     * Normalises legacy {from,to} keys to {from_date,to_date} (ISO Y-m-d).
     */
    public function withDecoded(array $app): array
    {
        foreach (['courts_practiced', 'tribunals_practiced'] as $field) {
            if (! empty($app[$field]) && is_string($app[$field])) {
                $decoded = json_decode($app[$field], true);
                $app[$field] = is_array($decoded) ? $decoded : [];
            } elseif (empty($app[$field])) {
                $app[$field] = [];
            }

            if (is_array($app[$field])) {
                $app[$field] = array_map([$this, 'normalizePracticePeriodRow'], $app[$field]);
            }
        }

        // One-to-many multi-select masters (qualifications, nature, field of law)
        try {
            $app = (new ApplicationMasterLink())->hydrateApplication($app);
        } catch (\Throwable $e) {
            // Tables may not exist yet during early migrate
            log_message('debug', 'Master link hydrate skipped: ' . $e->getMessage());
        }

        return $app;
    }

    public function encodeListFields(array $data): array
    {
        foreach (['courts_practiced', 'tribunals_practiced'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $rows = array_map([$this, 'normalizePracticePeriodRow'], $data[$field]);
                $data[$field] = json_encode(array_values(array_filter($rows, static function ($row) {
                    if (! is_array($row)) {
                        return false;
                    }

                    return array_filter($row, static fn ($v) => $v !== null && $v !== '');
                })));
            }
        }

        return $data;
    }

    /**
     * Ensure practice period rows use from_date / to_date (Y-m-d or null).
     *
     * @param mixed $row
     *
     * @return array<string, mixed>
     */
    private function normalizePracticePeriodRow($row): array
    {
        if (! is_array($row)) {
            return [];
        }

        $iso = static function ($value): ?string {
            $value = trim((string) ($value ?? ''));
            if ($value === '') {
                return null;
            }
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            $dt = \DateTime::createFromFormat('Y-m-d', $value);

            return ($dt && $dt->format('Y-m-d') === $value) ? $value : null;
        };

        $from = $iso($row['from_date'] ?? $row['from'] ?? null);
        $to   = $iso($row['to_date'] ?? $row['to'] ?? null);

        unset($row['from'], $row['to']);
        $row['from_date'] = $from;
        $row['to_date']   = $to;

        return $row;
    }

    /**
     * Age in whole years as on a reference date (legacy helper).
     */
    public function calculateAgeAsOn(?string $dob, ?string $asOn = null): ?int
    {
        $parts = $this->calculateAgePartsAsOn($dob, $asOn);

        return $parts['years'] ?? null;
    }

    /**
     * Age as years, months and days on a reference date (default: notification date).
     *
     * @return array{years: int, months: int, days: int}|null
     */
    public function calculateAgePartsAsOn(?string $dob, ?string $asOn = null): ?array
    {
        if (empty($dob)) {
            return null;
        }

        $asOn = $asOn ?: self::ageAsOnDate();

        try {
            $birth = new \DateTime($dob);
            $ref   = new \DateTime($asOn);
            if ($birth > $ref) {
                return null;
            }
            $diff = $birth->diff($ref);

            return [
                'years'  => (int) $diff->y,
                'months' => (int) $diff->m,
                'days'   => (int) $diff->d,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
