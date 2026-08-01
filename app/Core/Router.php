<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Enterprise Application Router
 */
class Router
{
    private static array $routes = [];

    public static function get(string $path, callable|array|string $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, callable|array|string $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    private static function addRoute(string $method, string $path, callable|array|string $handler): void
    {
        self::$routes[] = [
            'method'  => $method,
            'path'    => '/' . trim($path, '/'),
            'handler' => $handler
        ];
    }

    public static function dispatch(?string $requestUri = null, ?string $requestMethod = null): void
    {
        $uri = $requestUri ?? $_SERVER['REQUEST_URI'] ?? '/';
        $method = $requestMethod ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Strip query string
        $uri = strtok($uri, '?') ?: '/';

        // Remove base directory path if app is running in a subfolder (e.g., /KVN_Construction/public)
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseDir = rtrim(dirname($scriptName), '/');
        if ($baseDir !== '' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route path pattern to regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func_array($handler, $matches);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    if (class_exists($class)) {
                        $controller = new $class();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $matches);
                            return;
                        }
                    }
                }

                if (is_string($handler) && str_contains($handler, '@')) {
                    [$class, $action] = explode('@', $handler, 2);
                    $fullClass = str_starts_with($class, 'App\\Controllers\\') ? $class : 'App\\Controllers\\' . $class;
                    if (class_exists($fullClass)) {
                        $controller = new $fullClass();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $matches);
                            return;
                        }
                    }
                }
            }
        }

        self::notFound();
    }

    public static function notFound(): void
    {
        http_response_code(404);
        $viewPath = defined('ROOT_PATH') ? ROOT_PATH . '/app/views/errors/404.php' : '../app/views/errors/404.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "<h1>404 Not Found</h1><p>The requested route was not found.</p>";
        }
        exit;
    }
}
