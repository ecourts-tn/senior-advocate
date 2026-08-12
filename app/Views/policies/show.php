<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title"><?= esc($policy['title']) ?></h1>
    <p class="page-subtitle">
        Last updated: <?= esc($lastUpdated) ?>
    </p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <article class="card card-mhc policy-article">
            <div class="card-body">
                <?php foreach ($policy['body'] as $para): ?>
                    <p class="policy-para"><?= esc($para) ?></p>
                <?php endforeach; ?>
            </div>
        </article>
    </div>
    <div class="col-lg-4">
        <nav class="card card-mhc" aria-label="Policy documents">
            <div class="card-header">Related policies</div>
            <div class="list-group list-group-flush policy-nav">
                <?php foreach ($policies as $key => $item): ?>
                    <a class="list-group-item list-group-item-action <?= $key === $slug ? 'active' : '' ?>"
                       href="<?= base_url('policy/' . $key) ?>">
                        <?= esc($item['title']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
    </div>
</div>

<?= $this->endSection() ?>
