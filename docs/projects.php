<?php

include "includes/auth.php";
include "includes/db.php";

/* ================================= */
/* ADD PROJECT */
/* ================================= */

if(isset($_POST['add_project'])){

    $client_name =
        $_POST['client_name'];

    $project_name =
        $_POST['project_name'];

    $location =
        $_POST['location'];

    $budget =
        $_POST['budget'];

    $status =
        $_POST['status'];

    $progress =
        $_POST['progress'];

    $start_date =
        $_POST['start_date'];

    $end_date =
        $_POST['end_date'];

    $description =
        $_POST['description'];

    /* IMAGE */

    $imageName =
        time() . "_" .
        $_FILES['project_image']['name'];

    $tmpName =
        $_FILES['project_image']['tmp_name'];

    $uploadPath =
        "uploads/projects/" . $imageName;

    move_uploaded_file(
        $tmpName,
        $uploadPath
    );

    /* INSERT */

    $sql = "

    INSERT INTO projects (

        client_name,
        project_name,
        location,
        budget,
        status,
        progress,
        start_date,
        end_date,
        project_image,
        description

    )

    VALUES (

        '$client_name',
        '$project_name',
        '$location',
        '$budget',
        '$status',
        '$progress',
        '$start_date',
        '$end_date',
        '$imageName',
        '$description'
    )
    ";

    $conn->query($sql);

    header("Location: projects.php");
}

/* ================================= */
/* DELETE */
/* ================================= */

if(isset($_GET['delete'])){

    $id =
        $_GET['delete'];

    /* DELETE IMAGE */

    $project =
    $conn->query(
        "SELECT * FROM projects
         WHERE id=$id"
    )->fetch_assoc();

    if($project){

        @unlink(
            "uploads/projects/" .
            $project['project_image']
        );
    }

    /* DELETE PROJECT */

    $conn->query(
        "DELETE FROM projects
         WHERE id=$id"
    );

    header("Location: projects.php");
}

/* ================================= */
/* GET PROJECTS */
/* ================================= */

$projects =
$conn->query(
    "SELECT * FROM projects
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Projects Management
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
            Projects Management
        </h1>

        <!-- ADD PROJECT -->

        <div class="card">

            <form method="POST"
                  enctype="multipart/form-data">

                <input type="text"
                       name="client_name"
                       placeholder="Client Name"
                       required>

                <input type="text"
                       name="project_name"
                       placeholder="Project Name"
                       required>

                <input type="text"
                       name="location"
                       placeholder="Project Location"
                       required>

                <input type="number"
                       name="budget"
                       placeholder="Project Budget"
                       required>

                <select name="status">

                    <option>
                        Planning
                    </option>

                    <option>
                        Ongoing
                    </option>

                    <option>
                        Completed
                    </option>

                    <option>
                        Delayed
                    </option>

                </select>

                <input type="number"
                       name="progress"
                       placeholder="Progress %"
                       min="0"
                       max="100"
                       required>

                <input type="date"
                       name="start_date"
                       required>

                <input type="date"
                       name="end_date"
                       required>

                <input type="file"
                       name="project_image"
                       required>

                <textarea name="description"
                          placeholder="Project Description"></textarea>

                <button type="submit"
                        name="add_project">

                    Add Project

                </button>

            </form>

        </div>

        <!-- PROJECTS LIST -->

        <div class="card">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Image</th>

                    <th>Project</th>

                    <th>Client</th>

                    <th>Status</th>

                    <th>Progress</th>

                    <th>Budget</th>

                    <th>Actions</th>

                </tr>

                <?php while($row = $projects->fetch_assoc()) { ?>

                <tr>

                    <td>

                        <?php echo $row['id']; ?>

                    </td>

                    <td>

                        <img src="uploads/projects/<?php echo $row['project_image']; ?>"
                             width="80"
                             style="border-radius:10px;">

                    </td>

                    <td>

                        <?php echo $row['project_name']; ?>

                    </td>

                    <td>

                        <?php echo $row['client_name']; ?>

                    </td>

                    <td>

                        <?php echo $row['status']; ?>

                    </td>

                    <td>

                        <?php echo $row['progress']; ?>%

                    </td>

                    <td>

                        ₹<?php echo number_format($row['budget']); ?>

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