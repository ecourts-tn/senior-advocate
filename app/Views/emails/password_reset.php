<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password reset</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.5;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <h1 style="font-size:1.25rem;color:#0f2340;margin:0 0 12px;">Password reset request</h1>
    <p>Dear <?= esc($name) ?>,</p>
    <p>
        We received a request to reset the password for your account on the
        <strong>Portal for Designation of Senior Advocates</strong> (High Court of Madras).
    </p>
    <p>
        Click the button below to set a new password. This link is valid for
        <strong><?= esc($expires) ?></strong> and can be used only once.
    </p>
    <p style="text-align:center;margin:28px 0;">
        <a href="<?= esc($resetUrl) ?>"
           style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
            Reset password
        </a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        If the button does not work, copy and paste this URL into your browser:<br>
        <a href="<?= esc($resetUrl) ?>" style="color:#1a3558;word-break:break-all;"><?= esc($resetUrl) ?></a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        If you did not request a password reset, you can ignore this message. Your password will remain unchanged.
    </p>
    <?= view('emails/_contact_footer', ['site' => $site ?? config(\Config\Site::class)]) ?>
</div>
</body>
</html>
