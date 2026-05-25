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
| CREATE SESSIONS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS admin_sessions (

        id INT AUTO_INCREMENT PRIMARY KEY,

        admin_id INT NOT NULL,

        admin_name VARCHAR(255) NOT NULL,

        session_token VARCHAR(255) NOT NULL,

        ip_address VARCHAR(100) NOT NULL,

        device_name VARCHAR(255) NOT NULL,

        browser VARCHAR(255) NOT NULL,

        operating_system VARCHAR(255) NOT NULL,

        login_time TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

        last_activity TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

        status ENUM('active','expired')
        NOT NULL DEFAULT 'active'

    )
    "
);

/*
|--------------------------------------------------------------------------
| INSERT DEMO SESSIONS
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM admin_sessions
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $stmt =
        $conn->prepare(
            "
            INSERT INTO admin_sessions
            (

                admin_id,
                admin_name,
                session_token,
                ip_address,
                device_name,
                browser,
                operating_system,
                status

            )

            VALUES

            (?, ?, ?, ?, ?, ?, ?, ?)
            "
        );

    if ($stmt) {

        $adminId =
            1;

        $adminName =
            'Admin';

        $sessionToken =
            bin2hex(random_bytes(16));

        $ipAddress =
            '127.0.0.1';

        $deviceName =
            'Desktop';

        $browser =
            'Google Chrome';

        $os =
            'Windows 11';

        $status =
            'active';

        $stmt->bind_param(
            'isssssss',
            $adminId,
            $adminName,
            $sessionToken,
            $ipAddress,
            $deviceName,
            $browser,
            $os,
            $status
        );

        $stmt->execute();
        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| TERMINATE SESSION
|--------------------------------------------------------------------------
*/

if (isset($_GET['terminate'])) {

    $sessionId =
        (int) $_GET['terminate'];

    $stmt =
        $conn->prepare(
            "
            UPDATE admin_sessions
            SET status = 'expired'
            WHERE id = ?
            "
        );

    if ($stmt) {

        $stmt->bind_param(
            'i',
            $sessionId
        );

        $stmt->execute();

        $stmt->close();
    }

    header(
        'Location: sessions.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| TERMINATE ALL SESSIONS
|--------------------------------------------------------------------------
*/

if (isset($_POST['terminate_all'])) {

    $conn->query(
        "
        UPDATE admin_sessions
        SET status = 'expired'
        "
    );

    header(
        'Location: sessions.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH SESSIONS
|--------------------------------------------------------------------------
*/

$sessions =
    $conn->query(
        "
        SELECT *
        FROM admin_sessions
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
        Admin Sessions
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

        .terminate-all{

            background:#dc3545;

            color:#fff;

            border:none;

            padding:12px 20px;

            border-radius:10px;

            font-size:14px;

            font-weight:bold;

            cursor:pointer;
        }

        .terminate-all:hover{

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

        .active{

            background:#d4edda;

            color:#155724;
        }

        .expired{

            background:#f8d7da;

            color:#721c24;
        }

        .terminate-btn{

            display:inline-block;

            background:#dc3545;

            color:#fff;

            padding:8px 12px;

            border-radius:8px;

            text-decoration:none;

            font-size:13px;

            font-weight:bold;
        }

        .terminate-btn:hover{

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
            Admin Sessions
        </h1>

        <form method="POST">

            <button
                type="submit"
                name="terminate_all"
                class="terminate-all"
                onclick="return confirm('Terminate all sessions?')"
            >
                Terminate All Sessions
            </button>

        </form>

    </div>

    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Admin</th>

                <th>IP Address</th>

                <th>Device</th>

                <th>Browser</th>

                <th>OS</th>

                <th>Login Time</th>

                <th>Last Activity</th>

                <th>Status</th>

                <th>Action</th>

            </tr>

        </thead>

        <tbody>

        <?php if ($sessions && $sessions->num_rows > 0): ?>

            <?php while ($row = $sessions->fetch_assoc()): ?>

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
                                (string)$row['ip_address']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['device_name']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['browser']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['operating_system']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['login_time']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['last_activity']
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

                        <?php if ($row['status'] === 'active'): ?>

                            <a
                                href="?terminate=<?php echo (int)$row['id']; ?>"
                                class="terminate-btn"
                                onclick="return confirm('Terminate this session?')"
                            >
                                Terminate
                            </a>

                        <?php else: ?>

                            Expired

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="10"
                    class="empty"
                >

                    No active sessions found.

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