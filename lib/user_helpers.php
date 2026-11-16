<?php
// File: lib/user_helpers.php
/**
 * Used by navigation and guards after app.php starts the session.
 * @return bool Whether the session has a user entry; does not redirect.
 */
function is_logged_in(): bool
{
    return isset($_SESSION["user"]);
}

/**
 * Read the current email; the caller still escapes it for HTML output.
 * @return string Signed-in email, or an empty string when absent.
 */
function get_user_email(): string
{
    return is_logged_in() ? ($_SESSION["user"]["email"] ?? "") : "";
}
?>
