<?php $steps = $steps ?? sad_step_labels(); ?>
<?php if (! empty($isEditWindowEdit) && ! empty($editWindow['open'])): ?>
    <div class="alert alert-info py-2 no-print" role="status">
        <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
        <strong>Edit window:</strong>
        <?= esc($editWindow['message'] ?: 'You may update this application and resubmit from Step 7.') ?>
        <?php if (! empty($editWindow['to'])): ?>
            <span class="small ms-1">(closes <?= esc($editWindow['to']) ?>)</span>
        <?php endif; ?>
    </div>
<?php elseif (! empty($app['status']) && $app['status'] === 'returned'): ?>
    <div class="alert alert-warning py-2 no-print" role="status">
        <i class="bi bi-arrow-return-left me-1" aria-hidden="true"></i>
        This application was returned for correction. Update the form and resubmit from Step 7.
    </div>
<?php endif; ?>
<div class="stepper-wrap no-print">
    <div class="stepper" role="navigation" aria-label="Application steps">
        <?php foreach ($steps as $n => $label): ?>
            <?php
            $class = 'step';
            $isActive = (int) $step === (int) $n;
            $isDone = (int) $n < (int) $step;
            if ($isActive) {
                $class .= ' active';
            } elseif ($isDone) {
                $class .= ' done';
            }
            ?>
            <a href="<?= base_url('applicant/application/step/' . $n) ?>"
               class="<?= $class ?> text-decoration-none"
               <?= $isActive ? 'aria-current="step"' : '' ?>>
                <div class="step-num"><?= $isDone ? '✓' : $n ?></div>
                <div class="fw-semibold" style="font-size:0.78rem;line-height:1.25;"><?= esc($label) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
