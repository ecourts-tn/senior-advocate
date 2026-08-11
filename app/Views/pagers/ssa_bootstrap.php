<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Bootstrap 5 pagination for SSA portal lists.
 *
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav aria-label="Table pagination">
    <ul class="pagination pagination-sm mb-0 flex-wrap">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="First">&laquo;</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Previous">&lsaquo;</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>>
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Next">&rsaquo;</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Last">&raquo;</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        <?php endif ?>
    </ul>
</nav>
