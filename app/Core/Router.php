<?php

namespace App\Core;

class Router
{
    private static array $routes = [];

    public static function get(string $path, array $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, array $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (isset(self::$routes[$method][$uri])) {
            [$controller, $action] = self::$routes[$method][$uri];
            if (class_exists($controller) && method_exists($controller, $action)) {
                (new $controller())->$action();
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
