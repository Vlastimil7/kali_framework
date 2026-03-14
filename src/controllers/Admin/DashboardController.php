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

    public function index()
    {
        $userCount = $this->userModel->getUserCount();

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard | VK-DEV.cz',
            'userCount' => $userCount,

        ]);
    }
}
