<?php

namespace Controllers;

use Core\Controller;
use Models\User;


class DashboardController extends Controller
{
    private $userModel;
 

    public function __construct()
    {
        // Kontrola, zda je uživatel přihlášen
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->userModel = new User();

    }

    // V DashboardController.php upravte metodu index
    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Získání dat o uživateli
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            // Pokud uživatel neexistuje, odhlásíme ho
            session_unset();
            session_destroy();
            header('Location: ' . BASE_URL . '/login');
            exit;
        }



    

        $this->view('dashboard/index', [
            'title' => 'Můj přehled | Counter.cz',
            'user' => $user
        ]);
    }
}
