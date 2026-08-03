<?php

namespace Controllers\Admin;

use Core\Request;
use Models\VoucherCode;
use Helpers\Toast;
use Helpers\Validator;

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

        $validator = Validator::make(['code' => $code], [
            'code' => 'bail|required|string|max:100',
        ], [], ['code' => 'kód voucheru']);
        if ($validator->fails()) {
            $validator->flash('voucher_code', $request->post());
            header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify');
            exit;
        }

        $row = $this->voucherCodeModel->getVerifyDataByCode($code);

        if (!$row) {
            Toast::error('Kód nenalezen.');
            header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify');
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

        $validator = Validator::make(['code' => $code, 'note' => $note], [
            'code' => 'bail|required|string|max:100',
            'note' => 'nullable|string|max:1000',
        ], [], ['code' => 'kód voucheru', 'note' => 'poznámka']);
        if ($validator->fails()) {
            $validator->flash('voucher_code', $request->post());
            header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify');
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
        header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify?code=' . urlencode($code));
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

        $validator = Validator::make(['code' => $code, 'reason' => $reason, 'type' => $type], [
            'code' => 'bail|required|string|max:100',
            'reason' => 'nullable|string|max:1000',
            'type' => 'required|in:canceled,refunded',
        ], [], [
            'code' => 'kód voucheru',
            'reason' => 'důvod',
            'type' => 'typ změny',
        ]);
        if ($validator->fails()) {
            $validator->flash('voucher_code', $request->post());
            header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify');
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

        header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify?code=' . urlencode($code));
        exit;
    }

    // POST /admin/voucher-codes/exchange
    public function exchange(Request $request)
    {
        $code   = strtoupper($request->string('code'));
        $reason = $request->string('reason');

        $validator = Validator::make(['code' => $code, 'reason' => $reason], [
            'code' => 'bail|required|string|max:100',
            'reason' => 'nullable|string|max:1000',
        ], [], ['code' => 'kód voucheru', 'reason' => 'důvod']);
        if ($validator->fails()) {
            $validator->flash('voucher_code', $request->post());
            header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify');
            exit;
        }

        $adminId = (int)($_SESSION['user_id'] ?? 0);

        $res = $this->voucherCodeModel->exchangeByCode($code, $adminId, $reason);

        if (!$res['success']) {
            Toast::error($res['message'] ?? 'Nelze provést výměnu.');
            header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify?code=' . urlencode($code));
            exit;
        }

        // zpráva + přesměrování na nový kód (ať ho hned vidíš)
        Toast::success('Voucher byl vyměněn. Nový kód: ' . ($res['new_code'] ?? ''));
        header('Location: ' . config('app.base_url', '') . '/admin/voucher-codes/verify?code=' . urlencode((string)($res['new_code'] ?? $code)));
        exit;
    }
}
