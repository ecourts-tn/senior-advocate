<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Official notification PDF / document attached to a designation notification.
 */
class AddDocumentPathToDesignationNotifications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('designation_notifications', [
            'document_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'Relative path under writable/uploads for the official notification file',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('designation_notifications', 'document_path');
    }
}
