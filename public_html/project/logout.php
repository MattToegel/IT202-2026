<?php
require_once(__DIR__ . "/../../lib/app.php");

session_unset();
session_destroy();
session_start();
session_regenerate_id(true);
add_flash("Logged out", "success");

header("Location: login.php");
exit;
?>