<?php

namespace Controllers\Front;

use Core\Controller;

final class DocsController extends Controller
{
    public function index(): void
    {
        $this->view('docs/index', [
            'title' => __('page_title', [], 'docs') . ' | ' . config('app.name'),
            'description' => __('page_description', [], 'docs'),
        ]);
    }
}
