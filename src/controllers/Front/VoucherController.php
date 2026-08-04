<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;
use Helpers\Toast;
use Helpers\Validator;
use Models\Order;
use Models\Voucher;
use Services\Vouchers\OrderService;

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
    public function processOrder(Request $request, $slug)
    {
        if (!$request->isMethod('POST')) {
            return $this->show404();
        }

        $voucher = $this->voucherModel->getBySlug($slug);
        if (!$voucher) {
            return $this->show404();
        }

        $form = [
            'name'    => $request->string('name'),
            'email'   => $request->string('email'),
            'phone'   => $request->string('phone'),
            'street'  => $request->string('street'),
            'city'    => $request->string('city'),
            'zip'     => $request->string('zip'),
            'company' => $request->filled('company') ? $request->string('company') : null,
            'ico'     => $request->string('ico'),
            'dic'     => $request->string('dic'),
        ];

        $validator = Validator::make($form, [
            'name' => 'bail|required|string|max:120',
            'email' => 'bail|required|email|max:254',
            'phone' => 'bail|required|string|max:30',
            'street' => 'bail|required|string|max:150',
            'city' => 'bail|required|string|max:100',
            'zip' => ['bail', 'required', 'regex:/^[0-9A-Za-z\s-]{3,12}$/'],
            'company' => 'nullable|string|max:150',
            'ico' => 'nullable|string|max:20',
            'dic' => 'nullable|string|max:20',
        ], [], [
            'name' => 'jméno',
            'email' => 'e-mail',
            'phone' => 'telefon',
            'street' => 'ulice',
            'city' => 'město',
            'zip' => 'PSČ',
            'company' => 'firma',
            'ico' => 'IČO',
            'dic' => 'DIČ',
        ]);
        if ($validator->fails()) {
            $validator->flash('voucher_order', $request->post());
            header('Location: ' . locale_url("voucher/{$slug}/order"));
            exit;
        }

        $result = $this->orderService->createOrderAndPayment($voucher, $form);

        if (!$result['success']) {
            Toast::error($result['error']);
            header('Location: ' . locale_url("voucher/{$slug}/order"));
            exit;
        }

        // Redirect na Comgate platební bránu
        header('Location: ' . $result['redirect']);
        exit;
    }
}
