<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration successful</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.55;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Registration successful</h1>
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
        You may now sign in and begin the Application-cum-Consent Letter for designation as Senior Advocate.
    </p>
    <p style="text-align:center;margin:28px 0;">
        <a href="<?= esc($loginUrl) ?>"
           style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
            Sign in to portal
        </a>
    </p>
    <p style="font-size:0.9rem;color:#6b6558;">
        Please read the Instructions carefully before submitting. Errors cannot be rectified after submission.
    </p>
    <?= view('emails/_contact_footer', ['site' => $site]) ?>
</div>
</body>
</html>
