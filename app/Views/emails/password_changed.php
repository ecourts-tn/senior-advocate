<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password changed</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.5;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <h1 style="font-size:1.25rem;color:#0f2340;margin:0 0 12px;">Password changed</h1>
    <p>Dear <?= esc($name) ?>,</p>
    <p>
        The password for your account on the
        <strong>Portal for Designation of Senior Advocates</strong> was changed successfully.
    </p>
    <p>
        If you did not make this change, please contact the
        <strong>Registrar (Administration), Madras High Court</strong> immediately
        and use the “Forgot password” option if you still have access to your registered email.
    </p>
    <?= view('emails/_contact_footer', ['site' => $site ?? config(\Config\Site::class)]) ?>
</div>
</body>
</html>
