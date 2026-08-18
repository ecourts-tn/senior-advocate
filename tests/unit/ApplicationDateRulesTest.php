<?php

use App\Libraries\ApplicationDateRules;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ApplicationDateRulesTest extends CIUnitTestCase
{
    public function testParseDateAcceptsIsoAndDatetime(): void
    {
        $this->assertSame('2024-06-15', ApplicationDateRules::parseDate('2024-06-15'));
        $this->assertSame('2024-06-15', ApplicationDateRules::parseDate('2024-06-15 10:30:00'));
        $this->assertNull(ApplicationDateRules::parseDate(''));
        $this->assertNull(ApplicationDateRules::parseDate('15-06-2024'));
        $this->assertNull(ApplicationDateRules::parseDate('2024-13-40'));
    }

    public function testDecidedOnMustBeBetweenEnrolmentAndNotification(): void
    {
        $enrol = '2010-03-01';
        $notif = '2024-06-15';

        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('2010-03-01', $notif, $enrol));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('2024-06-15', $notif, $enrol));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('2018-01-01', $notif, $enrol));
        $this->assertFalse(ApplicationDateRules::decidedOnIsValid('2010-02-28', $notif, $enrol));
        $this->assertFalse(ApplicationDateRules::decidedOnIsValid('2024-06-16', $notif, $enrol));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('', $notif, $enrol));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid(null, $notif, $enrol));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('2024-06-15', $notif, null));
        $this->assertFalse(ApplicationDateRules::decidedOnIsValid('2024-06-16', $notif, null));
    }

    public function testDecidedOnMinAndMaxAreEnrolmentAndNotification(): void
    {
        $this->assertSame('2010-03-01', ApplicationDateRules::decidedOnMin('2010-03-01'));
        $this->assertSame('2024-06-15', ApplicationDateRules::decidedOnMax('2024-06-15'));
        $this->assertSame('2024-01-01', ApplicationDateRules::decidedOnMax('2024-01-01'));
        $this->assertNull(ApplicationDateRules::decidedOnMin(null));
        $this->assertNull(ApplicationDateRules::decidedOnMax(''));
    }

    public function testDateOfBirthCannotBeBefore1900(): void
    {
        $notif = '2026-08-15';

        $this->assertTrue(ApplicationDateRules::dateOfBirthIsValid('1900-01-01', $notif));
        $this->assertTrue(ApplicationDateRules::dateOfBirthIsValid('1980-06-15', $notif));
        $this->assertFalse(ApplicationDateRules::dateOfBirthIsValid('1899-12-31', $notif));
        $this->assertFalse(ApplicationDateRules::dateOfBirthIsValid('2026-08-16', $notif));
        $this->assertTrue(ApplicationDateRules::dateOfBirthIsValid('', $notif));
        $this->assertTrue(ApplicationDateRules::dateOfBirthIsValid(null, $notif));
        $this->assertSame('1900-01-01', ApplicationDateRules::DATE_OF_BIRTH_MIN);
    }

    public function testPracticeDatesMustBeBetweenEnrolmentAndNotification(): void
    {
        $enrol = '2010-03-01';
        $notif = '2026-08-15';

        $this->assertTrue(ApplicationDateRules::practiceDateIsValid('2010-03-01', $enrol, $notif));
        $this->assertTrue(ApplicationDateRules::practiceDateIsValid('2026-08-15', $enrol, $notif));
        $this->assertTrue(ApplicationDateRules::practiceDateIsValid('2018-01-01', $enrol, $notif));
        $this->assertFalse(ApplicationDateRules::practiceDateIsValid('2010-02-28', $enrol, $notif));
        $this->assertFalse(ApplicationDateRules::practiceDateIsValid('2026-08-16', $enrol, $notif));
        $this->assertTrue(ApplicationDateRules::practiceDateIsValid('', $enrol, $notif));
    }

    public function testFirstInvalidDecidedOn(): void
    {
        $this->assertNull(ApplicationDateRules::firstInvalidDecidedOn(['2020-01-01', ''], '2024-06-15', '2010-03-01'));
        $this->assertNull(ApplicationDateRules::firstInvalidDecidedOn(['2020-01-01', '2024-06-15'], '2024-06-15', '2010-03-01'));
        $this->assertSame('2024-06-16', ApplicationDateRules::firstInvalidDecidedOn(['2020-01-01', '2024-06-16'], '2024-06-15', '2010-03-01'));
        $this->assertSame('2010-02-28', ApplicationDateRules::firstInvalidDecidedOn(['2010-02-28', '2020-01-01'], '2024-06-15', '2010-03-01'));
    }
}
