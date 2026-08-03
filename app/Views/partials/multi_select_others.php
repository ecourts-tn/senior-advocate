<?php
/**
 * Multi-select checkboxes with an "Others" free-text field.
 *
 * Vars:
 * - string $name          base field name (e.g. qualifications)
 * - list<string> $options known labels
 * - list<string> $selected selected values (may include MasterRegistry::OTHERS_VALUE)
 * - string $other         free text when Others is chosen
 * - bool $required
 * - string|null $help
 */
use App\Models\MasterRegistry;

$name     = $name ?? 'field';
$options  = $options ?? [];
$selected = $selected ?? [];
$other    = $other ?? '';
$required = ! empty($required);
$help     = $help ?? null;
$othersVal = MasterRegistry::OTHERS_VALUE;
$hasOthers = in_array($othersVal, $selected, true)
    || in_array(MasterRegistry::OTHERS_LABEL, $selected, true);
?>
<div class="lookup-multi" data-others-group>
    <div class="row g-2">
        <?php foreach ($options as $opt): ?>
            <?php $id = $name . '_' . substr(md5($opt), 0, 8); ?>
            <div class="col-md-6 col-lg-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="<?= esc($name) ?>[]" value="<?= esc($opt) ?>"
                           id="<?= esc($id) ?>"
                           <?= in_array($opt, $selected, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= esc($id) ?>"><?= esc($opt) ?></label>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-md-6 col-lg-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       name="<?= esc($name) ?>[]" value="<?= esc($othersVal) ?>"
                       id="<?= esc($name) ?>_others"
                       data-others-trigger
                       <?= $hasOthers ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= esc($name) ?>_others">Others</label>
            </div>
        </div>
    </div>
    <div class="mt-2" data-others-field <?= $hasOthers ? '' : 'hidden' ?>>
        <label class="form-label" for="<?= esc($name) ?>_other_text">Please specify (Others)</label>
        <input type="text" name="<?= esc($name) ?>_other" id="<?= esc($name) ?>_other_text"
               class="form-control" maxlength="500"
               value="<?= esc($other) ?>"
               placeholder="Enter other value"
               <?= $hasOthers ? '' : 'disabled' ?>>
    </div>
    <?php if ($help): ?>
        <div class="form-text"><?= esc($help) ?></div>
    <?php endif; ?>
    <?php if ($required): ?>
        <div class="form-text text-muted">Select at least one option (or Others with a value).</div>
    <?php endif; ?>
</div>
