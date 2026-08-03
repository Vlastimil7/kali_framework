<?php

namespace Controllers\Admin;

use Models\Voucher;

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

    public function store()
    {
        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $currency = trim((string)($_POST['currency'] ?? 'CZK'));
        $price = trim((string)($_POST['price'] ?? $_POST['price_czk'] ?? ''));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($name === '') $errors[] = 'Název je povinný.';
        if ($slug === '') $errors[] = 'Slug je povinný.';
        if ($price === '' || !preg_match('~^\d+([.,]\d{1,2})?$~', $price)) $errors[] = 'Cena není ve správném formátu.';
        if ($this->voucherModel->existsBySlug($slug)) $errors[] = 'Slug už existuje, zvol jiný.';

        if ($errors) {
            $_SESSION['_flash_error'] = implode(' ', $errors);
            $_SESSION['_old'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/vouchers/create');
            exit;
        }

        $priceCents = (int) round((float) str_replace(',', '.', $price) * 100);
        $description = trim((string)($_POST['description'] ?? ''));
        $validityMonths = (int)($_POST['validity_months'] ?? 6);

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
            $_SESSION['_flash_error'] = $res['message'] ?? 'Chyba při vytváření voucheru.';
            $_SESSION['_old'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/vouchers/create');
            exit;
        }

        $_SESSION['_flash_success'] = 'Voucher vytvořen.';
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

    public function update(int $id)
    {
        $voucher = $this->voucherModel->getById($id);
        if (!$voucher) return $this->show404();

        $name = trim((string)($_POST['name'] ?? ''));
        $slug = trim((string)($_POST['slug'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $currency = trim((string)($_POST['currency'] ?? ($voucher['currency'] ?? 'CZK')));
        $price = trim((string)($_POST['price'] ?? $_POST['price_czk'] ?? ''));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if ($name === '') $errors[] = 'Název je povinný.';
        if ($slug === '') $errors[] = 'Slug je povinný.';
        if ($description === '') $errors[] = 'Popis je povinný.';
        if ($price === '' || !preg_match('~^\d+([.,]\d{1,2})?$~', $price)) $errors[] = 'Cena není ve správném formátu.';
        if ($this->voucherModel->existsBySlug($slug, $id)) $errors[] = 'Slug už existuje, zvol jiný.';

        if ($errors) {
            $_SESSION['_flash_error'] = implode(' ', $errors);
            $_SESSION['_old'] = $_POST;
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
            $_SESSION['_flash_error'] = $res['message'] ?? 'Chyba při ukládání voucheru.';
            $_SESSION['_old'] = $_POST;
            header('Location: ' . BASE_URL . '/admin/vouchers/edit/' . (int)$id);
            exit;
        }

        $_SESSION['_flash_success'] = 'Voucher uložen.';
        header('Location: ' . BASE_URL . '/admin/vouchers/edit/' . (int)$id);
        exit;
    }

    public function deactivate(int $id)
    {
        $result = $this->voucherModel->deactivate($id);
        $_SESSION[$result['success'] ? '_flash_success' : '_flash_error'] = $result['message'] ?? 'Voucher se nepodařilo deaktivovat.';
        header('Location: ' . BASE_URL . '/admin/vouchers');
        exit;
    }
}
