<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvocateDbModel extends Model
{
    protected $table            = 'advocate_t';
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
     * Split an enrolment value into its serial number and year (e.g. "Ms. 1234/2010" → 1234 + 2010).
     *
     * @return array{number: string, year: string}|null
     */
    public static function parseNumberAndYear(string $enrolment): ?array
    {
        $enrolment = self::normaliseEnrolment($enrolment);
        if ($enrolment === '') {
            return null;
        }
        if (! preg_match('/(\d+)\D*((?:19|20)\d{2})\b/', $enrolment, $m)) {
            return null;
        }
        $number = ltrim($m[1], '0');
        if ($number === '') {
            $number = '0';
        }

        return [
            'number' => $number,
            'year'   => $m[2],
        ];
    }

    /**
     * Whether two enrolment strings refer to the same number + year.
     */
    public static function sameNumberAndYear(string $left, string $right): bool
    {
        $a = self::parseNumberAndYear($left);
        $b = self::parseNumberAndYear($right);
        if ($a === null || $b === null) {
            return strcasecmp(self::normaliseEnrolment($left), self::normaliseEnrolment($right)) === 0;
        }

        return $a['number'] === $b['number'] && $a['year'] === $b['year'];
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

        $sql = 'SELECT * FROM advocate_t WHERE advenrol = ? OR LOWER(advenrol) = LOWER(?) LIMIT 1';
        $row = $this->db->query($sql, [$enrolment, $enrolment])->getRowArray();

        return $row ?: null;
    }

    /**
     * Map registration form fields onto advocate_t columns.
     * Empty / missing values are omitted so updates do not wipe existing data.
     *
     * @param array<string, mixed> $registration
     *
     * @return array<string, string>
     */
    public static function registrationFieldsToRow(array $registration): array
    {
        $enrolment = self::normaliseEnrolment((string) ($registration['enrolment_number'] ?? $registration['advenrol'] ?? ''));

        $name = trim((string) ($registration['name'] ?? $registration['advname'] ?? ''));
        if ($name !== '') {
            $name = mb_substr($name, 0, 100);
        }

        $mobile = trim((string) ($registration['mobile'] ?? $registration['mobileno'] ?? ''));
        if ($mobile === '0' || $mobile === '0.0') {
            $mobile = '';
        }
        if ($mobile !== '' && str_contains($mobile, '.')) {
            $mobile = explode('.', $mobile, 2)[0];
        }
        if ($mobile !== '') {
            $mobile = mb_substr($mobile, 0, 20);
        }

        $row = [];
        if ($enrolment !== '') {
            $row['advenrol'] = mb_substr($enrolment, 0, 40);
        }
        if ($name !== '') {
            $row['advname'] = $name;
        }
        if ($mobile !== '') {
            $row['mobileno'] = $mobile;
        }

        return $row;
    }

    /**
     * Insert advocate master row from registration, or update provided fields if it exists.
     *
     * @param array<string, mixed> $registration
     *
     * @return 'inserted'|'updated'|'unchanged'|'skipped'
     */
    public function upsertFromRegistration(array $registration): string
    {
        $row       = self::registrationFieldsToRow($registration);
        $enrolment = $row['advenrol'] ?? '';
        if ($enrolment === '') {
            return 'skipped';
        }

        $now      = date('Y-m-d H:i:s');
        $existing = $this->findByEnrolment($enrolment);

        if ($existing) {
            $update = $row;
            unset($update['advenrol']);
            if ($update === []) {
                return 'unchanged';
            }

            $update['create_modify'] = $now;
            $this->update((string) $existing['advenrol'], $update);

            return 'updated';
        }

        $this->insert([
            'advenrol'      => $enrolment,
            'advname'       => $row['advname'] ?? '',
            'mobileno'      => $row['mobileno'] ?? null,
            'create_modify' => $now,
        ]);

        return 'inserted';
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
