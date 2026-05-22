<?php

class View
{
    // =====================================
    // SHARED GLOBAL DATA
    // =====================================

    protected static $sharedData = [];

    // =====================================
    // RENDER VIEW
    // =====================================

    public static function render(
        $view,
        $data = [],
        $layout = null
    )
    {
        // MERGE SHARED DATA

        $data = array_merge(

            self::$sharedData,

            $data
        );

        // EXTRACT VARIABLES

        extract($data);

        // VIEW PATH

        $viewPath =
        '../app/views/' .
        $view .
        '.php';

        // CHECK VIEW EXISTS

        if(!file_exists($viewPath)){

            die(

                "View not found: " .

                $viewPath
            );
        }

        // START BUFFER

        ob_start();

        require $viewPath;

        $content =
        ob_get_clean();

        // LOAD LAYOUT

        if($layout){

            $layoutPath =
            '../app/views/layouts/' .
            $layout .
            '.php';

            if(!file_exists($layoutPath)){

                die(

                    "Layout not found: " .

                    $layoutPath
                );
            }

            require $layoutPath;

        } else {

            echo $content;
        }
    }

    // =====================================
    // INCLUDE PARTIAL
    // =====================================

    public static function partial(
        $partial,
        $data = []
    )
    {
        extract($data);

        $partialPath =
        '../app/views/' .
        $partial .
        '.php';

        if(file_exists($partialPath)){

            require $partialPath;

        } else {

            die(

                "Partial not found: " .

                $partialPath
            );
        }
    }

    // =====================================
    // SHARE DATA GLOBALLY
    // =====================================

    public static function share(
        $key,
        $value
    )
    {
        self::$sharedData[$key] =
        $value;
    }

    // =====================================
    // SHARE MULTIPLE DATA
    // =====================================

    public static function shareMany(
        $data = []
    )
    {
        self::$sharedData = array_merge(

            self::$sharedData,

            $data
        );
    }

    // =====================================
    // ESCAPE OUTPUT
    // =====================================

    public static function escape($value)
    {
        return htmlspecialchars(

            $value,

            ENT_QUOTES,

            'UTF-8'
        );
    }

    // =====================================
    // CHECK VIEW EXISTS
    // =====================================

    public static function exists(
        $view
    )
    {
        return file_exists(

            '../app/views/' .
            $view .
            '.php'
        );
    }

    // =====================================
    // RENDER JSON
    // =====================================

    public static function json(
        $data = [],
        $status = 200
    )
    {
        http_response_code($status);

        header(
            'Content-Type: application/json'
        );

        echo json_encode($data);

        exit;
    }

    // =====================================
    // RENDER ERROR PAGE
    // =====================================

    public static function error(
        $message = 'Page Not Found',
        $status = 404
    )
    {
        http_response_code($status);

        echo "

            <h1>{$status}</h1>

            <p>{$message}</p>

        ";

        exit;
    }

    // =====================================
    // GET FLASH MESSAGE
    // =====================================

    public static function flash()
    {
        if(isset($_SESSION['flash'])){

            $flash =
            $_SESSION['flash'];

            unset($_SESSION['flash']);

            return $flash;
        }

        return null;
    }

    // =====================================
    // DISPLAY FLASH ALERT
    // =====================================

    public static function flashAlert()
    {
        $flash =
        self::flash();

        if($flash){

            echo '

                <div class="alert alert-' .

                $flash['type'] .

                ' alert-auto-dismiss">

                    ' .

                    self::escape(
                        $flash['message']
                    ) .

                '

                </div>
            ';
        }
    }

    // =====================================
    // OLD FORM VALUE
    // =====================================

    public static function old(
        $key,
        $default = ''
    )
    {
        return $_POST[$key]
        ?? $default;
    }

    // =====================================
    // ACTIVE MENU CHECK
    // =====================================

    public static function active(
        $page,
        $class = 'active'
    )
    {
        $currentPage =
        basename($_SERVER['PHP_SELF']);

        return $currentPage === $page
        ? $class
        : '';
    }

    // =====================================
    // INCLUDE HEADER
    // =====================================

    public static function header()
    {
        require
        '../app/views/layouts/header.php';
    }

    // =====================================
    // INCLUDE FOOTER
    // =====================================

    public static function footer()
    {
        require
        '../app/views/layouts/footer.php';
    }

    // =====================================
    // INCLUDE SIDEBAR
    // =====================================

    public static function sidebar()
    {
        require
        '../app/views/layouts/sidebar.php';
    }

    // =====================================
    // INCLUDE NAVBAR
    // =====================================

    public static function navbar()
    {
        require
        '../app/views/layouts/navbar.php';
    }
}
?>