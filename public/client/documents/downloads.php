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
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once '../../includes/db.php';

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
| CREATE DOWNLOADS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS client_downloads (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        document_title VARCHAR(255) NOT NULL,

        category VARCHAR(100) NOT NULL,

        file_name VARCHAR(255) NOT NULL,

        file_type VARCHAR(50) NOT NULL,

        file_size VARCHAR(50) NOT NULL,

        total_downloads INT
        DEFAULT 0,

        last_downloaded DATETIME
        DEFAULT NULL,

        status ENUM(
            'Available',
            'Restricted',
            'Expired'
        )
        NOT NULL DEFAULT 'Available',

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
        FROM client_downloads
        WHERE client_id = $clientId
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO client_downloads
        (

            client_id,
            document_title,
            category,
            file_name,
            file_type,
            file_size,
            total_downloads,
            last_downloaded,
            status

        )

        VALUES

        (
            $clientId,
            'Villa Construction Agreement',
            'Agreement',
            'villa-agreement.pdf',
            'PDF',
            '2.4 MB',
            5,
            NOW
            (),
            'Available'
        ),
        