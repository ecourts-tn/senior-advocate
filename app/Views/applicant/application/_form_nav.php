<div class="form-actions no-print">
    <div class="form-actions-left">
        <?php if ($step > 1): ?>
            <button type="submit" name="action" value="prev" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Previous
            </button>
        <?php endif; ?>
        <button type="submit" name="action" value="save" class="btn btn-outline-primary">
            <i class="bi bi-save"></i> Save Draft
        </button>
    </div>
    <div class="form-actions-right">
        <?php if ($step < 7): ?>
            <button type="submit" name="action" value="next" class="btn btn-mhc">
                Save &amp; Next <i class="bi bi-arrow-right"></i>
            </button>
        <?php else: ?>
            <button type="submit" name="action" value="submit" class="btn btn-gold"
                    onclick="return confirm('Submit this application? Errors cannot be rectified later.');">
                <i class="bi bi-send-check"></i> Submit Application
            </button>
        <?php endif; ?>
    </div>
</div>
