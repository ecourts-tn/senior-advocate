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
        'user_id', 'application_no', 'status', 'current_step',
        'title', 'full_name', 'date_of_birth', 'age_years', 'age_months',
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
        'applied_other_court', 'applied_other_details',
        'fir_lodged', 'fir_details',
        'criminal_case_party', 'criminal_case_details',
        'bar_council_proceedings', 'bar_council_details',
        'general_health', 'other_information',
        'declaration_name', 'declaration_accepted', 'instructions_accepted',
        'declaration_date',
        'photo_path', 'signature_path', 'enrolment_cert_path',
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
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_REJECTED         = 'rejected';
    public const STATUS_RETURNED         = 'returned';

    public const STATUSES = [
        self::STATUS_DRAFT            => 'Draft',
        self::STATUS_SUBMITTED        => 'Submitted',
        self::STATUS_UNDER_REVIEW     => 'Under Review',
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVED         => 'Accepted',
        self::STATUS_REJECTED         => 'Rejected',
        self::STATUS_RETURNED         => 'Returned for Correction',
    ];

    /**
     * Workflow actions available to staff.
     *
     * TEMPORARY simplified flow (reviewer / multi-step approver path disabled):
     *  Applicant submits → submitted
     *  Admin → approved | rejected (remarks required)
     *
     * Intermediate statuses (under_review, pending_approval) remain decidable so
     * any applications already in the old pipeline are not stuck.
     */
    public const ACTIONS = [
        'approve' => [
            'label'            => 'Accept',
            'to'               => self::STATUS_APPROVED,
            'roles'            => ['admin'],
            'from'             => [
                self::STATUS_SUBMITTED,
                self::STATUS_UNDER_REVIEW,
                self::STATUS_PENDING_APPROVAL,
            ],
            'remarks_required' => true,
        ],
        'reject' => [
            'label'            => 'Reject',
            'to'               => self::STATUS_REJECTED,
            'roles'            => ['admin'],
            'from'             => [
                self::STATUS_SUBMITTED,
                self::STATUS_UNDER_REVIEW,
                self::STATUS_PENDING_APPROVAL,
            ],
            'remarks_required' => true,
        ],
    ];

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
     * Actions the given role may perform on this application in its current status.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function availableActions(string $currentStatus, string $role): array
    {
        $out = [];
        foreach (self::ACTIONS as $key => $meta) {
            if (! in_array($role, $meta['roles'], true)) {
                continue;
            }
            if (! in_array($currentStatus, $meta['from'], true)) {
                continue;
            }
            $out[$key] = $meta;
        }

        return $out;
    }

    public static function resolveAction(string $action): ?array
    {
        return self::ACTIONS[$action] ?? null;
    }

    public function findDraftForUser(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->whereIn('status', [self::STATUS_DRAFT, self::STATUS_RETURNED])
            ->orderBy('id', 'DESC')
            ->first();
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
    public function calculateAgeAsOn(?string $dob, string $asOn = '2026-01-01'): ?int
    {
        $parts = $this->calculateAgePartsAsOn($dob, $asOn);

        return $parts['years'] ?? null;
    }

    /**
     * Age as years and months on a reference date.
     *
     * @return array{years: int, months: int}|null
     */
    public function calculateAgePartsAsOn(?string $dob, string $asOn = '2026-01-01'): ?array
    {
        if (empty($dob)) {
            return null;
        }

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
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
