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
    <?php render_head($page_title); ?>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <p>Welcome, <?php echo htmlspecialchars(get_user_username()); ?></p>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
