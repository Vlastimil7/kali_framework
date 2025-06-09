<?php
namespace Controllers;

use Core\Controller;

class HomeController extends Controller {
    public function index() {
        $data = [
            'title' => 'Domů | Kali-framework'
        ];
        
        $this->view('home/index', $data);
    }
}