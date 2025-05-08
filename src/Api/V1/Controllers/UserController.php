<?php

namespace Api\V1\Controllers;

use Api\BaseApiController;

class UserController extends BaseApiController
{
    private $testUsers = [
        1 => [
            'id' => 1,
            'name' => 'Jan Novák',
            'email' => 'jan.novak@example.com',
            'credit' => 500,
            'role' => 'user'
        ],
        2 => [
            'id' => 2,
            'name' => 'Petr Svoboda',
            'email' => 'petr.svoboda@example.com',
            'credit' => 750,
            'role' => 'user'
        ],
        3 => [
            'id' => 3,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'credit' => 1000,
            'role' => 'admin'
        ]
    ];
    
    /**
     * Seznam uživatelů
     * GET /api/v1/users
     */
    public function index()
    {
        if (!$this->validateMethod('GET')) {
            return;
        }
        
        $this->response($this->testUsers, true, 'Seznam uživatelů');
    }
    
    /**
     * Detail uživatele
     * GET /api/v1/users/{id}
     */
    public function show($id)
    {
        if (!$this->validateMethod('GET')) {
            return;
        }
        
        if (!isset($this->testUsers[$id])) {
            $this->response(null, false, 'Uživatel nebyl nalezen', 404);
            return;
        }
        
        $this->response($this->testUsers[$id], true, 'Detail uživatele');
    }
    
    /**
     * Vytvoření uživatele
     * POST /api/v1/users
     */
    public function create()
    {
        if (!$this->validateMethod('POST')) {
            return;
        }
        
        $data = $this->getRequestData();
        
        // Simulace vytvoření
        $newUser = [
            'id' => 4, // V reálné aplikaci by ID bylo přiděleno databází
            'name' => $data['name'] ?? 'Nový uživatel',
            'email' => $data['email'] ?? 'novy@example.com',
            'credit' => $data['credit'] ?? 0,
            'role' => 'user'
        ];
        
        $this->response($newUser, true, 'Uživatel byl vytvořen', 201);
    }
    
    /**
     * Aktualizace uživatele
     * PUT /api/v1/users/{id}
     */
    public function update($id)
    {
        if (!$this->validateMethod('PUT')) {
            return;
        }
        
        if (!isset($this->testUsers[$id])) {
            $this->response(null, false, 'Uživatel nebyl nalezen', 404);
            return;
        }
        
        $data = $this->getRequestData();
        
        // Simulace aktualizace
        $updatedUser = $this->testUsers[$id];
        if (isset($data['name'])) $updatedUser['name'] = $data['name'];
        if (isset($data['email'])) $updatedUser['email'] = $data['email'];
        if (isset($data['credit'])) $updatedUser['credit'] = $data['credit'];
        
        $this->response($updatedUser, true, 'Uživatel byl aktualizován');
    }
    
    /**
     * Smazání uživatele
     * DELETE /api/v1/users/{id}
     */
    public function delete($id)
    {
        if (!$this->validateMethod('DELETE')) {
            return;
        }
        
        if (!isset($this->testUsers[$id])) {
            $this->response(null, false, 'Uživatel nebyl nalezen', 404);
            return;
        }
        
        // Simulace smazání
        $this->response(null, true, 'Uživatel byl smazán');
    }
}