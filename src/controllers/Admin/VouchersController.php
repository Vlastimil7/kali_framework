<?php

namespace Controllers\Admin;

use Core\Request;
use Models\Voucher;
use Helpers\Flash;
use Helpers\Toast;

class VouchersController extends BaseAdminController
{
    private Voucher $voucherModel;

    public function __construct()
    {
        parent::__construct();
        $this->voucherModel = new Voucher();
    }

    public function index()
    {
        $vouchers = $this->voucherModel->getAllAdmin();

        $this->view('admin/vouchers/index', [
            'title' => 'Vouchery | Admin',
            'vouchers' => $vouchers,

        ]);
    }

    public function detail(int $id)
    {
        $voucher = $this->voucherModel->getById($id);
        if (!$voucher) return $this->show404();

        $this->view('admin/vouchers/detail', [
            'title' => 'Detail voucheru | Admin',
            'voucher' => $voucher,
        ]);
    }

    public function create()
    {
        $this->view('admin/vouchers/create', [
            'title' => 'Nový voucher | Admin',

        ]);
    }

    public function store(Request $request)
    {
        $name = $request->string('name');
        $slug = $request->string('slug');
        $currency = $request->string('currency', 'CZK');
        $price = $request->string('price', $request->string('price_czk'));
        $isActive = $request->has('is_active') ? 1 : 0;

        $errors = [];
        if ($name === '') $errors[] = 'Název je povinný.';
        if ($slug === '') $errors[] = 'Slug je povinný.';
        if ($price === '' || !preg_match('~^\d+([.,]\d{1,2})?$~', $price)) $errors[] = 'Cena není ve správném formátu.';
        if ($this->voucherModel->existsBySlug($slug)) $errors[] = 'Slug už existuje, zvol jiný.';

        if ($errors) {
            Toast::error(implode(' ', $errors));
            Flash::withInput('admin_voucher', $request->post());
            header('Location: ' . BASE_URL . '/admin/vouchers/create');
            exit;
        }

        $priceCents = (int) round((float) str_replace(',', '.', $price) * 100);
        $description = $request->string('description');
        $validityMonths = $request->int('validity_months', 6);

        $res = $this->voucherModel->create([
            'name' => $name,
            'slug' => $slug,
            'description' => ($description !== '' ? $description : null),
            'price_cents' => $priceCents,
            'currency' => $currency ?: 'CZK',
            'validity_months' => $validityMonths,
            'is_active' => $isActive,
            // 'created_by' => $adminId (až budeš mít admin user)
        ]);

        if (!$res['success']) {
            Toast::error($res['message'] ?? 'Chyba při vytváření voucheru.');
            Flash::withInput('admin_voucher', $request->post());
            header('Location: ' . BASE_URL . '/admin/vouchers/create');
            exit;
        }

        Toast::success('Voucher vytvořen.');
        header('Location: ' . BASE_URL . '/admin/vouchers/' . (int)$res['id']);
        exit;
    }



    public function edit(int $id)
    {
        $voucher = $this->voucherModel->getById($id);
        if (!$voucher) return $this->show404();

        $this->view('admin/vouchers/edit', [
            'title' => 'Upravit voucher | Admin',
            'voucher' => $voucher,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $voucher = $this->voucherModel->getById($id);
        if (!$voucher) return $this->show404();

        $name = $request->string('name');
        $slug = $request->string('slug');
        $description = $request->string('description');
        $currency = $request->string('currency', (string)($voucher['currency'] ?? 'CZK'));
        $price = $request->string('price', $request->string('price_czk'));
        $isActive = $request->has('is_active') ? 1 : 0;

        $errors = [];
        if ($name === '') $errors[] = 'Název je povinný.';
        if ($slug === '') $errors[] = 'Slug je povinný.';
        if ($description === '') $errors[] = 'Popis je povinný.';
        if ($price === '' || !preg_match('~^\d+([.,]\d{1,2})?$~', $price)) $errors[] = 'Cena není ve správném formátu.';
        if ($this->voucherModel->existsBySlug($slug, $id)) $errors[] = 'Slug už existuje, zvol jiný.';

        if ($errors) {
            Toast::error(implode(' ', $errors));
            Flash::withInput('admin_voucher', $request->post());
            header('Location: ' . BASE_URL . '/admin/vouchers/edit/' . (int)$id);
            exit;
        }

        $priceCents = (int) round((float) str_replace(',', '.', $price) * 100);

        $res = $this->voucherModel->updateAdmin($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => ($description !== '' ? $description : null),
            'price_cents' => $priceCents,
            'currency' => $currency ?: 'CZK',
            'is_active' => $isActive,
        ]);

        if (!$res['success']) {
            Toast::error($res['message'] ?? 'Chyba při ukládání voucheru.');
            Flash::withInput('admin_voucher', $request->post());
            header('Location: ' . BASE_URL . '/admin/vouchers/edit/' . (int)$id);
            exit;
        }

        Toast::success('Voucher uložen.');
        header('Location: ' . BASE_URL . '/admin/vouchers/edit/' . (int)$id);
        exit;
    }

    public function deactivate(int $id)
    {
        $result = $this->voucherModel->deactivate($id);
        if ($result['success']) {
            Toast::success($result['message'] ?? 'Voucher byl deaktivován.');
        } else {
            Toast::error($result['message'] ?? 'Voucher se nepodařilo deaktivovat.');
        }
        header('Location: ' . BASE_URL . '/admin/vouchers');
        exit;
    }
}
