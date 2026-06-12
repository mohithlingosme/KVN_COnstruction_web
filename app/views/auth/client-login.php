<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| CLIENT LOGIN PAGE
|--------------------------------------------------------------------------
| File: /public/login.php
|--------------------------------------------------------------------------
*/

require_once '../config/app.php';
require_once '../helpers/security.php';
require_once '../helpers/session.php';
require_once '../helpers/csrf.php';
require_once '../middleware/guest.php';

/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle = 'Client Login | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

unset($_SESSION['error']);
unset($_SESSION['success']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo escape($pageTitle); ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body{
            background:#f5f7fa;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:30px 15px;
        }

        .login-card{
            width:100%;
            max-width:520px;
            background:#ffffff;
            border:none;
            border-radius:20px;
            box-shadow:0 15px 40px rgba(0,0,0,0.08);
            overflow:hidden;
        }

        .login-header{
            text-align:center;
            padding:40px 30px 20px;
        }

        .login-header h1{
            font-size:34px;
            font-weight:800;
            margin-bottom:10px;
            color:#111827;
        }

        .login-header p{
            color:#6b7280;
            margin:0;
            line-height:1.6;
        }

        .login-body{
            padding:0 30px 35px;
        }

        .form-label{
            font-weight:700;
            color:#111827;
        }

        .input-group-text{
            background:#f3f4f6;
            font-weight:700;
            border-radius:12px 0 0 12px;
        }

        .form-control{
            height:52px;
            border-radius:0 12px 12px 0;
        }

        .btn-login{
            background:#f5b400;
            border:none;
            color:#111827;
            font-weight:700;
            height:52px;
            border-radius:12px;
        }

        .btn-login:hover{
            background:#e0a400;
        }

        .alert{
            border-radius:12px;
        }

        .login-note{
            text-align:center;
            margin-top:20px;
            color:#6b7280;
            font-size:14px;
        }

        .register-link{
            text-align:center;
            margin-top:20px;
        }

        .register-link a{
            text-decoration:none;
            font-weight:700;
            color:#f5b400;
        }

        .register-link a:hover{
            color:#d89c00;
        }

        @media(max-width:576px){

            .login-header h1{
                font-size:28px;
            }

            .login-body{
                padding:0 20px 25px;
            }
        }

    </style>

</head>

<body>

<div class="login-card">

    <div class="login-header">

        <h1>Welcome Back</h1>

        <p>
            Login to your KVN Construction account
            using OTP verification.
        </p>

    </div>

    <div class="login-body">

        <?php if($error): ?>

            <div class="alert alert-danger">

                <?php echo escape($error); ?>

            </div>

        <?php endif; ?>

        <?php if($success): ?>

            <div class="alert alert-success">

                <?php echo escape($success); ?>

            </div>

        <?php endif; ?>

        <form
            action="auth/phone-login-handler.php"
            method="POST"
            autocomplete="off"
        >

            <?php echo csrfField(); ?>

            <div class="mb-4">

                <label class="form-label">

                    Mobile Number

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        +91

                    </span>

                    <input
                        type="tel"
                        name="phone"
                        class="form-control"
                        placeholder="9876543210"
                        pattern="[6-9][0-9]{9}"
                        maxlength="10"
                        minlength="10"
                        required
                    >

                </div>

            </div>

            <button
                type="submit"
                class="btn btn-login w-100"
            >

                <i class="bi bi-send-fill me-2"></i>

                Send OTP

            </button>

        </form>

        <div class="login-note">

            OTP will be sent to your registered
            mobile number.

        </div>

        <div class="register-link">

            New Customer?

            <a href="register.php">

                Create Account

            </a>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>