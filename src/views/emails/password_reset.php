<h1 style="margin:0 0 20px;font-size:28px">Reset hesla</h1>

<p style="line-height:1.6">Dobrý den, <?= $e($name) ?>,</p>
<p style="line-height:1.6">
    obdrželi jsme žádost o reset hesla pro váš účet. Odkaz je platný jednu hodinu.
</p>
<p style="margin:28px 0">
    <a href="<?= $e($resetUrl) ?>" style="display:inline-block;padding:12px 22px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px">
        Resetovat heslo
    </a>
</p>
<p style="color:#6b7280;font-size:13px;line-height:1.5">
    Pokud jste o reset hesla nežádali, tento email můžete ignorovat.<br><br>
    Odkaz: <a href="<?= $e($resetUrl) ?>"><?= $e($resetUrl) ?></a>
</p>
