<?php
/**
 * List table footer: result summary + pagination links.
 *
 * Expected:
 * - CodeIgniter\Pager\Pager $pager
 * - int $total
 * - int $perPage
 * - int $page
 * - string|null $group  pager group name (default 'default')
 */
$group   = $group ?? 'default';
$total   = (int) ($total ?? 0);
$perPage = max(1, (int) ($perPage ?? 25));
$page    = max(1, (int) ($page ?? 1));
$from    = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
$to      = min($total, $page * $perPage);
?>
<div class="table-footer d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3 border-top">
    <div class="small text-muted">
        <?php if ($total === 0): ?>
            No records found
        <?php else: ?>
            Showing <strong><?= $from ?></strong>–<strong><?= $to ?></strong>
            of <strong><?= $total ?></strong>
        <?php endif; ?>
    </div>
    <?php if ($total > $perPage && isset($pager)): ?>
        <div class="table-pager">
            <?= $pager->links($group, 'ssa_bootstrap') ?>
        </div>
    <?php endif; ?>
</div>
