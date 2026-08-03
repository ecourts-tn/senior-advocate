<?php

namespace App\Database\Seeds;

use App\Models\NotificationTemplateModel;
use CodeIgniter\Database\Seeder;

/**
 * Seeds default email/SMS notification templates for portal events.
 */
class NotificationTemplateSeeder extends Seeder
{
    public function run()
    {
        $model = model(NotificationTemplateModel::class);
        $model->ensureDefaults();
    }
}
