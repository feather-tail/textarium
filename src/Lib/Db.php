<?php
namespace App\Lib;

use PDO;

class Db
{
    public static function getConnection(): PDO
    {
        static $pdo;

        if (!$pdo) {
            $dsn  = $_ENV['DB_DSN'] ?? 'mysql:host=localhost;dbname=mydb;charset=utf8mb4';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $pdo;
    }
}
