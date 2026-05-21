<?php

$email = $_POST['email'];
$password = $_POST['password'];

/* DEMO LOGIN */

if($email == "admin@gmail.com"
   && $password == "admin123"){

    echo "
    <script>
        alert('Login Successful');
        window.location.href='dashboard.php';
    </script>
    ";

}else{

    echo "
    <script>
        alert('Invalid Email or Password');
        window.location.href='index.php';
    </script>
    ";
}
?>