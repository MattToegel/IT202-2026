<?php
require_once(__DIR__ . "/../../lib/app.php");

session_unset();
session_destroy();
session_start();
session_regenerate_id(true);
flash_notice("Logged out");

header("Location: login.php");
exit;
?>