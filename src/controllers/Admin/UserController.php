<?php

namespace Controllers\Admin;

use Core\Request;
use Models\User;
use Helpers\Flash;
use Helpers\Toast;
use Helpers\Validator;

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

        $adminUserValidationData = $userData;
        $adminUserValidationData['password_confirm'] = $request->string('password_confirm');

        $validator = Validator::make($adminUserValidationData, [
            'email' => 'bail|required|email|max:254',
            'password' => 'bail|required|string|min:8|max:255',
            'name' => 'bail|required|string|max:100',
            'surname' => 'bail|required|string|max:100',
            'phone' => ['nullable', 'regex:/^[0-9+\s\-]{6,20}$/'],
            'role' => 'required|in:user,admin',
            'password_confirm' => 'bail|required|string|same:password',
        ], [
            'password_confirm.same' => 'Hesla se neshodují.',
        ], [
            'email' => 'e-mail',
            'password' => 'heslo',
            'name' => 'jméno',
            'surname' => 'příjmení',
            'phone' => 'telefon',
            'role' => 'role',
            'password_confirm' => 'potvrzení hesla',
        ]);
        if ($validator->fails()) {
            $validator->flash('admin_user', $request->post());
            header('Location: ' . BASE_URL . '/admin/users/create');
            exit;
        }

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

        $adminUserValidationData = $userData;
        $adminUserValidationData['password_confirm'] = $request->string('password_confirm');

        $validator = Validator::make($adminUserValidationData, [
            'name' => 'bail|required|string|max:100',
            'surname' => 'bail|required|string|max:100',
            'email' => 'bail|required|email|max:254',
            'phone' => ['nullable', 'regex:/^[0-9+\s\-]{6,20}$/'],
            'role' => 'required|in:user,admin',
            'password' => 'sometimes|nullable|string|min:8|max:255',
            'password_confirm' => 'bail|required_with:password|nullable|string|same:password',
        ], [
            'password_confirm.same' => 'Hesla se neshodují.',
        ], [
            'name' => 'jméno',
            'surname' => 'příjmení',
            'email' => 'e-mail',
            'phone' => 'telefon',
            'role' => 'role',
            'password' => 'heslo',
            'password_confirm' => 'potvrzení hesla',
        ]);
        if ($validator->fails()) {
            $validator->flash('admin_user', $request->post());
            header('Location: ' . BASE_URL . '/admin/users/edit/' . $id);
            exit;
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
