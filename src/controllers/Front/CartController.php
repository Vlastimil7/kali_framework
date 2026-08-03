<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;
use Core\Database;
use Models\Cart;
use Models\Order;
use Models\OrderItem;
use Services\Payments\ComgateService;
use Helpers\Logger;
use Helpers\Toast;

class CartController extends Controller
{
    private Cart $cartModel;
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private ComgateService $comgate;

    public function __construct()
    {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->comgate = new ComgateService();
    }

    /**
     * Zobrazení obsahu košíku
     */
    public function index()
    {
        $cart = $this->cartModel->getCart();

        $this->view('cart/index', [
            'title' => 'Košík | Midobarbershop.cz',
            'cart' => $cart,
            'show_sidebar' => false,
        ]);
    }

    /**
     * Přidání voucheru do košíku
     */
    public function addVoucher(Request $request)
    {
        if (!$request->isMethod('POST')) {
            header('Location: ' . locale_url('vouchers'));
            exit;
        }

        $voucherId = $request->int('voucher_id');
        $quantity  = $request->int('quantity', 1);

        $result = $this->cartModel->addVoucher($voucherId, $quantity);

        $this->toastResult($result);

        header('Location: ' . locale_url('cart'));
        exit;
    }

    /**
     * Aktualizace množství
     */
    public function updateItem(Request $request)
    {
        if (!$request->isMethod('POST')) {
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $itemKey = $request->string('item_key');
        $quantity = $request->int('quantity');

        if ($itemKey === '') {
            Toast::error('Položka nebyla nalezena');
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $result = $this->cartModel->updateItemQuantity($itemKey, $quantity);

        $this->toastResult($result);

        header('Location: ' . locale_url('cart'));
        exit;
    }

    /**
     * Odstranění položky
     */
    public function removeItem(Request $request)
    {
        if (!$request->isMethod('POST')) {
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $itemKey = $request->string('item_key');

        if ($itemKey === '') {
            Toast::error('Položka nebyla nalezena');
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $result = $this->cartModel->removeItem($itemKey);

        $this->toastResult($result);

        header('Location: ' . locale_url('cart'));
        exit;
    }

    /**
     * Vyprázdnění košíku
     */
    public function clearCart(Request $request)
    {
        if (!$request->isMethod('POST')) {
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $result = $this->cartModel->clearCart();

        $this->toastResult($result);

        header('Location: ' . locale_url('cart'));
        exit;
    }

    /**
     * Checkout (bez loginu)
     */
    public function checkout()
    {
        $cart = $this->cartModel->getCart();

        if (empty($cart['items'])) {
            Toast::error('Váš košík je prázdný');
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $this->view('cart/checkout', [
            'title' => 'Dokončení objednávky | Midobarbershop.cz',
            'cart' => $cart,
            'show_sidebar' => false,
        ]);
    }

    /**
     * Vytvoření objednávky + založení platby v Comgate + redirect na bránu
     * ROUTE: POST /cart/create-order
     */
    public function createOrder(Request $request)
    {
        Logger::info('CartController::createOrder START', [
            'method' => $request->method(),
            'uri'    => $request->uri(),
        ]);

        if (!$request->isMethod('POST')) {
            Logger::warning('CartController::createOrder non-POST request');
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $cart = $this->cartModel->getCart();

        Logger::info('Cart loaded', [
            'items_count' => isset($cart['items']) ? count($cart['items']) : 0,
            'total_cents' => (int)($cart['total_amount_cents'] ?? 0),
        ]);

        if (empty($cart['items'])) {
            Logger::warning('Cart is empty, aborting createOrder');
            Toast::error('Váš košík je prázdný');
            header('Location: ' . locale_url('cart'));
            exit;
        }

        $billing = [
            'billing_name'     => $request->string('billing_name'),
            'billing_email'    => $request->string('billing_email'),
            'billing_phone'    => $request->string('billing_phone'),
            'billing_street'   => $request->string('billing_street'),
            'billing_house_no' => $request->string('billing_house_no'),
            'billing_city'     => $request->string('billing_city'),
            'billing_zip'      => $request->string('billing_zip'),
            'billing_company'  => $request->filled('billing_company') ? $request->string('billing_company') : null,
            'billing_ico'      => $request->filled('billing_ico') ? $request->string('billing_ico') : null,
            'billing_dic'      => $request->filled('billing_dic') ? $request->string('billing_dic') : null,
        ];

        Logger::info('Billing received', [
            'name_len'  => strlen($billing['billing_name']),
            'email'     => $billing['billing_email'],
            'phone_len' => strlen($billing['billing_phone']),
            'city'      => $billing['billing_city'],
            'zip'       => $billing['billing_zip'],
        ]);

        foreach (['billing_name', 'billing_email', 'billing_phone', 'billing_street', 'billing_house_no', 'billing_city', 'billing_zip'] as $k) {
            if ($billing[$k] === '') {
                Logger::warning('Billing validation failed - missing field', ['field' => $k]);
                Toast::error('Vyplňte prosím všechna povinná pole.');
                header('Location: ' . locale_url('cart/checkout'));
                exit;
            }
        }

        if (!filter_var($billing['billing_email'], FILTER_VALIDATE_EMAIL)) {
            Logger::warning('Billing validation failed - invalid email', ['email' => $billing['billing_email']]);
            Toast::error('Email není validní.');
            header('Location: ' . locale_url('cart/checkout'));
            exit;
        }

        $recipientMap = $request->post('recipient_name', []);
        $noteMap      = $request->post('note', []);

        $db = Database::getInstance();
        $orderNumber = $this->generateOrderNumber();

        Logger::info('Order number generated', ['order_number' => $orderNumber]);

        try {
            Logger::info('DB transaction begin');
            $db->beginTransaction();

            $orderId = $this->orderModel->create([
                'order_number' => $orderNumber,
                ...$billing,
                'total_amount_cents' => (int)$cart['total_amount_cents'],
                'currency' => 'CZK',
                'status' => 'awaiting_payment',
            ]);

            Logger::info('Order created', [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'total_cents' => (int)$cart['total_amount_cents'],
            ]);

            foreach ($cart['items'] as $key => $item) {
                $qty  = (int)$item['quantity'];
                $unit = (int)$item['unit_price_cents'];

                $this->orderItemModel->create([
                    'order_id' => $orderId,
                    'voucher_id' => (int)$item['voucher_id'],
                    'quantity' => $qty,
                    'unit_price_cents' => $unit,
                    'total_price_cents' => $unit * $qty,
                    'voucher_name_snapshot' => (string)($item['name'] ?? ''),

                    'recipient_name' => isset($recipientMap[$key]) && trim((string)$recipientMap[$key]) !== '' ? trim((string)$recipientMap[$key]) : null,
                    'note' => isset($noteMap[$key]) && trim((string)$noteMap[$key]) !== '' ? trim((string)$noteMap[$key]) : null,
                ]);
            }

            $txId = $this->insertTransaction($db, [
                'order_id' => $orderId,
                'comgate_pay_id' => null,
                'type' => 'payment_init',
                'amount_cents' => (int)$cart['total_amount_cents'],
                'currency' => 'CZK',
                'status' => 'created',
                'message' => null,
                'request_payload' => [
                    'order_number' => $orderNumber,
                    'billing_email' => $billing['billing_email'],
                ],
                'response_payload' => null,
            ]);

            Logger::info('Transaction created (payment_init)', [
                'tx_id' => $txId,
                'order_id' => $orderId,
            ]);

            $db->commit();
            Logger::info('DB transaction COMMIT ok', ['order_id' => $orderId, 'tx_id' => $txId]);

            Logger::info('Comgate createPayment CALL', [
                'ref' => (string)$orderId,
                'priceCents' => (int)$cart['total_amount_cents'],
                'email' => (string)$billing['billing_email'],
            ]);

            $paymentResult = $this->comgate->createPayment([
                'reference'  => (string)$orderId,
                'priceCents' => (int)$cart['total_amount_cents'],
                'email'      => (string)$billing['billing_email'],
                'phone'      => (string)$billing['billing_phone'],
                'name'       => (string)$billing['billing_name'],
            ]);

            Logger::info('Comgate createPayment RESULT', [
                'success' => (bool)($paymentResult['success'] ?? false),
                'transId' => $paymentResult['transId'] ?? null,
                'redirectUrl' => $paymentResult['redirectUrl'] ?? null,
                'code' => $paymentResult['code'] ?? null,
                'error' => $paymentResult['error'] ?? null,
            ]);

            if (!($paymentResult['success'] ?? false)) {
                $this->updateTransaction($db, $txId, [
                    'status' => 'error',
                    'message' => $paymentResult['error'] ?? 'createPayment failed',
                    'response_payload' => $paymentResult,
                ]);

                Toast::error('Nepodařilo se vytvořit platbu. Zkuste to prosím znovu.');
                header('Location: ' . locale_url('cart/checkout'));
                exit;
            }

            $payId = (string)$paymentResult['transId'];

            $db->prepare("UPDATE orders SET comgate_pay_id = :pid, comgate_ref_id = :rid WHERE id = :id")
                ->execute([
                    ':pid' => $payId,
                    ':rid' => (string)$orderId,
                    ':id'  => $orderId,
                ]);

            $this->updateTransaction($db, $txId, [
                'status' => 'pending',
                'comgate_pay_id' => $payId,
                'message' => null,
                'response_payload' => $paymentResult,
            ]);

            $this->cartModel->clearCart();
            Logger::info('Cart cleared');

            $redirect = (string)($paymentResult['redirectUrl'] ?? '');
            if ($redirect === '') {
                Logger::error('Comgate redirectUrl missing even though success=true', [
                    'order_id' => $orderId,
                    'tx_id' => $txId,
                ]);

                Toast::error('Platba byla založena, ale chybí redirect URL. Kontaktujte správce.');
                header('Location: ' . locale_url('cart/checkout'));
                exit;
            }

            Logger::info('Redirecting user to Comgate', ['redirectUrl' => $redirect]);
            header('Location: ' . $redirect);
            exit;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                try {
                    $db->rollBack();
                } catch (\Throwable $ignore) {
                }
            }

            Logger::exception($e, ['context' => 'CartController::createOrder']);

            Toast::error('Nepodařilo se vytvořit objednávku. Zkuste to prosím znovu.');
            header('Location: ' . locale_url('cart/checkout'));
            exit;
        }
    }

    private function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return "MB-$date-$rand";
    }

    private function toastResult(array $result): void
    {
        $message = (string)($result['message'] ?? '');
        if (!empty($result['success'])) {
            Toast::success($message);
            return;
        }

        Toast::error($message);
    }

    private function insertTransaction($db, array $t): int
    {
        $sql = "INSERT INTO transactions
                (order_id, comgate_pay_id, type, amount_cents, currency, status, message, request_payload, response_payload)
                VALUES
                (:order_id, :comgate_pay_id, :type, :amount_cents, :currency, :status, :message, :request_payload, :response_payload)";

        $st = $db->prepare($sql);
        $st->execute([
            ':order_id' => (int)$t['order_id'],
            ':comgate_pay_id' => $t['comgate_pay_id'] ?? null,
            ':type' => (string)$t['type'],
            ':amount_cents' => (int)$t['amount_cents'],
            ':currency' => (string)($t['currency'] ?? 'CZK'),
            ':status' => (string)$t['status'],
            ':message' => $t['message'] ?? null,
            ':request_payload' => $t['request_payload'] ? json_encode($t['request_payload'], JSON_UNESCAPED_UNICODE) : null,
            ':response_payload' => $t['response_payload'] ? json_encode($t['response_payload'], JSON_UNESCAPED_UNICODE) : null,
        ]);

        return (int)$db->lastInsertId();
    }

    private function updateTransaction($db, int $txId, array $t): void
    {
        $sql = "UPDATE transactions
                SET
                  comgate_pay_id = COALESCE(:comgate_pay_id, comgate_pay_id),
                  status = COALESCE(:status, status),
                  message = :message,
                  response_payload = COALESCE(:response_payload, response_payload)
                WHERE id = :id";

        $st = $db->prepare($sql);
        $st->execute([
            ':id' => $txId,
            ':comgate_pay_id' => $t['comgate_pay_id'] ?? null,
            ':status' => $t['status'] ?? null,
            ':message' => $t['message'] ?? null,
            ':response_payload' => isset($t['response_payload']) ? json_encode($t['response_payload'], JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
