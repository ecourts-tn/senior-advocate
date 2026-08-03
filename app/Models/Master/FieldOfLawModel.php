<?php

namespace App\Models\Master;

class FieldOfLawModel extends BaseMasterModel
{
    protected $table = 'master_fields_of_law';

    public static function defaultLabels(): array
    {
        return [
            'Constitutional law',
            'Criminal law',
            'Civil law',
            'Arbitration',
            'Corporate law',
            'Family law',
            'Human Rights',
            'Public Interest Litigation (PIL)',
            'International law',
            'Law relating to women',
            'Intellectual Property',
            'Environmental law',
            'Taxation law',
        ];
    }
}
