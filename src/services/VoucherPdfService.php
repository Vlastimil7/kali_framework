<?php

namespace Services;

use Dompdf\Dompdf;

class VoucherPdfService
{
    private string $storageDir;
    private string $publicBaseUrl;
    private string $logoPath; // absolutní cesta k logu na disku

    public function __construct(string $storageDir, string $publicBaseUrl, string $logoPath)
    {
        $this->storageDir = rtrim($storageDir, '/\\');
        $this->publicBaseUrl = rtrim($publicBaseUrl, '/');
        $this->logoPath = $logoPath;
    }

    /**
     * Vygeneruje 1 PDF soubor obsahující všechny vouchery v objednávce (každý na vlastní stránce).
     * Vrací absolutní cestu k PDF na disku.
     */
    public function renderOrderVouchersPdf(array $order, array $items, array $codes): string
    {
        $orderNo = (string)($order['order_number'] ?? ('order-' . ($order['id'] ?? '0')));
        $outDir = $this->storageDir . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'orders';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }

        $pdfPath = $outDir . DIRECTORY_SEPARATOR . $orderNo . '-vouchers.pdf';

        $html = $this->renderHtml($order, $items, $codes);

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }

    public function renderSingleVoucherPdf(array $order, array $items, array $codeRow): string
    {
        $orderNo = (string)($order['order_number'] ?? ('order-' . ($order['id'] ?? '0')));

        $outDir = $this->storageDir . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'vouchers';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }

        $code = (string)($codeRow['code'] ?? 'voucher');
        $safeCode = preg_replace('~[^A-Z0-9\-]~i', '_', $code);

        $pdfPath = $outDir . DIRECTORY_SEPARATOR . $orderNo . '-' . $safeCode . '.pdf';

        $html = $this->renderHtml($order, $items, [$codeRow]);

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }


    private function renderHtml(array $order, array $items, array $codes): string
    {
        $logoDataUri = $this->fileToDataUri($this->logoPath);

        // rychlá mapa voucher_id -> název (když chceš)
        $voucherNames = [];
        foreach ($items as $it) {
            if (!empty($it['voucher_id'])) {
                $voucherNames[(int)$it['voucher_id']] = (string)($it['voucher_name_snapshot'] ?? $it['voucher_name'] ?? '');
            }
        }

        $buyer = (string)($order['billing_name'] ?? '');
        $date = date('d.m.Y');

        ob_start();
        ?>
        <!doctype html>
        <html lang="cs">

        <head>
            <meta charset="utf-8">
            <style>
                @page {
                    margin: 18mm 14mm;
                }

                body {
                    font-family: DejaVu Sans, sans-serif;
                    color: #111;
                }

                .voucher {
                    border: 2px solid #c8a44d;
                    border-radius: 14px;
                    padding: 18px;
                    background: #0b0b0d;
                    color: #f5f2e9;
                    position: relative;
                    overflow: hidden;
                }

                .gold-line {
                    height: 2px;
                    background: #c8a44d;
                    margin: 12px 0 16px;
                }

                .top {
                    display: table;
                    width: 100%;
                }

                .top-left {
                    display: table-cell;
                    width: 70%;
                    vertical-align: top;
                }

                .top-right {
                    display: table-cell;
                    width: 30%;
                    vertical-align: top;
                    text-align: right;
                }

                .logo {
                    height: 52px;
                }

                .brand {
                    font-size: 18px;
                    font-weight: 700;
                    letter-spacing: .5px;
                    color: #c8a44d;
                    margin-top: 8px;
                }

                .subtitle {
                    font-size: 12px;
                    color: #ddd;
                    margin-top: 2px;
                }

                .title {
                    font-size: 26px;
                    font-weight: 800;
                    margin: 12px 0 4px;
                    color: #f5f2e9;
                }

                .voucher-name {
                    font-size: 16px;
                    color: #c8a44d;
                    font-weight: 700;
                    margin: 0 0 10px;
                }

                .grid {
                    display: table;
                    width: 100%;
                    margin-top: 10px;
                }

                .col {
                    display: table-cell;
                    width: 50%;
                    vertical-align: top;
                    padding-right: 10px;
                }

                .label {
                    font-size: 10px;
                    color: #bdb8aa;
                    margin-bottom: 3px;
                }

                .val {
                    font-size: 13px;
                    font-weight: 700;
                    color: #f5f2e9;
                }

                .codebox {
                    margin-top: 14px;
                    border: 2px dashed #c8a44d;
                    border-radius: 12px;
                    padding: 12px;
                    background: rgba(255, 255, 255, 0.03);
                }

                .code {
                    font-family: DejaVu Sans Mono, monospace;
                    font-size: 18px;
                    letter-spacing: 1px;
                    color: #c8a44d;
                    font-weight: 800;
                }

                .note {
                    margin-top: 12px;
                    font-size: 11px;
                    color: #d7d2c6;
                    line-height: 1.35;
                }

                .pagebreak {
                    page-break-after: always;
                }

                .water {
                    position: absolute;
                    top: -60px;
                    right: -80px;
                    width: 260px;
                    height: 260px;
                    border-radius: 999px;
                    background: rgba(200, 164, 77, 0.08);
                }

                .val.price {
                    font-size: 18px;
                    color: #c8a44d;
                    font-weight: 900;
                }
            </style>
        </head>

        <body>
            <?php foreach ($codes as $idx => $c): ?>
                <?php
                        $voucherId = (int)($c['voucher_id'] ?? 0);
                $voucherName = (string)($c['voucher_name'] ?? ($voucherNames[$voucherId] ?? 'Voucher'));
                $code = (string)($c['code'] ?? '');
                $validFrom = (string)($c['valid_from'] ?? '-');
                $validTo = (string)($c['valid_to'] ?? '-');

                $recipient = '';
                $dedication = '';
                $priceLabel = '-';
                if (!empty($c['order_item_id'])) {
                    foreach ($items as $it) {
                        if ((int)$it['id'] === (int)$c['order_item_id']) {
                            $recipient = (string)($it['recipient_name'] ?? '');
                            $dedication = (string)($it['note'] ?? '');

                            // CENA (preferuj unit_price_cents; fallback total_price_cents)
                            $cents = null;
                            if (isset($it['unit_price_cents']) && $it['unit_price_cents'] !== null) {
                                $cents = (int)$it['unit_price_cents'];
                            } elseif (isset($it['total_price_cents']) && $it['total_price_cents'] !== null) {
                                // fallback – když by unit nebyla
                                $cents = (int)$it['total_price_cents'];
                            }

                            $currency = (string)($order['currency'] ?? 'CZK');

                            if ($cents !== null) {
                                $val = $cents / 100;
                                $priceLabel = ($currency === 'CZK')
                                    ? number_format($val, 0, ',', ' ') . ' CZK'
                                    : number_format($val, 2, ',', ' ') . ' ' . htmlspecialchars($currency);
                            }

                            break;
                        }
                    }
                }
                ?>
                <div class="voucher">
                    <div class="water"></div>

                    <div class="top">
                        <div class="top-left">
                            <?php if ($logoDataUri): ?>
                                <img class="logo" src="<?= htmlspecialchars($logoDataUri) ?>" alt="Logo">
                            <?php endif; ?>
                            <div class="brand">MIDOBARBERSHOP</div>
                            <div class="subtitle">Dárkový poukaz</div>
                        </div>
                        <div class="top-right">
                            <div class="label">Datum vystavení</div>
                            <div class="val"><?= htmlspecialchars($date) ?></div>
                            <div style="height:10px;"></div>
                            <div class="label">Kupující</div>
                            <div class="val"><?= htmlspecialchars($buyer ?: '-') ?></div>
                        </div>
                    </div>

                    <div class="gold-line"></div>

                    <div class="title">E-VOUCHER</div>
                    <div class="voucher-name"><?= htmlspecialchars($voucherName) ?></div>
                    <div class="val price"><?= $priceLabel ?></div>

                    <div class="grid">
                        <div class="col">
                            <div class="label">Jméno a příjmení (příjemce)</div>
                            <div class="val"><?= htmlspecialchars($recipient ?: '-') ?></div>

                            <div style="height:10px;"></div>

                            <div class="label">Platnost od / do</div>
                            <div class="val"><?= htmlspecialchars($validFrom) ?> – <?= htmlspecialchars($validTo) ?></div>
                        </div>
                        <div class="col">
                            <div class="label">Věnování</div>
                            <div class="val"><?= htmlspecialchars($dedication ?: '-') ?></div>

                            <div style="height:10px;"></div>

                            <div class="label">Objednávka</div>
                            <div class="val"><?= htmlspecialchars((string)($order['order_number'] ?? '-')) ?></div>
                        </div>
                    </div>

                    <div class="codebox">
                        <div class="label">Kód voucheru</div>
                        <div class="code"><?= htmlspecialchars($code) ?></div>
                    </div>

                    <div class="note">
                        Voucher je možné uplatnit na recepci po předložení kódu. Voucher je jednorázový.
                        Při ztrátě kódu nelze voucher uplatnit bez ověření.
                    </div>
                </div>

                <?php if ($idx < count($codes) - 1): ?>
                    <div class="pagebreak"></div>
                <?php endif; ?>
            <?php endforeach; ?>

        </body>

        </html>
<?php
        return (string)ob_get_clean();
    }

    private function fileToDataUri(string $path): ?string
    {
        if (!$path || !is_file($path)) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => null
        };
        if (!$mime) {
            return null;
        }
        $data = base64_encode((string)file_get_contents($path));
        return "data:$mime;base64,$data";
    }
}
