<h1 style="margin:0 0 20px;font-size:28px">Objednávka <?= $e($orderNumber) ?></h1>
<p style="line-height:1.6">Dobrý den<?= $name !== '' ? ' ' . $e($name) : '' ?>,</p>
<p style="line-height:1.6">
    stav vaší objednávky byl změněn na: <strong><?= $e($statusLabel) ?></strong>.
</p>
<?php if ($note !== ''): ?>
    <div style="margin-top:20px;padding:16px;background:#f9fafb;border-radius:8px;line-height:1.6">
        <strong>Poznámka:</strong><br>
        <?= nl2br($e($note)) ?>
    </div>
<?php endif; ?>
