<?php

namespace Controllers\Front;

use Core\Controller;

final class ContactPageController extends Controller
{
    public function index(): void
    {
        $this->view('pages/contact', [
            'title' => __('contact_page_title') . ' | ' . config('app.name'),
            'description' => __('contact_page_description'),
        ]);
    }
}
