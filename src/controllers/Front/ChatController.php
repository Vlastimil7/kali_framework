<?php

namespace Controllers\Front;

use Core\Controller;

class ChatController extends Controller
{
    public function index()
    {
        $this->view('chat/index', [
            'title' => 'Chat s Vlastimilem Kaláškem',
        ]);
    }


}
