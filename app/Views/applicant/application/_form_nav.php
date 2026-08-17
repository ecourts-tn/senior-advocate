<?php if (! empty($app['id'])): ?>
    <input type="hidden" name="application_id" value="<?= (int) $app['id'] ?>">
    <input type="hidden" name="application_status" value="<?= esc($app['status'] ?? '') ?>">
<?php endif; ?>
<div class="form-actions no-print">
    <div class="form-actions-left">
        <?php if ($step > 1): ?>
            <button type="submit" name="action" value="prev" class="btn btn-outline-secondary" formnovalidate>
                <i class="bi bi-arrow-left"></i> Previous
            </button>
        <?php endif; ?>
        <button type="submit" name="action" value="save" class="btn btn-outline-primary" formnovalidate>
            <i class="bi bi-save"></i> Save Draft
        </button>
    </div>
    <div class="form-actions-right">
        <?php if ($step < 7): ?>
            <button type="submit" name="action" value="next" class="btn btn-mhc">
                Save &amp; Next <i class="bi bi-arrow-right"></i>
            </button>
        <?php else: ?>
            <?php
            $isResubmit = ($app['status'] ?? '') === 'returned';
            $submitLabel = $isResubmit ? 'Resubmit Application' : 'Submit Application';
            $submitConfirm = $isResubmit
                ? 'Resubmit this corrected application to the Registry?'
                : 'Submit this application? Errors cannot be rectified later.';
            ?>
            <button type="submit" name="action" value="submit" class="btn btn-success"
                    onclick="return confirm(<?= json_encode($submitConfirm) ?>);">
                <i class="bi bi-send-check"></i> <?= esc($submitLabel) ?>
            </button>
        <?php endif; ?>
    </div>
</div>
