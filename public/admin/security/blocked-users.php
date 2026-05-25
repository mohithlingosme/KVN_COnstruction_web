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
| CREATE BLOCKED USERS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS blocked_users (

        id INT AUTO_INCREMENT PRIMARY KEY,

        email VARCHAR(255) NOT NULL,

        ip_address VARCHAR(100) NOT NULL,

        reason TEXT NOT NULL,

        blocked_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

        status ENUM('blocked','unblocked')
        NOT NULL DEFAULT 'blocked'

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEMO DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM blocked_users
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO blocked_users
        (

            email,
            ip_address,
            reason,
            status

        )

        VALUES

        (
            'hacker@example.com',
            '192.168.1.100',
            'Multiple failed login attempts detected.',
            'blocked'
        ),

        (
            'spamuser@gmail.com',
            '10.0.0.5',
            'Spam activity detected.',
            'blocked'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| ADD BLOCKED USER
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_block'])
) {

    $email =
        trim($_POST['email'] ?? '');

    $ipAddress =
        trim($_POST['ip_address'] ?? '');

    $reason =
        trim($_POST['reason'] ?? '');

    if (
        $email === '' ||
        $ipAddress === '' ||
        $reason === ''
    ) {

        $error =
            'Please fill all fields.';
    }

    if ($error === '') {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO blocked_users
                (

                    email,
                    ip_address,
                    reason,
                    status

                )

                VALUES

                (?, ?, ?, 'blocked')
                "
            );

        if ($stmt) {

            $stmt->bind_param(
                'sss',
                $email,
                $ipAddress,
                $reason
            );

            $stmt->execute();

            $stmt->close();

            $success =
                'User blocked successfully.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| UNBLOCK USER
|--------------------------------------------------------------------------
*/

if (isset($_GET['unblock'])) {

    $id =
        (int) $_GET['unblock'];

    $stmt =
        $conn->prepare(
            "
            UPDATE blocked_users
            SET status = 'unblocked'
            WHERE id = ?
            "
        );

    if ($stmt) {

        $stmt->bind_param(
            'i',
            $id
        );

        $stmt->execute();

        $stmt->close();
    }

    header(
        'Location: blocked-users.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    $stmt =
        $conn->prepare(
            "
            DELETE FROM blocked_users
            WHERE id = ?
            "
        );

    if ($stmt) {

        $stmt->bind_param(
            'i',
            $id
        );

        $stmt->execute();

        $stmt->close();
    }

    header(
        'Location: blocked-users.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH BLOCKED USERS
|--------------------------------------------------------------------------
*/

$users =
    $conn->query(
        "
        SELECT *
        FROM blocked_users
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
        Blocked Users
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

        h1{

            margin-bottom:30px;

            color:#222;
        }

        .form-box{

            background:#fafafa;

            padding:25px;

            border-radius:15px;

            margin-bottom:35px;

            border:1px solid #eee;
        }

        .form-group{

            margin-bottom:20px;
        }

        label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;

            color:#333;
        }

        input,
        textarea{

            width:100%;

            padding:14px;

            border:1px solid #ddd;

            border-radius:10px;

            font-size:15px;
        }

        textarea{

            resize:vertical;

            min-height:100px;
        }

        button{

            background:#dc3545;

            color:#fff;

            border:none;

            padding:14px 20px;

            border-radius:10px;

            font-size:15px;

            font-weight:bold;

            cursor:pointer;
        }

        button:hover{

            background:#b02a37;
        }

        .alert{

            padding:15px;

            border-radius:10px;

            margin-bottom:20px;

            font-weight:bold;
        }

        .success{

            background:#d4edda;

            color:#155724;
        }

        .error{

            background:#f8d7da;

            color:#721c24;
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

        .blocked{

            background:#f8d7da;

            color:#721c24;
        }

        .unblocked{

            background:#d4edda;

            color:#155724;
        }

        .action-btn{

            display:inline-block;

            padding:8px 12px;

            border-radius:8px;

            text-decoration:none;

            font-size:13px;

            font-weight:bold;

            margin-right:8px;
        }

        .unblock{

            background:#28a745;

            color:#fff;
        }

        .delete{

            background:#dc3545;

            color:#fff;
        }

        .unblock:hover{

            background:#218838;
        }

        .delete:hover{

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

    <h1>
        Blocked Users
    </h1>

    <?php if ($success !== ''): ?>

        <div class="alert success">

            <?php
                echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="alert error">

            <?php
                echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>

    <!-- BLOCK USER FORM -->

    <div class="form-box">

        <form method="POST">

            <div class="form-group">

                <label>
                    User Email
                </label>

                <input
                    type="email"
                    name="email"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    IP Address
                </label>

                <input
                    type="text"
                    name="ip_address"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Reason
                </label>

                <textarea
                    name="reason"
                    required
                ></textarea>

            </div>

            <button
                type="submit"
                name="add_block"
            >
                Block User
            </button>

        </form>

    </div>

    <!-- TABLE -->

    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Email</th>

                <th>IP Address</th>

                <th>Reason</th>

                <th>Status</th>

                <th>Blocked At</th>

                <th>Action</th>

            </tr>

        </thead>

        <tbody>

        <?php if ($users && $users->num_rows > 0): ?>

            <?php while ($row = $users->fetch_assoc()): ?>

                <tr>

                    <td>

                        <?php
                            echo (int)$row['id'];
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['email']
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
                                (string)$row['reason']
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
                                (string)$row['blocked_at']
                            );
                        ?>

                    </td>

                    <td>

                        <?php if ($row['status'] === 'blocked'): ?>

                            <a
                                href="?unblock=<?php echo (int)$row['id']; ?>"
                                class="action-btn unblock"
                                onclick="return confirm('Unblock this user?')"
                            >
                                Unblock
                            </a>

                        <?php endif; ?>

                        <a
                            href="?delete=<?php echo (int)$row['id']; ?>"
                            class="action-btn delete"
                            onclick="return confirm('Delete this record?')"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="7"
                    class="empty"
                >

                    No blocked users found.

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