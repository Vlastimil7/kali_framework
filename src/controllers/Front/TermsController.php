<?php

namespace Controllers\Front;

use Core\Controller;

class TermsController extends Controller
{

    public function show()
    {
        $this->view('terms/index', [
            'title' => 'Obchodní podmínky',
        ]);
    }

    public function showShippingPayment()
    {
        $this->view('terms/shipping_payment', [
            'title' => 'Obchodní podmínky - Doprava a platba',
        ]);
    }
}
