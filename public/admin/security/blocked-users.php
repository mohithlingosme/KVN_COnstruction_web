<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| SECURITY - BLOCKED USERS
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
'Blocked Users | ' . APP_NAME;

/*
|--------------------------------------------------------------------------
| REPOSITORY
|--------------------------------------------------------------------------
*/

$securityRepo = repo('SecurityAdmin');

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

    if ($error === '' && $securityRepo) {

        $ok = $securityRepo->insertBlockedUser([
            'email'     => $email,
            'ip_address'=> $ipAddress,
            'reason'    => $reason,
            'status'    => 'blocked',
        ]);

        if ($ok) {
            $success =
                'User blocked successfully.';
        } else {
            $error =
                'Failed to block user.';
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

    if ($securityRepo) {
        $securityRepo->unblockUser($id);
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

    if ($securityRepo) {
        $securityRepo->deleteBlockedUser($id);
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

$users = [];

if ($securityRepo) {
    $users = $securityRepo->getBlockedUsers();
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

        <?php if (!empty($users)): ?>

            <?php foreach ($users as $row): ?>

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
                                (string)($row['reason'] ?? '')
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
                                (string)($row['blocked_at'] ?? '')
                            );
                        ?>

                    </td>

                    <td>

                        <?php if (($row['status'] ?? '') === 'blocked'): ?>

                            <a
                                href="?unblock=<?php echo (int)($row['id'] ?? 0); ?>"
                                class="action-btn unblock"
                                onclick="return confirm('Unblock this user?')"
                            >
                                Unblock
                            </a>

                        <?php endif; ?>

                        <a
                            href="?delete=<?php echo (int)($row['id'] ?? 0); ?>"
                            class="action-btn delete"
                            onclick="return confirm('Delete this record?')"
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

                    No blocked users found.

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