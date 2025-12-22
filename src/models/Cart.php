<?php
namespace Models;

class Cart
{
    public function __construct()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [],               // key => item
                'total_amount_cents' => 0,   // int
                'count' => 0,                // total qty
            ];
        }
    }

    public function getCart(): array
    {
        return $_SESSION['cart'];
    }

    public function clearCart(): array
    {
        $_SESSION['cart'] = [
            'items' => [],
            'total_amount_cents' => 0,
            'count' => 0,
        ];

        return ['success' => true, 'message' => 'Košík byl vyprázdněn', 'cart' => $_SESSION['cart']];
    }

    public function addVoucher(int $voucherId, int $quantity = 1): array
    {
        if ($voucherId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Neplatné údaje pro přidání do košíku'];
        }

        $voucherModel = new Voucher();
        $voucher = $voucherModel->getById($voucherId);

        if (!$voucher || (int)$voucher['is_active'] !== 1) {
            return ['success' => false, 'message' => 'Voucher nebyl nalezen nebo není aktivní'];
        }

        $key = 'v:' . $voucherId;

        if (!isset($_SESSION['cart']['items'][$key])) {
            $_SESSION['cart']['items'][$key] = [
                'voucher_id' => (int)$voucher['id'],
                'name' => (string)$voucher['name'],
                'slug' => (string)$voucher['slug'],
                'unit_price_cents' => (int)$voucher['price_cents'],
                'currency' => (string)$voucher['currency'],
                'validity_months' => (int)$voucher['validity_months'],
                'quantity' => 0,

                // volitelné – vyplní se až na checkoutu
                'recipient_name' => null,
                'note' => null,
            ];
        }

        $_SESSION['cart']['items'][$key]['quantity'] += $quantity;

        // klidně omez (např. max 10 ks / položku)
        if ($_SESSION['cart']['items'][$key]['quantity'] > 10) {
            $_SESSION['cart']['items'][$key]['quantity'] = 10;
        }

        $this->recalcTotals();

        return ['success' => true, 'message' => 'Voucher byl přidán do košíku', 'cart' => $_SESSION['cart']];
    }

    public function updateItemQuantity(string $itemKey, int $quantity): array
    {
        if (!isset($_SESSION['cart']['items'][$itemKey])) {
            return ['success' => false, 'message' => 'Položka nebyla v košíku nalezena'];
        }

        if ($quantity <= 0) {
            return $this->removeItem($itemKey);
        }

        if ($quantity > 10) $quantity = 10;

        $_SESSION['cart']['items'][$itemKey]['quantity'] = $quantity;
        $this->recalcTotals();

        return ['success' => true, 'message' => 'Množství bylo aktualizováno', 'cart' => $_SESSION['cart']];
    }

    public function removeItem(string $itemKey): array
    {
        if (!isset($_SESSION['cart']['items'][$itemKey])) {
            return ['success' => false, 'message' => 'Položka nebyla v košíku nalezena'];
        }

        unset($_SESSION['cart']['items'][$itemKey]);
        $this->recalcTotals();

        return ['success' => true, 'message' => 'Položka byla odstraněna z košíku', 'cart' => $_SESSION['cart']];
    }

    /**
     * Volitelné: uloží recipient/note do session, aby šly přenést do DB.
     * (Použijeme na checkoutu)
     */
    public function updateItemMeta(string $itemKey, ?string $recipientName, ?string $note): array
    {
        if (!isset($_SESSION['cart']['items'][$itemKey])) {
            return ['success' => false, 'message' => 'Položka nebyla v košíku nalezena'];
        }

        $_SESSION['cart']['items'][$itemKey]['recipient_name'] = $recipientName ? trim($recipientName) : null;
        $_SESSION['cart']['items'][$itemKey]['note'] = $note ? trim($note) : null;

        return ['success' => true];
    }

    private function recalcTotals(): void
    {
        $total = 0;
        $count = 0;

        foreach ($_SESSION['cart']['items'] as $item) {
            $qty = (int)$item['quantity'];
            $unit = (int)$item['unit_price_cents'];

            $count += $qty;
            $total += $unit * $qty;
        }

        $_SESSION['cart']['count'] = $count;
        $_SESSION['cart']['total_amount_cents'] = $total;
    }
}
