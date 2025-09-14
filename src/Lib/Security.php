<?php
declare(strict_types=1);

namespace App\Lib;

class Security
{
    public static function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if (!preg_match('#^https?://#i', $url)) {
            return false;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return false;
        }

        return true;
    }
}
