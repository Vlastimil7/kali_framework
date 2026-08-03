<h1 style="margin:0 0 20px;font-size:28px">Dobrý den, <?= $e($name) ?>!</h1>

<p style="margin:0 0 20px;line-height:1.6">
    Váš účet je připravený. Pomocí následujícího tlačítka se můžete přihlásit.
</p>

<p style="margin:28px 0">
    <a href="<?= $e($loginUrl) ?>"
       style="display:inline-block;padding:12px 22px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px">
        Přihlásit se
    </a>
</p>

<p style="margin:20px 0 0;color:#6b7280;font-size:13px;line-height:1.5">
    Pokud tlačítko nefunguje, otevřete adresu:<br>
    <a href="<?= $e($loginUrl) ?>"><?= $e($loginUrl) ?></a>
</p>
