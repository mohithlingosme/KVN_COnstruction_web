<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/middleware/AuthMiddleware.php';
require_once __DIR__ . '/includes/db.php';

AuthMiddleware::requireAuth();

// Helper function for escaping output
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
*/
if (isset($_POST['add_package'])) {
    try {
        $package_name = trim((string)($_POST['package_name'] ?? ''));
        $base_price = trim((string)($_POST['base_price'] ?? ''));
        $location_multiplier = trim((string)($_POST['location_multiplier'] ?? ''));
        $interior_multiplier = trim((string)($_POST['interior_multiplier'] ?? ''));
        $smart_home_multiplier = trim((string)($_POST['smart_home_multiplier'] ?? ''));
        $vastu_multiplier = trim((string)($_POST['vastu_multiplier'] ?? ''));
        $material_grade = trim((string)($_POST['material_grade'] ?? ''));
        $estimated_timeline = trim((string)($_POST['estimated_timeline'] ?? ''));

        if ($package_name === '' || $base_price === '' || $estimated_timeline === '') {
            $error = "Please fill required fields.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO construction_packages
                (package_name, base_price, location_multiplier, interior_multiplier, smart_home_multiplier, vastu_multiplier, material_grade, estimated_timeline)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if ($stmt === false) {
                $error = "Database insert preparation failed: " . $conn->error;
                error_log("Package insert prepare error: " . $conn->error);
            } else {
                $stmt->bind_param(
                    "sdddddds",
                    $package_name,
                    $base_price,
                    $location_multiplier,
                    $interior_multiplier,
                    $smart_home_multiplier,
                    $vastu_multiplier,
                    $material_grade,
                    $estimated_timeline
                );

                if (!$stmt->execute()) {
                    $error = "Database insert failed: " . $stmt->error;
                    error_log("Package insert execute error: " . $stmt->error);
                } else {
                    $success = "Package added successfully!";
                }

                $stmt->close();
            }

            if ($success !== '') {
                header("Refresh:2; url=admin-packages.php");
            }
        }
    } catch (Throwable $e) {
        $error = "Error adding package: " . $e->getMessage();
        error_log("Package add error: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {
    try {
        $id = (int)($_GET['delete'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM construction_packages WHERE id = ?");
            if ($stmt !== false) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $success = "Package deleted successfully!";
                    error_log("Package deleted: ID {$id}");
                } else {
                    $error = "Failed to delete package: " . $stmt->error;
                    error_log("Package delete error: " . $stmt->error);
                }
                $stmt->close();
            }
        } else {
            $error = "Invalid package ID.";
        }
        header("Refresh:2; url=admin-packages.php");
        exit();
    } catch (Throwable $e) {
        $error = "Error deleting package: " . $e->getMessage();
        error_log("Package delete error: " . $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/
if (isset($_POST['update_package'])) {
    try {
        $id = (int)($_POST['id'] ?? 0);

        $package_name = trim((string)($_POST['package_name'] ?? ''));
        $base_price = trim((string)($_POST['base_price'] ?? ''));
        $location_multiplier = trim((string)($_POST['location_multiplier'] ?? ''));
        $interior_multiplier = trim((string)($_POST['interior_multiplier'] ?? ''));
        $smart_home_multiplier = trim((string)($_POST['smart_home_multiplier'] ?? ''));
        $vastu_multiplier = trim((string)($_POST['vastu_multiplier'] ?? ''));
        $material_grade = trim((string)($_POST['material_grade'] ?? ''));
        $estimated_timeline = trim((string)($_POST['estimated_timeline'] ?? ''));

        if ($id > 0 && $package_name !== '') {
            $stmt = $conn->prepare(
                "UPDATE construction_packages SET
                    package_name = ?,
                    base_price = ?,
                    location_multiplier = ?,
                    interior_multiplier = ?,
                    smart_home_multiplier = ?,
                    vastu_multiplier = ?,
                    material_grade = ?,
                    estimated_timeline = ?
                WHERE id = ?"
            );

            if ($stmt !== false) {
                $stmt->bind_param(
                    "sdddddsi",
                    $package_name,
                    $base_price,
                    $location_multiplier,
                    $interior_multiplier,
                    $smart_home_multiplier,
                    $vastu_multiplier,
                    $material_grade,
                    $estimated_timeline,
                    $id
                );

                if (!$stmt->execute()) {
                    $error = "Database update failed: " . $stmt->error;
                    error_log("Package update error: " . $stmt->error);
                } else {
                    $success = "Package updated successfully!";
                    error_log("Package updated: ID {$id}");
                }
                $stmt->close();
            } else {
                $error = "Database prepare failed: " . $conn->error;
                error_log("Package update prepare error: " . $conn->error);
            }
        } else {
            $error = "Invalid package ID or name.";
        }

        if ($success !== '') {
            header("Refresh:2; url=admin-packages.php");
        }
    } catch (Throwable $e) {
        $error = "Error updating package: " . $e->getMessage();
        error_log("Package update error: " . $e->getMessage());
    }
}

// Load packages
$packages = [];
try {
    $res = $conn->query("SELECT * FROM construction_packages ORDER BY id DESC");
    if ($res !== false) {
        $packages = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "Failed to load packages: " . $conn->error;
        error_log("Package query error: " . $conn->error);
    }
} catch (Throwable $e) {
    $error = "Error loading packages: " . $e->getMessage();
    error_log("Package load error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Construction Packages | KVN</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:Arial; background:#f5f5f5; padding:40px; }
        h1{ margin-bottom:30px; color:#222; }
        .container{ max-width:1200px; margin:auto; }
        .card{
            background:#fff; padding:30px; border-radius:20px; margin-bottom:30px;
            box-shadow:0 5px 20px rgba(0,0,0,0.05);
        }
        form{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
        input, select, textarea { padding:15px; border:1px solid #ddd; border-radius:10px; font-family:Arial; }
        button{
            background:#f5b400; color:#fff; border:none; padding:15px; border-radius:10px;
            cursor:pointer; font-weight:bold;
        }
        button:hover { background:#d89d00; }
        table{ width:100%; border-collapse:collapse; }
        table th, table td{ padding:15px; border-bottom:1px solid #eee; text-align:left; }
        .delete-btn{ background:red; padding:10px 15px; font-size:12px; }
        .action-buttons{ display:flex; gap:10px; }
        @media(max-width:768px){
            form{ grid-template-columns:1fr; }
            body{ padding:20px; }
            table{ display:block; overflow:auto; }
        }
        .error{
            background:#ffe5e5; color:#d10000; padding:12px; border-radius:10px;
            margin-bottom:20px; font-size:14px;
        }
        .success{
            background:#e5ffe5; color:#00b000; padding:12px; border-radius:10px;
            margin-bottom:20px; font-size:14px;
        }
    </style>
</head>
<body>
<div class="container">

    <?php if ($error !== ""): ?>
        <div class="error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="success"><?php echo e($success); ?></div>
    <?php endif; ?>

    <div class="card">
        <h1>Add Construction Package</h1>

        <form method="POST">
            <input type="text" name="package_name" placeholder="Package Name" required>
            <input type="number" name="base_price" placeholder="Base Price" step="0.01" required>

            <input type="number" step="0.01" name="location_multiplier" placeholder="Location Multiplier" required>
            <input type="number" step="0.01" name="interior_multiplier" placeholder="Interior Multiplier" required>

            <input type="number" step="0.01" name="smart_home_multiplier" placeholder="Smart Home Multiplier" required>
            <input type="number" step="0.01" name="vastu_multiplier" placeholder="Vastu Multiplier" required>

            <input type="text" name="material_grade" placeholder="Material Grade" required>
            <input type="text" name="estimated_timeline" placeholder="Timeline (e.g., 12 months)" required>

            <button type="submit" name="add_package">Add Package</button>
        </form>
    </div>

    <div class="card">
        <h1>Manage Packages</h1>

        <?php if (count($packages) === 0): ?>
            <p>No packages found.</p>
        <?php else: ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Package</th>
                <th>Price</th>
                <th>Location</th>
                <th>Interior</th>
                <th>Smart</th>
                <th>Vastu</th>
                <th>Material</th>
                <th>Timeline</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($packages as $row): ?>
                <tr>
                    <form method="POST">
                        <td>
                            <?php echo (int)$row['id']; ?>
                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                        </td>

                        <td><input type="text" name="package_name" value="<?php echo e((string)$row['package_name']); ?>"></td>
                        <td><input type="number" step="0.01" name="base_price" value="<?php echo e((string)$row['base_price']); ?>"></td>

                        <td><input type="number" step="0.01" name="location_multiplier" value="<?php echo e((string)$row['location_multiplier']); ?>"></td>
                        <td><input type="number" step="0.01" name="interior_multiplier" value="<?php echo e((string)$row['interior_multiplier']); ?>"></td>

                        <td><input type="number" step="0.01" name="smart_home_multiplier" value="<?php echo e((string)$row['smart_home_multiplier']); ?>"></td>
                        <td><input type="number" step="0.01" name="vastu_multiplier" value="<?php echo e((string)$row['vastu_multiplier']); ?>"></td>

                        <td><input type="text" name="material_grade" value="<?php echo e((string)$row['material_grade']); ?>"></td>
                        <td><input type="text" name="estimated_timeline" value="<?php echo e((string)$row['estimated_timeline']); ?>"></td>

                        <td>
                            <div class="action-buttons">
                                <button type="submit" name="update_package">Update</button>
                                <a href="?delete=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this package?');">
                                    <button type="button" class="delete-btn">Delete</button>
                                </a>
                            </div>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>

        </table>

        <?php endif; ?>
    </div>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:Arial; background:#f5f5f5; padding:40px; }
        h1{ margin-bottom:30px; color:#222; }
        .container{ max-width:1200px; margin:auto; }
        .card{
            background:#fff; padding:30px; border-radius:20px; margin-bottom:30px;
            box-shadow:0 5px 20px rgba(0,0,0,0.05);
        }
        form{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
        input{ padding:15px; border:1px solid #ddd; border-radius:10px; }
        button{
            background:#f5b400; color:#fff; border:none; padding:15px; border-radius:10px;
            cursor:pointer; font-weight:bold;
        }
        table{ width:100%; border-collapse:collapse; }
        table th, table td{ padding:15px; border-bottom:1px solid #eee; text-align:left; }
        .edit-btn{ background:#222; }
        .delete-btn{ background:red; }
        .action-buttons{ display:flex; gap:10px; }
        @media(max-width:768px){
            form{ grid-template-columns:1fr; }
            body{ padding:20px; }
            table{ display:block; overflow:auto; }
        }
        .error{
            background:#ffe5e5; color:#d10000; padding:12px; border-radius:10px;
            margin-bottom:20px; font-size:14px;
        }
    </style>
</head>
<body>
<div class="container">

    <?php if ($error !== "") : ?>
        <div class="error"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <h1>Add Construction Package</h1>

        <form method="POST">
            <input type="text" name="package_name" placeholder="Package Name" required>
            <input type="number" name="base_price" placeholder="Base Price" required>

            <input type="number" step="0.01" name="location_multiplier" placeholder="Location Multiplier" required>
            <input type="number" step="0.01" name="interior_multiplier" placeholder="Interior Multiplier" required>

            <input type="number" step="0.01" name="smart_home_multiplier" placeholder="Smart Home Multiplier" required>
            <input type="number" step="0.01" name="vastu_multiplier" placeholder="Vastu Multiplier" required>

            <input type="text" name="material_grade" placeholder="Material Grade" required>
            <input type="text" name="estimated_timeline" placeholder="Timeline" required>

            <button type="submit" name="add_package">Add Package</button>
        </form>
    </div>

    <div class="card">
        <h1>Manage Packages</h1>

        <table>
            <tr>
                <th>ID</th>
                <th>Package</th>
                <th>Price</th>
                <th>Location</th>
                <th>Interior</th>
                <th>Smart</th>
                <th>Vastu</th>
                <th>Material</th>
                <th>Timeline</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($packages as $row) : ?>
                <tr>
                    <form method="POST">
                        <td>
                            <?php echo (int)$row['id']; ?>
                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                        </td>

                        <td><input type="text" name="package_name" value="<?php echo e((string)$row['package_name']); ?>"></td>
                        <td><input type="number" name="base_price" value="<?php echo e((string)$row['base_price']); ?>"></td>

                        <td><input type="number" step="0.01" name="location_multiplier" value="<?php echo e((string)$row['location_multiplier']); ?>"></td>
                        <td><input type="number" step="0.01" name="interior_multiplier" value="<?php echo e((string)$row['interior_multiplier']); ?>"></td>

                        <td><input type="number" step="0.01" name="smart_home_multiplier" value="<?php echo e((string)$row['smart_home_multiplier']); ?>"></td>
                        <td><input type="number" step="0.01" name="vastu_multiplier" value="<?php echo e((string)$row['vastu_multiplier']); ?>"></td>

                        <td><input type="text" name="material_grade" value="<?php echo e((string)$row['material_grade']); ?>"></td>
                        <td><input type="text" name="estimated_timeline" value="<?php echo e((string)$row['estimated_timeline']); ?>"></td>

                        <td>
                            <div class="action-buttons">
                                <button type="submit" name="update_package" class="edit-btn">Update</button>
                                <a href="?delete=<?php echo (int)$row['id']; ?>">
                                    <button type="button" class="delete-btn">Delete</button>
                                </a>
                            </div>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>

        </table>
    </div>

</div>
</body>
</html>
