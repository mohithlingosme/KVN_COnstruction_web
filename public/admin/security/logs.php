<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SECURITY - SECURITY LOGS
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
'Security Logs | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| REPOSITORY
|--------------------------------------------------------------------------
*/

$securityRepo = repo('SecurityAdmin');

/*
|--------------------------------------------------------------------------
| DELETE LOG
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $deleteId =
        (int) $_GET['delete'];

    if ($securityRepo) {
        $securityRepo->deleteSecurityLog($deleteId);
    }

    header(
        'Location: logs.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| CLEAR ALL LOGS
|--------------------------------------------------------------------------
*/

if (isset($_POST['clear_logs'])) {

    if ($securityRepo) {
        $securityRepo->clearSecurityLogs();
    }

    header(
        'Location: logs.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH LOGS
|--------------------------------------------------------------------------
*/

$logs = [];

if ($securityRepo) {
    $logs = $securityRepo->getSecurityLogs();
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
        Security Logs
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

            vertical-align:top;
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

        .warning{

            background:#fff3cd;

            color:#856404;
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
            Security Logs
        </h1>

        <form method="POST">

            <button
                type="submit"
                name="clear_logs"
                class="clear-btn"
                onclick="return confirm('Are you sure you want to clear all logs?')"
            >
                Clear All Logs
            </button>

        </form>

    </div>

    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Admin</th>

                <th>Action</th>

                <th>IP Address</th>

                <th>User Agent</th>

                <th>Status</th>

                <th>Date</th>

                <th>Action</th>

            </tr>

        </thead>

        <tbody>

        <?php if (!empty($logs)): ?>

            <?php foreach ($logs as $row): ?>

                <tr>

                    <td>

                        <?php
                            echo (int)($row['id'] ?? 0);
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)($row['admin_name'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)($row['action_type'] ?? '')
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
                                (string)($row['user_agent'] ?? '')
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
                                (string)($row['created_at'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <a
                            href="?delete=<?php echo (int)($row['id'] ?? 0); ?>"
                            class="delete-btn"
                            onclick="return confirm('Delete this log?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="8"
                    class="empty"
                >

                    No security logs found.

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