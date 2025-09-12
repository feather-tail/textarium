<?php
namespace App;

class Router
{
  private array $routes = [];

  public function get(string $path, callable $handler): void
  {
    if (!is_callable($handler)) {
      throw new \InvalidArgumentException("Handler must be callable");
    }
    $this->routes["GET"][] = ["path" => $path, "handler" => $handler];
  }

  public function post(string $path, callable $handler): void
  {
    if (!is_callable($handler)) {
      throw new \InvalidArgumentException("Handler must be callable");
    }
    $this->routes["POST"][] = ["path" => $path, "handler" => $handler];
  }

  public function dispatch(): void
  {
    $method = $_SERVER["REQUEST_METHOD"];
    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    foreach ($this->routes[$method] ?? [] as $route) {
      if (strpos($route["path"], "{") === false) {
        if ($route["path"] === $uri) {
          call_user_func($route["handler"]);
          return;
        }
      } else {
        $pattern = preg_replace_callback(
          "#\{([\w]+)(?::([^}]+))?\}#",
          function ($matches) {
            return "(" . ($matches[2] ?? "[^/]+") . ")";
          },
          $route["path"],
        );
        $pattern = "#^" . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
          array_shift($matches);
          call_user_func_array($route["handler"], $matches);
          return;
        }
      }
    }

    http_response_code(404);
    echo "404 Not Found";
  }
}
