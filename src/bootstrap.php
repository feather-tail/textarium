<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
