<?php

namespace Controllers;

use Core\Controller;
use Models\User;
use Models\Order;
use Models\Menu;
use Models\MealSize;

class DashboardController extends Controller
{
    private $userModel;
    private $orderModel;
    private $menuModel;
    private $mealSizeModel;

    public function __construct()
    {
        // Kontrola, zda je uživatel přihlášen
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->userModel = new User();
        $this->orderModel = new Order();
        $this->menuModel = new Menu();
        $this->mealSizeModel = new MealSize();
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

        // Pro běžné uživatele
        $activeOrders = $this->orderModel->getActiveOrdersByUserId($userId);
        $categories = $this->menuModel->getActiveMenuCategories();
        $menuItems = [];
        $mealSizes = $this->mealSizeModel->getActiveSizes();

        // Získání položek pro každou kategorii
        foreach ($categories as $category) {
            $menuItems[$category['id']] = $this->menuModel->getCurrentMenuItemsByCategory($category['id']);
        }

        $this->view('dashboard/index', [
            'title' => 'Můj přehled | SuperKrabicky.cz',
            'user' => $user,
            'activeOrders' => $activeOrders,
            'categories' => $categories,
            'menuItems' => $menuItems,
            'mealSizes' => $mealSizes
        ]);
    }
}
