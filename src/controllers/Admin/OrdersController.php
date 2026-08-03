<?php

namespace Controllers\Admin;

use Core\Request;
use Models\Order;
use Models\OrderItem;
use Models\VoucherCode;
use Services\VoucherPdfService;
use Services\Vouchers\OrderService;
use Services\Mail\Mail;
use Services\Mail\Mailables\OrderPaidEmail;
use Services\Mail\Mailables\OrderStatusChangedEmail;
use Helpers\Flash;
use Helpers\Toast;
use Helpers\Validator;

class OrdersController extends BaseAdminController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private VoucherCode $voucherCodeModel;

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
        $logoAbs = config('app.base_path') . '/public/assets/logo.png'; // uprav si reálnou cestu
        $storage = config('app.base_path') . '/storage';
        $this->voucherPdf = new VoucherPdfService($storage, config('app.base_url', ''), $logoAbs);

    }

    private function redirect(string $path): void
    {
        header('Location: ' . config('app.base_url', '') . $path);
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

        ]);
    }

    public function markPaid(int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $st = (string)($order['status'] ?? '');
        if (!in_array($st, ['pending', 'awaiting_payment'], true)) {
            Toast::error('Tuto objednávku nelze označit jako zaplacenou.');
            $this->redirect('/admin/orders/' . $id);
        }

        // 1) nastav paid
        $ok = $this->orderModel->markPaid($id);
        if (!$ok) {
            Toast::error('Nepodařilo se označit objednávku jako zaplacenou.');
            $this->redirect('/admin/orders/' . $id);
        }

        // 2) vygeneruj kódy (idempotentně)
        $gen = $this->voucherCodeModel->ensureGeneratedForPaidOrder($id);
        if (!($gen['success'] ?? false)) {
            Toast::error($gen['message'] ?? 'Objednávka je paid, ale nepodařilo se vygenerovat kódy.');
            $this->redirect('/admin/orders/' . $id);
        }

        Toast::success(!empty($gen['skipped'])
            ? 'Objednávka označena jako zaplacená. Kódy už existovaly.'
            : 'Objednávka označena jako zaplacená. Vygenerováno kódů: ' . (int)($gen['created'] ?? 0) . '.');

        // 3) PDF + email (neblokuj paid, jen případně flash error)
        $order = $this->orderModel->getById($id);
        $items = $this->orderItemModel->getByOrderId($id);
        $codes = $this->voucherCodeModel->getByOrderId($id);

        if (!empty($codes)) {
            $pdfPaths = [];
            foreach ($codes as $c) {
                $pdfPaths[] = $this->voucherPdf->renderSingleVoucherPdf($order, $items, $c);
            }
            $mailRes = Mail::to((string) $order['billing_email'], (string) ($order['billing_name'] ?? ''))
                ->send(new OrderPaidEmail($order, $pdfPaths));
            if (!$mailRes->successful()) {
                Toast::error('Objednávka je paid, ale email se nepodařilo odeslat: ' . $mailRes->message);
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

        ]);
    }

    public function update(Request $request, int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $data = [
            'billing_name' => $request->string('billing_name'),
            'billing_email' => $request->string('billing_email'),
            'billing_phone' => $request->string('billing_phone'),
            'billing_street' => $request->string('billing_street'),
            'billing_house_no' => $request->string('billing_house_no'),
            'billing_city' => $request->string('billing_city'),
            'billing_zip' => $request->string('billing_zip'),
            'billing_company' => $request->string('billing_company') ?: null,
            'billing_ico' => $request->string('billing_ico') ?: null,
            'billing_dic' => $request->string('billing_dic') ?: null,
        ];

        $validator = Validator::make($data, [
            'billing_name' => 'bail|required|string|max:120',
            'billing_email' => 'bail|required|email|max:254',
            'billing_phone' => 'nullable|string|max:30',
            'billing_street' => 'nullable|string|max:150',
            'billing_house_no' => 'nullable|string|max:20',
            'billing_city' => 'nullable|string|max:100',
            'billing_zip' => 'nullable|string|max:12',
            'billing_company' => 'nullable|string|max:150',
            'billing_ico' => 'nullable|string|max:20',
            'billing_dic' => 'nullable|string|max:20',
        ], [], [
            'billing_name' => 'jméno',
            'billing_email' => 'e-mail',
            'billing_phone' => 'telefon',
            'billing_street' => 'ulice',
            'billing_house_no' => 'číslo domu',
            'billing_city' => 'město',
            'billing_zip' => 'PSČ',
            'billing_company' => 'firma',
            'billing_ico' => 'IČO',
            'billing_dic' => 'DIČ',
        ]);

        if ($validator->fails()) {
            $validator->flash('admin_order', $request->post());
            $this->redirect('/admin/orders/edit/' . $id);
        }

        $res = $this->orderModel->updateAdmin($id, $data);
        if (!($res['success'] ?? false)) {
            Toast::error($res['message'] ?? 'Chyba při ukládání.');
            Flash::withInput('admin_order', $request->post());
            $this->redirect('/admin/orders/edit/' . $id);
        }

        Toast::success('Objednávka byla uložena.');
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * STORNO (canceled) – jen před zaplacením
     */
    public function cancel(Request $request, int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $status = (string)($order['status'] ?? '');
        if (!in_array($status, ['pending', 'awaiting_payment'], true)) {
            Toast::error('Objednávku lze stornovat jen před zaplacením.');
            $this->redirect('/admin/orders/' . $id);
        }

        $note = $request->string('note');

        // 1) objednávka canceled
        $res = $this->orderModel->setStatusAdmin($id, 'canceled', $this->adminId(), $note);
        if (!($res['success'] ?? false)) {
            Toast::error($res['message'] ?? 'Chyba při stornu objednávky.');
            $this->redirect('/admin/orders/' . $id);
        }

        // 2) kódy canceled (pokud existují a nejsou redeemed)
        $this->voucherCodeModel->voidAllByOrderId($id, 'canceled', $this->adminId(), $note);

        // 3) email status
        $order = $this->orderModel->getById($id);
        $mailRes = Mail::to((string) $order['billing_email'], (string) ($order['billing_name'] ?? ''))
            ->send(new OrderStatusChangedEmail($order, 'canceled', $note));
        if (!$mailRes->successful()) {
            Toast::error('Objednávka je canceled, ale email se nepodařilo odeslat: ' . $mailRes->message);
        }

        Toast::success('Objednávka byla stornována.');
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * EXPIRE – typicky pending/awaiting_payment (ne placené)
     */
    public function expire(Request $request, int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        $status = (string)($order['status'] ?? '');
        if (!in_array($status, ['pending', 'awaiting_payment'], true)) {
            Toast::error('Expiraci lze nastavit jen pro nezaplacenou objednávku.');
            $this->redirect('/admin/orders/' . $id);
        }

        $note = $request->string('note');

        $res = $this->orderModel->setStatusAdmin($id, 'expired', $this->adminId(), $note);
        if (!($res['success'] ?? false)) {
            Toast::error($res['message'] ?? 'Chyba při změně stavu.');
            $this->redirect('/admin/orders/' . $id);
        }

        $exp = $this->voucherCodeModel->expireAllByOrderId($id);
        if (!($exp['success'] ?? false)) {
            Toast::error($exp['message'] ?? 'Objednávka je expired, ale nepodařilo se změnit stavy voucherů.');
            $this->redirect('/admin/orders/' . $id);
        }

        // email status
        $order = $this->orderModel->getById($id);
        $mailRes = Mail::to((string) $order['billing_email'], (string) ($order['billing_name'] ?? ''))
            ->send(new OrderStatusChangedEmail($order, 'expired', $note));
        if (!$mailRes->successful()) {
            Toast::error('Objednávka je expired, ale email se nepodařilo odeslat: ' . $mailRes->message);
        }

        Toast::success('Objednávka nastavena jako expired. Změněno voucherů: ' . (int)($exp['affected'] ?? 0) . '.');
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * REFUND – jen paid + nesmí existovat redeemed kód
     */
    public function refund(Request $request, int $id)
    {
        $order = $this->orderModel->getById($id);
        if (!$order) return $this->show404();

        if (($order['status'] ?? '') !== 'paid') {
            Toast::error('Refund lze jen pro paid objednávku.');
            $this->redirect('/admin/orders/' . $id);
        }

        if ($this->voucherCodeModel->hasRedeemedByOrderId($id)) {
            Toast::error('Nelze refundovat: objednávka má uplatněný (redeemed) voucher.');
            $this->redirect('/admin/orders/' . $id);
        }

        $note = $request->string('note');

        // ✅ 1) COMGATE REFUND
        $cg = $this->orderService->adminRefund(
            $id,
            $this->adminId(),
            $note
            // amountCents → null = full refund
        );

        if (!($cg['success'] ?? false)) {
            Toast::error('Refund na Comgate selhal: ' . ($cg['error'] ?? 'neznámá chyba'));
            $this->redirect('/admin/orders/' . $id);
        }

        // ✅ 2) objednávka refunded
        $res = $this->orderModel->setStatusAdmin($id, 'refunded', $this->adminId(), $note);
        if (!($res['success'] ?? false)) {
            Toast::error($res['message'] ?? 'Chyba při refundu objednávky.');
            $this->redirect('/admin/orders/' . $id);
        }

        // ✅ 3) kódy refunded
        $this->voucherCodeModel->voidAllByOrderId($id, 'refunded', $this->adminId(), $note);

        // ✅ 4) email
        $order = $this->orderModel->getById($id);
        $mailRes = Mail::to((string) $order['billing_email'], (string) ($order['billing_name'] ?? ''))
            ->send(new OrderStatusChangedEmail($order, 'refunded', $note));
        if (!$mailRes->successful()) {
            Toast::error('Objednávka je refunded, ale email se nepodařilo odeslat.');
        }

        Toast::success('Objednávka refundována a platba vrácena přes Comgate.');
        $this->redirect('/admin/orders/' . $id);
    }
}
