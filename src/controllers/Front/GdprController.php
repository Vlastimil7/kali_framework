<?php

namespace Controllers\Front;
use Core\Controller;

class GdprController extends Controller {

        public function show()
    {
          $this->view('gdpr/index', [
            'title' => 'Ochrana osobních údajů',
        ]);
    }
}         