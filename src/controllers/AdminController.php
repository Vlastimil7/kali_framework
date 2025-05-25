<?php

namespace Controllers;

use Core\Controller;
use Models\User;


class AdminController extends Controller
{
    private $userModel;


    public function __construct()
    {
        // Kontrola, zda je přihlášený uživatel admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->userModel = new User();


    }

    // Zobrazení admin dashboardu
    public function dashboard()
    {
        // Získání počtu uživatelů
        $userCount = $this->userModel->getUserCount();




        // Můžete přidat další statistiky podle potřeby

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard | Counter.cz',
            'userCount' => $userCount

        ]);
    }

    // Zobrazení seznamu uživatelů
    public function usersList()
    {
        // Získání všech uživatelů
        $users = $this->userModel->getAllUsers();

        $this->view('admin/users/index', [
            'title' => 'Správa uživatelů | Counter.cz',
            'users' => $users
        ]);
    }

    // Formulář pro přidání uživatele
    public function addUser()
    {
        $this->view('admin/users/create', [
            'title' => 'Přidat uživatele | Counter.cz'
        ]);
    }
    // Zobrazení editace uživatele
    public function editUser($id)
    {
        // Získání dat uživatele
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->show404();
            return;
        }

        $this->view('admin/users/edit', [
            'title' => 'Úprava uživatele | Counter.cz',
            'user' => $user
        ]);
    }

    // Zpracování aktualizace uživatele
    public function updateUser($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Získání aktualizovaných údajů
            $userData = [
                'name' => $_POST['name'] ?? '',
                'surname' => $_POST['surname'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'role' => $_POST['role'] ?? 'user',
                'credit_balance' => $_POST['credit_balance'] ?? 0,
                'allow_debit' => isset($_POST['allow_debit']) ? 1 : 0
            ];

            // Přidání hesla, pokud bylo vyplněno
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }

            $result = $this->userModel->updateUser($id, $userData);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Uživatel byl úspěšně aktualizován';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
            exit;
        }
    }

    // Smazání uživatele
    public function deleteUser($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->userModel->deleteUser($id);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Uživatel byl úspěšně smazán';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
    }

    // Zpracování formuláře pro přidání uživatele
    public function storeUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'name' => $_POST['name'] ?? '',
                'surname' => $_POST['surname'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'credit_balance' => $_POST['credit_balance'] ?? 0,
                'allow_debit' => isset($_POST['allow_debit']) ? 1 : 0,
                'role' => $_POST['role'] ?? 'user'
            ];

            $result = $this->userModel->register($userData);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Uživatel byl úspěšně vytvořen';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/users');
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                $_SESSION['form_data'] = $userData;
                header('Location: ' . BASE_URL . '/admin/users/add');
            }
            exit;
        }
    }

    // Dobití kreditu uživateli
    public function addCredit($id)
    {
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->show404();
            return;
        }

        $this->view('admin/users/add_credit', [
            'title' => 'Dobít kredit | Counter.cz',
            'user' => $user
        ]);
    }

    // Zpracování dobití kreditu
    public function processAddCredit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

            if ($amount <= 0) {
                $_SESSION['flash_message'] = 'Částka musí být větší než 0';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/admin/users/credit/' . $id);
                exit;
            }

            $result = $this->userModel->updateCredit($id, $amount);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Kredit byl úspěšně dobit';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/admin/users/credit/' . $id);
            }
            exit;
        }
    }




}
