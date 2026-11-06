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

    public static function put(string $path, array $handler): void
    {
        self::$routes['PUT'][$path] = $handler;
    }

    public static function patch(string $path, array $handler): void
    {
        self::$routes['PATCH'][$path] = $handler;
    }

    public static function delete(string $path, array $handler): void
    {
        self::$routes['DELETE'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = $_POST['_method'];
            if (!is_string($override) || !in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'], true)) {
                http_response_code(400);
                echo "400 Bad Request";
                return;
            }
            $method = strtoupper($override);
        }

        $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '', 2)[0]));
        if ($method !== 'POST' && in_array($method, ['PUT', 'PATCH', 'DELETE'], true)
            && $contentType === 'application/x-www-form-urlencoded') {
            $body = file_get_contents('php://input');
            if ($body !== false) {
                parse_str($body, $_POST);
            }
        }

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!is_string($uri)) {
            http_response_code(400);
            echo "400 Bad Request";
            return;
        }

        foreach (self::$routes[$method] ?? [] as $path => [$controller, $action]) {
            $routeSegments = explode('/', trim($path, '/'));
            $uriSegments = explode('/', trim($uri, '/'));
            if (count($routeSegments) !== count($uriSegments)) {
                continue;
            }

            $params = [];
            foreach ($routeSegments as $index => $routeSegment) {
                if (preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $routeSegment) === 1) {
                    $params[] = rawurldecode($uriSegments[$index]);
                } elseif ($routeSegment !== rawurldecode($uriSegments[$index])) {
                    continue 2;
                }
            }

            if (class_exists($controller) && method_exists($controller, $action)) {
                (new $controller())->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
