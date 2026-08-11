<?php

namespace App\Models\Master;

class QualificationModel extends BaseMasterModel
{
    protected $table = 'master_qualifications';

    public static function defaultLabels(): array
    {
        return [
            'LL.B.',
            'B.L.',
            'LL.M.',
            'M.L.',
            'Ph.D. in Law',
            'B.A. LL.B.',
            'B.Com. LL.B.',
            'B.B.A. LL.B.',
        ];
    }
}
