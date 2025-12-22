<?php

namespace Controllers\Admin;

use Core\Controller;

class BaseAdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}
