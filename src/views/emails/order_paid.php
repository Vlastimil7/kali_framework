<h1 style="margin:0 0 20px;font-size:28px">Děkujeme za objednávku <?= $e($orderNumber) ?></h1>
<p style="line-height:1.6">Dobrý den<?= $name !== '' ? ' ' . $e($name) : '' ?>,</p>
<p style="line-height:1.6">
    vaše objednávka byla <strong>zaplacena</strong>. V příloze tohoto emailu najdete své vouchery ve formátu PDF.
</p>
<?php if (!empty($order['total_amount_cents'])): ?>
    <p style="line-height:1.6">
        Celková částka: <strong><?= $e(number_format(((int) $order['total_amount_cents']) / 100, 2, ',', ' ')) ?> <?= $e($order['currency'] ?? 'CZK') ?></strong>
    </p>
<?php endif; ?>
<p style="margin-top:28px;color:#6b7280">Děkujeme za váš nákup.</p>
