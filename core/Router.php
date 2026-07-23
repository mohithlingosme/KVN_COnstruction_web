<?php

class Router
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];
    protected $controllerPath = '';

    public function __construct()
    {
        $url = $this->parseUrl();

        // =====================================
        // CONTROLLER RESOLUTION
        // Supports nested controllers like admin/LeadController
        // Returns 404 if no controller found
        // =====================================

        $controllerFound = false;

        if (!empty($url) && isset($url[0]) && !empty($url[0])) {
            $urlParts = $url;
            $maxDepth = count($urlParts);

            // Try progressively deeper paths to find nested controllers
            // e.g., admin/leads -> admin/LeadController
            for ($depth = $maxDepth; $depth >= 1; $depth--) {
                $possiblePath = array_slice($urlParts, 0, $depth);
                $controllerName = ucfirst(end($possiblePath)) . 'Controller';
                $subDir = ($depth > 1) ? implode('/', array_slice($possiblePath, 0, -1)) . '/' : '';
                $controllerPath = '../app/controllers/' . $subDir . $controllerName . '.php';

                if (file_exists($controllerPath)) {
                    $this->controller = $controllerName;
                    $this->controllerPath = $controllerPath;
                    // Remove controller URL segments
                    for ($i = 0; $i < $depth; $i++) {
                        unset($url[$i]);
                    }
                    $url = array_values($url);
                    $controllerFound = true;
                    break;
                }

                // Also try with plural directory name (e.g., projects/ProjectController)
                $pluralSubDir = ($depth > 1) ? implode('/', array_slice($possiblePath, 0, -1)) : $possiblePath[0] ?? '';
                $altControllerPath = '../app/controllers/' . $pluralSubDir . '/' . $controllerName . '.php';
                if (file_exists($altControllerPath)) {
                    $this->controller = $controllerName;
                    $this->controllerPath = $altControllerPath;
                    for ($i = 0; $i < $depth; $i++) {
                        unset($url[$i]);
                    }
                    $url = array_values($url);
                    $controllerFound = true;
                    break;
                }
            }

            // Fallback: try single-level controller
            if (!$controllerFound) {
                $controllerName = ucfirst($url[0]) . 'Controller';
                $controllerPath = '../app/controllers/' . $controllerName . '.php';

                if (file_exists($controllerPath)) {
                    $this->controller = $controllerName;
                    $this->controllerPath = $controllerPath;
                    unset($url[0]);
                    $url = array_values($url);
                    $controllerFound = true;
                }
            }
        }

        // If no controller was found, return 404
        if (!$controllerFound) {
            self::notFound();
            return;
        }

        // LOAD CONTROLLER
        if ($this->controllerPath === '' || !file_exists($this->controllerPath)) {
            self::notFound();
            return;
        }
        require_once $this->controllerPath;

        if (!class_exists($this->controller)) {
            self::notFound();
            return;
        }

        $this->controller = new $this->controller;

        // =====================================
        // METHOD RESOLUTION
        // =====================================

        if (isset($url[0]) && method_exists($this->controller, $url[0])) {
            $this->method = $url[0];
            unset($url[0]);
            $url = array_values($url);
        }

        // =====================================
        // PARAMETERS
        // =====================================

        $this->params = $url ? array_values($url) : [];

        // =====================================
        // EXECUTE METHOD
        // =====================================

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode(
                '/',
                filter_var(
                    rtrim($_GET['url'], '/'),
                    FILTER_SANITIZE_URL
                )
            );
        }

        // Fallback: parse from REQUEST_URI
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        // Remove query string
        $requestUri = strtok($requestUri, '?');
        
        // Remove base path
        if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Remove leading/trailing slashes
        $requestUri = trim($requestUri, '/');
        
        if (!empty($requestUri)) {
            return explode('/', filter_var($requestUri, FILTER_SANITIZE_URL));
        }

        return [];
    }

    public static function redirect($path)
    {
        header('Location: ' . base_url($path));
        exit;
    }

    public static function notFound()
    {
        http_response_code(404);
        
        // Try to load a custom 404 view
        $viewPath = '../app/views/errors/404.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "<h1>404 - Page Not Found</h1><p>The requested URL could not be found on this server.</p>";
        }
        
        exit;
    }

    public static function url($path = '')
    {
        return base_url($path);
    }

    public static function currentUrl()
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    public static function isActive($route)
    {
        return strpos(self::currentUrl(), $route) !== false;
    }

    public static function middleware($middleware)
    {
        $middlewarePath = '../middleware/' . $middleware . '.php';

        if (file_exists($middlewarePath)) {
            require_once $middlewarePath;
        } else {
            die("Middleware not found: " . $middleware);
        }
    }

    public static function controller($controller)
    {
        $controllerName = ucfirst($controller) . 'Controller';
        $controllerPath = '../app/controllers/' . $controllerName . '.php';

        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            return new $controllerName;
        }

        self::notFound();
        return null;
    }

    public static function view($view, $data = [])
    {
        // Validate view name to prevent directory traversal
        if (preg_match('/[\/\\\\]/', $view)) {
            self::notFound();
            return;
        }

        $viewPath = '../app/views/' . $view . '.php';

        if (file_exists($viewPath)) {
            // Use $data directly instead of extract()
            require_once $viewPath;
        } else {
            self::notFound();
        }
    }
}
