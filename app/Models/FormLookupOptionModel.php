<?php

namespace App\Models;

/**
 * @deprecated Use MasterRegistry and App\Models\Master\* table models.
 * Kept as a thin alias so any leftover references keep working.
 */
class FormLookupOptionModel
{
    public const OTHERS_VALUE = MasterRegistry::OTHERS_VALUE;

    public const OTHERS_LABEL = MasterRegistry::OTHERS_LABEL;

    public const CATEGORIES = [
        'qualification'      => 'Educational qualifications',
        'court'              => 'Courts',
        'tribunal'           => 'Tribunals',
        'nature_of_practice' => 'Nature of practice',
        'field_of_law'       => 'Field of law',
    ];

    public function ensureDefaults(): void
    {
        MasterRegistry::ensureAllDefaults();
    }

    /**
     * @return list<string>
     */
    public function labelsFor(string $category): array
    {
        return MasterRegistry::model($category)->activeLabels();
    }

    /**
     * @param list<string>|string|null $selected
     */
    public static function resolveMulti(?array $selected, ?string $other): string
    {
        return MasterRegistry::resolveMulti($selected, $other);
    }

    /**
     * @param list<string> $knownLabels
     *
     * @return array{selected: list<string>, other: string}
     */
    public static function parseMultiStored(?string $stored, array $knownLabels): array
    {
        return MasterRegistry::parseMultiStored($stored, $knownLabels);
    }

    public static function resolveSingle(?string $value, ?string $other): string
    {
        return MasterRegistry::resolveSingle($value, $other);
    }

    /**
     * @param list<string> $knownLabels
     *
     * @return array{value: string, other: string}
     */
    public static function parseSingleStored(?string $stored, array $knownLabels): array
    {
        return MasterRegistry::parseSingleStored($stored, $knownLabels);
    }

    public static function isTruthy(mixed $value): bool
    {
        return MasterRegistry::isTruthy($value);
    }
}
