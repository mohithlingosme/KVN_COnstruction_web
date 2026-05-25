<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id'])) {

    header('Location: ../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

/*
|--------------------------------------------------------------------------
| CREATE SECURITY LOGS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS security_logs (

        id INT AUTO_INCREMENT PRIMARY KEY,

        admin_id INT DEFAULT NULL,

        admin_name VARCHAR(255) NOT NULL,

        action_type VARCHAR(255) NOT NULL,

        ip_address VARCHAR(100) NOT NULL,

        user_agent TEXT NOT NULL,

        status ENUM('success','failed','warning')
        NOT NULL DEFAULT 'success',

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEMO LOGS
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM security_logs
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO security_logs
        (

            admin_id,
            admin_name,
            action_type,
            ip_address,
            user_agent,
            status

        )

        VALUES

        (
            1,
            'Admin',
            'Admin Login',
            '127.0.0.1',
            'Chrome Browser',
            'success'
        ),

        (
            1,
            'Admin',
            'Password Changed',
            '127.0.0.1',
            'Chrome Browser',
            'warning'
        ),

        (
            0,
            'Unknown User',
            'Failed Login Attempt',
            '192.168.1.10',
            'Firefox Browser',
            'failed'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| DELETE LOG
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['delete'])
) {

    $deleteId =
        (int) $_GET['delete'];

    $stmt =
        $conn->prepare(
            "
            DELETE FROM security_logs
            WHERE id = ?
            "
        );

    if ($stmt) {

        $stmt->bind_param(
            'i',
            $deleteId
        );

        $stmt->execute();

        $stmt->close();
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

if (
    isset($_POST['clear_logs'])
) {

    $conn->query(
        "
        TRUNCATE TABLE security_logs
        "
    );

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

$logs =
    $conn->query(
        "
        SELECT *
        FROM security_logs
        ORDER BY id DESC
        "
    );

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

        <?php if ($logs && $logs->num_rows > 0): ?>

            <?php while ($row = $logs->fetch_assoc()): ?>

                <tr>

                    <td>

                        <?php
                            echo (int)$row['id'];
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['admin_name']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['action_type']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['ip_address']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['user_agent']
                            );
                        ?>

                    </td>

                    <td>

                        <span
                            class="badge <?php echo htmlspecialchars((string)$row['status']); ?>"
                        >

                            <?php
                                echo ucfirst(
                                    htmlspecialchars(
                                        (string)$row['status']
                                    )
                                );
                            ?>

                        </span>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['created_at']
                            );
                        ?>

                    </td>

                    <td>

                        <a
                            href="?delete=<?php echo (int)$row['id']; ?>"
                            class="delete-btn"
                            onclick="return confirm('Delete this log?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

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
        href="../dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>