<?php

namespace Controllers\Admin;

use Models\User;

class UserController extends BaseAdminController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index()
    {
        $users = $this->userModel->getAllUsers();

        $this->view('admin/users/index', [
            'title' => 'Správa uživatelů | VK-DEV.cz',
            'users' => $users,

        ]);
    }

    public function create()
    {
        $this->view('admin/users/create', [
            'title' => 'Přidat uživatele | VK-DEV.cz',

        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->show404();
        }

        $userData = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'name' => $_POST['name'] ?? '',
            'surname' => $_POST['surname'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role' => $_POST['role'] ?? 'user',
        ];

        $result = $this->userModel->register($userData);

        if ($result['success']) {
            $_SESSION['flash_message'] = 'Uživatel byl úspěšně vytvořen';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = 'error';
        $_SESSION['form_data'] = $userData;
        header('Location: ' . BASE_URL . '/admin/users/create');
        exit;
    }

    public function edit(int $id)
    {
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            return $this->show404();
        }

        $this->view('admin/users/edit', [
            'title' => 'Úprava uživatele | VK-DEV.cz',
            'user' => $user,

        ]);
    }

    public function update(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->show404();
        }

        $userData = [
            'name' => $_POST['name'] ?? '',
            'surname' => $_POST['surname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role' => $_POST['role'] ?? 'user',
        ];

        if (!empty($_POST['password'])) {
            $userData['password'] = $_POST['password'];
        }

        $result = $this->userModel->updateUser($id, $userData);

        $_SESSION['flash_message'] = $result['success']
            ? 'Uživatel byl úspěšně aktualizován'
            : ($result['message'] ?? 'Chyba při aktualizaci');
        $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';

        header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
        exit;
    }

    public function delete(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->show404();
        }

        $result = $this->userModel->deleteUser($id);

        $_SESSION['flash_message'] = $result['success']
            ? 'Uživatel byl úspěšně smazán'
            : ($result['message'] ?? 'Chyba při mazání');
        $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
}
