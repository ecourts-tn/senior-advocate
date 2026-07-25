<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application <?= esc($decisionLabel) ?></title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.55;color:#232323;background:#f5f2ea;padding:24px;">
<?php
$isApproved = ($decision ?? '') === 'approved';
$bannerBg   = $isApproved ? '#1b6b3a' : '#9b1c1c';
?>
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <div style="background:<?= $bannerBg ?>;color:#fff;padding:10px 14px;border-radius:4px;font-weight:600;margin-bottom:16px;">
        Application <?= esc($decisionLabel) ?>
    </div>
    <p>Dear <?= esc($name) ?>,</p>
    <p>
        Your application
        <strong><?= esc($applicationNo) ?></strong>
        for designation as Senior Advocate has been
        <strong><?= esc(strtolower($decisionLabel)) ?></strong>
        by the High Court of Madras.
    </p>
    <?php if (! empty($remarks)): ?>
        <p style="margin:16px 0 8px;font-weight:600;">Remarks</p>
        <div style="background:#faf8f3;border:1px solid #d9d2c5;border-radius:4px;padding:12px;white-space:pre-wrap;"><?= esc($remarks) ?></div>
    <?php endif; ?>
    <?= view('emails/_contact_footer', ['site' => $site]) ?>
</div>
</body>
</html>
