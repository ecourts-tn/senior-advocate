<?php

namespace App\Libraries;

/**
 * Date constraints for the application-cum-consent form.
 */
class ApplicationDateRules
{
    public const DATE_OF_BIRTH_MIN = '1900-01-01';

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
     * Date of birth must be on or after 1900-01-01 (and on or before the notification date when given).
     * Empty dates are allowed (required-ness is checked separately).
     */
    public static function dateOfBirthIsValid(mixed $dateOfBirth, ?string $notificationDate = null): bool
    {
        $date = self::parseDate($dateOfBirth);
        if ($date === null) {
            return true;
        }
        if ($date < self::DATE_OF_BIRTH_MIN) {
            return false;
        }
        $notificationDate = self::parseDate($notificationDate);
        if ($notificationDate !== null && $date > $notificationDate) {
            return false;
        }

        return true;
    }

    /**
     * Earliest allowed "Decided on" date: the date of enrolment.
     */
    public static function decidedOnMin(?string $enrolmentDate): ?string
    {
        return self::parseDate($enrolmentDate);
    }

    /**
     * Latest allowed "Decided on" date: the notification date (inclusive).
     */
    public static function decidedOnMax(?string $notificationDate): ?string
    {
        return self::parseDate($notificationDate);
    }

    /**
     * Decided-on must fall on or after enrolment and on or before the notification date.
     * Empty dates are allowed (the field itself is optional).
     */
    public static function decidedOnIsValid(
        mixed $decidedOn,
        ?string $notificationDate,
        ?string $enrolmentDate = null
    ): bool {
        $date = self::parseDate($decidedOn);
        if ($date === null) {
            return true;
        }
        $enrolmentDate    = self::parseDate($enrolmentDate);
        $notificationDate = self::parseDate($notificationDate);

        if ($enrolmentDate !== null && $date < $enrolmentDate) {
            return false;
        }
        if ($notificationDate !== null && $date > $notificationDate) {
            return false;
        }

        return true;
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
    public static function firstInvalidDecidedOn(
        array $dates,
        ?string $notificationDate,
        ?string $enrolmentDate = null
    ): ?string {
        foreach ($dates as $date) {
            if (! self::decidedOnIsValid($date, $notificationDate, $enrolmentDate)) {
                return self::parseDate($date);
            }
        }

        return null;
    }
}
