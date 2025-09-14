<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Csrf;

abstract class BaseController
{
    protected function requirePostWithCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '⛔ Метод не разрешён';
            exit;
        }

        if (!Csrf::check()) {
            Csrf::deny();
        }
    }
}
