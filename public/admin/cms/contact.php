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
    $phone           = trim($_POST['phone'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $officeAddress   = trim($_POST['office_address'] ?? '');
    $officeHours     = trim($_POST['office_hours'] ?? '');
    $googleMapLink   = trim($_POST['google_map_link'] ?? '');
    $formTitle       = trim($_POST['form_title'] ?? '');
    $formDescription = trim($_POST['form_description'] ?? '');
    $whyChooseTitle  = trim($_POST['why_choose_title'] ?? '');
    $whyChooseContent = trim($_POST['why_choose_content'] ?? '');

    if (
        $heroTitle === '' || $heroDescription === '' || $phone === '' ||
        $email === '' || $officeAddress === '' || $officeHours === '' ||
        $googleMapLink === '' || $formTitle === '' || $formDescription === '' ||
        $whyChooseTitle === '' || $whyChooseContent === ''
    ) {
        $error = 'Please fill all fields.';
    }

    if ($error === '') {
        $result = $cmsService->saveContactPage([
            'hero_title'         => $heroTitle,
            'hero_description'   => $heroDescription,
            'phone'              => $phone,
            'email'              => $email,
            'office_address'     => $officeAddress,
            'office_hours'       => $officeHours,
            'google_map_link'    => $googleMapLink,
            'form_title'         => $formTitle,
            'form_description'   => $formDescription,
            'why_choose_title'   => $whyChooseTitle,
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

$data = $cmsService->getContactPage();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Page CMS</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f4f4f4; padding:40px; }
        .container { max-width:1100px; margin:auto; background:#ffffff; padding:40px; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        h1 { margin-bottom:35px; color:#222; }
        h2 { margin-bottom:20px; color:#444; }
        .section { margin-bottom:40px; }
        .form-group { margin-bottom:25px; }
        label { display:block; margin-bottom:10px; font-weight:bold; color:#333; }
        input, textarea { width:100%; padding:14px; border:1px solid #ddd; border-radius:10px; font-size:15px; }
        textarea { min-height:140px; resize:vertical; }
        button { width:100%; padding:16px; border:none; border-radius:10px; background:#f5b400; color:#fff; font-size:16px; font-weight:bold; cursor:pointer; transition:0.3s; }
        button:hover { background:#d99f00; }
        .alert { padding:15px 20px; border-radius:10px; margin-bottom:30px; font-weight:bold; }
        .success { background:#e7f9ed; color:#1e7e34; }
        .error { background:#ffe5e5; color:#d8000c; }
        .back { display:inline-block; margin-top:30px; text-decoration:none; color:#333; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Contact Page CMS</h1>

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

        <div class="section">
            <h2>Contact Information</h2>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars((string)($data['phone'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars((string)($data['email'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Office Address</label>
                <textarea name="office_address" required><?php echo htmlspecialchars((string)($data['office_address'] ?? '')); ?></textarea>
            </div>
            <div class="form-group">
                <label>Office Hours</label>
                <input type="text" name="office_hours" value="<?php echo htmlspecialchars((string)($data['office_hours'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Google Map Link</label>
                <input type="text" name="google_map_link" value="<?php echo htmlspecialchars((string)($data['google_map_link'] ?? '')); ?>" required>
            </div>

        <div class="section">
            <h2>Contact Form Section</h2>
            <div class="form-group">
                <label>Form Title</label>
                <input type="text" name="form_title" value="<?php echo htmlspecialchars((string)($data['form_title'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Form Description</label>
                <textarea name="form_description" required><?php echo htmlspecialchars((string)($data['form_description'] ?? '')); ?></textarea>
            </div>

        <div class="section">
            <h2>Why Choose Us</h2>
            <div class="form-group">
                <label>Section Title</label>
                <input type="text" name="why_choose_title" value="<?php echo htmlspecialchars((string)($data['why_choose_title'] ?? '')); ?>" required>
            </div>
            <div class="form-group">
                <label>Section Content</label>
                <textarea name="why_choose_content" required><?php echo htmlspecialchars((string)($data['why_choose_content'] ?? '')); ?></textarea>
            </div>

        <button type="submit">Save Contact Page</button>
    </form>

    <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="back">← Back to Dashboard</a>
</div>
</body>
</html>
