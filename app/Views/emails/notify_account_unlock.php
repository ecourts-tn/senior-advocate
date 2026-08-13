<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unlock your account</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.55;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Your account is locked</h1>
    <p>Dear <?= esc($name) ?>,</p>
    <p>
        Your account on the
        <strong><?= esc($site->portalName) ?></strong>
        was locked after several unsuccessful sign-in attempts.
    </p>
    <p>Registered email: <strong><?= esc($email) ?></strong></p>
    <p>
        Click the button below to unlock your account. This link is valid for
        <strong><?= esc($expires ?? '1 hour') ?></strong> and can be used only once.
    </p>
    <p style="text-align:center;margin:28px 0;">
        <a href="<?= esc($unlockUrl) ?>"
           style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
            Unlock account
        </a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        If the button does not work, copy and paste this URL into your browser:<br>
        <a href="<?= esc($unlockUrl) ?>" style="color:#1a3558;word-break:break-all;"><?= esc($unlockUrl) ?></a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        If you did not try to sign in, someone else may have used your email address.
        You can ignore this message if you do not need to unlock the account, or use
        Forgot password after unlocking if you do not recognise the attempts.
    </p>
</div>
</body>
</html>
