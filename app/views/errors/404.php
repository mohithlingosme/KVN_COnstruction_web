<?php
/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| 404 ERROR PAGE
|--------------------------------------------------------------------------
*/
$pageTitle = '404 - Page Not Found | KVN Construction';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo defined('APP_URL') ? APP_URL : '/KVN_Construction/public'; ?>/assets/css/style.css">
    <style>
        .error-container { text-align: center; padding: 100px 20px; max-width: 600px; margin: 0 auto; }
        .error-container h1 { font-size: 120px; margin: 0; color: #e74c3c; line-height: 1; }
        .error-container h2 { font-size: 28px; margin: 20px 0; color: #333; }
        .error-container p { font-size: 16px; color: #666; margin-bottom: 30px; }
        .error-container .btn-main { display: inline-block; padding: 12px 30px; background: #d4af37; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; }
        .error-container .btn-main:hover { background: #b5952f; color: #fff; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for does not exist, has been moved, or is temporarily unavailable.</p>
        <a href="<?php echo defined('APP_URL') ? APP_URL : '/KVN_Construction/public'; ?>" class="btn-main">Go to Homepage</a>
    </div>
</body>
</html>