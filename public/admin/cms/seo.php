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
| VARIABLES
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| UPDATE SEO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id              = (int) ($_POST['id'] ?? 0);
    $metaTitle       = trim($_POST['meta_title'] ?? '');
    $metaDescription = trim($_POST['meta_description'] ?? '');
    $metaKeywords    = trim($_POST['meta_keywords'] ?? '');
    $canonicalUrl    = trim($_POST['canonical_url'] ?? '');
    $ogTitle         = trim($_POST['og_title'] ?? '');
    $ogDescription   = trim($_POST['og_description'] ?? '');
    $ogImage         = trim($_POST['og_image'] ?? '');
    $robots          = trim($_POST['robots'] ?? '');

    if ($metaTitle === '' || $metaDescription === '' || $metaKeywords === '' ||
        $canonicalUrl === '' || $ogTitle === '' || $ogDescription === '' ||
        $ogImage === '' || $robots === '') {
        $error = 'Please fill all fields.';
    }

    if ($error === '' && $id > 0) {
        $result = $cmsService->saveSeoById($id, [
            'meta_title'       => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords'    => $metaKeywords,
            'canonical_url'    => $canonicalUrl,
            'og_title'         => $ogTitle,
            'og_description'   => $ogDescription,
            'og_image'         => $ogImage,
            'robots'           => $robots,
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
| FETCH SEO DATA
|--------------------------------------------------------------------------
*/

$seoPages = $cmsService->getAllSeoSettings();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO CMS</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f5f5f5; padding:40px; }
        .container { max-width:1200px; margin:auto; background:#fff; padding:40px; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        h1 { margin-bottom:35px; color:#222; }
        h2 { margin-bottom:25px; color:#444; }
        .seo-card { border:1px solid #eee; border-radius:16px; padding:30px; margin-bottom:40px; background:#fafafa; }
        .form-group { margin-bottom:20px; }
        label { display:block; margin-bottom:10px; font-weight:bold; color:#333; }
        input, textarea { width:100%; padding:14px; border:1px solid #ddd; border-radius:10px; font-size:15px; }
        textarea { min-height:120px; resize:vertical; }
        button { padding:14px 25px; border:none; border-radius:10px; background:#f5b400; color:#fff; font-weight:bold; cursor:pointer; }
        button:hover { background:#d99f00; }
        .alert { padding:16px 20px; border-radius:10px; margin-bottom:25px; font-weight:bold; }
        .success { background:#e7f9ed; color:#1e7e34; }
        .error { background:#ffe5e5; color:#d8000c; }
        .page-title { margin-bottom:20px; color:#111; text-transform:capitalize; }
        .back { display:inline-block; margin-top:20px; text-decoration:none; color:#333; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>SEO Settings CMS</h1>

    <?php if ($success !== ''): ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php foreach ($seoPages as $seo): ?>
        <div class="seo-card">
            <h2 class="page-title"><?php echo htmlspecialchars(ucfirst((string)$seo['page_name'])); ?> Page SEO</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo (int)$seo['id']; ?>">

                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" value="<?php echo htmlspecialchars((string)$seo['meta_title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea name="meta_description" required><?php echo htmlspecialchars((string)$seo['meta_description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Meta Keywords</label>
                    <textarea name="meta_keywords" required><?php echo htmlspecialchars((string)$seo['meta_keywords']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Canonical URL</label>
                    <input type="text" name="canonical_url" value="<?php echo htmlspecialchars((string)$seo['canonical_url']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Open Graph Title</label>
                    <input type="text" name="og_title" value="<?php echo htmlspecialchars((string)$seo['og_title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Open Graph Description</label>
                    <textarea name="og_description" required><?php echo htmlspecialchars((string)$seo['og_description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Open Graph Image</label>
                    <input type="text" name="og_image" value="<?php echo htmlspecialchars((string)$seo['og_image']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Robots</label>
                    <input type="text" name="robots" value="<?php echo htmlspecialchars((string)$seo['robots']); ?>" required>
                </div>

                <button type="submit">Save SEO Settings</button>
            </form>
        </div>
    <?php endforeach; ?>

    <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="back">← Back to Dashboard</a>
</div>
</body>
</html>
