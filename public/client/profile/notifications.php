<?php

declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['client_id'])) {

    header('Location: ../login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

/*
|--------------------------------------------------------------------------
| CREATE TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_notifications (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        title VARCHAR(255) NOT NULL,

        message TEXT NOT NULL,

        type ENUM(
            'Project',
            'Payment',
            'Support',
            'General'
        )
        NOT NULL DEFAULT 'General',

        is_read ENUM(
            'Yes',
            'No'
        )
        NOT NULL DEFAULT 'No',

        created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

    )
    "
);

/*
|--------------------------------------------------------------------------
| CLIENT DETAILS
|--------------------------------------------------------------------------
*/

$clientId =
    (int) $_SESSION['client_id'];

$clientName =
    $_SESSION['client_name'] ?? 'Client';

/*
|--------------------------------------------------------------------------
| INSERT DEMO NOTIFICATIONS
|--------------------------------------------------------------------------
*/

$checkNotifications =
    $conn->query(
        "
        SELECT id
        FROM client_notifications
        WHERE client_id = $clientId
        LIMIT 1
        "
    );

if (
    $checkNotifications &&
    $checkNotifications->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO client_notifications
        (

            client_id,
            title,
            message,
            type,
            is_read

        )

        VALUES

        (
           