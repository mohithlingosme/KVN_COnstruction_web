<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SECURITY - LOGIN ATTEMPTS
|--------------------------------------------------------------------------
| REFACTORED: All SQL delegated to SecurityAdminRepository.
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/formatter.php';

require_once '../../../includes/repositories.php';

require_once '../../../bootstrap/providers/ServiceProvider.php';

/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle =
'Login Attempts | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| REPOSITORY
|--------------------------------------------------------------------------
*/

$securityRepo = repo('SecurityAdmin');

/*
|--------------------------------------------------------------------------
| DELETE ATTEMPT
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $deleteId =
        (int) $_GET['delete'];

    if ($securityRepo) {
        $securityRepo->deleteLoginAttempt($deleteId);
    }

    header(
        'Location: login-attempts.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| CLEAR ALL ATTEMPTS
|--------------------------------------------------------------------------
*/

if (isset($_POST['clear_attempts'])) {

    if ($securityRepo) {
        $securityRepo->clearLoginAttempts();
    }

    header(
        'Location: login-attempts.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH LOGIN ATTEMPTS
|--------------------------------------------------------------------------
*/

$attempts = [];

if ($securityRepo) {
    $attempts = $securityRepo->getLoginAttempts();
}

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
        Login Attempts
    </title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{

            font-family:Arial,sans-serif;

            background:#f4f6f9;

            padding:40px;
        }

        .container{

            max-width:1400px;

            margin:auto;

            background:#fff;

            padding:35px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .top-bar{

            display:flex;

            justify-content:space-between;

            align-items:center;

            margin-bottom:30px;

            flex-wrap:wrap;

            gap:15px;
        }

        h1{

            color:#222;
        }

        .clear-btn{

            background:#dc3545;

            color:#fff;

            border:none;

            padding:12px 20px;

            border-radius:10px;

            font-size:14px;

            font-weight:bold;

            cursor:pointer;
        }

        .clear-btn:hover{

            background:#b02a37;
        }

        table{

            width:100%;

            border-collapse:collapse;
        }

        thead{

            background:#f5b400;

            color:#fff;
        }

        th,
        td{

            padding:15px;

            border-bottom:1px solid #eee;

            text-align:left;
        }

        tr:hover{

            background:#fafafa;
        }

        .badge{

            padding:8px 14px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            display:inline-block;
        }

        .success{

            background:#d4edda;

            color:#155724;
        }

        .failed{

            background:#f8d7da;

            color:#721c24;
        }

        .delete-btn{

            display:inline-block;

            background:#dc3545;

            color:#fff;

            padding:8px 12px;

            border-radius:8px;

            text-decoration:none;

            font-size:13px;

            font-weight:bold;
        }

        .delete-btn:hover{

            background:#b02a37;
        }

        .empty{

            text-align:center;

            padding:40px;

            color:#777;
        }

        .back{

            display:inline-block;

            margin-top:25px;

            text-decoration:none;

            font-weight:bold;

            color:#333;
        }

        @media(max-width:992px){

            table{

                display:block;

                overflow-x:auto;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="top-bar">

        <h1>
            Login Attempts
        </h1>

        <form method="POST">

            <button
                type="submit"
                name="clear_attempts"
                class="clear-btn"
                onclick="return confirm('Clear all login attempts?')"
            >
                Clear All Attempts
            </button>

        </form>

    </div>

    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Email</th>

                <th>IP Address</th>

                <th>Browser</th>

                <th>Status</th>

                <th>Attempted At</th>

                <th>Action</th>

            </tr>

        </thead>

        <tbody>

        <?php if (!empty($attempts)): ?>

            <?php foreach ($attempts as $row): ?>

                <tr>

                    <td>

                        <?php
                            echo (int)($row['id'] ?? 0);
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)($row['email'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)($row['ip_address'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)($row['browser'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <span
                            class="badge <?php echo htmlspecialchars((string)($row['status'] ?? '')); ?>"
                        >

                            <?php
                                echo ucfirst(
                                    htmlspecialchars(
                                        (string)($row['status'] ?? '')
                                    )
                                );
                            ?>

                        </span>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)($row['attempted_at'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <a
                            href="?delete=<?php echo (int)($row['id'] ?? 0); ?>"
                            class="delete-btn"
                            onclick="return confirm('Delete this login attempt?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="7"
                    class="empty"
                >

                    No login attempts found.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

    <a
        href="<?php echo base_url('admin/dashboard.php'); ?>"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>