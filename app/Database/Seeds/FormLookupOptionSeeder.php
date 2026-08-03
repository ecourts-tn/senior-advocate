<?php

namespace App\Database\Seeds;

use App\Models\MasterRegistry;
use CodeIgniter\Database\Seeder;

/**
 * Seeds default rows into individual master tables.
 */
class FormLookupOptionSeeder extends Seeder
{
    public function run()
    {
        MasterRegistry::ensureAllDefaults();
    }
}
