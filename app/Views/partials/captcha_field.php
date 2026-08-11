<?php
/**
 * CAPTCHA input block for auth forms.
 * Expects nothing; loads image from captcha/image route.
 */
$fieldId = $fieldId ?? 'captcha';
$inputName = $inputName ?? 'captcha';
?>
<div class="mb-3 captcha-field">
    <label class="form-label required" for="<?= esc($fieldId) ?>">Security check (CAPTCHA)</label>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <img src="<?= base_url('captcha/image') ?>?t=<?= time() ?>"
             alt="CAPTCHA image — enter the characters shown"
             class="captcha-image border rounded"
             id="<?= esc($fieldId) ?>Img"
             width="180" height="56"
             decoding="async">
        <button type="button" class="btn btn-outline-secondary captcha-refresh"
                data-captcha-img="#<?= esc($fieldId) ?>Img"
                title="Refresh captcha" aria-label="Refresh captcha">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh
        </button>
    </div>
    <input type="text" name="<?= esc($inputName) ?>" id="<?= esc($fieldId) ?>"
           class="form-control text-uppercase"
           value="" required autocomplete="off" autocapitalize="characters"
           spellcheck="false" maxlength="8"
           placeholder="Enter the characters shown above"
           aria-describedby="<?= esc($fieldId) ?>Help">
    <div class="form-text" id="<?= esc($fieldId) ?>Help">
        Case-insensitive. Click Refresh if the image is hard to read.
    </div>
</div>
