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
| CREATE QUOTATIONS TABLE
|--------------------------------------------------------------------------
*/

$conn->query(
    "
    CREATE TABLE IF NOT EXISTS quotations (

        id INT AUTO_INCREMENT PRIMARY KEY,

        quotation_no VARCHAR(100) NOT NULL,

        client_name VARCHAR(255) NOT NULL,

        client_phone VARCHAR(50) NOT NULL,

        project_type VARCHAR(255) NOT NULL,

        project_location VARCHAR(255) NOT NULL,

        estimated_cost DECIMAL(12,2) NOT NULL,

        quotation_status ENUM(
            'Pending',
            'Approved',
            'Rejected'
        )
        NOT NULL DEFAULT 'Pending',

        valid_until DATE NOT NULL,

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
        FROM quotations
        LIMIT 1
        "
    );

if (
    $check &&
    $check->num_rows === 0
) {

    $conn->query(
        "
        INSERT INTO quotations
        (

            quotation_no,
            client_name,
            client_phone,
            project_type,
            project_location,
            estimated_cost,
            quotation_status,
            valid_until,
            notes

        )

        VALUES

        (
            'KVN-QT-1001',
            'Rahul Sharma',
            '9876543210',
            'Luxury Villa',
            'Bangalore',
            8500000,
            'Approved',
            '2026-07-15',
            'Turnkey construction package.'
        ),

        (
            'KVN-QT-1002',
            'Sneha Reddy',
            '9988776655',
            'Modern Duplex',
            'Hyderabad',
            6200000,
            'Pending',
            '2026-06-30',
            'Interior excluded from package.'
        ),

        (
            'KVN-QT-1003',
            'TechBuild Pvt Ltd',
            '9123456780',
            'Commercial Complex',
            'Chennai',
            25000000,
            'Rejected',
            '2026-05-28',
            'Client requested revised costing.'
        )
        "
    );
}

/*
|--------------------------------------------------------------------------
| ADD QUOTATION
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_quotation'])
) {

    $quotationNo =
        trim($_POST['quotation_no'] ?? '');

    $clientName =
        trim($_POST['client_name'] ?? '');

    $clientPhone =
        trim($_POST['client_phone'] ?? '');

    $projectType =
        trim($_POST['project_type'] ?? '');

    $projectLocation =
        trim($_POST['project_location'] ?? '');

    $estimatedCost =
        trim($_POST['estimated_cost'] ?? '');

    $quotationStatus =
        trim($_POST['quotation_status'] ?? '');

    $validUntil =
        trim($_POST['valid_until'] ?? '');

    $notes =
        trim($_POST['notes'] ?? '');

    if (
        $quotationNo === '' ||
        $clientName === '' ||
        $clientPhone === '' ||
        $projectType === '' ||
        $projectLocation === '' ||
        $estimatedCost === '' ||
        $quotationStatus === '' ||
        $validUntil === ''
    ) {

        $error =
            'Please fill all required fields.';
    }

    if ($error === '') {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO quotations
                (

                    quotation_no,
                    client_name,
                    client_phone,
                    project_type,
                    project_location,
                    estimated_cost,
                    quotation_status,
                    valid_until,
                    notes

                )

                VALUES

                (?, ?, ?, ?, ?, ?, ?, ?, ?)
                "
            );

        if ($stmt) {

            $stmt->bind_param(
                'sssssisss',
                $quotationNo,
                $clientName,
                $clientPhone,
                $projectType,
                $projectLocation,
                $estimatedCost,
                $quotationStatus,
                $validUntil,
                $notes
            );

            $stmt->execute();

            $stmt->close();

            $success =
                'Quotation added successfully.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| DELETE QUOTATION
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id =
        (int) $_GET['delete'];

    $stmt =
        $conn->prepare(
            "
            DELETE FROM quotations
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
        'Location: quotations.php'
    );

    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATIONS
|--------------------------------------------------------------------------
*/

$quotations =
    $conn->query(
        "
        SELECT *
        FROM quotations
        ORDER BY id DESC
        "
    );

/*
|--------------------------------------------------------------------------
| STATS
|--------------------------------------------------------------------------
*/

$totalQuotations   = 0;
$approvedQuotes    = 0;
$pendingQuotes     = 0;
$rejectedQuotes    = 0;
$totalQuotationAmt = 0;

if ($quotations && $quotations->num_rows > 0) {

    while ($calc = $quotations->fetch_assoc()) {

        $totalQuotations++;

        $totalQuotationAmt +=
            (float)$calc['estimated_cost'];

        if (
            $calc['quotation_status']
            === 'Approved'
        ) {

            $approvedQuotes++;
        }

        if (
            $calc['quotation_status']
            === 'Pending'
        ) {

            $pendingQuotes++;
        }

        if (
            $calc['quotation_status']
            === 'Rejected'
        ) {

            $rejectedQuotes++;
        }
    }

    $quotations->data_seek(0);
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
        Quotations Report
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

            max-width:1600px;

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

        .Approved{

            background:#d4edda;

            color:#155724;
        }

        .Pending{

            background:#fff3cd;

            color:#856404;
        }

        .Rejected{

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
        Quotations Report
    </h1>

    <!-- STATS -->

    <div class="stats">

        <div class="card">

            <h3>Total Quotations</h3>

            <h2>
                <?php echo $totalQuotations; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Approved</h3>

            <h2>
                <?php echo $approvedQuotes; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Pending</h3>

            <h2>
                <?php echo $pendingQuotes; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Rejected</h3>

            <h2>
                <?php echo $rejectedQuotes; ?>
            </h2>

        </div>

        <div class="card">

            <h3>Total Quotation Value</h3>

            <h2>
                ₹<?php
                    echo number_format(
                        $totalQuotationAmt,
                        2
                    );
                ?>
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
                        Quotation Number
                    </label>

                    <input
                        type="text"
                        name="quotation_no"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Client Name
                    </label>

                    <input
                        type="text"
                        name="client_name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Client Phone
                    </label>

                    <input
                        type="text"
                        name="client_phone"
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
                        Estimated Cost
                    </label>

                    <input
                        type="number"
                        name="estimated_cost"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select
                        name="quotation_status"
                        required
                    >

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Approved">
                            Approved
                        </option>

                        <option value="Rejected">
                            Rejected
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Valid Until
                    </label>

                    <input
                        type="date"
                        name="valid_until"
                        required
                    >

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
                name="add_quotation"
            >
                Add Quotation
            </button>

        </form>

    </div>

    <!-- TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Quotation No</th>

                    <th>Client</th>

                    <th>Phone</th>

                    <th>Project Type</th>

                    <th>Location</th>

                    <th>Estimated Cost</th>

                    <th>Status</th>

                    <th>Valid Until</th>

                    <th>Created</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($quotations && $quotations->num_rows > 0): ?>

                <?php while ($row = $quotations->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo (int)$row['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['quotation_no']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['client_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['client_phone']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_type']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['project_location']); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format((float)$row['estimated_cost'], 2); ?>
                        </td>

                        <td>

                            <span
                                class="badge <?php echo htmlspecialchars((string)$row['quotation_status']); ?>"
                            >

                                <?php
                                    echo htmlspecialchars(
                                        (string)$row['quotation_status']
                                    );
                                ?>

                            </span>

                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['valid_until']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars((string)$row['created_at']); ?>
                        </td>

                        <td>

                            <a
                                href="?delete=<?php echo (int)$row['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Delete this quotation?')"
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

                        No quotations found.

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