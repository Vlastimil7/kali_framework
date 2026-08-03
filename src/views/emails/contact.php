<?php
$fullName = trim((string) ($form['fullName'] ?? (($form['firstName'] ?? '') . ' ' . ($form['lastName'] ?? ''))));
$rows = [
    'Jméno' => $fullName,
    'Email' => $form['email'] ?? '',
    'Telefon' => $form['phone'] ?? '',
    'Typ projektu' => $form['topic'] ?? '',
    'Rozpočet' => $form['budget'] ?? '',
    'Lokalita / provozovna' => $form['clinic'] ?? '',
];
?>
<h1 style="margin:0 0 20px;font-size:28px">Nová poptávka</h1>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse">
    <?php foreach ($rows as $label => $value): ?>
        <?php if ((string) $value !== ''): ?>
            <tr>
                <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;color:#6b7280;width:160px"><?= $e($label) ?></td>
                <td style="padding:10px 12px;border-bottom:1px solid #e5e7eb"><?= $e($value) ?></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

<h2 style="margin:28px 0 10px;font-size:18px">Zpráva</h2>
<div style="padding:16px;background:#f9fafb;border-radius:8px;line-height:1.6">
    <?= nl2br($e($form['message'] ?? '')) ?>
</div>
