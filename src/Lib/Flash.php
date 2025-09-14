<?php
declare(strict_types=1);

namespace App\Lib;

class Flash
{
    public static function success(string $message): void
    {
        $_SESSION['flash']['success'][] = $message;
    }

    public static function error(string $message): void
    {
        $_SESSION['flash']['error'][] = $message;
    }

    public static function getMessages(): array
    {
        $messages = $_SESSION['flash'] ?? ['success' => [], 'error' => []];
        unset($_SESSION['flash']);
        return array_map(
            fn(array $group) => array_map(
                fn(string $msg) => htmlspecialchars($msg, ENT_QUOTES | ENT_HTML5),
                $group
            ),
            $messages
        );
    }
}
