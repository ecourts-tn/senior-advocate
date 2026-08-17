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

    public function testDecidedOnMustBeBeforeNotificationDate(): void
    {
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('2024-06-14', '2024-06-15'));
        $this->assertFalse(ApplicationDateRules::decidedOnIsValid('2024-06-15', '2024-06-15'));
        $this->assertFalse(ApplicationDateRules::decidedOnIsValid('2024-06-16', '2024-06-15'));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid('', '2024-06-15'));
        $this->assertTrue(ApplicationDateRules::decidedOnIsValid(null, '2024-06-15'));
    }

    public function testDecidedOnMaxIsDayBeforeNotification(): void
    {
        $this->assertSame('2024-06-14', ApplicationDateRules::decidedOnMax('2024-06-15'));
        $this->assertSame('2023-12-31', ApplicationDateRules::decidedOnMax('2024-01-01'));
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
        $this->assertNull(ApplicationDateRules::firstInvalidDecidedOn(['2020-01-01', ''], '2024-06-15'));
        $this->assertSame('2024-06-15', ApplicationDateRules::firstInvalidDecidedOn(['2020-01-01', '2024-06-15'], '2024-06-15'));
    }
}
