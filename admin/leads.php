<?php

include "includes/auth.php";
include "includes/db.php";

/* ADD LEAD */

if(isset($_POST['add_lead'])){

    $name =
        $_POST['name'];

    $phone =
        $_POST['phone'];

    $email =
        $_POST['email'];

    $project_type =
        $_POST['project_type'];

    $budget =
        $_POST['budget'];

    $status =
        $_POST['status'];

    $notes =
        $_POST['notes'];

    $sql = "

    INSERT INTO leads (

        name,
        phone,
        email,
        project_type,
        budget,
        status,
        notes

    )

    VALUES (

        '$name',
        '$phone',
        '$email',
        '$project_type',
        '$budget',
        '$status',
        '$notes'
    )
    ";

    $conn->query($sql);

    header("Location: leads.php");
}

/* DELETE */

if(isset($_GET['delete'])){

    $id =
        $_GET['delete'];

    $conn->query(
        "DELETE FROM leads
         WHERE id=$id"
    );

    header("Location: leads.php");
}

/* GET LEADS */

$leads =
$conn->query(
    "SELECT * FROM leads
     ORDER BY id DESC"
);
?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Leads CRM
    </title>

    <link rel="stylesheet"
          href="assets/css/admin.css">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>
            KVN Admin
        </h2>

        <ul>

            <li>
                <a href="dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="leads.php">
                    Leads
                </a>
            </li>

            <li>
                <a href="projects.php">
                    Projects
                </a>
            </li>

            <li>
                <a href="appointments.php">
                    Appointments
                </a>
            </li>

            <li>
                <a href="admin-packages.php">
                    Packages
                </a>
            </li>

            <li>
                <a href="logout.php">
                    Logout
                </a>
            </li>

        </ul>

    </aside>

    <!-- MAIN -->

    <main class="main-content">

        <h1>
            Leads CRM
        </h1>

        <!-- ADD LEAD -->

        <div class="card">

            <form method="POST">

                <input type="text"
                       name="name"
                       placeholder="Client Name"
                       required>

                <input type="text"
                       name="phone"
                       placeholder="Phone"
                       required>

                <input type="email"
                       name="email"
                       placeholder="Email">

                <input type="text"
                       name="project_type"
                       placeholder="Project Type">

                <input type="text"
                       name="budget"
                       placeholder="Budget">

                <select name="status">

                    <option>
                        New Lead
                    </option>

                    <option>
                        Follow-up
                    </option>

                    <option>
                        Converted
                    </option>

                    <option>
                        Closed
                    </option>

                </select>

                <textarea name="notes"
                          placeholder="Notes"></textarea>

                <button type="submit"
                        name="add_lead">

                    Add Lead

                </button>

            </form>

        </div>

        <!-- LEADS TABLE -->

        <div class="card">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Phone</th>

                    <th>Email</th>

                    <th>Project</th>

                    <th>Budget</th>

                    <th>Status</th>

                    <th>Actions</th>

                </tr>

                <?php while($row = $leads->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo $row['id']; ?>
                    </td>

                    <td>
                        <?php echo $row['name']; ?>
                    </td>

                    <td>
                        <?php echo $row['phone']; ?>
                    </td>

                    <td>
                        <?php echo $row['email']; ?>
                    </td>

                    <td>
                        <?php echo $row['project_type']; ?>
                    </td>

                    <td>
                        <?php echo $row['budget']; ?>
                    </td>

                    <td>
                        <?php echo $row['status']; ?>
                    </td>

                    <td>

                        <a href="?delete=<?php echo $row['id']; ?>">

                            Delete

                        </a>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </main>

</div>

</body>

</html>