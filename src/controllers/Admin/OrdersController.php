<?php

namespace Controllers\Admin;

use Models\Order;
use Models\OrderItem;
use Models\VoucherCode;
use Services\VoucherPdfService;
use Helpers\Mailer;
use Services\Vouchers\OrderService;

class OrdersController extends BaseAdminController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private VoucherCode $voucherCodeModel;

    private Mailer $mailer;
    private VoucherPdfService $voucherPdf;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();

        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->voucherCodeModel = new VoucherCode();
        $this->orderService = new OrderService();

        // PDF service
        $logoAbs = BASE_PATH . '/public/assets/logo.png'; // uprav si reálnou cestu
        $storage = BASE_PATH . '/storage';
        $this->voucherPdf = new VoucherPdfService($storage, BASE_URL, $logoAbs);

        // Mailer
        $this->mailer = new Mailer();
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    private function adminId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public function index()
    {
        $orders = $this->orderModel->getListAdmin(200);

        $this->view('admin/orders/index', [
            'title' => 'Objednávky | Admin',
            'orders' => $orders,
            'show_sidebar' => false,
        ]);
    }

    public function detail(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $items = $this->orderItemModel->getByOrderId($id);
        $codes = $this->voucherCodeModel->getByOrderId($id);

        $this->view('admin/orders/detail', [
            'title' => 'Detail objednávky | Admin',
            'order' => $order,
            'items' => $items,
            'codes' => $codes,
            'show_sidebar' => false,
        ]);
    }

    public function markPaid(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $st = (string)($order['status'] ?? '');
        if (!in_array($st, ['pending', 'awaiting_payment'], true)) {
            $_SESSION['_flash_error'] = 'Tuto objednávku nelze označit jako zaplacenou.';
            $this->redirect('/admin/orders/' . $id);
        }

        // 1) nastav paid
        $ok = $this->orderModel->markPaid($id);
        if (!$ok) {
            $_SESSION['_flash_error'] = 'Nepodařilo se označit objednávku jako zaplacenou.';
            $this->redirect('/admin/orders/' . $id);
        }

        // 2) vygeneruj kódy (idempotentně)
        $gen = $this->voucherCodeModel->ensureGeneratedForPaidOrder($id);
        if (!($gen['success'] ?? false)) {
            $_SESSION['_flash_error'] = $gen['message'] ?? 'Objednávka je paid, ale nepodařilo se vygenerovat kódy.';
            $this->redirect('/admin/orders/' . $id);
        }

        $_SESSION['_flash_success'] = !empty($gen['skipped'])
            ? 'Objednávka označena jako zaplacená. Kódy už existovaly.'
            : 'Objednávka označena jako zaplacená. Vygenerováno kódů: ' . (int)($gen['created'] ?? 0) . '.';

        // 3) PDF + email (neblokuj paid, jen případně flash error)
        $order = $this->orderModel->getById($id);
        $items = $this->orderItemModel->getByOrderId($id);
        $codes = $this->voucherCodeModel->getByOrderId($id);

        if (!empty($codes)) {
            $pdfPaths = [];
            foreach ($codes as $c) {
                $pdfPaths[] = $this->voucherPdf->renderSingleVoucherPdf($order, $items, $c);
            }
            $mailRes = $this->mailer->sendOrderPaidWithVoucherAttachments($order, $pdfPaths);
            if (!($mailRes['success'] ?? false)) {
                $_SESSION['_flash_error'] = 'Objednávka je paid, ale email se nepodařilo odeslat: ' . ($mailRes['message'] ?? '');
            }
        }

        $this->redirect('/admin/orders/' . $id);
    }

    public function edit(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $this->view('admin/orders/edit', [
            'title' => 'Editace objednávky | Admin',
            'order' => $order,
            'show_sidebar' => false,
        ]);
    }

    public function update(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $data = [
            'billing_name' => trim((string)($_POST['billing_name'] ?? '')),
            'billing_email' => trim((string)($_POST['billing_email'] ?? '')),
            'billing_phone' => trim((string)($_POST['billing_phone'] ?? '')),
            'billing_street' => trim((string)($_POST['billing_street'] ?? '')),
            'billing_house_no' => trim((string)($_POST['billing_house_no'] ?? '')),
            'billing_city' => trim((string)($_POST['billing_city'] ?? '')),
            'billing_zip' => trim((string)($_POST['billing_zip'] ?? '')),
            'billing_company' => trim((string)($_POST['billing_company'] ?? '')) ?: null,
            'billing_ico' => trim((string)($_POST['billing_ico'] ?? '')) ?: null,
            'billing_dic' => trim((string)($_POST['billing_dic'] ?? '')) ?: null,
        ];

        $errors = [];
        if ($data['billing_name'] === '') $errors[] = 'Jméno je povinné.';
        if ($data['billing_email'] === '' || !filter_var($data['billing_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email není validní.';
        }

        if ($errors) {
            $_SESSION['_flash_error'] = implode(' ', $errors);
            $_SESSION['_old'] = $_POST;
            $this->redirect('/admin/orders/edit/' . $id);
        }

        $res = $this->orderModel->updateAdmin($id, $data);
        if (!($res['success'] ?? false)) {
            $_SESSION['_flash_error'] = $res['message'] ?? 'Chyba při ukládání.';
            $_SESSION['_old'] = $_POST;
            $this->redirect('/admin/orders/edit/' . $id);
        }

        $_SESSION['_flash_success'] = 'Objednávka byla uložena.';
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * STORNO (canceled) – jen před zaplacením
     */
    public function cancel(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $status = (string)($order['status'] ?? '');
        if (!in_array($status, ['pending', 'awaiting_payment'], true)) {
            $_SESSION['_flash_error'] = 'Objednávku lze stornovat jen před zaplacením.';
            $this->redirect('/admin/orders/' . $id);
        }

        $note = trim((string)($_POST['note'] ?? ''));

        // 1) objednávka canceled
        $res = $this->orderModel->setStatusAdmin($id, 'canceled', $this->adminId(), $note);
        if (!($res['success'] ?? false)) {
            $_SESSION['_flash_error'] = $res['message'] ?? 'Chyba při stornu objednávky.';
            $this->redirect('/admin/orders/' . $id);
        }

        // 2) kódy canceled (pokud existují a nejsou redeemed)
        $this->voucherCodeModel->voidAllByOrderId($id, 'canceled', $this->adminId(), $note);

        // 3) email status
        $order = $this->orderModel->getById($id);
        $mailRes = $this->mailer->sendOrderStatusChanged($order, 'canceled', $note);
        if (!($mailRes['success'] ?? false)) {
            $_SESSION['_flash_error'] = 'Objednávka je canceled, ale email se nepodařilo odeslat: ' . ($mailRes['message'] ?? '');
        }

        $_SESSION['_flash_success'] = 'Objednávka byla stornována.';
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * EXPIRE – typicky pending/awaiting_payment (ne placené)
     */
    public function expire(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $status = (string)($order['status'] ?? '');
        if (!in_array($status, ['pending', 'awaiting_payment'], true)) {
            $_SESSION['_flash_error'] = 'Expiraci lze nastavit jen pro nezaplacenou objednávku.';
            $this->redirect('/admin/orders/' . $id);
        }

        $note = trim((string)($_POST['note'] ?? ''));

        $res = $this->orderModel->setStatusAdmin($id, 'expired', $this->adminId(), $note);
        if (!($res['success'] ?? false)) {
            $_SESSION['_flash_error'] = $res['message'] ?? 'Chyba při změně stavu.';
            $this->redirect('/admin/orders/' . $id);
        }

        $exp = $this->voucherCodeModel->expireAllByOrderId($id);
        if (!($exp['success'] ?? false)) {
            $_SESSION['_flash_error'] = $exp['message'] ?? 'Objednávka je expired, ale nepodařilo se změnit stavy voucherů.';
            $this->redirect('/admin/orders/' . $id);
        }

        // email status
        $order = $this->orderModel->getById($id);
        $mailRes = $this->mailer->sendOrderStatusChanged($order, 'expired', $note);
        if (!($mailRes['success'] ?? false)) {
            $_SESSION['_flash_error'] = 'Objednávka je expired, ale email se nepodařilo odeslat: ' . ($mailRes['message'] ?? '');
        }

        $_SESSION['_flash_success'] = 'Objednávka nastavena jako expired. Změněno voucherů: ' . (int)($exp['affected'] ?? 0) . '.';
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * REFUND – jen paid + nesmí existovat redeemed kód
     */
    public function refund(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        if (($order['status'] ?? '') !== 'paid') {
            $_SESSION['_flash_error'] = 'Refund lze jen pro paid objednávku.';
            $this->redirect('/admin/orders/' . $id);
        }

        if ($this->voucherCodeModel->hasRedeemedByOrderId($id)) {
            $_SESSION['_flash_error'] = 'Nelze refundovat: objednávka má uplatněný (redeemed) voucher.';
            $this->redirect('/admin/orders/' . $id);
        }

        $note = trim((string)($_POST['note'] ?? ''));

        // ✅ 1) COMGATE REFUND
        $cg = $this->orderService->adminRefund(
            $id,
            $this->adminId(),
            $note
            // amountCents → null = full refund
        );

        if (!($cg['success'] ?? false)) {
            $_SESSION['_flash_error'] =
                'Refund na Comgate selhal: ' . ($cg['error'] ?? 'neznámá chyba');
            $this->redirect('/admin/orders/' . $id);
        }

        // ✅ 2) objednávka refunded
        $res = $this->orderModel->setStatusAdmin($id, 'refunded', $this->adminId(), $note);
        if (!($res['success'] ?? false)) {
            $_SESSION['_flash_error'] = $res['message'] ?? 'Chyba při refundu objednávky.';
            $this->redirect('/admin/orders/' . $id);
        }

        // ✅ 3) kódy refunded
        $this->voucherCodeModel->voidAllByOrderId($id, 'refunded', $this->adminId(), $note);

        // ✅ 4) email
        $order = $this->orderModel->getById($id);
        $mailRes = $this->mailer->sendOrderStatusChanged($order, 'refunded', $note);
        if (!($mailRes['success'] ?? false)) {
            $_SESSION['_flash_error'] =
                'Objednávka je refunded, ale email se nepodařilo odeslat.';
        }

        $_SESSION['_flash_success'] = 'Objednávka refundována a platba vrácena přes Comgate.';
        $this->redirect('/admin/orders/' . $id);
    }

}
