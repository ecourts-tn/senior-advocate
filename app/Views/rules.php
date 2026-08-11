<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$site          = $site ?? config(\Config\Site::class);
$rulesEmbedUrl = $rulesEmbedUrl ?? site_url('rules/view');
$rulesTitle    = $site->rulesTitle ?? 'Rules for Designation of Senior Advocates, 2026';
$embedSrc      = $rulesEmbedUrl . (str_contains($rulesEmbedUrl, '?') ? '&' : '?') . 'embedded=1#toolbar=1&navpanes=0&view=FitH';
?>

<div class="rules-page">
    <div class="rules-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="page-title mb-0"><?= esc($rulesTitle) ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('rules/download') ?>" class="btn btn-mhc">
                <i class="bi bi-download me-1" aria-hidden="true"></i> Download PDF
            </a>
            <a href="<?= esc($rulesEmbedUrl) ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-fullscreen me-1" aria-hidden="true"></i> Full page
                <span class="visually-hidden">(opens in a new tab)</span>
            </a>
        </div>
    </div>

    <div class="card card-mhc rules-viewer-card mb-0">
        <div class="card-body p-0">
            <div class="rules-pdf-frame-wrap">
                <object
                    class="rules-pdf-object"
                    data="<?= esc($embedSrc) ?>"
                    type="application/pdf"
                    aria-label="<?= esc($rulesTitle) ?>">
                    <iframe
                        class="rules-pdf-frame"
                        src="<?= esc($embedSrc) ?>"
                        title="<?= esc($rulesTitle) ?>"
                        loading="eager"
                        allow="fullscreen">
                    </iframe>
                    <div class="rules-pdf-fallback p-4 text-center">
                        <p class="mb-3">Your browser cannot display PDFs inline.</p>
                        <a href="<?= base_url('rules/download') ?>" class="btn btn-mhc me-2">
                            <i class="bi bi-download me-1"></i> Download PDF
                        </a>
                        <a href="<?= esc($rulesEmbedUrl) ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                            Open PDF
                        </a>
                    </div>
                </object>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
