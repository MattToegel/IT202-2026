<?php
// File: public_html/project/index.php
require_once(__DIR__ . "/../../lib/app.php");
// TODO: use your name or project title in the welcome heading.
$page_title = "Welcome to My Project";
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
    <div class="jumbotron">
        <h1 class="display-4"><?php echo htmlspecialchars($page_title); ?></h1>
        <!-- TODO: add your project description and semester. -->
        <p class="lead">This is my IT202 project.</p>
        <!-- TODO: replace your_ucid with your UCID. -->
        <p>UCID: your_ucid</p>
        <p>
            <a class="btn btn-primary" href="register.php">Register</a>
            <a class="btn btn-secondary" href="login.php">Login</a>
        </p>
    </div>
</body>
</html>
