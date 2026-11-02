<?php
// File: lib/flash_messages.php; lib/app.php starts the session first.
/** Called before redirecting to save one user-facing success notice. */
function flash_notice(string $notice): void
{
    $_SESSION["flash_notice"] = $notice;
}

/** Called by the destination page to read and remove the saved notice. */
function take_flash_notice(): string
{
    $notice = $_SESSION["flash_notice"] ?? "";
    unset($_SESSION["flash_notice"]);
    return $notice;
}
?>
