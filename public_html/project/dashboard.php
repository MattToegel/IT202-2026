<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Dashboard";

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php render_nav(); ?>
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <p>Welcome, <?php echo htmlspecialchars(get_user_email()); ?></p>
    <?php render_flash_messages(); ?>
</body>
</html>
