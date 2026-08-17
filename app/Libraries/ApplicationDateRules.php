<?php

namespace App\Libraries;

/**
 * Date constraints for the application-cum-consent form.
 */
class ApplicationDateRules
{
    /**
     * Normalize a posted or stored date to Y-m-d, or null if empty/invalid.
     */
    public static function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === false) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }
        if (! preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
            return null;
        }
        $ymd = $m[1];
        $dt  = \DateTime::createFromFormat('Y-m-d', $ymd);

        return ($dt && $dt->format('Y-m-d') === $ymd) ? $ymd : null;
    }

    /**
     * Latest allowed "Decided on" date: the day before the notification date.
     */
    public static function decidedOnMax(?string $notificationDate): ?string
    {
        $notificationDate = self::parseDate($notificationDate);
        if ($notificationDate === null) {
            return null;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $notificationDate);
        if (! $dt) {
            return null;
        }

        return $dt->modify('-1 day')->format('Y-m-d');
    }

    /**
     * Decided-on must be strictly earlier than the notification date.
     * Empty dates are allowed (the field itself is optional).
     */
    public static function decidedOnIsValid(mixed $decidedOn, ?string $notificationDate): bool
    {
        $date = self::parseDate($decidedOn);
        if ($date === null) {
            return true;
        }
        $notificationDate = self::parseDate($notificationDate);
        if ($notificationDate === null) {
            return true;
        }

        return $date < $notificationDate;
    }

    /**
     * Practice from/to must fall on or after enrolment and on or before the notification date.
     * Empty dates are allowed.
     */
    public static function practiceDateIsValid(mixed $date, ?string $enrolmentDate, ?string $notificationDate): bool
    {
        $parsed = self::parseDate($date);
        if ($parsed === null) {
            return true;
        }
        $enrolmentDate    = self::parseDate($enrolmentDate);
        $notificationDate = self::parseDate($notificationDate);

        if ($enrolmentDate !== null && $parsed < $enrolmentDate) {
            return false;
        }
        if ($notificationDate !== null && $parsed > $notificationDate) {
            return false;
        }

        return true;
    }

    /**
     * @param list<mixed> $dates
     */
    public static function firstInvalidDecidedOn(array $dates, ?string $notificationDate): ?string
    {
        foreach ($dates as $date) {
            if (! self::decidedOnIsValid($date, $notificationDate)) {
                return self::parseDate($date);
            }
        }

        return null;
    }
}
