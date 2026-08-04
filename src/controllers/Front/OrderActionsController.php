<?php

namespace Controllers\Front;

use Core\Controller;
use Helpers\Auth;
use Models\Order;
use Models\OrderItem;
use Models\Voucher;
use Models\VoucherCode;

class OrderActionsController extends Controller
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private Voucher $voucherModel;
    private VoucherCode $voucherCodeModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->voucherModel = new Voucher();
        $this->voucherCodeModel = new VoucherCode();
    }

    public function markAsPaid(int $orderId)
    {
        // ✅ 1) nejdřív autorizace (ať se neprozrazuje nic o objednávce)
        if (!Auth::isAdmin()) {

            return $this->show404();
        }

        $order = $this->orderModel->getById($orderId);
        if (!$order) {

            return $this->show404();
        }

        // už je paid → jen zobrazíme
        if ($order['status'] !== 'paid') {
            $this->orderModel->updateStatus($orderId, 'paid');

            $items = $this->orderItemModel->getByOrderId($orderId);

            foreach ($items as $item) {
                $voucher = $this->voucherModel->getById((int)$item['voucher_id']);
                if (!$voucher) {
                    continue;
                }

                for ($i = 0; $i < (int)$item['quantity']; $i++) {
                    $code = strtoupper(bin2hex(random_bytes(4))) . '-' . $orderId;

                    $validFrom = date('Y-m-d');
                    $validTo = date('Y-m-d', strtotime("+{$voucher['validity_months']} months"));

                    $this->voucherCodeModel->create([
                        'order_id'      => (int)$order['id'],
                        'order_item_id' => (int)$item['id'],
                        'voucher_id'    => (int)$voucher['id'],
                        'code'          => $code,
                        'valid_from'    => $validFrom,
                        'valid_to'      => $validTo,
                        'status'        => 'active',
                    ]);
                }
            }

            $order = $this->orderModel->getById($orderId);
        }

        $codes = $this->voucherCodeModel->getByOrderId($orderId);

        $this->view('order/mark_as_paid', [
            'title' => 'Objednávka zaplacena',
            'order' => $order,
            'codes' => $codes,
            'show_sidebar' => false,
        ]);
    }
}
