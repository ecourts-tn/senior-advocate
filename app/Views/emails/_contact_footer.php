<?php
/**
 * Shared contact footer for portal notification emails.
 * @var \Config\Site|null $site
 */
$org   = isset($site) ? $site->organisation : 'Madras High Court';
$email = isset($site) ? $site->email : 'registrar.admin@hcmadras.tn.gov.in';
$phone = isset($site) ? $site->phone : '+91-44-2534 2190';
$addr  = isset($site) ? $site->address : 'High Court Buildings, Chennai – 600 104, Tamil Nadu, India';
?>
<p style="margin-top:16px;font-size:0.9rem;color:#6b6558;line-height:1.55;">
    For further queries, please contact the
    <strong>Registrar (Administration), Madras High Court</strong>.
</p>
<p style="font-size:0.85rem;color:#6b6558;line-height:1.5;margin:8px 0 0;">
    Email: <a href="mailto:<?= esc($email) ?>" style="color:#1a3558;"><?= esc($email) ?></a><br>
    Phone: <?= esc($phone) ?><br>
    <?= esc($addr) ?>
</p>
<hr style="border:none;border-top:1px solid #d9d2c5;margin:20px 0;">
<p style="font-size:0.8rem;color:#6b6558;margin:0;">
    <?= esc($org) ?> — Registrar (Administration)
</p>
