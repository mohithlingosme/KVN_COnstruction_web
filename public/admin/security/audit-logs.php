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
| CREATE AUDIT LOGS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS audit_logs (

        id INT AUTO_INCREMENT PRIMARY KEY,

        admin_id INT DEFAULT NULL,

        admin_name VARCHAR(255) NOT NULL,

        module_name VARCHAR(255) NOT NULL,

        action_performed VARCHAR(255) NOT NULL,

        affected_record VARCHAR(255) NOT NULL,

        ip_address VARCHAR(100) NOT NULL,

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

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
        FROM audit_logs
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO audit_logs
        (

            admin_id,
            admin_name,
            module_name,
            action_performed,
            affected_record,
            ip_address

        )

        VALUES

        (
            1,
            'Admin',
            'Services',
            'Created New Service',
            'Premium Villa Construction',
            '127.0.0.1'
        ),

        (
            1,
            'Admin',
            'Portfolio',
            'Updated Portfolio Item',
            'Luxury Duplex Project',
            '127.0.0.1'
        ),

        (
            1,
            'Admin',
            'Testimonials',
            'Deleted Testimonial',
            'Client Review #5',
            '192.168.1.5'
        ),

        (
            1,
            'Admin',
            'CMS',
            'Updated Homepage Content',
            'Homepage Hero Section',
            '10.0.0.2'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| DELETE LOG
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $deleteId =
        (int) $_GET['delete'];

    $stmt =
        $conn->prepare(
            "
            DELETE FROM audit_logs
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
        'Location: audit-logs.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| CLEAR ALL LOGS
|--------------------------------------------------------------------------
*/

if (isset($_POST['clear_logs'])) {

    $conn->query(
        "
        TRUNCATE TABLE audit_logs
        "
    );

    header(
        'Location: audit-logs.php'
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
        FROM audit_logs
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
        Audit Logs
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

            max-width:1450px;

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

        .module{

            background:#f5b400;

            color:#fff;

            padding:8px 12px;

            border-radius:30px;

            font-size:12px;

            font-weight:bold;

            display:inline-block;
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
            Audit Logs
        </h1>

        <form method="POST">

            <button
                type="submit"
                name="clear_logs"
                class="clear-btn"
                onclick="return confirm('Clear all audit logs?')"
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

                <th>Module</th>

                <th>Action</th>

                <th>Affected Record</th>

                <th>IP Address</th>

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

                        <span class="module">

                            <?php
                                echo htmlspecialchars(
                                    (string)$row['module_name']
                                );
                            ?>

                        </span>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['action_performed']
                            );
                        ?>

                    </td>

                    <td>

                        <?php
                            echo htmlspecialchars(
                                (string)$row['affected_record']
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

                    No audit logs found.

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