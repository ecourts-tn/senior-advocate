<?php

namespace App\Models;

use App\Models\Master\BaseMasterModel;
use App\Models\Master\CourtModel;
use App\Models\Master\FieldOfLawModel;
use App\Models\Master\NatureOfPracticeModel;
use App\Models\Master\QualificationModel;
use App\Models\Master\TribunalModel;

/**
 * Registry of master types → individual table models.
 * Static helpers for form "Others" resolution remain here for shared use.
 */
class MasterRegistry
{
    public const OTHERS_VALUE = '__others__';

    public const OTHERS_LABEL = 'Others';

    /**
     * @var array<string, class-string<BaseMasterModel>>
     */
    public const MODELS = [
        'qualification'      => QualificationModel::class,
        'court'              => CourtModel::class,
        'tribunal'           => TribunalModel::class,
        'nature_of_practice' => NatureOfPracticeModel::class,
        'field_of_law'       => FieldOfLawModel::class,
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'qualification'      => 'Educational qualifications',
            'court'              => 'Courts',
            'tribunal'           => 'Tribunals',
            'nature_of_practice' => 'Nature of practice',
            'field_of_law'       => 'Field of law',
        ];
    }

    public static function modelClass(string $key): ?string
    {
        return self::MODELS[$key] ?? null;
    }

    public static function model(string $key): BaseMasterModel
    {
        $class = self::modelClass($key);
        if ($class === null) {
            throw new \InvalidArgumentException('Unknown master type: ' . $key);
        }

        return model($class);
    }

    public static function ensureAllDefaults(): void
    {
        foreach (array_keys(self::MODELS) as $key) {
            self::model($key)->ensureDefaults();
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public static function allActiveLabels(): array
    {
        $out = [];
        foreach (array_keys(self::MODELS) as $key) {
            $out[$key] = self::model($key)->activeLabels();
        }

        return $out;
    }

    /**
     * @param list<string>|string|null $selected
     */
    public static function resolveMulti(?array $selected, ?string $other): string
    {
        $selected = array_values(array_filter(array_map('trim', $selected ?? []), static fn ($v) => $v !== ''));
        $parts    = [];
        $hasOther = false;

        foreach ($selected as $v) {
            if ($v === self::OTHERS_VALUE || strcasecmp($v, self::OTHERS_LABEL) === 0) {
                $hasOther = true;
                continue;
            }
            $parts[] = $v;
        }

        $other = trim((string) $other);
        if ($hasOther && $other !== '') {
            $parts[] = $other;
        } elseif (! $hasOther && $other !== '' && $selected === []) {
            $parts[] = $other;
        }

        $seen = [];
        $out  = [];
        foreach ($parts as $p) {
            $k = mb_strtolower($p);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[]    = $p;
        }

        return implode(', ', $out);
    }

    /**
     * @param list<string> $knownLabels
     *
     * @return array{selected: list<string>, other: string}
     */
    public static function parseMultiStored(?string $stored, array $knownLabels): array
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return ['selected' => [], 'other' => ''];
        }

        $parts = preg_split('/\s*,\s*|\r\n|\n|\r/', $stored) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), static fn ($v) => $v !== ''));

        $knownMap = [];
        foreach ($knownLabels as $lab) {
            $knownMap[mb_strtolower($lab)] = $lab;
        }

        $selected = [];
        $others   = [];
        foreach ($parts as $p) {
            $k = mb_strtolower($p);
            if (isset($knownMap[$k])) {
                $selected[] = $knownMap[$k];
            } else {
                $others[] = $p;
            }
        }

        if ($others !== []) {
            $selected[] = self::OTHERS_VALUE;
        }

        return [
            'selected' => $selected,
            'other'    => implode(', ', $others),
        ];
    }

    public static function resolveSingle(?string $value, ?string $other): string
    {
        $value = trim((string) $value);
        $other = trim((string) $other);
        if ($value === self::OTHERS_VALUE || strcasecmp($value, self::OTHERS_LABEL) === 0) {
            return $other;
        }

        return $value;
    }

    /**
     * @param list<string> $knownLabels
     *
     * @return array{value: string, other: string}
     */
    public static function parseSingleStored(?string $stored, array $knownLabels): array
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return ['value' => '', 'other' => ''];
        }

        foreach ($knownLabels as $lab) {
            if (strcasecmp($lab, $stored) === 0) {
                return ['value' => $lab, 'other' => ''];
            }
        }

        return ['value' => self::OTHERS_VALUE, 'other' => $stored];
    }

    public static function isTruthy(mixed $value): bool
    {
        return BaseMasterModel::isTruthy($value);
    }
}
