<?php
namespace App\Lib;

class Csrf
{
  public static function token(): string
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $_SESSION["csrf_token"] ??= bin2hex(random_bytes(32));
    return $_SESSION["csrf_token"];
  }

  public static function input(): string
  {
    return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
  }

  public static function check(): bool
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $token = $_POST["csrf_token"] ?? ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "");
    $validToken = isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $host = $_SERVER["HTTP_HOST"] ?? "";
      $origin = $_SERVER["HTTP_ORIGIN"] ?? "";
      $scheme =
        $_SERVER["REQUEST_SCHEME"] ??
        (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http");
      $referer = $_SERVER["HTTP_REFERER"] ?? "";

      $isSameOrigin = false;

      if ($origin) {
        $isSameOrigin =
          parse_url($origin, PHP_URL_HOST) === $host &&
          parse_url($origin, PHP_URL_SCHEME) === $scheme;
      } elseif ($referer) {
        $isSameOrigin =
          parse_url($referer, PHP_URL_HOST) === $host &&
          parse_url($referer, PHP_URL_SCHEME) === $scheme;
      } else {
        error_log("[CSRF] Отсутствует Origin и Referer для POST-запроса");
        return false;
      }

      if (!$isSameOrigin) {
        error_log("[CSRF] Нарушение источника запроса: " . ($origin ?: $referer));
        return false;
      }
    }

    return $validToken;
  }

  public static function deny(): void
  {
    http_response_code(403);
    echo "<h1>⛔ CSRF-проверка не пройдена</h1>";
    exit();
  }
}
