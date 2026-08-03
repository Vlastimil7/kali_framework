<?php

namespace Services\Vouchers;

use Core\Database;
use Helpers\Logger;
use Services\Payments\ComgateService;
use Services\Mail\Mail;
use Services\Mail\Mailables\OrderPaidEmail;
use Services\VoucherPdfService;

use Models\VoucherCode;

class OrderService
{
    private Database $db;
    private ComgateService $comgate;

    private VoucherPdfService $voucherPdf;
    private VoucherCode $voucherCodeModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->comgate = new ComgateService();

        // stejně jako v adminu
        $logoAbs = config('app.base_path') . '/public/assets/images/logo/mido_barbershop_01_logo.png'; // uprav dle reality
        $storage = config('app.base_path') . '/storage';

        $this->voucherPdf = new VoucherPdfService($storage, config('app.base_url', ''), $logoAbs);
        $this->voucherCodeModel = new VoucherCode();
    }

    /**
     * Vytvoří objednávku + init transakci (COMMIT),
     * zavolá Comgate createPayment,
     * uloží comgate_pay_id do orders i transactions,
     * nastaví status awaiting_payment.
     */
    public function createOrderAndPayment(array $voucher, array $form): array
    {
        try {
            $this->db->beginTransaction();

            $orderId = $this->insertOrder($voucher, $form);
            $this->insertOrderItem($orderId, $voucher);

            $txId = $this->insertTransaction([
                'order_id' => $orderId,
                'type' => 'payment_init',
                'amount_cents' => (int)$voucher['price_cents'],
                'currency' => $voucher['currency'] ?? 'CZK',
                'status' => 'created',
                'request_payload' => [
                    'voucher_id' => $voucher['id'] ?? null,
                    'email' => $form['email'] ?? null,
                    'name' => $form['name'] ?? null,
                ],
            ]);

            $this->setOrderStatus($orderId, 'awaiting_payment');

            $this->db->commit();

            // Comgate mimo DB transakci
            $paymentResult = $this->comgate->createPayment([
                'reference'  => (string)$orderId,
                'priceCents' => (int)$voucher['price_cents'],
                'email'      => (string)$form['email'],
                'phone'      => $form['phone'] ?? null,
                'name'       => (string)$form['name'],
            ]);

            if (!$paymentResult['success']) {
                $this->updateTransaction($txId, [
                    'status' => 'error',
                    'message' => $paymentResult['error'] ?? 'createPayment failed',
                    'response_payload' => $paymentResult,
                ]);

                Logger::warning('Comgate createPayment failed', [
                    'order_id' => $orderId,
                    'tx_id' => $txId,
                    'error' => $paymentResult['error'] ?? null,
                ]);

                return [
                    'success' => false,
                    'error'   => 'Nepodařilo se vytvořit platbu. Zkuste to prosím později.',
                ];
            }

            // ulož comgate ids
            $this->setOrderComgateIds($orderId, $paymentResult['transId'], (string)$orderId);

            $this->updateTransaction($txId, [
                'status' => 'pending',
                'comgate_pay_id' => $paymentResult['transId'],
                'response_payload' => $paymentResult,
            ]);

            return [
                'success'  => true,
                'redirect' => $paymentResult['redirectUrl'],
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollback();
            Logger::exception($e, ['context' => 'OrderService::createOrderAndPayment']);

            return [
                'success' => false,
                'error'   => 'Nastala neočekávaná chyba při vytváření objednávky.',
            ];
        }
    }

    /**
     * Zpracování NOTIFY (server-to-server).
     * - idempotentní
     * - log do transactions (payment_status)
     * - paid => paid_at + PDF + email + aktivace voucherů
     */
    public function processGatewayNotification(array $n): bool
    {
        $orderId = (int)($n['refId'] ?? 0);
        $payId   = (string)($n['transId'] ?? '');
        $status  = strtolower((string)($n['status'] ?? ''));

        if ($orderId <= 0 || $payId === '' || $status === '') {
            Logger::warning('processGatewayNotification missing fields', ['n' => $n]);
            return false;
        }

        $order = $this->findOrder($orderId);
        if (!$order) {
            Logger::warning('processGatewayNotification order not found', ['order_id' => $orderId]);
            return false;
        }

        // idempotence: finální stav -> jen OK
        $orderStatus = (string)($order['status'] ?? '');
        if (in_array($orderStatus, ['paid', 'refunded', 'canceled', 'expired'], true)) {
            Logger::info('Notify ignored (order already final)', [
                'order_id' => $orderId,
                'status' => $orderStatus,
            ]);
            return true;
        }

        // uložit comgate ids (bez přepisů)
        $this->setOrderComgateIds($orderId, $payId, (string)$orderId);

        // log notifikace
        $this->insertTransaction([
            'order_id' => $orderId,
            'type' => 'payment_status',
            'amount_cents' => (int)($order['total_amount_cents'] ?? 0),
            'currency' => (string)($order['currency'] ?? 'CZK'),
            'status' => $status,
            'comgate_pay_id' => $payId,
            'request_payload' => $n['raw'] ?? $n,
        ]);

        // paid
        if (in_array($status, ['paid', 'success', 'ok'], true)) {
            $this->markOrderPaid($orderId);
            $this->fulfillPaidOrderOnce($orderId);
            return true;
        }

        // canceled
        if (in_array($status, ['canceled', 'cancelled', 'failed'], true)) {
            $this->markOrderCanceled($orderId, null);
            // zneplatni pending/active kódy (pokud existují)
            $this->voucherCodeModel->voidAllByOrderId($orderId, 'canceled', 0, 'Comgate: ' . $status);
            return true;
        }

        // refunded
        if (in_array($status, ['refunded'], true)) {
            $this->markOrderRefunded($orderId, null);
            $this->voucherCodeModel->voidAllByOrderId($orderId, 'refunded', 0, 'Comgate: refunded');
            return true;
        }

        Logger::info('Notify received non-final status', [
            'order_id' => $orderId,
            'status' => $status
        ]);

        return true;
    }

    /**
     * Paid fulfillment:
     * - vygeneruj kódy (idempotentně)
     * - 1 PDF za objednávku
     * - email s PDF (idempotentně přes email_paid_sent_at)
     */
    private function fulfillPaidOrderOnce(int $orderId): void
    {
        $order = $this->findOrder($orderId);
        if (!$order) return;

        // idempotence: email už poslán
        if (!empty($order['email_paid_sent_at'])) {
            Logger::info('fulfillPaidOrderOnce: already done', ['order_id' => $orderId]);
            return;
        }

        // 1) kódy (idempotentně) – tvoje metoda je super
        $gen = $this->voucherCodeModel->ensureGeneratedForPaidOrder($orderId);
        if (!($gen['success'] ?? false)) {
            Logger::warning('fulfillPaidOrderOnce: ensureGeneratedForPaidOrder failed', [
                'order_id' => $orderId,
                'message' => $gen['message'] ?? null
            ]);
            return;
        }

        // 2) načti items + codes
        $items = $this->getOrderItems($orderId);
        $codes = $this->voucherCodeModel->getByOrderId($orderId);

        if (empty($codes)) {
            Logger::warning('fulfillPaidOrderOnce: no codes after generation', ['order_id' => $orderId]);
            return;
        }

        // 3) PDF (použij existující, jinak generuj)
        $pdfPath = (string)($order['pdf_path'] ?? '');
        if ($pdfPath === '' || !is_file($pdfPath)) {
            $pdfPath = $this->voucherPdf->renderOrderVouchersPdf($order, $items, $codes);

            $this->db->prepare("
                UPDATE orders
                SET pdf_generated_at = NOW(),
                    pdf_path = :path
                WHERE id = :id AND pdf_generated_at IS NULL
            ")->execute([
                ':id' => $orderId,
                ':path' => $pdfPath
            ]);
        }

        // 4) email (HTML + příloha)
        $mailRes = Mail::to((string) $order['billing_email'], (string) ($order['billing_name'] ?? ''))
            ->send(new OrderPaidEmail($order, [$pdfPath]));
        if (!$mailRes->successful()) {
            Logger::warning('fulfillPaidOrderOnce: email failed', [
                'order_id' => $orderId,
                'message' => $mailRes->message,
            ]);
            return;
        }

        $this->db->prepare("
            UPDATE orders
            SET email_paid_sent_at = NOW()
            WHERE id = :id AND email_paid_sent_at IS NULL
        ")->execute([':id' => $orderId]);

        Logger::info('fulfillPaidOrderOnce: DONE', ['order_id' => $orderId]);
    }

    private function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT oi.*, v.name AS voucher_name
            FROM order_items oi
            LEFT JOIN vouchers v ON v.id = oi.voucher_id
            WHERE oi.order_id = :id
            ORDER BY oi.id ASC
        ");
        $stmt->execute([':id' => $orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    // -------------------- Order status setters --------------------

    private function setOrderStatus(int $orderId, string $status): void
    {
        $this->db->prepare("UPDATE orders SET status = :s WHERE id = :id")
            ->execute([':s' => $status, ':id' => $orderId]);
    }

    private function setOrderComgateIds(int $orderId, string $payId, string $refId): void
    {
        $sql = "UPDATE orders
                SET
                  comgate_pay_id = COALESCE(comgate_pay_id, :pay),
                  comgate_ref_id = COALESCE(comgate_ref_id, :ref)
                WHERE id = :id";
        $this->db->prepare($sql)->execute([
            ':id'  => $orderId,
            ':pay' => $payId,
            ':ref' => $refId,
        ]);
    }

    private function markOrderPaid(int $orderId): void
    {
        $sql = "UPDATE orders
                SET status = 'paid',
                    paid_at = COALESCE(paid_at, NOW())
                WHERE id = :id AND status != 'paid'";
        $this->db->prepare($sql)->execute([':id' => $orderId]);
    }

    private function markOrderCanceled(int $orderId, ?int $by = null, ?string $reason = null): void
    {
        $sql = "UPDATE orders
                SET status='canceled',
                    canceled_at = COALESCE(canceled_at, NOW()),
                    canceled_by = COALESCE(:by, canceled_by),
                    cancel_reason = COALESCE(:reason, cancel_reason)
                WHERE id=:id AND status NOT IN ('paid','refunded')";
        $this->db->prepare($sql)->execute([':id' => $orderId, ':by' => $by, ':reason' => $reason]);
    }

    private function markOrderRefunded(int $orderId, ?int $by = null, ?string $reason = null): void
    {
        $sql = "UPDATE orders
                SET status='refunded',
                    refunded_at = COALESCE(refunded_at, NOW()),
                    refunded_by = COALESCE(:by, refunded_by),
                    refund_reason = COALESCE(:reason, refund_reason)
                WHERE id=:id";
        $this->db->prepare($sql)->execute([':id' => $orderId, ':by' => $by, ':reason' => $reason]);
    }

    // -------------------- Transactions helpers --------------------

    private function insertTransaction(array $t): int
    {
        $sql = "INSERT INTO transactions
                (order_id, comgate_pay_id, type, amount_cents, currency, status, message, request_payload, response_payload)
                VALUES
                (:order_id, :comgate_pay_id, :type, :amount_cents, :currency, :status, :message, :request_payload, :response_payload)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':order_id' => (int)$t['order_id'],
            ':comgate_pay_id' => $t['comgate_pay_id'] ?? null,
            ':type' => (string)$t['type'],
            ':amount_cents' => (int)$t['amount_cents'],
            ':currency' => (string)($t['currency'] ?? 'CZK'),
            ':status' => (string)$t['status'],
            ':message' => $t['message'] ?? null,
            ':request_payload' => isset($t['request_payload']) ? json_encode($t['request_payload'], JSON_UNESCAPED_UNICODE) : null,
            ':response_payload' => isset($t['response_payload']) ? json_encode($t['response_payload'], JSON_UNESCAPED_UNICODE) : null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function updateTransaction(int $txId, array $t): void
    {
        $sql = "UPDATE transactions
                SET
                  comgate_pay_id = COALESCE(:comgate_pay_id, comgate_pay_id),
                  status = COALESCE(:status, status),
                  message = :message,
                  response_payload = COALESCE(:response_payload, response_payload)
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $txId,
            ':comgate_pay_id' => $t['comgate_pay_id'] ?? null,
            ':status' => $t['status'] ?? null,
            ':message' => $t['message'] ?? null,
            ':response_payload' => isset($t['response_payload']) ? json_encode($t['response_payload'], JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    // -------------------- Order find + insert --------------------

    private function findOrder(int $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $orderId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function insertOrder(array $voucher, array $form): int
    {
        $sql = "INSERT INTO orders
                (order_number, billing_name, billing_email, billing_phone,
                 billing_street, billing_house_no, billing_city, billing_zip,
                 billing_company, billing_ico, billing_dic,
                 total_amount_cents, currency, status)
                VALUES
                (:order_number, :name, :email, :phone,
                 :street, :house_no, :city, :zip,
                 :company, :ico, :dic,
                 :total, :currency, 'awaiting_payment')";

        $stmt = $this->db->prepare($sql);

        $orderNumber = $form['order_number'] ?? ('#' . time() . '-' . mt_rand(100, 999));

        $stmt->execute([
            ':order_number' => $orderNumber,
            ':name' => $form['name'],
            ':email' => $form['email'],
            ':phone' => $form['phone'] ?? '',
            ':street' => $form['street'],
            ':house_no' => $form['house_no'] ?? '',
            ':city' => $form['city'],
            ':zip' => $form['zip'],
            ':company' => $form['company'] ?? null,
            ':ico' => !empty($form['ico']) ? $form['ico'] : null,
            ':dic' => !empty($form['dic']) ? $form['dic'] : null,
            ':total' => (int)$voucher['price_cents'],
            ':currency' => $voucher['currency'] ?? 'CZK',
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function insertOrderItem(int $orderId, array $voucher): void
    {
        $sql = "INSERT INTO order_items (order_id, voucher_id, quantity, price_cents)
                VALUES (:order_id, :voucher_id, 1, :price)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId,
            ':voucher_id' => (int)$voucher['id'],
            ':price' => (int)$voucher['price_cents'],
        ]);
    }

    public function adminRefund(int $orderId, int $adminId, string $note = '', ?int $amountCents = null): array
    {
        $order = $this->findOrder($orderId);
        if (!$order) return ['success' => false, 'error' => 'Order not found'];

        $payId = (string)($order['comgate_pay_id'] ?? '');
        if ($payId === '') return ['success' => false, 'error' => 'Missing comgate_pay_id'];

        $total = (int)($order['total_amount_cents'] ?? 0);
        if ($total <= 0) return ['success' => false, 'error' => 'Order total is 0'];

        if ($amountCents === null) $amountCents = $total;
        if ($amountCents <= 0 || $amountCents > $total) {
            return ['success' => false, 'error' => 'Invalid refund amount'];
        }

        $res = $this->comgate->refund($payId, $amountCents);

        $this->insertTransaction([
            'order_id' => $orderId,
            'comgate_pay_id' => $payId,
            'type' => 'refund', // ať ti to netruncuje sloupec
            'amount_cents' => $amountCents,
            'currency' => (string)($order['currency'] ?? 'CZK'),
            'status' => ($res['success'] ?? false) ? 'ok' : 'error',
            'message' => $res['error'] ?? null,
            'request_payload' => ['amountCents' => $amountCents, 'note' => $note, 'adminId' => $adminId],
            'response_payload' => $res,
        ]);

        return $res;
    }


}
