<?php

namespace Controllers\Admin;

use Core\Request;
use Models\User;
use Helpers\Flash;
use Helpers\Toast;

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

    public function store(Request $request)
    {
        if (!$request->isMethod('POST')) {
            return $this->show404();
        }

        $userData = [
            'email' => $request->string('email'),
            'password' => $request->string('password'),
            'name' => $request->string('name'),
            'surname' => $request->string('surname'),
            'phone' => $request->string('phone'),
            'role' => $request->string('role', 'user'),
        ];

        $result = $this->userModel->register($userData);

        if ($result['success']) {
            Toast::success('Uživatel byl úspěšně vytvořen');
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }

        Toast::error($result['message']);
        Flash::withInput('admin_user', $userData);
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

    public function update(Request $request, int $id)
    {
        if (!$request->isMethod('POST')) {
            return $this->show404();
        }

        $userData = [
            'name' => $request->string('name'),
            'surname' => $request->string('surname'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'role' => $request->string('role', 'user'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = $request->string('password');
        }

        $result = $this->userModel->updateUser($id, $userData);

        if ($result['success']) {
            Toast::success('Uživatel byl úspěšně aktualizován');
        } else {
            Toast::error($result['message'] ?? 'Chyba při aktualizaci');
        }

        header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
        exit;
    }

    public function delete(Request $request, int $id)
    {
        if (!$request->isMethod('POST')) {
            return $this->show404();
        }

        $result = $this->userModel->deleteUser($id);

        if ($result['success']) {
            Toast::success('Uživatel byl úspěšně smazán');
        } else {
            Toast::error($result['message'] ?? 'Chyba při mazání');
        }

        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
}
