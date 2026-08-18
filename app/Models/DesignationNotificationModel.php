<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Official designation notifications that define application and edit windows.
 */
class DesignationNotificationModel extends Model
{
    protected $table            = 'designation_notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'notification_number',
        'notification_date',
        'frequency',
        'title',
        'application_start_date',
        'application_end_date',
        'edit_window_start_date',
        'edit_window_end_date',
        'document_path',
        'is_active',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const FREQUENCY_3_MONTHS = '3_months';
    public const FREQUENCY_6_MONTHS = '6_months';
    public const FREQUENCY_YEARLY   = 'yearly';

    /**
     * @var array<string, string>
     */
    public const FREQUENCIES = [
        self::FREQUENCY_3_MONTHS => 'Once in 3 months',
        self::FREQUENCY_6_MONTHS => 'Once in 6 months',
        self::FREQUENCY_YEARLY   => 'Once a year',
    ];

    /**
     * Human label for a frequency key.
     */
    public static function frequencyLabel(?string $key): string
    {
        if ($key === null || $key === '') {
            return '—';
        }

        return self::FREQUENCIES[$key] ?? $key;
    }

    /**
     * Display label for lists / filters.
     */
    public static function displayLabel(array $row): string
    {
        $num  = trim((string) ($row['notification_number'] ?? ''));
        $date = ! empty($row['notification_date'])
            ? date('d-m-Y', strtotime((string) $row['notification_date']))
            : '';
        $title = trim((string) ($row['title'] ?? ''));

        $parts = array_filter([$num, $date !== '' ? 'dt. ' . $date : '', $title]);

        return $parts !== [] ? implode(' — ', $parts) : ('#' . (int) ($row['id'] ?? 0));
    }

    /**
     * Currently active notification (most recent by notification date / id).
     */
    public function getActive(): ?array
    {
        return $this->where('is_active', true)
            ->orderBy('notification_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Notifications that have an uploaded document (for public portal listing).
     *
     * @return list<array<string, mixed>>
     */
    public function withDocuments(int $limit = 50): array
    {
        return $this->where('document_path IS NOT NULL', null, false)
            ->where('document_path !=', '')
            ->orderBy('notification_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit);
    }

    /**
     * Whether any notification PDF is published for portal display.
     */
    public function hasPublishedDocuments(): bool
    {
        return (int) $this->where('document_path IS NOT NULL', null, false)
            ->where('document_path !=', '')
            ->countAllResults() > 0;
    }

    /**
     * Format a notification datetime for display (12-hour clock).
     * Example: 04-08-2026 10:30 AM
     */
    public static function formatDateTime(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '—';
        }
        $ts = strtotime($value);

        return $ts !== false ? date('d-m-Y h:i A', $ts) : $value;
    }

    /**
     * Value for HTML datetime-local inputs (Y-m-d\TH:i).
     */
    public static function toDatetimeLocal(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        $ts = strtotime($value);

        return $ts !== false ? date('Y-m-d\TH:i', $ts) : '';
    }

    /**
     * Normalise form datetime-local / SQL datetime to Y-m-d H:i:s.
     */
    public static function normalizeDateTime(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * Calendar date (Y-m-d) from a date or datetime string, or null if empty/invalid.
     */
    public static function calendarDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $m)) {
            $ymd = $m[1];
            $dt  = \DateTime::createFromFormat('Y-m-d', $ymd);

            return ($dt && $dt->format('Y-m-d') === $ymd) ? $ymd : null;
        }

        return null;
    }

