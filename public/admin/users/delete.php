<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
| File:
| /public/admin/users/delete.php
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';

require_once '../../../middleware/admin.php';

require_once '../../../helpers/security.php';

require_once '../../../helpers/session.php';

require_once '../../../helpers/rateLimiter.php';

/*
|--------------------------------------------------------------------------
| VALIDATE USER ID
|--------------------------------------------------------------------------
*/

$userId =
(int) ($_GET['id'] ?? 0);

if ($userId <= 0) {

    $_SESSION['error'] =
    'Invalid user ID.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| PREVENT SELF DELETE
|--------------------------------------------------------------------------
*/

if ($userId == currentUserId()) {

    $_SESSION['error'] =
    'You cannot delete your own account.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| RATE LIMIT
|--------------------------------------------------------------------------
*/

if (

    !checkRateLimit(

        'delete_user',

        10,

        300
    )
) {

    $_SESSION['error'] =
    'Too many requests. Please try again later.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| FETCH USER
|--------------------------------------------------------------------------
*/

$query = "

    SELECT *

    FROM users

    WHERE id = :id

    LIMIT 1
";

$stmt =
$conn->prepare($query);

$stmt->execute([

    ':id' => $userId
]);

$user =
$stmt->fetch();

if (!$user) {

    $_SESSION['error'] =
    'User not found.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| PREVENT SUPER ADMIN DELETE
|--------------------------------------------------------------------------
|
| Optional protection
|--------------------------------------------------------------------------
*/

if (

    isset($user['role'])

    &&

    $user['role'] === 'super_admin'
) {

    $_SESSION['error'] =
    'Super admin cannot be deleted.';

    redirect('admin/users/index.php');
}

/*
|--------------------------------------------------------------------------
| DELETE PROFILE IMAGE
|--------------------------------------------------------------------------
*/

if (

    !empty($user['profile_image'])
) {

    $imagePath =

        ROOT_PATH

        .

        '/uploads/users/'

        .

        $user['profile_image'];

    if (file_exists($imagePath)) {

        @unlink($imagePath);
    }
}

/*
|--------------------------------------------------------------------------
| DELETE USER SESSIONS
|--------------------------------------------------------------------------
*/

try {

    $sessionQuery = "

        DELETE FROM user_sessions

        WHERE user_id = :user_id
    ";

    $sessionStmt =
    $conn->prepare($sessionQuery);

    $sessionStmt->execute([

        ':user_id' => $userId
    ]);

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| DELETE SECURITY LOGS
|--------------------------------------------------------------------------
*/

try {

    $logQuery = "

        DELETE FROM security_logs

        WHERE user_id = :user_id
    ";

    $logStmt =
    $conn->prepare($logQuery);

    $logStmt->execute([

        ':user_id' => $userId
    ]);

} catch(Exception $e){}

/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
*/

try {

    $deleteQuery = "

        DELETE FROM users

        WHERE id = :id

        LIMIT 1
    ";

    $deleteStmt =
    $conn->prepare($deleteQuery);

    $deleteStmt->execute([

        ':id' => $userId
    ]);

    /*
    |--------------------------------------------------------------------------
    | SECURITY LOG
    |--------------------------------------------------------------------------
    */

    logSecurityEvent(

        currentUserId(),

        'user_deleted',

        'warning',

        'Deleted user ID: ' . $userId
    );

    $_SESSION['success'] =
    'User deleted successfully.';

} catch(Exception $e) {

    $_SESSION['error'] =
    'Failed to delete user.';
}

/*
|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

redirect('admin/users/index.php');

?>