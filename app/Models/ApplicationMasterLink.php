<?php

namespace App\Models;

use App\Models\Master\BaseMasterModel;
use CodeIgniter\Database\BaseConnection;

/**
 * One-to-many links between applications and multi-select masters.
 * Masters: qualification, nature_of_practice, field_of_law.
 */
class ApplicationMasterLink
{
    /**
     * @var array<string, array{table: string, master: class-string<BaseMasterModel>, fk: string, app_column: string}>
     */
    public const MULTI = [
        'qualification' => [
            'table'      => 'application_qualifications',
            'master'     => Master\QualificationModel::class,
            'fk'         => 'qualification_id',
            'app_column' => 'qualifications',
        ],
        'nature_of_practice' => [
            'table'      => 'application_nature_of_practice',
            'master'     => Master\NatureOfPracticeModel::class,
            'fk'         => 'nature_id',
            'app_column' => 'nature_of_practice',
        ],
        'field_of_law' => [
            'table'      => 'application_fields_of_law',
            'master'     => Master\FieldOfLawModel::class,
            'fk'         => 'field_id',
            'app_column' => 'field_of_law',
        ],
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Replace all multi-select links for a master type from posted selections.
     *
     * @param list<string>|string|null $selected  Labels and/or __others__
     *
     * @return string Display string for denormalised applications column
     */
    public function syncMulti(int $applicationId, string $masterKey, $selected, ?string $otherText = null): string
    {
        $meta = self::MULTI[$masterKey] ?? null;
        if ($meta === null) {
            throw new \InvalidArgumentException('Unknown multi master: ' . $masterKey);
        }

        if (! is_array($selected)) {
            $selected = ($selected !== null && $selected !== '') ? [(string) $selected] : [];
        }

        /** @var BaseMasterModel $masterModel */
        $masterModel = model($meta['master']);
        $masterRows  = $masterModel->findAll();
        $byLabel     = [];
        foreach ($masterRows as $row) {
            $byLabel[mb_strtolower(trim((string) $row['label']))] = (int) $row['id'];
        }

        $links   = [];
        $display = [];
        $order   = 0;
        $hasOther = false;

        foreach ($selected as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            if ($value === MasterRegistry::OTHERS_VALUE
                || strcasecmp($value, MasterRegistry::OTHERS_LABEL) === 0) {
                $hasOther = true;
                continue;
            }
            $key = mb_strtolower($value);
            if (isset($byLabel[$key])) {
                $links[] = [
                    'application_id' => $applicationId,
                    $meta['fk']      => $byLabel[$key],
                    'other_text'     => null,
                    'sort_order'     => $order,
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
                $display[] = $value;
                $order += 10;
            } else {
                // Unknown posted label treated as free text
                $links[] = [
                    'application_id' => $applicationId,
                    $meta['fk']      => null,
                    'other_text'     => $value,
                    'sort_order'     => $order,
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
                $display[] = $value;
                $order += 10;
            }
        }

        $otherText = trim((string) $otherText);
        if ($hasOther && $otherText !== '') {
            $links[] = [
                'application_id' => $applicationId,
                $meta['fk']      => null,
                'other_text'     => $otherText,
                'sort_order'     => $order,
                'created_at'     => date('Y-m-d H:i:s'),
            ];
            $display[] = $otherText;
        }

        // Replace children
        $this->db->table($meta['table'])->where('application_id', $applicationId)->delete();
        foreach ($links as $link) {
            $this->db->table($meta['table'])->insert($link);
        }

        // Unique display labels
        $seen = [];
        $out  = [];
        foreach ($display as $d) {
            $k = mb_strtolower($d);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[]    = $d;
        }

        return implode(', ', $out);
    }

    /**
     * Load multi-select state for form redisplay.
     *
     * @return array{selected: list<string>, other: string, display: string, rows: list<array>}
     */
    public function loadMulti(int $applicationId, string $masterKey): array
    {
        $meta = self::MULTI[$masterKey] ?? null;
        if ($meta === null) {
            throw new \InvalidArgumentException('Unknown multi master: ' . $masterKey);
        }

        /** @var BaseMasterModel $masterModel */
        $masterModel = model($meta['master']);
        $masterTable = $masterModel->getTable();
        $fk          = $meta['fk'];

        $builder = $this->db->table($meta['table'] . ' AS link')
            ->select("link.*, m.label AS master_label")
            ->join("{$masterTable} AS m", "m.id = link.{$fk}", 'left')
            ->where('link.application_id', $applicationId)
            ->orderBy('link.sort_order', 'ASC')
            ->orderBy('link.id', 'ASC');

        $rows = $builder->get()->getResultArray();

        $selected = [];
        $others   = [];
        $display  = [];

        foreach ($rows as $row) {
            if (! empty($row[$fk]) && ! empty($row['master_label'])) {
                $selected[] = (string) $row['master_label'];
                $display[]  = (string) $row['master_label'];
            } elseif (! empty($row['other_text'])) {
                $others[]  = (string) $row['other_text'];
                $display[] = (string) $row['other_text'];
            }
        }

        if ($others !== []) {
            $selected[] = MasterRegistry::OTHERS_VALUE;
        }

        return [
            'selected' => $selected,
            'other'    => implode(', ', $others),
            'display'  => implode(', ', $display),
            'rows'     => $rows,
        ];
    }

    /**
     * Attach multi-master data onto an application array for views/PDF.
     *
     * @param array<string, mixed> $app
     *
     * @return array<string, mixed>
     */
    public function hydrateApplication(array $app): array
    {
        $id = (int) ($app['id'] ?? 0);
        if ($id <= 0) {
            return $app;
        }

        foreach (self::MULTI as $key => $meta) {
            $loaded = $this->loadMulti($id, $key);
            $app[$meta['app_column']] = $loaded['display'] !== ''
                ? $loaded['display']
                : ($app[$meta['app_column']] ?? '');
            $app['_multi'][$key] = [
                'selected' => $loaded['selected'],
                'other'    => $loaded['other'],
                'rows'     => $loaded['rows'],
            ];
        }

        return $app;
    }
}
