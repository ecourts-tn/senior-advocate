<?php $steps = $steps ?? sad_step_labels(); ?>
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
