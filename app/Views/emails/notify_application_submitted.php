<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application submitted</title>
</head>
<body style="font-family:Segoe UI,Arial,sans-serif;line-height:1.55;color:#232323;background:#f5f2ea;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d9d2c5;border-radius:6px;padding:24px;">
    <h1 style="font-size:1.2rem;color:#0f2340;margin:0 0 12px;">Application submitted</h1>
    <p>Dear <?= esc($name) ?>,</p>
    <p>
        Your Application-cum-Consent Letter for designation as Senior Advocate has been
        <strong>submitted successfully</strong>.
    </p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:0.95rem;">
        <tr>
            <td style="padding:8px;border:1px solid #d9d2c5;background:#faf8f3;font-weight:600;width:40%;">Application No.</td>
            <td style="padding:8px;border:1px solid #d9d2c5;"><?= esc($applicationNo) ?></td>
        </tr>
        <tr>
            <td style="padding:8px;border:1px solid #d9d2c5;background:#faf8f3;font-weight:600;">Submitted at</td>
            <td style="padding:8px;border:1px solid #d9d2c5;"><?= esc($submittedAt) ?></td>
        </tr>
    </table>
    <p>
        Please keep this Application Number for future reference. Also submit the prescribed paper book
        to the Permanent Secretariat as per the Instructions.
    </p>
    <p style="text-align:center;margin:28px 0;">
        <a href="<?= esc($viewUrl) ?>"
           style="display:inline-block;background:#0f2340;color:#fff;text-decoration:none;padding:12px 22px;border-radius:4px;font-weight:600;">
            View application
        </a>
    </p>
    <?= view('emails/_contact_footer', ['site' => $site]) ?>
</div>
</body>
</html>
