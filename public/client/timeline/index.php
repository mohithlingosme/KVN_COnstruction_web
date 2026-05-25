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
| CREATE TABLES
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS project_timelines (

        id INT AUTO_INCREMENT PRIMARY KEY,

        client_id INT NOT NULL,

        project_name VARCHAR(255) NOT NULL,

        milestone_title VARCHAR(255) NOT NULL,

        description TEXT DEFAULT NULL,

        status ENUM(
            'Pending',
            'In Progress',
            'Completed',
            'Delayed'
        )
        NOT NULL DEFAULT 'Pending',

        progress INT NOT NULL DEFAULT 0,

        start_date DATE DEFAULT NULL,

        end_date DATE DEFAULT NULL,

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
| INSERT DEMO DATA
|--------------------------------------------------------------------------
*/

$check =
    $conn->query(
        "
        SELECT id
        FROM project_timelines
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
        INSERT INTO project_timelines
        (

            client_id,
            project_name,
            milestone_title,
            description,
            status,
            progress,
            start_date,
            end_date

        )

        VALUES

        (
            $clientId,
            'Luxury Villa Construction',
            'Site Preparation',
            'Initial land cleaning and foundation preparation completed.',
            'Completed',
            100,
            '2026-05-01',
            '2026-05-08'
        ),

        (
            $clientId,
            'Luxury Villa Construction