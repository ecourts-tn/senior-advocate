<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify your email</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.55;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Verify your email address</h1>
    <p>Dear <?= esc($name) ?>,</p>
    <p>
        Your account has been created on the
        <strong><?= esc($site->portalName) ?></strong> portal of the
        <?= esc($site->organisation) ?>.
    </p>
    <p>
        Registered email: <strong><?= esc($email) ?></strong>
    </p>
    <p>
        Please verify your email address to activate your account. You will not be able to sign in
        until verification is complete. This link is valid for
        <strong><?= esc($expires ?? '48 hours') ?></strong>.
    </p>
    <p style="text-align:center;margin:28px 0;">
        <a href="<?= esc($verifyUrl) ?>"
           style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
            Verify email address
        </a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        If the button does not work, copy and paste this URL into your browser:<br>
        <a href="<?= esc($verifyUrl) ?>" style="color:#1a3558;word-break:break-all;"><?= esc($verifyUrl) ?></a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        If you did not register on this portal, you can ignore this message.
    </p>
</div>
</body>
</html>
