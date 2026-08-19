<?php

use App\Models\DesignationNotificationModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class NotificationAdminDateTest extends CIUnitTestCase
{
    public function testValidChainIncludingFutureWindows(): void
    {
        $this->assertNull(DesignationNotificationModel::validateAdminDates([
            'notification_date'      => '2026-08-18',
            'application_start_date' => '2026-08-20 09:00',
            'application_end_date'   => '2026-09-15 17:00',
            'edit_window_start_date' => '2026-09-15 17:00',
            'edit_window_end_date'   => '2026-09-30 18:00',
        ], '2026-08-18'));
    }

    public function testNotificationDateCannotBeFuture(): void
    {
        $this->assertSame(
            'Notification date cannot be a future date.',
            DesignationNotificationModel::validateAdminDates([
                'notification_date'      => '2026-08-19',
                'application_start_date' => '2026-08-19 09:00',
                'application_end_date'   => '2026-08-20 17:00',
            ], '2026-08-18')
        );
    }

    public function testApplicationStartCannotPrecedeNotificationDate(): void
    {
        $this->assertSame(
            'Application start date cannot be earlier than the notification date.',
            DesignationNotificationModel::validateAdminDates([
                'notification_date'      => '2026-08-18',
                'application_start_date' => '2026-08-17 09:00',
                'application_end_date'   => '2026-08-20 17:00',
            ], '2026-08-18')
        );
    }

    public function testApplicationEndCannotPrecedeStart(): void
    {
        $this->assertSame(
            'Application end date/time cannot be earlier than the application start date/time.',
            DesignationNotificationModel::validateAdminDates([
                'notification_date'      => '2026-08-18',
                'application_start_date' => '2026-08-20 17:00',
                'application_end_date'   => '2026-08-20 09:00',
            ], '2026-08-18')
        );
    }

    public function testEditStartCannotPrecedeApplicationEnd(): void
    {
        $this->assertSame(
            'Edit window start date/time cannot be earlier than the application end date/time.',
            DesignationNotificationModel::validateAdminDates([
                'notification_date'      => '2026-08-18',
                'application_start_date' => '2026-08-18 09:00',
                'application_end_date'   => '2026-08-25 17:00',
                'edit_window_start_date' => '2026-08-25 16:00',
                'edit_window_end_date'   => '2026-08-30 18:00',
            ], '2026-08-18')
        );
    }

    public function testEditEndCannotPrecedeEditStart(): void
    {
        $this->assertSame(
            'Edit window end date/time cannot be earlier than the edit window start date/time.',
            DesignationNotificationModel::validateAdminDates([
                'notification_date'      => '2026-08-18',
                'application_start_date' => '2026-08-18 09:00',
                'application_end_date'   => '2026-08-25 17:00',
                'edit_window_start_date' => '2026-08-26 09:00',
                'edit_window_end_date'   => '2026-08-26 08:00',
            ], '2026-08-18')
        );
    }

    public function testEditWindowRequiresBothEnds(): void
    {
        $this->assertSame(
            'Edit window start and end dates must both be entered.',
            DesignationNotificationModel::validateAdminDates([
                'notification_date'      => '2026-08-18',
                'application_start_date' => '2026-08-18 09:00',
                'application_end_date'   => '2026-08-18 17:00',
                'edit_window_start_date' => '2026-08-18 17:00',
                'edit_window_end_date'   => '',
            ], '2026-08-18')
        );
    }
}
