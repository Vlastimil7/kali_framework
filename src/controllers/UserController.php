<?php
namespace Controllers;

use Core\Controller;
use Models\User;
use Models\Order;

class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Zobrazení přihlašovacího formuláře
    public function showLogin() {
        $this->view('users/login', [
            'title' => 'Přihlášení | SuperKrabicky.cz'
        ]);
    }
    
    // Zpracování přihlášení
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $this->userModel->login($email, $password);
            
            if ($result['success']) {
                // Uložení údajů do session
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_name'] = $result['user']['name'];
                $_SESSION['user_email'] = $result['user']['email'];
                $_SESSION['user_role'] = $result['user']['role'];
                
                // Přesměrování podle role
                if ($result['user']['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/dashboard');
                } else {
                    header('Location: ' . BASE_URL . '/dashboard');
                }
                exit;
            } else {
                // Nastavení flash zprávy
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                
                // Zachování emailu pro pohodlí
                $_SESSION['form_data'] = ['email' => $email];
                
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
    }
    
    // Zobrazení registračního formuláře
    public function showRegister() {
        $this->view('users/register', [
            'title' => 'Registrace | SuperKrabicky.cz'
        ]);
    }
    
    // Zpracování registrace
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'name' => $_POST['name'] ?? '',
                'surname' => $_POST['surname'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            
            $result = $this->userModel->register($userData);
            
            if ($result['success']) {
                $_SESSION['flash_message'] = 'Registrace proběhla úspěšně! Nyní se můžete přihlásit.';
                $_SESSION['flash_type'] = 'success';
                
                header('Location: ' . BASE_URL . '/login');
                exit;
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                
                // Zachování zadaných údajů
                $_SESSION['form_data'] = $userData;
                
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
        }
    }
    
    // Zobrazení profilu uživatele
    public function showProfile() {
        // Kontrola, zda je uživatel přihlášen
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        // Případně načtení objednávek uživatele
        $orderModel = new Order();
        $orders = $orderModel->getRecentOrdersByUserId($userId, 5); // Posledních 5 objednávek
        
        $this->view('users/profile', [
            'title' => 'Můj profil | SuperKrabicky.cz',
            'user' => $user,
            'orders' => $orders
        ]);
    }
    
    // Aktualizace profilu
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Kontrola, zda je uživatel přihlášen
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Získání aktualizovaných údajů
            $userData = [
                'name' => $_POST['name'] ?? '',
                'surname' => $_POST['surname'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            
            // Přidání hesla, pokud bylo vyplněno
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            }
            
            $result = $this->userModel->updateProfile($userId, $userData);
            
            if ($result['success']) {
                $_SESSION['flash_message'] = 'Profil byl úspěšně aktualizován';
                $_SESSION['flash_type'] = 'success';
                
                // Aktualizace session proměnných
                $_SESSION['user_name'] = $userData['name'];
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }
            
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }
    }
    
    // Odhlášení uživatele
    public function logout() {
        // Zničení session
        session_unset();
        session_destroy();
        
        // Přesměrování na přihlašovací stránku
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}