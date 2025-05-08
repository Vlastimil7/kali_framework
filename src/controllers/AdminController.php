<?php

namespace Controllers;

use Core\Controller;
use Models\User;
use Models\Order;
use Models\Meals;
use Models\MealSize;
use Models\Menu;
use Models\CreditTransaction;
use Models\DeliveryLocation;

class AdminController extends Controller
{
    private $userModel;
    private $orderModel;
    private $mealsModel;
    private $mealSizeModel;
    private $menuModel;
    private $CreditTransactionModel;
    private $locationModel;

    public function __construct()
    {
        // Kontrola, zda je přihlášený uživatel admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $this->userModel = new User();
        $this->orderModel = new Order();
        $this->mealsModel = new Meals();
        $this->mealSizeModel = new MealSize();
        $this->menuModel = new Menu();
        $this->CreditTransactionModel = new CreditTransaction();
        $this->locationModel = new DeliveryLocation();

    }

    // Zobrazení admin dashboardu
    public function dashboard()
    {
        // Získání počtu uživatelů
        $userCount = $this->userModel->getUserCount();

        // Získání počtu objednávek
        $orderCount = $this->orderModel->getOrderCount();

        //Získání počet transakcí
        $totalTransactionCount = $this->CreditTransactionModel->getTotalTransactionCount();

        // Získání počtu čekajících objednávek
        $waitingOrdersCount = $this->CreditTransactionModel->getTransactionCountByStatus('pending');

        // Získaní počet platebních nastevní
        $paymentSettingsCount = $this->CreditTransactionModel->getPaymentSettingsCount();

        // Získání počtu míst doručení
        $locationCount = $this->locationModel->getLocationCount();

        // Získání počtu jídel
        $menuCount = $this->menuModel->getMenuCategoriesCount();



        // Můžete přidat další statistiky podle potřeby

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard | SuperKrabicky.cz',
            'userCount' => $userCount,
            'orderCount' => $orderCount,
            'waitingOrdersCount' => $waitingOrdersCount,
            'totalTransactionCount' => $totalTransactionCount,
            'paymentSettingsCount' => $paymentSettingsCount,
            'locationCount' => $locationCount,
            'menuCount' => $menuCount

        ]);
    }

    // Zobrazení seznamu uživatelů
    public function usersList()
    {
        // Získání všech uživatelů
        $users = $this->userModel->getAllUsers();

        $this->view('admin/users/index', [
            'title' => 'Správa uživatelů | SuperKrabicky.cz',
            'users' => $users
        ]);
    }

    // Formulář pro přidání uživatele
    public function addUser()
    {
        $this->view('admin/users/create', [
            'title' => 'Přidat uživatele | SuperKrabicky.cz'
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
            'title' => 'Úprava uživatele | SuperKrabicky.cz',
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
            'title' => 'Dobít kredit | SuperKrabicky.cz',
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


    // Metoda pro přidání jídla
    public function addFood()
    {
        // Získat velikosti porcí pro výběr
        $mealSizes = $this->mealSizeModel->getActiveSizes();


        $this->view('admin/meals/create', [
            'title' => 'Přidat jídlo | SuperKrabicky.cz',
            'mealSizes' => $mealSizes
        ]);
    }

    // Metoda pro uložení jídla
    public function storeFood()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Příprava dat pro přidání jídla s novými poli
            $mealData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'ingredients' => $_POST['ingredients'] ?? '',
                'allergens' => $_POST['allergens'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'calories' => $_POST['calories'] ?? null,
                'proteins' => $_POST['proteins'] ?? null,
                'carbs' => $_POST['carbs'] ?? null,
                'fats' => $_POST['fats'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Zpracování uploaded obrázku
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = ROOT_PATH . '/public/uploads/meals/';

                // Vytvoření adresáře, pokud neexistuje
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;

                // Přesun nahraného souboru
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Uložení relativní cesty k obrázku
                    $mealData['image_path'] = '/uploads/meals/' . $fileName;
                } else {
                    $_SESSION['flash_message'] = 'Nepodařilo se nahrát obrázek';
                    $_SESSION['flash_type'] = 'error';
                }
            }

            // Přidání jídla
            $result = $this->mealsModel->addMeal($mealData);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Jídlo bylo úspěšně přidáno';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/meals');
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                $_SESSION['form_data'] = $mealData;
                header('Location: ' . BASE_URL . '/admin/meals/create');
            }
            exit;
        }
    }

    // Metoda pro seznam jídel
    public function mealsList()
    {
        // Získání všech jídel
        $meals = $this->mealsModel->getAllMeals();

        $this->view('admin/meals/index', [
            'title' => 'Správa jídel | SuperKrabicky.cz',
            'meals' => $meals
        ]);
    }

    // Zobrazení seznamu kategorií menu s počtem položek
    public function menuList()
    {
        // Získání všech kategorií menu s počtem položek
        $menuCategories = $this->menuModel->getAllMenuCategoriesWithItemCount();

        $this->view('admin/menu/items', [
            'title' => 'Správa menu | SuperKrabicky.cz',
            'menuCategories' => $menuCategories
        ]);
    }

    // Zobrazení položek konkrétní kategorie menu
    public function menuCategoryItems($menuId)
    {
        // Získání detailu kategorie
        $menuCategory = $this->menuModel->getMenuCategory($menuId);

        if (!$menuCategory) {
            $this->show404();
            return;
        }

        // Získání všech položek v této kategorii
        $menuItems = $this->menuModel->getMenuItemsByCategory($menuId);

        // Získání všech aktivních jídel pro případné přidání do menu
        $availableMeals = $this->mealsModel->getActiveMeals();
        $sizes = $this->mealSizeModel->getActiveSizes();

        $this->view('admin/menu/category_items', [
            'title' => 'Položky kategorie ' . $menuCategory['name'] . ' | SuperKrabicky.cz',
            'menuCategory' => $menuCategory,
            'menuItems' => $menuItems,
            'availableMeals' => $availableMeals,
            'sizes' => $sizes
        ]);
    }

    // Přidání jídla do konkrétní kategorie menu
    public function addMealToCategory($menuId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $menuItemData = [
                'menu_id' => $menuId,
                'meal_id' => $_POST['meal_id'] ?? null,
                'meal_size_id' => $_POST['meal_size_id'] ?? null,
                'date_from' => $_POST['date_from'] ?? date('Y-m-d'),
                'date_to' => !empty($_POST['date_to']) ? $_POST['date_to'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Validace
            if (empty($menuItemData['meal_id'])) {
                $_SESSION['flash_message'] = 'Jídlo je povinná položka';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/admin/menu/category/' . $menuId);
                exit;
            }

            $result = $this->menuModel->addMenuItem($menuItemData);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/menu/category/' . $menuId);
            exit;
        }
    }

    // Odebrání jídla z kategorie menu
    public function removeMealFromCategory($menuItemId, $menuId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->menuModel->deactivateMenuItem($menuId);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Položka byla úspěšně odebrána z menu';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/menu/category/' . $menuItemId);
            exit;
        }
    }

    // Metoda pro úpravu jídla
    public function editMeal($id)
    {
        // Získání dat jídla
        $meal = $this->mealsModel->getMealById($id);

        if (!$meal) {
            $this->show404();
            return;
        }

        // Získat velikosti porcí pro výběr
        $mealSizes = $this->mealSizeModel->getActiveSizes();

        $this->view('admin/meals/edit', [
            'title' => 'Úprava jídla | SuperKrabicky.cz',
            'meal' => $meal,
            'mealSizes' => $mealSizes
        ]);
    }

    // Metoda pro aktualizaci jídla
    public function updateMeal($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Příprava dat pro aktualizaci jídla
            $mealData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'ingredients' => $_POST['ingredients'] ?? '',
                'allergens' => $_POST['allergens'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'calories' => $_POST['calories'] ?? null,
                'proteins' => $_POST['proteins'] ?? null,
                'carbs' => $_POST['carbs'] ?? null,
                'fats' => $_POST['fats'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Zpracování uploaded obrázku
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = ROOT_PATH . '/public/uploads/meals/';

                // Vytvoření adresáře, pokud neexistuje
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $uploadPath = $uploadDir . $fileName;

                // Přesun nahraného souboru
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Uložení relativní cesty k obrázku
                    $mealData['image_path'] = '/uploads/meals/' . $fileName;
                } else {
                    $_SESSION['flash_message'] = 'Nepodařilo se nahrát obrázek';
                    $_SESSION['flash_type'] = 'error';
                }
            }

            // Aktualizace jídla
            $result = $this->mealsModel->updateMeal($id, $mealData);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Jídlo bylo úspěšně aktualizováno';
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/meals');
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/admin/meals/edit/' . $id);
            }
            exit;
        }
    }


    // Zobrazení seznamu kategorií menu
    public function menuCategories()
    {

        $categories = $this->menuModel->getAllMenuCategories();

        $this->view('admin/menu/categories', [
            'title' => 'Kategorie menu | SuperKrabicky.cz',
            'categories' => $categories
        ]);
    }

    // Formulář pro přidání kategorie menu
    public function addMenuCategory()
    {
        $this->view('admin/menu/category_create', [
            'title' => 'Přidat kategorii menu | SuperKrabicky.cz'
        ]);
    }

    // Zpracování formuláře pro přidání kategorie menu
    public function storeMenuCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $categoryData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $result = $this->menuModel->addMenuCategory($categoryData);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/menu/categories');
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                $_SESSION['form_data'] = $categoryData;
                header('Location: ' . BASE_URL . '/admin/menu/categories/add');
            }
            exit;
        }
    }

    // Zobrazení formuláře pro úpravu kategorie menu
    public function editMenuCategory($id)
    {

        $category = $this->menuModel->getMenuCategory($id);

        if (!$category) {
            $this->show404();
            return;
        }

        $this->view('admin/menu/category_edit', [
            'title' => 'Upravit kategorii menu | SuperKrabicky.cz',
            'category' => $category
        ]);
    }

    // Zpracování aktualizace kategorie menu
    public function updateMenuCategory($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $categoryData = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $result = $this->menuModel->updateMenuCategory($id, $categoryData);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/menu/categories/edit/' . $id);
            exit;
        }
    }


    // Formulář pro přidání položky do menu
    public function addMenuItem()
    {
        $categories = $this->menuModel->getActiveMenuCategories();
        $meals = $this->mealsModel->getActiveMeals();
        $sizes = $this->mealSizeModel->getActiveSizes();

        $this->view('admin/menu/item_create', [
            'title' => 'Přidat položku do menu | SuperKrabicky.cz',
            'categories' => $categories,
            'meals' => $meals,
            'sizes' => $sizes
        ]);
    }

    // Zpracování formuláře pro přidání položky do menu
    public function storeMenuItem()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $menuItemData = [
                'menu_id' => $_POST['menu_id'] ?? null,
                'meal_id' => $_POST['meal_id'] ?? null,
                'meal_size_id' => $_POST['meal_size_id'] ?? null,
                'date_from' => $_POST['date_from'] ?? date('Y-m-d'),
                'date_to' => !empty($_POST['date_to']) ? $_POST['date_to'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Validace
            if (empty($menuItemData['menu_id']) || empty($menuItemData['meal_id'])) {
                $_SESSION['flash_message'] = 'Kategorie menu a jídlo jsou povinné položky';
                $_SESSION['flash_type'] = 'error';
                $_SESSION['form_data'] = $menuItemData;
                header('Location: ' . BASE_URL . '/admin/menu/items/add');
                exit;
            }

            $result = $this->menuModel->addMenuItem($menuItemData);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . BASE_URL . '/admin/menu/items');
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
                $_SESSION['form_data'] = $menuItemData;
                header('Location: ' . BASE_URL . '/admin/menu/items/add');
            }
            exit;
        }
    }

    // Zobrazení formuláře pro úpravu položky menu
    public function editMenuItem($id)
    {
        $menuItem = $this->menuModel->getMenuItem($id);

        if (!$menuItem) {
            $this->show404();
            return;
        }

        $categories = $this->menuModel->getActiveMenuCategories();
        $meals = $this->mealsModel->getActiveMeals();
        $sizes = $this->mealSizeModel->getActiveSizes();

        $this->view('admin/menu/item_edit', [
            'title' => 'Upravit položku menu | SuperKrabicky.cz',
            'menuItem' => $menuItem,
            'categories' => $categories,
            'meals' => $meals,
            'sizes' => $sizes
        ]);
    }

    // Zpracování aktualizace položky menu
    public function updateMenuItem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $menuItemData = [
                'menu_id' => $_POST['menu_id'] ?? null,
                'meal_id' => $_POST['meal_id'] ?? null,
                'meal_size_id' => $_POST['meal_size_id'] ?? null,
                'date_from' => $_POST['date_from'] ?? date('Y-m-d'),
                'date_to' => !empty($_POST['date_to']) ? $_POST['date_to'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $result = $this->menuModel->updateMenuItem($id, $menuItemData);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/menu/items/edit/' . $id);
            exit;
        }
    }

    // Deaktivace položky menu
    public function deactivateMenuItem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->menuModel->deactivateMenuItem($id);

            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }

            header('Location: ' . BASE_URL . '/admin/menu/items');
            exit;
        }
    }

    /**
     * Aktualizace stavu objednávky (jen pro admina)
     */
    public function updateOrderStatus($id)
    {
        // Kontrola, zda je uživatel admin
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash_message'] = 'Nemáte oprávnění k této akci';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';

            // Validace stavu
            $validStatuses = ['pending', 'confirmed', 'preparing', 'delivering', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                $_SESSION['flash_message'] = 'Neplatný stav objednávky';
                $_SESSION['flash_type'] = 'error';
                header('Location: ' . BASE_URL . '/orders/view/' . $id);
                exit;
            }

            // Aktualizace stavu objednávky
            
            $result = $this->orderModel->updateOrderStatus($id, $status);

            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = $result['success'] ? 'success' : 'error';

            header('Location: ' . BASE_URL . '/orders/view/' . $id);
            exit;
        } else {
            header('Location: ' . BASE_URL . '/orders/view/' . $id);
            exit;
        }
    }
}
