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
        'title', 'full_name', 'date_of_birth', 'age_years',
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

    public const STATUS_DRAFT        = 'draft';
    public const STATUS_SUBMITTED    = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED     = 'approved';
    public const STATUS_REJECTED     = 'rejected';
    public const STATUS_RETURNED     = 'returned';

    public const STATUSES = [
        self::STATUS_DRAFT        => 'Draft',
        self::STATUS_SUBMITTED    => 'Submitted',
        self::STATUS_UNDER_REVIEW => 'Under Review',
        self::STATUS_APPROVED     => 'Approved',
        self::STATUS_REJECTED     => 'Rejected',
        self::STATUS_RETURNED     => 'Returned for Correction',
    ];

    public const TOTAL_STEPS = 7;

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
        }

        return $app;
    }

    public function encodeListFields(array $data): array
    {
        foreach (['courts_practiced', 'tribunals_practiced'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode(array_values(array_filter($data[$field], static function ($row) {
                    if (! is_array($row)) {
                        return false;
                    }
                    return array_filter($row, static fn ($v) => $v !== null && $v !== '');
                })));
            }
        }

        return $data;
    }

    public function calculateAgeAsOn(?string $dob, string $asOn = '2026-01-01'): ?int
    {
        if (empty($dob)) {
            return null;
        }

        try {
            $birth = new \DateTime($dob);
            $ref   = new \DateTime($asOn);

            return (int) $birth->diff($ref)->y;
        } catch (\Exception $e) {
            return null;
        }
    }
}
