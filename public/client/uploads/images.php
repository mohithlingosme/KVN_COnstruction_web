<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['client_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../../includes/repositories.php';

$clientService = new \App\Services\ClientService();
$clientId = (int) $_SESSION['client_id'];
$clientName = $_SESSION['client_name'] ?? 'Client';

$images = $clientService->getClientImages($clientId);

/*
|--------------------------------------------------------------------------
| HANDLE UPLOAD
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

$uploadDir = '../../uploads/client_images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if ($_FILES['image']['error'] === 0) {
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            if ($clientService->addClientImage($clientId, $fileName)) {
                $successMessage = 'Image uploaded successfully.';
                $images = $clientService->getClientImages($clientId);
            } else {
                $errorMessage = 'Failed to save image record.';
            }
        } else {
            $errorMessage = 'Failed to upload image.';
        }
    } else {
        $errorMessage = 'Please select an image to upload.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Images</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:Arial,sans-serif; background:#f3f4f6; color:#222; }
        .sidebar{ width:260px; height:100vh; background:#111827; position:fixed; top:0; left:0; padding:30px 20px; overflow:auto; }
        .sidebar h2{ color:#f5b400; margin-bottom:35px; }
        .sidebar a{ display:block; text-decoration:none; color:#fff; padding:14px 16px; border-radius:10px; margin-bottom:10px; transition:0.3s; }
        .sidebar a:hover, .sidebar .active{ background:#f5b400; color:#111; }
        .main{ margin-left:260px; padding:40px; }
        .topbar{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:35px; }
        .logout-btn{ text-decoration:none; background:#dc3545; color:#fff; padding:12px 18px; border-radius:10px; font-weight:bold; }
        .upload-card{ background:#fff; border-radius:20px; padding:25px; margin-bottom:30px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:20px; }
        .image-card{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        .image-card img{ width:100%; height:200px; object-fit:cover; }
        .image-card .info{ padding:15px; }
        .image-card .info p{ color:#555; font-size:13px; }
        .success{ background:#d4edda; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px; }
        .error{ background:#f8d7da; color:#721c24; padding:15px; border-radius:10px; margin-bottom:20px; }
        .empty{ text-align:center; padding:80px; color:#777; background:#fff; border-radius:20px; }
        @media(max-width:992px){ .sidebar{ width:100%; height:auto; position:relative; } .main{ margin-left:0; } }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>KVN Client</h2>
    <a href="<?php echo base_url('client/dashboard.php'); ?>">Dashboard</a>
    <a href="images.php" class="active">Images</a>
    <a href="videos.php">Videos</a>
    <a href="testimonials.php">Testimonials</a>
    <a href="feedback.php">Feedback</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>My Images</h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (!empty($successMessage)): ?><div class="success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
    <?php if (!empty($errorMessage)): ?><div class="error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
    <div class="upload-card">
        <h3>Upload Image</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top:15px;">
            <input type="file" name="image" accept="image/*" required style="padding:12px; border:1px solid #ddd; border-radius:10px; width:100%;">
            <button type="submit" style="margin-top:15px; background:#111827; color:#fff; border:none; padding:12px 22px; border-radius:10px; font-weight:bold; cursor:pointer;">Upload</button>
        </form>
    </div>
    <?php if (count($images) > 0): ?>
        <div class="grid">
            <?php foreach ($images as $img): ?>
                <div class="image-card">
                    <img src="../../uploads/client_images/<?php echo htmlspecialchars((string)($img['filename'] ?? $img['file_path'] ?? '')); ?>" alt="Uploaded Image">
                    <div class="info">
                        <p><?php echo htmlspecialchars((string)($img['title'] ?? $img['description'] ?? '')); ?></p>
                        <p style="color:#888; font-size:12px;"><?php echo htmlspecialchars((string)($img['created_at'] ?? '')); ?></p>
                    </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty"><h2>No Images</h2><p>You haven't uploaded any images yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