    /**
     * Validate notification / application / edit-window dates.
     *
     * Notification date cannot be in the future.
     * Application start is on or after the notification date (future allowed).
     * Application end is on or after application start (future allowed).
     * Edit start is on or after application end (future allowed).
     * Edit end is on or after edit start (future allowed).
     *
     * @param array<string, mixed> $payload
     */
    public static function validateAdminDates(array $payload, ?string $today = null): ?string
    {
        $today = $today ?? date('Y-m-d');

        $notif = self::calendarDate((string) ($payload['notification_date'] ?? ''));
        if ($notif === null) {
            return 'Notification date is required and must be a valid date.';
        }
        if ($notif > $today) {
            return 'Notification date cannot be a future date.';
        }

        $appStart = self::normalizeDateTime($payload['application_start_date'] ?? null);
        $appEnd   = self::normalizeDateTime($payload['application_end_date'] ?? null);
        if ($appStart === null || $appEnd === null) {
            return 'Application start and end must be valid date and time values.';
        }
        if (self::calendarDate($appStart) < $notif) {
            return 'Application start date cannot be earlier than the notification date.';
        }
        if (strtotime($appEnd) < strtotime($appStart)) {
            return 'Application end date/time cannot be earlier than the application start date/time.';
        }

        $editStart = self::normalizeDateTime($payload['edit_window_start_date'] ?? null);
        $editEnd   = self::normalizeDateTime($payload['edit_window_end_date'] ?? null);
        if ($editStart === null && $editEnd === null) {
            return null;
        }
        if ($editStart === null || $editEnd === null) {
            return 'Edit window start and end dates must both be entered.';
        }
        if (strtotime($editStart) < strtotime($appEnd)) {
            return 'Edit window start date/time cannot be earlier than the application end date/time.';
        }
        if (strtotime($editEnd) < strtotime($editStart)) {
            return 'Edit window end date/time cannot be earlier than the edit window start date/time.';
        }

        return null;
    }

