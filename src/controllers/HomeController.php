<?php
namespace Controllers;

use Core\Controller;

class HomeController extends Controller {
    public function index() {
        $data = [
            'title' => 'Domů | SuperKrabicky.cz'
        ];
        
        $this->view('home/index', $data);
    }
}