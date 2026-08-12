<?php
/**
 * CAPTCHA input block for auth forms.
 *
 * @var string|null $fieldId   Input element id
 * @var string|null $inputName Form field name
 * @var string|null $scope     CaptchaService scope (default|lookup|register|…)
 * @var string|null $label     Optional custom label
 * @var string|null $help      Optional help text
 * @var bool|null   $required  HTML5 required (default true). Set false when captcha
 *                             is validated only via a secondary action (e.g. search).
 */
$fieldId   = $fieldId ?? 'captcha';
$inputName = $inputName ?? 'captcha';
$scope     = \App\Libraries\CaptchaService::normaliseScope($scope ?? null);
$label     = $label ?? 'Security check (CAPTCHA)';
$help      = $help ?? 'Case-insensitive. Click Refresh if the image is hard to read.';
$required  = (bool) ($required ?? true);

$imgQuery = 't=' . time() . '&r=' . bin2hex(random_bytes(2));
if ($scope !== \App\Libraries\CaptchaService::SCOPE_DEFAULT) {
    $imgQuery .= '&scope=' . rawurlencode($scope);
}
?>
<div class="mb-3 captcha-field" data-captcha-scope="<?= esc($scope) ?>">
    <label class="form-label<?= $required ? ' required' : '' ?>" for="<?= esc($fieldId) ?>"><?= esc($label) ?></label>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <img src="<?= base_url('captcha/image') ?>?<?= esc($imgQuery) ?>"
             alt="CAPTCHA image — enter the characters shown"
             class="captcha-image border rounded"
             id="<?= esc($fieldId) ?>Img"
             data-captcha-scope="<?= esc($scope) ?>"
             width="180" height="56"
             decoding="async">
        <button type="button" class="btn btn-outline-secondary captcha-refresh"
                data-captcha-img="#<?= esc($fieldId) ?>Img"
                data-captcha-input="#<?= esc($fieldId) ?>"
                data-captcha-scope="<?= esc($scope) ?>"
                title="Refresh captcha" aria-label="Refresh captcha">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh
        </button>
    </div>
    <input type="text" name="<?= esc($inputName) ?>" id="<?= esc($fieldId) ?>"
           class="form-control text-uppercase captcha-input"
           data-captcha-scope="<?= esc($scope) ?>"
           value=""<?= $required ? ' required' : '' ?> autocomplete="off" autocapitalize="characters"
           spellcheck="false" maxlength="8"
           placeholder="Enter the characters shown above"
           aria-describedby="<?= esc($fieldId) ?>Help">
    <div class="form-text" id="<?= esc($fieldId) ?>Help">
        <?= esc($help) ?>
    </div>
</div>
