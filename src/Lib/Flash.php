<?php
declare(strict_types=1);

namespace App\Lib;

class Flash
{
  private static function ensureSession(): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
  }

  public static function success(string $message): void
  {
    self::ensureSession();
    $_SESSION["flash"]["success"][] = $message;
  }

  public static function error(string $message): void
  {
    self::ensureSession();
    $_SESSION["flash"]["error"][] = $message;
  }

  public static function info(string $message): void
  {
    self::ensureSession();
    $_SESSION["flash"]["info"][] = $message;
  }

  public static function getMessages(): array
  {
    self::ensureSession();
    $messages = $_SESSION["flash"] ?? ["success" => [], "error" => [], "info" => []];
    unset($_SESSION["flash"]);
    return array_map(
      fn(array $group) => array_map(
        fn(string $msg) => htmlspecialchars($msg, ENT_QUOTES | ENT_HTML5),
        $group,
      ),
      $messages,
    );
  }
}
