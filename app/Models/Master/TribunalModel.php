<?php

namespace App\Models\Master;

class TribunalModel extends BaseMasterModel
{
    protected $table = 'master_tribunals';

    public static function defaultLabels(): array
    {
        return [
            'National Company Law Tribunal (NCLT)',
            'National Company Law Appellate Tribunal (NCLAT)',
            'Central Administrative Tribunal (CAT)',
            'Income Tax Appellate Tribunal (ITAT)',
            'Customs, Excise and Service Tax Appellate Tribunal (CESTAT)',
            'Debt Recovery Tribunal (DRT) / DRAT',
            'National Green Tribunal (NGT)',
            'Armed Forces Tribunal',
            'Consumer Disputes Redressal Commission',
        ];
    }
}
