<?php

declare(strict_types=1);

require_once '../config/app.php';
require_once '../helpers/security.php';
require_once '../helpers/session.php';
require_once '../helpers/csrf.php';
require_once '../middleware/guest.php';

$pageTitle = 'Create Account | ' . APP_NAME;
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

unset($_SESSION['error'], $_SESSION['success']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:30px 15px;
            background:#f5f7fa;
        }
        .register-card{
            width:100%;
            max-width:560px;
            background:#fff;
            border-radius:20px;
            box-shadow:0 15px 40px rgba(0,0,0,.08);
            padding:36px;
        }
        h1{
            font-size:32px;
            font-weight:800;
            color:#111827;
        }
        .text-muted{
            line-height:1.6;
        }
        .form-label{
            font-weight:700;
            color:#111827;
        }
        .form-control{
            min-height:50px;
            border-radius:12px;
        }
        .btn-register{
            min-height:52px;
            border:0;
            border-radius:12px;
            background:#f5b400;
            color:#111827;
            font-weight:800;
        }
        .btn-register:hover{
            background:#e0a400;
            color:#111827;
        }
        a{
            color:#d89c00;
            font-weight:700;
            text-decoration:none;
        }
    </style>
</head>
<body>
    <main class="register-card">
        <div class="text-center mb-4">
            <h1>Create Account</h1>
            <p class="text-muted mb-0">Register as a KVN Construction client.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo escape($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo escape($success); ?></div>
        <?php endif; ?>

        <form action="auth/register-handler.php" method="POST" autocomplete="off">
            <?php echo csrfField(); ?>

            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input class="form-control" id="full_name" name="full_name" maxlength="150" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" id="email" type="email" name="email" maxlength="150" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="phone">Mobile Number</label>
                <input class="form-control" id="phone" type="tel" name="phone" pattern="[6-9][0-9]{9}" maxlength="10" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" type="password" name="password" minlength="8" required>
                </div>

                <div class="col-md-6 mb-4">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input class="form-control" id="confirm_password" type="password" name="confirm_password" minlength="8" required>
                </div>
            </div>

            <button class="btn btn-register w-100" type="submit">
                <i class="bi bi-person-plus-fill me-2"></i>
                Create Account
            </button>
        </form>

        <p class="text-center mt-4 mb-0">
            Already registered?
            <a href="login.php">Login with OTP</a>
        </p>
    </main>
</body>
</html>