    /**
     * Notification whose application window includes now (and is active).
     */
    public function getOpenForApplications(?string $now = null): ?array
    {
        $now ??= date('Y-m-d H:i:s');

        return $this->where('is_active', true)
            ->where('application_start_date <=', $now)
            ->where('application_end_date >=', $now)
            ->orderBy('notification_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Whether applications may be started under this notification now.
     */
    public static function isApplicationOpen(array $notification, ?string $now = null): bool
    {
        $nowTs = strtotime($now ?? date('Y-m-d H:i:s'));
        $start = trim((string) ($notification['application_start_date'] ?? ''));
        $end   = trim((string) ($notification['application_end_date'] ?? ''));

        if ($start === '' || $end === '' || $nowTs === false) {
            return false;
        }

        $startTs = strtotime($start);
        $endTs   = strtotime($end);
        if ($startTs === false || $endTs === false) {
            return false;
        }

        return $nowTs >= $startTs && $nowTs <= $endTs;
    }

    /**
     * Application period status for UI and start-application gates.
     *
     * @return array{
     *   open: bool,
     *   status: string,
     *   message: string,
     *   notification: ?array,
     *   notification_id: ?int,
     *   notification_number: string,
     *   notification_date: string,
     *   application_start_date: string,
     *   application_end_date: string
     * }
     */
    public static function applicationPeriodInfo(): array
    {
        $empty = [
            'open'                   => false,
            'status'                 => 'none',
            'message'                => 'The application submission period has not commenced yet. Please try again later.',
            'notification'           => null,
            'notification_id'        => null,
            'notification_number'    => '',
            'notification_date'      => '',
            'application_start_date' => '',
            'application_end_date'   => '',
        ];

        try {
            $model = model(self::class);
            $open  = $model->getOpenForApplications();
            if ($open) {
                return [
                    'open'                   => true,
                    'status'                 => 'open',
                    'message'                => 'Application submission is open until '
                        . self::formatDateTime($open['application_end_date'] ?? null) . '.',
                    'notification'           => $open,
                    'notification_id'        => (int) ($open['id'] ?? 0) ?: null,
                    'notification_number'    => (string) ($open['notification_number'] ?? ''),
                    'notification_date'      => (string) ($open['notification_date'] ?? ''),
                    'application_start_date' => (string) ($open['application_start_date'] ?? ''),
                    'application_end_date'   => (string) ($open['application_end_date'] ?? ''),
                ];
            }

            $active = $model->getActive();
            if (! $active) {
                return $empty;
            }

            $nowTs   = time();
            $startTs = strtotime((string) ($active['application_start_date'] ?? ''));
            $endTs   = strtotime((string) ($active['application_end_date'] ?? ''));

            $base = [
                'open'                   => false,
                'notification'           => $active,
                'notification_id'        => (int) ($active['id'] ?? 0) ?: null,
                'notification_number'    => (string) ($active['notification_number'] ?? ''),
                'notification_date'      => (string) ($active['notification_date'] ?? ''),
                'application_start_date' => (string) ($active['application_start_date'] ?? ''),
                'application_end_date'   => (string) ($active['application_end_date'] ?? ''),
            ];

            if ($startTs !== false && $nowTs < $startTs) {
                return $base + [
                    'status'  => 'upcoming',
                    'message' => 'Application submission will start on '
                        . self::formatDateTime($active['application_start_date'] ?? null) . '.',
                ];
            }

            if ($endTs !== false && $nowTs > $endTs) {
                return $base + [
                    'status'  => 'closed',
                    'message' => 'The last date for submitting applications was '
                        . self::formatDateTime($active['application_end_date'] ?? null)
                        . '. The application submission period is now closed.',
                ];
            }

            return $base + [
                'status'  => 'closed',
                'message' => 'The application submission period is not open.',
            ];
        } catch (\Throwable $e) {
            return $empty;
        }
    }

    /**
     * Whether the post-submission edit window is open for this notification (date + time).
     */
    public static function isEditWindowOpen(array $notification, ?string $now = null): bool
    {
        $nowTs = strtotime($now ?? date('Y-m-d H:i:s'));
        if ($nowTs === false) {
            return false;
        }

        $start = trim((string) ($notification['edit_window_start_date'] ?? ''));
        $end   = trim((string) ($notification['edit_window_end_date'] ?? ''));

        if ($start === '' && $end === '') {
            return false;
        }
        if ($start !== '') {
            $startTs = strtotime($start);
            if ($startTs === false || $nowTs < $startTs) {
                return false;
            }
        }
        if ($end !== '') {
            $endTs = strtotime($end);
            if ($endTs === false || $nowTs > $endTs) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{open: bool, from: string, to: string, message: string, notification_id: ?int, notification_number: string}
     */
    public static function editWindowInfo(?array $notification = null): array
    {
        if ($notification === null) {
            try {
                $notification = model(self::class)->getActive();
            } catch (\Throwable $e) {
                $notification = null;
            }
        }

        if (! $notification) {
            return [
                'open'                => false,
                'from'                => '',
                'to'                  => '',
                'message'             => '',
                'notification_id'     => null,
                'notification_number' => '',
            ];
        }

        $from = (string) ($notification['edit_window_start_date'] ?? '');
        $to   = (string) ($notification['edit_window_end_date'] ?? '');
        $open = self::isEditWindowOpen($notification);

        $message = $open
            ? 'The Permanent Secretariat has opened a limited window to correct and resubmit your application.'
            : '';

        return [
            'open'                => $open,
            'from'                => $from,
            'to'                  => $to,
            'message'             => $message,
            'notification_id'     => (int) ($notification['id'] ?? 0) ?: null,
            'notification_number' => (string) ($notification['notification_number'] ?? ''),
        ];
    }

    /**
     * Deactivate all other notifications (only one active cycle at a time).
     */
    public function deactivateOthers(int $exceptId): void
    {
        db_connect()->table($this->table)
            ->where('id !=', $exceptId)
            ->update([
                'is_active'  => false,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Options for admin filter dropdowns: [id => label].
     *
     * @return array<int, string>
     */
    public function optionsForFilter(): array
    {
        $rows = $this->orderBy('notification_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['id']] = self::displayLabel($row);
        }

        return $out;
    }

    /**
     * Application counts keyed by notification_id.
     *
     * @return array<int, int>
     */
    public function applicationCounts(): array
    {
        try {
            $rows = db_connect()->table('applications')
                ->select('notification_id, COUNT(*) AS cnt')
                ->where('notification_id IS NOT NULL', null, false)
                ->where('deleted_at IS NULL', null, false)
                ->groupBy('notification_id')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('debug', 'DesignationNotification applicationCounts: ' . $e->getMessage());

            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['notification_id']] = (int) $row['cnt'];
        }

        return $out;
    }
}
