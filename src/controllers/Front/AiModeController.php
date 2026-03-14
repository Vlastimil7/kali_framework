<?php
namespace Controllers\Front;

use Core\Controller;

final class AiModeController extends Controller
{
    public function toggle()
    {
        $enabled = ($_POST['enabled'] ?? '') === '1';

        $_SESSION['ai_mode'] = $enabled ? 1 : 0;

        // kam se vrátit
        $back = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/');
        header('Location: ' . $back);
        exit;
    }
}