<?php

namespace Controllers\Front;

use Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Domů | Midobarbershop.cz',
            'show_sidebar' => false,

        ];

        $this->view('home/index', $data);
    }
}
