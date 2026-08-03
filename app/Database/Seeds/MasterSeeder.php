<?php

namespace App\Database\Seeds;

use App\Models\MasterRegistry;
use CodeIgniter\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run()
    {
        MasterRegistry::ensureAllDefaults();
    }
}
