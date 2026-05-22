<?php

class Controller
{
    // =====================================
    // LOAD VIEW
    // =====================================

    protected function view($view, $data = [])
    {
        // EXTRACT DATA TO VARIABLES

        extract($data);

        // VIEW PATH

        $viewPath =
        '../app/views/' . $view . '.php';

        // CHECK VIEW EXISTS

        if(file_exists($viewPath)){

            require_once $viewPath;

        }else{

            die(

                "View file not found: " .

                $viewPath
            );
        }
    }

    // =====================================
    // LOAD MODEL
    // =====================================

    protected function model($model)
    {
        $modelPath =
        '../app/models/' . $model . '.php';

        if(file_exists($modelPath)){

            require_once $modelPath;

            return new $model();

        }else{

            die(

                "Model file not found: " .

                $modelPath
            );
        }
    }

    // =====================================
    // REDIRECT
    // =====================================

    protected function redirect($path)
    {
        header(

            'Location: ' .

            base_url($path)
        );

        exit;
    }

    // =====================================
    // JSON RESPONSE
    // =====================================

    protected function json($data = [], $status = 200)
    {
        http_response_code($status);

        header(
            'Content-Type: application/json'
        );

        echo json_encode($data);

        exit;
    }

    // =====================================
    // SANITIZE INPUT
    // =====================================

    protected function sanitize($input)
    {
        if(is_array($input)){

            return array_map(

                [$this, 'sanitize'],

                $input
            );
        }

        return htmlspecialchars(

            trim($input),

            ENT_QUOTES,

            'UTF-8'
        );
    }

    // =====================================
    // VALIDATE REQUIRED FIELDS
    // =====================================

    protected function validateRequired(
        $fields = [],
        $data = []
    )
    {
        $errors = [];

        foreach($fields as $field){

            if(
                !isset($data[$field]) ||

                empty(trim($data[$field]))
            ){

                $errors[$field] =
                ucfirst($field) .

                ' is required.';
            }
        }

        return $errors;
    }

    // =====================================
    // FLASH MESSAGE
    // =====================================

    protected function setFlash(
        $type,
        $message
    )
    {
        $_SESSION['flash'] = [

            'type' => $type,

            'message' => $message
        ];
    }

    // =====================================
    // GET FLASH MESSAGE
    // =====================================

    protected function getFlash()
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
    // CHECK REQUEST METHOD
    // =====================================

    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD']
        === 'POST';
    }

    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD']
        === 'GET';
    }

    // =====================================
    // REQUEST INPUT
    // =====================================

    protected function input($key, $default = null)
    {
        return $_POST[$key]
        ?? $_GET[$key]
        ?? $default;
    }

    // =====================================
    // FILE UPLOAD CHECK
    // =====================================

    protected function hasFile($key)
    {
        return isset($_FILES[$key]) &&

        $_FILES[$key]['error'] === 0;
    }

    // =====================================
    // ABORT REQUEST
    // =====================================

    protected function abort(
        $message = 'Access Denied',
        $status = 403
    )
    {
        http_response_code($status);

        die($message);
    }

    // =====================================
    // AUTH CHECK
    // =====================================

    protected function auth()
    {
        if(!isset($_SESSION['user_id'])){

            $this->redirect(
                'public/login.php'
            );
        }
    }

    // =====================================
    // ADMIN CHECK
    // =====================================

    protected function admin()
    {
        $this->auth();

        if(
            $_SESSION['user_role']
            !== 'admin'
        ){

            $this->abort(
                'Unauthorized Access'
            );
        }
    }

    // =====================================
    // CLIENT CHECK
    // =====================================

    protected function client()
    {
        $this->auth();

        if(
            $_SESSION['user_role']
            !== 'client'
        ){

            $this->abort(
                'Unauthorized Access'
            );
        }
    }
}
?>