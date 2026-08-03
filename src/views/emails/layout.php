<!DOCTYPE html>
<html lang="<?= $e(lang()->getCurrentLanguage()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($subject ?? '') ?></title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:32px 12px">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden">
                    <tr>
                        <td style="padding:24px 32px;background:#111827;color:#ffffff;font-size:22px;font-weight:bold">
                            VK-DEV.cz
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px">
                            <?= $content ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background:#f9fafb;color:#6b7280;font-size:12px;text-align:center">
                            &copy; <?= date('Y') ?> VK-DEV.cz
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
