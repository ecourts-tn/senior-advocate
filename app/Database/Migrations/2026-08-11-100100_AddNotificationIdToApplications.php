<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Link each application to the designation notification under which it was filed.
 */
class AddNotificationIdToApplications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('applications', [
            'notification_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK to designation_notifications.id',
            ],
        ]);

        $this->db->query('CREATE INDEX IF NOT EXISTS applications_notification_id ON applications (notification_id)');
    }

    public function down()
    {
        $this->forge->dropColumn('applications', 'notification_id');
    }
}
