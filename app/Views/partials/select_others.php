<?php
/**
 * Single select with "Others" free-text field (for dynamic rows).
 *
 * Vars:
 * - string $name        select name e.g. court_name[]
 * - string $otherName   other text name e.g. court_other[]
 * - list<string> $options
 * - string $value       selected option or OTHERS_VALUE
 * - string $other
 * - string|null $placeholder
 * - bool $showLabel
 * - string|null $label
 */
use App\Models\MasterRegistry;

$name        = $name ?? 'field';
$otherName   = $otherName ?? ($name . '_other');
$options     = $options ?? [];
$value       = $value ?? '';
$other       = $other ?? '';
$placeholder = $placeholder ?? 'Select…';
$showLabel   = $showLabel ?? false;
$label       = $label ?? 'Option';
$othersVal   = MasterRegistry::OTHERS_VALUE;
$isOthers    = ($value === $othersVal) || strcasecmp((string) $value, MasterRegistry::OTHERS_LABEL) === 0;
?>
<div data-others-group>
    <?php if ($showLabel): ?>
        <label class="form-label"><?= esc($label) ?></label>
    <?php endif; ?>
    <select name="<?= esc($name) ?>" class="form-select" data-others-trigger>
        <option value=""><?= esc($placeholder) ?></option>
        <?php foreach ($options as $opt): ?>
            <option value="<?= esc($opt) ?>" <?= $value === $opt ? 'selected' : '' ?>><?= esc($opt) ?></option>
        <?php endforeach; ?>
        <option value="<?= esc($othersVal) ?>" <?= $isOthers ? 'selected' : '' ?>>Others</option>
    </select>
    <div class="mt-1" data-others-field <?= $isOthers ? '' : 'hidden' ?>>
        <input type="text" name="<?= esc($otherName) ?>" class="form-control form-control-sm"
               value="<?= esc($other) ?>"
               placeholder="Please specify"
               maxlength="255"
               <?= $isOthers ? '' : 'disabled' ?>>
    </div>
</div>
