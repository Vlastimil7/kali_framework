<?php

namespace Controllers\Front;

use Core\Controller;
use Models\Order;
use Models\OrderItem;
use Models\VoucherCode;

class OrderController extends Controller
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private VoucherCode $voucherCodeModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->voucherCodeModel = new VoucherCode();
    }

    /**
     * /order/success?order=MB-YYYYMMDD-XXXXXX
     */
    public function success()
    {
        $orderNumber = trim($_GET['order'] ?? '');
        if ($orderNumber === '') {
            return $this->show404();
        }

        $order = $this->orderModel->getByOrderNumber($orderNumber);
        if (!$order) {
            return $this->show404();
        }

        $items = $this->orderItemModel->getByOrderId((int)$order['id']);

        // ✅ kódy voucherů pro objednávku (pokud už je zaplaceno / vygenerováno)
        $codes = $this->voucherCodeModel->getByOrderId((int)$order['id']);

        $this->view('order/success', [
            'title' => 'Děkujeme za objednávku | Midobarbershop',
            'order' => $order,
            'items' => $items,
            'codes' => $codes,
            'show_sidebar' => false,
        ]);
    }
}
