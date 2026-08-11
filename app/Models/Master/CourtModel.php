<?php

namespace App\Models\Master;

class CourtModel extends BaseMasterModel
{
    protected $table = 'master_courts';

    public static function defaultLabels(): array
    {
        return [
            'Supreme Court of India',
            'High Court of Madras (Principal Seat)',
            'High Court of Madras (Madurai Bench)',
            'Other High Court(s)',
            'District Court(s)',
            'Trial Court(s) / Subordinate Court(s)',
        ];
    }
}
