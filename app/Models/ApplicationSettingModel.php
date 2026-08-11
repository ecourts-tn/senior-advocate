<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationSettingModel extends Model
{
    protected $table = 'application_settings';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'cycle_year',
        'application_start_date',
        'application_last_date',
        'is_active',
    ];

    protected $useTimestamps = true;

    public function getActiveForCycle(int $cycleYear): ?array
    {
        return $this->where('cycle_year', $cycleYear)
            ->where('is_active', true)
            ->first();
    }

    public function isApplicationOpen(int $cycleYear): bool
    {
        $setting = $this->getActiveForCycle($cycleYear);

        if (!$setting) {
            return false;
        }

        $today = date('Y-m-d');

        return $today >= $setting['application_start_date']
            && $today <= $setting['application_last_date'];
    }
}