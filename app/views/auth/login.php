<?php require_once '../../config/app.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | <?php echo APP_NAME; ?></title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body {
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
        }

        .login-header {
            background: #0d6efd;
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .login-body {
            padding: 30px;
            background: #fff;
        }

        .form-control {
            height: 50px;
            border-radius: 10px;
        }

        .btn-login {
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
        }

        .brand-logo {
            font-size: 50px;
            margin-bottom: 10px;
        }

    </style>

</head>

<body>

    <div class="card login-card">

        <!-- HEADER -->
        <div class="login-header">

            <div class="brand-logo">
                <i class="bi bi-buildings-fill"></i>
            </div>

            <h2>KVN Construction</h2>

            <p class="mb-0 mt-2">
                Admin Login Portal
            </p>

        </div>

        <!-- BODY -->
        <div class="login-body">

            <!-- SUCCESS MESSAGE -->
            <?php if (isset($_SESSION['success'])): ?>

                <div class="alert alert-success">

                    <?php
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>

                </div>

            <?php endif; ?>

            <!-- ERROR MESSAGE -->
            <?php if (isset($_SESSION['error'])): ?>

                <div class="alert alert-danger">

                    <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>

                </div>

            <?php endif; ?>

            <!-- LOGIN FORM -->
            <form
                action="../../public/login-handler.php"
                method="POST"
            >

                <!-- EMAIL -->
                <div class="mb-3">

                    <label class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter email"
                        required
                    >

                </div>

                <!-- PASSWORD -->
                <div class="mb-4">

                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter password"
                        required
                    >

                </div>

                <!-- LOGIN BUTTON -->
                <button
                    type="submit"
                    class="btn btn-primary w-100 btn-login"
                >

                    <i class="bi bi-box-arrow-in-right"></i>
                    Login

                </button>

            </form>

            <!-- FOOTER -->
            <div class="text-center mt-4">

                <small class="text-muted">
                    © <?php echo date('Y'); ?>
                    <?php echo APP_NAME; ?>
                </small>

            </div>

        </div>

    </div>

</body>

</html>