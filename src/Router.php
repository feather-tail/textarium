<?php
namespace App;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->routes['GET'][] = ['path' => $path, 'handler' => $handler];
    }

    public function post(string $path, $handler): void
    {
        $this->routes['POST'][] = ['path' => $path, 'handler' => $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (strpos($route['path'], '{') === false) {
                if ($route['path'] === $uri) {
                    if (is_callable($route['handler'])) {
                        call_user_func($route['handler']);
                        return;
                    }
                    break;
                }
            } else {
                $pattern = preg_replace_callback('#\{([\w]+)(?::([^}]+))?\}#', function ($matches) {
                    return '(' . ($matches[2] ?? '[^/]+') . ')';
                }, $route['path']);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches);

                    if (is_callable($route['handler'])) {
                        call_user_func_array($route['handler'], $matches);
                        return;
                    }
                    break;
                }
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
