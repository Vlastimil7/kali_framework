<?php
namespace Controllers;

use Core\Controller;

class HomeController extends Controller {
    public function index() {
        $data = [
            'title' => 'Domů | Counter.cz'
        ];
        
        $this->view('home/index', $data);
    }
}