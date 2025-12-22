<?php

namespace Controllers\Admin;

use Models\User;

class DashboardController extends BaseAdminController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function dashboard()
    {
        $userCount = $this->userModel->getUserCount();

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard | Midobarbershop.cz',
            'userCount' => $userCount,
            'show_sidebar' => false,
        ]);
    }
}
