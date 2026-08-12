<?php

namespace App\Models\Master;

use CodeIgniter\Model;

/**
 * Shared behaviour for individual master tables.
 */
abstract class BaseMasterModel extends Model
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'label', 'sort_order', 'is_active', 'updated_by',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** @return list<string> */
    abstract public static function defaultLabels(): array;

    /**
     * Insert any missing default labels (does not overwrite existing rows).
     */
    public function ensureDefaults(): void
    {
        $order = 1;
        foreach (static::defaultLabels() as $label) {
            $exists = $this->where('label', $label)->first();
            if ($exists) {
                $order += 1;
                continue;
            }
            $this->insert([
                'label'      => $label,
                'sort_order' => $order,
                'is_active'  => true,
            ]);
            $order += 1;
        }
    }

    /**
     * Active labels ordered for form dropdowns.
     *
     * @return list<string>
     */
    public function activeLabels(): array
    {
        $rows = $this->where('is_active', true)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('label', 'ASC')
            ->findAll();

        return array_values(array_map(static fn ($r) => (string) $r['label'], $rows));
    }

    public static function isTruthy(mixed $value): bool
    {
        return $value === true
            || $value === 1
            || $value === '1'
            || $value === 't'
            || $value === 'true';
    }
}
