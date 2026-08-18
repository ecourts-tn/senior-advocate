<?php

namespace App\Database\Migrations;

use App\Models\ApplicationModel;
use CodeIgniter\Database\Migration;

/**
 * Cumulative experience from courts practiced (Sl. No. 14) periods.
 * Used for display and admin filtering.
 */
class AddCumulativeExpToApplications extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('cumulative_exp_years', 'applications')) {
            $this->forge->addColumn('applications', [
                'cumulative_exp_years' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 3,
                    'null'       => true,
                    'after'      => 'courts_practiced',
                    'comment'    => 'Cumulative years from courts practiced periods (merged)',
                ],
            ]);
        }
        if (! $this->db->fieldExists('cumulative_exp_months', 'applications')) {
            $this->forge->addColumn('applications', [
                'cumulative_exp_months' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 2,
                    'null'       => true,
                    'after'      => 'cumulative_exp_years',
                    'comment'    => 'Cumulative months remainder from courts practiced periods',
                ],
            ]);
        }

        // Backfill from existing courts_practiced JSON
        try {
            $model = model(ApplicationModel::class);
            $rows  = $this->db->table('applications')
                ->select('id, courts_practiced, notification_id')
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $parts = $model->calculateCumulativeCourtExperience(
                    $row['courts_practiced'] ?? null,
                    ApplicationModel::ageAsOnDate($row)
                );
                $this->db->table('applications')
                    ->where('id', (int) $row['id'])
                    ->update([
                        'cumulative_exp_years'  => $parts['years'],
                        'cumulative_exp_months' => $parts['months'],
                    ]);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Cumulative exp backfill skipped: ' . $e->getMessage());
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('cumulative_exp_months', 'applications')) {
            $this->forge->dropColumn('applications', 'cumulative_exp_months');
        }
        if ($this->db->fieldExists('cumulative_exp_years', 'applications')) {
            $this->forge->dropColumn('applications', 'cumulative_exp_years');
        }
    }
}
