<?php

namespace Controllers\Front;

use Core\Controller;
use Models\Voucher;
use Models\Order;
use Services\Vouchers\OrderService;
use Helpers\Logger;

class VoucherController extends Controller
{
    private Voucher $voucherModel;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->voucherModel = new Voucher();
        $this->orderService = new OrderService();
    }

    /**
     * /vouchers – výpis všech aktivních voucherů
     */
    public function index()
    {
        $vouchers = $this->voucherModel->getActive();

        $this->view('vouchers/index', [
            'title' => 'Dárkové vouchery | Midobarbershop',
            'vouchers' => $vouchers,
            'show_sidebar' => false,
        ]);
    }

    /**
     * /voucher/{slug} – detail voucheru
     */
    public function detail($slug)
    {
        $voucher = $this->voucherModel->getBySlug($slug);

        if (!$voucher) {
            return $this->show404();
        }

        $this->view('vouchers/detail', [
            'title'   => $voucher['name'],
            'voucher' => $voucher,
        ]);
    }

    /**
     * /voucher/{slug}/order – objednávkový formulář
     */
    public function orderForm($slug)
    {
        $voucher = $this->voucherModel->getBySlug($slug);

        if (!$voucher) {
            return $this->show404();
        }

        $this->view('vouchers/order_form', [
            'title'   => 'Objednat voucher',
            'voucher' => $voucher,
        ]);
    }

    /**
     * POST /voucher/{slug}/order/process
     * Odeslání objednávky → vytváří order, položky, transakci + redirect na Comgate
     */
    public function processOrder($slug)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->show404();
        }

        $voucher = $this->voucherModel->getBySlug($slug);
        if (!$voucher) {
            return $this->show404();
        }

        $form = [
            'name'    => trim($_POST['name'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'phone'   => trim($_POST['phone'] ?? ''),
            'street'  => trim($_POST['street'] ?? ''),
            'city'    => trim($_POST['city'] ?? ''),
            'zip'     => trim($_POST['zip'] ?? ''),
            'company' => !empty($_POST['company']) ? trim($_POST['company']) : null,
            'ico'     => trim($_POST['ico'] ?? ''),
            'dic'     => trim($_POST['dic'] ?? ''),
        ];

        $result = $this->orderService->createOrderAndPayment($voucher, $form);

        if (!$result['success']) {
            $_SESSION['flash_message'] = $result['error'];
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . locale_url("voucher/{$slug}/order"));
            exit;
        }

        // Redirect na Comgate platební bránu
        header("Location: " . $result['redirect']);
        exit;
    }
}
