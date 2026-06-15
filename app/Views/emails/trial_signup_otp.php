<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Email verification</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f9; margin: 0; padding: 24px;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 520px; margin: 0 auto;">
    <tr>
      <td style="background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 24px rgba(15,23,42,0.08);">
        <h1 style="margin: 0 0 8px; font-size: 22px; color: #0d2137;">Verify your email</h1>
        <p style="margin: 0 0 20px; color: #475569; line-height: 1.5;">
          Hi <?= esc($firstName) ?>, use the code below to complete your <?= esc($productName) ?> signup.
        </p>
        <div style="text-align: center; margin: 28px 0;">
          <span style="display: inline-block; letter-spacing: 0.35em; font-size: 32px; font-weight: 700; color: #1e4d8c; padding: 16px 24px; background: #eff6ff; border-radius: 10px;">
            <?= esc($code) ?>
          </span>
        </div>
        <p style="margin: 0 0 8px; color: #64748b; font-size: 14px; line-height: 1.5;">
          This code expires in <?= (int) $ttlMinutes ?> minutes. If you did not request this, you can safely ignore this email.
        </p>
        <p style="margin: 24px 0 0; color: #94a3b8; font-size: 12px;">
          &mdash; <?= esc($productName) ?>
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
