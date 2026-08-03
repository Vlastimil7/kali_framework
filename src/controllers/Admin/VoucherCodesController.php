<?php

namespace Controllers\Admin;

use Core\Request;
use Models\VoucherCode;
use Helpers\Toast;

class VoucherCodesController extends BaseAdminController
{
    private VoucherCode $voucherCodeModel;

    public function __construct()
    {
        parent::__construct();
        $this->voucherCodeModel = new VoucherCode();
    }

    // GET /admin/voucher-codes/verify
    public function verifyForm()
    {
        $this->view('admin/voucher_codes/verify', [
            'title' => 'Ověření voucheru | Admin',

            'result' => null,
        ]);
    }

    // POST /admin/voucher-codes/verify
    public function verify(Request $request)
    {
        $code = strtoupper($request->string('code'));

        if ($code === '') {
            Toast::error('Zadej kód voucheru.');
            header('Location: ' . BASE_URL . '/admin/voucher-codes/verify');
            exit;
        }

        $row = $this->voucherCodeModel->getVerifyDataByCode($code);

        if (!$row) {
            Toast::error('Kód nenalezen.');
            header('Location: ' . BASE_URL . '/admin/voucher-codes/verify');
            exit;
        }

        // flags pro UI
        $orderStatus = (string)($row['order_status'] ?? '');
        $codeStatus  = (string)($row['status'] ?? '');

        $isPaidOrder  = ($orderStatus === 'paid');
        $isActiveCode = ($codeStatus === 'active');

        $isExpired = false;
        if (!empty($row['valid_to'])) {
            $isExpired = (strtotime($row['valid_to'] . ' 23:59:59') < time());
        }

        $row['_flags'] = [
            'is_paid_order'   => $isPaidOrder,
            'is_active_code'  => $isActiveCode,
            'is_expired'      => $isExpired,
        ];

        $this->view('admin/voucher_codes/verify', [
            'title' => 'Ověření voucheru | Admin',

            'result' => $row,
        ]);
    }

    // POST /admin/voucher-codes/redeem
    public function redeem(Request $request)
    {
        $code = strtoupper($request->string('code'));
        $note = $request->string('note');

        if ($code === '') {
            Toast::error('Zadej kód voucheru.');
            header('Location: ' . BASE_URL . '/admin/voucher-codes/verify');
            exit;
        }

        $adminId = (int)($_SESSION['user_id'] ?? 0);

        $res = $this->voucherCodeModel->redeemByCode($code, $adminId, $note);

        if (!$res['success']) {
            Toast::error($res['message'] ?? 'Nelze uplatnit.');
        } else {
            Toast::success('Voucher byl uplatněn.');
        }

        // vrať se na verify s výsledkem
        header('Location: ' . BASE_URL . '/admin/voucher-codes/verify?code=' . urlencode($code));
        exit;
    }

    // POST /admin/voucher-codes/void  (storno/refund)
    public function void(Request $request)
    {
        $code   = strtoupper($request->string('code'));
        $reason = $request->string('reason');

        // volitelně: canceled/refunded
        $type = strtolower($request->string('type', 'canceled'));
        if (!in_array($type, ['canceled', 'refunded'], true)) {
            $type = 'canceled';
        }

        if ($code === '') {
            Toast::error('Zadej kód voucheru.');
            header('Location: ' . BASE_URL . '/admin/voucher-codes/verify');
            exit;
        }

        $adminId = (int)($_SESSION['user_id'] ?? 0);

        $res = $this->voucherCodeModel->voidByCode($code, $adminId, $reason, $type);

        if (!$res['success']) {
            Toast::error($res['message'] ?? 'Nelze zrušit/refundovat.');
        } else {
            Toast::success(($type === 'refunded')
                ? 'Voucher byl refundován.'
                : 'Voucher byl zrušen (storno).');
        }

        header('Location: ' . BASE_URL . '/admin/voucher-codes/verify?code=' . urlencode($code));
        exit;
    }

    // POST /admin/voucher-codes/exchange
    public function exchange(Request $request)
    {
        $code   = strtoupper($request->string('code'));
        $reason = $request->string('reason');

        if ($code === '') {
            Toast::error('Zadej kód voucheru.');
            header('Location: ' . BASE_URL . '/admin/voucher-codes/verify');
            exit;
        }

        $adminId = (int)($_SESSION['user_id'] ?? 0);

        $res = $this->voucherCodeModel->exchangeByCode($code, $adminId, $reason);

        if (!$res['success']) {
            Toast::error($res['message'] ?? 'Nelze provést výměnu.');
            header('Location: ' . BASE_URL . '/admin/voucher-codes/verify?code=' . urlencode($code));
            exit;
        }

        // zpráva + přesměrování na nový kód (ať ho hned vidíš)
        Toast::success('Voucher byl vyměněn. Nový kód: ' . ($res['new_code'] ?? ''));
        header('Location: ' . BASE_URL . '/admin/voucher-codes/verify?code=' . urlencode((string)($res['new_code'] ?? $code)));
        exit;
    }
}
