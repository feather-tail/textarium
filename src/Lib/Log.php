<?php
namespace App\Lib;

use App\Lib\Db;
use App\Lib\Auth;

class Log
{
  public static function write(
    string $action,
    string $type = null,
    int $id = null,
    string $details = null,
  ): void {
    $pdo = Db::getConnection();

    $stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, object_type, object_id, details)
            VALUES (?, ?, ?, ?, ?)
        ");
    $user = Auth::currentUser();
    $stmt->execute([$user["id"] ?? null, $action, $type, $id, $details]);
  }
}
