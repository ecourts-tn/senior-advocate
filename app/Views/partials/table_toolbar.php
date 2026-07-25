<?php
/**
 * List table toolbar: search + page size (+ optional extra fields via $extraFilters HTML).
 *
 * Expected vars:
 * - string $q
 * - int $perPage
 * - list<int> $allowedPerPage
 * - string|null $action   form action URL (default current)
 * - string|null $placeholder
 * - string|null $extraFilters  raw HTML for extra filter columns (status, etc.)
 * - bool $showSearch (default true)
 */
$action        = $action ?? current_url();
$placeholder   = $placeholder ?? 'Search…';
$showSearch    = $showSearch ?? true;
$allowedPerPage = $allowedPerPage ?? [10, 25, 50, 100];
$extraFilters  = $extraFilters ?? '';
?>
<div class="card card-mhc mb-3 table-toolbar">
    <div class="card-body">
        <form method="get" action="<?= esc($action) ?>" class="row g-2 align-items-end" role="search">
            <?php if ($showSearch): ?>
                <div class="col-md-4 col-lg-4">
                    <label class="form-label" for="listSearch">Search</label>
                    <input type="search" name="q" id="listSearch" class="form-control"
                           value="<?= esc($q ?? '') ?>"
                           placeholder="<?= esc($placeholder) ?>"
                           autocomplete="off">
                </div>
            <?php endif; ?>

            <?= $extraFilters ?>

            <div class="col-6 col-md-2 col-lg-2">
                <label class="form-label" for="listPerPage">Page size</label>
                <select name="per_page" id="listPerPage" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($allowedPerPage as $n): ?>
                        <option value="<?= (int) $n ?>" <?= (int) ($perPage ?? 25) === (int) $n ? 'selected' : '' ?>>
                            <?= (int) $n ?> / page
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-auto">
                <button type="submit" class="btn btn-mhc w-100">
                    <i class="bi bi-funnel me-1" aria-hidden="true"></i> Apply
                </button>
            </div>

            <?php if (! empty($q) || ! empty($hasActiveFilters)): ?>
                <div class="col-12 col-md-auto">
                    <a href="<?= esc($action) ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
