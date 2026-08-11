<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Application period and edit window on notifications include date + time.
 */
class NotificationWindowsToDateTime extends Migration
{
    public function up()
    {
        // PostgreSQL: convert DATE → TIMESTAMP; preserve full end-of-day for existing end dates.
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN application_start_date TYPE TIMESTAMP
                USING application_start_date::timestamp
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN application_end_date TYPE TIMESTAMP
                USING (application_end_date::timestamp + TIME '23:59:59')
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN edit_window_start_date TYPE TIMESTAMP
                USING CASE
                    WHEN edit_window_start_date IS NULL THEN NULL
                    ELSE edit_window_start_date::timestamp
                END
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN edit_window_end_date TYPE TIMESTAMP
                USING CASE
                    WHEN edit_window_end_date IS NULL THEN NULL
                    ELSE (edit_window_end_date::timestamp + TIME '23:59:59')
                END
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN application_start_date TYPE DATE
                USING application_start_date::date
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN application_end_date TYPE DATE
                USING application_end_date::date
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN edit_window_start_date TYPE DATE
                USING edit_window_start_date::date
        ");
        $this->db->query("
            ALTER TABLE designation_notifications
            ALTER COLUMN edit_window_end_date TYPE DATE
                USING edit_window_end_date::date
        ");
    }
}
