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

$videos = $clientService->getClientVideos($clientId);

/*
|--------------------------------------------------------------------------
| HANDLE ADD VIDEO
|--------------------------------------------------------------------------
*/

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');

    if (empty($title) || empty($videoUrl)) {
        $errorMessage = 'Title and video URL are required.';
    } else {
        if ($clientService->addClientVideo($clientId, $title, $videoUrl)) {
            $successMessage = 'Video added successfully.';
            $videos = $clientService->getClientVideos($clientId);
        } else {
            $errorMessage = 'Failed to add video.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Videos</title>
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
        .grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; }
        .video-card{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 5px 20px rgba(0,0,0,0.08); padding:20px; }
        .video-card h3{ margin-bottom:8px; }
        .video-card p{ color:#555; font-size:13px; }
        .video-card iframe{ width:100%; height:200px; border-radius:10px; margin-bottom:10px; border:none; }
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
    <a href="images.php">Images</a>
    <a href="videos.php" class="active">Videos</a>
    <a href="testimonials.php">Testimonials</a>
    <a href="feedback.php">Feedback</a>
    <a href="<?php echo base_url('logout.php'); ?>">Logout</a>
</div>
<div class="main">
    <div class="topbar">
        <h1>My Videos</h1>
        <a href="<?php echo base_url('logout.php'); ?>" class="logout-btn">Logout</a>
    </div>
    <?php if (!empty($successMessage)): ?><div class="success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
    <?php if (!empty($errorMessage)): ?><div class="error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>
    <div class="upload-card">
        <h3>Add Video</h3>
        <form method="POST" style="margin-top:15px;">
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Title</label>
                <input type="text" name="title" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Video URL (YouTube embed)</label>
                <input type="text" name="video_url" required placeholder="https://www.youtube.com/embed/..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
            </div>
            <button type="submit" style="background:#111827; color:#fff; border:none; padding:12px 22px; border-radius:10px; font-weight:bold; cursor:pointer;">Add Video</button>
        </form>
    </div>
    <?php if (count($videos) > 0): ?>
        <div class="grid">
            <?php foreach ($videos as $v): ?>
                <div class="video-card">
                    <?php $videoUrl = $v['video_url'] ?? ''; ?>
                    <?php if (strpos($videoUrl, 'youtube.com/embed') !== false || strpos($videoUrl, 'youtube.com') !== false): ?>
                        <iframe src="<?php echo htmlspecialchars((string)$videoUrl); ?>" allowfullscreen></iframe>
                    <?php else: ?>
                        <video controls style="width:100%; border-radius:10px; margin-bottom:10px;">
                            <source src="<?php echo htmlspecialchars((string)$videoUrl); ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars((string)($v['title'] ?? '')); ?></h3>
                    <p><?php echo htmlspecialchars((string)($v['created_at'] ?? '')); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty"><h2>No Videos</h2><p>You haven't added any videos yet.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
