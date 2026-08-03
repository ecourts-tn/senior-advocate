<?php

namespace App\Models\Master;

class NatureOfPracticeModel extends BaseMasterModel
{
    protected $table = 'master_nature_of_practice';

    public static function defaultLabels(): array
    {
        return [
            'Civil',
            'Criminal',
            'Constitutional',
            'Taxation',
            'Labour / Industrial',
            'Company / Corporate',
            'Service',
            'Arbitration',
            'Writ / Public Law',
        ];
    }
}
