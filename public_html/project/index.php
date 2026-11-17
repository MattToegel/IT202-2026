<?php
// File: public_html/project/index.php
require_once(__DIR__ . "/../../lib/app.php");
// TODO: use your name or project title in the welcome heading.
$page_title = "Welcome to My Project";
?>
<!doctype html>
<html lang="en">
<head>
    <?php render_head($page_title); ?>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
    <div class="jumbotron">
        <h1 class="display-4"><?php echo htmlspecialchars($page_title); ?></h1>
        <!-- TODO: add your project description and semester. -->
        <p class="lead">This is my IT202 project.</p>
        <!-- TODO: replace your_ucid with your UCID. -->
        <p>UCID: your_ucid</p>
        <p>
            <a class="btn btn-primary" href="<?php echo project_url("register.php"); ?>">Register</a>
            <a class="btn btn-secondary" href="<?php echo project_url("login.php"); ?>">Login</a>
        </p>
    </div>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
