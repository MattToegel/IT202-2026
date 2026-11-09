<?php
// File: lib/flash_messages.php; app.php starts the session first.
/**
 * Queue feedback before rendering a page or redirecting.
 * @param string $msg User-facing text, without passwords or internal errors.
 * @param string $color Display category, such as success or danger.
 */
function add_flash(string $msg = "", string $color = "info"): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("Flash messages require an active session.");
        return;
    }
    if (!isset($_SESSION["flash"])) {
        $_SESSION["flash"] = [];
    }
    $_SESSION["flash"][] = ["text" => $msg, "color" => $color];
}

/**
 * Queue the page's collected errors without rendering them.
 * @param string[] $errors Errors collected during server validation.
 * @param string $color Display category shared by these errors.
 */
function flash_errors(array $errors, string $color = "danger"): void
{
    foreach ($errors as $error) {
        add_flash($error, $color);
    }
}

/**
 * Drain the session queue when the flash partial is ready to render.
 * @return array[] Pending messages with text/color keys, in insertion order.
 */
function get_flash_messages(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("Flash messages require an active session.");
        return [];
    }
    $messages = $_SESSION["flash"] ?? [];
    $_SESSION["flash"] = [];
    return $messages;
}
?>
