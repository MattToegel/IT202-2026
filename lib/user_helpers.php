<?php
// File: lib/user_helpers.php
/** Used by navigation and page guards to check the current login session. */
function is_logged_in(): bool
{
    return isset($_SESSION["user"]);
}

/** Read the signed-in email for page output; callers still escape it when rendering. */
function get_user_email(): string
{
    return is_logged_in() ? ($_SESSION["user"]["email"] ?? "") : "";
}
?>
