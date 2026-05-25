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
| CREATE LEADS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS leads (

        id INT AUTO_INCREMENT PRIMARY KEY,

        full_name VARCHAR(255) NOT NULL,

        phone VARCHAR(50) NOT NULL,

        email VARCHAR(255) NOT NULL,

        project_type VARCHAR(255) NOT NULL,

        project_location VARCHAR(255) NOT NULL,

        budget VARCHAR(100) NOT NULL,

        lead_source VARCHAR(255) NOT NULL,

        status ENUM('New','Contacted','Qualified','Closed')
        NOT NULL DEFAULT 'New',

        notes TEXT DEFAULT NULL,

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
        FROM leads
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO leads
        (

            full_name,
            phone,
            email,
            project_type,
            project_location,
            budget,
            lead_source,
            status,
            notes

        )

        VALUES

        (
            'Rahul Sharma',
            '9876543210',
            'rahul@gmail.com',
            'Luxury Villa',
            'Bangalore',
            '₹80 Lakhs',
            'Website',
            'New',
            'Interested in turnkey construction.'
        ),

        (
            'Sneha Reddy',
            '9988776655',
            'sneha@gmail.com',
            'Duplex House',
            'Hyderabad',
            '₹1.2 Crore',
            'Instagram',
            'Contacted',
            'Requested call back next week.'
        ),

        (
            'Arjun Kumar',
            '9123456789',
            'arjun@gmail.com',
            'Commercial Building',
            'Chennai',
            '₹3 Crore',
            'Referral',
            'Qualified',
            'Ready for project discussion.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| ADD LEAD
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_lead'])
) {

    $fullName =
        trim($_POST['full_name'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $projectType =
        trim($_POST['project_type'] ?? '');

    $projectLocation =
        trim($_POST['project_location'] ?? '');

    $budget =
        trim($_POST['budget'] ?? '');

    $leadSource =
        trim($_POST['lead_source'] ?? '');

    $status =
        trim($_POST['status'] ?? '');

    $notes =
        trim($_POST['notes'] ?? '');

    if (
        $fullName === '' ||
        $phone === '' ||
        $email === '' ||
        $projectType === '' ||
        $projectLocation === '' ||
        $budget === '' ||
        $leadSource === '' ||
        $status === ''
    ) {

        $error =
            'Please fill all required fields.';
    }

    if ($error === '') {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO leads
                (

                    full_name,
                    phone,
                    email,
                    project_type,
                    project_location,
                    budget,
                    lead_source,
                    status,
                    notes

                )

                VALUES

                (?, ?, ?, ?, ?, ?, ?, ?, ?)
                "
            );

        if ($stmt) {

            $stmt->bind_param(
                'sssssssss',
                $fullName,
                $phone,
                $email,
                $projectType,
                $projectLocation,
                $budget,
                $leadSource,
                $status,
                $notes
            );

            $stmt->execute();

            $stmt->close();

            $success =
                'Lead added successfully.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE LEAD
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    $stmt =
        $conn->prepare(
            "
            DELETE FROM leads
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
        'Location: leads.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH LEADS
|--------------------------------------------------------------------------
*/

$leads =
    $conn->query(
        "
        SELECT *
        FROM leads
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| LEAD COUNTS
|--------------------------------------------------------------------------
*/

$totalLeads      = 0;
$newLeads        = 0;
$contactedLeads  = 0;
$qualifiedLeads  = 0;
$closedLeads     = 0;

if ($leads && $leads->num_rows > 0) {

    while ($calc = $leads->fetch_assoc()) {

        $totalLeads++;

        if ($calc['status'] === 'New') {

            $newLeads++;
        }

        if ($calc['status'] === 'Contacted') {

            $contactedLeads++;
        }

        if ($calc['status'] === 'Qualified') {

            $qualifiedLeads++;
        }

        if ($calc['status'] === 'Closed') {

            $closedLeads++;
        }
    }

    $leads->data_seek(0);
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
        Leads Report
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

            max-width:1500px;

            margin:auto;
        }

        h1{

            margin-bottom:30px;

            color:#222;
        }

        .stats{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(220px,1fr));

            gap:20px;

            margin-bottom:35px;
        }

        .card{

            background:#fff;

            padding:25px;

            border-radius:18px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }

        .card h3{

            color:#666;

            margin-bottom:10px;

            font-size:15px;
        }

        .card h2{

            color:#111;
        }

        .form-box{

            background:#fff;

            padding:30px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);

            margin-bottom:35px;
        }

        .grid{

            display:grid;

            grid-template-columns:
                repeat(auto-fit,minmax(250px,1fr));

            gap:20px;
        }

        .form-group{

            margin-bottom:20px;
        }

        label{

            display:block;

            margin-bottom:8px;

            font-weight:bold;
        }

        input,
        select,
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

            background:#f5b400;

            color:#fff;

            border:none;

            padding:14px 20px;

            border-radius:10px;

            font-size:15px;

            font-weight:bold;

            cursor:pointer;
        }

        button:hover{

            opacity:0.9;
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

        .table-box{

            background:#fff;

            padding:30px;

            border-radius:20px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
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

        .New{

            background:#d1ecf1;

            color:#0c5460;
        }

        .Contacted{

            background:#fff3cd;

            color:#856404;
        }

        .Qualified{

            background:#d4edda;

            color:#155724;
        }

        .Closed{

            background:#f8d7da;

            color:#721c24;
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

        .back{

            display:inline-block;

            margin-top:25px;

            text-decoration:none;

            font-weight:bold;

            color:#333;
        }

        .empty{

            text-align:center;

            padding:40px;

            color:#777;
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
        Leads Report
    </h1>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h3>Total Leads</h3>

            <h2>
                <?php echo $totalLeads; ?>
            </h2>

        </div>

        <div class="card">

            <h3>New Leads</h3>

            <h2>
                <?php echo $newLeads; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Contacted</h3>

            <h2>
                <?php echo $contactedLeads; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Qualified</h3>

            <h2>
                <?php echo $qualifiedLeads; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Closed</h3>

            <h2>
                <?php echo $closedLeads; ?>
            </h2>

        </div>

    </div>

    <!-- ALERTS -->

    <?php if ($success !== ''): ?>

        <div class="alert success">

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="alert error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>

    <!-- FORM -->

    <div class="form-box">

        <form method="POST">

            <div class="grid">

                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Project Type
                    </label>

                    <input
                        type="text"
                        name="project_type"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Project Location
                    </label>

                    <input
                        type="text"
                        name="project_location"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Budget
                    </label>

                    <input
                        type="text"
                        name="budget"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Lead Source
                    </label>

                    <input
                        type="text"
                        name="lead_source"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select
                        name="status"
                        required
                    >

                        <option value="New">
                            New
                        </option>

                        <option value="Contacted">
                            Contacted
                        </option>

                        <option value="Qualified">
                            Qualified
                        </option>

                        <option value="Closed">
                            Closed
                        </option>

                    </select>

                </div>

            </div>

            <div class="form-group">

                <label>
                    Notes
                </label>

                <textarea
                    name="notes"
                ></textarea>

            </div>

            <button
                type="submit"
                name="add_lead"
            >
                Add Lead
            </button>

        </form>

    </div>

    <!-- TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Phone</th>

                    <th>Email</th>

                    <th>Project</th>

                    <th>Location</th>

                    <th>Budget</th>

                    <th>Source</th>

                    <th>Status</th>

                    <th>Created</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($leads && $leads->num_rows > 0): ?>

                <?php while ($row = $leads->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo (int)$row['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['full_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['phone']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['email']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_type']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_location']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['budget']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['lead_source']); ?>
                        </td>

                        <td>

                            <span
                                class="badge <?php echo htmlspecialchars((string)$row['status']); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['created_at']); ?>
                        </td>

                        <td>

                            <a
                                href="?delete=<?php echo (int)$row['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this lead?')"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="11"
                        class="empty"
                    >

                        No leads found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <a
        href="../dashboard.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>