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
| DELETE FAQ
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $cmsService->deleteFaq($id);
    header('Location: faq.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$success = '';
$error   = '';

$editMode = false;
$editId = 0;
$question = '';
$answer = '';
$displayOrder = 0;
$status = 'active';

/*
|--------------------------------------------------------------------------
| EDIT FETCH
|--------------------------------------------------------------------------
*/

if (isset($_GET['edit'])) {
    $editMode = true;
    $editId = (int) $_GET['edit'];
    $faq = $cmsService->getFaq($editId);
    if ($faq) {
        $question = $faq['question'] ?? '';
        $answer = $faq['answer'] ?? '';
        $displayOrder = (int)($faq['display_order'] ?? 0);
        $status = $faq['status'] ?? 'active';
    }
}

/*
|--------------------------------------------------------------------------
| SAVE FAQ
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $question     = trim($_POST['question'] ?? '');
    $answer       = trim($_POST['answer'] ?? '');
    $displayOrder = (int) ($_POST['display_order'] ?? 0);
    $status       = trim($_POST['status'] ?? 'active');
    $faqId        = (int) ($_POST['faq_id'] ?? 0);

    if ($question === '' || $answer === '') {
        $error = 'Please fill all required fields.';
    }

    if ($error === '') {
        $result = $cmsService->saveFaq([
            'question'      => $question,
            'answer'        => $answer,
            'display_order' => $displayOrder,
            'status'        => $status,
        ], $faqId > 0 ? $faqId : null);

        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

/*
|--------------------------------------------------------------------------
| FETCH FAQS
|--------------------------------------------------------------------------
*/

$faqs = $cmsService->getAllFaqs();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ CMS</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial,sans-serif; background:#f5f5f5; padding:40px; }
        .container { max-width:1200px; margin:auto; background:#fff; padding:40px; border-radius:20px; box-shadow:0 5px 20px rgba(0,0,0,0.08); }
        h1 { margin-bottom:30px; color:#222; }
        h2 { margin-bottom:20px; color:#444; }
        .section { margin-bottom:50px; }
        .form-group { margin-bottom:20px; }
        label { display:block; margin-bottom:10px; font-weight:bold; }
        input, textarea, select { width:100%; padding:14px; border:1px solid #ddd; border-radius:10px; font-size:15px; }
        textarea { min-height:150px; resize:vertical; }
        button { padding:14px 22px; border:none; border-radius:10px; background:#f5b400; color:#fff; font-weight:bold; cursor:pointer; }
        button:hover { background:#d99f00; }
        .alert { padding:15px 20px; border-radius:10px; margin-bottom:25px; font-weight:bold; }
        .success { background:#e7f9ed; color:#1e7e34; }
        .error { background:#ffe5e5; color:#d8000c; }
        table { width:100%; border-collapse:collapse; }
        table th, table td { padding:16px; border-bottom:1px solid #eee; text-align:left; vertical-align:top; }
        table th { background:#fafafa; }
        .status { padding:6px 12px; border-radius:20px; font-size:13px; font-weight:bold; display:inline-block; }
        .active { background:#e7f9ed; color:#1e7e34; }
        .inactive { background:#ffe5e5; color:#d8000c; }
        .actions a { text-decoration:none; margin-right:10px; font-weight:bold; }
        .edit { color:#0066cc; }
        .delete { color:#d8000c; }
        .back { display:inline-block; margin-top:30px; text-decoration:none; color:#333; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>FAQ CMS</h1>

    <?php if ($success !== ''): ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- FAQ FORM -->
    <div class="section">
        <h2><?php echo $editMode ? 'Edit FAQ' : 'Add FAQ'; ?></h2>
        <form method="POST">
            <input type="hidden" name="faq_id" value="<?php echo htmlspecialchars((string)$editId); ?>">

            <div class="form-group">
                <label>Question</label>
                <input type="text" name="question" value="<?php echo htmlspecialchars($question); ?>" required>
            </div>
            <div class="form-group">
                <label>Answer</label>
                <textarea name="answer" required><?php echo htmlspecialchars($answer); ?></textarea>
            </div>
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" value="<?php echo htmlspecialchars((string)$displayOrder); ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit"><?php echo $editMode ? 'Update FAQ' : 'Add FAQ'; ?></button>
        </form>
    </div>

    <!-- FAQ TABLE -->
    <div class="section">
        <h2>All FAQs</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Status</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($faqs) > 0): ?>
                    <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td><?php echo (int)$faq['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars((string)$faq['question']); ?></strong>
                                <br>
                                <?php echo nl2br(htmlspecialchars((string)$faq['answer'])); ?>
                            </td>
                            <td>
                                <span class="status <?php echo htmlspecialchars((string)$faq['status']); ?>">
                                    <?php echo ucfirst((string)$faq['status']); ?>
                                </span>
                            </td>
                            <td><?php echo (int)($faq['display_order'] ?? 0); ?></td>
                            <td class="actions">
                                <a href="?edit=<?php echo (int)$faq['id']; ?>" class="edit">Edit</a>
                                <a href="?delete=<?php echo (int)$faq['id']; ?>" class="delete" onclick="return confirm('Delete this FAQ?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No FAQs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="back">← Back to Dashboard</a>
</div>
</body>
</html>
