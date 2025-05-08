<?php

namespace Controllers;

use Core\Controller;
use Models\DeliveryLocation;

class LocationController extends Controller
{
    private $locationModel;
    
    public function __construct()
    {
        $this->locationModel = new DeliveryLocation();
        
        // Kontrola, zda je přihlášen admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash_message'] = 'Pro přístup k této sekci musíte být přihlášeni jako administrátor';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
    
    /**
     * Zobrazení seznamu míst doručení
     */
    public function index()
    {
        // Získání všech míst doručení
        $locations = $this->locationModel->getAllActiveLocations();
        
        $this->view('admin/locations/index', [
            'title' => 'Správa míst doručení | SuperKrabicky.cz',
            'locations' => $locations
        ]);
    }
    
    /**
     * Zobrazení formuláře pro přidání nového místa doručení
     */
    public function add()
    {
        $this->view('admin/locations/form', [
            'title' => 'Přidat místo doručení | SuperKrabicky.cz',
            'location' => null,
            'action' => 'add'
        ]);
    }
    
    /**
     * Zpracování přidání nového místa doručení
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Validace
            $errors = [];
            
            if (empty($data['name'])) {
                $errors[] = 'Název místa doručení je povinný';
            }
            
            if (empty($data['address'])) {
                $errors[] = 'Adresa místa doručení je povinná';
            }
            
            if (empty($errors)) {
                $result = $this->locationModel->addLocation($data);
                
                if ($result['success']) {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'success';
                    header('Location: ' . BASE_URL . '/admin/locations');
                    exit;
                } else {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode('<br>', $errors);
                $_SESSION['flash_type'] = 'error';
            }
            
            // Pokud jsou chyby, vrátíme data zpět do formuláře
            $this->view('admin/locations/form', [
                'title' => 'Přidat místo doručení | SuperKrabicky.cz',
                'location' => $data,
                'action' => 'add'
            ]);
        } else {
            header('Location: ' . BASE_URL . '/admin/locations/add');
            exit;
        }
    }
    
    /**
     * Zobrazení formuláře pro úpravu místa doručení
     * 
     * @param int $id ID místa doručení
     */
    public function edit($id)
    {
        $location = $this->locationModel->getLocationById($id);
        
        if (!$location) {
            $_SESSION['flash_message'] = 'Místo doručení nebylo nalezeno';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . BASE_URL . '/admin/locations');
            exit;
        }
        
        $this->view('admin/locations/form', [
            'title' => 'Upravit místo doručení | SuperKrabicky.cz',
            'location' => $location,
            'action' => 'edit'
        ]);
    }
    
    /**
     * Zpracování úpravy místa doručení
     * 
     * @param int $id ID místa doručení
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Validace
            $errors = [];
            
            if (empty($data['name'])) {
                $errors[] = 'Název místa doručení je povinný';
            }
            
            if (empty($data['address'])) {
                $errors[] = 'Adresa místa doručení je povinná';
            }
            
            if (empty($errors)) {
                $result = $this->locationModel->updateLocation($id, $data);
                
                if ($result['success']) {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'success';
                    header('Location: ' . BASE_URL . '/admin/locations');
                    exit;
                } else {
                    $_SESSION['flash_message'] = $result['message'];
                    $_SESSION['flash_type'] = 'error';
                }
            } else {
                $_SESSION['flash_message'] = implode('<br>', $errors);
                $_SESSION['flash_type'] = 'error';
            }
            
            // Pokud jsou chyby, vrátíme data zpět do formuláře
            $this->view('admin/locations/form', [
                'title' => 'Upravit místo doručení | SuperKrabicky.cz',
                'location' => $data,
                'action' => 'edit',
                'id' => $id
            ]);
        } else {
            header('Location: ' . BASE_URL . '/admin/locations/edit/' . $id);
            exit;
        }
    }
    
    /**
     * Odstranění místa doručení
     * 
     * @param int $id ID místa doručení
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->locationModel->deleteLocation($id);
            
            if ($result['success']) {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }
            
            header('Location: ' . BASE_URL . '/admin/locations');
            exit;
        } else {
            header('Location: ' . BASE_URL . '/admin/locations');
            exit;
        }
    }
}