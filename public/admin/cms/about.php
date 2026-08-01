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
| ADMIN CMS SERVICE
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../includes/repositories.php';

$cmsService = new \App\Services\AdminCmsService();

/*
|--------------------------------------------------------------------------
| INIT
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| HANDLE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $heroTitle       = trim($_POST['hero_title'] ?? '');
    $heroDescription = trim($_POST['hero_description'] ?? '');
    $missionTitle    = trim($_POST['mission_title'] ?? '');
    $missionContent  = trim($_POST['mission_content'] ?? '');
    $visionTitle     = trim($_POST['vision_title'] ?? '');
    $visionContent   = trim($_POST['vision_content'] ?? '');
    $processContent  = trim($_POST['process_content'] ?? '');
    $whyChooseContent = trim($_POST['why_choose_content'] ?? '');

    if (
        $heroTitle === '' ||
        $heroDescription === '' ||
        $missionTitle === '' ||
        $missionContent === '' ||
        $visionTitle === '' ||
        $visionContent === '' ||
        $processContent === '' ||
        $whyChooseContent === ''
    ) {
        $error = 'All fields are required.';
    }

    if ($error === '') {
        $result = $cmsService->saveAboutPage([
            'hero_title'         => $heroTitle,
            'hero_description'   => $heroDescription,
            'mission_title'      => $missionTitle,
            'mission_content'    => $missionContent,
            'vision_title'       => $visionTitle,
            'vision_content'     => $visionContent,
            'process_content'    => $processContent,
            'why_choose_content' => $whyChooseContent,
        ]);

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

/*
|--------------------------------------------------------------------------
| FETCH DATA
|--------------------------------------------------------------------------
*/

$data = $cmsService->getAboutPage();

?>
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Page CMS</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f5f5f5; padding:40px; }
        .container { max-width:1100px; margin:auto; background:#fff; padding:40px; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        h1 { margin-bottom:35px; color:#222; }
        h2 { margin-bottom:20px; color:#444; }
        .section { margin-bottom:40px; }
        .form-group { margin-bottom:25px; }
        label { display:block; margin-bottom:10px; font-weight:bold; color:#333; }
        input, textarea { width:100%; padding:14px; border:1px solid #ddd; border-radius:10px; font-size:15px; }
        textarea { min-height:160px; resize:vertical; }
        button { width:100%; padding:16px; border:none; border-radius:10px; background:#f5b400; color:#fff; font-size:16px; font-weight:bold; cursor:pointer; }
        button:hover { background:#d99f00; }
        .alert { padding:16px 20px; border-radius:10px; margin-bottom:30px; font-weight:bold; }
        .success { background:#e7f9ed; color:#1e7e34; }
        .error { background:#ffe5e5; color:#d8000c; }
        .back { display:inline-block; margin-top:30px; text-decoration:none; color:#333; font-weight:bold; }
    </style>
</head>
<body>

<div class="container">

    <h1>About Page CMS</h1>

    <?php if ($success !== ''): ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="section">
            <h2>Hero Section</h2>
            <div class="form-group">
                <label>Hero Title</label>
                <input type="text" name="hero_title" value="<?php echo htmlspecialchars((string)($data['hero_title'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Hero Description</label>
                <textarea name="hero_description" required><?php echo htmlspecialchars((string)($data['hero_description'] ?? '')); ?></textarea>
            </div>
        </div>

        <div class="section">
            <h2>Mission Section</h2>
            <div class="form-group">
                <label>Mission Title</label>
                <input type="text" name="mission_title" value="<?php echo htmlspecialchars((string)($data['mission_title'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Mission Content</label>
                <textarea name="mission_content" required><?php echo htmlspecialchars((string)($data['mission_content'] ?? '')); ?></textarea>
            </div>
        </div>

        <div class="section">
            <h2>Vision Section</h2>
            <div class="form-group">
                <label>Vision Title</label>
                <input type="text" name="vision_title" value="<?php echo htmlspecialchars((string)($data['vision_title'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Vision Content</label>
                <textarea name="vision_content" required><?php echo htmlspecialchars((string)($data['vision_content'] ?? '')); ?></textarea>
            </div>
        </div>

        <div class="section">
            <h2>Construction Process</h2>
            <div class="form-group">
                <label>Process Content</label>
                <textarea name="process_content" required><?php echo htmlspecialchars((string)($data['process_content'] ?? '')); ?></textarea>
            </div>
        </div>

        <div class="section">
            <h2>Why Choose Us</h2>
            <div class="form-group">
                <label>Why Choose KVN</label>
                <textarea name="why_choose_content" required><?php echo htmlspecialchars((string)($data['why_choose_content'] ?? '')); ?></textarea>
            </div>
        </div>

        <button type="submit">Save About Page</button>

    </form>

    <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="back">← Back to Dashboard</a>

</div>

</body>
</html>