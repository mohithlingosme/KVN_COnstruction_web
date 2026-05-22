<?php

class Router
{
    // =====================================
    // DEFAULT CONTROLLER
    // =====================================

    protected $controller =
    'HomeController';

    // =====================================
    // DEFAULT METHOD
    // =====================================

    protected $method =
    'index';

    // =====================================
    // URL PARAMETERS
    // =====================================

    protected $params = [];

    // =====================================
    // CONSTRUCTOR
    // =====================================

    public function __construct()
    {
        // GET URL

        $url =
        $this->parseUrl();

        // =====================================
        // CONTROLLER
        // =====================================

        if(
            isset($url[0]) &&
            !empty($url[0])
        ){

            $controllerName =
            ucfirst($url[0]) .
            'Controller';

            $controllerPath =
            '../app/controllers/' .
            $controllerName .
            '.php';

            if(file_exists($controllerPath)){

                $this->controller =
                $controllerName;

                unset($url[0]);
            }
        }

        // LOAD CONTROLLER

        require_once
        '../app/controllers/' .
        $this->controller .
        '.php';

        $this->controller =
        new $this->controller;

        // =====================================
        // METHOD
        // =====================================

        if(
            isset($url[1]) &&
            method_exists(
                $this->controller,
                $url[1]
            )
        ){

            $this->method =
            $url[1];

            unset($url[1]);
        }

        // =====================================
        // PARAMETERS
        // =====================================

        $this->params =
        $url
        ? array_values($url)
        : [];

        // =====================================
        // EXECUTE METHOD
        // =====================================

        call_user_func_array(

            [

                $this->controller,

                $this->method
            ],

            $this->params
        );
    }

    // =====================================
    // PARSE URL
    // =====================================

    private function parseUrl()
    {
        if(isset($_GET['url'])){

            return explode(

                '/',

                filter_var(

                    rtrim(
                        $_GET['url'],
                        '/'
                    ),

                    FILTER_SANITIZE_URL
                )
            );
        }

        return [];
    }

    // =====================================
    // REDIRECT HELPER
    // =====================================

    public static function redirect(
        $path
    )
    {
        header(

            'Location: ' .

            base_url($path)
        );

        exit;
    }

    // =====================================
    // 404 HANDLER
    // =====================================

    public static function notFound()
    {
        http_response_code(404);

        echo "

            <h1>404</h1>

            <p>Page Not Found</p>

        ";

        exit;
    }

    // =====================================
    // CLEAN URL GENERATOR
    // =====================================

    public static function url(
        $path = ''
    )
    {
        return base_url($path);
    }

    // =====================================
    // CURRENT URL
    // =====================================

    public static function currentUrl()
    {
        return $_SERVER['REQUEST_URI']
        ?? '';
    }

    // =====================================
    // CHECK ACTIVE ROUTE
    // =====================================

    public static function isActive(
        $route
    )
    {
        return strpos(

            self::currentUrl(),

            $route

        ) !== false;
    }

    // =====================================
    // ROUTE MIDDLEWARE
    // =====================================

    public static function middleware(
        $middleware
    )
    {
        $middlewarePath =
        '../middleware/' .
        $middleware .
        '.php';

        if(file_exists($middlewarePath)){

            require_once
            $middlewarePath;

        } else {

            die(

                "Middleware not found: " .

                $middleware
            );
        }
    }

    // =====================================
    // LOAD CONTROLLER MANUALLY
    // =====================================

    public static function controller(
        $controller
    )
    {
        $controllerName =
        ucfirst($controller) .
        'Controller';

        $controllerPath =
        '../app/controllers/' .
        $controllerName .
        '.php';

        if(file_exists($controllerPath)){

            require_once
            $controllerPath;

            return new $controllerName;

        } else {

            self::notFound();
        }
    }

    // =====================================
    // ROUTE TO VIEW
    // =====================================

    public static function view(
        $view,
        $data = []
    )
    {
        extract($data);

        $viewPath =
        '../app/views/' .
        $view .
        '.php';

        if(file_exists($viewPath)){

            require_once
            $viewPath;

        } else {

            self::notFound();
        }
    }
}
?>