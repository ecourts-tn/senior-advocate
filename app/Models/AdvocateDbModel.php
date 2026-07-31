<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvocateDbModel extends Model
{
    protected $table            = 'advocate_db';
    protected $primaryKey       = 'advenrol';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'advenrol', 'advname', 'advaddr', 'adv_gend', 'adv_fat_hus', 'adv_id',
        'dob', 'dobsen', 'doen', 'doensen', 'mastcur', 'mastex', 'bar',
        'remarks', 'mobileno', 'create_modify',
    ];

    protected $useTimestamps = false;

    /**
     * Normalise enrolment number for lookup (trim, collapse spaces, case-insensitive match).
     */
    public static function normaliseEnrolment(string $enrolment): string
    {
        $enrolment = trim($enrolment);
        $enrolment = preg_replace('/\s+/', '', $enrolment) ?? $enrolment;

        return $enrolment;
    }

    /**
     * Find advocate by enrolment number (exact, then case-insensitive).
     */
    public function findByEnrolment(string $enrolment): ?array
    {
        $enrolment = self::normaliseEnrolment($enrolment);
        if ($enrolment === '') {
            return null;
        }

        $sql = 'SELECT * FROM advocate_db WHERE advenrol = ? OR LOWER(advenrol) = LOWER(?) LIMIT 1';
        $row = $this->db->query($sql, [$enrolment, $enrolment])->getRowArray();

        return $row ?: null;
    }

    /**
     * Map advocate_db row to registration form fields.
     *
     * @return array{enrolment_number: string, name: string, mobile: string, address: string, date_of_birth: string, gender: string, father_husband: string, bar: string, enrolment_date: string, found: bool}
     */
    public function toRegistrationPrefill(array $row): array
    {
        $mobile = trim((string) ($row['mobileno'] ?? ''));
        if ($mobile === '0' || $mobile === '0.0') {
            $mobile = '';
        }
        // Strip decimal noise from legacy numeric mobile
        if ($mobile !== '' && str_contains($mobile, '.')) {
            $mobile = explode('.', $mobile, 2)[0];
        }

        $dob = $this->cleanDate($row['dob'] ?? null);
        $doen = $this->cleanDate($row['doen'] ?? null);

        return [
            'found'            => true,
            'enrolment_number' => (string) ($row['advenrol'] ?? ''),
            'name'             => trim((string) ($row['advname'] ?? '')),
            'mobile'           => $mobile,
            'address'          => trim((string) ($row['advaddr'] ?? '')),
            'date_of_birth'    => $dob,
            'gender'           => trim((string) ($row['adv_gend'] ?? '')),
            'father_husband'   => trim((string) ($row['adv_fat_hus'] ?? '')),
            'bar'              => trim((string) ($row['bar'] ?? '')),
            'enrolment_date'   => $doen,
        ];
    }

    protected function cleanDate($value): string
    {
        if ($value === null || $value === '' || $value === '0000-00-00') {
            return '';
        }
        $s = substr((string) $value, 0, 10);
        if ($s === '0000-00-00' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return '';
        }
        // Reject obviously invalid years
        $y = (int) substr($s, 0, 4);
        if ($y < 1900 || $y > 2100) {
            return '';
        }

        return $s;
    }
}
