<?php

namespace Api\V1\Controllers;

use Api\BaseApiController;

class MenuController extends BaseApiController
{
    private $testMenuItems = [
        1 => [
            'id' => 1,
            'name' => 'Kuřecí krabička',
            'description' => 'Kuřecí maso s rýží a zeleninou',
            'price' => 159,
            'category_id' => 1,
            'is_active' => true
        ],
        2 => [
            'id' => 2,
            'name' => 'Hovězí krabička',
            'description' => 'Hovězí maso s bramborami a omáčkou',
            'price' => 189,
            'category_id' => 1,
            'is_active' => true
        ],
        3 => [
            'id' => 3,
            'name' => 'Vegetariánská krabička',
            'description' => 'Grilovaná zelenina s tofu a rýží',
            'price' => 139,
            'category_id' => 2,
            'is_active' => true
        ]
    ];
    
    private $testCategories = [
        1 => [
            'id' => 1,
            'name' => 'Masové krabičky',
            'description' => 'Krabičky s masem'
        ],
        2 => [
            'id' => 2,
            'name' => 'Vegetariánské krabičky',
            'description' => 'Krabičky bez masa'
        ]
    ];
    
    /**
     * Seznam všech položek v menu
     * GET /api/v1/menu
     */
    public function index()
    {
        if (!$this->validateMethod('GET')) {
            return;
        }
        
        $this->response($this->testMenuItems, true, 'Seznam položek menu');
    }
    
    /**
     * Detail položky menu
     * GET /api/v1/menu/{id}
     */
    public function show($id)
    {
        if (!$this->validateMethod('GET')) {
            return;
        }
        
        if (!isset($this->testMenuItems[$id])) {
            $this->response(null, false, 'Položka nebyla nalezena', 404);
            return;
        }
        
        $this->response($this->testMenuItems[$id], true, 'Detail položky menu');
    }
    
    /**
     * Seznam kategorií
     * GET /api/v1/menu/categories
     */
    public function categories()
    {
        if (!$this->validateMethod('GET')) {
            return;
        }
        
        $this->response($this->testCategories, true, 'Seznam kategorií');
    }
    
    /**
     * Položky v konkrétní kategorii
     * GET /api/v1/menu/category/{id}
     */
    public function categoryItems($categoryId)
    {
        if (!$this->validateMethod('GET')) {
            return;
        }
        
        if (!isset($this->testCategories[$categoryId])) {
            $this->response(null, false, 'Kategorie nebyla nalezena', 404);
            return;
        }
        
        $items = [];
        foreach ($this->testMenuItems as $item) {
            if ($item['category_id'] == $categoryId) {
                $items[] = $item;
            }
        }
        
        $this->response([
            'category' => $this->testCategories[$categoryId],
            'items' => $items
        ], true, 'Položky v kategorii');
    }
}