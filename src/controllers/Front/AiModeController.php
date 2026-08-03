<?php
namespace Controllers\Front;

use Core\Controller;
use Core\Request;

final class AiModeController extends Controller
{
    public function toggle(Request $request)
    {
        $enabled = $request->boolean('enabled');

        $_SESSION['ai_mode'] = $enabled ? 1 : 0;

        // kam se vrátit
        $back = $request->header('Referer', locale_url());
        header('Location: ' . $back);
        exit;
    }
}
